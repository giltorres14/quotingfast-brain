<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔧 FIXING OPT-IN DATES FOR SURAJ IMPORTS 🔧\n";
echo "============================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n";
echo "This will update opt_in_date for all SURAJ_BULK leads\n\n";

$totalUpdated = 0;
$phoneToOptIn = [];

// First, collect all phone -> opt-in date mappings
echo "Step 1: Reading opt-in dates from CSV files...\n";
foreach ($files as $index => $file) {
    echo "Reading " . basename($file) . "...";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    if ($header[0] == 'First Name') {
        echo " [SKIP - wrong format]\n";
        fclose($handle);
        continue;
    }
    
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        $phone = trim($row[29] ?? ''); // Column 30
        if (!$phone || strlen($phone) < 10) continue;
        
        $timestamp = trim($row[1] ?? ''); // Column 2 - the opt-in timestamp
        if ($timestamp) {
            try {
                // Parse the timestamp (format: 2025-08-08T12:17:14.786Z)
                $optInDate = Carbon::parse($timestamp);
                $phoneToOptIn[$phone] = $optInDate->toDateTimeString();
                $count++;
            } catch (\Exception $e) {
                // Skip invalid dates
            }
        }
    }
    fclose($handle);
    echo " [$count dates collected]\n";
}

echo "\nCollected " . count($phoneToOptIn) . " phone -> opt-in date mappings\n\n";

// Now update the database
echo "Step 2: Updating database records...\n";

// Show sample of what we'll update
$samples = array_slice($phoneToOptIn, 0, 3, true);
echo "\nSample updates:\n";
foreach ($samples as $phone => $optIn) {
    echo "  Phone $phone -> Opt-in: $optIn\n";
}

echo "\nStarting database updates...\n";

$batchSize = 100;
$batch = [];
foreach ($phoneToOptIn as $phone => $optInDate) {
    $batch[$phone] = $optInDate;
    
    if (count($batch) >= $batchSize) {
        // Update this batch
        $phones = array_keys($batch);
        $leads = DB::table('leads')
            ->where('source', 'SURAJ_BULK')
            ->whereIn('phone', $phones)
            ->get(['id', 'phone']);
        
        foreach ($leads as $lead) {
            if (isset($batch[$lead->phone])) {
                DB::table('leads')
                    ->where('id', $lead->id)
                    ->update([
                        'opt_in_date' => $batch[$lead->phone],
                        'updated_at' => now()
                    ]);
                $totalUpdated++;
            }
        }
        
        echo "  Updated $totalUpdated records...\r";
        $batch = [];
    }
}

// Update remaining batch
if (!empty($batch)) {
    $phones = array_keys($batch);
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->whereIn('phone', $phones)
        ->get(['id', 'phone']);
    
    foreach ($leads as $lead) {
        if (isset($batch[$lead->phone])) {
            DB::table('leads')
                ->where('id', $lead->id)
                ->update([
                    'opt_in_date' => $batch[$lead->phone],
                    'updated_at' => now()
                ]);
            $totalUpdated++;
        }
    }
}

echo "\n\n============================================\n";
echo "✅ COMPLETE!\n";
echo "Updated opt_in_date for " . number_format($totalUpdated) . " records\n";

// Verify the update
$sample = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->whereNotNull('opt_in_date')
    ->orderBy('opt_in_date', 'desc')
    ->limit(5)
    ->get(['phone', 'name', 'opt_in_date']);

echo "\nSample of updated records:\n";
foreach ($sample as $lead) {
    echo "  {$lead->phone} - {$lead->name}: Opt-in {$lead->opt_in_date}\n";
}

echo "============================================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔧 FIXING OPT-IN DATES FOR SURAJ IMPORTS 🔧\n";
echo "============================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n";
echo "This will update opt_in_date for all SURAJ_BULK leads\n\n";

$totalUpdated = 0;
$phoneToOptIn = [];

// First, collect all phone -> opt-in date mappings
echo "Step 1: Reading opt-in dates from CSV files...\n";
foreach ($files as $index => $file) {
    echo "Reading " . basename($file) . "...";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    if ($header[0] == 'First Name') {
        echo " [SKIP - wrong format]\n";
        fclose($handle);
        continue;
    }
    
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        $phone = trim($row[29] ?? ''); // Column 30
        if (!$phone || strlen($phone) < 10) continue;
        
        $timestamp = trim($row[1] ?? ''); // Column 2 - the opt-in timestamp
        if ($timestamp) {
            try {
                // Parse the timestamp (format: 2025-08-08T12:17:14.786Z)
                $optInDate = Carbon::parse($timestamp);
                $phoneToOptIn[$phone] = $optInDate->toDateTimeString();
                $count++;
            } catch (\Exception $e) {
                // Skip invalid dates
            }
        }
    }
    fclose($handle);
    echo " [$count dates collected]\n";
}

echo "\nCollected " . count($phoneToOptIn) . " phone -> opt-in date mappings\n\n";

// Now update the database
echo "Step 2: Updating database records...\n";

// Show sample of what we'll update
$samples = array_slice($phoneToOptIn, 0, 3, true);
echo "\nSample updates:\n";
foreach ($samples as $phone => $optIn) {
    echo "  Phone $phone -> Opt-in: $optIn\n";
}

echo "\nStarting database updates...\n";

$batchSize = 100;
$batch = [];
foreach ($phoneToOptIn as $phone => $optInDate) {
    $batch[$phone] = $optInDate;
    
    if (count($batch) >= $batchSize) {
        // Update this batch
        $phones = array_keys($batch);
        $leads = DB::table('leads')
            ->where('source', 'SURAJ_BULK')
            ->whereIn('phone', $phones)
            ->get(['id', 'phone']);
        
        foreach ($leads as $lead) {
            if (isset($batch[$lead->phone])) {
                DB::table('leads')
                    ->where('id', $lead->id)
                    ->update([
                        'opt_in_date' => $batch[$lead->phone],
                        'updated_at' => now()
                    ]);
                $totalUpdated++;
            }
        }
        
        echo "  Updated $totalUpdated records...\r";
        $batch = [];
    }
}

// Update remaining batch
if (!empty($batch)) {
    $phones = array_keys($batch);
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->whereIn('phone', $phones)
        ->get(['id', 'phone']);
    
    foreach ($leads as $lead) {
        if (isset($batch[$lead->phone])) {
            DB::table('leads')
                ->where('id', $lead->id)
                ->update([
                    'opt_in_date' => $batch[$lead->phone],
                    'updated_at' => now()
                ]);
            $totalUpdated++;
        }
    }
}

echo "\n\n============================================\n";
echo "✅ COMPLETE!\n";
echo "Updated opt_in_date for " . number_format($totalUpdated) . " records\n";

// Verify the update
$sample = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->whereNotNull('opt_in_date')
    ->orderBy('opt_in_date', 'desc')
    ->limit(5)
    ->get(['phone', 'name', 'opt_in_date']);

echo "\nSample of updated records:\n";
foreach ($sample as $lead) {
    echo "  {$lead->phone} - {$lead->name}: Opt-in {$lead->opt_in_date}\n";
}

echo "============================================\n";






