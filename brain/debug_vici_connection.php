#!/usr/bin/env php
<?php
/**
 * DEBUG VICIDIAL CONNECTION AND CAMPAIGN SETTINGS
 */

$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

echo "═══════════════════════════════════════════════════════════════\n";
echo "DEBUGGING VICIDIAL CONNECTION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test 1: Basic connection
echo "1. Testing basic proxy connection...\n";
$ch = curl_init($viciProxy);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => 'SELECT NOW()']));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
echo "   Response: " . substr($response, 0, 200) . "...\n\n";

// Test 2: Check what tables we can see
echo "2. Checking available tables...\n";
$queries = [
    "SHOW TABLES LIKE 'vicidial_campaigns'",
    "SELECT COUNT(*) as count FROM vicidial_campaigns",
    "SELECT campaign_id FROM vicidial_campaigns LIMIT 5",
    "SHOW DATABASES",
    "SELECT DATABASE()"
];

foreach ($queries as $query) {
    echo "\n   Query: $query\n";
    
    $ch = curl_init($viciProxy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    if ($result && isset($result['success'])) {
        if ($result['success']) {
            echo "   ✅ Success\n";
            if (isset($result['data'])) {
                echo "   Data: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
            } elseif (isset($result['output'])) {
                // Parse tab-separated output
                $lines = explode("\n", trim($result['output']));
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        echo "   Output: $line\n";
                    }
                }
            }
        } else {
            echo "   ❌ Failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "   ❌ Invalid response\n";
    }
    
    curl_close($ch);
}

// Test 3: Try direct SSH command format
echo "\n3. Testing direct command format...\n";
$directQuery = "mysql -h localhost -u cron -p'1234' asterisk -e \"SELECT campaign_id, dial_method, hopper_level, list_order_mix FROM vicidial_campaigns WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";

$ch = curl_init($viciProxy);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $directQuery]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result && $result['success']) {
    echo "   ✅ Direct query successful\n";
    if (isset($result['output'])) {
        echo "   Raw output:\n";
        $lines = explode("\n", $result['output']);
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                echo "   $line\n";
            }
        }
    }
} else {
    echo "   ❌ Direct query failed\n";
}

curl_close($ch);

// Test 4: Check the correct database
echo "\n4. Testing with correct database (Q6hdjl67GRigMofv)...\n";
$dbQuery = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SELECT campaign_id, dial_method, hopper_level, list_order_mix, next_agent_call, lead_filter_id FROM vicidial_campaigns WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";

$ch = curl_init($viciProxy);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $dbQuery]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$result = json_decode($response, true);

echo "   Full response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

if ($result && $result['success'] && isset($result['output'])) {
    echo "\n   ✅ FOUND CAMPAIGN SETTINGS!\n";
    echo "   Parsing output...\n\n";
    
    $lines = explode("\n", trim($result['output']));
    if (count($lines) >= 2) {
        // First line is headers
        $headers = preg_split('/\s+/', $lines[0]);
        // Second line is data
        $data = preg_split('/\s+/', $lines[1]);
        
        echo "   AUTODIAL Campaign Settings:\n";
        echo "   ═══════════════════════════════════════\n";
        for ($i = 0; $i < count($headers) && $i < count($data); $i++) {
            echo "   " . str_pad($headers[$i] . ":", 20) . $data[$i] . "\n";
        }
    }
}

curl_close($ch);

echo "\n═══════════════════════════════════════════════════════════════\n";




