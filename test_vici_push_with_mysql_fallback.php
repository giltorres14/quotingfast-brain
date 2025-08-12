#!/usr/bin/env php
<?php
/**
 * Test Vici Push - Try API first, then MySQL
 * 
 * FINDINGS:
 * - Previously worked with direct MySQL connection on port 3306
 * - Now MySQL is blocked ("No route to host")
 * - API requires Callix whitelist which isn't working automatically
 * - Need manual whitelist of IP: 3.129.111.220
 */

echo "\n";
echo "===========================================\n";
echo "  VICI PUSH TEST - API & MySQL FALLBACK\n";
echo "===========================================\n\n";

echo "CURRENT SITUATION:\n";
echo "• Render IP: 3.129.111.220\n";
echo "• MySQL: BLOCKED (No route to host)\n";
echo "• API: Requires manual whitelist\n";
echo "• Callix Portal: Not effectively whitelisting\n\n";

$testLead = [
    'phone_number' => '2482205565',
    'first_name' => 'Test',
    'last_name' => 'Lead',
    'address' => '123 Test St',
    'city' => 'Detroit',
    'state' => 'MI',
    'postal_code' => '48201',
    'email' => 'test@example.com',
    'vendor_lead_code' => 'BRAIN_TEST_' . time(),
    'source_id' => 'BRAIN',
    'list_id' => '101',
    'phone_code' => '1'
];

echo "TEST LEAD:\n";
echo json_encode($testLead, JSON_PRETTY_PRINT) . "\n\n";

// Test 1: Try Non-Agent API
echo "TEST 1: NON-AGENT API\n";
echo "------------------------\n";

$apiUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiParams = array_merge($testLead, [
    'source' => 'BRAIN',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'custom_fields' => 'Y',
    'add_to_hopper' => 'Y',
    'hopper_priority' => '50',
    'campaign_id' => 'AUTODIAL'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($apiParams),
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
    echo "❌ Connection Error: $error\n";
} elseif (strpos($response, 'SUCCESS') !== false) {
    echo "✅ SUCCESS! Lead pushed via API\n";
    echo "Response: $response\n";
} else {
    echo "❌ API Failed\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n";

// Test 2: Try MySQL Direct (will likely fail)
echo "TEST 2: DIRECT MySQL CONNECTION\n";
echo "--------------------------------\n";

try {
    $dsn = "mysql:host=37.27.138.222;dbname=asterisk;port=3306;charset=utf8mb4";
    $pdo = new PDO($dsn, 'Superman', '8ZDWGAAQRD', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "✅ MySQL Connected!\n";
    
    // Try to insert lead
    $sql = "INSERT INTO vicidial_list 
            (phone_number, first_name, last_name, address1, city, state, 
             postal_code, email, vendor_lead_code, source_id, list_id, 
             phone_code, status, entry_date, last_local_call_time)
            VALUES 
            (:phone, :first_name, :last_name, :address, :city, :state,
             :zip, :email, :vendor_code, :source, :list_id,
             :phone_code, 'NEW', NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':phone' => $testLead['phone_number'],
        ':first_name' => $testLead['first_name'],
        ':last_name' => $testLead['last_name'],
        ':address' => $testLead['address'],
        ':city' => $testLead['city'],
        ':state' => $testLead['state'],
        ':zip' => $testLead['postal_code'],
        ':email' => $testLead['email'],
        ':vendor_code' => $testLead['vendor_lead_code'],
        ':source' => $testLead['source_id'],
        ':list_id' => $testLead['list_id'],
        ':phone_code' => $testLead['phone_code']
    ]);
    
    $leadId = $pdo->lastInsertId();
    echo "✅ Lead inserted! Vici Lead ID: $leadId\n";
    
} catch (Exception $e) {
    echo "❌ MySQL Failed: " . $e->getMessage() . "\n";
}

echo "\n";
echo "===========================================\n";
echo "  RECOMMENDATIONS\n";
echo "===========================================\n\n";

echo "Since MySQL is blocked, you need to:\n\n";
echo "1. Contact Vici administrator to manually whitelist:\n";
echo "   • IP Address: 3.129.111.220\n";
echo "   • Description: Render.com server for Brain system\n\n";
echo "2. Verify UploadAPI credentials are correct:\n";
echo "   • Username: UploadAPI\n";
echo "   • Password: 8ZDWGAAQRD\n\n";
echo "3. Ensure List 101 exists in AUTODIAL campaign\n\n";
echo "4. Once whitelisted, leads will flow automatically\n\n";

exit(0);

