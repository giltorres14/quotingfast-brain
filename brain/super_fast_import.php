#!/usr/bin/env php
<?php
/**
 * ULTRA FAST BULK IMPORT - Direct SQL with minimal overhead
 * Processes both Suraj and LQF data at maximum speed
 */

$dbConfig = [
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'dbname' => 'brain_production',
    'user' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
];

$dsn = "pgsql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}";
$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "========================================\n";
echo "ULTRA FAST BULK IMPORT\n";
echo "========================================\n\n";

// Get current counts
$stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE source LIKE '%SURAJ%'");
$surajStart = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE source LIKE '%LQF%'");
$lqfStart = $stmt->fetchColumn();

echo "📊 Current Status:\n";
echo "   Suraj: " . number_format($surajStart) . " leads\n";
echo "   LQF: " . number_format($lqfStart) . " leads\n\n";

// Load existing phones into memory for super fast duplicate checking
echo "📱 Loading phone index...\n";
$stmt = $pdo->query("SELECT phone FROM leads WHERE phone IS NOT NULL");
$existingPhones = [];
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $existingPhones[$row[0]] = 1;
}
$phoneCount = count($existingPhones);
echo "   Loaded " . number_format($phoneCount) . " phones\n\n";

// Process Suraj files
$surajFolder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$surajFiles = glob($surajFolder . '/*.csv');
$totalSurajFiles = count($surajFiles);

echo "📂 SURAJ IMPORT\n";
echo "   Files: $totalSurajFiles\n";
echo "   Size: 89 MB\n";

$startTime = microtime(true);
$surajImported = 0;
$surajDuplicates = 0;

// Prepare bulk insert statement
$insertStmt = $pdo->prepare("
    INSERT INTO leads (
        external_lead_id, phone, name, first_name, last_name, email,
        source, type, tenant_id, tcpa_compliant, campaign_id,
        vendor_name, buyer_name, opt_in_date, meta, payload,
        received_at, joined_at, created_at, updated_at
    ) VALUES (
        :external_lead_id, :phone, :name, :first_name, :last_name, :email,
        :source, :type, :tenant_id, :tcpa_compliant, :campaign_id,
        :vendor_name, :buyer_name, :opt_in_date, :meta, :payload,
        :received_at, :joined_at, :created_at, :updated_at
    )
    ON CONFLICT (phone) DO NOTHING
");

// Process each Suraj file
foreach ($surajFiles as $fileNum => $file) {
    $filename = basename($file);
    $progress = round(($fileNum + 1) / $totalSurajFiles * 100);
    echo "\r   Processing: $progress% - $filename";
    
    $handle = fopen($file, 'r');
    if (!$handle) continue;
    
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        continue;
    }
    
    // Auto-map columns
    $columnMap = [];
    foreach ($headers as $i => $header) {
        $normalized = strtolower(str_replace([' ', '_', '-'], '', trim($header)));
        if ($normalized == 'phone' || $normalized == 'phonenumber') $columnMap['phone'] = $i;
        elseif ($normalized == 'firstname') $columnMap['first_name'] = $i;
        elseif ($normalized == 'lastname') $columnMap['last_name'] = $i;
        elseif ($normalized == 'email') $columnMap['email'] = $i;
        elseif ($normalized == 'timestamp') $columnMap['timestamp'] = $i;
        elseif ($normalized == 'buyercampaignid') $columnMap['campaign_id'] = $i;
        elseif ($normalized == 'buyername') $columnMap['buyer_name'] = $i;
        elseif ($normalized == 'vendorname') $columnMap['vendor_name'] = $i;
    }
    
    $batch = [];
    $batchSize = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        // Extract phone
        if (!isset($columnMap['phone']) || !isset($row[$columnMap['phone']])) continue;
        $phone = preg_replace('/[^0-9]/', '', $row[$columnMap['phone']]);
        if (strlen($phone) == 11 && $phone[0] == '1') $phone = substr($phone, 1);
        if (strlen($phone) != 10) continue;
        
        // Skip duplicates
        if (isset($existingPhones[$phone])) {
            $surajDuplicates++;
            continue;
        }
        
        $existingPhones[$phone] = 1;
        
        // Build lead data
        $timestamp = round(microtime(true) * 1000);
        $now = date('Y-m-d H:i:s');
        
        $leadData = [
            'external_lead_id' => substr($timestamp . '000', 0, 13),
            'phone' => $phone,
            'name' => 'Unknown',
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'source' => 'SURAJ_BULK',
            'type' => 'auto',
            'tenant_id' => 1,
            'tcpa_compliant' => true,
            'campaign_id' => null,
            'vendor_name' => null,
            'buyer_name' => null,
            'opt_in_date' => null,
            'meta' => json_encode(['source_file' => $filename]),
            'payload' => json_encode(array_combine($headers, $row)),
            'received_at' => $now,
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ];
        
        // Map fields
        if (isset($columnMap['first_name']) && isset($row[$columnMap['first_name']])) {
            $leadData['first_name'] = trim($row[$columnMap['first_name']]);
        }
        if (isset($columnMap['last_name']) && isset($row[$columnMap['last_name']])) {
            $leadData['last_name'] = trim($row[$columnMap['last_name']]);
        }
        $leadData['name'] = trim(($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? '')) ?: 'Unknown';
        
        if (isset($columnMap['email']) && isset($row[$columnMap['email']])) {
            $leadData['email'] = trim($row[$columnMap['email']]);
        }
        if (isset($columnMap['campaign_id']) && isset($row[$columnMap['campaign_id']])) {
            $val = trim($row[$columnMap['campaign_id']]);
            if ($val) $leadData['campaign_id'] = rtrim(rtrim($val, '0'), '.');
        }
        if (isset($columnMap['buyer_name']) && isset($row[$columnMap['buyer_name']])) {
            $leadData['buyer_name'] = trim($row[$columnMap['buyer_name']]);
        }
        if (isset($columnMap['vendor_name']) && isset($row[$columnMap['vendor_name']])) {
            $leadData['vendor_name'] = trim($row[$columnMap['vendor_name']]);
        }
        if (isset($columnMap['timestamp']) && isset($row[$columnMap['timestamp']])) {
            $ts = trim($row[$columnMap['timestamp']]);
            if ($ts) {
                try {
                    $leadData['opt_in_date'] = date('Y-m-d H:i:s', strtotime($ts));
                } catch (Exception $e) {}
            }
        }
        
        // Execute insert
        try {
            $insertStmt->execute($leadData);
            $surajImported++;
        } catch (Exception $e) {
            // Skip on error
        }
        
        // Show progress
        if ($surajImported % 100 == 0) {
            echo "\r   Processing: $progress% - Imported: " . number_format($surajImported) . " | Dupes: " . number_format($surajDuplicates);
        }
    }
    
    fclose($handle);
}

