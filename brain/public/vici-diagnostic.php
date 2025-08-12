<?php
// Vici diagnostic script to identify the issue
header('Content-Type: text/plain');

echo "=== VICI INTEGRATION DIAGNOSTIC ===\n\n";

// 1. Check server IP
echo "1. SERVER IP ADDRESS:\n";
$server_ip = file_get_contents('https://api.ipify.org');
echo "   Render Server IP: " . $server_ip . "\n";
echo "   ⚠️  This IP needs to be whitelisted in Vici!\n\n";

// 2. Test connectivity to Vici
echo "2. VICI SERVER CONNECTIVITY:\n";
$vici_host = '162.241.97.210';
$connection = @fsockopen($vici_host, 80, $errno, $errstr, 5);
if ($connection) {
    echo "   ✅ Can connect to Vici server\n";
    fclose($connection);
} else {
    echo "   ❌ Cannot connect to Vici server\n";
    echo "   Error: $errstr ($errno)\n";
}
echo "\n";

// 3. Test Vici API with correct credentials
echo "3. VICI API TEST:\n";
$params = [
    'source' => 'brain',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'list_id' => '101',
    'phone_number' => '6145559876',
    'first_name' => 'Diagnostic',
    'last_name' => 'Test',
    'email' => 'diagnostic@test.com'
];

$vici_url = 'http://162.241.97.210/vicidial/non_agent_api.php';
$query = http_build_query($params);
$full_url = $vici_url . '?' . $query;

$ch = curl_init($full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_error($ch);
curl_close($ch);

echo "   URL: " . $vici_url . "\n";
echo "   User: UploadAPI\n";
echo "   List: 101\n";
echo "   Phone: 6145559876\n\n";

if ($response === false) {
    echo "   ❌ CURL Error: " . $error . "\n";
} else {
    echo "   HTTP Code: " . $info['http_code'] . "\n";
    echo "   Response: " . substr($response, 0, 200) . "\n\n";
    
    if (strpos($response, 'SUCCESS') !== false) {
        echo "   ✅ SUCCESS! Lead added to Vici\n";
    } elseif (strpos($response, 'ERROR') !== false) {
        echo "   ❌ ERROR from Vici API\n";
        echo "   Full response: " . $response . "\n";
    } else {
        echo "   ⚠️  Unexpected response\n";
    }
}

echo "\n4. DIAGNOSIS:\n";
echo "   Most likely issues:\n";
echo "   1. Render IP ($server_ip) not whitelisted in Vici\n";
echo "   2. Wrong API credentials (should be UploadAPI)\n";
echo "   3. List 101 doesn't exist or is inactive\n";
echo "   4. Vici API is disabled or restricted\n\n";

echo "5. SOLUTION:\n";
echo "   Add this IP to Vici whitelist: $server_ip\n";
echo "   Path in Vici: Admin > System Settings > Security\n";

