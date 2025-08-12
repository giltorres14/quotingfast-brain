#!/usr/bin/env php
<?php
/**
 * Test Vici Push with Protocol Fallback and Proper Firewall Auth
 * Based on the WORKING configuration from August 8th
 */

echo "\n";
echo "===========================================\n";
echo "  VICI PUSH TEST - PROTOCOL FALLBACK\n";
echo "===========================================\n\n";

$viciConfig = [
    'server' => 'philli.callix.ai',
    'api_endpoint' => '/vicidial/non_agent_api.php',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'list_id' => '101'
];

$testLead = [
    'phone_number' => '2482205565',
    'first_name' => 'Test',
    'last_name' => 'Lead',
    'address' => '123 Test St',
    'city' => 'Detroit',
    'state' => 'MI',
    'postal_code' => '48201',
    'email' => 'test@example.com',
    'vendor_lead_code' => 'BRAIN_TEST_' . time(),
    'source_id' => 'BRAIN'
];

echo "CONFIG:\n";
echo "• Server: {$viciConfig['server']}\n";
echo "• User: {$viciConfig['user']}\n";
echo "• List: {$viciConfig['list_id']}\n\n";

// Step 1: Firewall Authentication (using 'user' and 'pass' fields)
echo "STEP 1: FIREWALL AUTHENTICATION\n";
echo "--------------------------------\n";

$firewallUrl = "https://{$viciConfig['server']}:26793/92RG8UJYTW.php";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $firewallUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user' => $viciConfig['user'],  // NOT user_id
        'pass' => $viciConfig['pass']   // NOT password
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Firewall Response: HTTP $httpCode\n";
echo "Body: " . substr($response, 0, 200) . "\n\n";

// Step 2: Try HTTPS first, then HTTP (protocol fallback)
echo "STEP 2: API CALL WITH PROTOCOL FALLBACK\n";
echo "----------------------------------------\n";

$viciData = array_merge($testLead, [
    'source' => 'BRAIN',
    'user' => $viciConfig['user'],
    'pass' => $viciConfig['pass'],
    'function' => 'add_lead',
    'list_id' => $viciConfig['list_id'],
    'phone_code' => '1',
    'custom_fields' => 'Y',
    'add_to_hopper' => 'Y',
    'hopper_priority' => '50',
    'campaign_id' => 'AUTODIAL'
]);

$protocols = ['https', 'http'];
$success = false;

foreach ($protocols as $protocol) {
    echo "Trying $protocol://\n";
    
    $url = "$protocol://{$viciConfig['server']}{$viciConfig['api_endpoint']}";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($viciData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ Connection Error: $error\n";
        continue;
    }
    
    echo "  HTTP Code: $httpCode\n";
    echo "  Response: $response\n";
    
    if (strpos($response, 'SUCCESS') !== false) {
        echo "  ✅ SUCCESS! Lead pushed via $protocol\n";
        $success = true;
        
        // Parse the response to get lead ID
        if (preg_match('/SUCCESS.*\|(\d+)\|(\d+)\|/', $response, $matches)) {
            echo "  • List ID: {$matches[1]}\n";
            echo "  • Vici Lead ID: {$matches[2]}\n";
        }
        break;
    } elseif (strpos($response, 'ERROR') !== false) {
        echo "  ❌ API Error\n";
        
        // If login error, retry with firewall auth
        if (strpos($response, 'Login incorrect') !== false) {
            echo "  Retrying firewall auth...\n";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $firewallUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'user' => $viciConfig['user'],
                    'pass' => $viciConfig['pass']
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 10
            ]);
            curl_exec($ch);
            curl_close($ch);
            
            // Try API again
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($viciData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            echo "  Retry Response: $response\n";
            
            if (strpos($response, 'SUCCESS') !== false) {
                echo "  ✅ SUCCESS after retry!\n";
                $success = true;
                break;
            }
        }
    }
    
    echo "\n";
}

echo "\n";
echo "===========================================\n";
echo "  RESULT\n";
echo "===========================================\n\n";

if ($success) {
    echo "✅ LEAD SUCCESSFULLY PUSHED TO VICI!\n";
    echo "The system IS working - leads should be flowing.\n";
} else {
    echo "❌ UNABLE TO PUSH LEAD\n";
    echo "\nThis is the EXACT method that worked on August 8th.\n";
    echo "If it's not working now, either:\n";
    echo "1. The firewall whitelist has expired/been cleared\n";
    echo "2. The credentials have changed\n";
    echo "3. The server configuration has changed\n";
}

exit(0);

