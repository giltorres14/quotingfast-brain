<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🚀 FASTEST SURAJ IMPORT - Let Database Handle Duplicates 🚀\n";
echo "===========================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalProcessed = 0;
$startTime = time();

// Start from file 22 since we completed up to 21
$startFrom = 22;

foreach ($files as $index => $file) {
    if ($index < $startFrom - 1) {
        continue; // Skip already processed files
    }
    
    $filename = basename($file);
    echo "File " . ($index + 1) . "/" . count($files) . ": $filename ";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    if ($header[0] == 'First Name') {
        echo "[SKIP - wrong format]\n";
        fclose($handle);
        continue;
    }
    
    $batch = [];
    $fileCount = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        $phone = trim($row[29] ?? '');
        if (!$phone || strlen($phone) < 10) continue;
        
        $optInDate = trim($row[1] ?? '');
        $campaignId = trim($row[11] ?? '');
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        // Parse opt-in date
        $optInDateTime = null;
        if ($optInDate) {
            try {
                $optInDateTime = Carbon::parse($optInDate)->toDateTimeString();
            } catch (\Exception $e) {
                $optInDateTime = now();
            }
        }
        
        $firstName = trim($row[25] ?? '');
        $lastName = trim($row[26] ?? '');
        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
        
        $batch[] = [
            'external_lead_id' => substr(str_replace('.', '', microtime(true) * 1000), 0, 13),
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($row[28] ?? '') ?: null,
            'address' => trim($row[30] ?? ''),
            'city' => trim($row[27] ?? ''),
            'state' => trim($row[32] ?? ''),
            'zip_code' => trim($row[31] ?? ''),
            'source' => 'SURAJ_BULK',
            'type' => strpos(strtolower(trim($row[7] ?? '')), 'home') !== false ? 'home' : 'auto',
            'campaign_id' => $campaignId,
            'vendor_name' => trim($row[10] ?? ''),
            'buyer_name' => trim($row[13] ?? ''),
            'opt_in_date' => $optInDateTime,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Insert in batches - insertOrIgnore handles duplicates
        if (count($batch) >= 200) { // Smaller batch size to avoid memory issues
            $inserted = DB::table('leads')->insertOrIgnore($batch);
            $fileCount += $inserted;
            $totalProcessed += $inserted;
            echo ".";
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        $inserted = DB::table('leads')->insertOrIgnore($batch);
        $fileCount += $inserted;
        $totalProcessed += $inserted;
    }
    
    fclose($handle);
    echo " [$fileCount imported]\n";
    
    // Free memory
    unset($batch);
    gc_collect_cycles();
}

$elapsed = time() - $startTime;

echo "\n===========================================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "Records imported: " . number_format($totalProcessed) . "\n";
echo "Time: " . round($elapsed / 60, 1) . " minutes\n";
echo "\nTotal Suraj leads now: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "===========================================================\n";

