<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CRMIntegrationService
{
    protected $supportedCRMs = [
        'allstate_lead_manager' => 'Allstate Lead Manager',
        'ricochet360' => 'Ricochet360',
        'salesforce' => 'Salesforce',
        'hubspot' => 'HubSpot',
        'pipedrive' => 'Pipedrive',
        'zoho' => 'Zoho CRM',
        'dynamics' => 'Microsoft Dynamics',
        'freshsales' => 'Freshsales',
        'activecampaign' => 'ActiveCampaign',
        'gohighlevel' => 'GoHighLevel',
        'webhook' => 'Custom Webhook'
    ];

    /**
     * Get supported CRM list
     */
    public function getSupportedCRMs()
    {
        return $this->supportedCRMs;
    }

    /**
     * Send lead to buyer's CRM
     */
    public function sendLeadToCRM($buyerId, $leadData)
    {
        $buyer = Buyer::find($buyerId);
        if (!$buyer) {
            return ['success' => false, 'error' => 'Buyer not found'];
        }

        $crmConfig = $buyer->crm_config ?? [];
        if (empty($crmConfig) || !isset($crmConfig['enabled']) || !$crmConfig['enabled']) {
            return ['success' => false, 'error' => 'CRM integration not enabled'];
        }

        $crmType = $crmConfig['type'] ?? null;
        if (!$crmType || !isset($this->supportedCRMs[$crmType])) {
            return ['success' => false, 'error' => 'Unsupported CRM type'];
        }

        try {
            $result = $this->sendToCRMType($crmType, $crmConfig, $leadData, $buyer);
            
            // Log the result
            Log::info("CRM integration attempt", [
                'buyer_id' => $buyerId,
                'crm_type' => $crmType,
                'success' => $result['success'],
                'lead_id' => $leadData['external_lead_id'] ?? 'unknown'
            ]);

            // Update buyer's CRM stats
            $this->updateCRMStats($buyer, $result['success']);

            return $result;

        } catch (\Exception $e) {
            Log::error("CRM integration failed", [
                'buyer_id' => $buyerId,
                'crm_type' => $crmType,
                'error' => $e->getMessage(),
                'lead_id' => $leadData['external_lead_id'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => 'CRM integration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send to specific CRM type
     */
    private function sendToCRMType($crmType, $crmConfig, $leadData, $buyer)
    {
        switch ($crmType) {
            case 'allstate_lead_manager':
                return $this->sendToAllstateLeadManager($crmConfig, $leadData, $buyer);
            case 'ricochet360':
                return $this->sendToRicochet360($crmConfig, $leadData, $buyer);
            case 'salesforce':
                return $this->sendToSalesforce($crmConfig, $leadData, $buyer);
            case 'hubspot':
                return $this->sendToHubSpot($crmConfig, $leadData, $buyer);
            case 'pipedrive':
                return $this->sendToPipedrive($crmConfig, $leadData, $buyer);
            case 'zoho':
                return $this->sendToZoho($crmConfig, $leadData, $buyer);
            case 'dynamics':
                return $this->sendToDynamics($crmConfig, $leadData, $buyer);
            case 'freshsales':
                return $this->sendToFreshsales($crmConfig, $leadData, $buyer);
            case 'activecampaign':
                return $this->sendToActiveCampaign($crmConfig, $leadData, $buyer);
            case 'gohighlevel':
                return $this->sendToGoHighLevel($crmConfig, $leadData, $buyer);
            case 'webhook':
                return $this->sendToWebhook($crmConfig, $leadData, $buyer);
            default:
                return ['success' => false, 'error' => 'Unsupported CRM type'];
        }
    }

    /**
     * Send to Allstate Lead Manager
     */
    private function sendToAllstateLeadManager($config, $leadData, $buyer)
    {
        $postingUrl = $config['posting_url'] ?? '';
        $providerId = $config['provider_id'] ?? '';
        $leadType = $config['lead_type'] ?? 'Auto'; // Auto, Home, Renter, LiveTransfer-Auto, LiveTransfer-Home
        
        if (empty($postingUrl) || empty($providerId)) {
            return ['success' => false, 'error' => 'Missing required Allstate Lead Manager configuration'];
        }
        
        // Build the JSON payload according to Allstate Lead Manager API spec
        $payload = [
            // Required fields
            'ProviderId' => $providerId,
            'LeadType' => $leadType,
            'FirstName' => $leadData['first_name'] ?? '',
            'LastName' => $leadData['last_name'] ?? '',
            'Address1' => $leadData['address'] ?? ($leadData['street_address'] ?? ''),
            'City' => $leadData['city'] ?? '',
            'State' => $leadData['state'] ?? '',
            'ZipCode' => $leadData['zip_code'] ?? ($leadData['zip'] ?? ''),
        ];
        
        // Optional fields - only include if they have values
        $optionalFields = [
            'ProviderLeadId' => $leadData['external_lead_id'] ?? $leadData['id'] ?? null,
            'Address2' => $leadData['address2'] ?? null,
            'HomePhone' => $leadData['home_phone'] ?? $leadData['phone'] ?? null,
            'MobilePhone' => $leadData['mobile_phone'] ?? $leadData['cell_phone'] ?? null,
            'WorkPhone' => $leadData['work_phone'] ?? null,
            'WorkExt' => $leadData['work_ext'] ?? null,
            'EmailAddress' => $leadData['email'] ?? null,
            'AltEmailAddress' => $leadData['alt_email'] ?? null,
            'Website' => $leadData['website'] ?? null,
            'PolicyExpirationDate' => $leadData['policy_expiration_date'] ?? null,
            'DOB' => $leadData['date_of_birth'] ?? $leadData['dob'] ?? null,
            'MaritalStatus' => $leadData['marital_status'] ?? null,
            'Homeowner' => isset($leadData['homeowner']) ? (bool)$leadData['homeowner'] : null,
            'Renter' => isset($leadData['renter']) ? (bool)$leadData['renter'] : null,
        ];
        
        // Add Home insurance specific fields
        if ($leadType === 'Home') {
            $homeFields = [
                'HomePersonalPty' => $leadData['home_personal_property'] ?? null,
                'HomeCurrentInsured' => isset($leadData['home_current_insured']) ? (bool)$leadData['home_current_insured'] : null,
                'HomeCurrentCarrier' => $leadData['home_current_carrier'] ?? null,
                'YearBuilt' => $leadData['year_built'] ?? null,
                'PurchaseDate' => $leadData['purchase_date'] ?? null,
                'ConstructionType' => $leadData['construction_type'] ?? null,
                'GarageType' => $leadData['garage_type'] ?? null,
                'Stories' => $leadData['stories'] ?? null,
                'Baths' => $leadData['baths'] ?? null,
                'Bedrooms' => $leadData['bedrooms'] ?? null,
                'SqFootage' => $leadData['sq_footage'] ?? null,
                'RoofType' => $leadData['roof_type'] ?? null,
                'AgeOfRoof' => $leadData['age_of_roof'] ?? null,
                'BurglarAlarm' => isset($leadData['burglar_alarm']) ? (bool)$leadData['burglar_alarm'] : null,
            ];
            $optionalFields = array_merge($optionalFields, $homeFields);
        }
        
        // Add Auto insurance specific fields
        if ($leadType === 'Auto' || $leadType === 'LiveTransfer-Auto') {
            $autoFields = [
                'AutoInsured' => isset($leadData['auto_insured']) ? (bool)$leadData['auto_insured'] : null,
                'AutoCurrentCarrier' => $leadData['auto_current_carrier'] ?? null,
            ];
            
            // Add up to 4 vehicles
            for ($i = 1; $i <= 4; $i++) {
                $autoFields["Auto{$i}Make"] = $leadData["auto_{$i}_make"] ?? null;
                $autoFields["Auto{$i}Model"] = $leadData["auto_{$i}_model"] ?? null;
                $autoFields["Auto{$i}Year"] = $leadData["auto_{$i}_year"] ?? null;
                $autoFields["Auto{$i}Vin"] = $leadData["auto_{$i}_vin"] ?? null;
                $autoFields["Auto{$i}Trim"] = $leadData["auto_{$i}_trim"] ?? null;
            }
            
            $optionalFields = array_merge($optionalFields, $autoFields);
        }
        
        // Add non-null optional fields to payload
        foreach ($optionalFields as $key => $value) {
            if ($value !== null && $value !== '') {
                $payload[$key] = $value;
            }
        }
        
        // Add additional fields if any custom data exists
        $additionalFields = [];
        foreach ($leadData as $key => $value) {
            // Skip fields we've already mapped
            if (!in_array($key, ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'zip'])) {
                // Convert snake_case to Title Case for display
                $displayKey = ucwords(str_replace('_', ' ', $key));
                $additionalFields[$displayKey] = $value;
            }
        }
        
        if (!empty($additionalFields)) {
            $payload['AdditionalFields'] = $additionalFields;
        }
        
        // Send the request
        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($postingUrl, $payload);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'crm_id' => 'LML_' . uniqid(),
                'response_code' => $response->status(),
                'response' => $response->json(),
                'message' => 'Lead successfully sent to Allstate Lead Manager'
            ];
        } else {
            return [
                'success' => false,
                'error_code' => $response->status(),
                'error' => 'Failed to send lead to Allstate Lead Manager: ' . $response->body(),
                'response' => $response->body()
            ];
        }
    }

    /**
     * Send to Ricochet360
     */
    private function sendToRicochet360($config, $leadData, $buyer)
    {
        $apiUrl = $config['api_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';
        $listId = $config['list_id'] ?? '';
        
        if (empty($apiUrl) || empty($apiKey)) {
            return ['success' => false, 'error' => 'Missing required Ricochet360 configuration (API URL or API Key)'];
        }
        
        // Build the JSON payload for Ricochet360 lead posting
        $payload = [
            'api_key' => $apiKey,
            'lead' => [
                'first_name' => $leadData['first_name'] ?? '',
                'last_name' => $leadData['last_name'] ?? '',
                'email' => $leadData['email'] ?? '',
                'phone' => $leadData['phone'] ?? ($leadData['home_phone'] ?? $leadData['mobile_phone'] ?? ''),
                'address' => $leadData['address'] ?? ($leadData['street_address'] ?? ''),
                'city' => $leadData['city'] ?? '',
                'state' => $leadData['state'] ?? '',
                'zip_code' => $leadData['zip_code'] ?? ($leadData['zip'] ?? ''),
                'source' => 'QuotingFast Brain',
                'status' => 'new',
                'notes' => 'Lead imported from QuotingFast Brain CRM Integration',
                'external_id' => $leadData['external_lead_id'] ?? $leadData['id'] ?? null,
            ]
        ];
        
        // Add list ID if provided
        if ($listId) {
            $payload['list_id'] = $listId;
        }
        
        // Add optional contact fields
        $optionalFields = [
            'mobile_phone' => $leadData['mobile_phone'] ?? $leadData['cell_phone'] ?? null,
            'work_phone' => $leadData['work_phone'] ?? null,
            'alt_email' => $leadData['alt_email'] ?? null,
            'company' => $leadData['company'] ?? null,
            'website' => $leadData['website'] ?? null,
            'date_of_birth' => $leadData['date_of_birth'] ?? $leadData['dob'] ?? null,
            'lead_type' => $leadData['vertical'] ?? $leadData['lead_type'] ?? null,
        ];
        
        // Add non-null optional fields to lead data
        foreach ($optionalFields as $key => $value) {
            if ($value !== null && $value !== '') {
                $payload['lead'][$key] = $value;
            }
        }
        
        // Add custom fields if any
        $customFields = [];
        foreach ($leadData as $key => $value) {
            // Skip fields we've already mapped
            if (!in_array($key, ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'zip', 'id', 'external_lead_id'])) {
                $customFields[$key] = $value;
            }
        }
        
        if (!empty($customFields)) {
            $payload['lead']['custom_fields'] = $customFields;
        }
        
        // Send the request to Ricochet360 API
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'QuotingFast-Brain/1.0'
            ])
            ->post($apiUrl, $payload);
        
        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => true,
                'crm_id' => $responseData['lead_id'] ?? ('R360_' . uniqid()),
                'response_code' => $response->status(),
                'response' => $responseData,
                'message' => 'Lead successfully sent to Ricochet360'
            ];
        } else {
            return [
                'success' => false,
                'error_code' => $response->status(),
                'error' => 'Failed to send lead to Ricochet360: ' . $response->body(),
                'response' => $response->body()
            ];
        }
    }

    /**
     * Send to Salesforce
     */
    private function sendToSalesforce($config, $leadData, $buyer)
    {
        $instanceUrl = $config['instance_url'] ?? '';
        $accessToken = $config['access_token'] ?? '';

        if (!$instanceUrl || !$accessToken) {
            return ['success' => false, 'error' => 'Missing Salesforce credentials'];
        }

        // Map lead data to Salesforce format
        $salesforceData = $this->mapToSalesforce($leadData, $config);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post($instanceUrl . '/services/data/v58.0/sobjects/Lead', $salesforceData);

        if ($response->successful()) {
            return [
                'success' => true,
                'crm_id' => $response->json()['id'] ?? null,
                'response' => $response->json()
            ];
        }

        return [
            'success' => false,
            'error' => 'Salesforce API error: ' . $response->body(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Send to HubSpot
     */
    private function sendToHubSpot($config, $leadData, $buyer)
    {
        $apiKey = $config['api_key'] ?? '';
        $portalId = $config['portal_id'] ?? '';

        if (!$apiKey) {
            return ['success' => false, 'error' => 'Missing HubSpot API key'];
        }

        // Map lead data to HubSpot format
        $hubspotData = $this->mapToHubSpot($leadData, $config);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.hubapi.com/crm/v3/objects/contacts', $hubspotData);

        if ($response->successful()) {
            return [
                'success' => true,
                'crm_id' => $response->json()['id'] ?? null,
                'response' => $response->json()
            ];
        }

        return [
            'success' => false,
            'error' => 'HubSpot API error: ' . $response->body(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Send to Pipedrive
     */
    private function sendToPipedrive($config, $leadData, $buyer)
    {
        $apiToken = $config['api_token'] ?? '';
        $domain = $config['domain'] ?? '';

        if (!$apiToken || !$domain) {
            return ['success' => false, 'error' => 'Missing Pipedrive credentials'];
        }

        // Map lead data to Pipedrive format
        $pipedriveData = $this->mapToPipedrive($leadData, $config);

        $response = Http::post("https://{$domain}.pipedrive.com/api/v1/persons?api_token={$apiToken}", $pipedriveData);

        if ($response->successful()) {
            $personId = $response->json()['data']['id'] ?? null;
            
            // Create deal if person created successfully
            if ($personId) {
                $dealData = [
                    'title' => 'Insurance Lead - ' . ($leadData['first_name'] ?? 'Unknown'),
                    'person_id' => $personId,
                    'value' => $leadData['estimated_value'] ?? 0,
                    'currency' => 'USD'
                ];
                
                Http::post("https://{$domain}.pipedrive.com/api/v1/deals?api_token={$apiToken}", $dealData);
            }

            return [
                'success' => true,
                'crm_id' => $personId,
                'response' => $response->json()
            ];
        }

        return [
            'success' => false,
            'error' => 'Pipedrive API error: ' . $response->body(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Send to Zoho CRM
     */
    private function sendToZoho($config, $leadData, $buyer)
    {
        $accessToken = $config['access_token'] ?? '';
        $orgId = $config['org_id'] ?? '';

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Missing Zoho access token'];
        }

        // Map lead data to Zoho format
        $zohoData = $this->mapToZoho($leadData, $config);

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post('https://www.zohoapis.com/crm/v2/Leads', ['data' => [$zohoData]]);

        if ($response->successful()) {
            $responseData = $response->json();
            $leadId = $responseData['data'][0]['details']['id'] ?? null;

            return [
                'success' => true,
                'crm_id' => $leadId,
                'response' => $responseData
            ];
        }

        return [
            'success' => false,
            'error' => 'Zoho API error: ' . $response->body(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Send to Custom Webhook
     */
    private function sendToWebhook($config, $leadData, $buyer)
    {
        $webhookUrl = $config['webhook_url'] ?? '';
        $headers = $config['headers'] ?? [];
        $authMethod = $config['auth_method'] ?? 'none';

        if (!$webhookUrl) {
            return ['success' => false, 'error' => 'Missing webhook URL'];
        }

        // Prepare headers
        $requestHeaders = ['Content-Type' => 'application/json'];
        
        // Add authentication
        if ($authMethod === 'bearer' && isset($config['bearer_token'])) {
            $requestHeaders['Authorization'] = 'Bearer ' . $config['bearer_token'];
        } elseif ($authMethod === 'api_key' && isset($config['api_key'])) {
            $requestHeaders['X-API-Key'] = $config['api_key'];
        } elseif ($authMethod === 'basic' && isset($config['username']) && isset($config['password'])) {
            $requestHeaders['Authorization'] = 'Basic ' . base64_encode($config['username'] . ':' . $config['password']);
        }

        // Add custom headers
        foreach ($headers as $key => $value) {
            $requestHeaders[$key] = $value;
        }

        // Map data according to custom mapping
        $webhookData = $this->mapToWebhook($leadData, $config);

        $response = Http::withHeaders($requestHeaders)
            ->timeout(30)
            ->post($webhookUrl, $webhookData);

        if ($response->successful()) {
            return [
                'success' => true,
                'crm_id' => $response->json()['id'] ?? 'webhook_' . uniqid(),
                'response' => $response->json()
            ];
        }

        return [
            'success' => false,
            'error' => 'Webhook error: ' . $response->body(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Map lead data to Salesforce format
     */
    private function mapToSalesforce($leadData, $config)
    {
        $mapping = $config['field_mapping'] ?? [];
        
        $defaultMapping = [
            'FirstName' => $leadData['first_name'] ?? '',
            'LastName' => $leadData['last_name'] ?? '',
            'Email' => $leadData['email'] ?? '',
            'Phone' => $leadData['phone'] ?? '',
            'Company' => $leadData['company'] ?? 'Unknown',
            'Street' => $leadData['address'] ?? '',
            'City' => $leadData['city'] ?? '',
            'State' => $leadData['state'] ?? '',
            'PostalCode' => $leadData['zip'] ?? '',
            'LeadSource' => 'QuotingFast',
            'Status' => 'New',
            'Description' => 'Insurance lead from QuotingFast - ' . ($leadData['vertical'] ?? 'Insurance')
        ];

        // Apply custom mapping if provided
        foreach ($mapping as $salesforceField => $brainField) {
            if (isset($leadData[$brainField])) {
                $defaultMapping[$salesforceField] = $leadData[$brainField];
            }
        }

        return $defaultMapping;
    }

    /**
     * Map lead data to HubSpot format
     */
    private function mapToHubSpot($leadData, $config)
    {
        $mapping = $config['field_mapping'] ?? [];
        
        $properties = [
            'firstname' => $leadData['first_name'] ?? '',
            'lastname' => $leadData['last_name'] ?? '',
            'email' => $leadData['email'] ?? '',
            'phone' => $leadData['phone'] ?? '',
            'address' => $leadData['address'] ?? '',
            'city' => $leadData['city'] ?? '',
            'state' => $leadData['state'] ?? '',
            'zip' => $leadData['zip'] ?? '',
            'hs_lead_status' => 'NEW',
            'lifecyclestage' => 'lead',
            'lead_source' => 'QuotingFast'
        ];

        // Apply custom mapping
        foreach ($mapping as $hubspotField => $brainField) {
            if (isset($leadData[$brainField])) {
                $properties[$hubspotField] = $leadData[$brainField];
            }
        }

        return ['properties' => $properties];
    }

    /**
     * Map lead data to Pipedrive format
     */
    private function mapToPipedrive($leadData, $config)
    {
        return [
            'name' => ($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? ''),
            'email' => [['value' => $leadData['email'] ?? '', 'primary' => true]],
            'phone' => [['value' => $leadData['phone'] ?? '', 'primary' => true]],
            'add_time' => date('Y-m-d H:i:s'),
            'visible_to' => '3', // Everyone
            'owner_id' => $config['owner_id'] ?? null
        ];
    }

    /**
     * Map lead data to Zoho format
     */
    private function mapToZoho($leadData, $config)
    {
        return [
            'First_Name' => $leadData['first_name'] ?? '',
            'Last_Name' => $leadData['last_name'] ?? '',
            'Email' => $leadData['email'] ?? '',
            'Phone' => $leadData['phone'] ?? '',
            'Street' => $leadData['address'] ?? '',
            'City' => $leadData['city'] ?? '',
            'State' => $leadData['state'] ?? '',
            'Zip_Code' => $leadData['zip'] ?? '',
            'Lead_Source' => 'QuotingFast',
            'Lead_Status' => 'Not Contacted',
            'Company' => $leadData['company'] ?? 'Unknown'
        ];
    }

    /**
     * Map lead data to webhook format
     */
    private function mapToWebhook($leadData, $config)
    {
        $mapping = $config['field_mapping'] ?? [];
        
        if (empty($mapping)) {
            // Return raw lead data if no mapping specified
            return $leadData;
        }

        $mappedData = [];
        foreach ($mapping as $webhookField => $brainField) {
            if (isset($leadData[$brainField])) {
                $mappedData[$webhookField] = $leadData[$brainField];
            }
        }

        return $mappedData;
    }

    /**
     * Update buyer's CRM statistics
     */
    private function updateCRMStats($buyer, $success)
    {
        $stats = $buyer->crm_stats ?? [];
        
        $stats['total_attempts'] = ($stats['total_attempts'] ?? 0) + 1;
        if ($success) {
            $stats['successful_deliveries'] = ($stats['successful_deliveries'] ?? 0) + 1;
        } else {
            $stats['failed_deliveries'] = ($stats['failed_deliveries'] ?? 0) + 1;
        }
        
        $stats['success_rate'] = round(($stats['successful_deliveries'] / $stats['total_attempts']) * 100, 2);
        $stats['last_attempt'] = now()->toISOString();

        $buyer->update(['crm_stats' => $stats]);
    }

    /**
     * Test CRM connection
     */
    public function testCRMConnection($buyerId, $crmConfig)
    {
        $crmType = $crmConfig['type'] ?? null;
        
        try {
            switch ($crmType) {
                case 'allstate_lead_manager':
                    return $this->testAllstateLeadManagerConnection($crmConfig);
                case 'ricochet360':
                    return $this->testRicochet360Connection($crmConfig);
                case 'salesforce':
                    return $this->testSalesforceConnection($crmConfig);
                case 'hubspot':
                    return $this->testHubSpotConnection($crmConfig);
                case 'pipedrive':
                    return $this->testPipedriveConnection($crmConfig);
                case 'webhook':
                    return $this->testWebhookConnection($crmConfig);
                default:
                    return ['success' => false, 'error' => 'Connection test not available for this CRM'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test Allstate Lead Manager connection
     */
    private function testAllstateLeadManagerConnection($config)
    {
        $postingUrl = $config['posting_url'] ?? '';
        $providerId = $config['provider_id'] ?? '';
        
        if (empty($postingUrl) || empty($providerId)) {
            return [
                'success' => false,
                'error' => 'Missing required configuration (posting_url or provider_id)'
            ];
        }
        
        // Send a test lead
        $testPayload = [
            'ProviderId' => $providerId,
            'LeadType' => 'Auto',
            'FirstName' => 'Test',
            'LastName' => 'Lead',
            'Address1' => '123 Test St',
            'City' => 'Test City',
            'State' => 'TX',
            'ZipCode' => '12345',
            'EmailAddress' => 'test@quotingfast.com',
            'HomePhone' => '555-555-5555',
            'AdditionalFields' => [
                'Test Field' => 'This is a test lead from QuotingFast Brain CRM Integration'
            ]
        ];
        
        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($postingUrl, $testPayload);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Allstate Lead Manager',
                'test_data' => [
                    'response_code' => $response->status(),
                    'posting_url' => $postingUrl,
                    'provider_id' => $providerId,
                    'response_body' => $response->body()
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Connection test failed: HTTP ' . $response->status() . ' - ' . $response->body(),
                'error_code' => $response->status(),
                'response_body' => $response->body()
            ];
        }
    }

    /**
     * Test Ricochet360 connection
     */
    private function testRicochet360Connection($config)
    {
        $apiUrl = $config['api_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';
        
        if (empty($apiUrl) || empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'Missing required configuration (API URL or API Key)'
            ];
        }
        
        // Send a test lead to Ricochet360
        $testPayload = [
            'api_key' => $apiKey,
            'lead' => [
                'first_name' => 'Test',
                'last_name' => 'Lead',
                'email' => 'test@quotingfast.com',
                'phone' => '555-555-5555',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TX',
                'zip_code' => '12345',
                'source' => 'QuotingFast Brain - Connection Test',
                'status' => 'test',
                'notes' => 'This is a test lead from QuotingFast Brain CRM Integration test',
                'external_id' => 'TEST_' . uniqid(),
            ]
        ];
        
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'QuotingFast-Brain/1.0'
            ])
            ->post($apiUrl, $testPayload);
        
        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'success' => true,
                'message' => 'Successfully connected to Ricochet360',
                'test_data' => [
                    'response_code' => $response->status(),
                    'api_url' => $apiUrl,
                    'response_body' => $responseData,
                    'lead_id' => $responseData['lead_id'] ?? 'Unknown'
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Connection test failed: HTTP ' . $response->status() . ' - ' . $response->body(),
                'error_code' => $response->status(),
                'response_body' => $response->body()
            ];
        }
    }

    /**
     * Test Salesforce connection
     */
    private function testSalesforceConnection($config)
    {
        $instanceUrl = $config['instance_url'] ?? '';
        $accessToken = $config['access_token'] ?? '';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken
        ])->get($instanceUrl . '/services/data/v58.0/sobjects/Lead/describe');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Salesforce connection successful'];
        }

        return ['success' => false, 'error' => 'Failed to connect to Salesforce'];
    }

    /**
     * Test HubSpot connection
     */
    private function testHubSpotConnection($config)
    {
        $apiKey = $config['api_key'] ?? '';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey
        ])->get('https://api.hubapi.com/crm/v3/objects/contacts?limit=1');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'HubSpot connection successful'];
        }

        return ['success' => false, 'error' => 'Failed to connect to HubSpot'];
    }

    /**
     * Test Pipedrive connection
     */
    private function testPipedriveConnection($config)
    {
        $apiToken = $config['api_token'] ?? '';
        $domain = $config['domain'] ?? '';

        $response = Http::get("https://{$domain}.pipedrive.com/api/v1/users/me?api_token={$apiToken}");

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Pipedrive connection successful'];
        }

        return ['success' => false, 'error' => 'Failed to connect to Pipedrive'];
    }

    /**
     * Test webhook connection
     */
    private function testWebhookConnection($config)
    {
        $webhookUrl = $config['webhook_url'] ?? '';
        
        $testData = [
            'test' => true,
            'message' => 'QuotingFast CRM integration test',
            'timestamp' => now()->toISOString()
        ];

        $response = Http::timeout(10)->post($webhookUrl, $testData);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Webhook connection successful'];
        }

        return ['success' => false, 'error' => 'Webhook connection failed'];
    }

    /**
     * Get CRM integration templates
     */
    public function getCRMTemplates()
    {
        return [
            'allstate_lead_manager' => [
                'name' => 'Allstate Lead Manager',
                'fields' => [
                    'posting_url' => 'Posting URL (e.g., https://www.leadmanagementlab.com/api/accounts/abc123/leads/)',
                    'provider_id' => 'Provider ID (unique code from LML setup)',
                    'lead_type' => 'Default Lead Type (Auto, Home, Renter, LiveTransfer-Auto, LiveTransfer-Home)'
                ],
                'auth_type' => 'provider_id',
                'documentation' => 'Lead Manager Lead Post Integration API v1.6',
                'supported_fields' => [
                    'required' => ['ProviderId', 'LeadType', 'FirstName', 'LastName', 'Address1', 'City', 'State', 'ZipCode'],
                    'optional_contact' => ['HomePhone', 'MobilePhone', 'WorkPhone', 'EmailAddress', 'AltEmailAddress'],
                    'optional_personal' => ['DOB', 'MaritalStatus', 'Homeowner', 'Renter'],
                    'home_insurance' => ['HomeCurrentCarrier', 'YearBuilt', 'ConstructionType', 'GarageType', 'Stories', 'Baths', 'Bedrooms', 'SqFootage', 'RoofType', 'AgeOfRoof', 'BurglarAlarm'],
                    'auto_insurance' => ['AutoInsured', 'AutoCurrentCarrier', 'Auto1Make', 'Auto1Model', 'Auto1Year', 'Auto1Vin', 'Auto1Trim', 'Auto2Make', 'Auto2Model', 'Auto2Year', 'Auto3Make', 'Auto3Model', 'Auto3Year', 'Auto4Make', 'Auto4Model', 'Auto4Year']
                ]
            ],
            'ricochet360' => [
                'name' => 'Ricochet360',
                'fields' => [
                    'api_url' => 'API URL (e.g., https://yourcompany.ricochet360.com/api/leads)',
                    'api_key' => 'API Key (from your Ricochet360 account)',
                    'list_id' => 'List ID (optional - specific lead list to add leads to)'
                ],
                'auth_type' => 'api_key',
                'documentation' => 'Ricochet360 RESTful API for Lead Management',
                'supported_fields' => [
                    'required' => ['first_name', 'last_name', 'email', 'phone'],
                    'optional_contact' => ['mobile_phone', 'work_phone', 'alt_email', 'address', 'city', 'state', 'zip_code'],
                    'optional_business' => ['company', 'website', 'lead_type', 'source'],
                    'optional_personal' => ['date_of_birth', 'notes'],
                    'system' => ['status', 'external_id', 'custom_fields']
                ],
                'features' => [
                    'Real-time lead capture',
                    'Automatic lead distribution',
                    'Custom field support',
                    'Lead status tracking',
                    'Integration with auto dialer',
                    'CRM & marketing automation'
                ]
            ],
            'salesforce' => [
                'name' => 'Salesforce',
                'fields' => [
                    'instance_url' => 'Instance URL (e.g., https://yourorg.salesforce.com)',
                    'access_token' => 'Access Token',
                    'field_mapping' => 'Field Mapping (optional)'
                ],
                'auth_type' => 'oauth',
                'documentation' => 'https://developer.salesforce.com/docs/api-explorer/sobject/Lead'
            ],
            'hubspot' => [
                'name' => 'HubSpot',
                'fields' => [
                    'api_key' => 'Private App Token',
                    'portal_id' => 'Portal ID (optional)',
                    'field_mapping' => 'Field Mapping (optional)'
                ],
                'auth_type' => 'api_key',
                'documentation' => 'https://developers.hubspot.com/docs/api/crm/contacts'
            ],
            'pipedrive' => [
                'name' => 'Pipedrive',
                'fields' => [
                    'domain' => 'Company Domain',
                    'api_token' => 'API Token',
                    'owner_id' => 'Default Owner ID (optional)'
                ],
                'auth_type' => 'api_key',
                'documentation' => 'https://developers.pipedrive.com/docs/api/v1'
            ],
            'webhook' => [
                'name' => 'Custom Webhook',
                'fields' => [
                    'webhook_url' => 'Webhook URL',
                    'auth_method' => 'Authentication Method',
                    'field_mapping' => 'Field Mapping',
                    'headers' => 'Custom Headers (optional)'
                ],
                'auth_type' => 'various',
                'documentation' => 'Custom webhook integration'
            ]
        ];
    }
}