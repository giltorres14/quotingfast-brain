#!/usr/bin/env php
<?php
/**
 * Test Vici with Superman credentials
 */

echo "\n=== VICI INTEGRATION TEST ===\n\n";

// Step 1: Authenticate with Callix
echo "1. CALLIX AUTHENTICATION\n";
echo "   User: Superman\n";
echo "   Pass: 8ZDWGAAQRD\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://philli.callix.ai:26793/92RG8UJYTW.php',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user' => 'Superman',
        'pass' => '8ZDWGAAQRD'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Response: HTTP $httpCode\n";
if ($httpCode == 200) {
    echo "   ✅ Authentication sent\n";
} else {
    echo "   ❌ Failed\n";
}

sleep(2);

// Step 2: Test Vici API
echo "\n2. VICI API TEST\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://philli.callix.ai/vicidial/non_agent_api.php',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'source' => 'BRAIN',
        'user' => 'UploadAPI',
        'pass' => '8ZDWGAAQRD',
        'function' => 'version'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Response: $response\n";

if (strpos($response, 'VERSION') !== false || strpos($response, 'BUILD') !== false) {
    echo "   ✅ VICI API IS WORKING!\n";
} elseif (strpos($response, 'Login incorrect') !== false) {
    echo "   ❌ Still not whitelisted\n";
} else {
    echo "   ❓ Unknown response\n";
}

echo "\n";

