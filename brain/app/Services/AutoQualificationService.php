<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AutoQualificationService
{
    /**
     * Auto-fill the 12 qualification questions from lead data
     * This simulates what an agent would ask during the Vici call
     */
    public function generateQualificationData($lead)
    {
        try {
            Log::info('Auto-generating qualification data for lead', [
                'lead_id' => $lead->id ?? 'unknown',
                'lead_name' => $lead->name ?? 'unknown'
            ]);

            // Parse lead data sources
            $drivers = json_decode($lead->drivers ?? '[]', true) ?: [];
            $vehicles = json_decode($lead->vehicles ?? '[]', true) ?: [];
            $currentPolicy = json_decode($lead->current_policy ?? '{}', true) ?: [];
            $payload = json_decode($lead->payload ?? '{}', true) ?: [];

            // Auto-fill the 12 qualification questions based on available data
            $qualificationData = [
                // Q1: Are you currently insured?
                'currently_insured' => $this->determineCurrentlyInsured($currentPolicy, $drivers, $payload),
                
                // Q2: Who is your current provider? (follow-up to Q1)
                'current_company' => $this->getCurrentInsuranceCompany($currentPolicy, $payload),
                
                // Q3: How long have you been continuously insured? (follow-up to Q1)
                'insurance_duration' => $this->getInsuranceDuration($currentPolicy, $payload),
                
                // Q4: Do you have an active driver's license?
                'active_license' => $this->hasActiveLicense($drivers),
                
                // Q5 & Q6: DUI or SR22?
                'dui_sr22' => $this->getDUIOrSR22Status($drivers),
                'dui_timeframe' => $this->getDUITimeframe($drivers),
                
                // Q7: State (already have from lead)
                'state' => $lead->state ?? 'FL',
                
                // Q8: ZIP Code (already have from lead)
                'zip_code' => $lead->zip_code ?? '33139',
                
                // Q9: How many cars do you need a quote for?
                'num_vehicles' => $this->getVehicleCount($vehicles),
                
                // Q10: Do you own or rent your home?
                'home_status' => $this->getHomeOwnership($drivers, $payload),
                
                // Q11: Have you received a quote from Allstate in the last 2 months?
                'allstate_quote' => $this->hasRecentAllstateQuote($payload),
                
                // Q12: Ready to speak with an agent now?
                'ready_to_speak' => 'yes', // Default to yes for testing
                
                // Additional data for better Allstate integration
                'policy_expires' => $this->getPolicyExpirationDate($currentPolicy),
                'current_premium' => $this->getCurrentPremium($currentPolicy),
                'shopping_for_rates' => true, // They submitted a lead, so yes
                'coverage_type' => $lead->type ?? 'auto',
                'deductible_preference' => $this->getDeductiblePreference($currentPolicy),
                'credit_score' => $this->getCreditScore($drivers),
                'education_level' => $this->getEducationLevel($drivers),
                'occupation' => $this->getOccupation($drivers),
                'years_licensed' => $this->getYearsLicensed($drivers),
                'accidents_violations' => $this->hasAccidentsOrViolations($drivers),
                'dui_conviction' => $this->hasDUIConviction($drivers),
                'sr22_required' => $this->requiresSR22($drivers),
                'date_of_birth' => $this->getDateOfBirth($drivers),
                'gender' => $this->getGender($drivers),
                'marital_status' => $this->getMaritalStatus($drivers),
                'lead_quality_score' => $this->calculateLeadQualityScore($lead, $drivers, $vehicles),
                'motivation_level' => $this->calculateMotivationLevel($lead),
                'urgency' => $this->determineUrgency($currentPolicy),
                'best_time_to_call' => $this->getBestTimeToCall(),
                'agent_notes' => $this->generateAgentNotes($lead, $drivers, $vehicles)
            ];

            Log::info('Auto-qualification completed', [
                'lead_id' => $lead->id ?? 'unknown',
                'questions_filled' => count($qualificationData),
                'currently_insured' => $qualificationData['currently_insured'],
                'vehicle_count' => $qualificationData['num_vehicles'],
                'lead_quality_score' => $qualificationData['lead_quality_score']
            ]);

            return $qualificationData;

        } catch (\Exception $e) {
            Log::error('Auto-qualification failed', [
                'lead_id' => $lead->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            // Return basic fallback data
            return $this->getFallbackQualificationData($lead);
        }
    }

    private function determineCurrentlyInsured($currentPolicy, $drivers, $payload)
    {
        // Check if they have current insurance info
        if (!empty($currentPolicy['current_insurance']) || 
            !empty($currentPolicy['current_company']) ||
            !empty($payload['current_insurance'])) {
            return true;
        }
        
        // Check driver data for insurance status
        foreach ($drivers as $driver) {
            if (!empty($driver['current_insurance']) || !empty($driver['insurance_company'])) {
                return true;
            }
        }
        
        return false; // Default to not insured if no clear indication
    }

    private function getCurrentInsuranceCompany($currentPolicy, $payload)
    {
        $company = $currentPolicy['current_insurance'] ?? 
                   $currentPolicy['current_company'] ?? 
                   $payload['current_insurance'] ?? null;
        
        // Map to standard company names
        $companyMap = [
            'geico' => 'geico',
            'state farm' => 'state_farm',
            'progressive' => 'progressive',
            'allstate' => 'allstate',
            'farmers' => 'farmers',
            'usaa' => 'usaa',
            'liberty mutual' => 'liberty_mutual'
        ];
        
        $normalized = strtolower($company ?? '');
        return $companyMap[$normalized] ?? ($company ? 'other' : null);
    }

    private function getInsuranceDuration($currentPolicy, $payload)
    {
        // Try to determine from policy start date or other indicators
        $startDate = $currentPolicy['policy_start'] ?? $payload['policy_start'] ?? null;
        
        if ($startDate) {
            $years = now()->diffInYears($startDate);
            if ($years < 0.5) return 'under_6_months';
            if ($years < 1) return '6_months_1_year';
            if ($years <= 3) return '1_3_years';
            return 'over_3_years';
        }
        
        return 'over_3_years'; // Default assumption for lead quality
    }

    private function hasActiveLicense($drivers)
    {
        foreach ($drivers as $driver) {
            $status = strtolower($driver['license_status'] ?? '');
            if (in_array($status, ['valid', 'active', 'current'])) {
                return 'yes';
            }
            if (in_array($status, ['suspended', 'revoked', 'expired'])) {
                return $status;
            }
        }
        return 'yes'; // Default assumption
    }

    private function getDUIOrSR22Status($drivers)
    {
        $hasDUI = false;
        $hasSR22 = false;
        
        foreach ($drivers as $driver) {
            if (!empty($driver['dui_conviction']) || !empty($driver['dui'])) {
                $hasDUI = true;
            }
            if (!empty($driver['sr22_required']) || !empty($driver['sr22'])) {
                $hasSR22 = true;
            }
        }
        
        if ($hasDUI && $hasSR22) return 'both';
        if ($hasDUI) return 'dui_only';
        if ($hasSR22) return 'sr22_only';
        return 'no';
    }

    private function getDUITimeframe($drivers)
    {
        foreach ($drivers as $driver) {
            $duiDate = $driver['dui_date'] ?? null;
            if ($duiDate) {
                $years = now()->diffInYears($duiDate);
                if ($years < 1) return '1';
                if ($years <= 3) return '2';
                return '3';
            }
        }
        return null;
    }

    private function getVehicleCount($vehicles)
    {
        $count = count($vehicles);
        if ($count >= 4) return '4';
        if ($count >= 1) return (string)$count;
        return '1'; // Default assumption
    }

    private function getHomeOwnership($drivers, $payload)
    {
        // Check driver data first
        foreach ($drivers as $driver) {
            $residence = strtolower($driver['residence_type'] ?? '');
            if (in_array($residence, ['own', 'owned', 'owner'])) return 'own';
            if (in_array($residence, ['rent', 'rental', 'renter'])) return 'rent';
        }
        
        // Check payload
        $residence = strtolower($payload['residence_type'] ?? '');
        if (in_array($residence, ['own', 'owned', 'owner'])) return 'own';
        if (in_array($residence, ['rent', 'rental', 'renter'])) return 'rent';
        
        return 'own'; // Default assumption for lead quality
    }

    private function hasRecentAllstateQuote($payload)
    {
        // Check if they mentioned Allstate anywhere
        $payloadStr = strtolower(json_encode($payload));
        if (strpos($payloadStr, 'allstate') !== false) {
            return 'yes';
        }
        return 'no'; // Default
    }

    private function getPolicyExpirationDate($currentPolicy)
    {
        return $currentPolicy['expiration_date'] ?? 
               $currentPolicy['policy_expires'] ?? 
               now()->addMonths(2)->format('Y-m-d'); // Default 2 months out
    }

    private function getCurrentPremium($currentPolicy)
    {
        return $currentPolicy['premium'] ?? 
               $currentPolicy['monthly_premium'] ?? 
               $currentPolicy['cost'] ?? null;
    }

    private function getDeductiblePreference($currentPolicy)
    {
        return $currentPolicy['deductible'] ?? 500; // Default $500
    }

    private function getCreditScore($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['credit_score'])) {
                return (int)$driver['credit_score'];
            }
        }
        return 720; // Default good credit
    }

    private function getEducationLevel($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['education'])) {
                return $driver['education'];
            }
        }
        return 'Bachelors'; // Default
    }

    private function getOccupation($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['occupation'])) {
                return $driver['occupation'];
            }
        }
        return 'Professional'; // Default
    }

    private function getYearsLicensed($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['years_licensed'])) {
                return (int)$driver['years_licensed'];
            }
        }
        return 10; // Default
    }

    private function hasAccidentsOrViolations($drivers)
    {
        foreach ($drivers as $driver) {
            $accidents = ($driver['accidents_3_years'] ?? $driver['accidents'] ?? 0);
            $violations = ($driver['violations_3_years'] ?? $driver['violations'] ?? 0);
            if ($accidents > 0 || $violations > 0) {
                return true;
            }
        }
        return false;
    }

    private function hasDUIConviction($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['dui_conviction']) || !empty($driver['dui'])) {
                return true;
            }
        }
        return false;
    }

    private function requiresSR22($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['sr22_required']) || !empty($driver['sr22'])) {
                return true;
            }
        }
        return false;
    }

    private function getDateOfBirth($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['birth_date']) || !empty($driver['date_of_birth'])) {
                return $driver['birth_date'] ?? $driver['date_of_birth'];
            }
        }
        return '1985-03-15'; // Default
    }

    private function getGender($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['gender'])) {
                return $driver['gender'];
            }
        }
        return 'Female'; // Default
    }

    private function getMaritalStatus($drivers)
    {
        foreach ($drivers as $driver) {
            if (!empty($driver['marital_status'])) {
                return $driver['marital_status'];
            }
        }
        return 'Single'; // Default
    }

    private function calculateLeadQualityScore($lead, $drivers, $vehicles)
    {
        $score = 5; // Base score
        
        // Increase score for complete data
        if (!empty($drivers)) $score += 1;
        if (!empty($vehicles)) $score += 1;
        if (!empty($lead->email)) $score += 1;
        if (!empty($lead->tcpa_compliant) && $lead->tcpa_compliant) $score += 1;
        
        return min($score, 10); // Cap at 10
    }

    private function calculateMotivationLevel($lead)
    {
        // Base motivation on lead freshness and completeness
        $motivation = 7; // Base
        
        if ($lead->created_at && $lead->created_at->diffInHours() < 1) {
            $motivation += 1; // Fresh lead
        }
        
        return min($motivation, 10);
    }

    private function determineUrgency($currentPolicy)
    {
        $expirationDate = $currentPolicy['expiration_date'] ?? null;
        if ($expirationDate) {
            $daysUntilExpiration = now()->diffInDays($expirationDate);
            if ($daysUntilExpiration <= 30) return '30_days';
            if ($daysUntilExpiration <= 60) return '60_days';
        }
        return 'standard';
    }

    private function getBestTimeToCall()
    {
        // Vary based on time of day the lead came in
        $hour = now()->hour;
        if ($hour < 12) return 'mornings';
        if ($hour < 17) return 'afternoons';
        return 'evenings';
    }

    private function generateAgentNotes($lead, $drivers, $vehicles)
    {
        $notes = [];
        
        $notes[] = "AUTO-QUALIFIED LEAD from LeadsQuotingFast";
        $notes[] = "Lead Type: " . ucfirst($lead->type ?? 'auto');
        
        if (!empty($drivers)) {
            $notes[] = "Drivers: " . count($drivers);
        }
        
        if (!empty($vehicles)) {
            $notes[] = "Vehicles: " . count($vehicles);
            $vehicle = $vehicles[0] ?? null;
            if ($vehicle) {
                $notes[] = "Primary Vehicle: " . ($vehicle['year'] ?? '') . " " . ($vehicle['make'] ?? '') . " " . ($vehicle['model'] ?? '');
            }
        }
        
        $notes[] = "Source: " . ($lead->source ?? 'Unknown');
        $notes[] = "Received: " . ($lead->created_at ? $lead->created_at->format('M j, Y g:i A') : 'Unknown');
        
        return implode('. ', $notes) . '.';
    }

    private function getFallbackQualificationData($lead)
    {
        return [
            'currently_insured' => false,
            'current_company' => null,
            'insurance_duration' => 'over_3_years',
            'active_license' => 'yes',
            'dui_sr22' => 'no',
            'dui_timeframe' => null,
            'state' => $lead->state ?? 'FL',
            'zip_code' => $lead->zip_code ?? '33139',
            'num_vehicles' => '1',
            'home_status' => 'own',
            'allstate_quote' => 'no',
            'ready_to_speak' => 'yes',
            'lead_quality_score' => 5,
            'motivation_level' => 7,
            'urgency' => 'standard',
            'best_time_to_call' => 'evenings',
            'agent_notes' => 'AUTO-QUALIFIED FALLBACK DATA - Limited lead information available'
        ];
    }
}