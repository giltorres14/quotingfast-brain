<?php

echo "=== VICI DIRECT DATABASE UPDATE VIA SSH ===\n\n";
echo "This will update ALL Vici leads with Brain IDs in minutes, not days!\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get command line arguments
$testMode = in_array('--test', $argv);
$execute = in_array('--execute', $argv);

if (!$testMode && !$execute) {
    echo "⚠️  SAFETY MODE - No changes will be made\n";
    echo "   Use --test to test with 100 leads\n";
    echo "   Use --execute to run the full update\n\n";
}

// Step 1: Export Brain leads to a CSV file
echo "Step 1: Preparing Brain leads data...\n";

$csvFile = 'brain_leads_for_vici.csv';
$fp = fopen($csvFile, 'w');
fputcsv($fp, ['phone', 'brain_id', 'first_name', 'last_name']);

$totalBrainLeads = 0;
$chunkSize = 5000;
$offset = 0;

while (true) {
    $chunk = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($chunk->isEmpty()) {
        break;
    }
    
    foreach ($chunk as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            fputcsv($fp, [
                $cleanPhone,
                $lead->external_lead_id,
                $lead->first_name,
                $lead->last_name
            ]);
            $totalBrainLeads++;
        }
    }
    
    $offset += $chunkSize;
    echo "  Processed $totalBrainLeads leads...\r";
}

fclose($fp);
echo "\n✅ Prepared $totalBrainLeads Brain leads\n\n";

// Step 2: Upload CSV to Vici server
echo "Step 2: Uploading data to Vici server...\n";

$uploadResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "cat > /tmp/brain_leads.csv << 'EOF'\n" . file_get_contents($csvFile) . "\nEOF"
]);

if (!$uploadResponse->successful()) {
    die("❌ Failed to upload CSV to Vici server\n");
}

echo "✅ Data uploaded to Vici server\n\n";

// Step 3: Create MySQL update script
echo "Step 3: Creating update script...\n";

$mysqlScript = "
-- VICI Lead Update Script
-- Updates vendor_lead_code with Brain external_lead_id

USE asterisk;

-- Create temporary table for Brain data
DROP TABLE IF EXISTS temp_brain_leads;
CREATE TEMPORARY TABLE temp_brain_leads (
    phone VARCHAR(20),
    brain_id VARCHAR(20),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    INDEX idx_phone (phone)
);

-- Load Brain data from CSV
LOAD DATA LOCAL INFILE '/tmp/brain_leads.csv'
INTO TABLE temp_brain_leads
FIELDS TERMINATED BY ','
ENCLOSED BY '\"'
LINES TERMINATED BY '\\n'
IGNORE 1 LINES
(phone, brain_id, first_name, last_name);

-- Show how many Brain leads we have
SELECT COUNT(*) as total_brain_leads FROM temp_brain_leads;

-- Count how many matches we'll update
SELECT COUNT(*) as leads_to_update
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL;

-- Count how many already have Brain ID
SELECT COUNT(*) as already_have_brain_id
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code = tbl.brain_id;
";

if ($testMode) {
    $mysqlScript .= "
-- TEST MODE: Show sample of what would be updated
SELECT vl.lead_id, vl.list_id, vl.phone_number, 
       vl.vendor_lead_code as current_vendor_code,
       tbl.brain_id as new_vendor_code,
       vl.first_name, vl.last_name
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL
LIMIT 100;
";
} elseif ($execute) {
    $mysqlScript .= "
-- EXECUTE MODE: Perform the update
UPDATE vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
SET vl.vendor_lead_code = tbl.brain_id,
    vl.source_id = CONCAT('BRAIN_', tbl.brain_id),
    vl.comments = CONCAT('Brain ID updated: ', NOW(), ' | ', COALESCE(vl.comments, ''))
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL;

-- Show results
SELECT ROW_COUNT() as leads_updated;

-- Show sample of updated leads
SELECT lead_id, list_id, phone_number, vendor_lead_code, source_id
FROM vicidial_list
WHERE source_id LIKE 'BRAIN_%'
ORDER BY lead_id DESC
LIMIT 10;
";
} else {
    $mysqlScript .= "
-- SAFETY MODE: Just show statistics, no updates
SELECT 'SAFETY MODE - No updates performed' as status;
";
}

$mysqlScript .= "
-- Cleanup
DROP TABLE IF EXISTS temp_brain_leads;