$surajDuration = round(microtime(true) - $startTime, 2);
echo "\n   ✅ Suraj Complete: " . number_format($surajImported) . " imported in {$surajDuration}s\n";
echo "   Speed: " . round($surajImported / max($surajDuration, 1)) . " leads/second\n\n";

// Process LQF file
echo "📂 LQF IMPORT\n";
$lqfFile = $_SERVER['HOME'] . '/Downloads/LQF/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';
echo "   File: 397 MB\n";
echo "   Estimated records: ~170,000\n";

$startTime = microtime(true);
$lqfImported = 0;
$lqfDuplicates = 0;

$handle = fopen($lqfFile, 'r');
if ($handle) {
    $headers = fgetcsv($handle);
    
    // Map LQF columns
    $columnMap = [];
    foreach ($headers as $i => $header) {
        $normalized = strtolower(trim($header));
        $columnMap[$normalized] = $i;
    }
    
    $totalLines = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $totalLines++;
        
        // Get phone
        $phoneIdx = $columnMap['phone'] ?? $columnMap['phone number'] ?? null;
        if ($phoneIdx === null || !isset($row[$phoneIdx])) continue;
        
        $phone = preg_replace('/[^0-9]/', '', $row[$phoneIdx]);
        if (strlen($phone) == 11 && $phone[0] == '1') $phone = substr($phone, 1);
        if (strlen($phone) != 10) continue;
        
        // Skip duplicates
        if (isset($existingPhones[$phone])) {
            $lqfDuplicates++;
            continue;
        }
        
        $existingPhones[$phone] = 1;
        
        // Build lead data
        $timestamp = round(microtime(true) * 1000);
        $now = date('Y-m-d H:i:s');
        
        // Parse the Data column (last column with JSON)
        $dataIdx = $columnMap['data'] ?? count($row) - 1;
        $jsonData = [];
        if (isset($row[$dataIdx])) {
            try {
                $jsonData = json_decode($row[$dataIdx], true) ?: [];
            } catch (Exception $e) {}
        }
        
        $leadData = [
            'external_lead_id' => substr($timestamp . '000', 0, 13),
            'phone' => $phone,
            'name' => 'Unknown',
            'first_name' => $row[$columnMap['first name'] ?? -1] ?? null,
            'last_name' => $row[$columnMap['last name'] ?? -1] ?? null,
            'email' => $row[$columnMap['email'] ?? -1] ?? null,
            'source' => 'LQF_BULK',
            'type' => 'auto',
            'tenant_id' => 1,
            'tcpa_compliant' => true,
            'campaign_id' => null,
            'vendor_name' => $row[$columnMap['vendor'] ?? -1] ?? null,
            'buyer_name' => $row[$columnMap['buyer'] ?? -1] ?? null,
            'opt_in_date' => null,
            'meta' => json_encode(['source' => 'LQF bulk import']),
            'payload' => json_encode(array_combine($headers, $row)),
            'received_at' => $now,
            'joined_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            
            // Additional LQF fields
            'jangle_lead_id' => $row[$columnMap['lead id'] ?? -1] ?? null,
            'leadid_code' => $row[$columnMap['leadid code'] ?? -1] ?? null,
            'trusted_form_cert' => $row[$columnMap['trusted form cert url'] ?? -1] ?? null,
            'landing_page_url' => $row[$columnMap['landing page url'] ?? -1] ?? null,
            'tcpa_consent_text' => $row[$columnMap['tcpa consent text'] ?? -1] ?? null,
            'ip_address' => $row[$columnMap['ip address'] ?? -1] ?? null,
        ];
        
        // Set name
        $leadData['name'] = trim(($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? '')) ?: 'Unknown';
        
        // Extract campaign from buyer campaign
        $buyerCampaign = $row[$columnMap['buyer campaign'] ?? -1] ?? null;
        if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
            $leadData['campaign_id'] = $matches[1];
        }
        
        // Set opt_in_date from originally created
        $origCreated = $row[$columnMap['originally created'] ?? -1] ?? null;
        if ($origCreated) {
            try {
                $leadData['opt_in_date'] = date('Y-m-d H:i:s', strtotime($origCreated));
            } catch (Exception $e) {}
        }
        
        // Store JSON data if exists
        if (!empty($jsonData)) {
            if (isset($jsonData['drivers'])) {
                $leadData['drivers'] = json_encode($jsonData['drivers']);
            }
            if (isset($jsonData['vehicles'])) {
                $leadData['vehicles'] = json_encode($jsonData['vehicles']);
            }
        }
        
        // Execute insert
        try {
            $insertStmt->execute($leadData);
            $lqfImported++;
        } catch (Exception $e) {
            // Skip on error
        }
        
        // Show progress
        if ($totalLines % 500 == 0) {
            $progress = round($totalLines / 170000 * 100);
            echo "\r   Processing: ~$progress% - Imported: " . number_format($lqfImported) . " | Dupes: " . number_format($lqfDuplicates);
        }
    }
    
    fclose($handle);
}

