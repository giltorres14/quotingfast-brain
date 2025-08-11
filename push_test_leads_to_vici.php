<?php
/**
 * Script to push test leads to Vici through the Brain webhook
 * This bypasses the database and sends leads directly through the working webhook
 */

// Sample test leads - replace with your actual test lead data
$test_leads = [
    [
        'contact' => [
            'first_name' => 'Test',
            'last_name' => 'Lead1',
            'phone' => '5551234567',
            'email' => 'test1@example.com',
            'address' => '123 Main St',
            'city' => 'Columbus',
            'state' => 'OH',
            'zip_code' => '43215'
        ],
        'drivers' => [
            ['name' => 'Test Lead1', 'age' => 30, 'gender' => 'M']
        ],
        'vehicles' => [
            ['year' => 2020, 'make' => 'Honda', 'model' => 'Accord']
        ]
    ],
    [
        'contact' => [
            'first_name' => 'Test',
            'last_name' => 'Lead2',
            'phone' => '5551234568',
            'email' => 'test2@example.com',
            'address' => '456 Oak Ave',
            'city' => 'Cleveland',
            'state' => 'OH',
            'zip_code' => '44101'
        ],
        'drivers' => [
            ['name' => 'Test Lead2', 'age' => 35, 'gender' => 'F']
        ],
        'vehicles' => [
            ['year' => 2019, 'make' => 'Toyota', 'model' => 'Camry']
        ]
    ],
    // Add more test leads here
];

// Webhook URL
$webhook_url = 'https://quotingfast-brain-ohio.onrender.com/webhook.php';

// Process each lead
foreach ($test_leads as $index => $lead) {
    echo "Sending lead " . ($index + 1) . ": " . $lead['contact']['first_name'] . " " . $lead['contact']['last_name'] . "\n";
    
    // Prepare the data
    $json_data = json_encode($lead);
    
    // Setup cURL
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Display result
    if ($http_code == 201) {
        echo "✓ Success: " . $response . "\n";
    } else {
        echo "✗ Failed (HTTP $http_code): " . $response . "\n";
    }
    
    // Small delay between requests
    sleep(1);
}

echo "\nAll leads processed!\n";
echo "Check Vici List 101 to verify the leads appear.\n";
