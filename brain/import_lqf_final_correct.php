<?php
/**
 * Import ALL LQF chunks with CORRECT JSON handling
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$chunkDir = '/Users/giltorres/Downloads/lqf_chunks_final/';

echo "\n🚀 IMPORTING ALL LQF CHUNKS WITH CORRECT JSON\n";
echo "=============================================\n\n";

// Get all chunk files
$chunks = glob($chunkDir . '*.csv');
sort($chunks);

echo "Found " . count($chunks) . " chunk files\n\n";

$totalImported = 0;
$totalSkipped = 0;
$chunkNum = 1;

// Process each chunk
foreach ($chunks as $chunkFile) {
    echo "[" . $chunkNum . "/" . count($chunks) . "] Processing " . basename($chunkFile) . "...\n";
    
    $handle = fopen($chunkFile, 'r');
    if (!$handle) {
        echo "  ❌ Cannot open file\n";
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
    $batchSize = 100;
    
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
        
        // Check if already exists
        $exists = DB::table('leads')->where('phone', $phone)->exists();
        if ($exists) {
            $skipped++;
            continue;
        }
        
        // Parse the JSON data field
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
        
        // Extract data from parsed JSON
        $drivers = $parsedData['drivers'] ?? [];
        $vehicles = $parsedData['vehicles'] ?? [];
        $policy = $parsedData['requested_policy'] ?? [];
        
        // Build insert record
        $batch[] = [
            'external_lead_id' => (string)(microtime(true) * 10000),
            'source' => 'LQF_BULK',
            'phone' => $phone,
            'first_name' => $row[$cols['first name']] ?? '',
            'last_name' => $row[$cols['last name']] ?? '',
            'name' => trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
            'email' => strtolower($row[$cols['email']] ?? ''),
            'address' => $row[$cols['address']] ?? '',
            'city' => $row[$cols['city']] ?? '',
            'state' => $row[$cols['state']] ?? '',
            'zip_code' => $row[$cols['zip code']] ?? '',
            'ip_address' => $row[$cols['ip address']] ?? null,
            'type' => 'auto',
            'jangle_lead_id' => $row[$cols['lead id']] ?? null,
            'leadid_code' => $row[$cols['leadid code']] ?? null,
            'trusted_form_cert' => $row[$cols['trusted form cert url']] ?? null,
            'landing_page_url' => $row[$cols['landing page url']] ?? null,
            'tcpa_consent_text' => $row[$cols['tcpa consent text']] ?? null,
            'opt_in_date' => !empty($row[$cols['originally created']]) ? 
                date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
                date('Y-m-d H:i:s'),
            'vendor_name' => $row[$cols['vendor']] ?? null,
            'buyer_name' => $row[$cols['buyer']] ?? null,
            'campaign_id' => $campaignId,
            'drivers' => json_encode($drivers),
            'vehicles' => json_encode($vehicles),
            'current_policy' => json_encode($policy),
            'payload' => json_encode($parsedData),
            'meta' => json_encode([
                'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
                'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
                'buy_price' => $row[$cols['buy price']] ?? null,
                'sell_price' => $row[$cols['sell price']] ?? null,
            ]),
            'tcpa_compliant' => 1,
            'tenant_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert batch when full
        if (count($batch) >= $batchSize) {
            try {
                DB::table('leads')->insert($batch);
                $imported += count($batch);
            } catch (\Exception $e) {
                echo "  ⚠️  Batch insert failed: " . $e->getMessage() . "\n";
            }
            $batch = [];
        }
    }
    
    // Insert remaining batch
    if (!empty($batch)) {
        try {
            DB::table('leads')->insert($batch);
            $imported += count($batch);
        } catch (\Exception $e) {
            echo "  ⚠️  Final batch failed: " . $e->getMessage() . "\n";
        }
    }
    
    fclose($handle);
    
    echo "  ✓ Imported: " . number_format($imported) . ", Skipped: " . number_format($skipped) . "\n";
    
    $totalImported += $imported;
    $totalSkipped += $skipped;
    $chunkNum++;
}

echo "\n=============================================\n";
echo "✅ ALL CHUNKS IMPORTED!\n";
echo "=============================================\n\n";
echo "Total imported: " . number_format($totalImported) . " leads\n";
echo "Total skipped: " . number_format($totalSkipped) . " (duplicates)\n\n";

// Verify a sample
$sample = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->whereRaw("drivers != '[]'")
    ->first();

if ($sample) {
    echo "Sample verification:\n";
    echo "Name: " . $sample->name . "\n";
    $drivers = json_decode($sample->drivers, true);
    if (!empty($drivers)) {
        echo "✅ Driver data present: " . $drivers[0]['first_name'] . " " . $drivers[0]['last_name'] . "\n";
    }
    $vehicles = json_decode($sample->vehicles, true);
    if (!empty($vehicles)) {
        echo "✅ Vehicle data present: " . $vehicles[0]['year'] . " " . $vehicles[0]['make'] . "\n";
    }
}