$lqfDuration = round(microtime(true) - $startTime, 2);
echo "\n   ✅ LQF Complete: " . number_format($lqfImported) . " imported in {$lqfDuration}s\n";
echo "   Speed: " . round($lqfImported / max($lqfDuration, 1)) . " leads/second\n\n";

// Final counts
$stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE source LIKE '%SURAJ%'");
$surajFinal = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE source LIKE '%LQF%'");
$lqfFinal = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM leads");
$totalFinal = $stmt->fetchColumn();

echo "========================================\n";
echo "IMPORT COMPLETE\n";
echo "========================================\n";
echo "📊 Final Status:\n";
echo "   Suraj: " . number_format($surajFinal) . " (+" . number_format($surajFinal - $surajStart) . ")\n";
echo "   LQF: " . number_format($lqfFinal) . " (+" . number_format($lqfFinal - $lqfStart) . ")\n";
echo "   Total: " . number_format($totalFinal) . " leads\n\n";

$totalDuration = round($surajDuration + $lqfDuration, 2);
$totalImported = $surajImported + $lqfImported;
echo "⏱️  Total Time: {$totalDuration} seconds\n";
echo "⚡ Average Speed: " . round($totalImported / max($totalDuration, 1)) . " leads/second\n";

// Estimated times based on current speed
$surajRemaining = max(0, 100000 - $surajFinal); // Estimate 100k total Suraj leads
$lqfRemaining = max(0, 170000 - $lqfFinal); // Estimate 170k total LQF leads

if ($surajRemaining > 0 || $lqfRemaining > 0) {
    echo "\n📅 ESTIMATED COMPLETION:\n";
    
    if ($surajRemaining > 0) {
        $surajSpeed = $surajImported / max($surajDuration, 1);
        $surajEta = round($surajRemaining / max($surajSpeed, 1) / 60);
        echo "   Suraj: ~$surajEta minutes remaining\n";
    } else {
        echo "   Suraj: ✅ Complete\n";
    }
    
    if ($lqfRemaining > 0) {
        $lqfSpeed = $lqfImported / max($lqfDuration, 1);
        $lqfEta = round($lqfRemaining / max($lqfSpeed, 1) / 60);
        echo "   LQF: ~$lqfEta minutes remaining\n";
    } else {
        echo "   LQF: ✅ Complete\n";
    }
}

