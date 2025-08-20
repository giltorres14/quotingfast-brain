<?php
// brain/update_all_vici_leads.php
// Complete update of ALL Vici leads with Brain IDs

echo "=== COMPLETE VICI UPDATE WITH BRAIN IDs ===\n\n";

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

// First, let's see how many leads need updating
echo "📊 Analyzing current state...\n";

// Count Brain leads with valid external_lead_id
$brainLeadCount = Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->count();

echo "✅ Found " . number_format($brainLeadCount) . " Brain leads with valid IDs\n\n";

// Check how many are already in Vici
$checkSql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkSql) . " 2>&1"
]);
$output = $response->json()['output'] ?? '';
$output = preg_replace('/Could not create.*\n|Failed to add.*\n/', '', $output);
$alreadyUpdated = intval(trim($output));

echo "📈 Already updated in Vici: " . number_format($alreadyUpdated) . "\n";
echo "📝 Need to update: " . number_format($brainLeadCount - $alreadyUpdated) . "\n\n";

if ($brainLeadCount - $alreadyUpdated <= 0) {
    echo "✅ All leads are already updated!\n";
    exit(0);
}

echo "Starting update process...\n\n";

$totalUpdated = 0;
$totalFailed = 0;
$batchSize = 500;
$startTime = microtime(true);

// Process in batches using chunking
Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->select('id', 'external_lead_id', 'phone')
    ->orderBy('id')
    ->chunk($batchSize, function($chunk) use (&$totalUpdated, &$totalFailed, $startTime, $brainLeadCount) {
    $updates = [];
    
    foreach ($chunk as $lead) {
        // Build UPDATE statement for this lead
        $updates[] = sprintf(
            "UPDATE vicidial_list SET vendor_lead_code = '%s' WHERE phone_number = '%s' AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}$') LIMIT 1",
            $lead->external_lead_id,
            $lead->phone
        );
    }
    
    if (empty($updates)) return;
    
    // Join all updates with semicolons
    $batchSql = implode('; ', $updates);
    
    try {
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($batchSql) . " 2>&1"
        ]);
        
        if ($response->successful()) {
            $totalUpdated += count($updates);
            $elapsed = microtime(true) - $startTime;
            $rate = $totalUpdated / $elapsed;
            
            echo sprintf(
                "✅ Batch complete | Updated: %s | Rate: %.1f/sec | Progress: %.1f%%\n",
                number_format($totalUpdated),
                $rate,
                ($totalUpdated / $brainLeadCount) * 100
            );
        } else {
            $totalFailed += count($updates);
            echo "❌ Batch failed: " . ($response->json()['error'] ?? 'Unknown error') . "\n";
        }
    } catch (\Exception $e) {
        $totalFailed += count($updates);
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second
});

$endTime = microtime(true);
$totalTime = round($endTime - $startTime);

echo "\n=== UPDATE COMPLETE ===\n\n";
echo "✅ Successfully updated: " . number_format($totalUpdated) . " leads\n";
echo "❌ Failed: " . number_format($totalFailed) . " leads\n";
echo "⏱️ Total time: " . gmdate("H:i:s", $totalTime) . "\n";
echo "📊 Average rate: " . ($totalTime > 0 ? round($totalUpdated / $totalTime, 1) : 0) . " leads/sec\n\n";

// Verify the update
echo "🔍 Verifying update...\n";
$verifySql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($verifySql) . " 2>&1"
]);
$output = $response->json()['output'] ?? '';
$output = preg_replace('/Could not create.*\n|Failed to add.*\n/', '', $output);
$finalCount = intval(trim($output));

echo "✅ Total Vici leads with Brain IDs: " . number_format($finalCount) . "\n\n";

$kernel->terminate($request, $response);

// Complete update of ALL Vici leads with Brain IDs

echo "=== COMPLETE VICI UPDATE WITH BRAIN IDs ===\n\n";

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

// First, let's see how many leads need updating
echo "📊 Analyzing current state...\n";

// Count Brain leads with valid external_lead_id
$brainLeadCount = Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->count();

echo "✅ Found " . number_format($brainLeadCount) . " Brain leads with valid IDs\n\n";

// Check how many are already in Vici
$checkSql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkSql) . " 2>&1"
]);
$output = $response->json()['output'] ?? '';
$output = preg_replace('/Could not create.*\n|Failed to add.*\n/', '', $output);
$alreadyUpdated = intval(trim($output));

echo "📈 Already updated in Vici: " . number_format($alreadyUpdated) . "\n";
echo "📝 Need to update: " . number_format($brainLeadCount - $alreadyUpdated) . "\n\n";

if ($brainLeadCount - $alreadyUpdated <= 0) {
    echo "✅ All leads are already updated!\n";
    exit(0);
}

echo "Starting update process...\n\n";

$totalUpdated = 0;
$totalFailed = 0;
$batchSize = 500;
$startTime = microtime(true);

// Process in batches using chunking
Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->select('id', 'external_lead_id', 'phone')
    ->orderBy('id')
    ->chunk($batchSize, function($chunk) use (&$totalUpdated, &$totalFailed, $startTime, $brainLeadCount) {
    $updates = [];
    
    foreach ($chunk as $lead) {
        // Build UPDATE statement for this lead
        $updates[] = sprintf(
            "UPDATE vicidial_list SET vendor_lead_code = '%s' WHERE phone_number = '%s' AND (vendor_lead_code IS NULL OR vendor_lead_code = '' OR vendor_lead_code NOT REGEXP '^[0-9]{13}$') LIMIT 1",
            $lead->external_lead_id,
            $lead->phone
        );
    }
    
    if (empty($updates)) return;
    
    // Join all updates with semicolons
    $batchSql = implode('; ', $updates);
    
    try {
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($batchSql) . " 2>&1"
        ]);
        
        if ($response->successful()) {
            $totalUpdated += count($updates);
            $elapsed = microtime(true) - $startTime;
            $rate = $totalUpdated / $elapsed;
            
            echo sprintf(
                "✅ Batch complete | Updated: %s | Rate: %.1f/sec | Progress: %.1f%%\n",
                number_format($totalUpdated),
                $rate,
                ($totalUpdated / $brainLeadCount) * 100
            );
        } else {
            $totalFailed += count($updates);
            echo "❌ Batch failed: " . ($response->json()['error'] ?? 'Unknown error') . "\n";
        }
    } catch (\Exception $e) {
        $totalFailed += count($updates);
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second
});

$endTime = microtime(true);
$totalTime = round($endTime - $startTime);

echo "\n=== UPDATE COMPLETE ===\n\n";
echo "✅ Successfully updated: " . number_format($totalUpdated) . " leads\n";
echo "❌ Failed: " . number_format($totalFailed) . " leads\n";
echo "⏱️ Total time: " . gmdate("H:i:s", $totalTime) . "\n";
echo "📊 Average rate: " . ($totalTime > 0 ? round($totalUpdated / $totalTime, 1) : 0) . " leads/sec\n\n";

// Verify the update
echo "🔍 Verifying update...\n";
$verifySql = "SELECT COUNT(*) FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$'";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($verifySql) . " 2>&1"
]);
$output = $response->json()['output'] ?? '';
$output = preg_replace('/Could not create.*\n|Failed to add.*\n/', '', $output);
$finalCount = intval(trim($output));

echo "✅ Total Vici leads with Brain IDs: " . number_format($finalCount) . "\n\n";

$kernel->terminate($request, $response);


