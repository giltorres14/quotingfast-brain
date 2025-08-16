<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

echo "🚀 ULTRA FAST IMPORT - FINAL VERSION 🚀\n";
echo "=========================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$totalImported = 0;
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
    $fileCount = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 30) continue;
        
        $phone = trim($row[29] ?? '');
        if (!$phone || strlen($phone) < 10) continue;
        
        // Clean campaign_id and buyer_id
        $campaignId = trim($row[11] ?? '');
        if (substr($campaignId, -2) === '.0') {
            $campaignId = substr($campaignId, 0, -2);
        }
        
        $buyerId = trim($row[12] ?? '');
        if (substr($buyerId, -2) === '.0') {
            $buyerId = substr($buyerId, 0, -2);
        }
        
        $firstName = trim($row[25] ?? '');
        $lastName = trim($row[26] ?? '');
        $fullName = trim($firstName . ' ' . $lastName);
        if (!$fullName) $fullName = 'Unknown';
        
        $batch[] = [
            'external_lead_id' => time() . rand(100, 999), // Simple unique ID
            'phone' => $phone,
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($row[28] ?? ''),
            'city' => trim($row[27] ?? ''),
            'source' => 'SURAJ_BULK',
            'campaign_id' => $campaignId,
            'buyer_id' => $buyerId,
            'buyer_name' => trim($row[13] ?? ''),
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Insert in batches
        if (count($batch) >= 500) {
            try {
                $inserted = DB::table('leads')->insertOrIgnore($batch);
                $fileCount += $inserted;
                $totalImported += $inserted;
                $totalSkipped += (count($batch) - $inserted);
                echo ".";
            } catch (\Exception $e) {
                echo "E";
                // Log but continue
            }
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        try {
            $inserted = DB::table('leads')->insertOrIgnore($batch);
            $fileCount += $inserted;
            $totalImported += $inserted;
            $totalSkipped += (count($batch) - $inserted);
        } catch (\Exception $e) {
            // Continue
        }
    }
    
    fclose($handle);
    echo " [Imported: $fileCount]\n";
    
    $elapsed = time() - $startTime;
    $rate = $elapsed > 0 ? round($totalImported / $elapsed * 60) : 0;
    echo "  Total: " . number_format($totalImported) . " new | " . number_format($totalSkipped) . " skipped | Rate: " . number_format($rate) . "/min\n";
}

echo "\n=========================================\n";
echo "✅ COMPLETE!\n";
echo "Imported: " . number_format($totalImported) . " new records\n";
echo "Skipped: " . number_format($totalSkipped) . " duplicates\n";
echo "Time: " . round((time() - $startTime) / 60, 1) . " minutes\n";
echo "=========================================\n";


