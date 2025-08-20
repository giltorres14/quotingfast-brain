<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

echo "ULTRA FAST SURAJ IMPORT V2\n";
echo "===========================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalImported = 0;
$totalSkipped = 0;
$totalProcessed = 0;
$startTime = time();

// Process each file
foreach ($files as $index => $file) {
    $filename = basename($file);
    echo "File " . ($index + 1) . "/" . count($files) . ": $filename\n";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    // Skip if wrong format
    if ($header[0] == 'First Name') {
        echo "  Skipping - wrong format\n\n";
        fclose($handle);
        continue;
    }
    
    $batch = [];
    $fileImported = 0;
    $fileSkipped = 0;
    $fileProcessed = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        $fileProcessed++;
        
        // Show progress every 100 rows
        if ($fileProcessed % 100 == 0) {
            echo "  Processing: $fileProcessed rows... (imported: $fileImported, skipped: $fileSkipped)\r";
        }
        
        if (count($row) < 30) {
            $fileSkipped++;
            continue;
        }
        
        // Extract data - CORRECT columns based on actual data
        $phone = trim($row[29] ?? ''); // Column 30 (0-indexed = 29)
        if (!$phone || strlen($phone) < 10) {
            $fileSkipped++;
            continue;
        }
        
        // Clean campaign_id - remove .0
        $campaignId = trim($row[11] ?? ''); // Column 12 (0-indexed = 11)
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        // Clean buyer_id - remove .0
        $buyerId = trim($row[12] ?? ''); // Column 13
        if (substr($buyerId, -2) === '.0') {
            $buyerId = substr($buyerId, 0, -2);
        }
        
        $firstName = trim($row[25] ?? ''); // Column 26
        $lastName = trim($row[26] ?? '');  // Column 27
        $fullName = trim($firstName . ' ' . $lastName);
        if (!$fullName) $fullName = 'Unknown'; // Fallback if no name
        
        $leadData = [
            'external_lead_id' => Lead::generateExternalLeadId(),
            'phone' => $phone,
            'name' => $fullName,               // Required field
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($row[28] ?? ''),      // Column 29
            'city' => trim($row[27] ?? ''),       // Column 28
            'source' => 'SURAJ_BULK',
            'campaign_id' => $campaignId,
            'buyer_id' => $buyerId,
            'buyer_name' => trim($row[13] ?? ''), // Column 14
            'tenant_id' => 1,  // QuotingFast tenant
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $batch[] = $leadData;
        
        // Insert when batch is full
        if (count($batch) >= 1000) {
            $inserted = DB::table('leads')->insertOrIgnore($batch);
            $fileImported += $inserted;
            $fileSkipped += (count($batch) - $inserted);
            $totalImported += $inserted;
            $totalSkipped += (count($batch) - $inserted);
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        $inserted = DB::table('leads')->insertOrIgnore($batch);
        $fileImported += $inserted;
        $fileSkipped += (count($batch) - $inserted);
        $totalImported += $inserted;
        $totalSkipped += (count($batch) - $inserted);
    }
    
    fclose($handle);
    $totalProcessed += $fileProcessed;
    
    echo "\n  Complete: Processed $fileProcessed rows, Imported $fileImported, Skipped $fileSkipped\n";
    
    $elapsed = time() - $startTime;
    $rate = $elapsed > 0 ? round($totalImported / $elapsed * 60) : 0;
    echo "  Running total: $totalImported imported, $totalSkipped duplicates | Rate: $rate/min\n\n";
}

echo "\n=========================\n";
echo "IMPORT COMPLETE!\n";
echo "Total processed: " . number_format($totalProcessed) . " rows\n";
echo "Total imported: " . number_format($totalImported) . " new leads\n";
echo "Total skipped: " . number_format($totalSkipped) . " duplicates\n";
echo "Time: " . round((time() - $startTime) / 60, 1) . " minutes\n";
echo "=========================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

echo "ULTRA FAST SURAJ IMPORT V2\n";
echo "===========================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalImported = 0;
$totalSkipped = 0;
$totalProcessed = 0;
$startTime = time();

// Process each file
foreach ($files as $index => $file) {
    $filename = basename($file);
    echo "File " . ($index + 1) . "/" . count($files) . ": $filename\n";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    // Skip if wrong format
    if ($header[0] == 'First Name') {
        echo "  Skipping - wrong format\n\n";
        fclose($handle);
        continue;
    }
    
    $batch = [];
    $fileImported = 0;
    $fileSkipped = 0;
    $fileProcessed = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        $fileProcessed++;
        
        // Show progress every 100 rows
        if ($fileProcessed % 100 == 0) {
            echo "  Processing: $fileProcessed rows... (imported: $fileImported, skipped: $fileSkipped)\r";
        }
        
        if (count($row) < 30) {
            $fileSkipped++;
            continue;
        }
        
        // Extract data - CORRECT columns based on actual data
        $phone = trim($row[29] ?? ''); // Column 30 (0-indexed = 29)
        if (!$phone || strlen($phone) < 10) {
            $fileSkipped++;
            continue;
        }
        
        // Clean campaign_id - remove .0
        $campaignId = trim($row[11] ?? ''); // Column 12 (0-indexed = 11)
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        // Clean buyer_id - remove .0
        $buyerId = trim($row[12] ?? ''); // Column 13
        if (substr($buyerId, -2) === '.0') {
            $buyerId = substr($buyerId, 0, -2);
        }
        
        $firstName = trim($row[25] ?? ''); // Column 26
        $lastName = trim($row[26] ?? '');  // Column 27
        $fullName = trim($firstName . ' ' . $lastName);
        if (!$fullName) $fullName = 'Unknown'; // Fallback if no name
        
        $leadData = [
            'external_lead_id' => Lead::generateExternalLeadId(),
            'phone' => $phone,
            'name' => $fullName,               // Required field
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($row[28] ?? ''),      // Column 29
            'city' => trim($row[27] ?? ''),       // Column 28
            'source' => 'SURAJ_BULK',
            'campaign_id' => $campaignId,
            'buyer_id' => $buyerId,
            'buyer_name' => trim($row[13] ?? ''), // Column 14
            'tenant_id' => 1,  // QuotingFast tenant
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $batch[] = $leadData;
        
        // Insert when batch is full
        if (count($batch) >= 1000) {
            $inserted = DB::table('leads')->insertOrIgnore($batch);
            $fileImported += $inserted;
            $fileSkipped += (count($batch) - $inserted);
            $totalImported += $inserted;
            $totalSkipped += (count($batch) - $inserted);
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        $inserted = DB::table('leads')->insertOrIgnore($batch);
        $fileImported += $inserted;
        $fileSkipped += (count($batch) - $inserted);
        $totalImported += $inserted;
        $totalSkipped += (count($batch) - $inserted);
    }
    
    fclose($handle);
    $totalProcessed += $fileProcessed;
    
    echo "\n  Complete: Processed $fileProcessed rows, Imported $fileImported, Skipped $fileSkipped\n";
    
    $elapsed = time() - $startTime;
    $rate = $elapsed > 0 ? round($totalImported / $elapsed * 60) : 0;
    echo "  Running total: $totalImported imported, $totalSkipped duplicates | Rate: $rate/min\n\n";
}

echo "\n=========================\n";
echo "IMPORT COMPLETE!\n";
echo "Total processed: " . number_format($totalProcessed) . " rows\n";
echo "Total imported: " . number_format($totalImported) . " new leads\n";
echo "Total skipped: " . number_format($totalSkipped) . " duplicates\n";
echo "Time: " . round((time() - $startTime) / 60, 1) . " minutes\n";
echo "=========================\n";








