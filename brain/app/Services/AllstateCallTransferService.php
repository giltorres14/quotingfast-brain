<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AllstateCallTransferService
{
    private $apiKey;
    private $baseUrl;
    
    public function __construct()
    {
        // Production API key from DMS live-transfer
        $this->apiKey = 'b91446ade9d37650f93e305cbaf8c2c9f';
        $this->baseUrl = 'https://api.allstate.com/dms/live-transfer/v1';
    }
    
    /**
     * Transfer a lead to Allstate DMS system
     */
    public function transferCall($lead)
    {
        try {
            Log::info('Starting Allstate transfer', [
                'lead_id' => $lead->id ?? 'unknown',
                'lead_name' => $lead->name ?? 'unknown'
            ]);
            
            // Prepare lead data for Allstate API
            $transferData = $this->prepareLeadData($lead);
            
            // Make API call to Allstate
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/transfer', $transferData);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Allstate transfer successful', [
                    'lead_id' => $lead->id ?? 'unknown',
                    'allstate_response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'allstate_response' => $responseData,
                    'transfer_id' => $responseData['transfer_id'] ?? null,
                    'status' => $responseData['status'] ?? 'transferred'
                ];
            } else {
                Log::error('Allstate API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'lead_id' => $lead->id ?? 'unknown'
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Allstate API returned status: ' . $response->status(),
                    'response_body' => $response->body()
                ];
            }
            
        } catch (Exception $e) {
            Log::error('Allstate transfer exception', [
                'error' => $e->getMessage(),
                'lead_id' => $lead->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Prepare lead data in Allstate's expected format
     */
    private function prepareLeadData($lead)
    {
        // Parse JSON fields if they exist
        $drivers = [];
        $vehicles = [];
        $currentPolicy = [];
        
        if (isset($lead->drivers)) {
            $drivers = is_string($lead->drivers) ? json_decode($lead->drivers, true) : $lead->drivers;
        }
        
        if (isset($lead->vehicles)) {
            $vehicles = is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles;
        }
        
        if (isset($lead->current_policy)) {
            $currentPolicy = is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy;
        }
        
        // Prepare data in Allstate's expected format
        $transferData = [
            'lead_id' => $lead->id ?? uniqid('ALLSTATE_'),
            'source' => 'brain_api',
            'timestamp' => now()->toISOString(),
            'contact' => [
                'first_name' => $lead->first_name ?? '',
                'last_name' => $lead->last_name ?? '',
                'phone' => $this->formatPhoneNumber($lead->phone ?? ''),
                'email' => $lead->email ?? '',
                'address' => [
                    'street' => $lead->address ?? '',
                    'city' => $lead->city ?? '',
                    'state' => $lead->state ?? '',
                    'zip_code' => $lead->zip_code ?? ''
                ]
            ],
            'insurance_info' => [
                'current_carrier' => $currentPolicy['current_insurance'] ?? $lead->insurance_company ?? '',
                'coverage_type' => $currentPolicy['coverage'] ?? $lead->coverage_type ?? 'basic',
                'policy_expiration' => $currentPolicy['expiration_date'] ?? null
            ],
            'drivers' => $this->formatDrivers($drivers),
            'vehicles' => $this->formatVehicles($vehicles),
            'preferences' => [
                'transfer_type' => 'warm_transfer',
                'priority' => 'standard',
                'callback_url' => url('/webhook/allstate/callback')
            ]
        ];
        
        Log::info('Prepared Allstate transfer data', [
            'transfer_data' => $transferData,
            'lead_id' => $lead->id ?? 'unknown'
        ]);
        
        return $transferData;
    }
    
    /**
     * Format phone number for Allstate (10 digits, no formatting)
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // If it starts with 1 and is 11 digits, remove the 1
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
            $cleaned = substr($cleaned, 1);
        }
        
        return $cleaned;
    }
    
    /**
     * Format drivers data for Allstate
     */
    private function formatDrivers($drivers)
    {
        if (empty($drivers) || !is_array($drivers)) {
            return [];
        }
        
        $formatted = [];
        foreach ($drivers as $driver) {
            $formatted[] = [
                'name' => $driver['name'] ?? 'Unknown Driver',
                'age' => $driver['age'] ?? 25,
                'gender' => $driver['gender'] ?? 'Unknown',
                'license_status' => $driver['license_status'] ?? 'Valid',
                'violations' => $driver['violations'] ?? 0,
                'accidents' => $driver['accidents'] ?? []
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format vehicles data for Allstate
     */
    private function formatVehicles($vehicles)
    {
        if (empty($vehicles) || !is_array($vehicles)) {
            return [];
        }
        
        $formatted = [];
        foreach ($vehicles as $vehicle) {
            $formatted[] = [
                'year' => $vehicle['year'] ?? date('Y'),
                'make' => $vehicle['make'] ?? 'Unknown',
                'model' => $vehicle['model'] ?? 'Unknown',
                'usage' => $vehicle['usage'] ?? 'Personal',
                'ownership' => $vehicle['ownership'] ?? 'Own'
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Check transfer status
     */
    public function checkTransferStatus($transferId)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/transfer/' . $transferId);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to check status: ' . $response->status()
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
