<?php

echo "=== VICI UPDATE - CHUNKED APPROACH ===\n\n";
echo "This will split Brain leads into chunks and update Vici in batches\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Configuration
$chunkSize = 5000; // 5000 leads per chunk
$chunkDir = 'vici_update_chunks';

// Create chunk directory
if (!file_exists($chunkDir)) {
    mkdir($chunkDir);
}

// Step 1: Export Brain leads to chunk files
echo "Step 1: Creating chunk files...\n";

$totalLeads = 0;
$chunkNumber = 0;
$currentChunk = [];
$offset = 0;

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    // Write chunk to CSV file
    $chunkFile = sprintf('%s/chunk_%03d.csv', $chunkDir, $chunkNumber);
    $fp = fopen($chunkFile, 'w');
    fputcsv($fp, ['phone', 'brain_id', 'first_name', 'last_name']);
    
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            fputcsv($fp, [
                $cleanPhone,
                $lead->external_lead_id,
                $lead->first_name,
                $lead->last_name
            ]);
            $totalLeads++;
        }
    }
    
    fclose($fp);
    
    echo "  Created chunk $chunkNumber with " . count($leads) . " leads\n";
    
    $chunkNumber++;
    $offset += $chunkSize;
}

echo "\n✅ Created $chunkNumber chunks with $totalLeads total leads\n\n";

// Step 2: Create processing script
$processScript = '#!/bin/bash
# Vici Update Script - Process all chunks

echo "=== PROCESSING VICI UPDATES IN CHUNKS ==="
echo ""

TOTAL_UPDATED=0
CHUNK_DIR="' . $chunkDir . '"

for CHUNK_FILE in $CHUNK_DIR/chunk_*.csv; do
    if [ -f "$CHUNK_FILE" ]; then
        CHUNK_NAME=$(basename "$CHUNK_FILE" .csv)
        echo "Processing $CHUNK_NAME..."
        
        # Process this chunk
        php process_vici_chunk.php "$CHUNK_FILE"
        
        if [ $? -eq 0 ]; then
            echo "  ✅ $CHUNK_NAME processed successfully"
            # Move processed chunk to done folder
            mkdir -p $CHUNK_DIR/done
            mv "$CHUNK_FILE" "$CHUNK_DIR/done/"
        else
            echo "  ❌ $CHUNK_NAME failed - will retry later"
        fi
        
        echo "  Waiting 2 seconds before next chunk..."
        sleep 2
        echo ""
    fi
done

echo "=== ALL CHUNKS PROCESSED ==="
';

file_put_contents('process_all_vici_chunks.sh', $processScript);
chmod('process_all_vici_chunks.sh', 0755);

// Step 3: Create individual chunk processor
$chunkProcessor = '<?php
// Process a single chunk of Vici updates

if ($argc < 2) {
    die("Usage: php process_vici_chunk.php <chunk_file>\n");
}

$chunkFile = $argv[1];
if (!file_exists($chunkFile)) {
    die("Chunk file not found: $chunkFile\n");
}

// Read chunk data
$data = array_map("str_getcsv", file($chunkFile));
$header = array_shift($data); // Remove header

echo "  Processing " . count($data) . " leads from chunk...\n";

// Vici API credentials
$baseUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
$apiUser = "apiuser";
$apiPass = "UZPATJ59GJAVKG8ES6";

// Lists to search
$listsToSearch = [
    // First batch - most likely lists
    6010, 6011, 6012, 6013, 6014, 6015,
    // Second batch
    6016, 6017, 6018, 6019, 6020, 6021,
    // Third batch
    6022, 6023, 6024, 6025,
    // Fourth batch
    7010, 7011, 7012,
    // Fifth batch
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    // Sixth batch
    10006, 10007, 10008, 10009, 10010, 10011,
    // Seventh batch
    60010, 60020
];

$updated = 0;
$notFound = 0;
$alreadyHasId = 0;

