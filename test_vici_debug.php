<?php
// Debug script to check Vici integration status

echo "=== VICI INTEGRATION DEBUG ===\n\n";

// Check what credentials would be used
$vici_config = [
    'api_user' => 'UploadAPI',  // Should be this
    'api_pass' => '8ZDWGAAQRD',  // Should be this
    'test_mode' => false,  // Should be false
    'list_id' => 101
];

echo "Expected Configuration:\n";
echo "- API User: UploadAPI (NOT apiuser)\n";
echo "- API Pass: [hidden]\n";
echo "- Test Mode: false (NOT true)\n";
echo "- List ID: 101\n\n";

// Test direct Vici API call
$test_lead = [
    'phone_number' => '6145557777',
    'first_name' => 'DirectAPI',
    'last_name' => 'Test',
    'email' => 'direct@test.com',
    'address1' => '777 Direct St',
    'city' => 'Columbus',
    'state' => 'OH',
    'postal_code' => '43215'
];

$params = [
    'source' => 'brain',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'list_id' => 101,
    'phone_number' => $test_lead['phone_number'],
    'first_name' => $test_lead['first_name'],
    'last_name' => $test_lead['last_name'],
    'email' => $test_lead['email'],
    'address1' => $test_lead['address1'],
    'city' => $test_lead['city'],
    'state' => $test_lead['state'],
    'postal_code' => $test_lead['postal_code']
];

$vici_url = 'http://162.241.97.210/vicidial/non_agent_api.php';
$query_string = http_build_query($params);
$full_url = $vici_url . '?' . $query_string;

echo "Testing Direct Vici API Call:\n";
echo "- Phone: " . $test_lead['phone_number'] . "\n";
echo "- Name: " . $test_lead['first_name'] . " " . $test_lead['last_name'] . "\n";
echo "- URL: " . $vici_url . "\n\n";

// Make the API call
$ch = curl_init($full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response:\n";
echo "- HTTP Code: $http_code\n";
echo "- Response: " . substr($response, 0, 200) . "\n\n";

if (strpos($response, 'SUCCESS') !== false) {
    echo "✅ SUCCESS! Lead should be in Vici List 101\n";
    echo "✅ Phone number: " . $test_lead['phone_number'] . "\n";
} else {
    echo "❌ FAILED! Check credentials and configuration\n";
    echo "Common issues:\n";
    echo "1. Wrong API user (should be UploadAPI not apiuser)\n";
    echo "2. Wrong password\n";
    echo "3. List 101 doesn't exist\n";
    echo "4. IP not whitelisted in Vici\n";
}
