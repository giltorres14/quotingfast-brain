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

// Get date range
$firstCall = DB::table('orphan_call_logs')->min('call_date');
$lastCall = DB::table('orphan_call_logs')->max('call_date');

echo "Date Range: " . Carbon::parse($firstCall)->format('Y-m-d') . " to " . Carbon::parse($lastCall)->format('Y-m-d') . "\n";
echo "Total Calls: " . number_format($totalCalls) . "\n";
echo "Unique Leads: " . number_format($uniqueLeads) . "\n";
echo "Transfers (Sales): " . number_format($transfers) . "\n";
echo "Conversion Rate: " . number_format($conversionRate, 2) . "%\n\n";

// 2. DISPOSITION ANALYSIS
echo "📞 TOP DISPOSITIONS\n";
echo str_repeat("-", 70) . "\n";
$dispositions = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

printf("%-15s %10s %10s\n", "Status", "Count", "Percent");
printf("%-15s %10s %10s\n", str_repeat("-", 15), str_repeat("-", 10), str_repeat("-", 10));
foreach ($dispositions as $disp) {
    $percent = ($disp->count / $totalCalls) * 100;
    printf("%-15s %10s %9.2f%%\n", $disp->status, number_format($disp->count), $percent);
}

// 3. CALLS PER LEAD DISTRIBUTION
echo "\n📈 CALLS PER LEAD DISTRIBUTION\n";
echo str_repeat("-", 70) . "\n";
$callsPerLead = DB::table('orphan_call_logs')
    ->select(DB::raw('phone_number, COUNT(*) as call_count'))
    ->groupBy('phone_number')
    ->get();

$distribution = [
    '1 call' => 0,
    '2-5 calls' => 0,
    '6-10 calls' => 0,
    '11-20 calls' => 0,
    '21-30 calls' => 0,
    '31-40 calls' => 0,
    '41+ calls' => 0
];

$transfersByCallCount = [];
foreach ($callsPerLead as $lead) {
    // Check if this lead converted
    $hasTransfer = DB::table('orphan_call_logs')
        ->where('phone_number', $lead->phone_number)
        ->whereIn('status', ['XFER', 'XFERA'])
        ->exists();
    
    if ($lead->call_count == 1) {
        $distribution['1 call']++;
        if ($hasTransfer) $transfersByCallCount['1 call'] = ($transfersByCallCount['1 call'] ?? 0) + 1;
    } elseif ($lead->call_count <= 5) {
        $distribution['2-5 calls']++;
        if ($hasTransfer) $transfersByCallCount['2-5 calls'] = ($transfersByCallCount['2-5 calls'] ?? 0) + 1;
    } elseif ($lead->call_count <= 10) {
        $distribution['6-10 calls']++;
        if ($hasTransfer) $transfersByCallCount['6-10 calls'] = ($transfersByCallCount['6-10 calls'] ?? 0) + 1;
    } elseif ($lead->call_count <= 20) {
        $distribution['11-20 calls']++;
        if ($hasTransfer) $transfersByCallCount['11-20 calls'] = ($transfersByCallCount['11-20 calls'] ?? 0) + 1;
    } elseif ($lead->call_count <= 30) {
        $distribution['21-30 calls']++;
        if ($hasTransfer) $transfersByCallCount['21-30 calls'] = ($transfersByCallCount['21-30 calls'] ?? 0) + 1;
    } elseif ($lead->call_count <= 40) {
        $distribution['31-40 calls']++;
        if ($hasTransfer) $transfersByCallCount['31-40 calls'] = ($transfersByCallCount['31-40 calls'] ?? 0) + 1;
    } else {
        $distribution['41+ calls']++;
        if ($hasTransfer) $transfersByCallCount['41+ calls'] = ($transfersByCallCount['41+ calls'] ?? 0) + 1;
    }
}

printf("%-15s %10s %10s %12s %12s\n", "Call Range", "Leads", "Percent", "Transfers", "Conv Rate");
printf("%-15s %10s %10s %12s %12s\n", str_repeat("-", 15), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12), str_repeat("-", 12));
foreach ($distribution as $range => $count) {
    $percent = $uniqueLeads > 0 ? ($count / $uniqueLeads) * 100 : 0;
    $transfers = $transfersByCallCount[$range] ?? 0;
    $convRate = $count > 0 ? ($transfers / $count) * 100 : 0;
    printf("%-15s %10s %9.2f%% %12s %11.2f%%\n", 
        $range, 
        number_format($count), 
        $percent,
        number_format($transfers),
        $convRate
    );
}

