<?php
/**
 * Import RECENT Vici Call Logs (Last 30 Days)
 * From AUTODIAL and AUTO2 campaigns only
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
echo "           IMPORTING RECENT VICI CALL LOGS (LAST 30 DAYS)                      \n";
echo "================================================================================\n\n";

$startTime = microtime(true);
$DB_NAME = 'Q6hdjl67GRigMofv';

// Import last 30 days only for recent data analysis
$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(30);

echo "📊 Database: $DB_NAME\n";
echo "📅 Date Range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n";
echo "🎯 Campaigns: AUTODIAL, AUTO2\n\n";

// First, get a preview of what we're importing
$previewQuery = "
    SELECT 
        campaign_id,
        COUNT(*) as total_calls,
        COUNT(DISTINCT lead_id) as unique_leads,
        MIN(call_date) as oldest,
        MAX(call_date) as newest
    FROM vicidial_log 
    WHERE campaign_id IN ('AUTODIAL', 'AUTO2')
    AND call_date >= '" . $startDate->format('Y-m-d H:i:s') . "'
    GROUP BY campaign_id
";

try {
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -e " . escapeshellarg($previewQuery)
    ]);
    
    if ($response->successful()) {
        $output = json_decode($response->body(), true)['output'] ?? '';
        $lines = explode("\n", $output);
        echo "📊 Data Preview:\n";
        foreach($lines as $line) {
            if(strpos($line, 'Could not create') === false && 
               strpos($line, 'Failed to add') === false && 
               trim($line)) {
                echo "  " . $line . "\n";
            }
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Warning: Could not get preview\n";
}

echo "Starting import...\n" . str_repeat("-", 80) . "\n";

// Process day by day for better progress tracking
$totalImported = 0;
$currentDate = clone $startDate;

while ($currentDate->lte($endDate)) {
    $dayStart = $currentDate->format('Y-m-d 00:00:00');
    $dayEnd = $currentDate->format('Y-m-d 23:59:59');
    
    echo $currentDate->format('M d') . ": ";
    
    // Query for this day's data - using tab-separated format for easier parsing
    $query = "
        SELECT 
            vl.call_date,
            vl.lead_id,
            vl.list_id,
            vl.phone_number,
            vl.campaign_id,
            vl.status,
            vl.length_in_sec,
            vl.uniqueid,
            vl.user,
            vl.term_reason,
            IFNULL(vlist.vendor_lead_code, '')
        FROM vicidial_log vl
        LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
        WHERE vl.call_date BETWEEN '$dayStart' AND '$dayEnd'
        AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
    ";
    
    try {
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -N -B -e " . escapeshellarg($query)
        ]);
        
        if ($response->successful()) {
            $output = json_decode($response->body(), true)['output'] ?? '';
            
            // Parse the tab-separated output
            $lines = explode("\n", $output);
            $dayCount = 0;
            $batch = [];
            
            foreach ($lines as $line) {
                // Skip empty lines and SSH warnings
                if (empty(trim($line)) || 
                    strpos($line, 'Could not create') !== false || 
                    strpos($line, 'Failed to add') !== false) {
                    continue;
                }
                
                $data = explode("\t", $line);
                
                if (count($data) >= 10) {
                    $batch[] = [
                        'call_date' => $data[0],
                        'vici_lead_id' => $data[1] ?: null,
                        'list_id' => $data[2] ?: null,
                        'phone_number' => $data[3],
                        'campaign_id' => $data[4],
                        'status' => $data[5],
                        'length_in_sec' => intval($data[6]),
                        'uniqueid' => $data[7] ?: null,
                        'agent_id' => $data[8] ?: null,
                        'term_reason' => $data[9] ?: null,
                        'vendor_lead_code' => isset($data[10]) && $data[10] ? $data[10] : null,
                        'source_table' => 'vicidial_log',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    // Insert in batches of 500
                    if (count($batch) >= 500) {
                        DB::table('orphan_call_logs')->insertOrIgnore($batch);
                        $dayCount += count($batch);
                        $batch = [];
                    }
                }
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                DB::table('orphan_call_logs')->insertOrIgnore($batch);
                $dayCount += count($batch);
            }
            
            $campaignBreakdown = "";
            if ($dayCount > 0) {
                // Quick check of what was imported
                $check = DB::table('orphan_call_logs')
                    ->whereDate('call_date', $currentDate->format('Y-m-d'))
                    ->select('campaign_id', DB::raw('COUNT(*) as count'))
                    ->groupBy('campaign_id')
                    ->get();
                
                $breakdown = [];
                foreach($check as $c) {
                    $breakdown[] = $c->campaign_id . ":" . $c->count;
                }
                $campaignBreakdown = " (" . implode(", ", $breakdown) . ")";
            }
            
            echo str_pad($dayCount > 0 ? "✅ " . number_format($dayCount) . " calls" . $campaignBreakdown : "- no data", 40) . "\n";
            $totalImported += $dayCount;
            
        } else {
            echo "❌ Error\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . substr($e->getMessage(), 0, 50) . "\n";
    }
    
    // Move to next day
    $currentDate->addDay();
}

echo str_repeat("-", 80) . "\n";
echo "✅ IMPORT COMPLETE!\n";
echo "  • Total calls imported: " . number_format($totalImported) . "\n\n";

// Get comprehensive statistics
$stats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total,
        MIN(call_date) as oldest,
        MAX(call_date) as newest,
        COUNT(DISTINCT phone_number) as unique_phones,
        COUNT(DISTINCT vici_lead_id) as unique_leads,
        COUNT(DISTINCT list_id) as unique_lists,
        COUNT(DISTINCT campaign_id) as unique_campaigns
    ')
    ->first();

echo "📊 DATABASE STATISTICS:\n";
echo "  • Total call logs: " . number_format($stats->total) . "\n";
echo "  • Date range: " . substr($stats->oldest, 0, 10) . " to " . substr($stats->newest, 0, 10) . "\n";
echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n";
echo "  • Unique lists: " . number_format($stats->unique_lists) . "\n";
echo "  • Campaigns: " . number_format($stats->unique_campaigns) . "\n\n";

// Campaign breakdown
echo "📈 CALLS BY CAMPAIGN:\n";
$campaigns = DB::table('orphan_call_logs')
    ->select('campaign_id', DB::raw('COUNT(*) as count'))
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->groupBy('campaign_id')
    ->orderBy('count', 'desc')
    ->get();

foreach ($campaigns as $campaign) {
    $percentage = $stats->total > 0 ? round(($campaign->count / $stats->total) * 100, 1) : 0;
    echo "  • {$campaign->campaign_id}: " . number_format($campaign->count) . " ({$percentage}%)\n";
}

// Status distribution
echo "\n📊 TOP 10 CALL STATUSES:\n";
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($statuses as $status) {
    $statusName = $status->status ?: 'NULL';
    $percentage = $stats->total > 0 ? round(($status->count / $stats->total) * 100, 1) : 0;
    echo "  • {$statusName}: " . number_format($status->count) . " ({$percentage}%)\n";
}

// List distribution for our lists (101-120)
echo "\n📋 CALLS BY LIST (101-120 range):\n";
$lists = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('list_id')
    ->whereBetween('list_id', [101, 120])
    ->groupBy('list_id')
    ->orderBy('list_id')
    ->get();

if ($lists->isEmpty()) {
    echo "  No calls found in lists 101-120\n";
} else {
    foreach ($lists as $list) {
        echo "  • List {$list->list_id}: " . number_format($list->count) . " calls\n";
    }
}

// Average call metrics
$metrics = DB::table('orphan_call_logs')
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->selectRaw('
        AVG(length_in_sec) as avg_duration,
        MAX(length_in_sec) as max_duration,
        COUNT(CASE WHEN length_in_sec > 0 THEN 1 END) as connected_calls,
        COUNT(CASE WHEN status = "SALE" THEN 1 END) as sales
    ')
    ->first();

echo "\n📞 CALL METRICS:\n";
echo "  • Average duration: " . round($metrics->avg_duration) . " seconds\n";
echo "  • Longest call: " . round($metrics->max_duration / 60) . " minutes\n";
echo "  • Connected calls: " . number_format($metrics->connected_calls) . "\n";
echo "  • Sales: " . number_format($metrics->sales) . "\n";

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Total execution time: {$executionTime} seconds\n";
echo "================================================================================\n";




