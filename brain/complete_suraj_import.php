<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🚀 COMPLETING SURAJ IMPORT - NO DUPLICATES 🚀\n";
echo "==============================================\n\n";

// CORRECT COLUMN MAPPING
echo "Using CORRECT column mappings:\n";
echo "  Column B (1): Opt-in Date\n";
echo "  Column H (7): Lead Type (Home/Auto)\n";
echo "  Column K (10): Vendor Name\n";
echo "  Column L (11): Campaign ID\n";
echo "  Column M (12): Buyer ID\n";
echo "  Column N (13): Buyer Name\n";
echo "  Column AC (28): Email\n";
echo "  Column AD (29): Phone\n";
echo "  Column AE (30): Address\n";
echo "  Column AF (31): Zip Code\n";
echo "  Column AG (32): State\n";
echo "==============================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

// Load existing phones to prevent duplicates
echo "Loading existing phone numbers...\n";
$existingPhones = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
echo "Found " . count($existingPhones) . " existing Suraj leads\n\n";

$totalNew = 0;
$totalSkipped = 0;
$startTime = time();

foreach ($files as $index => $file) {
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
    $fileNew = 0;
    $fileSkipped = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        // CORRECT COLUMN MAPPINGS (0-indexed)
        $phone = trim($row[29] ?? '');      // Column AD
        if (!$phone || strlen($phone) < 10) continue;
        
        // Skip if already exists
        if (isset($existingPhones[$phone])) {
            $fileSkipped++;
            continue;
        }
        
        $optInDate = trim($row[1] ?? '');   // Column B
        $leadType = trim($row[7] ?? '');    // Column H
        $vendorName = trim($row[10] ?? ''); // Column K
        $campaignId = trim($row[11] ?? ''); // Column L
        $buyerId = trim($row[12] ?? '');    // Column M
        $buyerName = trim($row[13] ?? '');  // Column N
        $firstName = trim($row[25] ?? '');  // Column Z
        $lastName = trim($row[26] ?? '');   // Column AA
        $email = trim($row[28] ?? '');      // Column AC
        $address = trim($row[30] ?? '');    // Column AE
        $zipCode = trim($row[31] ?? '');    // Column AF
        $state = trim($row[32] ?? '');      // Column AG
        
        // Clean campaign_id - remove .0
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
        
        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
        
        $batch[] = [
            'external_lead_id' => substr(str_replace('.', '', microtime(true) * 1000), 0, 13),
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email ?: null,
            'address' => $address,
            'city' => trim($row[27] ?? ''), // Column AB
            'state' => $state,
            'zip_code' => $zipCode,
            'source' => 'SURAJ_BULK',
            'type' => strpos(strtolower($leadType), 'home') !== false ? 'home' : 'auto',
            'campaign_id' => $campaignId,
            'vendor_name' => $vendorName,
            'buyer_name' => $buyerName,
            'opt_in_date' => $optInDateTime,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Mark as existing
        $existingPhones[$phone] = true;
        
        // Insert in batches
        if (count($batch) >= 500) {
            try {
                DB::table('leads')->insert($batch);
                $fileNew += count($batch);
                $totalNew += count($batch);
                echo ".";
            } catch (\Exception $e) {
                echo "E";
            }
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        try {
            DB::table('leads')->insert($batch);
            $fileNew += count($batch);
            $totalNew += count($batch);
        } catch (\Exception $e) {
            // Continue
        }
    }
    
    fclose($handle);
    echo " [New: $fileNew, Skipped: $fileSkipped]\n";
}

$elapsed = time() - $startTime;

echo "\n==============================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "New records imported: " . number_format($totalNew) . "\n";
echo "Duplicates skipped: " . number_format($totalSkipped) . "\n";
echo "Time: " . round($elapsed / 60, 1) . " minutes\n";
echo "\nTotal Suraj leads now: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "==============================================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🚀 COMPLETING SURAJ IMPORT - NO DUPLICATES 🚀\n";
echo "==============================================\n\n";

// CORRECT COLUMN MAPPING
echo "Using CORRECT column mappings:\n";
echo "  Column B (1): Opt-in Date\n";
echo "  Column H (7): Lead Type (Home/Auto)\n";
echo "  Column K (10): Vendor Name\n";
echo "  Column L (11): Campaign ID\n";
echo "  Column M (12): Buyer ID\n";
echo "  Column N (13): Buyer Name\n";
echo "  Column AC (28): Email\n";
echo "  Column AD (29): Phone\n";
echo "  Column AE (30): Address\n";
echo "  Column AF (31): Zip Code\n";
echo "  Column AG (32): State\n";
echo "==============================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

// Load existing phones to prevent duplicates
echo "Loading existing phone numbers...\n";
$existingPhones = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
echo "Found " . count($existingPhones) . " existing Suraj leads\n\n";

$totalNew = 0;
$totalSkipped = 0;
$startTime = time();

foreach ($files as $index => $file) {
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
    $fileNew = 0;
    $fileSkipped = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        // CORRECT COLUMN MAPPINGS (0-indexed)
        $phone = trim($row[29] ?? '');      // Column AD
        if (!$phone || strlen($phone) < 10) continue;
        
        // Skip if already exists
        if (isset($existingPhones[$phone])) {
            $fileSkipped++;
            continue;
        }
        
        $optInDate = trim($row[1] ?? '');   // Column B
        $leadType = trim($row[7] ?? '');    // Column H
        $vendorName = trim($row[10] ?? ''); // Column K
        $campaignId = trim($row[11] ?? ''); // Column L
        $buyerId = trim($row[12] ?? '');    // Column M
        $buyerName = trim($row[13] ?? '');  // Column N
        $firstName = trim($row[25] ?? '');  // Column Z
        $lastName = trim($row[26] ?? '');   // Column AA
        $email = trim($row[28] ?? '');      // Column AC
        $address = trim($row[30] ?? '');    // Column AE
        $zipCode = trim($row[31] ?? '');    // Column AF
        $state = trim($row[32] ?? '');      // Column AG
        
        // Clean campaign_id - remove .0
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
        
        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
        
        $batch[] = [
            'external_lead_id' => substr(str_replace('.', '', microtime(true) * 1000), 0, 13),
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email ?: null,
            'address' => $address,
            'city' => trim($row[27] ?? ''), // Column AB
            'state' => $state,
            'zip_code' => $zipCode,
            'source' => 'SURAJ_BULK',
            'type' => strpos(strtolower($leadType), 'home') !== false ? 'home' : 'auto',
            'campaign_id' => $campaignId,
            'vendor_name' => $vendorName,
            'buyer_name' => $buyerName,
            'opt_in_date' => $optInDateTime,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Mark as existing
        $existingPhones[$phone] = true;
        
        // Insert in batches
        if (count($batch) >= 500) {
            try {
                DB::table('leads')->insert($batch);
                $fileNew += count($batch);
                $totalNew += count($batch);
                echo ".";
            } catch (\Exception $e) {
                echo "E";
            }
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        try {
            DB::table('leads')->insert($batch);
            $fileNew += count($batch);
            $totalNew += count($batch);
        } catch (\Exception $e) {
            // Continue
        }
    }
    
    fclose($handle);
    echo " [New: $fileNew, Skipped: $fileSkipped]\n";
}

$elapsed = time() - $startTime;

echo "\n==============================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "New records imported: " . number_format($totalNew) . "\n";
echo "Duplicates skipped: " . number_format($totalSkipped) . "\n";
echo "Time: " . round($elapsed / 60, 1) . " minutes\n";
echo "\nTotal Suraj leads now: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "==============================================\n";






