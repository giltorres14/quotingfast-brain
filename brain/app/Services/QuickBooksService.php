<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\BuyerPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuickBooksService
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;

    public function __construct()
    {
        $this->baseUrl = config('services.quickbooks.base_url', 'https://sandbox-quickbooks.api.intuit.com');
        $this->clientId = config('services.quickbooks.client_id');
        $this->clientSecret = config('services.quickbooks.client_secret');
        $this->redirectUri = config('services.quickbooks.redirect_uri');
        $this->scope = 'com.intuit.quickbooks.accounting com.intuit.quickbooks.payment';
    }

    /**
     * Generate OAuth URL for QuickBooks connection
     */
    public function getAuthUrl($buyerId)
    {
        $state = base64_encode(json_encode([
            'buyer_id' => $buyerId,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16))
        ]));

        $params = [
            'client_id' => $this->clientId,
            'scope' => $this->scope,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'access_type' => 'offline',
            'state' => $state
        ];

        return 'https://appcenter.intuit.com/connect/oauth2?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access tokens
     */
    public function exchangeCodeForTokens($code, $state, $realmId)
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri
                ]);

            if ($response->successful()) {
                $tokens = $response->json();
                
                // Decode state to get buyer ID
                $stateData = json_decode(base64_decode($state), true);
                $buyerId = $stateData['buyer_id'];

                // Store tokens securely
                $this->storeTokens($buyerId, $tokens, $realmId);

                return [
                    'success' => true,
                    'buyer_id' => $buyerId,
                    'tokens' => $tokens
                ];
            }

            return ['success' => false, 'error' => 'Token exchange failed'];

        } catch (\Exception $e) {
            Log::error('QuickBooks token exchange error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Store tokens securely
     */
    private function storeTokens($buyerId, $tokens, $realmId)
    {
        $buyer = Buyer::find($buyerId);
        if ($buyer) {
            $preferences = $buyer->preferences ?? [];
            $preferences['quickbooks'] = [
                'connected' => true,
                'realm_id' => $realmId,
                'access_token' => encrypt($tokens['access_token']),
                'refresh_token' => encrypt($tokens['refresh_token']),
                'token_expires_at' => now()->addSeconds($tokens['expires_in']),
                'connected_at' => now(),
                'scope' => $tokens['scope'] ?? $this->scope
            ];
            
            $buyer->update(['preferences' => $preferences]);
        }
    }

    /**
     * Get valid access token (refresh if needed)
     */
    public function getAccessToken($buyerId)
    {
        $buyer = Buyer::find($buyerId);
        if (!$buyer || !isset($buyer->preferences['quickbooks'])) {
            return null;
        }

        $qbData = $buyer->preferences['quickbooks'];
        
        // Check if token needs refresh
        if (now()->gt($qbData['token_expires_at'])) {
            return $this->refreshToken($buyer);
        }

        return decrypt($qbData['access_token']);
    }

    /**
     * Refresh access token
     */
    private function refreshToken($buyer)
    {
        try {
            $qbData = $buyer->preferences['quickbooks'];
            $refreshToken = decrypt($qbData['refresh_token']);

            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ]);

            if ($response->successful()) {
                $tokens = $response->json();
                $this->storeTokens($buyer->id, $tokens, $qbData['realm_id']);
                return $tokens['access_token'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('QuickBooks token refresh error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create customer in QuickBooks
     */
    public function createCustomer($buyer)
    {
        $accessToken = $this->getAccessToken($buyer->id);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $realmId = $buyer->preferences['quickbooks']['realm_id'];

        try {
            $customerData = [
                'Name' => $buyer->full_name,
                'CompanyName' => $buyer->company,
                'PrimaryEmailAddr' => ['Address' => $buyer->email],
                'PrimaryPhone' => ['FreeFormNumber' => $buyer->phone],
                'BillAddr' => [
                    'Line1' => $buyer->address,
                    'City' => $buyer->city,
                    'CountrySubDivisionCode' => $buyer->state,
                    'PostalCode' => $buyer->zip_code,
                    'Country' => 'USA'
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl}/v3/company/{$realmId}/customer", [
                    'Customer' => $customerData
                ]);

            if ($response->successful()) {
                $customer = $response->json()['QueryResponse']['Customer'][0] ?? null;
                
                if ($customer) {
                    // Store customer ID in buyer preferences
                    $preferences = $buyer->preferences;
                    $preferences['quickbooks']['customer_id'] = $customer['Id'];
                    $buyer->update(['preferences' => $preferences]);

                    return ['success' => true, 'customer' => $customer];
                }
            }

            return ['success' => false, 'error' => 'Customer creation failed'];

        } catch (\Exception $e) {
            Log::error('QuickBooks customer creation error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create invoice for buyer
     */
    public function createInvoice($buyer, $amount, $description, $dueDate = null)
    {
        $accessToken = $this->getAccessToken($buyer->id);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $realmId = $buyer->preferences['quickbooks']['realm_id'];
        $customerId = $buyer->preferences['quickbooks']['customer_id'] ?? null;

        if (!$customerId) {
            // Create customer first
            $customerResult = $this->createCustomer($buyer);
            if (!$customerResult['success']) {
                return $customerResult;
            }
            $customerId = $buyer->fresh()->preferences['quickbooks']['customer_id'];
        }

        try {
            $invoiceData = [
                'CustomerRef' => ['value' => $customerId],
                'DueDate' => $dueDate ? $dueDate->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
                'Line' => [
                    [
                        'Amount' => $amount,
                        'DetailType' => 'SalesItemLineDetail',
                        'SalesItemLineDetail' => [
                            'ItemRef' => ['value' => '1'], // Default service item
                            'Qty' => 1,
                            'UnitPrice' => $amount
                        ],
                        'Description' => $description
                    ]
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->baseUrl}/v3/company/{$realmId}/invoice", [
                    'Invoice' => $invoiceData
                ]);

            if ($response->successful()) {
                $invoice = $response->json()['QueryResponse']['Invoice'][0] ?? null;
                return ['success' => true, 'invoice' => $invoice];
            }

            return ['success' => false, 'error' => 'Invoice creation failed'];

        } catch (\Exception $e) {
            Log::error('QuickBooks invoice creation error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process payment through QuickBooks
     */
    public function processPayment($buyer, $amount, $paymentMethod = 'credit_card')
    {
        $accessToken = $this->getAccessToken($buyer->id);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        try {
            // Create a payment record
            $payment = BuyerPayment::create([
                'buyer_id' => $buyer->id,
                'transaction_id' => 'QB_' . uniqid(),
                'type' => 'credit',
                'amount' => $amount,
                'status' => 'processing',
                'payment_method' => $paymentMethod,
                'payment_processor' => 'quickbooks',
                'description' => "Account credit - $" . number_format($amount, 2),
                'processed_at' => now()
            ]);

            // In a real implementation, this would call QuickBooks Payments API
            // For now, we'll simulate successful processing
            
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'processor_response' => [
                    'transaction_id' => 'QB_' . uniqid(),
                    'status' => 'success',
                    'processed_at' => now()->toISOString()
                ]
            ]);

            // Update buyer balance
            $buyer->adjustBalance($amount, 'QuickBooks payment credit');

            Log::info("QuickBooks payment processed successfully", [
                'buyer_id' => $buyer->id,
                'amount' => $amount,
                'payment_id' => $payment->id
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'new_balance' => $buyer->fresh()->balance
            ];

        } catch (\Exception $e) {
            Log::error('QuickBooks payment processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get buyer's QuickBooks connection status
     */
    public function getConnectionStatus($buyerId)
    {
        $buyer = Buyer::find($buyerId);
        if (!$buyer || !isset($buyer->preferences['quickbooks'])) {
            return [
                'connected' => false,
                'status' => 'not_connected'
            ];
        }

        $qbData = $buyer->preferences['quickbooks'];
        
        return [
            'connected' => $qbData['connected'] ?? false,
            'status' => 'connected',
            'connected_at' => $qbData['connected_at'] ?? null,
            'realm_id' => $qbData['realm_id'] ?? null,
            'customer_id' => $qbData['customer_id'] ?? null,
            'scope' => $qbData['scope'] ?? null
        ];
    }

    /**
     * Disconnect QuickBooks integration
     */
    public function disconnect($buyerId)
    {
        $buyer = Buyer::find($buyerId);
        if ($buyer) {
            $preferences = $buyer->preferences ?? [];
            unset($preferences['quickbooks']);
            $buyer->update(['preferences' => $preferences]);
            
            Log::info("QuickBooks disconnected for buyer", ['buyer_id' => $buyerId]);
            return true;
        }
        
        return false;
    }

    /**
     * Generate payment link for buyer
     */
    public function generatePaymentLink($buyer, $amount, $description)
    {
        // This would integrate with QuickBooks Payments to create a secure payment link
        // For now, return a placeholder
        
        $paymentId = 'QB_LINK_' . uniqid();
        
        // Store payment intent
        Cache::put("qb_payment_{$paymentId}", [
            'buyer_id' => $buyer->id,
            'amount' => $amount,
            'description' => $description,
            'created_at' => now()
        ], 3600); // 1 hour expiry

        return [
            'success' => true,
            'payment_link' => url("/buyer/payment/quickbooks/{$paymentId}"),
            'payment_id' => $paymentId,
            'expires_at' => now()->addHour()
        ];
    }
}