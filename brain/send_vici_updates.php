<?php

echo "=== SENDING VICI UPDATES TO SERVER ===\n\n";

// Read the SQL file
$sqlContent = file_get_contents('vici_single_update.sql');

// Extract UPDATE statements
$pattern = '/UPDATE vicidial_list.*?(?=UPDATE vicidial_list|SELECT COUNT|$)/s';
preg_match_all($pattern, $sqlContent, $matches);

$updates = array_filter($matches[0], function($u) {
    return strpos($u, 'UPDATE') === 0;
});

echo "Found " . count($updates) . " UPDATE statements\n\n";

// Prepare updates with USE statement
$preparedUpdates = array_map(function($update) {
    return "USE Q6hdjl67GRigMofv;\n" . trim($update);
}, $updates);

// Send in batches of 5
$batchSize = 5;
$batches = array_chunk($preparedUpdates, $batchSize);
$totalSuccess = 0;
$totalFailed = 0;
$totalRows = 0;

foreach ($batches as $idx => $batch) {
    $batchNum = $idx + 1;
    echo "Sending batch $batchNum/" . count($batches) . "... ";
    
    $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-update/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['updates' => $batch]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        $totalSuccess += $result['success'] ?? 0;
        $totalFailed += $result['failed'] ?? 0;
        $totalRows += $result['rows_updated'] ?? 0;
        
        echo "✅ Success: " . $result['success'] . ", Failed: " . $result['failed'] . ", Rows: " . $result['rows_updated'] . "\n";
        
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                echo "  ⚠️  $error\n";
            }
        }
    } else {
        echo "❌ Failed (HTTP $httpCode)\n";
        $totalFailed += count($batch);
    }
    
    // Progress
    if ($batchNum % 5 == 0) {
        echo "  Progress: " . round($batchNum / count($batches) * 100) . "%\n";
    }
    
    // Small delay between batches
    usleep(500000); // 0.5 seconds
}

echo "\n=== COMPLETE ===\n";
echo "✅ Successful updates: $totalSuccess\n";
echo "❌ Failed updates: $totalFailed\n";
echo "📊 Total rows updated: $totalRows\n";

// Get final count
echo "\nVerifying final count...\n";
$ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'command' => "mysql -u root Q6hdjl67GRigMofv -e \"SELECT COUNT(*) as 'Vici leads with Brain IDs' FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,8001,8002,8003,8004,8005,8006,8007,8008,10006,10007,10008,10009,10010,10011,7010,7011,7012,60010,60020);\" 2>&1"
]));

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    $output = $result['output'] ?? '';
    // Clean output
    $output = str_replace("Could not create directory '/var/www/.ssh' (Permission denied).", "", $output);
    $output = str_replace("Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts).", "", $output);
    echo trim($output) . "\n";
}



echo "=== SENDING VICI UPDATES TO SERVER ===\n\n";

// Read the SQL file
$sqlContent = file_get_contents('vici_single_update.sql');

// Extract UPDATE statements
$pattern = '/UPDATE vicidial_list.*?(?=UPDATE vicidial_list|SELECT COUNT|$)/s';
preg_match_all($pattern, $sqlContent, $matches);

$updates = array_filter($matches[0], function($u) {
    return strpos($u, 'UPDATE') === 0;
});

echo "Found " . count($updates) . " UPDATE statements\n\n";

// Prepare updates with USE statement
$preparedUpdates = array_map(function($update) {
    return "USE Q6hdjl67GRigMofv;\n" . trim($update);
}, $updates);

// Send in batches of 5
$batchSize = 5;
$batches = array_chunk($preparedUpdates, $batchSize);
$totalSuccess = 0;
$totalFailed = 0;
$totalRows = 0;

foreach ($batches as $idx => $batch) {
    $batchNum = $idx + 1;
    echo "Sending batch $batchNum/" . count($batches) . "... ";
    
    $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-update/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['updates' => $batch]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        $totalSuccess += $result['success'] ?? 0;
        $totalFailed += $result['failed'] ?? 0;
        $totalRows += $result['rows_updated'] ?? 0;
        
        echo "✅ Success: " . $result['success'] . ", Failed: " . $result['failed'] . ", Rows: " . $result['rows_updated'] . "\n";
        
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                echo "  ⚠️  $error\n";
            }
        }
    } else {
        echo "❌ Failed (HTTP $httpCode)\n";
        $totalFailed += count($batch);
    }
    
    // Progress
    if ($batchNum % 5 == 0) {
        echo "  Progress: " . round($batchNum / count($batches) * 100) . "%\n";
    }
    
    // Small delay between batches
    usleep(500000); // 0.5 seconds
}

echo "\n=== COMPLETE ===\n";
echo "✅ Successful updates: $totalSuccess\n";
echo "❌ Failed updates: $totalFailed\n";
echo "📊 Total rows updated: $totalRows\n";

// Get final count
echo "\nVerifying final count...\n";
$ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'command' => "mysql -u root Q6hdjl67GRigMofv -e \"SELECT COUNT(*) as 'Vici leads with Brain IDs' FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,8001,8002,8003,8004,8005,8006,8007,8008,10006,10007,10008,10009,10010,10011,7010,7011,7012,60010,60020);\" 2>&1"
]));

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    $output = $result['output'] ?? '';
    // Clean output
    $output = str_replace("Could not create directory '/var/www/.ssh' (Permission denied).", "", $output);
    $output = str_replace("Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts).", "", $output);
    echo trim($output) . "\n";
}


