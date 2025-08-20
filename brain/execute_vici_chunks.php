<?php

echo "=== EXECUTING VICI UPDATE CHUNKS ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$chunks = glob('vici_chunk_*');
sort($chunks);

echo "Found " . count($chunks) . " chunks to process\n\n";

$totalUpdated = 0;
$startTime = time();

// First chunk needs special handling - it creates the temp table
echo "Step 1: Creating temporary table and initial data...\n";
$firstChunk = array_shift($chunks);

// Read and modify first chunk to ensure it has proper setup
$content = file_get_contents($firstChunk);

// Make sure it starts with USE asterisk and creates temp table
$setupSQL = "USE asterisk;\n";
$setupSQL .= "SET autocommit=0;\n";
$setupSQL .= "START TRANSACTION;\n\n";
$setupSQL .= "CREATE TEMPORARY TABLE IF NOT EXISTS brain_lead_map (\n";
$setupSQL .= "    phone VARCHAR(20) PRIMARY KEY,\n";
$setupSQL .= "    brain_id VARCHAR(20)\n";
$setupSQL .= ") ENGINE=MEMORY;\n\n";
$setupSQL .= $content;

// Execute first chunk
echo "Processing " . basename($firstChunk) . "...\n";
$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $setupSQL . "\nEOF 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') !== false) {
        echo "❌ Error: " . substr($output, 0, 500) . "\n";
        
        // Try with Superman user
        echo "Trying with Superman user...\n";
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $setupSQL . "\nEOF 2>&1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            echo "Result: " . substr($output, 0, 200) . "\n";
        }
    } else {
        echo "✅ Temporary table created\n";
    }
} else {
    echo "❌ Failed to execute first chunk\n";
    exit(1);
}

// Process remaining chunks
echo "\nStep 2: Loading Brain lead mappings...\n";
foreach ($chunks as $idx => $chunk) {
    echo "Processing " . basename($chunk) . " (" . ($idx + 2) . "/" . (count($chunks) + 1) . ")...\r";
    
    $content = file_get_contents($chunk);
    
    // Wrap in transaction context
    $chunkSQL = "USE asterisk;\n" . $content;
    
    $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $chunkSQL . "\nEOF 2>&1"
    ]);
    
    if (!$response->successful() || strpos($response->json()['output'] ?? '', 'ERROR') !== false) {
        // Try Superman user
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $chunkSQL . "\nEOF 2>&1"
        ]);
    }
    
    // Small delay between chunks
    usleep(100000); // 0.1 second
}

echo "\n✅ All mappings loaded\n\n";

// Step 3: Execute the actual update
echo "Step 3: Performing the update...\n";

$updateSQL = "USE asterisk;\n\n";
$updateSQL .= "-- Count matches before update\n";
$updateSQL .= "SELECT COUNT(*) as 'Leads to Update' FROM vicidial_list vl\n";
$updateSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$updateSQL .= "WHERE (vl.vendor_lead_code IS NULL OR vl.vendor_lead_code = '' OR vl.vendor_lead_code != blm.brain_id)\n";
$updateSQL .= "AND vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n";

$updateSQL .= "-- Perform update\n";
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
)) . ");\n\n";

$updateSQL .= "SELECT ROW_COUNT() as 'Leads Updated';\n\n";
$updateSQL .= "COMMIT;\n\n";
$updateSQL .= "-- Final count\n";
$updateSQL .= "SELECT COUNT(*) as 'Total with Brain ID' FROM vicidial_list\n";
$updateSQL .= "WHERE vendor_lead_code REGEXP '^[0-9]{13}$'\n";
$updateSQL .= "AND list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n";

$updateSQL .= "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n";

// Execute update
$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $updateSQL . "\nEOF 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') !== false) {
        // Try Superman user
        echo "Trying update with Superman user...\n";
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $updateSQL . "\nEOF 2>&1"
        ]);
        $output = $response->json()['output'] ?? '';
    }
    
    echo "\nResults:\n";
    echo $output . "\n";
} else {
    echo "❌ Failed to execute update\n";
}

// Cleanup
foreach (glob('vici_chunk_*') as $chunk) {
    unlink($chunk);
}
unlink('vici_bulk_update.sql');

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "Total time: " . round($totalTime / 60, 1) . " minutes\n";



echo "=== EXECUTING VICI UPDATE CHUNKS ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$chunks = glob('vici_chunk_*');
sort($chunks);

echo "Found " . count($chunks) . " chunks to process\n\n";