foreach ($data as $row) {
    list($phone, $brainId, $firstName, $lastName) = $row;
    
    // Search for this phone in Vici lists
    $found = false;
    
    foreach ($listsToSearch as $listId) {
        $params = [
            "source" => "test",
            "user" => $apiUser,
            "pass" => $apiPass,
            "function" => "search_phone_list",
            "phone_number" => $phone,
            "list_id" => $listId
        ];
        
        $url = $baseUrl . "?" . http_build_query($params);
        $response = @file_get_contents($url);
        
        if ($response && strpos($response, "ERROR") === false && strpos($response, "not found") === false) {
            $parts = explode("|", $response);
            if (count($parts) > 3) {
                $found = true;
                $viciLeadId = $parts[0];
                $currentVendorCode = $parts[1];
                
                if ($currentVendorCode == $brainId) {
                    $alreadyHasId++;
                } else {
                    // Update the lead
                    $updateParams = [
                        "source" => "test",
                        "user" => $apiUser,
                        "pass" => $apiPass,
                        "function" => "update_lead",
                        "lead_id" => $viciLeadId,
                        "list_id" => $listId,
                        "vendor_lead_code" => $brainId,
                        "search_method" => "LEAD_ID"
                    ];
                    
                    $updateUrl = $baseUrl . "?" . http_build_query($updateParams);
                    $updateResponse = @file_get_contents($updateUrl);
                    
                    if ($updateResponse && (strpos($updateResponse, "SUCCESS") !== false || strpos($updateResponse, "NOTICE") !== false)) {
                        $updated++;
                    }
                }
                break; // Found in this list, no need to check others
            }
        }
    }
    
    if (!$found) {
        $notFound++;
    }
    
    // Progress indicator
    if (($updated + $notFound + $alreadyHasId) % 100 == 0) {
        echo "    Progress: " . ($updated + $notFound + $alreadyHasId) . " / " . count($data) . "\n";
    }
}

echo "  Chunk complete: Updated=$updated, Already had ID=$alreadyHasId, Not found=$notFound\n";
';

file_put_contents('process_vici_chunk.php', $chunkProcessor);

echo "Step 2: Created processing scripts\n\n";

// Step 3: Show instructions
echo "=== READY TO PROCESS ===\n\n";
echo "Chunks created: $chunkNumber\n";
echo "Total leads: $totalLeads\n";
echo "Chunk size: $chunkSize leads each\n\n";

echo "To process all chunks, run:\n";
echo "  ./process_all_vici_chunks.sh\n\n";

echo "To process a single chunk (for testing):\n";
echo "  php process_vici_chunk.php $chunkDir/chunk_000.csv\n\n";

echo "To run in background:\n";
echo "  nohup ./process_all_vici_chunks.sh > vici_update.log 2>&1 &\n\n";

echo "Estimated time: " . round($totalLeads / 60, 0) . " minutes (at ~60 leads/minute)\n";



echo "=== VICI UPDATE - CHUNKED APPROACH ===\n\n";
echo "This will split Brain leads into chunks and update Vici in batches\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Configuration
$chunkSize = 5000; // 5000 leads per chunk
$chunkDir = 'vici_update_chunks';

// Create chunk directory
if (!file_exists($chunkDir)) {
    mkdir($chunkDir);
}

// Step 1: Export Brain leads to chunk files
echo "Step 1: Creating chunk files...\n";

$totalLeads = 0;
$chunkNumber = 0;
$currentChunk = [];
$offset = 0;

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    // Write chunk to CSV file
    $chunkFile = sprintf('%s/chunk_%03d.csv', $chunkDir, $chunkNumber);
    $fp = fopen($chunkFile, 'w');
    fputcsv($fp, ['phone', 'brain_id', 'first_name', 'last_name']);
    
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            fputcsv($fp, [
                $cleanPhone,
                $lead->external_lead_id,
                $lead->first_name,
                $lead->last_name
            ]);
            $totalLeads++;
        }
    }
    
    fclose($fp);
    
    echo "  Created chunk $chunkNumber with " . count($leads) . " leads\n";
    
    $chunkNumber++;
    $offset += $chunkSize;
}

echo "\n✅ Created $chunkNumber chunks with $totalLeads total leads\n\n";

// Step 2: Create processing script
$processScript = '#!/bin/bash
# Vici Update Script - Process all chunks

echo "=== PROCESSING VICI UPDATES IN CHUNKS ==="
echo ""

TOTAL_UPDATED=0
CHUNK_DIR="' . $chunkDir . '"

for CHUNK_FILE in $CHUNK_DIR/chunk_*.csv; do
    if [ -f "$CHUNK_FILE" ]; then
        CHUNK_NAME=$(basename "$CHUNK_FILE" .csv)
        echo "Processing $CHUNK_NAME..."
        
        # Process this chunk
        php process_vici_chunk.php "$CHUNK_FILE"
        
        if [ $? -eq 0 ]; then
            echo "  ✅ $CHUNK_NAME processed successfully"
            # Move processed chunk to done folder
            mkdir -p $CHUNK_DIR/done
            mv "$CHUNK_FILE" "$CHUNK_DIR/done/"
        else
            echo "  ❌ $CHUNK_NAME failed - will retry later"
        fi
        
        echo "  Waiting 2 seconds before next chunk..."
        sleep 2
        echo ""
    fi
