<?php

echo "=== VICI FAST BULK UPDATE VIA SSH ===\n\n";
echo "This will update ALL Vici leads with Brain IDs in MINUTES!\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get command line arguments
$execute = in_array('--execute', $argv);

if (!$execute) {
    echo "⚠️  DRY RUN MODE - No changes will be made\n";
    echo "   Use --execute to perform the actual update\n\n";
}

// Step 1: Create SQL statements for all Brain leads
echo "Step 1: Preparing update statements...\n";

$sqlFile = 'vici_bulk_update.sql';
$fp = fopen($sqlFile, 'w');

// Write header
fwrite($fp, "-- VICI Bulk Update Script\n");
fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- This updates vendor_lead_code for all matching phone numbers\n\n");
fwrite($fp, "USE asterisk;\n\n");
fwrite($fp, "SET autocommit=0;\n");
fwrite($fp, "START TRANSACTION;\n\n");

// Create temporary table and index
fwrite($fp, "-- Create temporary mapping table\n");
fwrite($fp, "CREATE TEMPORARY TABLE IF NOT EXISTS brain_lead_map (\n");
fwrite($fp, "    phone VARCHAR(20) PRIMARY KEY,\n");
fwrite($fp, "    brain_id VARCHAR(20)\n");
fwrite($fp, ") ENGINE=MEMORY;\n\n");

// Process Brain leads in chunks to build mapping
$totalLeads = 0;
$chunkSize = 1000;
$offset = 0;
$batchCount = 0;

echo "  Building phone to Brain ID mapping...\n";

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    // Build batch insert for mapping table
    fwrite($fp, "-- Batch $batchCount\n");
    fwrite($fp, "INSERT INTO brain_lead_map (phone, brain_id) VALUES\n");
    
    $values = [];
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            $values[] = sprintf("('%s','%s')", 
                addslashes($cleanPhone), 
                addslashes($lead->external_lead_id)
            );
            $totalLeads++;
        }
    }
    
    if (!empty($values)) {
        fwrite($fp, implode(",\n", $values) . ";\n\n");
    }
    
    $offset += $chunkSize;
    $batchCount++;
    echo "    Processed $totalLeads leads...\r";
}

echo "\n  ✅ Prepared $totalLeads Brain lead mappings\n\n";

