<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DataNormalizationService
{
    /**
     * Normalize lead data for specific buyer requirements
     */
    public static function normalizeForBuyer($data, $buyerProfile = 'allstate')
    {
        switch ($buyerProfile) {
            case 'allstate':
                return self::normalizeForAllstate($data);
            default:
                return $data;
        }
    }
    
    /**
     * Normalize data specifically for Allstate Lead Marketplace API
     * Based on requirements in AllstateCallTransferService.php
     */
    private static function normalizeForAllstate($data)
    {
        // Normalize drivers data
        if (isset($data['drivers']) && is_array($data['drivers'])) {
            foreach ($data['drivers'] as &$driver) {
                $driver = self::normalizeDriverForAllstate($driver);
            }
        }
        
        // Normalize vehicles data
        if (isset($data['vehicles']) && is_array($data['vehicles'])) {
            foreach ($data['vehicles'] as &$vehicle) {
                $vehicle = self::normalizeVehicleForAllstate($vehicle);
            }
        }
        
        // Normalize other lead fields
        $data = self::normalizeLeadFieldsForAllstate($data);
        
        Log::info('Data normalized for Allstate', [
            'original_drivers_count' => count($data['drivers'] ?? []),
            'original_vehicles_count' => count($data['vehicles'] ?? [])
        ]);
        
        return $data;
    }
    
    /**
     * Normalize driver data for Allstate
     */
    private static function normalizeDriverForAllstate($driver)
    {
        // Gender normalization - Allstate expects M/F only
        $genderMap = [
            'Male' => 'M',
            'Female' => 'F', 
            'M' => 'M',
            'F' => 'F',
            'male' => 'M',
            'female' => 'F',
            'Man' => 'M',
            'Woman' => 'F',
            // Any other value defaults to M
        ];
        $driver['gender'] = $genderMap[$driver['gender'] ?? 'M'] ?? 'M';
        
        // Marital status normalization - Allstate expects lowercase
        $maritalMap = [
            'Single' => 'single',
            'Married' => 'married',
            'Divorced' => 'divorced',
            'Widowed' => 'widowed',
            'Separated' => 'separated',
            'single' => 'single',
            'married' => 'married',
            'divorced' => 'divorced',
            'widowed' => 'widowed',
            'separated' => 'separated',
            // Any other value defaults to single
        ];
        $driver['marital_status'] = $maritalMap[$driver['marital_status'] ?? 'single'] ?? 'single';
        
        // License status normalization
        $licenseMap = [
            'Valid' => 'valid',
            'Suspended' => 'suspended',
            'Expired' => 'expired',
            'Revoked' => 'revoked',
            'Permit' => 'permit',
            'valid' => 'valid',
            'suspended' => 'suspended',
            'expired' => 'expired',
            'revoked' => 'revoked',
            'permit' => 'permit',
        ];
        $driver['license_status'] = $licenseMap[$driver['license_status'] ?? 'valid'] ?? 'valid';
        
        // Education normalization - Allstate expects uppercase
        $educationMap = [
            'High School' => 'HS',
            'Some College' => 'SOME_COLLEGE',
            'College' => 'COLLEGE',
            'Graduate' => 'GRADUATE',
            'HS' => 'HS',
            'COLLEGE' => 'COLLEGE',
            'GRADUATE' => 'GRADUATE',
        ];
        $driver['education'] = $educationMap[$driver['education'] ?? 'HS'] ?? 'HS';
        
        // Occupation normalization - Allstate expects uppercase
        $occupationMap = [
            'Professional' => 'PROFESSIONAL',
            'Manager' => 'MANAGER',
            'Student' => 'STUDENT',
            'Retired' => 'RETIRED',
            'Unemployed' => 'UNEMPLOYED',
            'Other' => 'OTHER',
        ];
        $driver['occupation'] = $occupationMap[$driver['occupation'] ?? 'OTHER'] ?? 'OTHER';
        
        // Violation types normalization
        if (isset($driver['violations']) && is_array($driver['violations'])) {
            foreach ($driver['violations'] as &$violation) {
                $violation = self::normalizeViolationForAllstate($violation);
            }
        }
        
        // Accident types normalization
        if (isset($driver['accidents']) && is_array($driver['accidents'])) {
            foreach ($driver['accidents'] as &$accident) {
                $accident = self::normalizeAccidentForAllstate($accident);
            }
        }
        
        return $driver;
    }
    
    /**
     * Normalize vehicle data for Allstate
     */
    private static function normalizeVehicleForAllstate($vehicle)
    {
        // Usage normalization - Allstate expects lowercase specific values
        $usageMap = [
            'Commute to Work' => 'commute',
            'Commute' => 'commute',
            'Personal Use' => 'pleasure',
            'Personal' => 'pleasure',
            'Pleasure' => 'pleasure',
            'Business' => 'business',
            'Commercial' => 'commercial',
            'commute' => 'commute',
            'pleasure' => 'pleasure',
            'business' => 'business',
            'commercial' => 'commercial',
        ];
        $vehicle['primary_use'] = $usageMap[$vehicle['primary_use'] ?? 'pleasure'] ?? 'pleasure';
        
        // Ownership normalization - Allstate expects lowercase
        $ownershipMap = [
            'Owned' => 'owned',
            'Leased' => 'leased',
            'Financed' => 'financed',
            'owned' => 'owned',
            'leased' => 'leased',
            'financed' => 'financed',
        ];
        $vehicle['ownership'] = $ownershipMap[$vehicle['ownership'] ?? 'owned'] ?? 'owned';
        
        // Garage normalization - convert to boolean
        if (isset($vehicle['garage'])) {
            $garageMap = [
                'Yes' => true,
                'No' => false,
                'Garage' => true,
                'Street' => false,
                'Driveway' => false,
                true => true,
                false => false,
                1 => true,
                0 => false,
            ];
            $vehicle['garage'] = $garageMap[$vehicle['garage']] ?? false;
        } else {
            $vehicle['garage'] = false;
        }
        
        // Annual mileage normalization - ensure it's an integer
        if (isset($vehicle['annual_miles'])) {
            $milesMap = [
                'Under 5,000' => 5000,
                '5,000-10,000' => 7500,
                '10,000-15,000' => 12500,
                '15,000-20,000' => 17500,
                'Over 20,000' => 25000,
            ];
            
            if (is_string($vehicle['annual_miles']) && isset($milesMap[$vehicle['annual_miles']])) {
                $vehicle['annual_miles'] = $milesMap[$vehicle['annual_miles']];
            } else {
                $vehicle['annual_miles'] = (int)($vehicle['annual_miles'] ?? 12000);
            }
        }
        
        return $vehicle;
    }
    
    /**
     * Normalize violation data for Allstate
     */
    private static function normalizeViolationForAllstate($violation)
    {
        $violationTypeMap = [
            'Speeding' => 'SPEEDING',
            'DUI/DWI' => 'DUI',
            'Reckless Driving' => 'RECKLESS',
            'Running Red Light' => 'RED_LIGHT',
            'Stop Sign Violation' => 'STOP_SIGN',
            'Improper Lane Change' => 'LANE_CHANGE',
            'Following Too Closely' => 'TAILGATING',
            'Failure to Yield' => 'FAILURE_TO_YIELD',
            'Careless Driving' => 'CARELESS',
        ];
        
        $violation['violation_type'] = $violationTypeMap[$violation['violation_type'] ?? 'OTHER'] ?? 'OTHER';
        
        return $violation;
    }
    
    /**
     * Normalize accident data for Allstate
     */
    private static function normalizeAccidentForAllstate($accident)
    {
        $accidentTypeMap = [
            'Rear-end' => 'REAR_END',
            'Side impact' => 'SIDE_IMPACT',
            'Head-on' => 'HEAD_ON',
            'Single vehicle' => 'SINGLE_VEHICLE',
            'Multi-vehicle' => 'MULTI_VEHICLE',
            'Backing/Parking' => 'BACKING',
            'Hit and run' => 'HIT_AND_RUN',
            'Rollover' => 'ROLLOVER',
        ];
        
        $accident['accident_type'] = $accidentTypeMap[$accident['accident_type'] ?? 'OTHER'] ?? 'OTHER';
        
        // At fault normalization
        $atFaultMap = [
            'true' => true,
            'false' => false,
            'Yes' => true,
            'No' => false,
            'partial' => false, // Allstate might not support partial, default to false
            'unknown' => false,
            true => true,
            false => false,
        ];
        $accident['at_fault'] = $atFaultMap[$accident['at_fault'] ?? false] ?? false;
        
        return $accident;
    }
    
    /**
     * Normalize other lead fields for Allstate
     */
    private static function normalizeLeadFieldsForAllstate($data)
    {
        // Coverage type normalization
        if (isset($data['coverage_type'])) {
            $coverageMap = [
                'Liability Only' => 'LIABILITY',
                'Full Coverage' => 'FULL',
                'Comprehensive' => 'COMPREHENSIVE',
                'Collision' => 'COLLISION',
                'LIABILITY' => 'LIABILITY',
                'FULL' => 'FULL',
                'COMPREHENSIVE' => 'COMPREHENSIVE',
                'COLLISION' => 'COLLISION',
            ];
            $data['coverage_type'] = $coverageMap[$data['coverage_type'] ?? 'BASIC'] ?? 'BASIC';
        }
        
        // Currently insured normalization
        if (isset($data['currently_insured'])) {
            $insuredMap = [
                'Yes' => true,
                'No' => false,
                'yes' => true,
                'no' => false,
                true => true,
                false => false,
                1 => true,
                0 => false,
            ];
            $data['currently_insured'] = $insuredMap[$data['currently_insured']] ?? false;
        }
        
        return $data;
    }
    
    /**
     * Get validation report for normalized data
     */
    public static function getValidationReport($originalData, $normalizedData, $buyerProfile = 'allstate')
    {
        $report = [
            'buyer_profile' => $buyerProfile,
            'changes_made' => [],
            'warnings' => [],
            'data_preserved' => true
        ];
        
        // Compare original vs normalized and report changes
        if ($buyerProfile === 'allstate') {
            $report = self::validateAllstateNormalization($originalData, $normalizedData, $report);
        }
        
        return $report;
    }
    
    /**
     * Validate Allstate normalization and report changes
     */
    private static function validateAllstateNormalization($original, $normalized, $report)
    {
        // Check driver changes
        if (isset($original['drivers']) && isset($normalized['drivers'])) {
            foreach ($original['drivers'] as $index => $originalDriver) {
                $normalizedDriver = $normalized['drivers'][$index] ?? [];
                
                foreach (['gender', 'marital_status', 'license_status'] as $field) {
                    if (($originalDriver[$field] ?? '') !== ($normalizedDriver[$field] ?? '')) {
                        $report['changes_made'][] = [
                            'field' => "drivers[$index][$field]",
                            'original' => $originalDriver[$field] ?? '',
                            'normalized' => $normalizedDriver[$field] ?? '',
                            'reason' => 'Allstate format requirement'
                        ];
                    }
                }
            }
        }
        
        // Check vehicle changes
        if (isset($original['vehicles']) && isset($normalized['vehicles'])) {
            foreach ($original['vehicles'] as $index => $originalVehicle) {
                $normalizedVehicle = $normalized['vehicles'][$index] ?? [];
                
                foreach (['primary_use', 'ownership', 'garage'] as $field) {
                    if (($originalVehicle[$field] ?? '') !== ($normalizedVehicle[$field] ?? '')) {
                        $report['changes_made'][] = [
                            'field' => "vehicles[$index][$field]",
                            'original' => $originalVehicle[$field] ?? '',
                            'normalized' => $normalizedVehicle[$field] ?? '',
                            'reason' => 'Allstate format requirement'
                        ];
                    }
                }
            }
        }
        
        return $report;
    }
}