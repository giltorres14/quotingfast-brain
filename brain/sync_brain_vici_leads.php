<?php
/**
 * Sync Brain leads with Vici leads by phone number
 * Updates vendor_lead_code in Vici to match Brain ID for iframe popup
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n================================================================================\n";
echo "           BRAIN <-> VICI LEAD SYNCHRONIZATION                                 \n";
echo "================================================================================\n\n";

$startTime = microtime(true);

// First, analyze the situation
echo "📊 ANALYZING CURRENT STATE...\n";
echo str_repeat("-", 60) . "\n";

// Get Brain stats
$brainTotal = DB::table('leads')->count();
$brainWithList = DB::table('leads')->whereNotNull('vici_list_id')->count();

echo "Brain Database:\n";
echo "  • Total leads: " . number_format($brainTotal) . "\n";
echo "  • With vici_list_id: " . number_format($brainWithList) . "\n\n";

// Check a sample to see current vendor_lead_code status in Vici
echo "🔍 Checking vendor_lead_code usage in Vici...\n";

$query = "
    SELECT 
        COUNT(*) as total,
        COUNT(vendor_lead_code) as has_vendor_code,
        COUNT(DISTINCT vendor_lead_code) as unique_vendor_codes
    FROM vicidial_list
    WHERE list_id NOT IN (199, 87878787)
    AND list_id > 1000
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
            echo "  • Total leads in active lists: " . number_format($data[0]) . "\n";
            echo "  • With vendor_lead_code: " . number_format($data[1]) . "\n";
            echo "  • Unique vendor codes: " . number_format($data[2]) . "\n";
        }
    }
}

echo "\n💡 SOLUTION: Match leads by phone and update vendor_lead_code\n";
echo str_repeat("-", 60) . "\n\n";

// Process in batches
$batchSize = 500;
$offset = 0;
$totalMatched = 0;
$totalUpdated = 0;

echo "Processing Brain leads in batches of $batchSize...\n\n";

while (true) {
    // Get batch of Brain leads
    $brainLeads = DB::table('leads')
        ->select('id', 'phone', 'vici_list_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->offset($offset)
        ->limit($batchSize)
        ->get();
    
    if ($brainLeads->isEmpty()) {
        break;
    }
    
    echo "Batch " . (($offset / $batchSize) + 1) . ": Processing " . count($brainLeads) . " leads... ";
    
    // Build phone list for Vici query
    $phones = [];
    $phoneToId = [];
    
    foreach ($brainLeads as $lead) {
        $cleanPhone = preg_replace('/\D/', '', $lead->phone);
        if (strlen($cleanPhone) == 10) {
            $phones[] = $cleanPhone;
            $phoneToId[$cleanPhone] = $lead->id;
        } elseif (strlen($cleanPhone) == 11 && substr($cleanPhone, 0, 1) == '1') {
            $cleanPhone = substr($cleanPhone, 1);
            $phones[] = $cleanPhone;
            $phoneToId[$cleanPhone] = $lead->id;
        }
    }
    
    if (empty($phones)) {
        echo "No valid phones\n";
        $offset += $batchSize;
        continue;
    }
    
    // Query Vici for these phones
    $phoneList = "'" . implode("','", $phones) . "'";
    
    // First, check which ones exist
    $checkQuery = "
        SELECT 
            phone_number,
            lead_id as vici_lead_id,
            list_id,
            vendor_lead_code,
            status
        FROM vicidial_list
        WHERE phone_number IN ($phoneList)
        AND list_id NOT IN (199, 87878787)
        ORDER BY entry_date DESC
    ";
    
    try {
        $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($checkQuery)
        ]);
        
        if ($response->successful()) {
            $output = json_decode($response->body(), true)['output'] ?? '';
            $lines = explode("\n", $output);
            
            $updates = [];
            $batchMatched = 0;
            
            foreach ($lines as $line) {
                if (empty(trim($line)) || strpos($line, 'Could') !== false) continue;
                
                $data = explode("\t", $line);
                if (count($data) >= 5) {
                    $viciPhone = $data[0];
                    $viciLeadId = $data[1];
                    $viciListId = $data[2];
                    $vendorCode = $data[3];
                    
                    if (isset($phoneToId[$viciPhone])) {
                        $brainId = $phoneToId[$viciPhone];
                        $batchMatched++;
                        
                        // Update Brain with correct list_id
                        DB::table('leads')
                            ->where('id', $brainId)
                            ->update([
                                'vici_list_id' => $viciListId,
                                'updated_at' => now()
                            ]);
                        
                        // Prepare update for Vici if vendor_lead_code is empty or different
                        if (empty($vendorCode) || $vendorCode != $brainId) {
                            $updates[] = [
                                'vici_lead_id' => $viciLeadId,
                                'brain_id' => $brainId
                            ];
                        }
                    }
                }
            }
            
            // Update vendor_lead_code in Vici
            if (!empty($updates)) {
                foreach ($updates as $update) {
                    $updateQuery = "
                        UPDATE vicidial_list 
                        SET vendor_lead_code = '{$update['brain_id']}'
                        WHERE lead_id = {$update['vici_lead_id']}
                    ";
                    
                    Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                        'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -e " . escapeshellarg($updateQuery)
                    ]);
                    
                    $totalUpdated++;
                }
            }
            
            $totalMatched += $batchMatched;
            echo "Matched: $batchMatched, Updated: " . count($updates) . "\n";
        }
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    $offset += $batchSize;
    
    // Progress indicator
    if ($offset % 5000 == 0) {
        echo "  [Progress: " . number_format($offset) . " / " . number_format($brainTotal) . " processed]\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SYNCHRONIZATION COMPLETE!\n\n";

echo "📊 RESULTS:\n";
echo "  • Total Brain leads processed: " . number_format($offset) . "\n";
echo "  • Matched with Vici: " . number_format($totalMatched) . "\n";
echo "  • vendor_lead_code updated: " . number_format($totalUpdated) . "\n";

// Verify the sync
echo "\n🔍 VERIFICATION:\n";

$verifyQuery = "
    SELECT 
        COUNT(*) as total,
        COUNT(vendor_lead_code) as has_vendor,
        COUNT(DISTINCT list_id) as unique_lists
    FROM vicidial_list
    WHERE vendor_lead_code IS NOT NULL
    AND vendor_lead_code != ''
    AND list_id > 1000
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($verifyQuery)
]);

if ($response->successful()) {
    $output = json_decode($response->body(), true)['output'] ?? '';
    $lines = explode("\n", $output);
    
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos($line, 'Could') !== false) continue;
        
        $data = explode("\t", $line);
        if (count($data) >= 3) {
            echo "  • Vici leads with Brain ID: " . number_format($data[1]) . "\n";
            echo "  • Across " . $data[2] . " different lists\n";
        }
    }
}

// Update Brain stats
$newStats = DB::table('leads')
    ->selectRaw('
        COUNT(*) as total,
        COUNT(vici_list_id) as has_list,
        COUNT(DISTINCT vici_list_id) as unique_lists
    ')
    ->first();

echo "\n📊 BRAIN DATABASE UPDATED:\n";
echo "  • Total leads: " . number_format($newStats->total) . "\n";
echo "  • With vici_list_id: " . number_format($newStats->has_list) . "\n";
echo "  • Unique lists: " . $newStats->unique_lists . "\n";

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Execution time: " . round($executionTime / 60, 1) . " minutes\n";

echo "\n✅ IFRAME INTEGRATION READY!\n";
echo "When agents receive calls, the vendor_lead_code will match Brain ID\n";
echo "and the correct lead will pop up in the iframe.\n";
echo "================================================================================\n";

