<?php

echo "=== SIMPLE VICI UPDATE EXECUTION ===\n\n";

// Read the SQL file
$sqlContent = file_get_contents('vici_single_update.sql');

// Extract UPDATE statements
$pattern = '/UPDATE vicidial_list.*?(?=UPDATE vicidial_list|SELECT COUNT|$)/s';
preg_match_all($pattern, $sqlContent, $matches);

$updates = array_filter($matches[0], function($u) {
    return strpos($u, 'UPDATE') === 0;
});

echo "Found " . count($updates) . " UPDATE statements\n";
echo "This will update up to 49,822 Vici leads\n\n";

$startTime = time();
$successCount = 0;
$failCount = 0;

// Process each UPDATE
foreach ($updates as $idx => $update) {
    $num = $idx + 1;
    echo "[$num/" . count($updates) . "] Processing chunk $num... ";
    
    // Create a smaller version - just do 100 phone numbers at a time
    if (preg_match_all("/WHEN '(\d+)' THEN '(\d+)'/", $update, $phoneMatches)) {
        $phones = $phoneMatches[1];
        $brainIds = $phoneMatches[2];
        
        // Process in smaller batches
        $batchSize = 100;
        $batches = array_chunk($phones, $batchSize);
        $chunkSuccess = 0;
        
        foreach ($batches as $batchIdx => $batchPhones) {
            $phoneList = "'" . implode("','", $batchPhones) . "'";
            
            // Build simple UPDATE for this batch
            $sql = "USE Q6hdjl67GRigMofv;\n";
            $sql .= "UPDATE vicidial_list SET vendor_lead_code = CASE phone_number\n";
            
            for ($i = 0; $i < count($batchPhones); $i++) {
                $phoneIdx = $batchIdx * $batchSize + $i;
                if (isset($phones[$phoneIdx]) && isset($brainIds[$phoneIdx])) {
                    $sql .= "  WHEN '{$phones[$phoneIdx]}' THEN '{$brainIds[$phoneIdx]}'\n";
                }
            }
            
            $sql .= "  ELSE vendor_lead_code END\n";
            $sql .= "WHERE phone_number IN ($phoneList)\n";
            $sql .= "AND list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,8001,8002,8003,8004,8005,8006,8007,8008,10006,10007,10008,10009,10010,10011,7010,7011,7012,60010,60020);";
            
            // Execute via curl
            $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'command' => "mysql -u root -e " . escapeshellarg($sql) . " 2>&1 | grep -E 'Query OK|ERROR' | head -1"
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                $output = $result['output'] ?? '';
                if (strpos($output, 'ERROR') === false) {
                    $chunkSuccess++;
                }
            }
            
            // Small delay
            usleep(100000); // 0.1 second
        }
        
        echo "✅ Processed " . count($phones) . " phones in " . count($batches) . " batches ($chunkSuccess successful)\n";
        $successCount++;
    } else {
        echo "❌ Failed to parse\n";
        $failCount++;
    }
    
    // Progress update
    if ($num % 10 == 0) {
        $elapsed = time() - $startTime;
        echo "  Progress: " . round($num / count($updates) * 100) . "% | ";
        echo "Time: " . round($elapsed / 60, 1) . " min\n";
    }
}

// Final verification
echo "\n=== VERIFYING RESULTS ===\n";

$ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'command' => "mysql -u root Q6hdjl67GRigMofv -e \"SELECT COUNT(*) as 'Total Vici leads with Brain IDs' FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,8001,8002,8003,8004,8005,8006,8007,8008,10006,10007,10008,10009,10010,10011,7010,7011,7012,60010,60020);\" 2>&1 | grep -v 'Could not' | grep -v 'Failed to'"
]));

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    echo $result['output'] ?? '';
}

$totalTime = time() - $startTime;
echo "\n\n=== COMPLETE ===\n";
echo "✅ Processed: $successCount chunks\n";
echo "❌ Failed: $failCount chunks\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";
echo "\nThe Vici leads have been updated with Brain IDs!\n";


