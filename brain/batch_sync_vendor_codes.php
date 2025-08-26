<?php
/**
 * Batch update vendor_lead_codes in Vici to match Brain IDs
 * This ensures iframe displays correct lead when agent gets a call
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== BATCH VENDOR CODE SYNC ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Process in chunks to avoid memory issues
$batchSize = 1000;
$offset = 0;
$totalUpdated = 0;

// First, get Brain leads that should be in Vici
echo "Processing Brain leads to update Vici vendor_lead_codes...\n\n";

while (true) {
    // Get batch of Brain leads
    $leads = DB::table('leads')
        ->select('id', 'phone', 'vici_list_id')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->orderBy('id')
        ->offset($offset)
        ->limit($batchSize)
        ->get();
    
    if ($leads->isEmpty()) {
        break;
    }
    
    echo "Batch " . (($offset / $batchSize) + 1) . ": Processing " . count($leads) . " leads...\n";
    
    // Build batch update query for Vici
    $updates = [];
    foreach ($leads as $lead) {
        $phone = preg_replace('/\D/', '', $lead->phone);
        if (strlen($phone) == 10) {
            $phone = '1' . $phone;
        }
        
        // Update vendor_lead_code where phone matches
        $updates[] = "UPDATE vicidial_list SET vendor_lead_code = '{$lead->id}' 
                     WHERE phone_number = '{$phone}' 
                     AND list_id NOT IN (199, 87878787)
                     AND (vendor_lead_code IS NULL OR vendor_lead_code = '');";
    }
    
    if (!empty($updates)) {
        // Execute batch update (process 50 at a time to avoid timeout)
        $chunks = array_chunk($updates, 50);
        foreach ($chunks as $chunk) {
            $sql = implode("\n", $chunk);
            
            $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -e " . escapeshellarg($sql)
            ]);
            
            if ($response->successful()) {
                $output = json_decode($response->body(), true)['output'] ?? '';
                // Count affected rows (rough estimate)
                $affected = substr_count($output, 'Query OK');
                $totalUpdated += $affected * 50; // Estimate
                echo "  Updated batch of " . count($chunk) . " queries\n";
            }
        }
    }
    
    $offset += $batchSize;
    
    // Progress indicator
    if ($offset % 10000 == 0) {
        echo "  Progress: Processed $offset Brain leads, estimated $totalUpdated Vici updates\n";
    }
}

echo "\n=== SYNC COMPLETE ===\n";
echo "Processed " . $offset . " Brain leads\n";
echo "Estimated Vici updates: ~$totalUpdated\n\n";

// Verify the update
echo "Verifying vendor_lead_code status in Vici...\n";

$query = "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN vendor_lead_code IS NOT NULL AND vendor_lead_code != '' THEN 1 END) as has_code,
        COUNT(CASE WHEN vendor_lead_code REGEXP '^[0-9]+$' THEN 1 END) as numeric_code
    FROM vicidial_list
    WHERE list_id IN (SELECT DISTINCT list_id FROM vicidial_lists WHERE campaign_id = 'Autodial')
    AND list_id NOT IN (199, 87878787)
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($query)
]);

if ($response->successful()) {
    $output = json_decode($response->body(), true)['output'] ?? '';
    $lines = explode("\n", $output);
    
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos($line, 'Could') !== false) continue;
        
        $data = explode("\t", $line);
        if (count($data) >= 3) {
            echo "\nFinal Vici Status:\n";
            echo "  Total active leads: " . number_format($data[0]) . "\n";
            echo "  With vendor_lead_code: " . number_format($data[1]) . "\n";
            echo "  With numeric code (Brain IDs): " . number_format($data[2]) . "\n";
            
            $percentage = round(($data[1] / $data[0]) * 100, 1);
            echo "  Coverage: {$percentage}%\n\n";
            
            if ($percentage > 80) {
                echo "✅ EXCELLENT! Most leads are now linked for iframe display.\n";
            } elseif ($percentage > 50) {
                echo "⚠️ GOOD PROGRESS! Over half of leads are linked.\n";
            } else {
                echo "🔄 PARTIAL: Some leads linked, may need another run.\n";
            }
        }
    }
}

echo "\n" . date('Y-m-d H:i:s') . " - Sync completed\n";