$totalUpdated = 0;
$startTime = time();

// First chunk needs special handling - it creates the temp table
echo "Step 1: Creating temporary table and initial data...\n";
$firstChunk = array_shift($chunks);

// Read and modify first chunk to ensure it has proper setup
$content = file_get_contents($firstChunk);

// Make sure it starts with USE asterisk and creates temp table
$setupSQL = "USE asterisk;\n";
$setupSQL .= "SET autocommit=0;\n";
$setupSQL .= "START TRANSACTION;\n\n";
$setupSQL .= "CREATE TEMPORARY TABLE IF NOT EXISTS brain_lead_map (\n";
$setupSQL .= "    phone VARCHAR(20) PRIMARY KEY,\n";
$setupSQL .= "    brain_id VARCHAR(20)\n";
$setupSQL .= ") ENGINE=MEMORY;\n\n";
$setupSQL .= $content;

// Execute first chunk
echo "Processing " . basename($firstChunk) . "...\n";
$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $setupSQL . "\nEOF 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') !== false) {
        echo "❌ Error: " . substr($output, 0, 500) . "\n";
        
        // Try with Superman user
        echo "Trying with Superman user...\n";
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $setupSQL . "\nEOF 2>&1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            echo "Result: " . substr($output, 0, 200) . "\n";
        }
    } else {
        echo "✅ Temporary table created\n";
    }
} else {
    echo "❌ Failed to execute first chunk\n";
    exit(1);
}

// Process remaining chunks
echo "\nStep 2: Loading Brain lead mappings...\n";
foreach ($chunks as $idx => $chunk) {
    echo "Processing " . basename($chunk) . " (" . ($idx + 2) . "/" . (count($chunks) + 1) . ")...\r";
    
    $content = file_get_contents($chunk);
    
    // Wrap in transaction context
    $chunkSQL = "USE asterisk;\n" . $content;
    
    $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $chunkSQL . "\nEOF 2>&1"
    ]);
    
    if (!$response->successful() || strpos($response->json()['output'] ?? '', 'ERROR') !== false) {
        // Try Superman user
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $chunkSQL . "\nEOF 2>&1"
        ]);
    }
    
    // Small delay between chunks
    usleep(100000); // 0.1 second
}

echo "\n✅ All mappings loaded\n\n";

// Step 3: Execute the actual update
echo "Step 3: Performing the update...\n";

$updateSQL = "USE asterisk;\n\n";
$updateSQL .= "-- Count matches before update\n";
$updateSQL .= "SELECT COUNT(*) as 'Leads to Update' FROM vicidial_list vl\n";
$updateSQL .= "INNER JOIN brain_lead_map blm ON vl.phone_number = blm.phone\n";
$updateSQL .= "WHERE (vl.vendor_lead_code IS NULL OR vl.vendor_lead_code = '' OR vl.vendor_lead_code != blm.brain_id)\n";
$updateSQL .= "AND vl.list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n";

$updateSQL .= "-- Perform update\n";
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
)) . ");\n\n";

$updateSQL .= "SELECT ROW_COUNT() as 'Leads Updated';\n\n";
$updateSQL .= "COMMIT;\n\n";
$updateSQL .= "-- Final count\n";
$updateSQL .= "SELECT COUNT(*) as 'Total with Brain ID' FROM vicidial_list\n";
$updateSQL .= "WHERE vendor_lead_code REGEXP '^[0-9]{13}$'\n";
$updateSQL .= "AND list_id IN (" . implode(',', array_merge(
    [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
     8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
     10006, 10007, 10008, 10009, 10010, 10011,
     6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
)) . ");\n\n";

$updateSQL .= "DROP TEMPORARY TABLE IF EXISTS brain_lead_map;\n";

// Execute update
$response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u cron -p1234 asterisk << 'EOF'\n" . $updateSQL . "\nEOF 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') !== false) {
        // Try Superman user
        echo "Trying update with Superman user...\n";
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u Superman -p8ZDWGAAQRD asterisk << 'EOF'\n" . $updateSQL . "\nEOF 2>&1"
        ]);
        $output = $response->json()['output'] ?? '';
    }
    
    echo "\nResults:\n";
    echo $output . "\n";
} else {
    echo "❌ Failed to execute update\n";
}

// Cleanup
foreach (glob('vici_chunk_*') as $chunk) {
    unlink($chunk);
}
unlink('vici_bulk_update.sql');

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "Total time: " . round($totalTime / 60, 1) . " minutes\n";








