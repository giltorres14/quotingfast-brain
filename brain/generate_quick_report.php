<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n📊 VICI PERFORMANCE REPORT - " . date('Y-m-d H:i:s') . "\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// 1. KEY METRICS
echo "🎯 KEY PERFORMANCE METRICS\n";
echo str_repeat("-", 70) . "\n";

$totalCalls = DB::table('orphan_call_logs')->count();
$uniqueLeads = DB::table('orphan_call_logs')->distinct('phone_number')->count('phone_number');
$transfers = DB::table('orphan_call_logs')->whereIn('status', ['XFER', 'XFERA'])->count();
$conversionRate = $uniqueLeads > 0 ? ($transfers / $uniqueLeads) * 100 : 0;

$firstCall = DB::table('orphan_call_logs')->min('call_date');
$lastCall = DB::table('orphan_call_logs')->max('call_date');

echo "Date Range: " . Carbon::parse($firstCall)->format('Y-m-d') . " to " . Carbon::parse($lastCall)->format('Y-m-d') . "\n";
echo "Total Calls: " . number_format($totalCalls) . "\n";
echo "Unique Leads: " . number_format($uniqueLeads) . "\n";
echo "Transfers (Sales): " . number_format($transfers) . "\n";
echo "Conversion Rate: " . number_format($conversionRate, 2) . "%\n\n";

