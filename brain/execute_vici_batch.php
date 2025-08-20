<?php

echo "=== VICI BATCH UPDATE - DIRECT EXECUTION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$sqlFiles = glob('vici_direct_*.sql');
sort($sqlFiles);

$totalFiles = count($sqlFiles);
echo "Found $totalFiles SQL files to process\n\n";

$startTime = time();
$successCount = 0;
$totalUpdated = 0;

// Process files in smaller batches
foreach ($sqlFiles as $idx => $file) {
    $fileNum = $idx + 1;
    echo "[$fileNum/$totalFiles] Processing " . basename($file) . "... ";
    
    // Read the SQL file and get individual UPDATE statements
    $content = file_get_contents($file);
    
    // Extract just the UPDATE statements (skip USE and SET commands)
    preg_match_all('/UPDATE vicidial_list.*?;/s', $content, $matches);
    $updates = $matches[0];
    
    if (empty($updates)) {
        echo "❌ No updates found\n";
        continue;
    }
    
    // Process updates in smaller batches (10 at a time)
    $batchSize = 10;
    $batches = array_chunk($updates, $batchSize);
    $fileUpdated = 0;
    
    foreach ($batches as $batchIdx => $batch) {
        // Combine batch into single command
        $batchSQL = "USE Q6hdjl67GRigMofv;\n" . implode("\n", $batch);
        
        // Execute batch
        $response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -e " . escapeshellarg($batchSQL) . " 2>&1 | tail -1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            // Count successful updates (each UPDATE affects 0 or more rows)
            $fileUpdated += count($batch);
        }
    }
    
    echo "✅ Processed $fileUpdated updates\n";
    $totalUpdated += $fileUpdated;
    $successCount++;
    
    // Progress update
    if ($fileNum % 5 == 0) {
        $elapsed = time() - $startTime;
        $rate = $fileNum / max(1, $elapsed);
        $remaining = ($totalFiles - $fileNum) / max(0.1, $rate);
        echo "  Progress: " . round($fileNum / $totalFiles * 100, 1) . "% | ";
        echo "Time: " . round($elapsed / 60, 1) . " min | ";
        echo "Remaining: " . round($remaining / 60, 1) . " min | ";
        echo "Updates: $totalUpdated\n";
    }
}

// Verify results
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1 | grep -v 'Could not' | grep -v 'Failed to'"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Vici leads with Brain IDs:\n$output\n";
}

// Move processed files
if (!file_exists('processed_sql')) {
    mkdir('processed_sql');
}
foreach ($sqlFiles as $file) {
    rename($file, 'processed_sql/' . basename($file));
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Processed $successCount files with $totalUpdated update statements\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";
echo "\nEstimated updates per minute: " . round($totalUpdated / max(1, $totalTime / 60)) . "\n";



echo "=== VICI BATCH UPDATE - DIRECT EXECUTION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$sqlFiles = glob('vici_direct_*.sql');
sort($sqlFiles);

$totalFiles = count($sqlFiles);
echo "Found $totalFiles SQL files to process\n\n";

$startTime = time();
$successCount = 0;
$totalUpdated = 0;

// Process files in smaller batches
foreach ($sqlFiles as $idx => $file) {
    $fileNum = $idx + 1;
    echo "[$fileNum/$totalFiles] Processing " . basename($file) . "... ";
    
    // Read the SQL file and get individual UPDATE statements
    $content = file_get_contents($file);
    
    // Extract just the UPDATE statements (skip USE and SET commands)
    preg_match_all('/UPDATE vicidial_list.*?;/s', $content, $matches);
    $updates = $matches[0];
    
    if (empty($updates)) {
        echo "❌ No updates found\n";
        continue;
    }
    
    // Process updates in smaller batches (10 at a time)
    $batchSize = 10;
    $batches = array_chunk($updates, $batchSize);
    $fileUpdated = 0;
    
    foreach ($batches as $batchIdx => $batch) {
        // Combine batch into single command
        $batchSQL = "USE Q6hdjl67GRigMofv;\n" . implode("\n", $batch);
        
        // Execute batch
        $response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -e " . escapeshellarg($batchSQL) . " 2>&1 | tail -1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            // Count successful updates (each UPDATE affects 0 or more rows)
            $fileUpdated += count($batch);
        }
    }
    
    echo "✅ Processed $fileUpdated updates\n";
    $totalUpdated += $fileUpdated;
    $successCount++;
    
    // Progress update
    if ($fileNum % 5 == 0) {
        $elapsed = time() - $startTime;
        $rate = $fileNum / max(1, $elapsed);
        $remaining = ($totalFiles - $fileNum) / max(0.1, $rate);
        echo "  Progress: " . round($fileNum / $totalFiles * 100, 1) . "% | ";
        echo "Time: " . round($elapsed / 60, 1) . " min | ";
        echo "Remaining: " . round($remaining / 60, 1) . " min | ";
        echo "Updates: $totalUpdated\n";
    }
}

// Verify results
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1 | grep -v 'Could not' | grep -v 'Failed to'"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Vici leads with Brain IDs:\n$output\n";
}

// Move processed files
if (!file_exists('processed_sql')) {
    mkdir('processed_sql');
}
foreach ($sqlFiles as $file) {
    rename($file, 'processed_sql/' . basename($file));
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Processed $successCount files with $totalUpdated update statements\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";
echo "\nEstimated updates per minute: " . round($totalUpdated / max(1, $totalTime / 60)) . "\n";








