<?php

namespace App\Services;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice as QbInvoice;
use QuickBooksOnline\API\Facades\Payment as QbPayment;
use Illuminate\Support\Facades\Log;

class QuickBooksService
{
    protected $dataService;

    public function __construct()
    {
        try {
            $this->dataService = DataService::Configure([
                'auth_mode' => 'oauth2',
                'ClientID' => env('QB_CLIENT_ID'),
                'ClientSecret' => env('QB_CLIENT_SECRET'),
                'accessTokenKey' => env('QB_ACCESS_TOKEN'),
                'refreshTokenKey' => env('QB_REFRESH_TOKEN'),
                'QBORealmID' => env('QB_REALM_ID'),
                'baseUrl' => env('QB_BASE_URL', 'Development'),
            ]);
            $this->dataService->setLogLocation(storage_path('logs/quickbooks.log'));
        } catch (\Exception $e) {
            Log::warning('QuickBooksService initialization failed: ' . $e->getMessage());
            $this->dataService = null;
        }
    }

    public function createInvoice(\App\Models\Invoice $invoice)
    {
        if (! $this->dataService) {
            return null;
        }
        try {
            $qboInvoice = QbInvoice::create([
                "CustomerRef" => ["value" => (string) $invoice->client_id],
                "TotalAmt" => $invoice->amount,
                "Line" => [] // TODO: add line items if needed
            ]);
            return $this->dataService->Add($qboInvoice);
        } catch (\Exception $e) {
            Log::error('QuickBooks invoice sync failed: ' . $e->getMessage());
            return null;
        }
    }

    public function createPayment(\App\Models\Payment $payment)
    {
        if (! $this->dataService) {
            return null;
        }
        try {
            $qboPayment = QbPayment::create([
                "CustomerRef" => ["value" => (string) $payment->invoice->client_id],
                "TotalAmt" => $payment->amount,
                "Line" => [
                    [
                        "Amount" => $payment->amount,
                        "LinkedTxn" => [
                            [
                                "TxnId" => (string) $payment->invoice_id,
                                "TxnType" => "Invoice"
                            ]
                        ]
                    ]
                ]
            ]);
            return $this->dataService->Add($qboPayment);
        } catch (\Exception $e) {
            Log::error('QuickBooks payment sync failed: ' . $e->getMessage());
            return null;
        }
    }
} 