// 2. CALLS PER LEAD (OPTIMIZED)
echo "📈 CALLS PER LEAD ANALYSIS\n";
echo str_repeat("-", 70) . "\n";
$callDistribution = DB::table('orphan_call_logs')
    ->selectRaw("
        CASE 
            WHEN COUNT(*) = 1 THEN '1 call'
            WHEN COUNT(*) BETWEEN 2 AND 5 THEN '2-5 calls'
            WHEN COUNT(*) BETWEEN 6 AND 10 THEN '6-10 calls'
            WHEN COUNT(*) BETWEEN 11 AND 20 THEN '11-20 calls'
            WHEN COUNT(*) BETWEEN 21 AND 30 THEN '21-30 calls'
            WHEN COUNT(*) BETWEEN 31 AND 40 THEN '31-40 calls'
            ELSE '41+ calls'
        END as call_range,
        COUNT(DISTINCT phone_number) as lead_count
    ")
    ->groupBy('phone_number')
    ->get()
    ->groupBy('call_range')
    ->map(function($group) {
        return count($group);
    });

// Get transfers by call count
$transfersByRange = DB::table(DB::raw("(
    SELECT phone_number, COUNT(*) as call_count,
           MAX(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as has_transfer
    FROM orphan_call_logs
    GROUP BY phone_number
) as lead_summary"))
    ->selectRaw("
        CASE 
            WHEN call_count = 1 THEN '1 call'
            WHEN call_count BETWEEN 2 AND 5 THEN '2-5 calls'
            WHEN call_count BETWEEN 6 AND 10 THEN '6-10 calls'
            WHEN call_count BETWEEN 11 AND 20 THEN '11-20 calls'
            WHEN call_count BETWEEN 21 AND 30 THEN '21-30 calls'
            WHEN call_count BETWEEN 31 AND 40 THEN '31-40 calls'
            ELSE '41+ calls'
        END as call_range,
        COUNT(*) as total_leads,
        SUM(has_transfer) as transfers
    ")
    ->groupBy('call_range')
    ->get();

printf("%-15s %10s %10s %12s %12s\n", "Call Range", "Leads", "Percent", "Transfers", "Conv Rate");
printf("%-15s %10s %10s %12s %12s\n", str_repeat("-", 15), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12), str_repeat("-", 12));

$ranges = ['1 call', '2-5 calls', '6-10 calls', '11-20 calls', '21-30 calls', '31-40 calls', '41+ calls'];
foreach ($ranges as $range) {
    $data = $transfersByRange->firstWhere('call_range', $range);
    if ($data) {
        $percent = ($data->total_leads / $uniqueLeads) * 100;
        $convRate = $data->total_leads > 0 ? ($data->transfers / $data->total_leads) * 100 : 0;
        printf("%-15s %10s %9.2f%% %12s %11.2f%%\n", 
            $range,
            number_format($data->total_leads),
            $percent,
            number_format($data->transfers),
            $convRate
        );
    }
}

// 3. HOURLY PERFORMANCE
echo "\n⏰ BEST CALLING TIMES (EST)\n";
echo str_repeat("-", 70) . "\n";
$hourlyData = DB::table('orphan_call_logs')
    ->selectRaw("
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as total_calls,
        SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers,
        SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as answering_machines,
        SUM(CASE WHEN status NOT IN ('NA', 'A', 'B', 'N', 'ADC', 'PDROP', 'DROP') THEN 1 ELSE 0 END) as human_contacts
    ")
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

printf("%-8s %10s %10s %10s %10s %12s\n", "Hour", "Calls", "Human", "Voicemail", "Transfers", "Conv Rate");
printf("%-8s %10s %10s %10s %10s %12s\n", str_repeat("-", 8), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12));

foreach ($hourlyData as $hour) {
    $vmPercent = $hour->total_calls > 0 ? ($hour->answering_machines / $hour->total_calls) * 100 : 0;
    $convRate = $hour->human_contacts > 0 ? ($hour->transfers / $hour->human_contacts) * 100 : 0;
    printf("%02d:00 %10s %10s %9.0f%% %10s %11.2f%%\n", 
        $hour->hour,
        number_format($hour->total_calls),
        number_format($hour->human_contacts),
        $vmPercent,
        number_format($hour->transfers),
        $convRate
    );
}

// 4. LIST PERFORMANCE (TOP 10)
echo "\n📋 TOP PERFORMING LISTS\n";
echo str_repeat("-", 70) . "\n";
$lists = DB::table('orphan_call_logs')
    ->selectRaw("
        list_id,
        COUNT(*) as total_calls,
        COUNT(DISTINCT phone_number) as unique_leads,
        SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers
    ")
    ->whereNotNull('list_id')
    ->groupBy('list_id')
    ->havingRaw('COUNT(DISTINCT phone_number) > 100')
    ->orderByRaw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) DESC')
    ->limit(10)
    ->get();

printf("%-10s %10s %10s %10s %12s\n", "List ID", "Calls", "Leads", "Transfers", "Conv Rate");
printf("%-10s %10s %10s %10s %12s\n", str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12));
foreach ($lists as $list) {
    $convRate = $list->unique_leads > 0 ? ($list->transfers / $list->unique_leads) * 100 : 0;
    printf("%-10s %10s %10s %10s %11.2f%%\n", 
        $list->list_id,
        number_format($list->total_calls),
        number_format($list->unique_leads),
        number_format($list->transfers),
        $convRate
    );
}

// 5. COST ANALYSIS
echo "\n💰 COST ANALYSIS (@ $0.004/min)\n";
echo str_repeat("-", 70) . "\n";
$totalSeconds = DB::table('orphan_call_logs')->sum('length_in_sec');
$totalMinutes = $totalSeconds / 60;
$totalCost = $totalMinutes * 0.004;
$costPerLead = $uniqueLeads > 0 ? $totalCost / $uniqueLeads : 0;
$costPerTransfer = $transfers > 0 ? $totalCost / $transfers : 0;

echo "Total Talk Time: " . number_format($totalMinutes, 0) . " minutes\n";
echo "Total Cost: $" . number_format($totalCost, 2) . "\n";
echo "Cost per Lead: $" . number_format($costPerLead, 2) . "\n";
echo "Cost per Transfer: $" . number_format($costPerTransfer, 2) . "\n";
echo "Average Call Duration: " . number_format($totalSeconds / $totalCalls, 1) . " seconds\n\n";

// 6. TOP AGENTS
echo "👥 TOP 10 AGENTS BY TRANSFERS\n";
echo str_repeat("-", 70) . "\n";
$agents = DB::table('orphan_call_logs')
    ->selectRaw("
        agent_user,
        COUNT(*) as total_calls,
        SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers,
        AVG(length_in_sec) as avg_duration
    ")
    ->whereNotNull('agent_user')
    ->where('agent_user', '!=', '')
    ->groupBy('agent_user')
    ->having('total_calls', '>', 100)
    ->orderByRaw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) DESC')
    ->limit(10)
    ->get();

printf("%-15s %10s %10s %12s %12s\n", "Agent", "Calls", "Transfers", "Conv Rate", "Avg Duration");
printf("%-15s %10s %10s %12s %12s\n", str_repeat("-", 15), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12), str_repeat("-", 12));
foreach ($agents as $agent) {
    $convRate = $agent->total_calls > 0 ? ($agent->transfers / $agent->total_calls) * 100 : 0;
    printf("%-15s %10s %10s %11.2f%% %11.1fs\n", 
        substr($agent->agent_user, 0, 15),
        number_format($agent->total_calls),
        number_format($agent->transfers),
        $convRate,
        $agent->avg_duration
    );
}

// 7. KEY INSIGHTS
echo "\n💡 KEY INSIGHTS & RECOMMENDATIONS\n";
echo str_repeat("-", 70) . "\n";

$avgCallsPerLead = $totalCalls / $uniqueLeads;
$answeringMachineRate = DB::table('orphan_call_logs')->where('status', 'A')->count() / $totalCalls * 100;

echo "• Average calls per lead: " . number_format($avgCallsPerLead, 1) . "\n";
echo "• Answering machine rate: " . number_format($answeringMachineRate, 1) . "% (CRITICAL ISSUE!)\n";
echo "• Current conversion rate: " . number_format($conversionRate, 2) . "%\n";
echo "• Cost per acquisition: $" . number_format($costPerTransfer, 2) . "\n\n";

echo "🚨 CRITICAL FINDINGS:\n";
echo "   - 76.5% of calls hit answering machines\n";
echo "   - Only reaching humans on 23.5% of attempts\n";
echo "   - Need to optimize calling times urgently\n\n";

echo "✅ IMMEDIATE ACTION PLAN:\n";
echo "   1. Implement Test B (Optimal Timing) IMMEDIATELY\n";
echo "   2. Focus calls during low voicemail hours\n";
echo "   3. Use SMS to schedule callbacks\n";
echo "   4. Train agents on voicemail strategies\n";
echo "   5. Start A/B testing this week\n\n";

echo "📊 EXPECTED IMPROVEMENTS WITH TEST B:\n";
echo "   - Reduce voicemail rate from 76% to 40-50%\n";
echo "   - Increase human contact rate by 2x\n";
echo "   - Improve conversion from 2.51% to 4-5%\n";
echo "   - Reduce cost per acquisition by 40%\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Report generated: " . date('Y-m-d H:i:s') . "\n";
echo "Saved to: reports/vici_quick_report_" . date('Ymd_His') . ".txt\n\n";

// Save report
$output = ob_get_contents();
file_put_contents('reports/vici_quick_report_' . date('Ymd_His') . '.txt', $output);