-- Final statistics
SELECT 
    COUNT(*) as total_vici_leads,
    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id,
    SUM(CASE WHEN vendor_lead_code IS NULL OR vendor_lead_code = '' THEN 1 ELSE 0 END) as no_vendor_code
FROM vicidial_list
WHERE list_id IN (" . implode(',', array_merge(
    // Autodial lists
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011],
    // Auto2 lists
    [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");
";

// Save script to file
file_put_contents('vici_update.sql', $mysqlScript);
echo "✅ Update script created\n\n";

// Step 4: Execute the script on Vici server
echo "Step 4: Executing database update...\n";

// Upload SQL script
$uploadSqlResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "cat > /tmp/vici_update.sql << 'EOF'\n" . $mysqlScript . "\nEOF"
]);

if (!$uploadSqlResponse->successful()) {
    die("❌ Failed to upload SQL script to Vici server\n");
}

// Execute the SQL script
$executeResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => 'mysql -u Superman -p8ZDWGAAQRD asterisk < /tmp/vici_update.sql 2>&1'
]);

if ($executeResponse->successful()) {
    $output = $executeResponse->json()['output'] ?? '';
    
    // Parse and display results
    echo "\n=== RESULTS ===\n";
    echo $output;
    
    if ($testMode) {
        echo "\n✅ TEST MODE COMPLETE\n";
        echo "   Review the results above\n";
        echo "   Run with --execute to perform the actual update\n";
    } elseif ($execute) {
        echo "\n✅ UPDATE COMPLETE!\n";
        echo "   All matching Vici leads now have Brain IDs\n";
    } else {
        echo "\n✅ ANALYSIS COMPLETE\n";
        echo "   Run with --test to see sample updates\n";
        echo "   Run with --execute to perform the update\n";
    }
} else {
    echo "❌ Failed to execute SQL script\n";
    $error = $executeResponse->json()['error'] ?? 'Unknown error';
    echo "Error: $error\n";
}

// Cleanup local files
unlink($csvFile);
unlink('vici_update.sql');

echo "\n=== DONE ===\n";
echo "Time taken: " . round((time() - $_SERVER['REQUEST_TIME']) / 60, 1) . " minutes\n";



echo "=== VICI DIRECT DATABASE UPDATE VIA SSH ===\n\n";
echo "This will update ALL Vici leads with Brain IDs in minutes, not days!\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get command line arguments
$testMode = in_array('--test', $argv);
$execute = in_array('--execute', $argv);

if (!$testMode && !$execute) {
    echo "⚠️  SAFETY MODE - No changes will be made\n";
    echo "   Use --test to test with 100 leads\n";
    echo "   Use --execute to run the full update\n\n";
}

// Step 1: Export Brain leads to a CSV file
echo "Step 1: Preparing Brain leads data...\n";

$csvFile = 'brain_leads_for_vici.csv';
$fp = fopen($csvFile, 'w');
fputcsv($fp, ['phone', 'brain_id', 'first_name', 'last_name']);

$totalBrainLeads = 0;
$chunkSize = 5000;
$offset = 0;

while (true) {
    $chunk = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($chunk->isEmpty()) {
        break;
    }
    
    foreach ($chunk as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            fputcsv($fp, [
                $cleanPhone,
                $lead->external_lead_id,
                $lead->first_name,
                $lead->last_name
            ]);
            $totalBrainLeads++;
        }
    }
    
    $offset += $chunkSize;
    echo "  Processed $totalBrainLeads leads...\r";
}

fclose($fp);
echo "\n✅ Prepared $totalBrainLeads Brain leads\n\n";

// Step 2: Upload CSV to Vici server
echo "Step 2: Uploading data to Vici server...\n";

$uploadResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "cat > /tmp/brain_leads.csv << 'EOF'\n" . file_get_contents($csvFile) . "\nEOF"
]);

if (!$uploadResponse->successful()) {
    die("❌ Failed to upload CSV to Vici server\n");
}

echo "✅ Data uploaded to Vici server\n\n";

// Step 3: Create MySQL update script
echo "Step 3: Creating update script...\n";

$mysqlScript = "
-- VICI Lead Update Script
-- Updates vendor_lead_code with Brain external_lead_id

USE asterisk;

-- Create temporary table for Brain data
DROP TABLE IF EXISTS temp_brain_leads;
CREATE TEMPORARY TABLE temp_brain_leads (
    phone VARCHAR(20),
    brain_id VARCHAR(20),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    INDEX idx_phone (phone)
);