done

echo "=== ALL CHUNKS PROCESSED ==="
';

file_put_contents('process_all_vici_chunks.sh', $processScript);
chmod('process_all_vici_chunks.sh', 0755);

// Step 3: Create individual chunk processor
$chunkProcessor = '<?php
// Process a single chunk of Vici updates

if ($argc < 2) {
    die("Usage: php process_vici_chunk.php <chunk_file>\n");
}

$chunkFile = $argv[1];
if (!file_exists($chunkFile)) {
    die("Chunk file not found: $chunkFile\n");
}

// Read chunk data
$data = array_map("str_getcsv", file($chunkFile));
$header = array_shift($data); // Remove header

echo "  Processing " . count($data) . " leads from chunk...\n";

// Vici API credentials
$baseUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
$apiUser = "apiuser";
$apiPass = "UZPATJ59GJAVKG8ES6";

// Lists to search
$listsToSearch = [
    // First batch - most likely lists
    6010, 6011, 6012, 6013, 6014, 6015,
    // Second batch
    6016, 6017, 6018, 6019, 6020, 6021,
    // Third batch
    6022, 6023, 6024, 6025,
    // Fourth batch
    7010, 7011, 7012,
    // Fifth batch
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    // Sixth batch
    10006, 10007, 10008, 10009, 10010, 10011,
    // Seventh batch
    60010, 60020
];

$updated = 0;
$notFound = 0;
$alreadyHasId = 0;

foreach ($data as $row) {
    list($phone, $brainId, $firstName, $lastName) = $row;
    
    // Search for this phone in Vici lists
    $found = false;
    
    foreach ($listsToSearch as $listId) {
        $params = [
            "source" => "test",
            "user" => $apiUser,
            "pass" => $apiPass,
            "function" => "search_phone_list",
            "phone_number" => $phone,
            "list_id" => $listId
        ];
        
        $url = $baseUrl . "?" . http_build_query($params);
        $response = @file_get_contents($url);
        
        if ($response && strpos($response, "ERROR") === false && strpos($response, "not found") === false) {
            $parts = explode("|", $response);
            if (count($parts) > 3) {
                $found = true;
                $viciLeadId = $parts[0];
                $currentVendorCode = $parts[1];
                
                if ($currentVendorCode == $brainId) {
                    $alreadyHasId++;
                } else {
                    // Update the lead
                    $updateParams = [
                        "source" => "test",
                        "user" => $apiUser,
                        "pass" => $apiPass,
                        "function" => "update_lead",
                        "lead_id" => $viciLeadId,
                        "list_id" => $listId,
                        "vendor_lead_code" => $brainId,
                        "search_method" => "LEAD_ID"
                    ];
                    
                    $updateUrl = $baseUrl . "?" . http_build_query($updateParams);
                    $updateResponse = @file_get_contents($updateUrl);
                    
                    if ($updateResponse && (strpos($updateResponse, "SUCCESS") !== false || strpos($updateResponse, "NOTICE") !== false)) {
                        $updated++;
                    }
                }
                break; // Found in this list, no need to check others
            }
        }
    }
    
    if (!$found) {
        $notFound++;
    }
    
    // Progress indicator
    if (($updated + $notFound + $alreadyHasId) % 100 == 0) {
        echo "    Progress: " . ($updated + $notFound + $alreadyHasId) . " / " . count($data) . "\n";
    }
}

echo "  Chunk complete: Updated=$updated, Already had ID=$alreadyHasId, Not found=$notFound\n";
';

file_put_contents('process_vici_chunk.php', $chunkProcessor);

echo "Step 2: Created processing scripts\n\n";

// Step 3: Show instructions
echo "=== READY TO PROCESS ===\n\n";
echo "Chunks created: $chunkNumber\n";
echo "Total leads: $totalLeads\n";
echo "Chunk size: $chunkSize leads each\n\n";

echo "To process all chunks, run:\n";
echo "  ./process_all_vici_chunks.sh\n\n";

echo "To process a single chunk (for testing):\n";
echo "  php process_vici_chunk.php $chunkDir/chunk_000.csv\n\n";

echo "To run in background:\n";
echo "  nohup ./process_all_vici_chunks.sh > vici_update.log 2>&1 &\n\n";

echo "Estimated time: " . round($totalLeads / 60, 0) . " minutes (at ~60 leads/minute)\n";








