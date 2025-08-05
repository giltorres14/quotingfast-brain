<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\DataNormalizationService;

class AllstateCallTransferService
{
    private $apiKey;
    private $baseUrl;
    
    public function __construct()
    {
        // Allstate Lead Marketplace API key - updated with correct tokens from Allstate
        $this->apiKey = env('ALLSTATE_API_KEY', 'dGVzdHZlbmRvcjo='); // Testing token from Allstate
        // Use testing environment first, then switch to production
        $this->baseUrl = env('ALLSTATE_API_ENV', 'testing') === 'production' 
            ? 'https://api.allstateleadmarketplace.com/v2'
            : 'https://int.allstateleadmarketplace.com/v2';
    }
    
    /**
     * Transfer a lead to Allstate DMS system
     * @param mixed $lead The lead data
     * @param string|null $vertical Override vertical detection (auto-insurance, home-insurance)
     */
    public function transferCall($lead, $vertical = null)
    {
        try {
            // Detect or use provided vertical
            $detectedVertical = $vertical ?? $this->detectVertical($lead);
            
            Log::info('Starting Allstate transfer', [
                'lead_id' => $lead->id ?? 'unknown',
                'lead_name' => $lead->name ?? 'unknown',
                'vertical' => $detectedVertical,
                'vertical_source' => $vertical ? 'explicit' : 'auto-detected'
            ]);
            
            // Prepare and normalize lead data for Allstate API
            $transferData = $this->prepareLeadData($lead);
            
            // Apply Allstate-specific data normalization
            $originalData = $transferData;
            $transferData = DataNormalizationService::normalizeForBuyer($transferData, 'allstate');
            
            // Log normalization changes for debugging
            $validationReport = DataNormalizationService::getValidationReport($originalData, $transferData, 'allstate');
            if (!empty($validationReport['changes_made'])) {
                Log::info('Data normalized for Allstate transfer', [
                    'lead_id' => $lead->id ?? 'unknown',
                    'changes' => $validationReport['changes_made']
                ]);
            }
            
            // Make API call to Allstate using correct Basic Auth format from Allstate rep
            $authHeader = 'Basic ' . $this->apiKey;
            
            // Add required vertical parameter to transfer data
            $transferData['vertical'] = $detectedVertical;
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => $authHeader
                ])
                ->post($this->baseUrl . '/leads', $transferData); // Submit lead to Allstate Lead Marketplace
            
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
        
