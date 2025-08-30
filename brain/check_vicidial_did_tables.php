#!/usr/bin/env php
<?php
/**
 * CHECK VICIDIAL DID TABLES AND OUTBOUND TRACKING
 * Discover how ViciDial tracks DIDs for monitoring
 */

$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           VICIDIAL DID TABLES INVESTIGATION                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

function executeViciCommand($command, $viciProxy, $apiKey) {
    $ch = curl_init($viciProxy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Check for DID-related tables
echo "1. Checking for DID-related tables...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SHOW TABLES LIKE '%did%'\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   DID Tables found:\n";
    $lines = explode("\n", $result['output']);
    foreach ($lines as $line) {
        if (!empty(trim($line)) && strpos($line, 'Could not') === false && strpos($line, 'Failed') === false) {
            echo "   • $line\n";
        }
    }
}

// Check campaign CID settings
echo "\n2. Checking campaign outbound CIDs...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SELECT campaign_id, campaign_cid, use_custom_cid, custom_cid_number FROM vicidial_campaigns WHERE campaign_id IN ('AUTODIAL', 'AUTO2')\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   Campaign CID settings:\n";
    echo $result['output'] . "\n";
}

// Check for phone numbers/DIDs
echo "\n3. Checking phones table for DIDs...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SELECT extension, dialplan_number, outbound_cid FROM phones LIMIT 5\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   Phone/DID configuration:\n";
    echo $result['output'] . "\n";
}

// Check vicidial_log for outbound CID usage
echo "\n4. Checking call logs for CID usage patterns...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SELECT DATE(call_date) as date, campaign_id, phone_code, COUNT(*) as calls, AVG(CASE WHEN status IN ('A','XFER','XFERA') THEN 1 ELSE 0 END) * 100 as answer_rate FROM vicidial_log WHERE call_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND campaign_id IN ('AUTODIAL', 'AUTO2') GROUP BY DATE(call_date), campaign_id, phone_code LIMIT 10\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   Recent call patterns:\n";
    echo $result['output'] . "\n";
}

// Check for carrier log (tracks outbound calls)
echo "\n5. Checking vicidial_carrier_log for outbound tracking...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"DESCRIBE vicidial_carrier_log\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   Carrier log structure found:\n";
    $lines = explode("\n", $result['output']);
    foreach ($lines as $line) {
        if (!empty(trim($line)) && strpos($line, 'Could not') === false) {
            echo "   $line\n";
        }
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                     ANALYSIS COMPLETE                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";












