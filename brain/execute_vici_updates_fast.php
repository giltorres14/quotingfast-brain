<?php

echo "=== EXECUTING VICI UPDATES - OPTIMIZED ===\n\n";

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
echo "Found $totalFiles SQL files to process\n";
echo "Total updates: 80,942\n\n";

$startTime = time();
$successCount = 0;
$failCount = 0;
$updatedRows = 0;

// Process each file
foreach ($sqlFiles as $idx => $file) {
    $fileNum = $idx + 1;
    echo "[$fileNum/$totalFiles] Processing " . basename($file) . "... ";
    
    // Read the SQL content
    $sqlContent = file_get_contents($file);
    
    // Execute via proxy using root user (no password)
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($sqlContent) . " 2>&1 | grep -E 'Query OK|ERROR' | head -1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        
        // Check for success
        if (strpos($output, 'Query OK') !== false) {
            // Extract number of rows affected
            if (preg_match('/(\d+) row/', $output, $matches)) {
                $rows = intval($matches[1]);
                $updatedRows += $rows;
                echo "✅ Updated $rows rows\n";
            } else {
                echo "✅ Done\n";
            }
            $successCount++;
        } elseif (strpos($output, 'ERROR') !== false) {
            echo "❌ Error: " . substr($output, 0, 100) . "\n";
            $failCount++;
        } else {
            echo "✅ Processed\n";
            $successCount++;
        }
    } else {
        echo "❌ Failed to execute\n";
        $failCount++;
    }
    
    // Progress indicator every 10 files
    if ($fileNum % 10 == 0) {
        $elapsed = time() - $startTime;
        $rate = $fileNum / max(1, $elapsed);
        $remaining = ($totalFiles - $fileNum) / max(0.1, $rate);
        echo "  Progress: " . round($fileNum / $totalFiles * 100, 1) . "% | ";
        echo "Time elapsed: " . round($elapsed / 60, 1) . " min | ";
        echo "Est. remaining: " . round($remaining / 60, 1) . " min\n";
    }
    
    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second
}

// Final check - count how many leads now have Brain IDs
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total_with_brain_id FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Final count of Vici leads with Brain IDs:\n$output\n";
}

// Cleanup - move processed files to archive
if (!file_exists('processed_sql')) {
    mkdir('processed_sql');
}

foreach ($sqlFiles as $file) {
    rename($file, 'processed_sql/' . basename($file));
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Successfully processed: $successCount files\n";
if ($failCount > 0) {
    echo "❌ Failed: $failCount files\n";
}
echo "📊 Total rows updated: $updatedRows\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";
echo "\nAll SQL files have been moved to processed_sql/ directory\n";



echo "=== EXECUTING VICI UPDATES - OPTIMIZED ===\n\n";

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
echo "Found $totalFiles SQL files to process\n";
echo "Total updates: 80,942\n\n";

$startTime = time();
$successCount = 0;
$failCount = 0;
$updatedRows = 0;

// Process each file
foreach ($sqlFiles as $idx => $file) {
    $fileNum = $idx + 1;
    echo "[$fileNum/$totalFiles] Processing " . basename($file) . "... ";
    
    // Read the SQL content
    $sqlContent = file_get_contents($file);
    
    // Execute via proxy using root user (no password)
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($sqlContent) . " 2>&1 | grep -E 'Query OK|ERROR' | head -1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        
        // Check for success
        if (strpos($output, 'Query OK') !== false) {
            // Extract number of rows affected
            if (preg_match('/(\d+) row/', $output, $matches)) {
                $rows = intval($matches[1]);
                $updatedRows += $rows;
                echo "✅ Updated $rows rows\n";
            } else {
                echo "✅ Done\n";
            }
            $successCount++;
        } elseif (strpos($output, 'ERROR') !== false) {
            echo "❌ Error: " . substr($output, 0, 100) . "\n";
            $failCount++;
        } else {
            echo "✅ Processed\n";
            $successCount++;
        }
    } else {
        echo "❌ Failed to execute\n";
        $failCount++;
    }
    
    // Progress indicator every 10 files
    if ($fileNum % 10 == 0) {
        $elapsed = time() - $startTime;
        $rate = $fileNum / max(1, $elapsed);
        $remaining = ($totalFiles - $fileNum) / max(0.1, $rate);
        echo "  Progress: " . round($fileNum / $totalFiles * 100, 1) . "% | ";
        echo "Time elapsed: " . round($elapsed / 60, 1) . " min | ";
        echo "Est. remaining: " . round($remaining / 60, 1) . " min\n";
    }
    
    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second
}

// Final check - count how many leads now have Brain IDs
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total_with_brain_id FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Final count of Vici leads with Brain IDs:\n$output\n";
}

// Cleanup - move processed files to archive
if (!file_exists('processed_sql')) {
    mkdir('processed_sql');
}

foreach ($sqlFiles as $file) {
    rename($file, 'processed_sql/' . basename($file));
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Successfully processed: $successCount files\n";
if ($failCount > 0) {
    echo "❌ Failed: $failCount files\n";
}
echo "📊 Total rows updated: $updatedRows\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";
echo "\nAll SQL files have been moved to processed_sql/ directory\n";






