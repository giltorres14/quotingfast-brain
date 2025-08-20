<?php

echo "=== EXECUTING FINAL VICI UPDATE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$startTime = time();

// Read the SQL file
$sqlContent = file_get_contents('vici_single_update.sql');

// Split by UPDATE statements
$parts = preg_split('/^(UPDATE vicidial_list)/m', $sqlContent, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

// Reconstruct UPDATE statements
$updates = [];
$currentUpdate = '';
for ($i = 0; $i < count($parts); $i++) {
    if ($parts[$i] === 'UPDATE vicidial_list' && isset($parts[$i+1])) {
        if ($currentUpdate) {
            $updates[] = $currentUpdate;
        }
        $currentUpdate = 'UPDATE vicidial_list' . $parts[$i+1];
        $i++;
    }
}
if ($currentUpdate) {
    $updates[] = $currentUpdate;
}

echo "Found " . count($updates) . " UPDATE statements to execute\n\n";

$successCount = 0;
$failCount = 0;
$totalRowsUpdated = 0;

// Execute each UPDATE
foreach ($updates as $idx => $update) {
    $num = $idx + 1;
    echo "[$num/" . count($updates) . "] Executing update chunk $num... ";
    
    // Add USE statement
    $sql = "USE Q6hdjl67GRigMofv;\n" . trim($update);
    
    // Execute via proxy
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root -e " . escapeshellarg($sql) . " 2>&1 | grep -E 'Query OK|ERROR|rows affected' | head -1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        
        if (strpos($output, 'Query OK') !== false) {
            // Extract rows affected
            if (preg_match('/(\d+) row/', $output, $matches)) {
                $rows = intval($matches[1]);
                $totalRowsUpdated += $rows;
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
    
    // Progress indicator
    if ($num % 10 == 0) {
        $elapsed = time() - $startTime;
        $rate = $num / max(1, $elapsed);
        $remaining = (count($updates) - $num) / max(0.1, $rate);
        echo "  Progress: " . round($num / count($updates) * 100) . "% | ";
        echo "Time: " . round($elapsed / 60, 1) . " min | ";
        echo "Remaining: " . round($remaining / 60, 1) . " min | ";
        echo "Rows updated: $totalRowsUpdated\n";
    }
    
    // Small delay between requests
    usleep(200000); // 0.2 seconds
}

// Final verification
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1 | grep -v 'Could not' | grep -v 'Failed to'"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Vici leads with Brain IDs:\n$output\n";
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Successfully executed: $successCount updates\n";
if ($failCount > 0) {
    echo "❌ Failed: $failCount updates\n";
}
echo "📊 Total rows updated: $totalRowsUpdated\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";

// Cleanup
if (file_exists('vici_single_update.sql')) {
    rename('vici_single_update.sql', 'processed_sql/vici_single_update_' . date('Ymd_His') . '.sql');
}

// Clean up old files
foreach (glob('vici_direct_*.sql') as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
foreach (glob('vici_chunk_*') as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

echo "\nAll temporary files have been cleaned up.\n";



echo "=== EXECUTING FINAL VICI UPDATE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$startTime = time();

// Read the SQL file
$sqlContent = file_get_contents('vici_single_update.sql');

// Split by UPDATE statements
$parts = preg_split('/^(UPDATE vicidial_list)/m', $sqlContent, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

// Reconstruct UPDATE statements
$updates = [];
$currentUpdate = '';
for ($i = 0; $i < count($parts); $i++) {
    if ($parts[$i] === 'UPDATE vicidial_list' && isset($parts[$i+1])) {
        if ($currentUpdate) {
            $updates[] = $currentUpdate;
        }
        $currentUpdate = 'UPDATE vicidial_list' . $parts[$i+1];
        $i++;
    }
}
if ($currentUpdate) {
    $updates[] = $currentUpdate;
}

echo "Found " . count($updates) . " UPDATE statements to execute\n\n";

$successCount = 0;
$failCount = 0;
$totalRowsUpdated = 0;

// Execute each UPDATE
foreach ($updates as $idx => $update) {
    $num = $idx + 1;
    echo "[$num/" . count($updates) . "] Executing update chunk $num... ";
    
    // Add USE statement
    $sql = "USE Q6hdjl67GRigMofv;\n" . trim($update);
    
    // Execute via proxy
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root -e " . escapeshellarg($sql) . " 2>&1 | grep -E 'Query OK|ERROR|rows affected' | head -1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        
        if (strpos($output, 'Query OK') !== false) {
            // Extract rows affected
            if (preg_match('/(\d+) row/', $output, $matches)) {
                $rows = intval($matches[1]);
                $totalRowsUpdated += $rows;
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
    
    // Progress indicator
    if ($num % 10 == 0) {
        $elapsed = time() - $startTime;
        $rate = $num / max(1, $elapsed);
        $remaining = (count($updates) - $num) / max(0.1, $rate);
        echo "  Progress: " . round($num / count($updates) * 100) . "% | ";
        echo "Time: " . round($elapsed / 60, 1) . " min | ";
        echo "Remaining: " . round($remaining / 60, 1) . " min | ";
        echo "Rows updated: $totalRowsUpdated\n";
    }
    
    // Small delay between requests
    usleep(200000); // 0.2 seconds
}

// Final verification
echo "\n=== VERIFYING RESULTS ===\n";

$checkSQL = "SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (" . 
    implode(',', array_merge(
        [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
         8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
         10006, 10007, 10008, 10009, 10010, 10011,
         6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020]
    )) . ");";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1 | grep -v 'Could not' | grep -v 'Failed to'"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Vici leads with Brain IDs:\n$output\n";
}

$totalTime = time() - $startTime;
echo "\n=== COMPLETE ===\n";
echo "✅ Successfully executed: $successCount updates\n";
if ($failCount > 0) {
    echo "❌ Failed: $failCount updates\n";
}
echo "📊 Total rows updated: $totalRowsUpdated\n";
echo "⏱️  Total time: " . round($totalTime / 60, 1) . " minutes\n";

// Cleanup
if (file_exists('vici_single_update.sql')) {
    rename('vici_single_update.sql', 'processed_sql/vici_single_update_' . date('Ymd_His') . '.sql');
}

// Clean up old files
foreach (glob('vici_direct_*.sql') as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
foreach (glob('vici_chunk_*') as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

echo "\nAll temporary files have been cleaned up.\n";








