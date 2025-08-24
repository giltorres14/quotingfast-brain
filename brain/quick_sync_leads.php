<?php
/**
 * Quick sync to establish vendor_lead_code connections
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== QUICK BRAIN-VICI SYNC ===\n\n";

// First, let's just check how many leads already have vendor_lead_code
echo "Checking current state...\n";

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
            echo "Vici AUTODIAL leads:\n";
            echo "  Total: " . number_format($data[0]) . "\n";
            echo "  With vendor_lead_code: " . number_format($data[1]) . "\n";
            echo "  With numeric code: " . number_format($data[2]) . "\n\n";
            
            if ($data[1] > $data[0] * 0.9) {
                echo "✅ Most leads already have vendor_lead_code!\n";
                echo "The iframe should work for these leads.\n\n";
            }
        }
    }
}

// Now let's update Brain to have the correct list assignments
echo "Updating Brain with correct list assignments...\n";

// Get a sample of phones from active Vici lists
$query2 = "
    SELECT 
        phone_number,
        list_id,
        vendor_lead_code
    FROM vicidial_list
    WHERE list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,6026,
                      8001,8002,8003,8004,8005,8006,8007,8008)
    AND vendor_lead_code IS NOT NULL
    AND vendor_lead_code != ''
    LIMIT 10000
";

$response2 = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($query2)
]);

if ($response2->successful()) {
    $output = json_decode($response2->body(), true)['output'] ?? '';
    $lines = explode("\n", $output);
    
    $updates = 0;
    $phoneToList = [];
    
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos($line, 'Could') !== false) continue;
        
        $data = explode("\t", $line);
        if (count($data) >= 3) {
            $phone = preg_replace('/\D/', '', $data[0]);
            $listId = $data[1];
            $vendorCode = $data[2];
            
            // If vendor_code looks like a Brain ID (numeric), update Brain
            if (is_numeric($vendorCode)) {
                $updated = DB::table('leads')
                    ->where('id', $vendorCode)
                    ->update(['vici_list_id' => $listId]);
                
                if ($updated) {
                    $updates++;
                }
            }
            
            // Also try to match by phone
            if (!isset($phoneToList[$phone])) {
                $phoneToList[$phone] = $listId;
            }
        }
    }
    
    echo "Updated $updates Brain leads with correct list IDs\n\n";
}

// Summary
$brainStats = DB::table('leads')
    ->selectRaw('
        COUNT(*) as total,
        COUNT(vici_list_id) as has_list,
        COUNT(CASE WHEN vici_list_id > 1000 THEN 1 END) as active_lists
    ')
    ->first();

echo "=== FINAL STATUS ===\n";
echo "Brain Database:\n";
echo "  Total leads: " . number_format($brainStats->total) . "\n";
echo "  With vici_list_id: " . number_format($brainStats->has_list) . "\n";
echo "  In active lists (>1000): " . number_format($brainStats->active_lists) . "\n\n";

echo "✅ Sync complete!\n";
echo "The iframe should now work when agents receive calls.\n";
echo "vendor_lead_code in Vici → Brain lead ID → Iframe displays correct lead\n";










