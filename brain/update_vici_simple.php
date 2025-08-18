<?php
// brain/update_vici_simple.php
// Simple efficient update of Vici leads with Brain IDs

echo "=== VICI UPDATE - SIMPLE & FAST ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "📊 Getting Brain leads with phone numbers...\n";

// Create a temporary file with all updates
$tempFile = tempnam(sys_get_temp_dir(), 'vici_update_');
$handle = fopen($tempFile, 'w');

$count = 0;
Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->whereNotNull('phone')
    ->select('external_lead_id', 'phone')
    ->orderBy('id')
    ->chunk(1000, function($chunk) use ($handle, &$count) {
        foreach ($chunk as $lead) {
            // Clean phone number
            $phone = preg_replace('/\D/', '', $lead->phone);
            if (strlen($phone) >= 10) {
                // Write update statement
                fwrite($handle, sprintf(
                    "UPDATE vicidial_list SET vendor_lead_code = '%s' WHERE phone_number = '%s' AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}$') LIMIT 1;\n",
                    $lead->external_lead_id,
                    $phone
                ));
                $count++;
            }
        }
        echo "  Prepared " . number_format($count) . " updates...\r";
    });

fclose($handle);
echo "\n✅ Prepared " . number_format($count) . " update statements\n\n";

// Read file and execute in batches
echo "📤 Sending updates to Vici...\n";
$startTime = microtime(true);
$totalUpdated = 0;
$batchSize = 100; // Process 100 updates at a time

$lines = file($tempFile);
$totalBatches = ceil(count($lines) / $batchSize);
$currentBatch = 0;

foreach (array_chunk($lines, $batchSize) as $batch) {
    $currentBatch++;
    $batchSql = implode('', $batch);
    
    try {
        // Execute via SSH
        $command = sprintf(
            'sshpass -p "8ZDWGAAQRD" ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p 22 Superman@66.175.219.105 "mysql -u root Q6hdjl67GRigMofv -e %s" 2>&1',
            escapeshellarg($batchSql)
        );
        
        $output = shell_exec($command);
        $totalUpdated += count($batch);
        
        $elapsed = microtime(true) - $startTime;
        $rate = $totalUpdated / $elapsed;
        $progress = ($currentBatch / $totalBatches) * 100;
        
        echo sprintf(
            "  Batch %d/%d | Updated: %s | Rate: %.1f/sec | Progress: %.1f%%\r",
            $currentBatch,
            $totalBatches,
            number_format($totalUpdated),
            $rate,
            $progress
        );
        
        // Small delay to avoid overwhelming
        usleep(50000); // 0.05 seconds
        
    } catch (\Exception $e) {
        echo "\n❌ Error in batch {$currentBatch}: " . $e->getMessage() . "\n";
    }
}

echo "\n\n";

// Clean up temp file
unlink($tempFile);

$endTime = microtime(true);
$totalTime = round($endTime - $startTime);

echo "=== UPDATE COMPLETE ===\n\n";
echo "✅ Updated: " . number_format($totalUpdated) . " records\n";
echo "⏱️ Total time: " . gmdate("H:i:s", $totalTime) . "\n";
echo "📊 Average rate: " . ($totalTime > 0 ? round($totalUpdated / $totalTime, 1) : 0) . " updates/sec\n\n";

// Verify
echo "🔍 Verifying update...\n";
$verifySql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$command = sprintf(
    'sshpass -p "8ZDWGAAQRD" ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p 22 Superman@66.175.219.105 "mysql -u root Q6hdjl67GRigMofv -B -N -e %s" 2>&1',
    escapeshellarg($verifySql)
);
$output = shell_exec($command);
$finalCount = intval(trim($output));

echo "✅ Total Vici leads with Brain IDs: " . number_format($finalCount) . "\n\n";

$kernel->terminate($request, $response);


// brain/update_vici_simple.php
// Simple efficient update of Vici leads with Brain IDs

echo "=== VICI UPDATE - SIMPLE & FAST ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "📊 Getting Brain leads with phone numbers...\n";

// Create a temporary file with all updates
$tempFile = tempnam(sys_get_temp_dir(), 'vici_update_');
$handle = fopen($tempFile, 'w');

$count = 0;
Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->whereNotNull('phone')
    ->select('external_lead_id', 'phone')
    ->orderBy('id')
    ->chunk(1000, function($chunk) use ($handle, &$count) {
        foreach ($chunk as $lead) {
            // Clean phone number
            $phone = preg_replace('/\D/', '', $lead->phone);
            if (strlen($phone) >= 10) {
                // Write update statement
                fwrite($handle, sprintf(
                    "UPDATE vicidial_list SET vendor_lead_code = '%s' WHERE phone_number = '%s' AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}$') LIMIT 1;\n",
                    $lead->external_lead_id,
                    $phone
                ));
                $count++;
            }
        }
        echo "  Prepared " . number_format($count) . " updates...\r";
    });

fclose($handle);
echo "\n✅ Prepared " . number_format($count) . " update statements\n\n";

// Read file and execute in batches
echo "📤 Sending updates to Vici...\n";
$startTime = microtime(true);
$totalUpdated = 0;
$batchSize = 100; // Process 100 updates at a time

$lines = file($tempFile);
$totalBatches = ceil(count($lines) / $batchSize);
$currentBatch = 0;

foreach (array_chunk($lines, $batchSize) as $batch) {
    $currentBatch++;
    $batchSql = implode('', $batch);
    
    try {
        // Execute via SSH
        $command = sprintf(
            'sshpass -p "8ZDWGAAQRD" ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p 22 Superman@66.175.219.105 "mysql -u root Q6hdjl67GRigMofv -e %s" 2>&1',
            escapeshellarg($batchSql)
        );
        
        $output = shell_exec($command);
        $totalUpdated += count($batch);
        
        $elapsed = microtime(true) - $startTime;
        $rate = $totalUpdated / $elapsed;
        $progress = ($currentBatch / $totalBatches) * 100;
        
        echo sprintf(
            "  Batch %d/%d | Updated: %s | Rate: %.1f/sec | Progress: %.1f%%\r",
            $currentBatch,
            $totalBatches,
            number_format($totalUpdated),
            $rate,
            $progress
        );
        
        // Small delay to avoid overwhelming
        usleep(50000); // 0.05 seconds
        
    } catch (\Exception $e) {
        echo "\n❌ Error in batch {$currentBatch}: " . $e->getMessage() . "\n";
    }
}

echo "\n\n";

// Clean up temp file
unlink($tempFile);

$endTime = microtime(true);
$totalTime = round($endTime - $startTime);

echo "=== UPDATE COMPLETE ===\n\n";
echo "✅ Updated: " . number_format($totalUpdated) . " records\n";
echo "⏱️ Total time: " . gmdate("H:i:s", $totalTime) . "\n";
echo "📊 Average rate: " . ($totalTime > 0 ? round($totalUpdated / $totalTime, 1) : 0) . " updates/sec\n\n";

// Verify
echo "🔍 Verifying update...\n";
$verifySql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$command = sprintf(
    'sshpass -p "8ZDWGAAQRD" ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p 22 Superman@66.175.219.105 "mysql -u root Q6hdjl67GRigMofv -B -N -e %s" 2>&1',
    escapeshellarg($verifySql)
);
$output = shell_exec($command);
$finalCount = intval(trim($output));

echo "✅ Total Vici leads with Brain IDs: " . number_format($finalCount) . "\n\n";

$kernel->terminate($request, $response);






