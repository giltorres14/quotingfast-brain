<?php
/**
 * Test Callix Authentication with different field names and credentials
 */

echo "=== TESTING CALLIX WHITELIST AUTHENTICATION ===\n\n";

$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';

// Test different credential combinations
$tests = [
    [
        'name' => 'Test 1: UploadAPI with user_id/password fields',
        'fields' => [
            'user_id' => 'UploadAPI',
            'password' => '8ZDWGAAQRD'
        ]
    ],
    [
        'name' => 'Test 2: UploadAPI with user/pass fields',
        'fields' => [
            'user' => 'UploadAPI',
            'pass' => '8ZDWGAAQRD'
        ]
    ],
    [
        'name' => 'Test 3: apiuser with user/pass fields',
        'fields' => [
            'user' => 'apiuser',
            'pass' => 'UZPATJ59GJAVKG8ES6'
        ]
    ],
    [
        'name' => 'Test 4: apiuser with user_id/password fields',
        'fields' => [
            'user_id' => 'apiuser',
            'password' => 'UZPATJ59GJAVKG8ES6'
        ]
    ]
];

foreach ($tests as $test) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $test['name'] . "\n";
    echo "Fields: " . json_encode($test['fields']) . "\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $whitelistUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($test['fields']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true, // Include headers in response
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (compatible; BrainWhitelist/1.0)'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "HTTP Code: $httpCode\n";
    
    if ($error) {
        echo "Error: $error\n";
    }
    
    // Check for redirects (often means success)
    if (strpos($headers, 'Location:') !== false) {
        preg_match('/Location: (.+)/', $headers, $matches);
        echo "Redirect to: " . trim($matches[1] ?? 'unknown') . "\n";
    }
    
    // Check body for success indicators
    $bodyPreview = substr(strip_tags($body), 0, 200);
    echo "Body preview: " . $bodyPreview . "\n";
    
    // Determine success
    $success = false;
    if ($httpCode === 200 || $httpCode === 302) {
        $success = true;
    }
    if (stripos($body, 'success') !== false || stripos($body, 'authenticated') !== false) {
        $success = true;
    }
    if (stripos($body, 'error') !== false || stripos($body, 'invalid') !== false || stripos($body, 'failed') !== false) {
        $success = false;
    }
    
    echo "Result: " . ($success ? "✅ SUCCESS" : "❌ FAILED") . "\n\n";
    
    // If successful, break and show which one worked
    if ($success) {
        echo "🎉 FOUND WORKING CREDENTIALS!\n";
        echo "Use these field names and values:\n";
        foreach ($test['fields'] as $key => $value) {
            echo "  $key: $value\n";
        }
        break;
    }
}

// Now test Vici API access to see if whitelist worked
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TESTING VICI API ACCESS:\n";

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
$viciError = curl_error($ch);
curl_close($ch);

echo "Vici URL: $viciUrl\n";
echo "HTTP Code: $viciHttpCode\n";
if ($viciError) {
    echo "Error: $viciError\n";
}
echo "Response: " . substr($viciResponse, 0, 100) . "\n";

if ($viciHttpCode === 200 && (strpos($viciResponse, 'VERSION') !== false || strpos($viciResponse, 'SUCCESS') !== false)) {
    echo "✅ Vici API is accessible - whitelist is working!\n";
} else {
    echo "❌ Vici API not accessible - whitelist may not be working\n";
}


