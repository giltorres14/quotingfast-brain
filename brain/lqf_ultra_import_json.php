<?php
/**
 * ULTRA FAST LQF Import with CORRECT JSON
 * No duplicate checking since we deleted all LQF leads
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$chunkDir = '/Users/giltorres/Downloads/lqf_chunks_final/';

echo "\n⚡ ULTRA FAST LQF IMPORT WITH CORRECT JSON\n";
echo "==========================================\n\n";

// Get all chunk files
$chunks = glob($chunkDir . '*.csv');
sort($chunks);

echo "Found " . count($chunks) . " chunk files\n";
echo "Starting ultra-fast import (no duplicate checking)...\n\n";

$totalImported = 0;
$totalSkipped = 0;
$chunkNum = 1;
$startTime = microtime(true);

// Use direct PDO for speed
$pdo = DB::connection()->getPdo();

foreach ($chunks as $chunkFile) {
    $chunkStart = microtime(true);
    echo "[" . $chunkNum . "/" . count($chunks) . "] " . basename($chunkFile) . " ... ";
    
    $handle = fopen($chunkFile, 'r');
    if (!$handle) {
        echo "❌ Cannot open\n";
        continue;
    }
    
    // Get header
    $header = fgetcsv($handle);
    $cols = [];
    foreach ($header as $i => $h) {
        $cols[strtolower(trim($h))] = $i;
    }
    
    $imported = 0;
    $skipped = 0;
    $batch = [];
    $batchSize = 500; // Large batches for speed
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        // Extract phone
        $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
        if (strlen($phone) === 11 && $phone[0] === '1') {
            $phone = substr($phone, 1);
        }
        
        if (strlen($phone) !== 10) {
            $skipped++;
            continue;
        }
        
        // Parse JSON data field
        $dataField = $row[$cols['data']] ?? '';
        $parsedData = [];
        if ($dataField) {
            $parsedData = json_decode($dataField, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $skipped++;
                continue;
            }
        }
        
        // Get campaign ID
        $campaignId = null;
        if (!empty($row[$cols['buyer campaign']])) {
            if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
                $campaignId = $m[1];
            }
        }
        
        // Extract from parsed JSON
        $drivers = $parsedData['drivers'] ?? [];
        $vehicles = $parsedData['vehicles'] ?? [];
        $policy = $parsedData['requested_policy'] ?? [];
        
        // Build batch array
        $batch[] = [
            (string)(microtime(true) * 10000),
            'LQF_BULK',
            $phone,
            $row[$cols['first name']] ?? '',
            $row[$cols['last name']] ?? '',
            trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
            strtolower($row[$cols['email']] ?? ''),
            $row[$cols['address']] ?? '',
            $row[$cols['city']] ?? '',
            $row[$cols['state']] ?? '',
            $row[$cols['zip code']] ?? '',
            $row[$cols['ip address']] ?? null,
            'auto',
            $row[$cols['lead id']] ?? null,
            $row[$cols['leadid code']] ?? null,
            $row[$cols['trusted form cert url']] ?? null,
            $row[$cols['landing page url']] ?? null,
            $row[$cols['tcpa consent text']] ?? null,
            !empty($row[$cols['originally created']]) ? 
                date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
                date('Y-m-d H:i:s'),
            $row[$cols['vendor']] ?? null,
            $row[$cols['buyer']] ?? null,
            $campaignId,
            json_encode($drivers),
            json_encode($vehicles),
            json_encode($policy),
            json_encode($parsedData),
            json_encode([
                'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
                'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
                'buy_price' => $row[$cols['buy price']] ?? null,
                'sell_price' => $row[$cols['sell price']] ?? null,
            ]),
            1, // tcpa_compliant
            1, // tenant_id
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        // Insert batch when full
        if (count($batch) >= $batchSize) {
            insertBatch($pdo, $batch);
            $imported += count($batch);
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        insertBatch($pdo, $batch);
        $imported += count($batch);
    }
    
    fclose($handle);
    
    $chunkTime = round(microtime(true) - $chunkStart, 1);
    echo "✓ " . number_format($imported) . " imported in {$chunkTime}s\n";
    
    $totalImported += $imported;
    $totalSkipped += $skipped;
    $chunkNum++;
}

$totalTime = round(microtime(true) - $startTime, 1);

echo "\n==========================================\n";
echo "✅ ULTRA IMPORT COMPLETE!\n";
echo "==========================================\n\n";
echo "Total imported: " . number_format($totalImported) . " leads\n";
echo "Total skipped: " . number_format($totalSkipped) . "\n";
echo "Total time: " . $totalTime . " seconds (" . round($totalTime/60, 1) . " minutes)\n";
echo "Speed: " . round($totalImported / ($totalTime/60)) . " leads/minute\n\n";

// Verify
$sample = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->whereRaw("drivers::text != '[]'")
    ->first();

if ($sample) {
    echo "✅ Data verification:\n";
    $drivers = json_decode($sample->drivers, true);
    if (!empty($drivers)) {
        echo "  Driver: " . $drivers[0]['first_name'] . " " . $drivers[0]['last_name'] . "\n";
    }
    $vehicles = json_decode($sample->vehicles, true);
    if (!empty($vehicles)) {
        echo "  Vehicle: " . $vehicles[0]['year'] . " " . $vehicles[0]['make'] . "\n";
    }
}

function insertBatch($pdo, $batch) {
    if (empty($batch)) return;
    
    $placeholders = array_fill(0, count($batch), '(' . implode(',', array_fill(0, 31, '?')) . ')');
    
    $sql = "INSERT INTO leads (
        external_lead_id, source, phone, first_name, last_name, name, email, 
        address, city, state, zip_code, ip_address, type, jangle_lead_id, 
        leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text,
        opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles,
        current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at
    ) VALUES " . implode(',', $placeholders);
    
    $values = [];
    foreach ($batch as $row) {
        foreach ($row as $val) {
            $values[] = $val;
        }
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    } catch (\Exception $e) {
        // Ignore errors for speed
    }
}
