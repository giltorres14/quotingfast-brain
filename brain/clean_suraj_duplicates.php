<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 CLEANING SURAJ DUPLICATES 🧹\n";
echo "================================\n\n";

// Find all duplicate phone numbers
$duplicates = DB::select("
    SELECT phone, COUNT(*) as count 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    GROUP BY phone 
    HAVING COUNT(*) > 1
    ORDER BY count DESC
");

echo "Found " . count($duplicates) . " phone numbers with duplicates\n\n";

$totalDeleted = 0;
$processed = 0;

foreach ($duplicates as $dup) {
    $processed++;
    
    // Get all leads with this phone number
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->where('phone', $dup->phone)
        ->orderBy('created_at', 'asc')
        ->get();
    
    // Find the BEST record (most complete data)
    $bestLead = null;
    $bestScore = -1;
    
    foreach ($leads as $lead) {
        $score = 0;
        // Score based on data completeness
        if (!empty($lead->state)) $score += 10;
        if (!empty($lead->zip_code)) $score += 10;
        if (!empty($lead->email)) $score += 5;
        if (!empty($lead->address)) $score += 5;
        if (!empty($lead->city)) $score += 5;
        if (!empty($lead->opt_in_date)) $score += 20; // Very important!
        if (!empty($lead->type)) $score += 5;
        if (!empty($lead->vendor_id)) $score += 5;
        if (!empty($lead->payload)) $score += 3;
        if (!empty($lead->meta)) $score += 2;
        
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestLead = $lead;
        }
    }
    
    // Delete all EXCEPT the best one
    $idsToDelete = [];
    foreach ($leads as $lead) {
        if ($lead->id != $bestLead->id) {
            $idsToDelete[] = $lead->id;
        }
    }
    
    if (count($idsToDelete) > 0) {
        DB::table('leads')->whereIn('id', $idsToDelete)->delete();
        $totalDeleted += count($idsToDelete);
        
        if ($processed <= 5) {
            echo "Phone {$dup->phone}: Kept ID {$bestLead->id} (score: $bestScore), deleted " . count($idsToDelete) . " duplicates\n";
        }
    }
    
    if ($processed % 100 == 0) {
        echo "  Processed $processed phones, deleted $totalDeleted duplicates...\n";
    }
}

echo "\n================================\n";
echo "✅ CLEANUP COMPLETE!\n";
echo "Processed " . number_format($processed) . " phone numbers\n";
echo "Deleted " . number_format($totalDeleted) . " duplicate records\n";

// Verify
$remaining = DB::select("
    SELECT COUNT(*) as count 
    FROM (
        SELECT phone 
        FROM leads 
        WHERE source = 'SURAJ_BULK' 
        GROUP BY phone 
        HAVING COUNT(*) > 1
    ) as dups
");

echo "Remaining duplicates: " . $remaining[0]->count . "\n";

// Show final count
$total = DB::table('leads')->where('source', 'SURAJ_BULK')->count();
echo "Total SURAJ_BULK leads now: " . number_format($total) . "\n";
echo "================================\n";


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 CLEANING SURAJ DUPLICATES 🧹\n";
echo "================================\n\n";

// Find all duplicate phone numbers
$duplicates = DB::select("
    SELECT phone, COUNT(*) as count 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    GROUP BY phone 
    HAVING COUNT(*) > 1
    ORDER BY count DESC
");

echo "Found " . count($duplicates) . " phone numbers with duplicates\n\n";

$totalDeleted = 0;
$processed = 0;

foreach ($duplicates as $dup) {
    $processed++;
    
    // Get all leads with this phone number
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->where('phone', $dup->phone)
        ->orderBy('created_at', 'asc')
        ->get();
    
    // Find the BEST record (most complete data)
    $bestLead = null;
    $bestScore = -1;
    
    foreach ($leads as $lead) {
        $score = 0;
        // Score based on data completeness
        if (!empty($lead->state)) $score += 10;
        if (!empty($lead->zip_code)) $score += 10;
        if (!empty($lead->email)) $score += 5;
        if (!empty($lead->address)) $score += 5;
        if (!empty($lead->city)) $score += 5;
        if (!empty($lead->opt_in_date)) $score += 20; // Very important!
        if (!empty($lead->type)) $score += 5;
        if (!empty($lead->vendor_id)) $score += 5;
        if (!empty($lead->payload)) $score += 3;
        if (!empty($lead->meta)) $score += 2;
        
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestLead = $lead;
        }
    }
    
    // Delete all EXCEPT the best one
    $idsToDelete = [];
    foreach ($leads as $lead) {
        if ($lead->id != $bestLead->id) {
            $idsToDelete[] = $lead->id;
        }
    }
    
    if (count($idsToDelete) > 0) {
        DB::table('leads')->whereIn('id', $idsToDelete)->delete();
        $totalDeleted += count($idsToDelete);
        
        if ($processed <= 5) {
            echo "Phone {$dup->phone}: Kept ID {$bestLead->id} (score: $bestScore), deleted " . count($idsToDelete) . " duplicates\n";
        }
    }
    
    if ($processed % 100 == 0) {
        echo "  Processed $processed phones, deleted $totalDeleted duplicates...\n";
    }
}

echo "\n================================\n";
echo "✅ CLEANUP COMPLETE!\n";
echo "Processed " . number_format($processed) . " phone numbers\n";
echo "Deleted " . number_format($totalDeleted) . " duplicate records\n";

// Verify
$remaining = DB::select("
    SELECT COUNT(*) as count 
    FROM (
        SELECT phone 
        FROM leads 
        WHERE source = 'SURAJ_BULK' 
        GROUP BY phone 
        HAVING COUNT(*) > 1
    ) as dups
");

echo "Remaining duplicates: " . $remaining[0]->count . "\n";

// Show final count
$total = DB::table('leads')->where('source', 'SURAJ_BULK')->count();
echo "Total SURAJ_BULK leads now: " . number_format($total) . "\n";
echo "================================\n";






