<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AllstateValidationService
{
    /**
     * Get all required fields for Allstate enrichment
     */
    public static function getRequiredFields()
    {
        return [
            // Main lead fields (required)
            'lead' => [
                'first_name' => 'First Name',
                'last_name' => 'Last Name', 
                'email' => 'Email Address',
                'phone' => 'Phone Number',
                'address' => 'Street Address',
                'city' => 'City',
                'state' => 'State',
                'zip_code' => 'ZIP Code',
                'birth_date' => 'Date of Birth'
            ],
            
            // Insurance status (CRITICAL - Allstate only accepts insured)
            'insurance' => [
                'currently_insured' => 'Currently Insured',
                'current_insurance' => 'Current Insurance Company'
            ],
            
            // At least one driver required
            'drivers' => [
                'minimum_count' => 1,
                'fields' => [
                    'first_name' => 'Driver First Name',
                    'last_name' => 'Driver Last Name',
                    'dob' => 'Driver Date of Birth',
                    'gender' => 'Driver Gender',
                    'marital_status' => 'Driver Marital Status',
                    'license_status' => 'Driver License Status'
                ]
            ],
            
            // At least one vehicle required
            'vehicles' => [
                'minimum_count' => 1,
                'fields' => [
                    'year' => 'Vehicle Year',
                    'make' => 'Vehicle Make',
                    'model' => 'Vehicle Model',
                    'primary_use' => 'Vehicle Primary Use',
                    'ownership' => 'Vehicle Ownership'
                ]
            ]
        ];
    }
    
    /**
     * Validate lead data for Allstate enrichment
     */
    public static function validateLeadForEnrichment($lead)
    {
        $validation = [
            'is_valid' => true,
            'missing_fields' => [],
            'errors' => [],
            'warnings' => []
        ];
        
        $requiredFields = self::getRequiredFields();
        
        // 1. CRITICAL: Check if currently insured (Allstate requirement)
        $currentPolicy = is_string($lead->current_policy ?? '') 
            ? json_decode($lead->current_policy, true) 
            : ($lead->current_policy ?? []);
            
        $currentlyInsured = $currentPolicy['currently_insured'] ?? $lead->currently_insured ?? '';
        
        if (empty($currentlyInsured) || strtolower($currentlyInsured) === 'no' || strtolower($currentlyInsured) === 'false') {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Allstate only accepts leads that are currently insured. This lead shows as uninsured.';
            $validation['missing_fields']['insurance.currently_insured'] = 'Must be currently insured for Allstate';
        }
        
        // 2. Validate main lead fields
        foreach ($requiredFields['lead'] as $field => $label) {
            if (empty($lead->$field)) {
                $validation['is_valid'] = false;
                $validation['missing_fields']["lead.$field"] = $label;
            }
        }
        
        // 3. Validate insurance fields
        if (empty($currentPolicy['current_insurance'] ?? $lead->insurance_company ?? '')) {
            $validation['is_valid'] = false;
            $validation['missing_fields']['insurance.current_insurance'] = 'Current Insurance Company';
        }
        
        // 4. Validate drivers
        $drivers = is_string($lead->drivers ?? '') 
            ? json_decode($lead->drivers, true) 
            : ($lead->drivers ?? []);
            
        if (empty($drivers) || !is_array($drivers) || count($drivers) < 1) {
            $validation['is_valid'] = false;
            $validation['missing_fields']['drivers.count'] = 'At least 1 driver required';
        } else {
            foreach ($drivers as $index => $driver) {
                foreach ($requiredFields['drivers']['fields'] as $field => $label) {
                    if (empty($driver[$field] ?? '')) {
                        $validation['is_valid'] = false;
                        $validation['missing_fields']["drivers.$index.$field"] = "Driver " . ($index + 1) . " - $label";
                    }
                }
            }
        }
        
        // 5. Validate vehicles
        $vehicles = is_string($lead->vehicles ?? '') 
            ? json_decode($lead->vehicles, true) 
            : ($lead->vehicles ?? []);
            
        if (empty($vehicles) || !is_array($vehicles) || count($vehicles) < 1) {
            $validation['is_valid'] = false;
            $validation['missing_fields']['vehicles.count'] = 'At least 1 vehicle required';
        } else {
            foreach ($vehicles as $index => $vehicle) {
                foreach ($requiredFields['vehicles']['fields'] as $field => $label) {
                    if (empty($vehicle[$field] ?? '')) {
                        $validation['is_valid'] = false;
                        $validation['missing_fields']["vehicles.$index.$field"] = "Vehicle " . ($index + 1) . " - $label";
                    }
                }
            }
        }
        
        // 6. Additional validations
        if (!empty($lead->phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
            if (strlen($cleanPhone) < 10) {
                $validation['is_valid'] = false;
                $validation['missing_fields']['lead.phone'] = 'Phone Number (must be 10 digits)';
            }
        }
        
        if (!empty($lead->email) && !filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            $validation['is_valid'] = false;
            $validation['missing_fields']['lead.email'] = 'Valid Email Address';
        }
        
        // Log validation results
        Log::info('Allstate validation completed', [
            'lead_id' => $lead->id ?? 'unknown',
            'is_valid' => $validation['is_valid'],
            'missing_count' => count($validation['missing_fields']),
            'currently_insured' => $currentlyInsured
        ]);
        
        return $validation;
    }
    
    /**
     * Get field mapping for frontend highlighting
     */
    public static function getFieldMappingForFrontend()
    {
        return [
            // Lead fields -> HTML element IDs
            'lead.first_name' => ['#contact-first-name', '.contact-info .first-name'],
            'lead.last_name' => ['#contact-last-name', '.contact-info .last-name'],
            'lead.email' => ['#contact-email', '.contact-info .email'],
            'lead.phone' => ['#contact-phone', '.contact-info .phone'],
            'lead.address' => ['#contact-address', '.contact-info .address'],
            'lead.city' => ['#contact-city', '.contact-info .city'],
            'lead.state' => ['#contact-state', '.contact-info .state', '#state'],
            'lead.zip_code' => ['#contact-zip', '.contact-info .zip', '#zip_code'],
            'lead.birth_date' => ['#contact-dob', '.contact-info .dob'],
            
            // Insurance fields
            'insurance.currently_insured' => ['#currently_insured', '.insurance-section .currently-insured'],
            'insurance.current_insurance' => ['#current_provider', '.insurance-section .current-provider'],
            
            // Driver fields (dynamic based on driver index)
            'drivers' => [
                'container' => '.drivers-section',
                'add_button' => '#add-driver-btn',
                'driver_cards' => '.driver-card'
            ],
            
            // Vehicle fields (dynamic based on vehicle index)
            'vehicles' => [
                'container' => '.vehicles-section',
                'add_button' => '#add-vehicle-btn', 
                'vehicle_cards' => '.vehicle-card'
            ]
        ];
    }
    
    /**
     * Generate validation summary for display
     */
    public static function generateValidationSummary($validation)
    {
        if ($validation['is_valid']) {
            return [
                'status' => 'success',
                'title' => 'Ready for Allstate Enrichment',
                'message' => 'All required fields are complete.',
                'action' => 'proceed'
            ];
        }
        
        $missingCount = count($validation['missing_fields']);
        $hasInsuranceError = !empty($validation['errors']);
        
        if ($hasInsuranceError) {
            return [
                'status' => 'error',
                'title' => 'Cannot Enrich to Allstate',
                'message' => $validation['errors'][0] ?? 'Lead does not meet Allstate requirements.',
                'action' => 'block',
                'details' => $validation['missing_fields']
            ];
        }
        
        return [
            'status' => 'warning',
            'title' => "Missing $missingCount Required Fields",
            'message' => "Please complete all required fields before enriching to Allstate.",
            'action' => 'block',
            'details' => $validation['missing_fields']
        ];
    }
}