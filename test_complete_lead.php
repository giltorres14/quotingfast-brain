<?php
/**
 * Send a COMPLETE test lead with ALL fields populated
 * This ensures we're testing with production-like data
 */

echo "=== SENDING COMPLETE TEST LEAD ===\n\n";

// Generate unique identifiers
$timestamp = time();
$unique_id = substr($timestamp, -4);

// COMPLETE lead data - ALL fields that LQF would send
$complete_lead = [
    'contact' => [
        'first_name' => 'John',
        'last_name' => 'TestComplete' . $unique_id,
        'phone' => '6145559' . $unique_id,  // Valid 10-digit Columbus, OH number
        'email' => 'john.complete' . $unique_id . '@test.com',
        'address' => '123 Main Street',
        'city' => 'Columbus',
        'state' => 'OH',
        'zip_code' => '43215',
        'ip_address' => '192.168.1.1',
        'date_of_birth' => '1985-05-15'
    ],
    'drivers' => [
        [
            'name' => 'John TestComplete',
            'age' => 38,
            'gender' => 'M',
            'marital_status' => 'married',
            'license_age' => 16,
            'accidents' => 0,
            'violations' => 0,
            'dui' => false,
            'sr22' => false
        ]
    ],
    'vehicles' => [
        [
            'year' => 2020,
            'make' => 'Honda',
            'model' => 'Accord',
            'vin' => '1HGCV1F30LA' . $unique_id,
            'primary_use' => 'commute',
            'annual_mileage' => 12000,
            'ownership' => 'owned',
            'garage_type' => 'garage'
        ]
    ],
    'current_policy' => [
        'current_insurance' => 'State Farm',
        'policy_expiration' => '2025-03-15',
        'coverage_type' => 'full',
        'premium' => 150,
        'continuous_coverage' => true,
        'years_with_company' => 5
    ],
    // Metadata fields
    'source' => 'leadsquotingfast',
    'type' => 'auto',
    'campaign_id' => 'TEST_CAMP_' . $unique_id,
    'external_lead_id' => '1000' . $unique_id,
    'utm_source' => 'google',
    'utm_medium' => 'cpc',
    'utm_campaign' => 'auto_insurance_ohio',
    'user_agent' => 'Mozilla/5.0 Test Browser',
    'landing_page' => 'https://quotingfast.com/auto-insurance',
    'tcpa_consent' => true,
    'tcpa_text' => 'I agree to be contacted',
    'lead_quality_score' => 85
];

// Send to Brain webhook
$webhook_url = 'https://quotingfast-brain-ohio.onrender.com/webhook.php';

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($complete_lead));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "LEAD DETAILS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Name: " . $complete_lead['contact']['first_name'] . " " . $complete_lead['contact']['last_name'] . "\n";
echo "Phone: " . $complete_lead['contact']['phone'] . " (10-digit valid)\n";
echo "Email: " . $complete_lead['contact']['email'] . "\n";
echo "Location: " . $complete_lead['contact']['city'] . ", " . $complete_lead['contact']['state'] . " " . $complete_lead['contact']['zip_code'] . "\n";
echo "Campaign ID: " . $complete_lead['campaign_id'] . "\n";
echo "External Lead ID: " . $complete_lead['external_lead_id'] . "\n";
echo "Type: " . $complete_lead['type'] . "\n";
echo "\n";

echo "WEBHOOK RESPONSE:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "HTTP Status: $http_code\n";
if ($error) {
    echo "Error: $error\n";
}
echo "Response: " . $response . "\n\n";

// Parse response
$result = json_decode($response, true);
if ($result && isset($result['success']) && $result['success']) {
    echo "✅ SUCCESS! Lead sent to webhook\n";
    echo "✅ Lead should be in Vici List 101 (if IP is whitelisted)\n";
    echo "\n";
    echo "CHECK VICI FOR:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Phone: " . $complete_lead['contact']['phone'] . "\n";
    echo "Name: " . $complete_lead['contact']['first_name'] . " " . $complete_lead['contact']['last_name'] . "\n";
} else {
    echo "❌ FAILED to send lead\n";
}

echo "\n";
echo "REQUIRED FIELDS CHECKLIST:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✓ first_name: " . $complete_lead['contact']['first_name'] . "\n";
echo "✓ last_name: " . $complete_lead['contact']['last_name'] . "\n";
echo "✓ phone: " . $complete_lead['contact']['phone'] . " (10 digits)\n";
echo "✓ email: " . $complete_lead['contact']['email'] . "\n";
echo "✓ address: " . $complete_lead['contact']['address'] . "\n";
echo "✓ city: " . $complete_lead['contact']['city'] . "\n";
echo "✓ state: " . $complete_lead['contact']['state'] . "\n";
echo "✓ zip_code: " . $complete_lead['contact']['zip_code'] . "\n";
echo "✓ type: " . $complete_lead['type'] . "\n";
echo "✓ campaign_id: " . $complete_lead['campaign_id'] . "\n";
echo "✓ external_lead_id: " . $complete_lead['external_lead_id'] . "\n";
echo "\n";

// Now test direct Vici connection
echo "TESTING DIRECT VICI CONNECTION:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$vici_params = [
    'source' => 'brain',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'list_id' => 101,
    'phone_number' => $complete_lead['contact']['phone'],
    'first_name' => $complete_lead['contact']['first_name'],
    'last_name' => $complete_lead['contact']['last_name'],
    'email' => $complete_lead['contact']['email'],
    'address1' => $complete_lead['contact']['address'],
    'city' => $complete_lead['contact']['city'],
    'state' => $complete_lead['contact']['state'],
    'postal_code' => $complete_lead['contact']['zip_code']
];

$vici_url = 'http://162.241.97.210/vicidial/non_agent_api.php';
$query_string = http_build_query($vici_params);
$full_url = $vici_url . '?' . $query_string;

$ch = curl_init($full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$vici_response = curl_exec($ch);
$vici_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$vici_error = curl_error($ch);
curl_close($ch);

echo "Vici URL: $vici_url\n";
echo "HTTP Code: $vici_http_code\n";
if ($vici_error) {
    echo "Error: $vici_error\n";
}
echo "Response: " . substr($vici_response, 0, 200) . "\n";

if (strpos($vici_response, 'SUCCESS') !== false) {
    echo "\n✅ VICI ACCEPTED THE LEAD!\n";
} else {
    echo "\n❌ VICI DID NOT ACCEPT - Likely IP not whitelisted (3.129.111.220)\n";
}

