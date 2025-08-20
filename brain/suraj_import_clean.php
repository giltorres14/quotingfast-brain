<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🚀 CLEAN SURAJ IMPORT - Validated Data Only 🚀\n";
echo "===========================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalProcessed = 0;
$totalSkipped = 0;
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
    $fileSkipped = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        $phone = trim($row[29] ?? '');
        if (!$phone || strlen($phone) < 10) continue;
        
        // VALIDATE ADDRESS - skip if it contains HTTP headers or is too long
        $address = trim($row[30] ?? '');
        if (strlen($address) > 255 || 
            stripos($address, 'Cache-Control') !== false ||
            stripos($address, 'Content-Type') !== false ||
            stripos($address, 'Transfer-Encoding') !== false ||
            stripos($address, '<br>') !== false ||
            stripos($address, 'http error') !== false) {
            $fileSkipped++;
            continue; // Skip corrupted records
        }
        
        // Validate other fields
        $city = trim($row[27] ?? '');
        if (strlen($city) > 100) {
            $fileSkipped++;
            continue;
        }
        
        $state = trim($row[32] ?? '');
        if (strlen($state) > 2) {
            $fileSkipped++;
            continue;
        }
        
        $zipCode = trim($row[31] ?? '');
        if (strlen($zipCode) > 10) {
            $fileSkipped++;
            continue;
        }
        
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
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
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
        
        // Insert in batches
        if (count($batch) >= 200) {
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
    echo " [$fileCount imported, $fileSkipped skipped]\n";
    $totalSkipped += $fileSkipped;
    
    // Free memory
    unset($batch);
    gc_collect_cycles();
}

$elapsed = time() - $startTime;

echo "\n===========================================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "Records imported: " . number_format($totalProcessed) . "\n";
echo "Records skipped (corrupted): " . number_format($totalSkipped) . "\n";
echo "Time: " . round($elapsed / 60, 1) . " minutes\n";
echo "\nTotal Suraj leads now: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "===========================================================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🚀 CLEAN SURAJ IMPORT - Validated Data Only 🚀\n";
echo "===========================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalProcessed = 0;
$totalSkipped = 0;
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
    $fileSkipped = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        $phone = trim($row[29] ?? '');
        if (!$phone || strlen($phone) < 10) continue;
        
        // VALIDATE ADDRESS - skip if it contains HTTP headers or is too long
        $address = trim($row[30] ?? '');
        if (strlen($address) > 255 || 
            stripos($address, 'Cache-Control') !== false ||
            stripos($address, 'Content-Type') !== false ||
            stripos($address, 'Transfer-Encoding') !== false ||
            stripos($address, '<br>') !== false ||
            stripos($address, 'http error') !== false) {
            $fileSkipped++;
            continue; // Skip corrupted records
        }
        
        // Validate other fields
        $city = trim($row[27] ?? '');
        if (strlen($city) > 100) {
            $fileSkipped++;
            continue;
        }
        
        $state = trim($row[32] ?? '');
        if (strlen($state) > 2) {
            $fileSkipped++;
            continue;
        }
        
        $zipCode = trim($row[31] ?? '');
        if (strlen($zipCode) > 10) {
            $fileSkipped++;
            continue;
        }
        
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
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
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
        
        // Insert in batches
        if (count($batch) >= 200) {
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
    echo " [$fileCount imported, $fileSkipped skipped]\n";
    $totalSkipped += $fileSkipped;
    
    // Free memory
    unset($batch);
    gc_collect_cycles();
}

$elapsed = time() - $startTime;

echo "\n===========================================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "Records imported: " . number_format($totalProcessed) . "\n";
echo "Records skipped (corrupted): " . number_format($totalSkipped) . "\n";
echo "Time: " . round($elapsed / 60, 1) . " minutes\n";
echo "\nTotal Suraj leads now: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "===========================================================\n";








