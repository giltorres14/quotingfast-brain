<?php
/**
 * Import FULL 90 DAYS of Vici Call Logs
 * AUTODIAL and AUTO2 campaigns from correct database
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
echo "           IMPORTING FULL 90 DAYS OF VICI CALL LOGS                            \n";
echo "================================================================================\n\n";

$startTime = microtime(true);
$DB_NAME = 'Q6hdjl67GRigMofv'; // CORRECT DATABASE!

// Import FULL 90 days
$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(90);

echo "📊 Database: $DB_NAME\n";
echo "📅 Date Range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n";
echo "🎯 Campaigns: AUTODIAL, AUTO2\n\n";

// Get a preview of total data
$countQuery = "
    SELECT 
        COUNT(*) as total_calls,
        MIN(call_date) as oldest,
        MAX(call_date) as newest
    FROM vicidial_log 
    WHERE campaign_id IN ('AUTODIAL', 'AUTO2')
    AND call_date >= '" . $startDate->format('Y-m-d') . "'
";

try {
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -N -B -e " . escapeshellarg($countQuery)
    ]);
    
    if ($response->successful()) {
        $output = json_decode($response->body(), true)['output'] ?? '';
        $lines = explode("\n", $output);
        foreach($lines as $line) {
            if(strpos($line, 'Could not create') === false && 
               strpos($line, 'Failed to add') === false && 
               trim($line)) {
                $data = explode("\t", $line);
                if(count($data) >= 3) {
                    echo "📊 Total calls to import: " . number_format($data[0]) . "\n";
                    echo "📅 Date range in Vici: " . $data[1] . " to " . $data[2] . "\n\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "Warning: Could not get preview\n";
}

echo "Starting import (this will take several minutes)...\n";
echo str_repeat("-", 80) . "\n";

// Process in weekly chunks for better performance
$totalImported = 0;
$currentDate = clone $startDate;
$weekNum = 1;

while ($currentDate->lte($endDate)) {
    $weekStart = $currentDate->format('Y-m-d 00:00:00');
    $weekEnd = min(
        $currentDate->copy()->addDays(6)->format('Y-m-d 23:59:59'),
        $endDate->format('Y-m-d 23:59:59')
    );
    
    echo "Week $weekNum (" . $currentDate->format('M d') . " - " . min($currentDate->copy()->addDays(6), $endDate)->format('M d') . "): ";
    
    // Query for this week's data
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
        WHERE vl.call_date BETWEEN '$weekStart' AND '$weekEnd'
        AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
    ";
    
    try {
        $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -N -B -e " . escapeshellarg($query)
        ]);
        
        if ($response->successful()) {
            $output = json_decode($response->body(), true)['output'] ?? '';
            $lines = explode("\n", $output);
            $weekCount = 0;
            $batch = [];
            
            foreach ($lines as $line) {
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
                    
                    // Insert in batches of 1000
                    if (count($batch) >= 1000) {
                        DB::table('orphan_call_logs')->insertOrIgnore($batch);
                        $weekCount += count($batch);
                        $batch = [];
                        echo ".";
                    }
                }
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                DB::table('orphan_call_logs')->insertOrIgnore($batch);
                $weekCount += count($batch);
            }
            
            echo " ✅ " . number_format($weekCount) . " calls\n";
            $totalImported += $weekCount;
            
        } else {
            echo " ❌ Error\n";
        }
        
    } catch (\Exception $e) {
        echo " ❌ Error: " . substr($e->getMessage(), 0, 50) . "\n";
    }
    
    // Move to next week
    $currentDate->addDays(7);
    $weekNum++;
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
        COUNT(DISTINCT list_id) as unique_lists
    ')
    ->first();

echo "📊 FINAL DATABASE STATISTICS:\n";
echo "  • Total call logs: " . number_format($stats->total) . "\n";
echo "  • Date range: " . substr($stats->oldest, 0, 10) . " to " . substr($stats->newest, 0, 10) . "\n";
echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n";
echo "  • Unique lists: " . number_format($stats->unique_lists) . "\n\n";

// Campaign breakdown
echo "📈 CALLS BY CAMPAIGN:\n";
$campaigns = DB::table('orphan_call_logs')
    ->select('campaign_id', DB::raw('COUNT(*) as count'))
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->groupBy('campaign_id')
    ->orderBy('count', 'desc')
    ->get();

foreach ($campaigns as $campaign) {
    echo "  • {$campaign->campaign_id}: " . number_format($campaign->count) . " calls\n";
}

// Status distribution
echo "\n📊 TOP CALL STATUSES:\n";
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(15)
    ->get();

foreach ($statuses as $status) {
    $statusName = $status->status ?: 'NULL';
    $percentage = $stats->total > 0 ? round(($status->count / $stats->total) * 100, 1) : 0;
    echo "  • {$statusName}: " . number_format($status->count) . " ({$percentage}%)\n";
}

// List distribution (101-120)
echo "\n📋 CALLS BY LIST (101-120):\n";
$lists = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('list_id')
    ->whereBetween('list_id', [101, 120])
    ->groupBy('list_id')
    ->orderBy('list_id')
    ->get();

if ($lists->isEmpty()) {
    echo "  No calls found in lists 101-120 yet\n";
} else {
    foreach ($lists as $list) {
        echo "  • List {$list->list_id}: " . number_format($list->count) . " calls\n";
    }
}

// Call metrics by attempt number
echo "\n📞 CONVERSION BY ATTEMPT NUMBER:\n";
$attemptAnalysis = DB::select("
    WITH lead_attempts AS (
        SELECT 
            vici_lead_id,
            status,
            ROW_NUMBER() OVER (PARTITION BY vici_lead_id ORDER BY call_date) as attempt_num
        FROM orphan_call_logs
        WHERE campaign_id IN ('AUTODIAL', 'AUTO2')
        AND vici_lead_id IS NOT NULL
    )
    SELECT 
        attempt_num,
        COUNT(*) as total_calls,
        COUNT(CASE WHEN status = 'SALE' THEN 1 END) as sales,
        ROUND(COUNT(CASE WHEN status = 'SALE' THEN 1 END) * 100.0 / COUNT(*), 2) as conversion_rate
    FROM lead_attempts
    WHERE attempt_num <= 10
    GROUP BY attempt_num
    ORDER BY attempt_num
");

foreach ($attemptAnalysis as $attempt) {
    echo "  • Attempt #{$attempt->attempt_num}: " . 
         number_format($attempt->total_calls) . " calls, " .
         $attempt->sales . " sales (" . $attempt->conversion_rate . "%)\n";
}

// Best calling times
echo "\n⏰ BEST CALLING HOURS (by contact rate):\n";
$hourAnalysis = DB::select("
    SELECT 
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as total_calls,
        COUNT(CASE WHEN length_in_sec > 30 THEN 1 END) as connected,
        ROUND(COUNT(CASE WHEN length_in_sec > 30 THEN 1 END) * 100.0 / COUNT(*), 1) as connect_rate
    FROM orphan_call_logs
    WHERE campaign_id IN ('AUTODIAL', 'AUTO2')
    GROUP BY EXTRACT(HOUR FROM call_date)
    ORDER BY connect_rate DESC
    LIMIT 5
");

foreach ($hourAnalysis as $hour) {
    $hourFormatted = str_pad($hour->hour, 2, '0', STR_PAD_LEFT) . ':00';
    echo "  • {$hourFormatted}: " . $hour->connect_rate . "% connect rate (" . 
         number_format($hour->connected) . "/" . number_format($hour->total_calls) . ")\n";
}

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Total execution time: " . round($executionTime / 60, 1) . " minutes\n";

// Set up continuous monitoring
echo "\n🔄 AUTOMATED SYNC STATUS:\n";
echo "  • Incremental sync runs every 5 minutes\n";
echo "  • Lead flow movements run every 5 minutes\n";
echo "  • Orphan matching runs every 10 minutes\n";
echo "  • Cron is active and monitoring\n";

echo "================================================================================\n";




