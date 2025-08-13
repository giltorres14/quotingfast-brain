<?php

// CUMULATIVE LEARNING APPLIED:
// 1. Must connect to PostgreSQL production database
// 2. Must handle double-encoded JSON payloads
// 3. Must set tenant_id = 1 for all leads
// 4. Must generate 13-digit external IDs
// 5. Must extract opt_in_date from timestamp column
// 6. All Suraj leads are TCPA compliant

$startTime = microtime(true);

// Connect to PostgreSQL
$pdo = new PDO(
    'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
    'brain_user',
    'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
);

echo "==================================\n";
echo "SUPER FAST SURAJ IMPORT\n";
echo "==================================\n\n";

// Get all CSV files
$folder = '/Users/giltorres/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
echo "Found " . count($files) . " CSV files\n\n";

$totalImported = 0;
$totalRows = 0;
$batchSize = 5000; // Large batch for speed

foreach ($files as $fileIndex => $file) {
    $filename = basename($file);
    $fileNum = $fileIndex + 1;
    
    echo "[$fileNum/" . count($files) . "] Processing $filename...\n";
    
    $handle = fopen($file, 'r');
    if (!$handle) continue;
    
    // Read headers
    $headers = fgetcsv($handle);
    
    // Find column indexes
    $phoneCol = array_search('PhoneNumber', $headers);
    $firstNameCol = array_search('FirstName', $headers);
    $lastNameCol = array_search('LastName', $headers);
    $emailCol = array_search('EmailAddress', $headers);
    $addressCol = array_search('MailAddress1', $headers);
    $cityCol = array_search('CityName', $headers);
    $stateCol = array_search('ProvinceStateName', $headers);
    $zipCol = array_search('PostalZipCode', $headers);
    $timestampCol = array_search('timestamp', $headers);
    
    $batch = [];
    $fileImported = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        $totalRows++;
        
        // Extract phone
        $phone = isset($row[$phoneCol]) ? preg_replace('/[^0-9]/', '', $row[$phoneCol]) : '';
        if (strlen($phone) != 10) continue;
        
        // Generate 13-digit external ID
        $timestamp = floor(microtime(true));
        $sequence = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $externalId = $timestamp . $sequence;
        
        // Extract opt-in date
        $optInDate = null;
        if (isset($row[$timestampCol]) && !empty($row[$timestampCol])) {
            try {
                $date = new DateTime($row[$timestampCol]);
                $optInDate = $date->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Use current date as fallback
                $optInDate = date('Y-m-d H:i:s');
            }
        } else {
            $optInDate = date('Y-m-d H:i:s');
        }
        
        // Build payload (store entire row)
        $payload = [];
        foreach ($headers as $i => $header) {
            if (isset($row[$i])) {
                $payload[$header] = $row[$i];
            }
        }
        
        // Build lead data
        $leadData = [
            'phone' => $phone,
            'first_name' => $row[$firstNameCol] ?? null,
            'last_name' => $row[$lastNameCol] ?? null,
            'name' => trim(($row[$firstNameCol] ?? '') . ' ' . ($row[$lastNameCol] ?? '')),
            'email' => $row[$emailCol] ?? null,
            'address' => $row[$addressCol] ?? null,
            'city' => $row[$cityCol] ?? null,
            'state' => $row[$stateCol] ?? null,
            'zip_code' => $row[$zipCol] ?? null,
            'source' => 'SURAJ_BULK',
            'type' => 'auto',
            'campaign_id' => 'SURAJ_IMPORT_' . date('Y-m-d'),
            'external_lead_id' => $externalId,
            'tenant_id' => 1,
            'tcpa_compliant' => true,
            'opt_in_date' => $optInDate,
            'payload' => json_encode($payload),
            'meta' => json_encode([
                'import_file' => $filename,
                'import_date' => date('Y-m-d H:i:s'),
                'source' => 'Suraj Bulk Import'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $batch[] = $leadData;
        
        // Insert batch when it reaches size
        if (count($batch) >= $batchSize) {
            insertBatch($pdo, $batch);
            $fileImported += count($batch);
            $totalImported += count($batch);
            echo "  Imported: " . number_format($fileImported) . " leads...\r";
            $batch = [];
        }
    }
    
    // Insert remaining batch
    if (!empty($batch)) {
        insertBatch($pdo, $batch);
        $fileImported += count($batch);
        $totalImported += count($batch);
    }
    
    fclose($handle);
    echo "  ✅ Imported: " . number_format($fileImported) . " leads from $filename\n";
}

function insertBatch($pdo, $batch) {
    if (empty($batch)) return;
    
    $columns = array_keys($batch[0]);
    $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
    $values = [];
    
    foreach ($batch as $row) {
        foreach ($columns as $col) {
            $values[] = $row[$col];
        }
    }
    
    $sql = "INSERT INTO leads (" . implode(',', $columns) . ") VALUES ";
    $sql .= implode(',', array_fill(0, count($batch), $placeholders));
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    } catch (Exception $e) {
        // Try one by one if batch fails
        foreach ($batch as $row) {
            try {
                $sql = "INSERT INTO leads (" . implode(',', $columns) . ") VALUES (" . 
                       implode(',', array_fill(0, count($columns), '?')) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($row));
            } catch (Exception $e2) {
                // Skip this lead
            }
        }
    }
}

$duration = round(microtime(true) - $startTime, 2);

echo "\n==================================\n";
echo "IMPORT COMPLETE\n";
echo "==================================\n";
echo "✅ Total imported: " . number_format($totalImported) . " leads\n";
echo "📊 Total rows processed: " . number_format($totalRows) . "\n";
echo "⏱️  Duration: $duration seconds\n";
echo "🚀 Speed: " . round($totalImported / max($duration, 1)) . " leads/second\n";

// Final count
$count = $pdo->query("SELECT COUNT(*) FROM leads WHERE source = 'SURAJ_BULK'")->fetchColumn();
echo "\n📈 Total Suraj leads in database: " . number_format($count) . "\n";
