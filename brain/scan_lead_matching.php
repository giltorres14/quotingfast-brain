<?php
/**
 * Scan Brain leads and check how many exist in Vici by phone number
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
echo "              BRAIN → VICI LEAD MATCHING ANALYSIS                              \n";
echo "================================================================================\n\n";

$startTime = microtime(true);

// Get Brain statistics
echo "📊 Analyzing Brain Database...\n";

$brainStats = DB::table('leads')->selectRaw('
    COUNT(*) as total,
    COUNT(DISTINCT phone) as unique_phones,
    COUNT(vici_list_id) as has_vici_list,
    MIN(created_at) as oldest,
    MAX(created_at) as newest
')->first();

echo "  • Total leads: " . number_format($brainStats->total) . "\n";
echo "  • Unique phone numbers: " . number_format($brainStats->unique_phones) . "\n";
echo "  • Leads with vici_list_id: " . number_format($brainStats->has_vici_list) . "\n";
echo "  • Date range: " . substr($brainStats->oldest, 0, 10) . " to " . substr($brainStats->newest, 0, 10) . "\n\n";

// Check by vici_list_id assignment
$listDistribution = DB::table('leads')
    ->selectRaw('vici_list_id, COUNT(*) as count')
    ->groupBy('vici_list_id')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

echo "📋 Brain Leads by Vici List:\n";
foreach ($listDistribution as $list) {
    $listId = $list->vici_list_id ?: 'NULL (not assigned)';
    echo "  • List {$listId}: " . number_format($list->count) . " leads\n";
}

echo "\n🔍 Sampling Phone Numbers to Check in Vici...\n";

// Get a sample of phone numbers from Brain
$sampleSize = 1000;
$sampleLeads = DB::table('leads')
    ->select('id', 'phone', 'vici_list_id', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit($sampleSize)
    ->get();

echo "  Checking {$sampleSize} recent leads...\n\n";

// Check these phones in Vici
$matchCount = 0;
$noMatchCount = 0;
$checkBatches = array_chunk($sampleLeads->toArray(), 100);

foreach ($checkBatches as $batchNum => $batch) {
    $phones = array_map(function($lead) {
        // Clean phone number - remove non-digits
        return preg_replace('/\D/', '', $lead->phone);
    }, $batch);
    
    $phoneList = "'" . implode("','", $phones) . "'";
    
    // Query Vici for these phone numbers
    $query = "
        SELECT 
            phone_number,
            lead_id,
            list_id,
            vendor_lead_code,
            entry_date
        FROM vicidial_list
        WHERE phone_number IN ($phoneList)
    ";
    
    try {
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($query)
        ]);
        
        if ($response->successful()) {
            $output = json_decode($response->body(), true)['output'] ?? '';
            $lines = explode("\n", $output);
            
            $viciPhones = [];
            foreach ($lines as $line) {
                if (empty(trim($line)) || 
                    strpos($line, 'Could not create') !== false || 
                    strpos($line, 'Failed to add') !== false) {
                    continue;
                }
                
                $data = explode("\t", $line);
                if (count($data) >= 1) {
                    $viciPhones[] = preg_replace('/\D/', '', $data[0]);
                }
            }
            
            // Count matches
            foreach ($phones as $phone) {
                if (in_array($phone, $viciPhones)) {
                    $matchCount++;
                } else {
                    $noMatchCount++;
                }
            }
        }
        
        echo "  Batch " . ($batchNum + 1) . "/" . count($checkBatches) . " processed\r";
        
    } catch (\Exception $e) {
        echo "  Error checking batch: " . $e->getMessage() . "\n";
    }
}

echo "\n\n📊 MATCHING RESULTS:\n";
echo "  • Leads checked: " . $sampleSize . "\n";
echo "  • Found in Vici: " . $matchCount . " (" . round($matchCount / $sampleSize * 100, 1) . "%)\n";
echo "  • NOT in Vici: " . $noMatchCount . " (" . round($noMatchCount / $sampleSize * 100, 1) . "%)\n\n";

// Check recent leads specifically
echo "🔍 Checking Today's Leads...\n";
$todayLeads = DB::table('leads')
    ->whereDate('created_at', Carbon::today())
    ->count();

$todayWithViciList = DB::table('leads')
    ->whereDate('created_at', Carbon::today())
    ->whereNotNull('vici_list_id')
    ->count();

echo "  • Leads created today: " . number_format($todayLeads) . "\n";
echo "  • With vici_list_id: " . number_format($todayWithViciList) . "\n";
echo "  • Push rate: " . ($todayLeads > 0 ? round($todayWithViciList / $todayLeads * 100, 1) : 0) . "%\n\n";

// Check for phone format issues
echo "📞 Phone Number Format Analysis:\n";
$phoneFormats = DB::table('leads')
    ->selectRaw("
        CASE 
            WHEN phone LIKE '+1%' THEN 'International (+1)'
            WHEN phone LIKE '1%' AND LENGTH(phone) = 11 THEN 'With country code (1)'
            WHEN LENGTH(REGEXP_REPLACE(phone, '[^0-9]', '')) = 10 THEN '10 digits'
            WHEN LENGTH(REGEXP_REPLACE(phone, '[^0-9]', '')) = 11 THEN '11 digits'
            ELSE 'Other'
        END as format,
        COUNT(*) as count
    ")
    ->groupBy('format')
    ->get();

foreach ($phoneFormats as $format) {
    echo "  • {$format->format}: " . number_format($format->count) . "\n";
}

// Provide recommendations
echo "\n💡 RECOMMENDATIONS:\n";

if ($matchCount < $sampleSize * 0.5) {
    echo "  ⚠️ Low match rate detected! Possible issues:\n";
    echo "     1. Leads not being pushed to Vici (check VICI_PUSH_ENABLED)\n";
    echo "     2. Phone format mismatch (Brain vs Vici formatting)\n";
    echo "     3. Make.com pushing with different vendor_lead_code\n";
    echo "     4. Duplicate prevention in Vici blocking inserts\n";
}

if ($brainStats->has_vici_list < $brainStats->total * 0.8) {
    echo "  ⚠️ Many leads missing vici_list_id:\n";
    echo "     - " . number_format($brainStats->total - $brainStats->has_vici_list) . " leads need list assignment\n";
    echo "     - Run bulk push to assign leads to List 101\n";
}

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Analysis completed in {$executionTime} seconds\n";
echo "================================================================================\n";










