<?php
/**
 * Continue Suraj import from file 22 onwards
 * Using cumulative learning from previous attempts
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$surajDir = '/Users/giltorres/Downloads/Suraj Leads/';

echo "\n📂 CONTINUING SURAJ IMPORT FROM FILE 22\n";
echo "========================================\n\n";

// Get all CSV files
$allFiles = glob($surajDir . '*.csv');
sort($allFiles);

// Start from file 22 (index 21)
$startIndex = 21;
$files = array_slice($allFiles, $startIndex);

echo "Found " . count($allFiles) . " total files\n";
echo "Starting from file " . ($startIndex + 1) . ": " . basename($files[0]) . "\n";
echo "Files to process: " . count($files) . "\n\n";

$totalImported = 0;
$totalSkipped = 0;
$fileNum = $startIndex + 1;

// Pre-load existing phones for duplicate checking
echo "Loading existing phones for duplicate checking...\n";
$existingPhones = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
echo "Loaded " . number_format(count($existingPhones)) . " existing Suraj phones\n\n";

foreach ($files as $file) {
    echo "[File $fileNum/" . count($allFiles) . "] " . basename($file) . " ... ";
    
    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "❌ Cannot open\n";
        $fileNum++;
        continue;
    }
    
    // Skip header
    $header = fgetcsv($handle);
    
    $imported = 0;
    $skipped = 0;
    $batch = [];
    $batchSize = 500;
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        // Extract and clean phone (Column T - index 19)
        $phone = preg_replace('/\D/', '', $row[19] ?? '');
        if (strlen($phone) === 11 && $phone[0] === '1') {
            $phone = substr($phone, 1);
        }
        
        // Skip invalid or duplicate phones
        if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
            $skipped++;
            continue;
        }
        
        // Extract campaign ID (Column L - index 11)
        $campaignId = trim($row[11] ?? '');
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        // Parse opt-in date (Column B - index 1)
        $optInDate = trim($row[1] ?? '');
        $optInDateTime = null;
        if ($optInDate) {
            try {
                $optInDateTime = Carbon::parse($optInDate)->toDateTimeString();
            } catch (\Exception $e) {
                $optInDateTime = now();
            }
        } else {
            $optInDateTime = now();
        }
        
        // Names (Columns Z & AA - indices 25 & 26)
        $firstName = trim($row[25] ?? '');
        $lastName = trim($row[26] ?? '');
        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
        
        // Build record
        $batch[] = [
            'external_lead_id' => (string)(microtime(true) * 10000),
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(trim($row[32] ?? '')), // Column AC
            'address' => trim($row[33] ?? ''), // Column AD
            'city' => trim($row[34] ?? ''), // Column AE
            'state' => trim($row[35] ?? ''), // Column AG
            'zip_code' => trim($row[34] ?? ''), // Column AF
            'ip_address' => trim($row[23] ?? ''), // Column X
            'source' => 'SURAJ_BULK',
            'type' => strtolower(trim($row[7] ?? 'auto')), // Column H
            'campaign_id' => $campaignId,
            'vendor_id' => trim($row[9] ?? ''), // Column J
            'vendor_name' => trim($row[10] ?? ''), // Column K
            'vendor_campaign_id' => trim($row[8] ?? ''), // Column I
            'buyer_id' => trim($row[12] ?? ''), // Column M
            'buyer_name' => trim($row[13] ?? ''), // Column N
            'opt_in_date' => $optInDateTime,
            'birth_date' => !empty($row[36]) ? Carbon::parse($row[36])->toDateString() : null, // Column AH
            'tcpa_compliant' => 1,
            'tenant_id' => 5,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Mark as seen
        $existingPhones[$phone] = 1;
        
        // Insert batch when full
        if (count($batch) >= $batchSize) {
            try {
                DB::table('leads')->insert($batch);
                $imported += count($batch);
            } catch (\Exception $e) {
                echo "\n  ⚠️  Batch error: " . $e->getMessage() . "\n";
            }
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        try {
            DB::table('leads')->insert($batch);
            $imported += count($batch);
        } catch (\Exception $e) {
            echo "\n  ⚠️  Final batch error: " . $e->getMessage() . "\n";
        }
    }
    
    fclose($handle);
    
    echo "✓ Imported: " . number_format($imported) . ", Skipped: " . number_format($skipped) . "\n";
    
    $totalImported += $imported;
    $totalSkipped += $skipped;
    $fileNum++;
    
    // Stop if we hit corrupted data
    if ($imported == 0 && $skipped == 0) {
        echo "\n⚠️  Empty or corrupted file detected. Stopping import.\n";
        break;
    }
}

echo "\n========================================\n";
echo "✅ SURAJ IMPORT COMPLETE!\n";
echo "========================================\n\n";
echo "Files processed: " . ($fileNum - $startIndex - 1) . "\n";
echo "Total imported: " . number_format($totalImported) . " new leads\n";
echo "Total skipped: " . number_format($totalSkipped) . " (duplicates)\n\n";

// Final stats
$surajTotal = DB::table('leads')->where('source', 'SURAJ_BULK')->count();
echo "Total Suraj leads in system: " . number_format($surajTotal) . "\n";


/**
 * Continue Suraj import from file 22 onwards
 * Using cumulative learning from previous attempts
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$surajDir = '/Users/giltorres/Downloads/Suraj Leads/';

echo "\n📂 CONTINUING SURAJ IMPORT FROM FILE 22\n";
echo "========================================\n\n";

// Get all CSV files
$allFiles = glob($surajDir . '*.csv');
sort($allFiles);

// Start from file 22 (index 21)
$startIndex = 21;
$files = array_slice($allFiles, $startIndex);

echo "Found " . count($allFiles) . " total files\n";
echo "Starting from file " . ($startIndex + 1) . ": " . basename($files[0]) . "\n";
echo "Files to process: " . count($files) . "\n\n";

$totalImported = 0;
$totalSkipped = 0;
$fileNum = $startIndex + 1;

// Pre-load existing phones for duplicate checking
echo "Loading existing phones for duplicate checking...\n";
$existingPhones = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
echo "Loaded " . number_format(count($existingPhones)) . " existing Suraj phones\n\n";

foreach ($files as $file) {
    echo "[File $fileNum/" . count($allFiles) . "] " . basename($file) . " ... ";
    
    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "❌ Cannot open\n";
        $fileNum++;
        continue;
    }
    
    // Skip header
    $header = fgetcsv($handle);
    
    $imported = 0;
    $skipped = 0;
    $batch = [];
    $batchSize = 500;
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        // Extract and clean phone (Column T - index 19)
        $phone = preg_replace('/\D/', '', $row[19] ?? '');
        if (strlen($phone) === 11 && $phone[0] === '1') {
            $phone = substr($phone, 1);
        }
        
        // Skip invalid or duplicate phones
        if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
            $skipped++;
            continue;
        }
        
        // Extract campaign ID (Column L - index 11)
        $campaignId = trim($row[11] ?? '');
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        // Parse opt-in date (Column B - index 1)
        $optInDate = trim($row[1] ?? '');
        $optInDateTime = null;
        if ($optInDate) {
            try {
                $optInDateTime = Carbon::parse($optInDate)->toDateTimeString();
            } catch (\Exception $e) {
                $optInDateTime = now();
            }
        } else {
            $optInDateTime = now();
        }
        
        // Names (Columns Z & AA - indices 25 & 26)
        $firstName = trim($row[25] ?? '');
        $lastName = trim($row[26] ?? '');
        $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
        
        // Build record
        $batch[] = [
            'external_lead_id' => (string)(microtime(true) * 10000),
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(trim($row[32] ?? '')), // Column AC
            'address' => trim($row[33] ?? ''), // Column AD
            'city' => trim($row[34] ?? ''), // Column AE
            'state' => trim($row[35] ?? ''), // Column AG
            'zip_code' => trim($row[34] ?? ''), // Column AF
            'ip_address' => trim($row[23] ?? ''), // Column X
            'source' => 'SURAJ_BULK',
            'type' => strtolower(trim($row[7] ?? 'auto')), // Column H
            'campaign_id' => $campaignId,
            'vendor_id' => trim($row[9] ?? ''), // Column J
            'vendor_name' => trim($row[10] ?? ''), // Column K
            'vendor_campaign_id' => trim($row[8] ?? ''), // Column I
            'buyer_id' => trim($row[12] ?? ''), // Column M
            'buyer_name' => trim($row[13] ?? ''), // Column N
            'opt_in_date' => $optInDateTime,
            'birth_date' => !empty($row[36]) ? Carbon::parse($row[36])->toDateString() : null, // Column AH
            'tcpa_compliant' => 1,
            'tenant_id' => 5,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Mark as seen
        $existingPhones[$phone] = 1;
        
        // Insert batch when full
        if (count($batch) >= $batchSize) {
            try {
                DB::table('leads')->insert($batch);
                $imported += count($batch);
            } catch (\Exception $e) {
                echo "\n  ⚠️  Batch error: " . $e->getMessage() . "\n";
            }
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        try {
            DB::table('leads')->insert($batch);
            $imported += count($batch);
        } catch (\Exception $e) {
            echo "\n  ⚠️  Final batch error: " . $e->getMessage() . "\n";
        }
    }
    
    fclose($handle);
    
    echo "✓ Imported: " . number_format($imported) . ", Skipped: " . number_format($skipped) . "\n";
    
    $totalImported += $imported;
    $totalSkipped += $skipped;
    $fileNum++;
    
    // Stop if we hit corrupted data
    if ($imported == 0 && $skipped == 0) {
        echo "\n⚠️  Empty or corrupted file detected. Stopping import.\n";
        break;
    }
}

echo "\n========================================\n";
echo "✅ SURAJ IMPORT COMPLETE!\n";
echo "========================================\n\n";
echo "Files processed: " . ($fileNum - $startIndex - 1) . "\n";
echo "Total imported: " . number_format($totalImported) . " new leads\n";
echo "Total skipped: " . number_format($totalSkipped) . " (duplicates)\n\n";

// Final stats
$surajTotal = DB::table('leads')->where('source', 'SURAJ_BULK')->count();
echo "Total Suraj leads in system: " . number_format($surajTotal) . "\n";








