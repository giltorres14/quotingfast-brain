<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViciApiUpdateService
{
    private string $baseUrl;
    private string $apiUser = 'apiuser';
    private string $apiPass;
    
    public function __construct()
    {
        // Use Non-Agent API for updating leads
        $viciServer = config('services.vici.web_server', 'philli.callix.ai');
        $this->baseUrl = "https://{$viciServer}/vicidial/non_agent_api.php";
        $this->apiPass = env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6');
    }
    
    /**
     * Update existing Vici lead with Brain Lead ID using API
     */
    public function updateViciLeadWithBrainId(Lead $lead): array
    {
        try {
            // Ensure we have the 13-digit external_lead_id
            $brainLeadId = $lead->external_lead_id;
            if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
                Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                    'lead_id' => $lead->id,
                    'current_external_id' => $brainLeadId
                ]);
                $brainLeadId = Lead::generateExternalLeadId();
                $lead->external_lead_id = $brainLeadId;
                $lead->save();
            }
            
            // First, search for the lead in Vici by phone number
            $searchResult = $this->searchLeadByPhone($lead->phone);
            
            if (!$searchResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Lead not found in Vici',
                    'brain_lead_id' => $brainLeadId,
                    'phone' => $lead->phone
                ];
            }
            
            $viciLeadId = $searchResult['lead_id'];
            
            // Update the lead using update_lead function
            $updateParams = [
                'source' => 'brain_update',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'update_lead',
                'lead_id' => $viciLeadId,
                'vendor_lead_code' => $brainLeadId, // Store 13-digit Brain Lead ID
                'source_id' => 'BRAIN_' . $brainLeadId,
                'comments' => "Brain Lead ID: {$brainLeadId} | Updated: " . now()->format('Y-m-d H:i:s')
            ];
            
            // Add any other fields that need updating
            if ($lead->first_name) {
                $updateParams['first_name'] = $lead->first_name;
            }
            if ($lead->last_name) {
                $updateParams['last_name'] = $lead->last_name;
            }
            if ($lead->address) {
                $updateParams['address1'] = $lead->address;
            }
            if ($lead->city) {
                $updateParams['city'] = $lead->city;
            }
            if ($lead->state) {
                $updateParams['state'] = $lead->state;
            }
            if ($lead->zip_code) {
                $updateParams['postal_code'] = $lead->zip_code;
            }
            if ($lead->email) {
                $updateParams['email'] = $lead->email;
            }
            
            Log::info('Vici API: Updating lead with Brain ID', [
                'vici_lead_id' => $viciLeadId,
                'brain_lead_id' => $brainLeadId,
                'phone' => $lead->phone
            ]);
            
            // Make API call
            $url = $this->baseUrl . '?' . http_build_query($updateParams);
            $response = Http::timeout(30)->get($url);
            $responseBody = $response->body();
            
            // Check response
            if (strpos($responseBody, 'SUCCESS') !== false || strpos($responseBody, 'NOTICE: update_lead') !== false) {
                Log::info('✅ Vici Lead Updated with Brain ID via API', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'phone' => $lead->phone,
                    'response' => substr($responseBody, 0, 200)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Vici lead updated with Brain Lead ID via API',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'api_response' => substr($responseBody, 0, 500)
                ];
            } else {
                Log::warning('Vici API update returned unexpected response', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'response' => substr($responseBody, 0, 500)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'API update failed',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'api_response' => substr($responseBody, 0, 500)
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Vici API Update Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'API error: ' . $e->getMessage(),
                'brain_lead_id' => $lead->external_lead_id ?? null
            ];
        }
    }
    
    /**
     * Search for a lead in Vici by phone number
     */
    private function searchLeadByPhone(string $phone): array
    {
        try {
            // Use search_phone_list function to find lead
            $searchParams = [
                'source' => 'brain_search',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'search_phone_list',
                'phone_number' => $phone,
                'records_to_return' => 1
            ];
            
            $url = $this->baseUrl . '?' . http_build_query($searchParams);
            $response = Http::timeout(30)->get($url);
            $responseBody = $response->body();
            
            // Parse response to get lead_id
            // Response format: SUCCESS: search_phone_list RESULTS FOUND - 1|lead_id: 12345|...
            if (strpos($responseBody, 'RESULTS FOUND') !== false) {
                // Extract lead_id from response
                if (preg_match('/lead_id:\s*(\d+)/', $responseBody, $matches)) {
                    return [
                        'success' => true,
                        'lead_id' => $matches[1],
                        'response' => substr($responseBody, 0, 200)
                    ];
                }
            }
            
            // Try alternative: list all leads and search
            // This is less efficient but works if search_phone_list isn't available
            return $this->searchViaListExport($phone);
            
        } catch (\Exception $e) {
            Log::error('Vici lead search failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Alternative search method using list export
     */
    private function searchViaListExport(string $phone): array
    {
        try {
            // Use list_export_calls_report to get leads
            $exportParams = [
                'source' => 'brain_export',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'list_export_calls_report',
                'list_id' => '101',
                'header' => 'YES',
                'rec_fields' => 'lead_id,phone_number,vendor_lead_code'
            ];
            
            $url = $this->baseUrl . '?' . http_build_query($exportParams);
            $response = Http::timeout(60)->get($url);
            $responseBody = $response->body();
            
            // Parse CSV response
            $lines = explode("\n", $responseBody);
            foreach ($lines as $line) {
                if (strpos($line, $phone) !== false) {
                    $fields = str_getcsv($line);
                    if (isset($fields[0]) && is_numeric($fields[0])) {
                        return [
                            'success' => true,
                            'lead_id' => $fields[0],
                            'vendor_code' => $fields[2] ?? null
                        ];
                    }
                }
            }
            
            return [
                'success' => false,
                'message' => 'Lead not found in list export'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Batch update multiple leads
     */
    public function batchUpdateLeads(array $leads, callable $progressCallback = null): array
    {
        $results = [
            'total' => count($leads),
            'updated' => 0,
            'not_found' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($leads as $index => $lead) {
            $result = $this->updateViciLeadWithBrainId($lead);
            
            if ($result['success']) {
                $results['updated']++;
            } elseif (strpos($result['message'], 'not found') !== false) {
                $results['not_found']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'lead_id' => $lead->id,
                'phone' => $lead->phone,
                'result' => $result
            ];
            
            // Call progress callback if provided
            if ($progressCallback) {
                $progressCallback($index + 1, $results['total'], $lead, $result);
            }
            
            // Small delay to avoid overwhelming the API
            usleep(100000); // 0.1 second delay
        }
        
        return $results;
    }
}



namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViciApiUpdateService
{
    private string $baseUrl;
    private string $apiUser = 'apiuser';
    private string $apiPass;
    
    public function __construct()
    {
        // Use Non-Agent API for updating leads
        $viciServer = config('services.vici.web_server', 'philli.callix.ai');
        $this->baseUrl = "https://{$viciServer}/vicidial/non_agent_api.php";
        $this->apiPass = env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6');
    }
    
    /**
     * Update existing Vici lead with Brain Lead ID using API
     */
    public function updateViciLeadWithBrainId(Lead $lead): array
    {
        try {
            // Ensure we have the 13-digit external_lead_id
            $brainLeadId = $lead->external_lead_id;
            if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
                Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                    'lead_id' => $lead->id,
                    'current_external_id' => $brainLeadId
                ]);
                $brainLeadId = Lead::generateExternalLeadId();
                $lead->external_lead_id = $brainLeadId;
                $lead->save();
            }
            
            // First, search for the lead in Vici by phone number
            $searchResult = $this->searchLeadByPhone($lead->phone);
            
            if (!$searchResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Lead not found in Vici',
                    'brain_lead_id' => $brainLeadId,
                    'phone' => $lead->phone
                ];
            }
            
            $viciLeadId = $searchResult['lead_id'];
            
            // Update the lead using update_lead function
            $updateParams = [
                'source' => 'brain_update',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'update_lead',
                'lead_id' => $viciLeadId,
                'vendor_lead_code' => $brainLeadId, // Store 13-digit Brain Lead ID
                'source_id' => 'BRAIN_' . $brainLeadId,
                'comments' => "Brain Lead ID: {$brainLeadId} | Updated: " . now()->format('Y-m-d H:i:s')
            ];
            
            // Add any other fields that need updating
            if ($lead->first_name) {
                $updateParams['first_name'] = $lead->first_name;
            }
            if ($lead->last_name) {
                $updateParams['last_name'] = $lead->last_name;
            }
            if ($lead->address) {
                $updateParams['address1'] = $lead->address;
            }
            if ($lead->city) {
                $updateParams['city'] = $lead->city;
            }
            if ($lead->state) {
                $updateParams['state'] = $lead->state;
            }
            if ($lead->zip_code) {
                $updateParams['postal_code'] = $lead->zip_code;
            }
            if ($lead->email) {
                $updateParams['email'] = $lead->email;
            }
            
            Log::info('Vici API: Updating lead with Brain ID', [
                'vici_lead_id' => $viciLeadId,
                'brain_lead_id' => $brainLeadId,
                'phone' => $lead->phone
            ]);
            
            // Make API call
            $url = $this->baseUrl . '?' . http_build_query($updateParams);
            $response = Http::timeout(30)->get($url);
            $responseBody = $response->body();
            
            // Check response
            if (strpos($responseBody, 'SUCCESS') !== false || strpos($responseBody, 'NOTICE: update_lead') !== false) {
                Log::info('✅ Vici Lead Updated with Brain ID via API', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'phone' => $lead->phone,
                    'response' => substr($responseBody, 0, 200)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Vici lead updated with Brain Lead ID via API',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'api_response' => substr($responseBody, 0, 500)
                ];
            } else {
                Log::warning('Vici API update returned unexpected response', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'response' => substr($responseBody, 0, 500)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'API update failed',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'api_response' => substr($responseBody, 0, 500)
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Vici API Update Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'API error: ' . $e->getMessage(),
                'brain_lead_id' => $lead->external_lead_id ?? null
            ];
        }
    }
    
    /**
     * Search for a lead in Vici by phone number
     */
    private function searchLeadByPhone(string $phone): array
    {
        try {
            // Use search_phone_list function to find lead
            $searchParams = [
                'source' => 'brain_search',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'search_phone_list',
                'phone_number' => $phone,
                'records_to_return' => 1
            ];
            
            $url = $this->baseUrl . '?' . http_build_query($searchParams);
            $response = Http::timeout(30)->get($url);
            $responseBody = $response->body();
            
            // Parse response to get lead_id
            // Response format: SUCCESS: search_phone_list RESULTS FOUND - 1|lead_id: 12345|...
            if (strpos($responseBody, 'RESULTS FOUND') !== false) {
                // Extract lead_id from response
                if (preg_match('/lead_id:\s*(\d+)/', $responseBody, $matches)) {
                    return [
                        'success' => true,
                        'lead_id' => $matches[1],
                        'response' => substr($responseBody, 0, 200)
                    ];
                }
            }
            
            // Try alternative: list all leads and search
            // This is less efficient but works if search_phone_list isn't available
            return $this->searchViaListExport($phone);
            
        } catch (\Exception $e) {
            Log::error('Vici lead search failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Alternative search method using list export
     */
    private function searchViaListExport(string $phone): array
    {
        try {
            // Use list_export_calls_report to get leads
            $exportParams = [
                'source' => 'brain_export',
                'user' => $this->apiUser,
                'pass' => $this->apiPass,
                'function' => 'list_export_calls_report',
                'list_id' => '101',
                'header' => 'YES',
                'rec_fields' => 'lead_id,phone_number,vendor_lead_code'
            ];
            
            $url = $this->baseUrl . '?' . http_build_query($exportParams);
            $response = Http::timeout(60)->get($url);
            $responseBody = $response->body();
            
            // Parse CSV response
            $lines = explode("\n", $responseBody);
            foreach ($lines as $line) {
                if (strpos($line, $phone) !== false) {
                    $fields = str_getcsv($line);
                    if (isset($fields[0]) && is_numeric($fields[0])) {
                        return [
                            'success' => true,
                            'lead_id' => $fields[0],
                            'vendor_code' => $fields[2] ?? null
                        ];
                    }
                }
            }
            
            return [
                'success' => false,
                'message' => 'Lead not found in list export'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Batch update multiple leads
     */
    public function batchUpdateLeads(array $leads, callable $progressCallback = null): array
    {
        $results = [
            'total' => count($leads),
            'updated' => 0,
            'not_found' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($leads as $index => $lead) {
            $result = $this->updateViciLeadWithBrainId($lead);
            
            if ($result['success']) {
                $results['updated']++;
            } elseif (strpos($result['message'], 'not found') !== false) {
                $results['not_found']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'lead_id' => $lead->id,
                'phone' => $lead->phone,
                'result' => $result
            ];
            
            // Call progress callback if provided
            if ($progressCallback) {
                $progressCallback($index + 1, $results['total'], $lead, $result);
            }
            
            // Small delay to avoid overwhelming the API
            usleep(100000); // 0.1 second delay
        }
        
        return $results;
    }
}


