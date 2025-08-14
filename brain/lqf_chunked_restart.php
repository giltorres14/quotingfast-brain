<?php
/**
 * LQF Import with auto-restart on memory failure
 * Imports in chunks and restarts when it fails
 */

ini_set('memory_limit', '512M'); // Don't go too high, we want it to fail and restart
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    die("File not found: $csvFile\n");
}

// Get restart count from file
$restartFile = __DIR__ . '/lqf_restart_count.txt';
$restartCount = 0;
if (file_exists($restartFile)) {
    $restartCount = (int)file_get_contents($restartFile);
}
$restartCount++;
file_put_contents($restartFile, $restartCount);

echo "\n🔄 LQF CHUNKED IMPORT - RESTART #$restartCount\n";
echo "========================================\n\n";

// Kill any existing slow import
$pid = trim(shell_exec("ps aux | grep 'artisan lqf:bulk-import' | grep -v grep | awk '{print $2}'"));
if ($pid) {
    echo "Killing slow import (PID: $pid)...\n";
    shell_exec("kill $pid");
}

// Pre-load existing phones for duplicate checking - but limit memory usage
echo "Loading existing phone numbers...\n";
$existingPhones = [];
$phoneChunks = DB::table('leads')
    ->select('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('phone')
    ->orderBy('id', 'desc')
    ->limit(100000) // Only load recent 100k to save memory
    ->pluck('phone')
    ->toArray();

foreach ($phoneChunks as $phone) {
    $existingPhones[$phone] = 1;
}
echo "✓ Loaded " . number_format(count($existingPhones)) . " recent phones\n\n";

// Track position in file
$positionFile = __DIR__ . '/lqf_position.txt';
$startLine = 1;
if (file_exists($positionFile)) {
    $startLine = (int)file_get_contents($positionFile);
}

echo "Starting from line: " . number_format($startLine) . "\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header
$header = fgetcsv($handle);
$columnMap = [];
foreach ($header as $index => $col) {
    $normalized = strtolower(trim($col));
    $columnMap[$normalized] = $index;
}

// Skip to start position
$currentLine = 1;
while ($currentLine < $startLine && !feof($handle)) {
    fgetcsv($handle);
    $currentLine++;
}

// Process records
$batchSize = 100; // Smaller batches to avoid memory issues
$batch = [];
$imported = 0;
$skipped = 0;
$startTime = microtime(true);
$maxRecordsPerRun = 5000; // Process 5k records then restart

echo "Processing CSV (max $maxRecordsPerRun records this run)...\n\n";

while (($data = fgetcsv($handle)) !== FALSE && $imported < $maxRecordsPerRun) {
    $currentLine++;
    
    // Extract phone
    $phone = preg_replace('/\D/', '', $data[$columnMap['phone']] ?? '');
    if (strlen($phone) < 10) {
        $skipped++;
        continue;
    }
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Check duplicate
    if (isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Build lead data (simplified to save memory)
    $firstName = trim($data[$columnMap['first name']] ?? '');
    $lastName = trim($data[$columnMap['last name']] ?? '');
    
    // Get campaign ID
    $campaignId = null;
    $buyerCampaign = $data[$columnMap['buyer campaign']] ?? '';
    if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
        $campaignId = $matches[1];
    }
    
    // Parse data field
    $dataField = $data[$columnMap['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        parse_str($dataField, $parsedData);
    }
    
    // Insert directly (no batching to save memory)
    try {
        DB::table('leads')->insert([
            'external_lead_id' => (string)(microtime(true) * 10000),
            'source' => 'LQF_BULK',
            'phone' => $phone,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName . ' ' . $lastName),
            'email' => strtolower(trim($data[$columnMap['email']] ?? '')),
            'address' => trim($data[$columnMap['address']] ?? ''),
            'city' => trim($data[$columnMap['city']] ?? ''),
            'state' => trim($data[$columnMap['state']] ?? ''),
            'zip_code' => trim($data[$columnMap['zip code']] ?? ''),
            'ip_address' => $data[$columnMap['ip address']] ?? null,
            'type' => 'auto',
            'jangle_lead_id' => $data[$columnMap['lead id']] ?? null,
            'leadid_code' => $data[$columnMap['leadid code']] ?? null,
            'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
            'landing_page_url' => $data[$columnMap['landing page url']] ?? null,
            'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
            'opt_in_date' => !empty($data[$columnMap['originally created']]) ? 
                date('Y-m-d H:i:s', strtotime($data[$columnMap['originally created']])) : 
                date('Y-m-d H:i:s'),
            'vendor_name' => $data[$columnMap['vendor']] ?? null,
            'buyer_name' => $data[$columnMap['buyer']] ?? null,
            'campaign_id' => $campaignId,
            'drivers' => json_encode($parsedData['drivers'] ?? []),
            'vehicles' => json_encode($parsedData['vehicles'] ?? []),
            'current_policy' => json_encode($parsedData['requested_policy'] ?? []),
            'payload' => json_encode($parsedData),
            'meta' => json_encode([
                'vendor_campaign' => $data[$columnMap['vendor campaign']] ?? null,
                'buyer_campaign' => $data[$columnMap['buyer campaign']] ?? null,
                'buy_price' => $data[$columnMap['buy price']] ?? null,
                'sell_price' => $data[$columnMap['sell price']] ?? null,
            ]),
            'tcpa_compliant' => 1,
            'tenant_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $imported++;
        $existingPhones[$phone] = 1; // Add to memory cache
        
        // Report progress every 100
        if ($imported % 100 == 0) {
            $elapsed = microtime(true) - $startTime;
            $rate = $imported / ($elapsed / 60);
            echo sprintf(
                "✓ Imported: %s | Rate: %d/min | Skipped: %s | Line: %s\n",
                number_format($imported),
                $rate,
                number_format($skipped),
                number_format($currentLine)
            );
            
            // Save position
            file_put_contents($positionFile, $currentLine);
        }
        
    } catch (\Exception $e) {
        // Skip on error
        $skipped++;
    }
}

fclose($handle);

// Save final position
file_put_contents($positionFile, $currentLine);

// Stats
$elapsed = microtime(true) - $startTime;
$totalImported = DB::table('leads')->where('source', 'LQF_BULK')->count();

echo "\n========================================\n";
echo "✅ CHUNK COMPLETE (Restart #$restartCount)\n";
echo "========================================\n\n";
echo "This run: " . number_format($imported) . " imported, " . number_format($skipped) . " skipped\n";
echo "Total so far: " . number_format($totalImported) . " leads\n";
echo "Current line: " . number_format($currentLine) . "\n";
echo "Time: " . round($elapsed/60, 1) . " minutes\n\n";

// Check if we need to continue
if ($currentLine < 149548) {
    echo "🔄 AUTO-RESTARTING IN 5 SECONDS...\n\n";
    sleep(5);
    
    // Restart ourselves
    $cmd = "php " . __FILE__;
    echo "Executing: $cmd\n";
    passthru($cmd);
} else {
    echo "🎉 IMPORT COMPLETE!\n";
    echo "Total imported: " . number_format($totalImported) . " leads\n";
    
    // Clean up tracking files
    unlink($positionFile);
    unlink($restartFile);
}