        // Prepare data in Allstate Lead Marketplace API format
        $transferData = [
            'vertical' => 'auto-insurance',
            'external_id' => $lead->id ?? uniqid('BRAIN_'),
            'first_name' => $lead->first_name ?? '',
            'last_name' => $lead->last_name ?? '',
            'email' => $lead->email ?? '',
            'home_phone' => $this->formatPhoneNumber($lead->phone ?? ''),
            'address1' => $lead->address ?? '',
            'city' => $lead->city ?? '',
            'state' => $lead->state ?? 'CA', // Default to CA for testing
            'zipcode' => $lead->zip_code ?? '',
            'country' => 'USA',
            'dob' => $lead->birth_date ?? '1990-01-01', // Default DOB if not provided
            'tcpa' => true, // Assuming TCPA consent
            'current_insurance_company' => strtolower($currentPolicy['current_insurance'] ?? $lead->insurance_company ?? 'other'),
            'desired_coverage_type' => strtoupper($lead->coverage_type ?? 'BASIC'),
            'currently_insured' => !empty($currentPolicy['current_insurance'] ?? $lead->insurance_company),
            'drivers' => $this->formatDriversForAllstate($drivers),
            'vehicles' => $this->formatVehiclesForAllstate($vehicles),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'Brain-API/1.0'
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
     * Format drivers data for Allstate Lead Marketplace API
     */
    private function formatDriversForAllstate($drivers)
    {
        if (empty($drivers) || !is_array($drivers)) {
            // Return default driver if none provided
            return [[
                'first_name' => 'Unknown',
                'last_name' => 'Driver',
                'dob' => '1990-01-01',
                'gender' => 'M',
                'marital_status' => 'single',
                'education' => 'HS',
                'occupation' => 'OTHER',
                'sr22_required' => false,
                'good_student_discount' => false,
                'defensive_driving_course' => false
            ]];
        }
        
        $formatted = [];
        foreach ($drivers as $driver) {
            $formatted[] = [
                'first_name' => $driver['first_name'] ?? explode(' ', $driver['name'] ?? 'Unknown Driver')[0],
                'last_name' => $driver['last_name'] ?? explode(' ', $driver['name'] ?? 'Unknown Driver')[1] ?? 'Driver',
                'dob' => $driver['dob'] ?? '1990-01-01',
                'gender' => strtoupper(substr($driver['gender'] ?? 'M', 0, 1)),
                'marital_status' => strtolower($driver['marital_status'] ?? 'single'),
                'education' => strtoupper($driver['education'] ?? 'HS'),
                'occupation' => strtoupper($driver['occupation'] ?? 'OTHER'),
                'sr22_required' => $driver['sr22_required'] ?? false,
                'good_student_discount' => $driver['good_student_discount'] ?? false,
                'defensive_driving_course' => $driver['defensive_driving_course'] ?? false
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format vehicles data for Allstate Lead Marketplace API
     */
    private function formatVehiclesForAllstate($vehicles)
    {
        if (empty($vehicles) || !is_array($vehicles)) {
            // Return default vehicle if none provided
            return [[
                'year' => (int)date('Y') - 5, // 5 year old car as default
                'make' => 'Toyota',
                'model' => 'Camry',
                'vin' => '',
                'usage' => 'pleasure',
                'ownership' => 'owned',
                'annual_mileage' => 12000,
                'garage' => false
            ]];
        }
        
        $formatted = [];
        foreach ($vehicles as $vehicle) {
            $formatted[] = [
                'year' => (int)($vehicle['year'] ?? date('Y') - 5),
                'make' => $vehicle['make'] ?? 'Toyota',
                'model' => $vehicle['model'] ?? 'Camry',
                'vin' => $vehicle['vin'] ?? '',
                'usage' => strtolower($vehicle['usage'] ?? 'pleasure'),
                'ownership' => strtolower($vehicle['ownership'] ?? 'owned'),
                'annual_mileage' => (int)($vehicle['annual_mileage'] ?? 12000),
                'garage' => $vehicle['garage'] ?? false
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
    
    /**
     * Automatically detect the vertical based on lead data
     * @param mixed $lead The lead data
     * @return string The detected vertical (auto-insurance, home-insurance)
     */
    private function detectVertical($lead)
    {
        // Convert to array if it's an object
        $leadData = is_object($lead) ? (array) $lead : $lead;
        
        // Check for home insurance indicators
        if ($this->hasHomeInsuranceFields($leadData)) {
            Log::info('Detected home insurance vertical', [
                'lead_id' => $lead->id ?? 'unknown',
                'indicators' => $this->getHomeInsuranceIndicators($leadData)
            ]);
            return 'home-insurance';
        }
        
        // Check for auto insurance indicators (more specific check)
        if ($this->hasAutoInsuranceFields($leadData)) {
            Log::info('Detected auto insurance vertical', [
                'lead_id' => $lead->id ?? 'unknown',
                'indicators' => $this->getAutoInsuranceIndicators($leadData)
            ]);
            return 'auto-insurance';
        }
        
        // Default to auto insurance
        Log::info('Defaulting to auto insurance vertical', [
            'lead_id' => $lead->id ?? 'unknown',
            'reason' => 'No specific indicators found'
        ]);
        return 'auto-insurance';
    }
    
    /**
     * Check if lead has home insurance specific fields
     */
    private function hasHomeInsuranceFields($leadData)
    {
        $homeFields = [
            'home_value', 'property_value', 'dwelling_value',
            'property_type', 'dwelling_type', 'home_type',
            'square_footage', 'year_built', 'construction_type',
            'roof_type', 'foundation_type', 'heating_type',
            'home_ownership', 'mortgage_company'
        ];
        
        foreach ($homeFields as $field) {
            if (isset($leadData[$field]) && !empty($leadData[$field])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if lead has auto insurance specific fields
     */
    private function hasAutoInsuranceFields($leadData)
    {
        $autoFields = [
            'vehicles', 'drivers', 'current_insurance_company',
            'policy_expiration', 'desired_coverage_type',
            'currently_insured', 'vehicle_year', 'vehicle_make',
            'vehicle_model', 'annual_mileage', 'primary_use'
        ];
        
        foreach ($autoFields as $field) {
            if (isset($leadData[$field]) && !empty($leadData[$field])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get home insurance indicators for logging
     */
    private function getHomeInsuranceIndicators($leadData)
    {
        $indicators = [];
        $homeFields = ['home_value', 'property_type', 'square_footage', 'year_built'];
        
        foreach ($homeFields as $field) {
            if (isset($leadData[$field]) && !empty($leadData[$field])) {
                $indicators[] = $field;
            }
        }
        
        return $indicators;
    }
    
    /**
     * Get auto insurance indicators for logging
     */
    private function getAutoInsuranceIndicators($leadData)
    {
        $indicators = [];
        $autoFields = ['vehicles', 'drivers', 'current_insurance_company', 'vehicle_year'];
        
        foreach ($autoFields as $field) {
            if (isset($leadData[$field]) && !empty($leadData[$field])) {
                $indicators[] = $field;
            }
        }
        
        return $indicators;
    }
}
