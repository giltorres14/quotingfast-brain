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