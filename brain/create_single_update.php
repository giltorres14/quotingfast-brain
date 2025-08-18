<?php

echo "=== CREATING SINGLE CONSOLIDATED UPDATE FILE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Target lists
$allLists = implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
));

echo "Creating optimized update using CASE statements...\n";

// Get all Brain leads
$leads = DB::table('leads')
    ->whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->get(['phone', 'external_lead_id']);

echo "Processing " . count($leads) . " Brain leads...\n";

// Build phone to Brain ID mapping
$phoneMap = [];
foreach ($leads as $lead) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
    if (strlen($cleanPhone) >= 10) {
        $phoneMap[$cleanPhone] = $lead->external_lead_id;
    }
}

echo "Found " . count($phoneMap) . " valid phone numbers\n";

// Create a single UPDATE with CASE statement (more efficient)
$fp = fopen('vici_single_update.sql', 'w');

fwrite($fp, "-- Single Consolidated Update for Vici\n");
fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- Total mappings: " . count($phoneMap) . "\n\n");
fwrite($fp, "USE Q6hdjl67GRigMofv;\n\n");

// Process in chunks to avoid SQL too long
$chunkSize = 1000;
$chunks = array_chunk($phoneMap, $chunkSize, true);
$chunkNum = 0;

foreach ($chunks as $chunk) {
    fwrite($fp, "-- Chunk " . ($chunkNum + 1) . " of " . count($chunks) . "\n");
    
    // Build UPDATE with CASE
    fwrite($fp, "UPDATE vicidial_list SET\n");
    fwrite($fp, "  vendor_lead_code = CASE phone_number\n");
    
    foreach ($chunk as $phone => $brainId) {
        fwrite($fp, "    WHEN '$phone' THEN '$brainId'\n");
    }
    
    fwrite($fp, "    ELSE vendor_lead_code\n");
    fwrite($fp, "  END,\n");
    fwrite($fp, "  source_id = CASE phone_number\n");
    
    foreach ($chunk as $phone => $brainId) {
        fwrite($fp, "    WHEN '$phone' THEN 'BRAIN_$brainId'\n");
    }
    
    fwrite($fp, "    ELSE source_id\n");
    fwrite($fp, "  END\n");
    fwrite($fp, "WHERE phone_number IN ('" . implode("','", array_keys($chunk)) . "')\n");
    fwrite($fp, "  AND list_id IN ($allLists)\n");
    fwrite($fp, "  AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}\$');\n\n");
    
    $chunkNum++;
}

// Add verification query
fwrite($fp, "-- Verification\n");
fwrite($fp, "SELECT COUNT(*) as 'Total Vici leads with Brain IDs' FROM vicidial_list\n");
fwrite($fp, "WHERE vendor_lead_code REGEXP '^[0-9]{13}\$'\n");
fwrite($fp, "AND list_id IN ($allLists);\n");

fclose($fp);

$fileSize = filesize('vici_single_update.sql');
echo "\n✅ Created vici_single_update.sql (" . round($fileSize / 1024 / 1024, 2) . " MB)\n";
echo "   Contains " . count($chunks) . " UPDATE statements\n";
echo "   Will update up to " . count($phoneMap) . " Vici leads\n\n";

echo "This file can be executed directly on the Vici server for maximum speed.\n";



echo "=== CREATING SINGLE CONSOLIDATED UPDATE FILE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Target lists
$allLists = implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
));

echo "Creating optimized update using CASE statements...\n";

// Get all Brain leads
$leads = DB::table('leads')
    ->whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->get(['phone', 'external_lead_id']);

echo "Processing " . count($leads) . " Brain leads...\n";

// Build phone to Brain ID mapping
$phoneMap = [];
foreach ($leads as $lead) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
    if (strlen($cleanPhone) >= 10) {
        $phoneMap[$cleanPhone] = $lead->external_lead_id;
    }
}

echo "Found " . count($phoneMap) . " valid phone numbers\n";

// Create a single UPDATE with CASE statement (more efficient)
$fp = fopen('vici_single_update.sql', 'w');

fwrite($fp, "-- Single Consolidated Update for Vici\n");
fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- Total mappings: " . count($phoneMap) . "\n\n");
fwrite($fp, "USE Q6hdjl67GRigMofv;\n\n");

// Process in chunks to avoid SQL too long
$chunkSize = 1000;
$chunks = array_chunk($phoneMap, $chunkSize, true);
$chunkNum = 0;

foreach ($chunks as $chunk) {
    fwrite($fp, "-- Chunk " . ($chunkNum + 1) . " of " . count($chunks) . "\n");
    
    // Build UPDATE with CASE
    fwrite($fp, "UPDATE vicidial_list SET\n");
    fwrite($fp, "  vendor_lead_code = CASE phone_number\n");
    
    foreach ($chunk as $phone => $brainId) {
        fwrite($fp, "    WHEN '$phone' THEN '$brainId'\n");
    }
    
    fwrite($fp, "    ELSE vendor_lead_code\n");
    fwrite($fp, "  END,\n");
    fwrite($fp, "  source_id = CASE phone_number\n");
    
    foreach ($chunk as $phone => $brainId) {
        fwrite($fp, "    WHEN '$phone' THEN 'BRAIN_$brainId'\n");
    }
    
    fwrite($fp, "    ELSE source_id\n");
    fwrite($fp, "  END\n");
    fwrite($fp, "WHERE phone_number IN ('" . implode("','", array_keys($chunk)) . "')\n");
    fwrite($fp, "  AND list_id IN ($allLists)\n");
    fwrite($fp, "  AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}\$');\n\n");
    
    $chunkNum++;
}

// Add verification query
fwrite($fp, "-- Verification\n");
fwrite($fp, "SELECT COUNT(*) as 'Total Vici leads with Brain IDs' FROM vicidial_list\n");
fwrite($fp, "WHERE vendor_lead_code REGEXP '^[0-9]{13}\$'\n");
fwrite($fp, "AND list_id IN ($allLists);\n");

fclose($fp);

$fileSize = filesize('vici_single_update.sql');
echo "\n✅ Created vici_single_update.sql (" . round($fileSize / 1024 / 1024, 2) . " MB)\n";
echo "   Contains " . count($chunks) . " UPDATE statements\n";
echo "   Will update up to " . count($phoneMap) . " Vici leads\n\n";

echo "This file can be executed directly on the Vici server for maximum speed.\n";






