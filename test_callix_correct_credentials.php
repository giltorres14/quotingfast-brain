#!/usr/bin/env php
<?php
/**
 * Test Callix Authentication with CORRECT credentials
 * Portal: https://philli.callix.ai:26793/92RG8UJYTW.php
 * 
 * CORRECT CREDENTIALS:
 * - User ID: Superman
 * - Password: 8ZDWGAAQRD
 */

echo "\n";
echo "===========================================\n";
echo "  CALLIX AUTHENTICATION TEST\n";
echo "===========================================\n\n";

$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';

echo "Portal: $whitelistUrl\n";
echo "User ID: Superman\n";
echo "Password: 8ZDWGAAQRD\n\n";

// Test 1: With correct field names
echo "TEST 1: Using 'user_id' and 'password' fields\n";
echo "-----------------------------------------------\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user_id' => 'Superman',
        'password' => '8ZDWGAAQRD'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
    if ($httpCode == 200) {
        echo "✅ Authentication request sent\n";
    }
}

echo "\n";

// Test 2: Try Vici API after authentication
echo "TEST 2: Vici API Test (after authentication)\n";
echo "---------------------------------------------\n";

sleep(2); // Give it a moment

$viciData = [
    'source' => 'BRAIN',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'version',
];

$viciUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $viciUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($viciData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
    if (strpos($response, 'VERSION') !== false || strpos($response, 'BUILD') !== false) {
        echo "✅ VICI API ACCESSIBLE!\n";
    } elseif (strpos($response, 'ERROR') !== false) {
        echo "❌ API Error (may need to wait or retry)\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "  SUMMARY\n";
echo "===========================================\n\n";

echo "If authentication worked, Vici should now accept connections from your IP.\n";
echo "The whitelist may need periodic refresh (every 30 minutes).\n\n";

exit(0);

