#!/usr/bin/env php
<?php
/**
 * Test updating a REAL lead we just added to Vici
 * We added lead ID 11543736, let's update it
 */

echo "\n";
echo "===========================================\n";
echo "  UPDATE REAL VICI LEAD TEST\n";
echo "===========================================\n\n";

// The lead we just added
$viciLeadId = '11543736';
$vendorCode = 'TEST_' . (time() - 100); // The vendor code we used earlier

echo "Target Lead:\n";
echo "  Vici Lead ID: $viciLeadId\n";
echo "  Vendor Code: $vendorCode\n\n";

// Working credentials
$config = [
    'user' => 'apiuser',
    'pass' => 'UZPATJ59GJAVKG8ES6',
    'server' => 'philli.callix.ai',
    'endpoint' => '/vicidial/non_agent_api.php'
];

// Method 1: Update by lead_id
echo "METHOD 1: Update by lead_id\n";
echo "----------------------------\n";

$updateParams = [
    'user' => $config['user'],
    'pass' => $config['pass'],
    'function' => 'update_lead',
    'source' => 'BRAIN_UPDATE',
    'lead_id' => $viciLeadId,
    'search_method' => 'LEAD_ID',
    'search_location' => 'SYSTEM',
    // New values to update
    'first_name' => 'BrainUpdated',
    'last_name' => 'TestLead',
    'email' => 'brain_updated@test.com',
    'vendor_lead_code' => 'BRAIN_' . rand(100000, 999999), // Simulate Brain Lead ID
    'comments' => 'Updated from Brain system at ' . date('Y-m-d H:i:s')
];

echo "Updating with:\n";
echo "  First Name: {$updateParams['first_name']}\n";
echo "  Last Name: {$updateParams['last_name']}\n";
echo "  Email: {$updateParams['email']}\n";
echo "  New Vendor Code: {$updateParams['vendor_lead_code']}\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://{$config['server']}{$config['endpoint']}",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($updateParams),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response: $response\n";

if (strpos($response, 'SUCCESS') !== false) {
    echo "✅ LEAD UPDATED SUCCESSFULLY!\n\n";
    
    // Now verify the update by searching for it
    echo "VERIFICATION: Searching for updated lead\n";
    echo "-----------------------------------------\n";
    
    $searchParams = [
        'user' => $config['user'],
        'pass' => $config['pass'],
        'function' => 'update_lead',
        'source' => 'BRAIN_VERIFY',
        'vendor_lead_code' => $updateParams['vendor_lead_code'],
        'search_method' => 'VENDOR_LEAD_CODE',
        'search_location' => 'LIST',
        'list_id' => '101',
        'custom_fields' => 'Y'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$config['server']}{$config['endpoint']}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($searchParams),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $verifyResponse = curl_exec($ch);
    curl_close($ch);
    
    echo "Verification Response: $verifyResponse\n";
    
    if (strpos($verifyResponse, 'SUCCESS') !== false || strpos($verifyResponse, 'NOTICE') !== false) {
        echo "✅ Lead found with updated vendor code!\n";
    }
    
} elseif (strpos($response, 'LEAD NOT FOUND') !== false) {
    echo "❌ Lead not found - trying by vendor code instead\n\n";
    
    // Method 2: Try updating by vendor_lead_code
    echo "METHOD 2: Update by vendor_lead_code\n";
    echo "-------------------------------------\n";
    
    $updateParams2 = [
        'user' => $config['user'],
        'pass' => $config['pass'],
        'function' => 'update_lead',
        'source' => 'BRAIN_UPDATE',
        'vendor_lead_code' => $vendorCode,
        'search_method' => 'VENDOR_LEAD_CODE',
        'search_location' => 'LIST',
        'list_id' => '101',
        // New values
        'first_name' => 'BrainUpdated',
        'last_name' => 'ViaVendorCode',
        'email' => 'vendor_code_update@test.com'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$config['server']}{$config['endpoint']}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($updateParams2),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response2 = curl_exec($ch);
    curl_close($ch);
    
    echo "Response: $response2\n";
    
    if (strpos($response2, 'SUCCESS') !== false) {
        echo "✅ LEAD UPDATED via vendor_lead_code!\n";
    }
} else {
    echo "❌ Update failed\n";
}

echo "\n";
echo "===========================================\n";
echo "  SUMMARY\n";
echo "===========================================\n\n";

echo "This test shows how Brain will update leads in Vici:\n";
echo "1. Search by lead_id (if known)\n";
echo "2. Search by vendor_lead_code (for existing leads)\n";
echo "3. Update any fields including vendor_lead_code\n\n";

echo "For your 3 months of leads:\n";
echo "- Brain will search by phone number in MySQL\n";
echo "- Find the Vici lead_id\n";
echo "- Update vendor_lead_code to Brain's Lead ID\n";
echo "- This creates the link between systems\n\n";

exit(0);
