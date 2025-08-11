<?php
/**
 * Callix Whitelist Test - Run this FROM RENDER to test authentication
 * This will help us figure out the correct credentials and field names
 */

header('Content-Type: application/json');

$tests = [];
$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';

// Test 1: UploadAPI with user_id/password
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user_id' => 'UploadAPI',
        'password' => '8ZDWGAAQRD'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => true
]);

$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error1 = curl_error($ch);
curl_close($ch);

$tests['test1_uploadapi_userid'] = [
    'credentials' => 'UploadAPI / 8ZDWGAAQRD',
    'fields' => 'user_id / password',
    'http_code' => $httpCode1,
    'error' => $error1,
    'response_preview' => substr($response1, 0, 200),
    'success' => $httpCode1 === 200 || $httpCode1 === 302
];

// Test 2: UploadAPI with user/pass
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user' => 'UploadAPI',
        'pass' => '8ZDWGAAQRD'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => true
]);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch);
curl_close($ch);

$tests['test2_uploadapi_user'] = [
    'credentials' => 'UploadAPI / 8ZDWGAAQRD',
    'fields' => 'user / pass',
    'http_code' => $httpCode2,
    'error' => $error2,
    'response_preview' => substr($response2, 0, 200),
    'success' => $httpCode2 === 200 || $httpCode2 === 302
];

// Test 3: apiuser with user/pass
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user' => 'apiuser',
        'pass' => 'UZPATJ59GJAVKG8ES6'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => true
]);

$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error3 = curl_error($ch);
curl_close($ch);

$tests['test3_apiuser'] = [
    'credentials' => 'apiuser / UZPATJ59GJAVKG8ES6',
    'fields' => 'user / pass',
    'http_code' => $httpCode3,
    'error' => $error3,
    'response_preview' => substr($response3, 0, 200),
    'success' => $httpCode3 === 200 || $httpCode3 === 302
];

// Test Vici API access
$viciUrl = 'http://162.241.97.210/vicidial/non_agent_api.php';
$viciParams = [
    'source' => 'brain',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'version'
];

$ch = curl_init($viciUrl . '?' . http_build_query($viciParams));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5
]);

$viciResponse = curl_exec($ch);
$viciHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$viciAccessible = $viciHttpCode === 200 && 
    (strpos($viciResponse, 'VERSION') !== false || strpos($viciResponse, 'SUCCESS') !== false);

// Prepare result
$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_ip' => file_get_contents('https://api.ipify.org?format=text'),
    'whitelist_tests' => $tests,
    'vici_access' => [
        'accessible' => $viciAccessible,
        'http_code' => $viciHttpCode,
        'response_preview' => substr($viciResponse, 0, 100)
    ],
    'recommendation' => null
];

// Find which test worked
foreach ($tests as $name => $test) {
    if ($test['success']) {
        $result['recommendation'] = "Use: " . $test['credentials'] . " with fields: " . $test['fields'];
        break;
    }
}

if (!$result['recommendation']) {
    $result['recommendation'] = "None of the credential combinations worked. Check with Vici admin.";
}

echo json_encode($result, JSON_PRETTY_PRINT);
