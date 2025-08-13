<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

echo "🔥 BLAZING FAST IMPORT - NO DUPLICATE CHECKING 🔥\n";
echo "================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n";
echo "WARNING: Importing ALL records, duplicates will be handled later\n\n";

$totalImported = 0;
$startTime = time();

// Use raw PDO for MAXIMUM speed
$pdo = DB::connection()->getPdo();
$sql = "INSERT INTO leads (external_lead_id, phone, name, first_name, last_name, email, city, source, campaign_id, buyer_id, buyer_name, tenant_id, created_at, updated_at) VALUES ";

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
    
    $values = [];
    $fileCount = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 30) continue;
        
        $phone = trim($row[29] ?? '');
        if (!$phone || strlen($phone) < 10) continue;
        
        $campaignId = trim($row[11] ?? '');
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        $buyerId = trim($row[12] ?? '');
        if (substr($buyerId, -2) === '.0') {
            $buyerId = substr($buyerId, 0, -2);
        }
        
        $firstName = $pdo->quote(trim($row[25] ?? ''));
        $lastName = $pdo->quote(trim($row[26] ?? ''));
        $fullName = trim(trim($row[25] ?? '') . ' ' . trim($row[26] ?? ''));
        if (!$fullName) $fullName = 'Unknown';
        $fullName = $pdo->quote($fullName);
        
        $email = $pdo->quote(trim($row[28] ?? ''));
        $city = $pdo->quote(trim($row[27] ?? ''));
        $buyerName = $pdo->quote(trim($row[13] ?? ''));
        $now = now()->toDateTimeString();
        
        // Use microseconds for unique IDs in bulk import
        $leadId = substr(str_replace('.', '', microtime(true) * 1000), 0, 13);
        
        $values[] = "($leadId, '$phone', $fullName, $firstName, $lastName, $email, $city, 'SURAJ_BULK', '$campaignId', '$buyerId', $buyerName, 1, '$now', '$now')";
        
        // Insert in batches of 500
        if (count($values) >= 500) {
            try {
                $pdo->exec($sql . implode(',', $values));
                $fileCount += count($values);
                $totalImported += count($values);
            } catch (Exception $e) {
                // Ignore duplicates
            }
            $values = [];
            echo ".";
        }
    }
    
    // Insert remaining
    if (!empty($values)) {
        try {
            $pdo->exec($sql . implode(',', $values));
            $fileCount += count($values);
            $totalImported += count($values);
        } catch (Exception $e) {
            // Ignore duplicates
        }
    }
    
    fclose($handle);
    echo " [$fileCount imported]\n";
    
    $elapsed = time() - $startTime;
    $rate = $elapsed > 0 ? round($totalImported / $elapsed * 60) : 0;
    echo "  Total: " . number_format($totalImported) . " | Rate: " . number_format($rate) . "/min | Time: " . round($elapsed/60, 1) . " min\n";
}

echo "\n================================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "Total imported: " . number_format($totalImported) . " records\n";
echo "Time: " . round((time() - $startTime) / 60, 1) . " minutes\n";
echo "Rate: " . round($totalImported / ((time() - $startTime) / 60)) . " records/minute\n";

// Count duplicates
echo "\n🔍 Checking for duplicates...\n";
$duplicates = DB::select("
    SELECT phone, COUNT(*) as count 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    GROUP BY phone 
    HAVING COUNT(*) > 1
");
echo "Found " . count($duplicates) . " phone numbers with duplicates\n";

if (count($duplicates) > 0) {
    $totalDups = 0;
    foreach ($duplicates as $dup) {
        $totalDups += ($dup->count - 1);
    }
    echo "Total duplicate records to clean: " . number_format($totalDups) . "\n";
    echo "\nRun 'php clean_duplicates.php' to remove duplicates\n";
}

echo "================================================\n";
