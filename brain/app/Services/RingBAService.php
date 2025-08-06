<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Lead;

class RingBAService
{
    private $baseUrls = [
        'insured' => 'https://display.ringba.com/enrich/2674154334576444838',
        'uninsured' => 'https://display.ringba.com/enrich/2676487329580844084', 
        'homeowner' => 'https://display.ringba.com/enrich/2717035800150673197'
    ];

    /**
     * Send lead to RingBA for enrichment and routing
     */
    public function sendLead(Lead $lead, array $qualificationData = [])
    {
        try {
            // Select the right RingBA campaign URL
            $url = $this->selectRingBAUrl($lead, $qualificationData);
            
            // Build the payload with all our intelligence
            $payload = $this->buildPayload($lead, $qualificationData);
            
            Log::info('Sending lead to RingBA', [
                'lead_id' => $lead->id,
                'url' => $url,
                'payload' => $payload
            ]);
            
            // Send to RingBA
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'QuotingFast-Brain/1.0'
                ])
                ->post($url, $payload);
            
            // Log the response
            Log::info('RingBA response received', [
                'lead_id' => $lead->id,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);
            
            // Update lead with RingBA tracking
            $lead->update([
                'ringba_sent_at' => now(),
                'ringba_url' => $url,
                'ringba_response' => $response->json(),
                'ringba_status' => $response->successful() ? 'sent' : 'failed'
            ]);
            
            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json(),
                'message' => $response->successful() ? 
                    'Lead sent to RingBA successfully' : 
                    'Failed to send lead to RingBA'
            ];
            
        } catch (\Exception $e) {
            Log::error('RingBA API Error', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Exception occurred while sending to RingBA'
            ];
        }
    }
    
    /**
     * Select the appropriate RingBA URL based on lead characteristics
     */
    private function selectRingBAUrl(Lead $lead, array $qualificationData = [])
    {
        // Use qualification data if available (from agent)
        if (!empty($qualificationData)) {
            if ($qualificationData['coverage_type'] === 'home') {
                return $this->baseUrls['homeowner'];
            }
            
            if ($qualificationData['currently_insured'] ?? false) {
                return $this->baseUrls['insured'];
            } else {
                return $this->baseUrls['uninsured'];
            }
        }
        
        // Fallback to lead data analysis
        if ($lead->type === 'home') {
            return $this->baseUrls['homeowner'];
        }
        
        // For auto leads, try to determine insurance status
        $payload = json_decode($lead->payload, true) ?? [];
        $currentlyInsured = $payload['currently_insured'] ?? 
                           $payload['current_insurance'] ?? 
                           !empty($payload['current_insurance_company']);
        
        return $currentlyInsured ? 
            $this->baseUrls['insured'] : 
            $this->baseUrls['uninsured'];
    }
    
    /**
     * Build comprehensive payload for RingBA
     */
    private function buildPayload(Lead $lead, array $qualificationData = [])
    {
        $payload = json_decode($lead->payload, true) ?? [];
        
        // Base lead information
        $data = [
            'lead_id' => $lead->id,
            'phone' => $lead->phone,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip_code' => $lead->zip_code,
            
            // Campaign Attribution
            'source' => $lead->source,
            'campaign_id' => $lead->campaign_id,
            'external_lead_id' => $lead->external_lead_id,
            
            // Timing Information
            'lead_created' => $lead->created_at->toISOString(),
            'timezone' => $lead->timezone ?? $this->getTimezoneFromState($lead->state),
        ];
        
        // Add qualification data if available (from agent)
        if (!empty($qualificationData)) {
            $data = array_merge($data, [
                // Insurance Status
                'currently_insured' => $qualificationData['currently_insured'] ?? null,
                'current_insurance_company' => $qualificationData['current_company'] ?? null,
                'policy_expiration_date' => $qualificationData['policy_expires'] ?? null,
                'shopping_for_rates' => $qualificationData['shopping_for_rates'] ?? null,
                
                // Coverage Details
                'coverage_type' => $qualificationData['coverage_type'] ?? $lead->type,
                'vehicle_count' => $qualificationData['vehicle_count'] ?? null,
                'home_ownership_status' => $qualificationData['home_status'] ?? null,
                'recent_claims' => $qualificationData['recent_claims'] ?? null,
                
                // Financial Information
                'current_monthly_premium' => $qualificationData['current_premium'] ?? null,
                'desired_budget' => $qualificationData['desired_budget'] ?? null,
                'urgency_level' => $qualificationData['urgency'] ?? null,
                
                // Decision Making
                'is_decision_maker' => $qualificationData['decision_maker'] ?? null,
                'motivation_score' => $qualificationData['motivation_level'] ?? null,
                'agent_quality_score' => $qualificationData['lead_quality_score'] ?? null,
                
                // Agent Information
                'qualified_by_agent' => true,
                'qualification_timestamp' => now()->toISOString(),
                'agent_notes' => $qualificationData['agent_notes'] ?? null,
            ]);
        } else {
            // Use original payload data as fallback
            $data = array_merge($data, [
                'coverage_type' => $lead->type,
                'currently_insured' => $payload['currently_insured'] ?? null,
                'current_insurance_company' => $payload['current_insurance_company'] ?? null,
                'qualified_by_agent' => false,
            ]);
        }
        
        // Add vehicle information for auto leads
        if ($lead->type === 'auto' || ($qualificationData['coverage_type'] ?? '') === 'auto') {
            $data['vehicles'] = $this->extractVehicleData($payload);
            $data['drivers'] = $this->extractDriverData($payload);
        }
        
        // Add property information for home leads
        if ($lead->type === 'home' || ($qualificationData['coverage_type'] ?? '') === 'home') {
            $data['property'] = $this->extractPropertyData($payload);
        }
        
        // Add any additional payload data that might be useful
        $data['original_payload'] = $payload;
        $data['lead_score'] = $this->calculateLeadScore($lead, $qualificationData);
        
        return $data;
    }
    
    /**
     * Extract vehicle information from payload
     */
    private function extractVehicleData(array $payload)
    {
        $vehicles = [];
        
        // Handle multiple vehicle formats
        if (isset($payload['vehicles']) && is_array($payload['vehicles'])) {
            $vehicles = $payload['vehicles'];
        } else {
            // Single vehicle data
            $vehicle = [];
            if (!empty($payload['year'])) $vehicle['year'] = $payload['year'];
            if (!empty($payload['make'])) $vehicle['make'] = $payload['make'];
            if (!empty($payload['model'])) $vehicle['model'] = $payload['model'];
            if (!empty($vehicle)) $vehicles[] = $vehicle;
        }
        
        return $vehicles;
    }
    
    /**
     * Extract driver information from payload
     */
    private function extractDriverData(array $payload)
    {
        $drivers = [];
        
        if (isset($payload['drivers']) && is_array($payload['drivers'])) {
            $drivers = $payload['drivers'];
        } else {
            // Primary driver data
            $driver = [];
            if (!empty($payload['date_of_birth'])) $driver['date_of_birth'] = $payload['date_of_birth'];
            if (!empty($payload['gender'])) $driver['gender'] = $payload['gender'];
            if (!empty($payload['marital_status'])) $driver['marital_status'] = $payload['marital_status'];
            if (!empty($driver)) $drivers[] = $driver;
        }
        
        return $drivers;
    }
    
    /**
     * Extract property information from payload
     */
    private function extractPropertyData(array $payload)
    {
        return [
            'property_type' => $payload['property_type'] ?? null,
            'year_built' => $payload['year_built'] ?? null,
            'square_footage' => $payload['square_footage'] ?? null,
            'bedrooms' => $payload['bedrooms'] ?? null,
            'bathrooms' => $payload['bathrooms'] ?? null,
            'garage' => $payload['garage'] ?? null,
            'basement' => $payload['basement'] ?? null,
            'roof_type' => $payload['roof_type'] ?? null,
            'heating_type' => $payload['heating_type'] ?? null,
            'property_value' => $payload['property_value'] ?? null,
        ];
    }
    
    /**
     * Calculate lead score based on available data
     */
    private function calculateLeadScore(Lead $lead, array $qualificationData = [])
    {
        $score = 5; // Base score
        
        // Agent qualification adds significant value
        if (!empty($qualificationData)) {
            $score += 3;
            
            // High motivation adds more points
            if (($qualificationData['motivation_level'] ?? 0) >= 8) {
                $score += 2;
            }
            
            // Decision maker adds points
            if ($qualificationData['decision_maker'] ?? false) {
                $score += 1;
            }
        }
        
        // Fresh leads score higher
        $hoursOld = $lead->created_at->diffInHours(now());
        if ($hoursOld < 1) $score += 2;
        elseif ($hoursOld < 24) $score += 1;
        
        // Complete data scores higher
        if (!empty($lead->email)) $score += 1;
        if (!empty($lead->address)) $score += 1;
        
        return min(10, max(1, $score));
    }
    
    /**
     * Get timezone from state code
     */
    private function getTimezoneFromState(string $state)
    {
        $timezones = [
            'CA' => 'America/Los_Angeles',
            'NY' => 'America/New_York', 
            'TX' => 'America/Chicago',
            'FL' => 'America/New_York',
            // Add more as needed
        ];
        
        return $timezones[$state] ?? 'America/New_York';
    }
}