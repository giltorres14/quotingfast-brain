<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApiEndpoint;

class ApiEndpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        ApiEndpoint::truncate();
        
        $this->seedWebhooks();
        $this->seedApiEndpoints();
        $this->seedTestUtilities();
    }
    
    private function seedWebhooks()
    {
        $webhooks = [
            [
                'name' => 'LeadsQuotingFast',
                'endpoint' => '/webhook.php',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'category' => 'lead_intake',
                'description' => 'Primary lead capture from QuotingFast platform',
                'features' => [
                    'Receives leads from LeadsQuotingFast platform',
                    'Stores lead data in PostgreSQL database',
                    'Generates external lead ID',
                    'Triggers auto-enrichment workflows',
                    'Sends leads to ViciDial automatically'
                ],
                'test_url' => '/webhook.php',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'Ringba Call Tracking',
                'endpoint' => '/webhook/ringba',
                'method' => 'POST', 
                'type' => 'webhook',
                'status' => 'active',
                'category' => 'call_tracking',
                'description' => 'Call tracking and routing integration',
                'features' => [
                    'Receives call tracking data from Ringba',
                    'Links calls to existing leads',
                    'Tracks call duration and outcomes',
                    'Updates lead status automatically'
                ],
                'test_url' => '/webhook/ringba',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'ViciDial',
                'endpoint' => '/webhook/vici',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'category' => 'crm_integration',
                'description' => 'ViciDial CRM integration callbacks',
                'features' => [
                    'Receives CRM status updates',
                    'Handles firewall authentication',
                    'Tracks lead transfer status',
                    'Retry logic for failed transfers'
                ],
                'test_url' => '/webhook/vici',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'Allstate Lead Marketplace',
                'endpoint' => '/webhook/allstate',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'category' => 'lead_marketplace',
                'description' => 'Allstate Lead Marketplace integration',
                'features' => [
                    'Transfers leads to Allstate API',
                    'Supports auto & home insurance verticals',
                    'Data normalization & validation',
                    'Real-time transfer status tracking'
                ],
                'test_url' => '/webhook/allstate',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'Twilio SMS/Voice',
                'endpoint' => '/webhook/twilio',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'category' => 'communications',
                'description' => 'SMS and voice communications webhook',
                'features' => [
                    'Handles SMS and voice callbacks',
                    'Links communications to leads',
                    'Tracks engagement metrics',
                    'Automated response workflows'
                ],
                'test_url' => '/webhook/twilio',
                'sort_order' => 1,
                'is_system' => true
            ]
        ];
        
        foreach ($webhooks as $webhook) {
            ApiEndpoint::create($webhook);
        }
    }
    
    private function seedApiEndpoints()
    {
        $apis = [
            [
                'name' => 'Health Check',
                'endpoint' => '/healthz',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'category' => 'system',
                'description' => 'System health monitoring endpoint',
                'features' => [
                    'Real-time system status',
                    'Database connectivity check',
                    'Service uptime monitoring',
                    'JSON formatted response'
                ],
                'test_url' => '/healthz',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'External Lead Lookup',
                'endpoint' => '/api/external-lead/{id}',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'category' => 'lead_management',
                'description' => 'Retrieve lead by external ID',
                'features' => [
                    'Lookup leads by external ID',
                    'JSON formatted lead data',
                    'Used by external systems',
                    'Fast indexed queries'
                ],
                'test_url' => '/api/external-lead/100411509',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'Quick Analytics',
                'endpoint' => '/api/analytics/quick/{period}',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'category' => 'analytics',
                'description' => 'Fast analytics for common time periods',
                'features' => [
                    'Fast analytics for common time periods',
                    'Supports: today, week, month, quarter',
                    'Lead volume and conversion metrics',
                    'Cached for performance'
                ],
                'test_url' => '/api/analytics/quick/today',
                'sort_order' => 1,
                'is_system' => true
            ]
        ];
        
        foreach ($apis as $api) {
            ApiEndpoint::create($api);
        }
    }
    
    private function seedTestUtilities()
    {
        $tests = [
            [
                'name' => 'Allstate Connection Test',
                'endpoint' => '/test/allstate/connection',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'testing',
                'category' => 'testing',
                'description' => 'Test Allstate API connection and authentication',
                'features' => [
                    'Test Allstate API authentication',
                    'Verify API endpoints and verticals',
                    'Debug connection issues',
                    'Environment switching support'
                ],
                'test_url' => '/test/allstate/connection',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'ViciDial Integration Test',
                'endpoint' => '/test/vici/{leadId}',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'testing',
                'category' => 'testing',
                'description' => 'Test ViciDial integration with specific lead',
                'features' => [
                    'Test ViciDial API connection',
                    'Verify firewall authentication',
                    'Test lead submission process',
                    'Debug transfer issues'
                ],
                'test_url' => '/test/vici/1',
                'sort_order' => 2,
                'is_system' => true
            ]
        ];
        
        foreach ($tests as $test) {
            ApiEndpoint::create($test);
        }
    }
}