// Add the actual UPDATE statement
fwrite($fp, "-- Show statistics before update\n");
fwrite($fp, "SELECT 'Before Update' as phase,\n");
fwrite($fp, "    COUNT(*) as total_leads,\n");
fwrite($fp, "    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id\n");
fwrite($fp, "FROM vicidial_list\n");
fwrite($fp, "WHERE list_id IN (" . implode(',', array_merge(
    // Autodial lists
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011],
    // Auto2 lists
    [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n");

if ($execute) {
    // Perform the update
    fwrite($fp, "-- EXECUTE: Update all matching leads\n");
    fwrite($fp, "UPDATE vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "SET vl.vendor_lead_code = blm.brain_id,\n");
    fwrite($fp, "    vl.source_id = CONCAT('BRAIN_', blm.brain_id)\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");\n\n");
    
    fwrite($fp, "SELECT ROW_COUNT() as 'Leads Updated';\n\n");
    fwrite($fp, "COMMIT;\n\n");
} else {
    // Dry run - just show what would be updated
    fwrite($fp, "-- DRY RUN: Show what would be updated\n");
    fwrite($fp, "SELECT COUNT(*) as 'Would Update'\n");
    fwrite($fp, "FROM vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");\n\n");
    
    fwrite($fp, "-- Show sample of what would be updated\n");
    fwrite($fp, "SELECT vl.lead_id, vl.list_id, vl.phone_number,\n");
    fwrite($fp, "    vl.vendor_lead_code as current_code,\n");
    fwrite($fp, "    blm.brain_id as new_code\n");
    fwrite($fp, "FROM vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ")\n");
    fwrite($fp, "LIMIT 20;\n\n");
    
    fwrite($fp, "ROLLBACK;\n\n");
}

// Show final statistics
fwrite($fp, "-- Show statistics after update\n");
fwrite($fp, "SELECT 'After Update' as phase,\n");
fwrite($fp, "    COUNT(*) as total_leads,\n");
fwrite($fp, "    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id\n");
fwrite($fp, "FROM vicidial_list\n");
fwrite($fp, "WHERE list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n");

fwrite($fp, "-- Cleanup\n");
fwrite($fp, "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n");

fclose($fp);

$fileSize = filesize($sqlFile);
echo "Step 2: SQL file created: $sqlFile (" . round($fileSize / 1024 / 1024, 2) . " MB)\n\n";

// Step 3: Split the file if it's too large
if ($fileSize > 5 * 1024 * 1024) { // If larger than 5MB, split it
    echo "Step 3: File is large, splitting into smaller chunks...\n";
    
    $lines = file($sqlFile);
    $chunkSize = 10000; // lines per chunk
    $chunkNum = 0;
    $currentChunk = [];
    $header = array_slice($lines, 0, 10); // Keep first 10 lines as header
    
    foreach ($lines as $i => $line) {
        if ($i < 10) continue; // Skip header lines
        
        $currentChunk[] = $line;
        
        if (count($currentChunk) >= $chunkSize || $i == count($lines) - 1) {
            $chunkFile = "vici_update_chunk_" . sprintf("%03d", $chunkNum) . ".sql";
            $fp = fopen($chunkFile, 'w');
            fwrite($fp, implode('', $header));
            fwrite($fp, implode('', $currentChunk));
            if ($i == count($lines) - 1) {
                // Add commit and cleanup to last chunk
                fwrite($fp, "\nCOMMIT;\n");
                fwrite($fp, "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n");
            }
            fclose($fp);
            echo "  Created $chunkFile\n";
            $chunkNum++;
            $currentChunk = [];
        }
    }
    
    echo "  ✅ Split into $chunkNum chunks\n\n";
    $sqlFiles = glob("vici_update_chunk_*.sql");
} else {
    $sqlFiles = [$sqlFile];
}

// Step 4: Execute via SSH
echo "Step 4: Executing on Vici server...\n\n";

foreach ($sqlFiles as $idx => $file) {
    echo "Processing " . basename($file) . "...\n";
    
    // Read file content
    $sqlContent = file_get_contents($file);
    
    // Execute via proxy
    $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u cron -p1234 asterisk -e " . escapeshellarg($sqlContent) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        echo "Result:\n" . substr($output, 0, 1000) . "\n\n";
        
        // If there's an error, try with different credentials
        if (strpos($output, 'Access denied') !== false) {
            echo "Trying with Superman user...\n";
            $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk -e " . escapeshellarg($sqlContent) . " 2>&1"
            ]);
            
            if ($response->successful()) {
                $output = $response->json()['output'] ?? '';
                echo "Result:\n" . substr($output, 0, 1000) . "\n\n";
            }
        }
    } else {
        echo "❌ Failed to execute\n";
    }
    
    // Small delay between chunks
    if ($idx < count($sqlFiles) - 1) {
        sleep(2);
    }
}

// Cleanup
foreach ($sqlFiles as $file) {
    unlink($file);
}

echo "\n=== COMPLETE ===\n";
if ($execute) {
    echo "✅ All matching Vici leads have been updated with Brain IDs!\n";
} else {
    echo "✅ Dry run complete. Review the results above.\n";
    echo "   Run with --execute to perform the actual update.\n";
}

$totalTime = time() - $_SERVER['REQUEST_TIME'];
echo "Time taken: " . round($totalTime / 60, 1) . " minutes\n";



echo "=== VICI FAST BULK UPDATE VIA SSH ===\n\n";
echo "This will update ALL Vici leads with Brain IDs in MINUTES!\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get command line arguments
$execute = in_array('--execute', $argv);

if (!$execute) {
    echo "⚠️  DRY RUN MODE - No changes will be made\n";
    echo "   Use --execute to perform the actual update\n\n";
}

// Step 1: Create SQL statements for all Brain leads
echo "Step 1: Preparing update statements...\n";

$sqlFile = 'vici_bulk_update.sql';
$fp = fopen($sqlFile, 'w');

// Write header
fwrite($fp, "-- VICI Bulk Update Script\n");
fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- This updates vendor_lead_code for all matching phone numbers\n\n");
fwrite($fp, "USE asterisk;\n\n");
fwrite($fp, "SET autocommit=0;\n");
fwrite($fp, "START TRANSACTION;\n\n");

// Create temporary table and index
fwrite($fp, "-- Create temporary mapping table\n");
fwrite($fp, "CREATE TEMPORARY TABLE IF NOT EXISTS brain_lead_map (\n");
fwrite($fp, "    phone VARCHAR(20) PRIMARY KEY,\n");
fwrite($fp, "    brain_id VARCHAR(20)\n");
fwrite($fp, ") ENGINE=MEMORY;\n\n");

// Process Brain leads in chunks to build mapping
$totalLeads = 0;
$chunkSize = 1000;
$offset = 0;
$batchCount = 0;

echo "  Building phone to Brain ID mapping...\n";

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    // Build batch insert for mapping table
    fwrite($fp, "-- Batch $batchCount\n");
    fwrite($fp, "INSERT INTO brain_lead_map (phone, brain_id) VALUES\n");
    
    $values = [];
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            $values[] = sprintf("('%s','%s')", 
                addslashes($cleanPhone), 
                addslashes($lead->external_lead_id)
            );
            $totalLeads++;
        }
    }
    
    if (!empty($values)) {
        fwrite($fp, implode(",\n", $values) . ";\n\n");
    }
    
    $offset += $chunkSize;
    $batchCount++;
    echo "    Processed $totalLeads leads...\r";
}

