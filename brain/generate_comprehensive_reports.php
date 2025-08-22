<?php
/**
 * Generate comprehensive reports from imported call logs
 * Analyzes 1.3M call records to provide actionable insights
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "================================================================================\n";
echo "                     90-DAY CALL LOG ANALYSIS REPORT                          \n";
echo "================================================================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// 1. OVERALL METRICS
echo "📊 OVERALL METRICS\n";
echo str_repeat("-", 80) . "\n";

$overallStats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total_calls,
        COUNT(DISTINCT phone_number) as unique_leads,
        COUNT(DISTINCT DATE(call_date)) as days_with_calls,
        MIN(call_date) as first_call,
        MAX(call_date) as last_call,
        AVG(length_in_sec) as avg_call_length,
        MAX(length_in_sec) as longest_call,
        COUNT(CASE WHEN length_in_sec > 120 THEN 1 END) as calls_over_2min,
        COUNT(CASE WHEN status IN (\'SALE\', \'QCPASS\', \'QCFAIL\') THEN 1 END) as sales_calls
    ')
    ->first();

echo "• Total Calls: " . number_format($overallStats->total_calls) . "\n";
echo "• Unique Leads Called: " . number_format($overallStats->unique_leads) . "\n";
echo "• Days with Activity: " . $overallStats->days_with_calls . "\n";
echo "• Date Range: " . substr($overallStats->first_call, 0, 10) . " to " . substr($overallStats->last_call, 0, 10) . "\n";
echo "• Avg Call Length: " . round($overallStats->avg_call_length) . " seconds\n";
echo "• Longest Call: " . round($overallStats->longest_call / 60) . " minutes\n";
echo "• Calls > 2 minutes: " . number_format($overallStats->calls_over_2min) . " (" . 
     round(($overallStats->calls_over_2min / $overallStats->total_calls) * 100, 1) . "%)\n";
echo "• Sales-related calls: " . number_format($overallStats->sales_calls) . "\n\n";

// 2. DISPOSITION ANALYSIS
echo "📋 TOP DISPOSITIONS\n";
echo str_repeat("-", 80) . "\n";

$dispositions = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(15)
    ->get();

foreach ($dispositions as $disp) {
    $pct = round(($disp->count / $overallStats->total_calls) * 100, 1);
    echo sprintf("%-15s %8s calls (%5.1f%%)\n", 
        $disp->status ?: 'UNKNOWN', 
        number_format($disp->count), 
        $pct
    );
}
echo "\n";

// 3. CONTACT RATE ANALYSIS
echo "📞 CONTACT RATE ANALYSIS\n";
echo str_repeat("-", 80) . "\n";

$contactStats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(CASE WHEN status IN (\'NA\', \'B\', \'DC\', \'N\', \'A\', \'AA\', \'AB\', \'ADC\') THEN 1 END) as no_answer,
        COUNT(CASE WHEN status IN (\'VM\', \'AM\') THEN 1 END) as voicemail,
        COUNT(CASE WHEN status IN (\'NI\', \'DNC\', \'DNCL\') THEN 1 END) as not_interested,
        COUNT(CASE WHEN status IN (\'SALE\', \'QCPASS\', \'QCFAIL\') THEN 1 END) as sales,
        COUNT(CASE WHEN status IN (\'CALLBK\', \'CBK\') THEN 1 END) as callbacks,
        COUNT(CASE WHEN length_in_sec > 30 THEN 1 END) as connected_calls
    ')
    ->first();

$totalCalls = $overallStats->total_calls;
echo "• No Answer/Busy: " . number_format($contactStats->no_answer) . " (" . 
     round(($contactStats->no_answer / $totalCalls) * 100, 1) . "%)\n";
echo "• Voicemail: " . number_format($contactStats->voicemail) . " (" . 
     round(($contactStats->voicemail / $totalCalls) * 100, 1) . "%)\n";
echo "• Not Interested: " . number_format($contactStats->not_interested) . " (" . 
     round(($contactStats->not_interested / $totalCalls) * 100, 1) . "%)\n";
echo "• Sales/Qualified: " . number_format($contactStats->sales) . " (" . 
     round(($contactStats->sales / $totalCalls) * 100, 1) . "%)\n";
echo "• Callbacks: " . number_format($contactStats->callbacks) . " (" . 
     round(($contactStats->callbacks / $totalCalls) * 100, 1) . "%)\n";
echo "• Connected (>30 sec): " . number_format($contactStats->connected_calls) . " (" . 
     round(($contactStats->connected_calls / $totalCalls) * 100, 1) . "%)\n\n";

// 4. CALL FREQUENCY ANALYSIS
echo "📈 CALL FREQUENCY PATTERNS\n";
echo str_repeat("-", 80) . "\n";

$callPatterns = DB::table('orphan_call_logs')
    ->select('phone_number', DB::raw('COUNT(*) as call_count'))
    ->groupBy('phone_number')
    ->get();

$frequencies = [
    '1' => 0,
    '2-3' => 0,
    '4-5' => 0,
    '6-10' => 0,
    '11-20' => 0,
    '21-30' => 0,
    '31-50' => 0,
    '50+' => 0
];

foreach ($callPatterns as $pattern) {
    $count = $pattern->call_count;
    if ($count == 1) $frequencies['1']++;
    elseif ($count <= 3) $frequencies['2-3']++;
    elseif ($count <= 5) $frequencies['4-5']++;
    elseif ($count <= 10) $frequencies['6-10']++;
    elseif ($count <= 20) $frequencies['11-20']++;
    elseif ($count <= 30) $frequencies['21-30']++;
    elseif ($count <= 50) $frequencies['31-50']++;
    else $frequencies['50+']++;
}

$totalLeads = $overallStats->unique_leads;
foreach ($frequencies as $range => $count) {
    $pct = round(($count / $totalLeads) * 100, 1);
    echo sprintf("• %6s calls: %8s leads (%5.1f%%)\n", $range, number_format($count), $pct);
}
echo "\n";

// 5. TIME-BASED ANALYSIS
echo "⏰ BEST CALLING TIMES\n";
echo str_repeat("-", 80) . "\n";

$hourlyStats = DB::table('orphan_call_logs')
    ->selectRaw('
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as calls,
        COUNT(CASE WHEN length_in_sec > 30 THEN 1 END) as connected,
        COUNT(CASE WHEN status IN (\'SALE\', \'QCPASS\') THEN 1 END) as sales
    ')
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

echo "Hour  | Total Calls | Connected | Connect Rate | Sales\n";
echo "------|-------------|-----------|--------------|-------\n";
foreach ($hourlyStats as $hour) {
    if ($hour->hour >= 9 && $hour->hour <= 18) { // Business hours only
        $connectRate = $hour->calls > 0 ? round(($hour->connected / $hour->calls) * 100, 1) : 0;
        echo sprintf("%2d:00 | %11s | %9s | %11.1f%% | %5s\n",
            $hour->hour,
            number_format($hour->calls),
            number_format($hour->connected),
            $connectRate,
            number_format($hour->sales)
        );
    }
}
echo "\n";

// 6. AGENT PERFORMANCE
echo "👥 TOP AGENTS BY VOLUME\n";
echo str_repeat("-", 80) . "\n";

$agentStats = DB::table('orphan_call_logs')
    ->select('agent_id', DB::raw('
        COUNT(*) as total_calls,
        AVG(length_in_sec) as avg_talk_time,
        COUNT(CASE WHEN status IN (\'SALE\', \'QCPASS\') THEN 1 END) as sales
    '))
    ->whereNotNull('agent_id')
    ->where('agent_id', '!=', '')
    ->groupBy('agent_id')
    ->orderBy('total_calls', 'desc')
    ->limit(10)
    ->get();

echo "Agent      | Calls  | Avg Talk Time | Sales | Conv Rate\n";
echo "-----------|--------|---------------|-------|----------\n";
foreach ($agentStats as $agent) {
    $convRate = $agent->total_calls > 0 ? round(($agent->sales / $agent->total_calls) * 100, 1) : 0;
    echo sprintf("%-10s | %6s | %13s | %5s | %8.1f%%\n",
        substr($agent->agent_id, 0, 10),
        number_format($agent->total_calls),
        round($agent->avg_talk_time) . "s",
        number_format($agent->sales),
        $convRate
    );
}
echo "\n";

// 7. CAMPAIGN PERFORMANCE
echo "🎯 CAMPAIGN BREAKDOWN\n";
echo str_repeat("-", 80) . "\n";

$campaignStats = DB::table('orphan_call_logs')
    ->select('campaign_id', DB::raw('
        COUNT(*) as calls,
        COUNT(DISTINCT phone_number) as unique_leads,
        AVG(length_in_sec) as avg_length,
        COUNT(CASE WHEN status IN (\'SALE\', \'QCPASS\') THEN 1 END) as sales
    '))
    ->whereNotNull('campaign_id')
    ->groupBy('campaign_id')
    ->get();

foreach ($campaignStats as $campaign) {
    echo "Campaign: " . ($campaign->campaign_id ?: 'UNKNOWN') . "\n";
    echo "  • Calls: " . number_format($campaign->calls) . "\n";
    echo "  • Unique Leads: " . number_format($campaign->unique_leads) . "\n";
    echo "  • Avg Call Length: " . round($campaign->avg_length) . " seconds\n";
    echo "  • Sales: " . number_format($campaign->sales) . "\n";
    echo "  • Conversion: " . round(($campaign->sales / $campaign->calls) * 100, 2) . "%\n\n";
}

// 8. LIST PERFORMANCE
echo "📋 LIST PERFORMANCE\n";
echo str_repeat("-", 80) . "\n";

$listStats = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('
        COUNT(*) as calls,
        COUNT(DISTINCT phone_number) as leads,
        AVG(length_in_sec) as avg_length,
        COUNT(CASE WHEN length_in_sec > 30 THEN 1 END) as connected
    '))
    ->whereNotNull('list_id')
    ->where('list_id', '>', 0)
    ->groupBy('list_id')
    ->orderBy('calls', 'desc')
    ->limit(15)
    ->get();

echo "List ID | Calls   | Leads  | Avg Length | Connect Rate\n";
echo "--------|---------|--------|------------|-------------\n";
foreach ($listStats as $list) {
    $connectRate = $list->calls > 0 ? round(($list->connected / $list->calls) * 100, 1) : 0;
    echo sprintf("%7d | %7s | %6s | %10s | %11.1f%%\n",
        $list->list_id,
        number_format($list->calls),
        number_format($list->leads),
        round($list->avg_length) . "s",
        $connectRate
    );
}
echo "\n";

// 9. KEY INSIGHTS & RECOMMENDATIONS
echo "💡 KEY INSIGHTS & RECOMMENDATIONS\n";
echo str_repeat("=", 80) . "\n\n";

// Calculate key metrics for insights
$avgCallsPerLead = $overallStats->total_calls / $overallStats->unique_leads;
$overallConnectRate = ($contactStats->connected_calls / $overallStats->total_calls) * 100;
$overallConvRate = ($contactStats->sales / $overallStats->total_calls) * 100;

echo "📊 CURRENT PERFORMANCE:\n";
echo "• Average calls per lead: " . round($avgCallsPerLead, 1) . "\n";
echo "• Overall connect rate: " . round($overallConnectRate, 1) . "%\n";
echo "• Overall conversion rate: " . round($overallConvRate, 2) . "%\n";
echo "• Not Interested rate: " . round(($contactStats->not_interested / $totalCalls) * 100, 1) . "%\n\n";

echo "🎯 RECOMMENDATIONS:\n";

// Recommendation 1: Call frequency
if ($avgCallsPerLead > 15) {
    echo "1. REDUCE CALL FREQUENCY:\n";
    echo "   Current avg of " . round($avgCallsPerLead, 1) . " calls/lead is too high.\n";
    echo "   → Recommend: Max 10-12 calls per lead\n";
    echo "   → Focus on first 48 hours (Golden Hour strategy)\n\n";
} else {
    echo "1. CALL FREQUENCY: ✅ Good (" . round($avgCallsPerLead, 1) . " calls/lead)\n\n";
}

// Recommendation 2: Connect rate
if ($overallConnectRate < 15) {
    echo "2. IMPROVE CONNECT RATE:\n";
    echo "   Current " . round($overallConnectRate, 1) . "% is below industry average (15-20%)\n";
    echo "   → Implement local presence dialing\n";
    echo "   → Focus on best calling hours (10am-12pm, 2pm-4pm)\n";
    echo "   → Add SMS pre-notification\n\n";
} else {
    echo "2. CONNECT RATE: ✅ Good (" . round($overallConnectRate, 1) . "%)\n\n";
}

// Recommendation 3: Not Interested handling
$niRate = ($contactStats->not_interested / $totalCalls) * 100;
if ($niRate > 5) {
    echo "3. HIGH NOT INTERESTED RATE:\n";
    echo "   " . round($niRate, 1) . "% marked as Not Interested\n";
    echo "   → Implement NI retargeting campaign\n";
    echo "   → Use different script/approach after 30 days\n";
    echo "   → Consider rate reduction messaging\n\n";
}

// Recommendation 4: Best times
$bestHour = $hourlyStats->sortByDesc(function($h) {
    return $h->calls > 0 ? ($h->connected / $h->calls) : 0;
})->first();

echo "4. OPTIMAL CALLING TIMES:\n";
echo "   Best connect rate at " . $bestHour->hour . ":00 (" . 
     round(($bestHour->connected / $bestHour->calls) * 100, 1) . "%)\n";
echo "   → Prioritize calls between 10am-12pm and 2pm-4pm\n";
echo "   → Reduce evening calls (lower connect rates)\n\n";

// Recommendation 5: Lead flow
$highCallLeads = DB::table('orphan_call_logs')
    ->select('phone_number')
    ->groupBy('phone_number')
    ->havingRaw('COUNT(*) > 30')
    ->count();

if ($highCallLeads > 100) {
    echo "5. EXCESSIVE CALLING DETECTED:\n";
    echo "   " . number_format($highCallLeads) . " leads called 30+ times\n";
    echo "   → Implement strict call caps\n";
    echo "   → Move to email/SMS after 10 attempts\n";
    echo "   → Flag for manual review\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "Report generated: " . date('Y-m-d H:i:s') . "\n\n";

// Save report to file
$reportContent = ob_get_contents();
file_put_contents(__DIR__ . '/reports/90_day_analysis_' . date('Ymd_His') . '.txt', $reportContent);
echo "Report saved to: reports/90_day_analysis_" . date('Ymd_His') . ".txt\n";





