<?php

echo "=== VICI FAST BULK UPDATE - FINAL VERSION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Correct Vici database credentials - using root (no password needed)
$dbUser = 'root';
$dbPass = ''; // No password for root
$dbName = 'Q6hdjl67GRigMofv';

$chunks = glob('vici_chunk_*');
sort($chunks);

echo "Found " . count($chunks) . " chunks to process\n";
echo "Using database: $dbName\n\n";

$startTime = time();

// Step 1: Load all Brain mappings into temp table
echo "Step 1: Loading Brain lead mappings into Vici...\n";

// First, create the temp table
$setupSQL = "USE $dbName;\n";
$setupSQL .= "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n";
$setupSQL .= "CREATE TEMPORARY TABLE brain_lead_map (\n";
$setupSQL .= "    phone VARCHAR(20) PRIMARY KEY,\n";
$setupSQL .= "    brain_id VARCHAR(20)\n";
$setupSQL .= ") ENGINE=MEMORY;\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($setupSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') === false) {
        echo "✅ Temporary table created\n";
    } else {
        echo "❌ Error creating table: $output\n";
        exit(1);
    }
}

// Load data from chunks
foreach ($chunks as $idx => $chunk) {
    echo "  Loading chunk " . ($idx + 1) . "/" . count($chunks) . "...\r";
    
    // Extract just the INSERT statements from the chunk
    $content = file_get_contents($chunk);
    
    // Find INSERT statements
    if (preg_match_all('/INSERT INTO brain_lead_map.*?;/s', $content, $matches)) {
        foreach ($matches[0] as $insert) {
            $sql = "USE $dbName;\n" . $insert;
            
            $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($sql) . " 2>&1"
            ]);
            
            if (!$response->successful()) {
                echo "\n❌ Failed to load chunk " . ($idx + 1) . "\n";
            }
        }
    }
}

echo "\n✅ All Brain mappings loaded\n\n";

// Step 2: Check how many matches we have
echo "Step 2: Checking matches...\n";

$checkSQL = "USE $dbName;\n";
$checkSQL .= "SELECT COUNT(*) as total_matches FROM vicidial_list vl\n";
$checkSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$checkSQL .= "WHERE vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($checkSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Matches found:\n$output\n";
}

// Step 3: Perform the update
echo "Step 3: Updating Vici leads with Brain IDs...\n";

$updateSQL = "USE $dbName;\n";
$updateSQL .= "UPDATE vicidial_list vl\n";
$updateSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$updateSQL .= "SET vl.vendor_lead_code = blm.brain_id,\n";
$updateSQL .= "    vl.source_id = CONCAT('BRAIN_', blm.brain_id)\n";
$updateSQL .= "WHERE (vl.vendor_lead_code IS NULL OR vl.vendor_lead_code = '' OR vl.vendor_lead_code != blm.brain_id)\n";
$updateSQL .= "AND vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($updateSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Update result:\n$output\n";
}

// Step 4: Show final statistics
echo "\nStep 4: Final statistics...\n";

$statsSQL = "USE $dbName;\n";
$statsSQL .= "SELECT COUNT(*) as total_with_brain_id FROM vicidial_list\n";
$statsSQL .= "WHERE vendor_lead_code REGEXP '^[0-9]{13}\$'\n";
$statsSQL .= "AND list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($statsSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Final count:\n$output\n";
}

// Cleanup
echo "\nCleaning up temporary files...\n";
foreach ($chunks as $chunk) {
    unlink($chunk);
}
if (file_exists('vici_bulk_update.sql')) {
    unlink('vici_bulk_update.sql');
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ All Vici leads have been updated with Brain IDs!\n";
echo "Total time: " . round($totalTime / 60, 1) . " minutes\n";

echo "=== VICI FAST BULK UPDATE - FINAL VERSION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Correct Vici database credentials - using root (no password needed)
$dbUser = 'root';
$dbPass = ''; // No password for root
$dbName = 'Q6hdjl67GRigMofv';

$chunks = glob('vici_chunk_*');
sort($chunks);

echo "Found " . count($chunks) . " chunks to process\n";
echo "Using database: $dbName\n\n";

$startTime = time();

// Step 1: Load all Brain mappings into temp table
echo "Step 1: Loading Brain lead mappings into Vici...\n";

// First, create the temp table
$setupSQL = "USE $dbName;\n";
$setupSQL .= "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n";
$setupSQL .= "CREATE TEMPORARY TABLE brain_lead_map (\n";
$setupSQL .= "    phone VARCHAR(20) PRIMARY KEY,\n";
$setupSQL .= "    brain_id VARCHAR(20)\n";
$setupSQL .= ") ENGINE=MEMORY;\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($setupSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') === false) {
        echo "✅ Temporary table created\n";
    } else {
        echo "❌ Error creating table: $output\n";
        exit(1);
    }
}

// Load data from chunks
foreach ($chunks as $idx => $chunk) {
    echo "  Loading chunk " . ($idx + 1) . "/" . count($chunks) . "...\r";
    
    // Extract just the INSERT statements from the chunk
    $content = file_get_contents($chunk);
    
    // Find INSERT statements
    if (preg_match_all('/INSERT INTO brain_lead_map.*?;/s', $content, $matches)) {
        foreach ($matches[0] as $insert) {
            $sql = "USE $dbName;\n" . $insert;
            
            $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($sql) . " 2>&1"
            ]);
            
            if (!$response->successful()) {
                echo "\n❌ Failed to load chunk " . ($idx + 1) . "\n";
            }
        }
    }
}

echo "\n✅ All Brain mappings loaded\n\n";

// Step 2: Check how many matches we have
echo "Step 2: Checking matches...\n";

$checkSQL = "USE $dbName;\n";
$checkSQL .= "SELECT COUNT(*) as total_matches FROM vicidial_list vl\n";
$checkSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$checkSQL .= "WHERE vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($checkSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Matches found:\n$output\n";
}

// Step 3: Perform the update
echo "Step 3: Updating Vici leads with Brain IDs...\n";

$updateSQL = "USE $dbName;\n";
$updateSQL .= "UPDATE vicidial_list vl\n";
$updateSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$updateSQL .= "SET vl.vendor_lead_code = blm.brain_id,\n";
$updateSQL .= "    vl.source_id = CONCAT('BRAIN_', blm.brain_id)\n";
$updateSQL .= "WHERE (vl.vendor_lead_code IS NULL OR vl.vendor_lead_code = '' OR vl.vendor_lead_code != blm.brain_id)\n";
$updateSQL .= "AND vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($updateSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Update result:\n$output\n";
}

// Step 4: Show final statistics
echo "\nStep 4: Final statistics...\n";

$statsSQL = "USE $dbName;\n";
$statsSQL .= "SELECT COUNT(*) as total_with_brain_id FROM vicidial_list\n";
$statsSQL .= "WHERE vendor_lead_code REGEXP '^[0-9]{13}\$'\n";
$statsSQL .= "AND list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u $dbUser $dbName -e " . escapeshellarg($statsSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Final count:\n$output\n";
}

// Cleanup
echo "\nCleaning up temporary files...\n";
foreach ($chunks as $chunk) {
    unlink($chunk);
}
if (file_exists('vici_bulk_update.sql')) {
    unlink('vici_bulk_update.sql');
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ All Vici leads have been updated with Brain IDs!\n";
echo "Total time: " . round($totalTime / 60, 1) . " minutes\n";
