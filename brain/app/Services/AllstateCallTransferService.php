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
    private $environment;

    public function __construct()
    {
        // Allstate Lead Marketplace API configuration
        $this->environment = env('ALLSTATE_API_ENV', 'testing'); // Back to testing - only use production when live
        
        if ($this->environment === 'production') {
            // Production credentials (CONFIRMED WORKING from Allstate)
            $this->apiKey = env('ALLSTATE_API_KEY', 'YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6'); // Official production token
            $this->baseUrl = 'https://api.allstateleadmarketplace.com/v2';
        } else {
            // Testing credentials (OFFICIAL from Allstate email - second email correction)
            $this->apiKey = env('ALLSTATE_API_KEY', 'dGVzdHZlbmRvcjo='); // Official: corrected token from second email
            $this->baseUrl = 'https://int.allstateleadmarketplace.com/v2';
        }
    }
    
    /**
     * Transfer a lead to Allstate DMS system
     * @param mixed $lead The lead data
     * @param string $vertical The vertical type (auto-insurance, home-insurance)
     * @param array $qualificationData Agent qualification data from Top 13 Questions
     */
    public function transferCall($lead, $vertical = 'auto-insurance', $qualificationData = [])
    {
        try {
            Log::info('Starting Allstate transfer', [
                'lead_id' => $lead->id ?? 'unknown',
                'lead_name' => $lead->name ?? 'unknown',
                'vertical' => $vertical,
                'vertical_source' => 'enrichment_button_selection'
            ]);
            
            // Prepare and normalize lead data for Allstate API with qualification data
            $transferData = $this->prepareLeadData($lead, $qualificationData);
            
            // Skip old DataNormalizationService - we now have comprehensive formatting
            // that already includes all required Allstate fields in the correct format
            Log::info('Using enhanced comprehensive data formatting (skipping old normalization)', [
                'lead_id' => $lead->id ?? 'unknown',
                'comprehensive_formatting' => true
            ]);
            
            // Make API call to Allstate using correct Basic Auth format from Allstate rep
            $authHeader = 'Basic ' . $this->apiKey;
            
            // Add required vertical parameter to transfer data
            $transferData['vertical'] = $vertical;
            
            // Log the exact payload being sent to Allstate API
            Log::info('Sending to Allstate API', [
                'lead_id' => $lead->id ?? 'unknown',
                'endpoint' => $this->baseUrl . '/ping', // Both testing and production use /ping
                'auth_header' => $authHeader,
                'full_payload' => $transferData,
                'payload_json' => json_encode($transferData, JSON_PRETTY_PRINT)
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => $authHeader
                ])
                ->post($this->baseUrl . '/ping', $transferData); // Both testing and production use /ping endpoint
            
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
     * Prepare lead data in Allstate's expected format with agent qualification data
     */
    private function prepareLeadData($lead, $qualificationData = [])
    {
        // Parse all available data sources
        $drivers = [];
        $vehicles = [];
        $currentPolicy = [];
        $payload = [];
        
        // Parse lead JSON fields
        if (isset($lead->drivers)) {
            $drivers = is_string($lead->drivers) ? json_decode($lead->drivers, true) : $lead->drivers;
        }
        
        if (isset($lead->vehicles)) {
            $vehicles = is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles;
        }
        
        if (isset($lead->current_policy)) {
            $currentPolicy = is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy;
        }
        
        if (isset($lead->payload) && is_string($lead->payload)) {
            $payload = json_decode($lead->payload, true) ?? [];
        }
        
        // Parse qualification data from agent (Top 13 Questions)
        $qualData = $lead->qualification_data ?? $qualificationData;
        if (is_string($qualData)) {
            $qualData = json_decode($qualData, true) ?? [];
        }
        
        Log::info('Preparing Allstate data with all sources', [
            'lead_id' => $lead->id ?? 'unknown',
            'has_drivers' => !empty($drivers),
            'has_vehicles' => !empty($vehicles), 
            'has_qualification' => !empty($qualData),
            'qualification_keys' => array_keys($qualData),
            'raw_drivers' => $drivers,
            'raw_vehicles' => $vehicles
        ]);
        
        // Prepare comprehensive data for Allstate Lead Marketplace API
        $transferData = [
            // Basic Lead Information
            'vertical' => 'auto-insurance',
            'external_id' => $lead->external_lead_id ?? $lead->id ?? uniqid('BRAIN_'),
            'first_name' => $lead->first_name ?? '',
            'last_name' => $lead->last_name ?? '',
            'email' => $lead->email ?? '',
            'phone' => $this->formatPhoneNumber($lead->phone ?? ''),
            'address1' => $lead->address ?? '',
            'city' => $lead->city ?? '',
            'state' => $lead->state ?? 'CA',
            'zipcode' => $lead->zip_code ?? '',
            'country' => 'USA',
            
            // Enhanced Contact Information (required by Allstate)
            'dob' => $this->getBestDateOfBirth($drivers, $qualData, $payload), // API expects 'dob' not 'date_of_birth'
            'gender' => $this->getBestGender($drivers, $qualData, $payload),
            'marital_status' => $this->getBestMaritalStatus($drivers, $qualData, $payload),
            'residence_status' => $this->getHomeOwnership($qualData, $payload),
            
            // Insurance Status (prioritize agent qualification)
            'currently_insured' => $this->getCurrentInsuranceStatus($qualData, $currentPolicy, $payload),
            'current_insurance_company' => $this->getCurrentInsuranceCompany($qualData, $currentPolicy, $payload),
            'policy_expiration_date' => $this->getPolicyExpirationDate($qualData, $currentPolicy),
            'current_premium' => $this->getCurrentPremium($qualData, $currentPolicy),
            
            // Coverage Requirements
            'desired_coverage_type' => $this->getDesiredCoverageType($qualData, $payload),
            'coverage_level' => $this->getCoverageLevel($qualData, $payload),
            'deductible_preference' => $this->getDeductiblePreference($qualData, $payload),
            
            // Financial Information
            'credit_score_range' => $this->getCreditScoreRange($qualData, $payload),
            'home_ownership' => $this->getHomeOwnership($qualData, $payload),
            // Use same values as driver to avoid conflicts
            'education_level' => $this->mapEducationForAllstate($drivers[0]['education'] ?? 'HS'),
            'occupation' => $this->getOccupation($qualData, $payload),
            
            // Driving Information
            'years_licensed' => $this->getYearsLicensed($qualData, $drivers),
            'accidents_violations' => $this->getAccidentsViolations($qualData, $drivers),
            'dui_conviction' => $this->getDUIConviction($qualData, $drivers),
            'sr22_required' => $this->getSR22Required($qualData, $drivers),
            
            // Vehicle & Driver Arrays (comprehensive)
            'drivers' => $this->formatComprehensiveDrivers($drivers, $qualData, $payload),
            'vehicles' => $this->formatComprehensiveVehicles($vehicles, $qualData, $payload),
            
            // Lead Quality & Timing
            'lead_source' => $lead->source ?? 'web',
            'lead_quality_score' => $qualData['lead_quality_score'] ?? $lead->lead_score ?? 5,
            'urgency_level' => $qualData['urgency'] ?? $lead->urgency_level ?? 'standard',
            'best_time_to_call' => $this->getBestTimeToCall($qualData, $payload),
            
            // TCPA & Compliance (required by Allstate)
            'tcpa' => (bool) ($lead->tcpa_compliant ?? true), // API expects 'tcpa' not 'tcpa_compliant'
            'consent_timestamp' => $lead->created_at ?? now(),
            'opt_in_method' => 'web_form',
            
            // Technical Data
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'Brain-API/1.0',
            'referrer_url' => $payload['referrer'] ?? '',
            'landing_page' => $payload['landing_page'] ?? '',
            
            // Agent Qualification Metadata
            'qualified_by_agent' => !empty($qualData),
            'qualification_timestamp' => $lead->qualified_at ?? ($qualData ? now() : null),
            'agent_notes' => $qualData['agent_notes'] ?? '',
            'call_duration' => $qualData['call_duration'] ?? null,
            'motivation_score' => $qualData['motivation_level'] ?? null,
        ];
        
        Log::info('Prepared Allstate transfer data', [
            'lead_id' => $lead->id ?? 'unknown',
            'drivers_count' => count($transferData['drivers'] ?? []),
            'vehicles_count' => count($transferData['vehicles'] ?? []),
            'first_driver' => $transferData['drivers'][0] ?? null,
            'first_vehicle' => $transferData['vehicles'][0] ?? null,
            'main_fields' => [
                'date_of_birth' => $transferData['date_of_birth'] ?? null,
                'residence_status' => $transferData['residence_status'] ?? null,
                'tcpa_compliant' => $transferData['tcpa_compliant'] ?? null
            ]
        ]);
        
        return $transferData;
    }
    
    /**
     * Map coverage type from payload to Allstate format
     */
    private function mapCoverageType($payload)
    {
        $coverageType = 'BASIC'; // Default
        
        if (isset($payload['data']['requested_policy']['coverage_type'])) {
            $type = strtolower($payload['data']['requested_policy']['coverage_type']);
            switch ($type) {
                case 'superior coverage':
                case 'superior':
                    $coverageType = 'SUPERIOR';
                    break;
                case 'standard coverage':
                case 'standard':
                    $coverageType = 'STANDARD';
                    break;
                case 'basic coverage':
                case 'basic':
                    $coverageType = 'BASIC';
                    break;
                case 'state minimum':
                case 'minimum':
                    $coverageType = 'STATEMINIMUM';
                    break;
            }
        }
        
        return $coverageType;
    }
    
    /**
     * Map residence status to Allstate format
     */
    private function mapResidenceStatus($residenceType)
    {
        if (!$residenceType) {
            return 'own'; // Default
        }
        
        $type = strtolower($residenceType);
        switch ($type) {
            case 'own':
                return 'own';
            case 'rent':
                return 'rent';
            case 'live_with_parents':
            case 'parents':
                return 'live_with_parents';
            default:
                return 'own';
        }
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
                                    'usage' => $this->mapVehicleUsageForAllstate($vehicle['usage'] ?? 'pleasure'),
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
     * Get the vertical mapping for enrichment button types
     * @param string $enrichmentType The enrichment button type (insured, homeowner)
     * @return string The corresponding Allstate vertical
     */
    public static function getVerticalFromEnrichment($enrichmentType)
    {
        $mapping = [
            'insured' => 'auto-insurance',
            'homeowner' => 'home-insurance',
            'auto' => 'auto-insurance',
            'home' => 'home-insurance'
        ];
        
        return $mapping[strtolower($enrichmentType)] ?? 'auto-insurance';
    }
    
    // ========================================
    // COMPREHENSIVE DATA EXTRACTION METHODS
    // ========================================
    
    /**
     * Get best date of birth from all available sources
     */
    private function getBestDateOfBirth($drivers, $qualData, $payload)
    {
        // Priority: Agent qualification > Driver data > Payload > Default
        if (!empty($qualData['date_of_birth'])) {
            return \Carbon\Carbon::parse($qualData['date_of_birth'])->format('Y-m-d');
        }
        
        if (!empty($drivers[0]['birth_date'])) {
            return \Carbon\Carbon::parse($drivers[0]['birth_date'])->format('Y-m-d');
        }
        
        if (!empty($drivers[0]['dob'])) {
            return \Carbon\Carbon::parse($drivers[0]['dob'])->format('Y-m-d');
        }
        
        if (!empty($payload['date_of_birth'])) {
            return \Carbon\Carbon::parse($payload['date_of_birth'])->format('Y-m-d');
        }
        
        // Default to reasonable age (35 years old)
        return \Carbon\Carbon::now()->subYears(35)->format('Y-m-d');
    }
    
    /**
     * Get best gender from all available sources
     */
    private function getBestGender($drivers, $qualData, $payload)
    {
        $sources = [
            $qualData['gender'] ?? null,
            $drivers[0]['gender'] ?? null,
            $payload['gender'] ?? null
        ];
        
        foreach ($sources as $gender) {
            if ($gender) {
                return strtoupper(substr(trim($gender), 0, 1));
            }
        }
        
        return 'M'; // Default
    }
    
    /**
     * Get best marital status from all available sources
     */
    private function getBestMaritalStatus($drivers, $qualData, $payload)
    {
        $sources = [
            $qualData['marital_status'] ?? null,
            $drivers[0]['marital_status'] ?? null,
            $payload['marital_status'] ?? null
        ];
        
        foreach ($sources as $status) {
            if ($status) {
                return strtolower(trim($status));
            }
        }
        
        return 'single'; // Default
    }
    
    /**
     * Get current insurance status with agent priority
     */
    private function getCurrentInsuranceStatus($qualData, $currentPolicy, $payload)
    {
        // Agent qualification is most reliable
        if (isset($qualData['currently_insured'])) {
            return (bool) $qualData['currently_insured'];
        }
        
        // Check for current company indicators
        $company = $this->getCurrentInsuranceCompany($qualData, $currentPolicy, $payload);
        if ($company && strtolower($company) !== 'none') {
            return true;
        }
        
        return false; // Default to uninsured
    }
    
    /**
     * Get current insurance company name
     */
    private function getCurrentInsuranceCompany($qualData, $currentPolicy, $payload)
    {
        $sources = [
            $qualData['current_company'] ?? null,
            $qualData['current_insurance_company'] ?? null,
            $currentPolicy['current_insurance'] ?? null,
            $currentPolicy['company'] ?? null,
            $payload['current_insurance_company'] ?? null,
            $payload['insurance_company'] ?? null
        ];
        
        foreach ($sources as $company) {
            if ($company && strtolower($company) !== 'none') {
                return trim($company);
            }
        }
        
        return null;
    }
    
    /**
     * Get policy expiration date
     */
    private function getPolicyExpirationDate($qualData, $currentPolicy)
    {
        $sources = [
            $qualData['policy_expires'] ?? null,
            $qualData['policy_expiration_date'] ?? null,
            $currentPolicy['expiration_date'] ?? null,
            $currentPolicy['expires'] ?? null
        ];
        
        foreach ($sources as $date) {
            if ($date) {
                try {
                    return \Carbon\Carbon::parse($date)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get current premium amount
     */
    private function getCurrentPremium($qualData, $currentPolicy)
    {
        $sources = [
            $qualData['current_premium'] ?? null,
            $qualData['current_monthly_premium'] ?? null,
            $currentPolicy['premium'] ?? null,
            $currentPolicy['monthly_premium'] ?? null
        ];
        
        foreach ($sources as $premium) {
            if ($premium && is_numeric($premium)) {
                return (float) $premium;
            }
        }
        
        return null;
    }
    
    /**
     * Get desired coverage type
     */
    private function getDesiredCoverageType($qualData, $payload)
    {
        // Default to STANDARD (no longer asking agents this question)
        return 'STANDARD';
    }
    
    /**
     * Get coverage level preference
     */
    private function getCoverageLevel($qualData, $payload)
    {
        // Default to STANDARD (no longer asking agents this question)
        return 'STANDARD';
    }
    
    /**
     * Get deductible preference
     */
    private function getDeductiblePreference($qualData, $payload)
    {
        $sources = [
            $qualData['deductible_preference'] ?? null,
            $qualData['preferred_deductible'] ?? null,
            $payload['deductible'] ?? null
        ];
        
        foreach ($sources as $deductible) {
            if ($deductible && is_numeric($deductible)) {
                return (int) $deductible;
            }
        }
        
        return 500; // Default $500 deductible
    }
    
    /**
     * Get credit score range
     */
    private function getCreditScoreRange($qualData, $payload)
    {
        $sources = [
            $qualData['credit_score'] ?? null,
            $qualData['credit_score_range'] ?? null,
            $payload['credit_score'] ?? null
        ];
        
        foreach ($sources as $score) {
            if ($score) {
                if (is_numeric($score)) {
                    // Convert numeric score to range
                    $score = (int) $score;
                    if ($score >= 750) return 'EXCELLENT';
                    if ($score >= 700) return 'GOOD';
                    if ($score >= 650) return 'FAIR';
                    return 'POOR';
                }
                return strtoupper(trim($score));
            }
        }
        
        return 'GOOD'; // Default
    }
    
    /**
     * Get home ownership status
     */
    private function getHomeOwnership($qualData, $payload)
    {
        $sources = [
            $qualData['home_status'] ?? null,
            $qualData['home_ownership'] ?? null,
            $payload['home_ownership'] ?? null,
            $payload['residence_type'] ?? null
        ];
        
        foreach ($sources as $status) {
            if ($status) {
                $status = strtolower(trim($status));
                if (in_array($status, ['own', 'rent', 'live_with_parents'])) {
                    // Map to Allstate expected values
                    return $status === 'own' ? 'home' : $status;
                }
            }
        }
        
        return 'home'; // Default (mapped from 'own' to Allstate expected 'home')
    }
    
    /**
     * Get education level
     */
    private function getEducationLevel($qualData, $payload)
    {
        $sources = [
            $qualData['education'] ?? null,
            $qualData['education_level'] ?? null,
            $payload['education'] ?? null
        ];
        
        foreach ($sources as $education) {
            if ($education) {
                $education = strtoupper(trim($education));
                if (in_array($education, ['HS', 'SOME_COLLEGE', 'BACHELORS', 'MASTERS', 'PHD'])) {
                    return $education;
                }
            }
        }
        
        return 'HS'; // Default
    }
    
    /**
     * Get occupation
     */
    private function getOccupation($qualData, $payload)
    {
        $sources = [
            $qualData['occupation'] ?? null,
            $payload['occupation'] ?? null
        ];
        
        foreach ($sources as $occupation) {
            if ($occupation) {
                return strtoupper(trim($occupation));
            }
        }
        
        return 'OTHER'; // Default
    }
    
    /**
     * Get years licensed
     */
    private function getYearsLicensed($qualData, $drivers)
    {
        $sources = [
            $qualData['years_licensed'] ?? null,
            $qualData['driving_experience'] ?? null,
            $drivers[0]['years_licensed'] ?? null
        ];
        
        foreach ($sources as $years) {
            if ($years && is_numeric($years)) {
                return (int) $years;
            }
        }
        
        return 10; // Default 10 years
    }
    
    /**
     * Get accidents and violations
     */
    private function getAccidentsViolations($qualData, $drivers)
    {
        $sources = [
            $qualData['recent_claims'] ?? null,
            $qualData['accidents_violations'] ?? null,
            $drivers[0]['accidents'] ?? null,
            $drivers[0]['violations'] ?? null
        ];
        
        foreach ($sources as $incidents) {
            if ($incidents !== null) {
                return (bool) $incidents;
            }
        }
        
        return false; // Default no incidents
    }
    
    /**
     * Get DUI conviction status
     */
    private function getDUIConviction($qualData, $drivers)
    {
        $sources = [
            $qualData['dui_conviction'] ?? null,
            $drivers[0]['dui'] ?? null,
            $drivers[0]['dui_conviction'] ?? null
        ];
        
        foreach ($sources as $dui) {
            if ($dui !== null) {
                return (bool) $dui;
            }
        }
        
        return false; // Default no DUI
    }
    
    /**
     * Get SR22 requirement
     */
    private function getSR22Required($qualData, $drivers)
    {
        $sources = [
            $qualData['sr22_required'] ?? null,
            $drivers[0]['sr22_required'] ?? null
        ];
        
        foreach ($sources as $sr22) {
            if ($sr22 !== null) {
                return (bool) $sr22;
            }
        }
        
        return false; // Default no SR22
    }
    
    /**
     * Get best time to call
     */
    private function getBestTimeToCall($qualData, $payload)
    {
        $sources = [
            $qualData['best_time_to_call'] ?? null,
            $qualData['preferred_contact_time'] ?? null,
            $payload['best_time_to_call'] ?? null
        ];
        
        foreach ($sources as $time) {
            if ($time) {
                return trim($time);
            }
        }
        
        return 'anytime'; // Default
    }
    
    /**
     * Format comprehensive driver data for Allstate API
     */
    private function formatComprehensiveDrivers($drivers, $qualData, $payload)
    {
        $formattedDrivers = [];
        
        if (empty($drivers) || !is_array($drivers)) {
            // Create default driver from lead data if no drivers array
            $formattedDrivers[] = [
                'first_name' => $qualData['first_name'] ?? $payload['first_name'] ?? 'Unknown',
                'last_name' => $qualData['last_name'] ?? $payload['last_name'] ?? 'Unknown',
                'date_of_birth' => $this->getBestDateOfBirth($drivers, $qualData, $payload),
                'gender' => $this->getBestGender($drivers, $qualData, $payload),
                'marital_status' => $this->getBestMaritalStatus($drivers, $qualData, $payload),
                'license_status' => 'valid',
                'years_licensed' => $this->getYearsLicensed($qualData, $drivers),
                'education' => $this->getEducationLevel($qualData, $payload),
                'occupation' => $this->getOccupation($qualData, $payload),
                'credit_score' => $this->getCreditScoreRange($qualData, $payload),
                'accidents' => $this->getAccidentsViolations($qualData, $drivers),
                'violations' => $this->getAccidentsViolations($qualData, $drivers),
                'dui_conviction' => $this->getDUIConviction($qualData, $drivers),
                'sr22_required' => $this->getSR22Required($qualData, $drivers),
                'residence_type' => $this->getHomeOwnership($qualData, $payload)
            ];
        } else {
            // Process each driver with comprehensive data in Allstate format
            foreach ($drivers as $index => $driver) {
                $formattedDrivers[] = [
                    'id' => $index + 1, // API expects 'id' not 'driver_number'
                    'first_name' => $driver['first_name'] ?? "Driver" . ($index + 1),
                    'last_name' => $driver['last_name'] ?? 'Unknown',
                    'dob' => $this->formatDriverDateOfBirth($driver), // API expects 'dob' not 'date_of_birth'
                    'gender' => $this->mapGenderForAllstate($driver['gender'] ?? 'M'),
                    'marital_status' => strtolower($driver['marital_status'] ?? 'single'),
                    'relation' => $index === 0 ? 'self' : 'spouse',
                    'valid_license' => true,
                    'years_licensed' => (int) ($driver['years_licensed'] ?? 10),
                    'license_age' => (int) ($driver['license_age'] ?? 16), // Required field - age when first licensed
                    'edu_level' => $this->mapEducationForAllstate($driver['education'] ?? 'HS'),
                    'occupation' => $this->mapOccupationForAllstate($driver['occupation'] ?? 'OTHER'),
                    'years_employed' => (int) ($driver['years_employed'] ?? 5),
                    'years_at_residence' => (int) ($driver['years_at_residence'] ?? 3),
                    'tickets_and_accidents' => (bool) (($driver['accidents_3_years'] ?? $driver['accidents'] ?? 0) + ($driver['violations_3_years'] ?? $driver['violations'] ?? 0)), // Boolean: true if any incidents
                    'dui' => (bool) ($driver['dui_conviction'] ?? $driver['dui'] ?? false),
                    'requires_sr22' => (bool) ($driver['sr22_required'] ?? false), // API expects 'requires_sr22' not 'sr22'
                    'is_primary' => $index === 0 // First driver is primary
                ];
            }
        }
        
        return $formattedDrivers;
    }
    
    /**
     * Format comprehensive vehicle data for Allstate API
     */
    private function formatComprehensiveVehicles($vehicles, $qualData, $payload)
    {
        $formattedVehicles = [];
        
        if (empty($vehicles) || !is_array($vehicles)) {
            // Create default vehicle if none provided
            $formattedVehicles[] = [
                'year' => (int) ($qualData['vehicle_year'] ?? $payload['vehicle_year'] ?? date('Y') - 5),
                'make' => strtoupper($qualData['vehicle_make'] ?? $payload['vehicle_make'] ?? 'HONDA'),
                'model' => strtoupper($qualData['vehicle_model'] ?? $payload['vehicle_model'] ?? 'ACCORD'),
                'trim' => strtoupper($qualData['vehicle_trim'] ?? $payload['vehicle_trim'] ?? 'LX'),
                'vin' => $qualData['vehicle_vin'] ?? $payload['vehicle_vin'] ?? null,
                'ownership' => strtolower($qualData['vehicle_ownership'] ?? $payload['vehicle_ownership'] ?? 'owned'),
                'primary_driver' => 'Primary Driver',
                'annual_mileage' => (int) ($qualData['annual_mileage'] ?? $payload['annual_mileage'] ?? 12000),
                'usage' => strtolower($qualData['vehicle_usage'] ?? $payload['vehicle_usage'] ?? 'commuting'),
                'garage_status' => strtolower($qualData['garage_status'] ?? $payload['garage_status'] ?? 'garaged'),
                'comprehensive_deductible' => (int) ($qualData['comp_deductible'] ?? $payload['comp_deductible'] ?? 500),
                'collision_deductible' => (int) ($qualData['collision_deductible'] ?? $payload['collision_deductible'] ?? 500)
            ];
        } else {
            // Process each vehicle with comprehensive data in Allstate format
            foreach ($vehicles as $index => $vehicle) {
                $formattedVehicles[] = [
                    'id' => $index + 1, // API expects 'id' not 'vehicle_number'
                    'year' => (int) ($vehicle['year'] ?? date('Y') - 5),
                    'make' => strtoupper($vehicle['make'] ?? 'HONDA'),
                    'model' => strtoupper($vehicle['model'] ?? 'ACCORD'),
                    'trim' => strtoupper($vehicle['trim'] ?? 'LX'),
                    'vin' => $vehicle['vin'] ?? null,
                    'drivers' => [$index + 1], // Driver numbers who drive this vehicle
                    'leased' => (strtolower($vehicle['ownership'] ?? 'owned') === 'leased'),
                    'annual_mileage' => (int) ($vehicle['annual_mileage'] ?? 12000),
                    'primary_use' => $this->mapVehicleUsageForAllstate($vehicle['usage'] ?? 'commuting'),
                    'commute_days' => (int) ($vehicle['commute_days'] ?? 5), // Required field - default 5 days/week
                    'commute_mileage' => (int) ($vehicle['commute_mileage'] ?? 20), // Required field - default 20 miles one way
                    'garage_type' => $this->mapGarageTypeForAllstate($vehicle['garage_status'] ?? 'garaged'),
                    'alarm' => false, // Default no alarm system
                    'comprehensive_deductible' => (int) ($vehicle['comprehensive_deductible'] ?? 500),
                    'collision_deductible' => (int) ($vehicle['collision_deductible'] ?? 500),
                    'is_primary' => $index === 0 // First vehicle is primary
                ];
            }
        }
        
        return $formattedVehicles;
    }
    
    /**
     * Format driver date of birth
     */
    private function formatDriverDateOfBirth($driver)
    {
        $dateFields = ['birth_date', 'dob', 'date_of_birth'];
        
        foreach ($dateFields as $field) {
            if (!empty($driver[$field])) {
                try {
                    return \Carbon\Carbon::parse($driver[$field])->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        // Default to reasonable age (35 years old)
        return \Carbon\Carbon::now()->subYears(35)->format('Y-m-d');
    }
    
    /**
     * Format credit score to range
     */
    private function formatCreditScore($score)
    {
        if (!$score || !is_numeric($score)) {
            return 'GOOD';
        }
        
        $score = (int) $score;
        if ($score >= 750) return 'EXCELLENT';
        if ($score >= 700) return 'GOOD';
        if ($score >= 650) return 'FAIR';
        return 'POOR';
    }
    
    /**
     * Map gender to Allstate format (M/F)
     */
    private function mapGenderForAllstate($gender)
    {
        $genderMap = [
            'Male' => 'male',
            'Female' => 'female', 
            'M' => 'male',
            'F' => 'female',
            'male' => 'male',
            'female' => 'female',
            'Man' => 'male',
            'Woman' => 'female',
        ];
        return $genderMap[$gender ?? 'male'] ?? 'male';
    }
    
    /**
     * Map education to Allstate format
     */
    private function mapEducationForAllstate($education)
    {
        // Official Allstate education enums: GED, HS, SCL, ADG, BDG, MDG, DOC
        $educationMap = [
            'High School' => 'HS',
            'Some College' => 'SCL',
            'College' => 'BDG', // Bachelor's Degree
            'Bachelors' => 'BDG', // Bachelor's Degree  
            'Bachelor' => 'BDG',
            'Associates' => 'ADG', // Associate's Degree
            'Masters' => 'MDG', // Master's Degree
            'Master' => 'MDG',
            'Graduate' => 'MDG',
            'Doctorate' => 'DOC',
            'PhD' => 'DOC',
            'GED' => 'GED',
            'HS' => 'HS',
            'SCL' => 'SCL',
            'ADG' => 'ADG',
            'BDG' => 'BDG',
            'MDG' => 'MDG',
            'DOC' => 'DOC',
            // Legacy mappings
            'COLLEGE' => 'BDG',
            'GRADUATE' => 'MDG',
        ];
        return $educationMap[$education ?? 'HS'] ?? 'HS';
    }
    
    /**
     * Map garage type to Allstate format
     */
    private function mapGarageTypeForAllstate($garageStatus)
    {
        $garageMap = [
            'Garaged' => 'garage',
            'Garage' => 'garage', 
            'Driveway' => 'driveway',
            'Street' => 'street',
            'garaged' => 'garage',
            'garage' => 'garage',
            'driveway' => 'driveway',
            'street' => 'street',
        ];
        return $garageMap[$garageStatus ?? 'garage'] ?? 'garage';
    }
    
    /**
     * Map occupation to Allstate approved values with smart fallback to PROFESSIONAL
     */
    private function mapOccupationForAllstate($occupation)
    {
        // Official Allstate occupation values (from documentation)
        $approvedOccupations = ['MARKETING', 'SALES', 'ADMINMGMT', 'RETIRED', 'UNEMPLOYED', 'OTHER', 'TEACHER', 'HEALTHCARE'];
        
        $cleanOccupation = strtoupper(trim($occupation ?? ''));
        
        // Direct match with approved values
        if (in_array($cleanOccupation, $approvedOccupations)) {
            return $cleanOccupation;
        }
        
        // Map to official Allstate occupation codes
        $occupationMap = [
            // Management & Administration
            'MANAGER' => 'ADMINMGMT', 
            'MARKETING MANAGER' => 'MARKETING',
            'CONSTRUCTION MANAGER' => 'ADMINMGMT',
            'PROJECT MANAGER' => 'ADMINMGMT',
            'ACCOUNT MANAGER' => 'ADMINMGMT',
            'SALES MANAGER' => 'SALES',
            'OFFICE MANAGER' => 'ADMINMGMT',
            
            // Direct mappings to Allstate codes
            'MARKETING' => 'MARKETING',
            'SALES' => 'SALES',
            'ADMINMGMT' => 'ADMINMGMT',
            'TEACHER' => 'TEACHER',
            'HEALTHCARE' => 'HEALTHCARE',
            'RETIRED' => 'RETIRED',
            'UNEMPLOYED' => 'UNEMPLOYED',
            'OTHER' => 'OTHER',
            
            // Common mappings
            'STUDENT' => 'STUDENTNOTLIVINGWITHPARENTS',
            'COLLEGE STUDENT' => 'STUDENTNOTLIVINGWITHPARENTS',
            'ENGINEER' => 'ENGINEEROTHER',
            'DOCTOR' => 'PHYSICIAN',
            'LAWYER' => 'LAWYER',
            'NURSE' => 'NURSECNA',
            'ACCOUNTANT' => 'CPA',
            'CONSULTANT' => 'OTHER',
            'ANALYST' => 'OTHER',
            'DEVELOPER' => 'PROGRAMMER',
            'PROGRAMMER' => 'PROGRAMMER',
            'DESIGNER' => 'OTHER',
            'ARCHITECT' => 'ARCHITECT',
        ];
        
        // Check if we have a specific mapping
        if (isset($occupationMap[$cleanOccupation])) {
            return $occupationMap[$cleanOccupation];
        }
        
        // Default to OTHER for any unrecognized occupation
        return 'OTHER';
    }
    
    /**
     * Map vehicle usage to Allstate approved values with smart logic
     */
    private function mapVehicleUsageForAllstate($usage)
    {
        // Official Allstate values: pleasure, business, commutework, selfemployed, school, farm, gov, other
        $cleanUsage = strtolower(trim($usage ?? ''));
        
        // Direct matches first
        $approvedUsages = ['pleasure', 'business', 'commutework', 'selfemployed', 'school', 'farm', 'gov', 'other'];
        if (in_array($cleanUsage, $approvedUsages)) {
            return $cleanUsage;
        }
        
        // Smart mapping with logic for variations
        $usageMap = [
            // Commute variations → commutework
            'commute' => 'commutework',
            'commuting' => 'commutework',  // commuting maps to commutework
            'commute to work' => 'commutework',
            'work' => 'commutework',
            'to work' => 'commutework',
            'daily commute' => 'commutework',
            
            // Pleasure/Personal variations  
            'pleasure' => 'pleasure',
            'personal' => 'pleasure',
            'personal use' => 'pleasure',
            'recreational' => 'pleasure',
            'leisure' => 'pleasure',
            'family' => 'pleasure',
            'errands' => 'pleasure',
            
            // Business variations
            'business' => 'business',
            'work related' => 'business',
            'business use' => 'business',
            'company' => 'business',
            
            // Commercial variations
            'commercial' => 'commercial',
            'delivery' => 'commercial',
            'rideshare' => 'commercial',
            'uber' => 'commercial',
            'lyft' => 'commercial',
            'taxi' => 'commercial',
        ];
        
        // Check for mapping
        if (isset($usageMap[$cleanUsage])) {
            return $usageMap[$cleanUsage];
        }
        
        // Default to pleasure for any unrecognized usage
        return 'pleasure';
    }
}
