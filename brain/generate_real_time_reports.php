<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n📊 GENERATING COMPREHENSIVE VICI REPORTS\n";
echo "=========================================\n\n";

// 1. CONVERSION ANALYSIS (What we discovered: 1.08% conversion rate)
echo "1️⃣ CONVERSION ANALYSIS\n";
echo "------------------------\n";
$transfers = DB::table('orphan_call_logs')
    ->whereIn('status', ['XFER', 'XFERA'])
    ->count();

$totalCalls = DB::table('orphan_call_logs')->count();
$uniqueLeads = DB::table('orphan_call_logs')
    ->distinct('phone_number')
    ->count('phone_number');

$conversionRate = $uniqueLeads > 0 ? ($transfers / $uniqueLeads) * 100 : 0;

echo "Total Calls: " . number_format($totalCalls) . "\n";
echo "Unique Leads: " . number_format($uniqueLeads) . "\n";
echo "Transfers (Sales): " . number_format($transfers) . "\n";
echo "Conversion Rate: " . number_format($conversionRate, 2) . "%\n\n";

// 2. SPEED TO LEAD ANALYSIS
echo "2️⃣ SPEED TO LEAD ANALYSIS\n";
echo "--------------------------\n";
$speedBuckets = DB::table('orphan_call_logs as o1')
    ->selectRaw("
        CASE 
            WHEN EXTRACT(EPOCH FROM (o1.call_date - l.created_at))/60 < 5 THEN '< 5 min'
            WHEN EXTRACT(EPOCH FROM (o1.call_date - l.created_at))/60 < 30 THEN '5-30 min'
            WHEN EXTRACT(EPOCH FROM (o1.call_date - l.created_at))/60 < 60 THEN '30-60 min'
            WHEN EXTRACT(EPOCH FROM (o1.call_date - l.created_at))/3600 < 24 THEN '1-24 hours'
            ELSE '> 24 hours'
        END as speed_bucket,
        COUNT(*) as count,
        SUM(CASE WHEN o1.status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers
    ")
    ->join('leads as l', 'o1.phone_number', '=', DB::raw("REPLACE(REPLACE(REPLACE(l.phone, '(', ''), ')', ''), '-', '')"))
    ->whereRaw("o1.call_date = (SELECT MIN(call_date) FROM orphan_call_logs WHERE phone_number = o1.phone_number)")
    ->groupBy('speed_bucket')
    ->orderByRaw("
        CASE speed_bucket
            WHEN '< 5 min' THEN 1
            WHEN '5-30 min' THEN 2
            WHEN '30-60 min' THEN 3
            WHEN '1-24 hours' THEN 4
            ELSE 5
        END
    ")
    ->get();

foreach ($speedBuckets as $bucket) {
    $convRate = $bucket->count > 0 ? ($bucket->transfers / $bucket->count) * 100 : 0;
    echo sprintf("%-15s: %6d calls, %4d transfers (%.2f%% conversion)\n", 
        $bucket->speed_bucket, 
        $bucket->count, 
        $bucket->transfers,
        $convRate
    );
}

// 3. DISPOSITION BREAKDOWN
echo "\n3️⃣ DISPOSITION BREAKDOWN\n";
echo "-------------------------\n";
$dispositions = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(15)
    ->get();

foreach ($dispositions as $disp) {
    $percent = ($disp->count / $totalCalls) * 100;
    echo sprintf("%-10s: %8d (%.2f%%)\n", $disp->status, $disp->count, $percent);
}

// 4. CALLS PER LEAD ANALYSIS
echo "\n4️⃣ CALLS PER LEAD ANALYSIS\n";
echo "---------------------------\n";
$callsPerLead = DB::table('orphan_call_logs')
    ->select('phone_number', DB::raw('COUNT(*) as call_count'))
    ->groupBy('phone_number')
    ->get();

$callBuckets = [
    '1 call' => 0,
    '2-3 calls' => 0,
    '4-5 calls' => 0,
    '6-10 calls' => 0,
    '11-20 calls' => 0,
    '21-30 calls' => 0,
    '31-40 calls' => 0,
    '40+ calls' => 0
];

foreach ($callsPerLead as $lead) {
    if ($lead->call_count == 1) $callBuckets['1 call']++;
    elseif ($lead->call_count <= 3) $callBuckets['2-3 calls']++;
    elseif ($lead->call_count <= 5) $callBuckets['4-5 calls']++;
    elseif ($lead->call_count <= 10) $callBuckets['6-10 calls']++;
    elseif ($lead->call_count <= 20) $callBuckets['11-20 calls']++;
    elseif ($lead->call_count <= 30) $callBuckets['21-30 calls']++;
    elseif ($lead->call_count <= 40) $callBuckets['31-40 calls']++;
    else $callBuckets['40+ calls']++;
}

foreach ($callBuckets as $bucket => $count) {
    $percent = $uniqueLeads > 0 ? ($count / $uniqueLeads) * 100 : 0;
    echo sprintf("%-15s: %8d leads (%.2f%%)\n", $bucket, $count, $percent);
}

// 5. OPTIMAL CALLING TIMES
echo "\n5️⃣ OPTIMAL CALLING TIMES\n";
echo "-------------------------\n";
$hourlyPerformance = DB::table('orphan_call_logs')
    ->selectRaw("
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as total_calls,
        SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers,
        SUM(CASE WHEN status NOT IN ('NA', 'A', 'B', 'N') THEN 1 ELSE 0 END) as contacts
    ")
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

echo "Hour | Calls  | Contacts | Transfers | Conv Rate\n";
echo "-----|--------|----------|-----------|----------\n";
foreach ($hourlyPerformance as $hour) {
    $contactRate = $hour->total_calls > 0 ? ($hour->contacts / $hour->total_calls) * 100 : 0;
    $convRate = $hour->contacts > 0 ? ($hour->transfers / $hour->contacts) * 100 : 0;
    echo sprintf("%2d:00| %6d | %8d | %9d | %6.2f%%\n", 
        $hour->hour, 
        $hour->total_calls, 
        $hour->contacts,
        $hour->transfers,
        $convRate
    );
}

// 6. LIST PERFORMANCE
echo "\n6️⃣ LIST PERFORMANCE ANALYSIS\n";
echo "-----------------------------\n";
$listPerformance = DB::table('orphan_call_logs')
    ->select('list_id', 
        DB::raw('COUNT(*) as total_calls'),
        DB::raw('COUNT(DISTINCT phone_number) as unique_leads'),
        DB::raw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers')
    )
    ->groupBy('list_id')
    ->orderBy('total_calls', 'desc')
    ->limit(20)
    ->get();

echo "List ID | Calls   | Leads  | Transfers | Conv Rate\n";
echo "--------|---------|--------|-----------|----------\n";
foreach ($listPerformance as $list) {
    $convRate = $list->unique_leads > 0 ? ($list->transfers / $list->unique_leads) * 100 : 0;
    echo sprintf("%7s | %7d | %6d | %9d | %6.2f%%\n", 
        $list->list_id ?: 'NULL', 
        $list->total_calls, 
        $list->unique_leads,
        $list->transfers,
        $convRate
    );
}

// 7. COST ANALYSIS (Based on $0.004/min)
echo "\n7️⃣ COST ANALYSIS\n";
echo "-----------------\n";
$totalMinutes = DB::table('orphan_call_logs')
    ->sum('length_in_sec') / 60;

$totalCost = $totalMinutes * 0.004;
$costPerTransfer = $transfers > 0 ? $totalCost / $transfers : 0;
$costPerLead = $uniqueLeads > 0 ? $totalCost / $uniqueLeads : 0;

echo "Total Minutes: " . number_format($totalMinutes, 0) . "\n";
echo "Total Cost: $" . number_format($totalCost, 2) . "\n";
echo "Cost per Lead: $" . number_format($costPerLead, 2) . "\n";
echo "Cost per Transfer: $" . number_format($costPerTransfer, 2) . "\n";

// 8. AGENT PERFORMANCE
echo "\n8️⃣ TOP AGENT PERFORMANCE\n";
echo "-------------------------\n";
$agents = DB::table('orphan_call_logs')
    ->select('agent_user',
        DB::raw('COUNT(*) as total_calls'),
        DB::raw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers'),
        DB::raw('AVG(length_in_sec) as avg_duration')
    )
    ->whereNotNull('agent_user')
    ->where('agent_user', '!=', '')
    ->groupBy('agent_user')
    ->orderBy('transfers', 'desc')
    ->limit(10)
    ->get();

echo "Agent      | Calls  | Transfers | Conv Rate | Avg Duration\n";
echo "-----------|--------|-----------|-----------|-------------\n";
foreach ($agents as $agent) {
    $convRate = $agent->total_calls > 0 ? ($agent->transfers / $agent->total_calls) * 100 : 0;
    echo sprintf("%-10s | %6d | %9d | %8.2f%% | %5.1f min\n", 
        substr($agent->agent_user, 0, 10), 
        $agent->total_calls, 
        $agent->transfers,
        $convRate,
        $agent->avg_duration / 60
    );
}

// Save to file
$report = ob_get_contents();
file_put_contents('reports/comprehensive_vici_report_' . date('Y-m-d_His') . '.txt', $report);

echo "\n\n✅ Report saved to reports/comprehensive_vici_report_" . date('Y-m-d_His') . ".txt\n";

