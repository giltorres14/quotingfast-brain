#!/usr/bin/env php
<?php
/**
 * Test Vici with the ACTUAL working credentials from August 8th
 */

echo "\n=== VICI TEST WITH CORRECT CREDENTIALS ===\n\n";

// These are the credentials that ACTUALLY worked on August 8th
$credentials = [
    'user' => 'apiuser',
    'pass' => 'UZPATJ59GJAVKG8ES6'
];

echo "Testing with credentials that worked on August 8th:\n";
echo "User: {$credentials['user']}\n";
echo "Pass: {$credentials['pass']}\n\n";

// Test 1: Version check
echo "1. VERSION CHECK:\n";
$params = array_merge($credentials, [
    'source' => 'BRAIN',
    'function' => 'version'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://philli.callix.ai/vicidial/non_agent_api.php',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
curl_close($ch);

echo "Response: $response\n";

if (strpos($response, 'VERSION') !== false) {
    echo "✅ Version check successful!\n\n";
    
    // Test 2: Add lead
    echo "2. ADD LEAD TEST:\n";
    
    $leadParams = array_merge($credentials, [
        'source' => 'LQF_API',
        'function' => 'add_lead',
        'phone_number' => '2482205565',
        'phone_code' => '1',
        'list_id' => '101',
        'vendor_lead_code' => 'TEST_' . time(),
        'first_name' => 'Test',
        'last_name' => 'Lead',
        'address1' => '123 Test St',
        'city' => 'Detroit',
        'state' => 'MI',
        'postal_code' => '48201',
        'email' => 'test@example.com'
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://philli.callix.ai/vicidial/non_agent_api.php',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($leadParams),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "Response: $response\n";
    
    if (strpos($response, 'SUCCESS') !== false) {
        echo "✅ LEAD SUCCESSFULLY ADDED!\n";
        
        if (preg_match('/SUCCESS.*\|(\d+)\|(\d+)\|/', $response, $matches)) {
            echo "   List ID: {$matches[1]}\n";
            echo "   Vici Lead ID: {$matches[2]}\n";
        }
    } else {
        echo "❌ Failed to add lead\n";
    }
} else {
    echo "❌ Version check failed - wrong credentials\n";
}

echo "\n";