// 4. HOURLY PERFORMANCE
echo "\n⏰ HOURLY PERFORMANCE (EST)\n";
echo str_repeat("-", 70) . "\n";
$hourlyData = DB::table('orphan_call_logs')
    ->selectRaw("
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as total_calls,
        SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers,
        SUM(CASE WHEN status NOT IN ('NA', 'A', 'B', 'N', 'ADC', 'PDROP', 'DROP') THEN 1 ELSE 0 END) as contacts
    ")
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

printf("%-8s %10s %10s %10s %12s\n", "Hour", "Calls", "Contacts", "Transfers", "Conv Rate");
printf("%-8s %10s %10s %10s %12s\n", str_repeat("-", 8), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 10), str_repeat("-", 12));
foreach ($hourlyData as $hour) {
    $convRate = $hour->contacts > 0 ? ($hour->transfers / $hour->contacts) * 100 : 0;
    printf("%02d:00-%02d:00 %10s %10s %10s %11.2f%%\n", 
        $hour->hour,
        ($hour->hour + 1) % 24,
        number_format($hour->total_calls),
        number_format($hour->contacts),
        number_format($hour->transfers),
        $convRate
    );
}

// 5. LIST PERFORMANCE
echo "\n📋 TOP PERFORMING LISTS\n";
echo str_repeat("-", 70) . "\n";
$lists = DB::table('orphan_call_logs')
    ->select('list_id',
        DB::raw('COUNT(*) as total_calls'),
        DB::raw('COUNT(DISTINCT phone_number) as unique_leads'),
        DB::raw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers')
    )
    ->whereNotNull('list_id')
    ->groupBy('list_id')
    ->having('unique_leads', '>', 100)  // Only lists with significant volume
    ->orderBy('transfers', 'desc')
    ->limit(15)
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

// 6. COST ANALYSIS
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
echo "Average Call Duration: " . number_format($totalSeconds / $totalCalls, 1) . " seconds\n";

// 7. AGENT PERFORMANCE
echo "\n👥 TOP 10 AGENTS BY TRANSFERS\n";
echo str_repeat("-", 70) . "\n";
$agents = DB::table('orphan_call_logs')
    ->select('agent_user',
        DB::raw('COUNT(*) as total_calls'),
        DB::raw('SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers'),
        DB::raw('AVG(length_in_sec) as avg_duration')
    )
    ->whereNotNull('agent_user')
    ->where('agent_user', '!=', '')
    ->groupBy('agent_user')
    ->having('total_calls', '>', 100)  // Only agents with significant volume
    ->orderBy('transfers', 'desc')
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

// 8. RECOMMENDATIONS
echo "\n💡 KEY INSIGHTS & RECOMMENDATIONS\n";
echo str_repeat("-", 70) . "\n";

// Calculate key insights
$avgCallsPerLead = $totalCalls / $uniqueLeads;
$leadsWithOneCall = $distribution['1 call'] ?? 0;
$oneCallPercent = ($leadsWithOneCall / $uniqueLeads) * 100;

echo "• Average calls per lead: " . number_format($avgCallsPerLead, 1) . "\n";
echo "• " . number_format($oneCallPercent, 1) . "% of leads only received 1 call attempt\n";
echo "• Current conversion rate: " . number_format($conversionRate, 2) . "%\n";
echo "• Cost per acquisition: $" . number_format($costPerTransfer, 2) . "\n\n";

if ($conversionRate < 3) {
    echo "⚠️  CONVERSION OPTIMIZATION NEEDED:\n";
    echo "   - Current " . number_format($conversionRate, 2) . "% is below industry average (3-5%)\n";
    echo "   - Implement Test B strategy: 12-15 strategic calls\n";
    echo "   - Focus on Golden Hour (first 60 minutes)\n";
    echo "   - Add SMS/Email touchpoints\n\n";
}

if ($oneCallPercent > 30) {
    echo "⚠️  PERSISTENCE ISSUE DETECTED:\n";
    echo "   - " . number_format($oneCallPercent, 1) . "% of leads only called once\n";
    echo "   - Implement minimum 3-call rule for all leads\n";
    echo "   - Add automated follow-up sequences\n\n";
}

echo "✅ NEXT STEPS:\n";
echo "   1. Activate A/B Testing (Test A vs Test B)\n";
echo "   2. Monitor conversion rates by list\n";
echo "   3. Optimize calling times based on hourly data\n";
echo "   4. Train agents with < 2% conversion rate\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Report generated: " . date('Y-m-d H:i:s') . "\n\n";












