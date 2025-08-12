#!/usr/bin/env php
<?php
/**
 * Test pushing a lead to Vici - IT'S WORKING!
 */

echo "\n=== PUSHING TEST LEAD TO VICI ===\n\n";

$leadData = [
    'source' => 'BRAIN',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'phone_number' => '2482205565',
    'first_name' => 'Test',
    'last_name' => 'FromBrain',
    'address' => '123 Test St',
    'city' => 'Detroit', 
    'state' => 'MI',
    'postal_code' => '48201',
    'email' => 'test@brain.com',
    'vendor_lead_code' => 'BRAIN_' . time(),
    'list_id' => '101',
    'phone_code' => '1',
    'custom_fields' => 'Y',
    'add_to_hopper' => 'Y',
    'hopper_priority' => '50',
    'campaign_id' => 'AUTODIAL'
];

echo "Lead Details:\n";
echo "  Phone: {$leadData['phone_number']}\n";
echo "  Name: {$leadData['first_name']} {$leadData['last_name']}\n";
echo "  List: {$leadData['list_id']}\n";
echo "  Campaign: {$leadData['campaign_id']}\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://philli.callix.ai/vicidial/non_agent_api.php',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($leadData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response: $response\n\n";

if (strpos($response, 'SUCCESS') !== false) {
    echo "✅ LEAD SUCCESSFULLY PUSHED TO VICI!\n";
    
    // Parse the response to get lead ID
    if (preg_match('/SUCCESS.*\|(\d+)\|(\d+)\|/', $response, $matches)) {
        echo "   List ID: {$matches[1]}\n";
        echo "   Vici Lead ID: {$matches[2]}\n";
    }
    
    echo "\n🎉 VICI INTEGRATION IS FULLY WORKING!\n";
} else {
    echo "❌ Failed to push lead\n";
}

echo "\n";
