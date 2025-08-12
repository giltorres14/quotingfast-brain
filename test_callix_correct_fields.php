#!/usr/bin/env php
<?php
/**
 * Test Callix Portal with CORRECT field names
 * The portal shows "User ID" and "Password" labels
 */

echo "\n";
echo "===========================================\n";
echo "  CALLIX PORTAL TEST - CORRECT FIELDS\n";
echo "===========================================\n\n";

$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
$credentials = [
    'user_id' => 'UploadAPI',
    'password' => '8ZDWGAAQRD'
];

echo "URL: $whitelistUrl\n";
echo "Credentials:\n";
echo "  user_id: {$credentials['user_id']}\n";
echo "  password: {$credentials['password']}\n\n";

echo "TEST 1: POST with user_id/password fields\n";
echo "----------------------------------------\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($credentials),
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
    echo "❌ Connection Error: $error\n\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response (first 500 chars):\n";
    echo substr($response, 0, 500) . "\n\n";
    
    // Check if it looks like success
    if (strpos($response, 'Agent Validation') === false && 
        strpos($response, 'User ID') === false &&
        $httpCode == 200) {
        echo "✅ Likely SUCCESS - Form not shown again\n";
    } else {
        echo "❌ Still showing login form\n";
    }
}

echo "\nTEST 2: Now test Vici API\n";
echo "-------------------------\n";

$apiUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiData = [
    'source' => 'BRAIN',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'version'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($apiData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "API Response: $response\n";

if (strpos($response, 'VERSION') !== false || strpos($response, 'BUILD') !== false) {
    echo "✅ API ACCESS GRANTED - Whitelist worked!\n";
} elseif (strpos($response, 'Login incorrect') !== false) {
    echo "❌ API ACCESS DENIED - Whitelist didn't work\n";
} else {
    echo "? Unknown response\n";
}

echo "\n===========================================\n";
echo "  SUMMARY\n";
echo "===========================================\n\n";

echo "The Callix portal at:\n";
echo "https://philli.callix.ai:26793/92RG8UJYTW.php\n\n";
echo "Expects these field names:\n";
echo "  • user_id (NOT 'user')\n";
echo "  • password (NOT 'pass')\n\n";
echo "With credentials:\n";
echo "  • user_id: UploadAPI\n";
echo "  • password: 8ZDWGAAQRD\n\n";

exit(0);


