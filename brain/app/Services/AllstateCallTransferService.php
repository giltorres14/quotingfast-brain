<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AllstateCallTransferService
{
    private string $baseUrl;
    private string $apiKey;
    private bool $isTestMode;

    public function __construct()
    {
        $this->isTestMode = config('services.allstate.test_mode', config('app.env') !== 'production');
        $this->baseUrl = $this->isTestMode 
            ? config('services.allstate.test_url')
            : config('services.allstate.production_url');
        
        $this->apiKey = config('services.allstate.api_key');
    }

    /**
     * Transfer a call/lead to Allstate using Direct Post method
     * Based on: https://docs.allstateleadmarketplace.com/#call-transfers
     */
    public function transferCall(Lead $lead): array
    {
        try {
            Log::info('Allstate Call Transfer: Starting transfer', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'test_mode' => $this->isTestMode
            ]);

            // Map our Lead data to Allstate's required format
            $payload = $this->mapLeadToAllstateFormat($lead);

            // Make the API call to Allstate
            $response = $this->makeApiCall('/post', $payload);

            // Log the response
            Log::info('Allstate Call Transfer: Response received', [
                'lead_id' => $lead->id,
                'response' => $response
            ]);

            // Update lead status based on response
            $this->updateLeadStatus($lead, $response);

            return [
                'success' => $response['success'] ?? false,
                'allstate_response' => $response,
                'transferred_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Allstate Call Transfer: Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'failed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Map our Lead model data to Allstate's required format
     * Based on their Auto Insurance Lead Object specification
     */
    private function mapLeadToAllstateFormat(Lead $lead): array
    {
        // Extract driver and vehicle info from real LQF structure
        $drivers = is_array($lead->drivers) ? $lead->drivers : [];
        $vehicles = is_array($lead->vehicles) ? $lead->vehicles : [];
        $currentPolicy = is_array($lead->current_policy) ? $lead->current_policy : [];
        $requestedPolicy = is_array($lead->requested_policy) ? $lead->requested_policy : [];
        $primaryDriver = $drivers[0] ?? [];

        // Calculate years at address from months_at_residence
        $yearsAtAddress = isset($primaryDriver['months_at_residence']) 
            ? max(1, round($primaryDriver['months_at_residence'] / 12, 1))
            : 2;

        return [
            // Required fields
            'vertical' => 'auto-insurance',
            'external_id' => $lead->external_lead_id ?? $lead->id,
            
            // Location information (from contact data)
            'city' => $lead->city ?? 'Unknown',
            'state' => $lead->state ?? 'CA',
            'zipcode' => $lead->zip_code ?? '90210',
            'country' => 'USA',
            
            // Personal information (from contact data)
            'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
            'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
            'email' => $lead->email ?? '',
            'home_phone' => $this->formatPhoneNumber($lead->phone ?? ''),
            'dob' => $primaryDriver['birth_date'] ?? '1990-01-01',
            
            // Address information
            'address1' => $lead->address ?? 'Address not provided',
            'years_at_address' => (string)$yearsAtAddress,
            'residence_status' => $this->mapResidenceStatus($primaryDriver['residence_type'] ?? 'own'),
            
            // Insurance information (from current_policy)
            'currently_insured' => !empty($currentPolicy),
            'current_insurance_company' => $this->mapInsuranceCompany($currentPolicy['insurance_company'] ?? 'other'),
            'desired_coverage_type' => $this->mapCoverageType($requestedPolicy['coverage_type'] ?? 'BASIC'),
            'policy_start' => $currentPolicy['insured_since'] ?? now()->subYears(2)->format('Y-m-d'),
            'policy_expiration' => $currentPolicy['expiration_date'] ?? now()->addMonths(6)->format('Y-m-d'),
            
            // Contact preferences
            'best_contact_time' => '18:00', // 6 PM default
            'tcpa' => $lead->tcpa_compliant ?? true,
            
            // Technical information
            'ip_address' => $lead->ip_address ?? request()->ip(),
            'user_agent' => $lead->user_agent ?? request()->userAgent(),
            
            // Lead quality indicators
            'exclusive' => true, // LQF leads are typically exclusive
            'self_credit_rating' => $this->mapCreditRating($primaryDriver),
            
            // Driver information (mapped from real LQF structure)
            'drivers' => $this->mapDrivers($drivers, $lead),
            
            // Vehicle information (mapped from real LQF structure)
            'vehicles' => $this->mapVehicles($vehicles, $lead),
            
            // Additional metadata
            'bundle_insurance' => true,
            'insured_since' => $currentPolicy['insured_since'] ?? '2020-01-01',
            
            // LQF specific data
            'lead_source' => 'leadsquotingfast',
            'campaign_id' => $lead->campaign_id,
            'sell_price' => $lead->sell_price,
        ];
    }

    /**
     * Map drivers array to Allstate format
     */
    private function mapDrivers(array $drivers, Lead $lead): array
    {
        if (empty($drivers)) {
            // Create primary driver from lead data
            return [[
                'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
                'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
                'dob' => $lead->birth_date ? $lead->birth_date->format('Y-m-d') : '1990-01-01',
                'gender' => $this->mapGender($lead->gender ?? 'M'),
                'marital_status' => $this->mapMaritalStatus($lead->marital_status ?? 'single'),
                'education' => $this->mapEducation($lead->education ?? 'HS'),
                'occupation' => $this->mapOccupation($lead->occupation ?? 'OTHER'),
                'license_state' => $lead->license_state ?? $lead->state ?? 'CA',
                'license_status' => $this->mapLicenseStatus($lead->license_status ?? 'valid'),
                'age_licensed' => $lead->age_licensed ?? 18,
                'sr22_required' => $lead->sr22_required ?? false,
                'bankruptcy' => $lead->bankruptcy ?? false,
                'license_suspended' => $lead->license_suspended ?? false,
            ]];
        }

        return array_map(function($driver) {
            // Count violations, accidents, and claims for risk assessment
            $violations = $driver['major_violations'] ?? [];
            $accidents = $driver['accidents'] ?? [];
            $claims = $driver['claims'] ?? [];
            $tickets = $driver['tickets'] ?? [];

            return [
                'first_name' => $driver['first_name'] ?? 'Unknown',
                'last_name' => $driver['last_name'] ?? 'Driver',
                'dob' => $driver['birth_date'] ?? '1990-01-01',
                'gender' => $this->mapGender($driver['gender'] ?? 'M'),
                'marital_status' => $this->mapMaritalStatus($driver['marital_status'] ?? 'single'),
                'education' => $this->mapEducation($driver['education'] ?? 'HS'),
                'occupation' => $this->mapOccupation($driver['occupation'] ?? 'OTHER'),
                'license_state' => $driver['license_state'] ?? 'CA',
                'license_status' => $this->mapLicenseStatus($driver['license_status'] ?? 'valid'),
                'age_licensed' => $driver['age_licensed'] ?? 18,
                'sr22_required' => $driver['requires_sr22'] ?? false,
                'bankruptcy' => $driver['bankruptcy'] ?? false,
                'license_suspended' => $driver['license_ever_suspended'] ?? false,
                
                // Additional LQF-specific driver data
                'months_at_employer' => $driver['months_at_employer'] ?? 12,
                'months_at_residence' => $driver['months_at_residence'] ?? 24,
                'residence_type' => $driver['residence_type'] ?? 'own',
                
                // Risk indicators from LQF data
                'violation_count' => count($violations),
                'accident_count' => count($accidents),
                'claim_count' => count($claims),
                'ticket_count' => count($tickets),
                'has_major_violations' => !empty($violations),
                'has_at_fault_accidents' => $this->hasAtFaultAccidents($accidents),
                
                // Most recent violation/accident for underwriting
                'most_recent_violation' => $this->getMostRecentViolation($violations),
                'most_recent_accident' => $this->getMostRecentAccident($accidents),
            ];
        }, $drivers);
    }

    /**
     * Map vehicles array to Allstate format
     */
    private function mapVehicles(array $vehicles, Lead $lead): array
    {
        if (empty($vehicles)) {
            // Create primary vehicle from lead data
            return [[
                'year' => $lead->vehicle_year ?? 2020,
                'make' => $lead->vehicle_make ?? 'Toyota',
                'model' => $lead->vehicle_model ?? 'Camry',
                'vin' => $lead->vin ?? '',
                'ownership' => 'own',
                'primary_use' => 'commute',
                'annual_miles' => 12000,
                'garage_status' => 'garage',
            ]];
        }

        return array_map(function($vehicle) {
            return [
                'year' => $vehicle['year'] ?? 2020,
                'make' => $vehicle['make'] ?? 'Toyota',
                'model' => $vehicle['model'] ?? 'Camry',
                'submodel' => $vehicle['submodel'] ?? '',
                'vin' => $vehicle['vin'] ?? '',
                'ownership' => $this->mapOwnership($vehicle['ownership'] ?? 'own'),
                'primary_use' => $this->mapPrimaryUse($vehicle['primary_use'] ?? 'commute'),
                'annual_miles' => $vehicle['annual_miles'] ?? 12000,
                'garage_status' => $this->mapGarageStatus($vehicle['garage'] ?? 'garage'),
                
                // Additional LQF vehicle data
                'one_way_distance' => $vehicle['one_way_distance'] ?? 10,
                'weekly_commute_days' => $vehicle['weekly_commute_days'] ?? 5,
                'collision_deductible' => $vehicle['collision_deductible'] ?? '500',
                'comprehensive_deductible' => $vehicle['comprehensive_deductible'] ?? '500',
                
                // Safety features from LQF
                'airbags' => $vehicle['airbags'] ?? true,
                'abs' => $vehicle['abs'] ?? true,
                'automatic_seat_belts' => $vehicle['automatic_seat_belts'] ?? false,
                'four_wheel_drive' => $vehicle['four_wheel_drive'] ?? false,
                'alarm' => $vehicle['alarm'] ?? 'none',
                
                // Vehicle condition indicators  
                'salvaged' => $vehicle['salvaged'] ?? false,
                'rental' => $vehicle['rental'] ?? false,
                'towing' => $vehicle['towing'] ?? false,
            ];
        }, $vehicles);
    }

    /**
     * Make HTTP API call to Allstate
     */
    private function makeApiCall(string $endpoint, array $payload): array
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->baseUrl . $endpoint, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'data' => $data,
                'status_code' => $response->status(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
            'status_code' => $response->status(),
        ];
    }

    /**
     * Update lead status based on Allstate response
     */
    private function updateLeadStatus(Lead $lead, array $response): void
    {
        if ($response['success']) {
            $lead->update([
                'status' => 'transferred_to_allstate',
                'notes' => ($lead->notes ?? '') . "\n" . 'Transferred to Allstate at ' . now()->toDateTimeString(),
            ]);
        } else {
            $lead->update([
                'status' => 'transfer_failed',
                'notes' => ($lead->notes ?? '') . "\n" . 'Allstate transfer failed: ' . ($response['error'] ?? 'Unknown error'),
            ]);
        }
    }

    // Helper methods for data mapping
    private function extractFirstName(string $fullName): string
    {
        return explode(' ', trim($fullName))[0] ?? 'Unknown';
    }

    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? end($parts) : 'Unknown';
    }

    private function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) === 10) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6);
        }
        return $phone;
    }

    private function mapResidenceStatus(string $type): string
    {
        $mapping = [
            'own' => 'home',
            'rent' => 'rent',
            'other' => 'other',
        ];
        return $mapping[strtolower($type)] ?? 'home';
    }

    private function mapInsuranceCompany(string $company): string
    {
        $mapping = [
            'allstate' => 'allstate',
            'geico' => 'geico',
            'state_farm' => 'state_farm',
            'progressive' => 'progressive',
            'other' => 'other',
        ];
        return $mapping[strtolower($company)] ?? 'other';
    }

    private function mapCoverageType(string $type): string
    {
        $mapping = [
            'basic' => 'BASIC',
            'standard' => 'STANDARD',
            'superior' => 'SUPERIOR',
            'state_minimum' => 'STATEMINIMUM',
        ];
        return $mapping[strtolower($type)] ?? 'BASIC';
    }

    private function mapGender(string $gender): string
    {
        return strtoupper(substr($gender, 0, 1)) === 'F' ? 'F' : 'M';
    }

    private function mapMaritalStatus(string $status): string
    {
        $mapping = [
            'single' => 'single',
            'married' => 'married',
            'divorced' => 'divorced',
            'widowed' => 'widowed',
        ];
        return $mapping[strtolower($status)] ?? 'single';
    }

    private function mapEducation(string $education): string
    {
        $mapping = [
            'ged' => 'GED',
            'high_school' => 'HS',
            'some_college' => 'SCL',
            'associates' => 'ADG',
            'bachelors' => 'BDG',
            'masters' => 'MDG',
            'doctorate' => 'DOC',
        ];
        return $mapping[strtolower($education)] ?? 'HS';
    }

    private function mapOccupation(string $occupation): string
    {
        // Using some common mappings - full list available in Allstate docs
        $mapping = [
            'teacher' => 'TEACHER',
            'nurse' => 'NURSECNA',
            'engineer' => 'ENGINEEROTHER',
            'manager' => 'ADMINMGMT',
            'sales' => 'SALES',
            'retired' => 'RETIRED',
            'student' => 'STUDENTNOTLIVINGWITHPARENTS',
            'unemployed' => 'UNEMPLOYED',
        ];
        return $mapping[strtolower($occupation)] ?? 'OTHER';
    }

    private function mapLicenseStatus(string $status): string
    {
        $mapping = [
            'active' => 'valid',
            'valid' => 'valid',
            'suspended' => 'suspended',
            'expired' => 'expired',
            'permit' => 'permit',
            'temporary' => 'permit',
            'international' => 'valid', // Treat international as valid
        ];
        return $mapping[strtolower($status)] ?? 'valid';
    }

    /**
     * Map credit rating based on driver profile
     */
    private function mapCreditRating(array $driver): string
    {
        // Simple risk assessment based on driver history
        $riskFactors = 0;
        
        if ($driver['bankruptcy'] ?? false) $riskFactors += 2;
        if ($driver['license_ever_suspended'] ?? false) $riskFactors += 1;
        if (count($driver['major_violations'] ?? []) > 0) $riskFactors += 1;
        if (count($driver['accidents'] ?? []) > 1) $riskFactors += 1;
        
        if ($riskFactors >= 3) return 'poor';
        if ($riskFactors >= 1) return 'fair';
        return 'good';
    }

    /**
     * Check if driver has at-fault accidents
     */
    private function hasAtFaultAccidents(array $accidents): bool
    {
        foreach ($accidents as $accident) {
            if ($accident['at_fault'] ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get most recent violation for underwriting
     */
    private function getMostRecentViolation(array $violations): ?string
    {
        if (empty($violations)) return null;
        
        usort($violations, function($a, $b) {
            return strtotime($b['violation_date'] ?? '1900-01-01') - strtotime($a['violation_date'] ?? '1900-01-01');
        });
        
        return $violations[0]['description'] ?? null;
    }

    /**
     * Get most recent accident for underwriting
     */
    private function getMostRecentAccident(array $accidents): ?string
    {
        if (empty($accidents)) return null;
        
        usort($accidents, function($a, $b) {
            return strtotime($b['accident_date'] ?? '1900-01-01') - strtotime($a['accident_date'] ?? '1900-01-01');
        });
        
        return $accidents[0]['description'] ?? null;
    }

    /**
     * Map vehicle ownership
     */
    private function mapOwnership(string $ownership): string
    {
        $mapping = [
            'own' => 'own',
            'lease' => 'lease',
            'finance' => 'finance',
            'financed' => 'finance',
        ];
        return $mapping[strtolower($ownership)] ?? 'own';
    }

    /**
     * Map primary vehicle use
     */
    private function mapPrimaryUse(string $use): string
    {
        $mapping = [
            'commute work' => 'commute',
            'commute school' => 'commute',
            'pleasure' => 'pleasure',
            'business' => 'business',
        ];
        return $mapping[strtolower($use)] ?? 'commute';
    }

    /**
     * Map garage status
     */
    private function mapGarageStatus(string $garage): string
    {
        $mapping = [
            'full garage' => 'garage',
            'carport' => 'carport',
            'on street' => 'street',
            'no cover' => 'street',
        ];
        return $mapping[strtolower($garage)] ?? 'garage';
    }
}