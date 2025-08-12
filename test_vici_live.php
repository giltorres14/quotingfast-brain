<?php
// Test script to verify Vici integration is working
// This will send a test lead with proper 10-digit phone number

$webhook_url = 'https://quotingfast-brain-ohio.onrender.com/webhook.php';

// Generate unique test lead with valid 10-digit phone
$timestamp = date('His'); // Hours, minutes, seconds
$test_lead = [
    'contact' => [
        'first_name' => 'ViciTest',
        'last_name' => 'Lead' . $timestamp,
        'phone' => '6145550' . substr($timestamp, -3), // Valid Columbus OH number
        'email' => 'vicitest' . $timestamp . '@test.com',
        'address' => '456 Test Drive',
        'city' => 'Columbus', 
        'state' => 'OH',
        'zip_code' => '43215'
    ],
    'drivers' => [
        ['name' => 'ViciTest Lead', 'age' => 40, 'gender' => 'M']
    ],
    'vehicles' => [
        ['year' => 2023, 'make' => 'Toyota', 'model' => 'Camry']
    ]
];

// Send to webhook
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_lead));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n=== VICI TEST LEAD SENT ===\n";
echo "Phone Number: " . $test_lead['contact']['phone'] . "\n";
echo "Name: " . $test_lead['contact']['first_name'] . ' ' . $test_lead['contact']['last_name'] . "\n";
echo "HTTP Status: $http_code\n";
echo "Response: " . $response . "\n";
echo "\n✓ CHECK VICI LIST 101 FOR THIS LEAD\n";
echo "✓ Phone should be: " . $test_lead['contact']['phone'] . "\n\n";


