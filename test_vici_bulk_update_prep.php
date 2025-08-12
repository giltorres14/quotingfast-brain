#!/usr/bin/env php
<?php
/**
 * Vici Bulk Update Preparation Test
 * Tests all components needed for updating 3 months of leads
 */

echo "\n";
echo "================================================\n";
echo "  VICI BULK UPDATE PREPARATION TEST\n";
echo "  For updating 3 months of existing leads\n";
echo "================================================\n\n";

// Configuration that WORKS
$config = [
    'api' => [
        'server' => 'philli.callix.ai',
        'endpoint' => '/vicidial/non_agent_api.php',
        'user' => 'apiuser',
        'pass' => 'UZPATJ59GJAVKG8ES6'
    ],
    'mysql' => [
        'host' => '167.172.253.47',
        'db' => 'asterisk',
        'user' => 'cron',
        'pass' => '1234'
    ]
];

echo "CONFIGURATION:\n";
echo "API Server: {$config['api']['server']}\n";
echo "API User: {$config['api']['user']}\n";
echo "MySQL Host: {$config['mysql']['host']}\n";
echo "MySQL User: {$config['mysql']['user']}\n\n";

// Test 1: API Access
echo "TEST 1: API ACCESS\n";
echo "-------------------\n";

$params = [
    'user' => $config['api']['user'],
    'pass' => $config['api']['pass'],
    'source' => 'BRAIN',
    'function' => 'version'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://{$config['api']['server']}{$config['api']['endpoint']}",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'VERSION') !== false) {
    echo "✅ API Access: WORKING\n";
    echo "   Response: " . substr($response, 0, 50) . "...\n";
} else {
    echo "❌ API Access: FAILED\n";
    echo "   Response: $response\n";
}

// Test 2: MySQL Connection (if possible)
echo "\nTEST 2: MYSQL CONNECTION\n";
echo "------------------------\n";

try {
    $dsn = "mysql:host={$config['mysql']['host']};dbname={$config['mysql']['db']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['mysql']['user'], $config['mysql']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    // Count leads in lists
    $stmt = $pdo->query("
        SELECT 
            list_id,
            COUNT(*) as lead_count,
            COUNT(DISTINCT vendor_lead_code) as unique_vendor_codes
        FROM vicidial_list 
        WHERE list_id IN (101, 102, 103, 104, 105, 106, 107, 108)
        GROUP BY list_id
        ORDER BY list_id
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ MySQL Connection: WORKING\n\n";
    echo "Current Lead Distribution:\n";
    echo "List ID | Lead Count | Unique Vendor Codes\n";
    echo "--------|------------|--------------------\n";
    
    $totalLeads = 0;
    foreach ($results as $row) {
        printf("  %3d   |   %6d   |      %6d\n", 
            $row['list_id'], 
            $row['lead_count'], 
            $row['unique_vendor_codes']
        );
        $totalLeads += $row['lead_count'];
    }
    
    echo "--------|------------|--------------------\n";
    echo "TOTAL   |   " . str_pad($totalLeads, 6) . "   |\n";
    
    // Check for leads without vendor codes
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM vicidial_list 
        WHERE (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code = '0')
        AND list_id IN (101, 102, 103, 104, 105, 106, 107, 108)
    ");
    $noVendorCode = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($noVendorCode > 0) {
        echo "\n⚠️  WARNING: $noVendorCode leads have no vendor_lead_code!\n";
    }
    
} catch (Exception $e) {
    echo "❌ MySQL Connection: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 3: Update Function
echo "\nTEST 3: UPDATE_LEAD FUNCTION\n";
echo "-----------------------------\n";

$updateParams = [
    'user' => $config['api']['user'],
    'pass' => $config['api']['pass'],
    'function' => 'update_lead',
    'source' => 'BRAIN_UPDATE',
    'vendor_lead_code' => 'TEST_UPDATE_' . time(),
    'search_method' => 'VENDOR_LEAD_CODE',
    'search_location' => 'LIST',
    'list_id' => '101',
    'comments' => 'Test update from Brain system'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://{$config['api']['server']}{$config['api']['endpoint']}",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($updateParams),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'update_lead LEAD NOT FOUND') !== false) {
    echo "✅ Update Function: ACCESSIBLE\n";
    echo "   (Lead not found is expected for test vendor code)\n";
} elseif (strpos($response, 'SUCCESS') !== false) {
    echo "✅ Update Function: WORKING\n";
} else {
    echo "❓ Update Function: UNKNOWN\n";
    echo "   Response: $response\n";
}

// Summary
echo "\n";
echo "================================================\n";
echo "  BULK UPDATE READINESS SUMMARY\n";
echo "================================================\n\n";

echo "WHAT WILL HAPPEN:\n";
echo "1. Brain will connect to Vici MySQL to find leads by phone number\n";
echo "2. For each matched lead, Brain will update vendor_lead_code\n";
echo "3. This links Vici leads to Brain's Lead ID for tracking\n\n";

echo "COMMAND TO RUN (from Brain directory):\n";
echo "----------------------------------------\n";
echo "php artisan vici:update-vendor-codes\n\n";

echo "OPTIONS:\n";
echo "  --dry-run           Test without making changes\n";
echo "  --batch=100         Process 100 leads at a time\n";
echo "  --campaigns=Auto2   Filter by campaign\n";
echo "  --phone=2485551234  Update specific phone\n\n";

echo "RECOMMENDED FIRST STEP:\n";
echo "php artisan vici:update-vendor-codes --dry-run --batch=10\n\n";

echo "This will show you what WOULD be updated without making changes.\n";

exit(0);

