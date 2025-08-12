<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ApiEndpoint;
use Illuminate\Support\Facades\DB;

echo "Updating API Directory...\n";

try {
    // Start transaction
    DB::beginTransaction();

    // Clear existing endpoints (preserve any custom ones)
    ApiEndpoint::where('is_system', true)->delete();
    echo "Cleared existing system endpoints.\n";

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
            'name' => 'Webhook Status Monitor',
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
            'name' => 'Push Lead to Vici',
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
            'category' => 'vici_integration',
            'sort_order' => 30,
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
            'category' => 'vici_integration',
            'sort_order' => 31,
            'is_system' => true
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
    ];

    // Insert all endpoints
    $count = 0;
    foreach ($webhooks as $webhook) {
        ApiEndpoint::create($webhook);
        $count++;
    }
    echo "Created $count webhook endpoints.\n";

    $count = 0;
    foreach ($apis as $api) {
        ApiEndpoint::create($api);
        $count++;
    }
    echo "Created $count API endpoints.\n";

    $count = 0;
    foreach ($tests as $test) {
        ApiEndpoint::create($test);
        $count++;
    }
    echo "Created $count test utilities.\n";

    // Commit transaction
    DB::commit();
    
    echo "\n✅ API Directory updated successfully!\n";
    echo "Visit https://quotingfast-brain-ohio.onrender.com/api-directory to see the updated directory.\n";

} catch (\Exception $e) {
    DB::rollback();
    echo "\n❌ Error updating API directory: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

