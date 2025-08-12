<?php
/**
 * Test Callix authentication by simulating a browser visit
 * The portal might need cookies or session handling
 */

header('Content-Type: application/json');

$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
$result = [];

// Step 1: GET the page first (like a browser would)
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => '/tmp/callix_cookies.txt',
    CURLOPT_COOKIEFILE => '/tmp/callix_cookies.txt',
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$getResponse = curl_exec($ch);
$getHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result['step1_get'] = [
    'http_code' => $getHttpCode,
    'has_form' => strpos($getResponse, '<form') !== false,
    'has_user_field' => strpos($getResponse, 'user') !== false,
    'has_password_field' => strpos($getResponse, 'password') !== false
];

// Extract any hidden fields or tokens from the form
preg_match_all('/<input[^>]+type=["\']hidden["\'][^>]+>/i', $getResponse, $hiddenFields);
$result['hidden_fields'] = $hiddenFields[0] ?? [];

// Step 2: POST with credentials (using session from GET)
sleep(1); // Act more human-like

$postData = [
    'user_id' => 'UploadAPI',
    'password' => '8ZDWGAAQRD'
];

// Check if form uses different field names
if (strpos($getResponse, 'name="user"') !== false) {
    $postData = [
        'user' => 'UploadAPI',
        'pass' => '8ZDWGAAQRD'
    ];
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => '/tmp/callix_cookies.txt',
    CURLOPT_COOKIEFILE => '/tmp/callix_cookies.txt',
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Referer: ' . $whitelistUrl,
        'Origin: https://philli.callix.ai:26793'
    ]
]);

$postResponse = curl_exec($ch);
$postHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$headers = substr($postResponse, 0, $headerSize);
$body = substr($postResponse, $headerSize);

$result['step2_post'] = [
    'http_code' => $postHttpCode,
    'has_redirect' => strpos($headers, 'Location:') !== false,
    'body_preview' => substr(strip_tags($body), 0, 200)
];

// Extract redirect location if any
if (strpos($headers, 'Location:') !== false) {
    preg_match('/Location: (.+)/i', $headers, $matches);
    $result['step2_post']['redirect_to'] = trim($matches[1] ?? '');
}

// Step 3: Wait and test Vici
sleep(3);

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
    CURLOPT_TIMEOUT => 5,
    CURLOPT_CONNECTTIMEOUT => 3
]);

$viciResponse = curl_exec($ch);
$viciHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result['step3_vici_test'] = [
    'accessible' => $viciHttpCode === 200,
    'http_code' => $viciHttpCode,
    'response_preview' => substr($viciResponse, 0, 100),
    'has_version' => strpos($viciResponse, 'VERSION') !== false,
    'has_success' => strpos($viciResponse, 'SUCCESS') !== false
];

// Summary
$result['summary'] = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_ip' => '3.129.111.220',
    'whitelist_worked' => $result['step3_vici_test']['accessible'],
    'recommendation' => $result['step3_vici_test']['accessible'] 
        ? 'Whitelist successful! Vici is now accessible.'
        : 'Whitelist did not work. Vici still not accessible.'
];

// Clean up cookies
@unlink('/tmp/callix_cookies.txt');

echo json_encode($result, JSON_PRETTY_PRINT);