-- Load Brain data from CSV
LOAD DATA LOCAL INFILE '/tmp/brain_leads.csv'
INTO TABLE temp_brain_leads
FIELDS TERMINATED BY ','
ENCLOSED BY '\"'
LINES TERMINATED BY '\\n'
IGNORE 1 LINES
(phone, brain_id, first_name, last_name);

-- Show how many Brain leads we have
SELECT COUNT(*) as total_brain_leads FROM temp_brain_leads;

-- Count how many matches we'll update
SELECT COUNT(*) as leads_to_update
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL;

-- Count how many already have Brain ID
SELECT COUNT(*) as already_have_brain_id
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code = tbl.brain_id;
";

if ($testMode) {
    $mysqlScript .= "
-- TEST MODE: Show sample of what would be updated
SELECT vl.lead_id, vl.list_id, vl.phone_number, 
       vl.vendor_lead_code as current_vendor_code,
       tbl.brain_id as new_vendor_code,
       vl.first_name, vl.last_name
FROM vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL
LIMIT 100;
";
} elseif ($execute) {
    $mysqlScript .= "
-- EXECUTE MODE: Perform the update
UPDATE vicidial_list vl
INNER JOIN temp_brain_leads tbl ON vl.phone_number = tbl.phone
SET vl.vendor_lead_code = tbl.brain_id,
    vl.source_id = CONCAT('BRAIN_', tbl.brain_id),
    vl.comments = CONCAT('Brain ID updated: ', NOW(), ' | ', COALESCE(vl.comments, ''))
WHERE vl.vendor_lead_code != tbl.brain_id OR vl.vendor_lead_code IS NULL;

-- Show results
SELECT ROW_COUNT() as leads_updated;

-- Show sample of updated leads
SELECT lead_id, list_id, phone_number, vendor_lead_code, source_id
FROM vicidial_list
WHERE source_id LIKE 'BRAIN_%'
ORDER BY lead_id DESC
LIMIT 10;
";
} else {
    $mysqlScript .= "
-- SAFETY MODE: Just show statistics, no updates
SELECT 'SAFETY MODE - No updates performed' as status;
";
}

$mysqlScript .= "
-- Cleanup
DROP TABLE IF EXISTS temp_brain_leads;

-- Final statistics
SELECT 
    COUNT(*) as total_vici_leads,
    SUM(CASE WHEN vendor_lead_code REGEXP '^[0-9]{13}$' THEN 1 ELSE 0 END) as has_brain_id,
    SUM(CASE WHEN vendor_lead_code IS NULL OR vendor_lead_code = '' THEN 1 ELSE 0 END) as no_vendor_code
FROM vicidial_list
WHERE list_id IN (" . implode(',', array_merge(
    // Autodial lists
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011],
    // Auto2 lists
    [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");
";

// Save script to file
file_put_contents('vici_update.sql', $mysqlScript);
echo "✅ Update script created\n\n";

// Step 4: Execute the script on Vici server
echo "Step 4: Executing database update...\n";

// Upload SQL script
$uploadSqlResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "cat > /tmp/vici_update.sql << 'EOF'\n" . $mysqlScript . "\nEOF"
]);

if (!$uploadSqlResponse->successful()) {
    die("❌ Failed to upload SQL script to Vici server\n");
}

// Execute the SQL script
$executeResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => 'mysql -u Superman -p8ZDWGAAQRD asterisk < /tmp/vici_update.sql 2>&1'
]);

if ($executeResponse->successful()) {
    $output = $executeResponse->json()['output'] ?? '';
    
    // Parse and display results
    echo "\n=== RESULTS ===\n";
    echo $output;
    
    if ($testMode) {
        echo "\n✅ TEST MODE COMPLETE\n";
        echo "   Review the results above\n";
        echo "   Run with --execute to perform the actual update\n";
    } elseif ($execute) {
        echo "\n✅ UPDATE COMPLETE!\n";
        echo "   All matching Vici leads now have Brain IDs\n";
    } else {
        echo "\n✅ ANALYSIS COMPLETE\n";
        echo "   Run with --test to see sample updates\n";
        echo "   Run with --execute to perform the update\n";
    }
} else {
    echo "❌ Failed to execute SQL script\n";
    $error = $executeResponse->json()['error'] ?? 'Unknown error';
    echo "Error: $error\n";
}

// Cleanup local files
unlink($csvFile);
unlink('vici_update.sql');

echo "\n=== DONE ===\n";
echo "Time taken: " . round((time() - $_SERVER['REQUEST_TIME']) / 60, 1) . " minutes\n";