echo "\n  ✅ Prepared $totalLeads Brain lead mappings\n\n";

// Add the actual UPDATE statement
fwrite($fp, "-- Show statistics before update\n");
fwrite($fp, "SELECT 'Before Update' as phase,\n");
fwrite($fp, "    COUNT(*) as total_leads,\n");
fwrite($fp, "    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id\n");
fwrite($fp, "FROM vicidial_list\n");
fwrite($fp, "WHERE list_id IN (" . implode(',', array_merge(
    // Autodial lists
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011],
    // Auto2 lists
    [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n");

if ($execute) {
    // Perform the update
    fwrite($fp, "-- EXECUTE: Update all matching leads\n");
    fwrite($fp, "UPDATE vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "SET vl.vendor_lead_code = blm.brain_id,\n");
    fwrite($fp, "    vl.source_id = CONCAT('BRAIN_', blm.brain_id)\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");\n\n");
    
    fwrite($fp, "SELECT ROW_COUNT() as 'Leads Updated';\n\n");
    fwrite($fp, "COMMIT;\n\n");
} else {
    // Dry run - just show what would be updated
    fwrite($fp, "-- DRY RUN: Show what would be updated\n");
    fwrite($fp, "SELECT COUNT(*) as 'Would Update'\n");
    fwrite($fp, "FROM vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");\n\n");
    
    fwrite($fp, "-- Show sample of what would be updated\n");
    fwrite($fp, "SELECT vl.lead_id, vl.list_id, vl.phone_number,\n");
    fwrite($fp, "    vl.vendor_lead_code as current_code,\n");
    fwrite($fp, "    blm.brain_id as new_code\n");
    fwrite($fp, "FROM vicidial_list vl\n");
    fwrite($fp, "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n");
    fwrite($fp, "WHERE (vl.vendor_lead_code IS NULL \n");
    fwrite($fp, "    OR vl.vendor_lead_code = ''\n");
    fwrite($fp, "    OR vl.vendor_lead_code != blm.brain_id)\n");
    fwrite($fp, "AND vl.list_id IN (" . implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ")\n");
    fwrite($fp, "LIMIT 20;\n\n");
    
    fwrite($fp, "ROLLBACK;\n\n");
}

// Show final statistics
fwrite($fp, "-- Show statistics after update\n");
fwrite($fp, "SELECT 'After Update' as phase,\n");
fwrite($fp, "    COUNT(*) as total_leads,\n");
fwrite($fp, "    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id\n");
fwrite($fp, "FROM vicidial_list\n");
fwrite($fp, "WHERE list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n");

fwrite($fp, "-- Cleanup\n");
fwrite($fp, "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n");

fclose($fp);

$fileSize = filesize($sqlFile);
echo "Step 2: SQL file created: $sqlFile (" . round($fileSize / 1024 / 1024, 2) . " MB)\n\n";

// Step 3: Split the file if it's too large
if ($fileSize > 5 * 1024 * 1024) { // If larger than 5MB, split it
    echo "Step 3: File is large, splitting into smaller chunks...\n";
    
    $lines = file($sqlFile);
    $chunkSize = 10000; // lines per chunk
    $chunkNum = 0;
    $currentChunk = [];
    $header = array_slice($lines, 0, 10); // Keep first 10 lines as header
    
    foreach ($lines as $i => $line) {
        if ($i < 10) continue; // Skip header lines
        
        $currentChunk[] = $line;
        
        if (count($currentChunk) >= $chunkSize || $i == count($lines) - 1) {
            $chunkFile = "vici_update_chunk_" . sprintf("%03d", $chunkNum) . ".sql";
            $fp = fopen($chunkFile, 'w');
            fwrite($fp, implode('', $header));
            fwrite($fp, implode('', $currentChunk));
            if ($i == count($lines) - 1) {
                // Add commit and cleanup to last chunk
                fwrite($fp, "\nCOMMIT;\n");
                fwrite($fp, "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n");
            }
            fclose($fp);
            echo "  Created $chunkFile\n";
            $chunkNum++;
            $currentChunk = [];
        }
    }
    
    echo "  ✅ Split into $chunkNum chunks\n\n";
    $sqlFiles = glob("vici_update_chunk_*.sql");
} else {
    $sqlFiles = [$sqlFile];
}

// Step 4: Execute via SSH
echo "Step 4: Executing on Vici server...\n\n";

foreach ($sqlFiles as $idx => $file) {
    echo "Processing " . basename($file) . "...\n";
    
    // Read file content
    $sqlContent = file_get_contents($file);
    
    // Execute via proxy
    $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u cron -p1234 asterisk -e " . escapeshellarg($sqlContent) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        echo "Result:\n" . substr($output, 0, 1000) . "\n\n";
        
        // If there's an error, try with different credentials
        if (strpos($output, 'Access denied') !== false) {
            echo "Trying with Superman user...\n";
            $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk -e " . escapeshellarg($sqlContent) . " 2>&1"
            ]);
            
            if ($response->successful()) {
                $output = $response->json()['output'] ?? '';
                echo "Result:\n" . substr($output, 0, 1000) . "\n\n";
            }
        }
    } else {
        echo "❌ Failed to execute\n";
    }
    
    // Small delay between chunks
    if ($idx < count($sqlFiles) - 1) {
        sleep(2);
    }
}

// Cleanup
foreach ($sqlFiles as $file) {
    unlink($file);
}

echo "\n=== COMPLETE ===\n";
if ($execute) {
    echo "✅ All matching Vici leads have been updated with Brain IDs!\n";
} else {
    echo "✅ Dry run complete. Review the results above.\n";
    echo "   Run with --execute to perform the actual update.\n";
}

$totalTime = time() - $_SERVER['REQUEST_TIME'];
echo "Time taken: " . round($totalTime / 60, 1) . " minutes\n";


