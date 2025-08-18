<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 CLEANING SURAJ DUPLICATES 🧹\n";
echo "================================\n\n";

// Get duplicate phone numbers
echo "Finding duplicates...\n";
$duplicates = DB::select("
    SELECT phone, COUNT(*) as count 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    GROUP BY phone 
    HAVING COUNT(*) > 1
");

$totalDuplicates = count($duplicates);
echo "Found " . number_format($totalDuplicates) . " phone numbers with duplicates\n\n";

if ($totalDuplicates > 0) {
    $deleted = 0;
    $processed = 0;
    
    foreach ($duplicates as $dup) {
        // Keep the one with most data, delete the rest
        $leads = DB::table('leads')
            ->where('phone', $dup->phone)
            ->where('source', 'SURAJ_BULK')
            ->orderByRaw("
                CASE 
                    WHEN email IS NOT NULL THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN address IS NOT NULL AND address != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN state IS NOT NULL AND state != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN zip_code IS NOT NULL AND zip_code != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN opt_in_date IS NOT NULL THEN 1 ELSE 0 
                END DESC
            ")
            ->orderBy('created_at', 'asc') // If same completeness, keep older
            ->get();
        
        // Keep first (most complete), delete rest
        $keepId = $leads->first()->id;
        $toDelete = $leads->skip(1)->pluck('id')->toArray();
        
        if (!empty($toDelete)) {
            DB::table('leads')->whereIn('id', $toDelete)->delete();
            $deleted += count($toDelete);
        }
        
        $processed++;
        if ($processed % 100 == 0) {
            echo "Processed $processed/$totalDuplicates phone numbers, deleted $deleted duplicates\r";
        }
    }
    
    echo "\n\n✅ CLEANUP COMPLETE!\n";
    echo "Deleted " . number_format($deleted) . " duplicate records\n";
} else {
    echo "✅ No duplicates found!\n";
}

echo "\nFinal Suraj lead count: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "================================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 CLEANING SURAJ DUPLICATES 🧹\n";
echo "================================\n\n";

// Get duplicate phone numbers
echo "Finding duplicates...\n";
$duplicates = DB::select("
    SELECT phone, COUNT(*) as count 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    GROUP BY phone 
    HAVING COUNT(*) > 1
");

$totalDuplicates = count($duplicates);
echo "Found " . number_format($totalDuplicates) . " phone numbers with duplicates\n\n";

if ($totalDuplicates > 0) {
    $deleted = 0;
    $processed = 0;
    
    foreach ($duplicates as $dup) {
        // Keep the one with most data, delete the rest
        $leads = DB::table('leads')
            ->where('phone', $dup->phone)
            ->where('source', 'SURAJ_BULK')
            ->orderByRaw("
                CASE 
                    WHEN email IS NOT NULL THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN address IS NOT NULL AND address != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN state IS NOT NULL AND state != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN zip_code IS NOT NULL AND zip_code != '' THEN 1 ELSE 0 
                END +
                CASE 
                    WHEN opt_in_date IS NOT NULL THEN 1 ELSE 0 
                END DESC
            ")
            ->orderBy('created_at', 'asc') // If same completeness, keep older
            ->get();
        
        // Keep first (most complete), delete rest
        $keepId = $leads->first()->id;
        $toDelete = $leads->skip(1)->pluck('id')->toArray();
        
        if (!empty($toDelete)) {
            DB::table('leads')->whereIn('id', $toDelete)->delete();
            $deleted += count($toDelete);
        }
        
        $processed++;
        if ($processed % 100 == 0) {
            echo "Processed $processed/$totalDuplicates phone numbers, deleted $deleted duplicates\r";
        }
    }
    
    echo "\n\n✅ CLEANUP COMPLETE!\n";
    echo "Deleted " . number_format($deleted) . " duplicate records\n";
} else {
    echo "✅ No duplicates found!\n";
}

echo "\nFinal Suraj lead count: " . number_format(DB::table('leads')->where('source', 'SURAJ_BULK')->count()) . "\n";
echo "================================\n";


