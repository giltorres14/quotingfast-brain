#!/usr/bin/env php
<?php
/**
 * Test Script: Brain to Vici Lead Push
 * 
 * This script tests the lead flow from Brain to ViciDial
 * Run: php test_brain_to_vici_push.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use App\Services\ViciDialerService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "===========================================\n";
echo "  BRAIN → VICI LEAD PUSH TEST\n";
echo "===========================================\n\n";

// Test configuration
$testPhone = '2482205565'; // Test phone number
$testCampaign = 'AUTODIAL'; // Target campaign

echo "Test Configuration:\n";
echo "  • Phone: $testPhone\n";
echo "  • Campaign: $testCampaign\n";
echo "  • Target List: 101 (hardcoded)\n\n";

// Step 1: Create or find test lead
echo "Step 1: Creating test lead in Brain...\n";

$lead = Lead::firstOrCreate(
    ['phone' => $testPhone],
    [
        'name' => 'Test Lead',
        'first_name' => 'Test',
        'last_name' => 'Lead',
        'email' => 'test@example.com',
        'address' => '123 Test St',
        'city' => 'Detroit',
        'state' => 'MI',
        'zip_code' => '48201',
        'external_lead_id' => 'TEST_' . time(),
        'source' => 'test_script',
        'status' => 'NEW',
        'tcpajoin_date' => date('Y-m-d'), // Today's date for TCPA
        'meta' => json_encode([
            'test_run' => true,
            'created_by' => 'test_script',
            'timestamp' => now()->toIso8601String()
        ])
    ]
);

echo "  ✓ Lead created/found - ID: {$lead->id}\n";
echo "  ✓ External ID: {$lead->external_lead_id}\n\n";

// Step 2: Initialize ViciDialerService
echo "Step 2: Initializing ViciDialerService...\n";

try {
    $viciService = new ViciDialerService();
    echo "  ✓ Service initialized\n\n";
} catch (Exception $e) {
    echo "  ✗ Failed to initialize: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Push lead to Vici
echo "Step 3: Pushing lead to ViciDial...\n";

try {
    $result = $viciService->pushLead($lead, $testCampaign);
    
    if ($result['success']) {
        echo "  ✓ SUCCESS! Lead pushed to ViciDial\n";
        echo "  • Method: " . ($result['method'] ?? 'unknown') . "\n";
        echo "  • Vici Lead ID: " . ($result['vici_lead_id'] ?? 'N/A') . "\n";
        echo "  • List ID: " . ($result['list_id'] ?? 'N/A') . "\n";
        echo "  • Campaign: " . ($result['campaign_id'] ?? $testCampaign) . "\n";
        
        // Update lead record
        $lead->update([
            'vici_lead_id' => $result['vici_lead_id'] ?? null,
            'vici_pushed_at' => now(),
            'vici_list_id' => $result['list_id'] ?? '101',
            'vici_campaign' => $testCampaign
        ]);
        
        echo "  ✓ Lead record updated\n\n";
    } else {
        echo "  ✗ FAILED to push lead\n";
        echo "  • Error: " . ($result['error'] ?? 'Unknown error') . "\n";
        
        if (isset($result['vici_response'])) {
            echo "  • Response: " . json_encode($result['vici_response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "  ✗ Exception: " . $e->getMessage() . "\n";
    echo "  • Trace: " . $e->getTraceAsString() . "\n\n";
}

// Step 4: Verify in database
echo "Step 4: Verifying lead status...\n";

$lead->refresh();

echo "  • Brain Lead ID: {$lead->id}\n";
echo "  • External Lead ID: {$lead->external_lead_id}\n";
echo "  • Vici Lead ID: " . ($lead->vici_lead_id ?? 'Not set') . "\n";
echo "  • Vici List ID: " . ($lead->vici_list_id ?? 'Not set') . "\n";
echo "  • Vici Pushed At: " . ($lead->vici_pushed_at ?? 'Never') . "\n";
echo "  • TCPA Join Date: " . ($lead->tcpajoin_date ?? 'Not set') . "\n\n";

// Step 5: Check call metrics
echo "Step 5: Checking call metrics...\n";

try {
    $metrics = $viciService->getCallMetrics($lead->id);
    
    if ($metrics['success']) {
        echo "  ✓ Call metrics retrieved\n";
        echo "  • Status: " . ($metrics['data']['status'] ?? 'N/A') . "\n";
        echo "  • Called Count: " . ($metrics['data']['called_count'] ?? 0) . "\n";
        echo "  • Total Calls: " . ($metrics['data']['total_calls'] ?? 0) . "\n";
    } else {
        echo "  • No call metrics found (lead may not be in Vici yet)\n";
    }
} catch (Exception $e) {
    echo "  • Could not retrieve metrics: " . $e->getMessage() . "\n";
}

echo "\n";
echo "===========================================\n";
echo "  TEST COMPLETE\n";
echo "===========================================\n\n";

// Summary
echo "SUMMARY:\n";
echo "--------\n";
if ($lead->vici_lead_id) {
    echo "✅ Lead successfully pushed to ViciDial\n";
    echo "   • Brain ID: {$lead->id}\n";
    echo "   • Vici ID: {$lead->vici_lead_id}\n";
    echo "   • List: {$lead->vici_list_id}\n";
    echo "   • Campaign: {$lead->vici_campaign}\n";
} else {
    echo "❌ Lead was NOT pushed to ViciDial\n";
    echo "   Check logs for errors\n";
}

echo "\nNEXT STEPS:\n";
echo "-----------\n";
echo "1. Check ViciDial admin panel for the lead\n";
echo "2. Verify lead appears in List 101\n";
echo "3. Confirm vendor_lead_code = 'BRAIN_{$lead->id}'\n";
echo "4. Monitor lead progression through lists\n\n";

exit(0);

