<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiEndpoint;

class ApiEndpointSeeder extends Seeder
{
    public function run()
    {
        // Clear existing non-system endpoints
        ApiEndpoint::where('is_system', false)->delete();

        // Webhooks
        $webhooks = [
            // Lead Intake
            [
                'name' => 'Main Lead Webhook',
                'endpoint' => '/webhook.php',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'description' => 'Primary endpoint for LeadsQuotingFast (LQF) leads',
                'features' => [
                    'Accepts JSON payloads',
                    'Processes contact and data fields',
                    'Auto-pushes to Vici List 101',
                    'Stores full payload for debugging'
                ],
                'category' => 'lead_intake',
                'sort_order' => 1,
                'is_system' => true
            ],
            [
                'name' => 'Auto Insurance Webhook',
                'endpoint' => '/webhook/auto',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'description' => 'Dedicated endpoint for auto insurance leads',
                'features' => [
                    'Auto-sets type to "auto"',
                    'Validates vehicle data',
                    'Validates driver information',
                    'Routes to auto campaigns'
                ],
                'category' => 'lead_intake',
                'sort_order' => 2,
                'is_system' => true
            ],
            [
                'name' => 'Home Insurance Webhook',
                'endpoint' => '/webhook/home',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'active',
                'description' => 'Dedicated endpoint for home insurance leads',
                'features' => [
                    'Auto-sets type to "home"',
                    'Validates property data',
                    'Handles mortgage information',
                    'Routes to home campaigns'
                ],
                'category' => 'lead_intake',
                'sort_order' => 3,
                'is_system' => true
            ],
            [
                'name' => 'Webhook Status',
                'endpoint' => '/webhook/status',
                'method' => 'GET',
                'type' => 'webhook',
                'status' => 'active',
                'description' => 'Monitor webhook health and recent activity',
                'features' => [
                    'Shows last received timestamps',
                    'Displays daily counts',
                    'Checks Vici connection',
                    'Shows whitelist status'
                ],
                'test_url' => '/webhook/status',
                'category' => 'monitoring',
                'sort_order' => 4,
                'is_system' => true
            ],

            // External System Webhooks
            [
                'name' => 'Vici Webhook',
                'endpoint' => '/webhook/vici',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'inactive',
                'description' => 'Receives callbacks from Vici dialer system',
                'features' => [
                    'Processes agent dispositions',
                    'Updates lead status',
                    'Tracks call outcomes',
                    'Records agent notes'
                ],
                'category' => 'external_systems',
                'sort_order' => 10,
                'is_system' => false
            ],
            [
                'name' => 'RingBA Webhook',
                'endpoint' => '/webhook/ringba',
                'method' => 'POST',
                'type' => 'webhook',
                'status' => 'testing',
                'description' => 'Receives enriched lead data from RingBA',
                'features' => [
                    'Processes bid responses',
                    'Updates lead enrichment',
                    'Tracks revenue data',
                    'Handles buyer routing'
                ],
                'category' => 'external_systems',
                'sort_order' => 11,
                'is_system' => false
            ],
        ];

        // API Endpoints
        $apis = [
            // Lead Management
            [
                'name' => 'Get Lead by ID',
                'endpoint' => '/api/leads/{id}',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Retrieve complete lead information by ID',
                'features' => [
                    'Returns full lead data',
                    'Includes enrichment data',
                    'Shows Vici status',
                    'Includes call history'
                ],
                'category' => 'lead_management',
                'sort_order' => 20,
                'is_system' => true
            ],
            [
                'name' => 'Update Lead',
                'endpoint' => '/api/leads/{id}',
                'method' => 'PUT',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Update lead information',
                'features' => [
                    'Partial updates supported',
                    'Syncs with Vici',
                    'Validates data types',
                    'Audit trail maintained'
                ],
                'category' => 'lead_management',
                'sort_order' => 21,
                'is_system' => true
            ],
            [
                'name' => 'Search Leads',
                'endpoint' => '/api/leads/search',
                'method' => 'POST',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Search leads with advanced filters',
                'features' => [
                    'Filter by date range',
                    'Filter by source',
                    'Filter by status',
                    'Pagination support'
                ],
                'category' => 'lead_management',
                'sort_order' => 22,
                'is_system' => true
            ],

            // Analytics
            [
                'name' => 'Daily Analytics',
                'endpoint' => '/api/analytics/daily',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Get daily lead and call analytics',
                'features' => [
                    'Lead counts by hour',
                    'Conversion rates',
                    'Revenue tracking',
                    'Source performance'
                ],
                'test_url' => '/api/analytics/daily',
                'category' => 'analytics',
                'sort_order' => 30,
                'is_system' => true
            ],
            [
                'name' => 'Date Range Analytics',
                'endpoint' => '/api/analytics/{startDate}/{endDate}',
                'method' => 'GET',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Get analytics for custom date range',
                'features' => [
                    'Custom date ranges',
                    'Filter by agent',
                    'Filter by campaign',
                    'Export to CSV'
                ],
                'category' => 'analytics',
                'sort_order' => 31,
                'is_system' => true
            ],

            // External Integrations
            [
                'name' => 'Push to Vici',
                'endpoint' => '/api/vici/push',
                'method' => 'POST',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Manually push a lead to Vici dialer',
                'features' => [
                    'Force push to List 101',
                    'Override duplicate checks',
                    'Custom campaign assignment',
                    'Returns Vici lead ID'
                ],
                'category' => 'external_integrations',
                'sort_order' => 40,
                'is_system' => true
            ],
            [
                'name' => 'Update Vici Lead',
                'endpoint' => '/api/vici/update/{leadId}',
                'method' => 'PUT',
                'type' => 'api',
                'status' => 'active',
                'description' => 'Update existing lead in Vici',
                'features' => [
                    'Update by Brain ID or Vici ID',
                    'Sync vendor_lead_code',
                    'Update contact info',
                    'Add agent notes'
                ],
                'category' => 'external_integrations',
                'sort_order' => 41,
                'is_system' => true
            ],
            [
                'name' => 'Test Allstate Connection',
                'endpoint' => '/api/allstate/test',
                'method' => 'POST',
                'type' => 'api',
                'status' => 'testing',
                'description' => 'Test Allstate API connectivity',
                'features' => [
                    'Tests production endpoint',
                    'Tests test environment',
                    'Validates credentials',
                    'Returns detailed response'
                ],
                'test_url' => '/test/allstate/connection',
                'category' => 'external_integrations',
                'sort_order' => 42,
                'is_system' => false
            ],
            [
                'name' => 'Submit to Allstate',
                'endpoint' => '/api/allstate/submit',
                'method' => 'POST',
                'type' => 'api',
                'status' => 'testing',
                'description' => 'Submit qualified lead to Allstate',
                'features' => [
                    'Validates all required fields',
                    'Maps to Allstate format',
                    'Returns bid response',
                    'Tracks submission status'
                ],
                'category' => 'external_integrations',
                'sort_order' => 43,
                'is_system' => false
            ],
        ];

        // Test Utilities
        $tests = [
            // System Diagnostics
            [
                'name' => 'Diagnostics Dashboard',
                'endpoint' => '/diagnostics',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Comprehensive system diagnostics',
                'features' => [
                    'Database connection test',
                    'Vici API test',
                    'Callix whitelist test',
                    'Server information'
                ],
                'test_url' => '/diagnostics',
                'category' => 'system_diagnostics',
                'sort_order' => 50,
                'is_system' => true
            ],
            [
                'name' => 'Server Egress IP',
                'endpoint' => '/server-egress-ip',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Get server\'s public IP address',
                'features' => [
                    'Shows Render egress IP',
                    'Useful for whitelisting',
                    'Shows server hostname',
                    'Environment information'
                ],
                'test_url' => '/server-egress-ip',
                'category' => 'system_diagnostics',
                'sort_order' => 51,
                'is_system' => true
            ],
            [
                'name' => 'Database Test',
                'endpoint' => '/test-db.php',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Test database connectivity',
                'features' => [
                    'Tests PostgreSQL connection',
                    'Shows connection details',
                    'Lists recent leads',
                    'Performance metrics'
                ],
                'test_url' => '/test-db.php',
                'category' => 'system_diagnostics',
                'sort_order' => 52,
                'is_system' => true
            ],

            // Vici Testing
            [
                'name' => 'Vici Whitelist Check',
                'endpoint' => '/vici-whitelist-check.php',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Check Vici whitelist status',
                'features' => [
                    'Tests Vici connectivity',
                    'Attempts re-whitelist',
                    'Shows API response',
                    'Tests with fallback'
                ],
                'test_url' => '/vici-whitelist-check.php',
                'category' => 'vici_testing',
                'sort_order' => 60,
                'is_system' => true
            ],
            [
                'name' => 'Callix Portal Test',
                'endpoint' => '/callix-test.php',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Test Callix whitelist portal',
                'features' => [
                    'Tests authentication',
                    'Tries multiple credentials',
                    'Shows response codes',
                    'Debug information'
                ],
                'test_url' => '/callix-test.php',
                'category' => 'vici_testing',
                'sort_order' => 61,
                'is_system' => true
            ],
            [
                'name' => 'Callix Browser Test',
                'endpoint' => '/callix-browser-test.php',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Browser-like Callix authentication',
                'features' => [
                    'Simulates browser session',
                    'Handles cookies',
                    'Multiple auth attempts',
                    'Detailed logging'
                ],
                'test_url' => '/callix-browser-test.php',
                'category' => 'vici_testing',
                'sort_order' => 62,
                'is_system' => true
            ],

            // Lead Testing
            [
                'name' => 'Test Lead Generator',
                'endpoint' => '/test-leads',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Generate and submit test leads',
                'features' => [
                    'Generate random test data',
                    'Submit to any webhook',
                    'Bulk generation option',
                    'Track test results'
                ],
                'test_url' => '/test-leads',
                'category' => 'lead_testing',
                'sort_order' => 70,
                'is_system' => true
            ],
            [
                'name' => 'Agent Lead View Test',
                'endpoint' => '/agent/lead/TEST_LEAD_1',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'active',
                'description' => 'Test agent lead display interface',
                'features' => [
                    'View test lead format',
                    'Test qualification form',
                    'Check field mappings',
                    'Test enrichment buttons'
                ],
                'test_url' => '/agent/lead/TEST_LEAD_1',
                'category' => 'lead_testing',
                'sort_order' => 71,
                'is_system' => true
            ],

            // Integration Testing
            [
                'name' => 'Allstate Test Dashboard',
                'endpoint' => '/admin/allstate-testing',
                'method' => 'GET',
                'type' => 'test',
                'status' => 'testing',
                'description' => 'Allstate API testing interface',
                'features' => [
                    'Test data validation',
                    'Field mapping tests',
                    'Submit test leads',
                    'View API responses'
                ],
                'test_url' => '/admin/allstate-testing',
                'category' => 'integration_testing',
                'sort_order' => 80,
                'is_system' => false
            ],
        ];

        // Insert all endpoints
        foreach ($webhooks as $webhook) {
            ApiEndpoint::create($webhook);
        }

        foreach ($apis as $api) {
            ApiEndpoint::create($api);
        }

        foreach ($tests as $test) {
            ApiEndpoint::create($test);
        }

        $this->command->info('API endpoints seeded successfully!');
    }
}