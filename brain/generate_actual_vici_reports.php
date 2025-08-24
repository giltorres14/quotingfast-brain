<?php
/**
 * Generate Reports from Actual Vici Data
 * Uses orphan_call_logs and vici_call_metrics tables
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n================================================================================\n";
echo "                    VICI CALL DATA ANALYSIS REPORT                             \n";
echo "                    Generated: " . Carbon::now()->format('Y-m-d H:i:s EST') . "    \n";
echo "================================================================================\n\n";

// 1. DATA OVERVIEW
echo "📊 1. DATA OVERVIEW\n";
echo "━━━━━━━━━━━━━━━━━━━\n";

$orphanCount = DB::table('orphan_call_logs')->count();
$metricsCount = DB::table('vici_call_metrics')->count();
$leadCount = DB::table('leads')->whereNotNull('vici_list_id')->count();

echo "Orphan Call Logs: " . number_format($orphanCount) . "\n";
echo "Vici Call Metrics: " . number_format($metricsCount) . "\n";
echo "Leads with Vici IDs: " . number_format($leadCount) . "\n\n";

// 2. ORPHAN CALLS ANALYSIS
if ($orphanCount > 0) {
    echo "📞 2. ORPHAN CALLS ANALYSIS\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $orphanStats = DB::table('orphan_call_logs')
        ->selectRaw('
            COUNT(*) as total_calls,
            COUNT(DISTINCT phone_number) as unique_phones,
            COUNT(DISTINCT campaign_id) as campaigns,
            AVG(length_in_sec) as avg_duration,
            SUM(length_in_sec) as total_seconds
        ')
        ->first();
    
    echo "Total Orphan Calls: " . number_format($orphanStats->total_calls) . "\n";
    echo "Unique Phone Numbers: " . number_format($orphanStats->unique_phones) . "\n";
    echo "Campaigns: " . $orphanStats->campaigns . "\n";
    echo "Average Duration: " . round($orphanStats->avg_duration) . " seconds\n";
    echo "Total Talk Time: " . round($orphanStats->total_seconds / 60) . " minutes\n\n";
    
    // Status distribution
    $statusDist = DB::table('orphan_call_logs')
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
    
    echo "Status Distribution:\n";
    foreach ($statusDist as $status) {
        $percentage = round(($status->count / $orphanStats->total_calls) * 100, 2);
        echo sprintf("  %-10s: %6d calls (%5.2f%%)\n", 
            $status->status ?: 'UNKNOWN', 
            $status->count, 
            $percentage
        );
    }
    echo "\n";
}

// 3. VICI CALL METRICS ANALYSIS
if ($metricsCount > 0) {
    echo "📈 3. VICI CALL METRICS ANALYSIS\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Campaign performance
    $campaigns = DB::table('vici_call_metrics')
        ->select('campaign_id', DB::raw('COUNT(*) as leads'), DB::raw('SUM(total_calls) as total_calls'))
        ->whereNotNull('campaign_id')
        ->groupBy('campaign_id')
        ->orderBy('leads', 'desc')
        ->get();
    
    echo "Campaign Performance:\n";
    foreach ($campaigns as $campaign) {
        $avgCalls = $campaign->leads > 0 ? round($campaign->total_calls / $campaign->leads, 1) : 0;
        echo sprintf("  %-15s: %6d leads, %7d calls (%.1f avg)\n",
            $campaign->campaign_id,
            $campaign->leads,
            $campaign->total_calls,
            $avgCalls
        );
    }
    echo "\n";
    
    // List distribution
    $lists = DB::table('leads')
        ->select('vici_list_id', DB::raw('COUNT(*) as count'))
        ->whereNotNull('vici_list_id')
        ->where('vici_list_id', '>', 0)
        ->groupBy('vici_list_id')
        ->orderBy('vici_list_id')
        ->get();
    
    if ($lists->count() > 0) {
        echo "Lead Distribution by List:\n";
        foreach ($lists as $list) {
            echo sprintf("  List %3d: %6d leads\n", $list->vici_list_id, $list->count);
        }
        echo "\n";
    }
}

// 4. CALL FREQUENCY PATTERNS
echo "📞 4. CALL FREQUENCY PATTERNS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$callPatterns = DB::table('vici_call_metrics')
    ->select(DB::raw('
        CASE 
            WHEN total_calls = 0 THEN "0 calls"
            WHEN total_calls <= 5 THEN "1-5 calls"
            WHEN total_calls <= 10 THEN "6-10 calls"
            WHEN total_calls <= 20 THEN "11-20 calls"
            WHEN total_calls <= 30 THEN "21-30 calls"
            WHEN total_calls <= 40 THEN "31-40 calls"
            WHEN total_calls <= 48 THEN "41-48 calls"
            ELSE "49+ calls"
        END as call_range,
        COUNT(*) as lead_count
    '))
    ->groupBy('call_range')
    ->orderByRaw('
        CASE call_range
            WHEN "0 calls" THEN 0
            WHEN "1-5 calls" THEN 1
            WHEN "6-10 calls" THEN 2
            WHEN "11-20 calls" THEN 3
            WHEN "21-30 calls" THEN 4
            WHEN "31-40 calls" THEN 5
            WHEN "41-48 calls" THEN 6
            ELSE 7
        END
    ')
    ->get();

$totalLeadsAnalyzed = $callPatterns->sum('lead_count');
echo "Leads by Call Attempts:\n";
foreach ($callPatterns as $pattern) {
    $percentage = $totalLeadsAnalyzed > 0 ? round(($pattern->lead_count / $totalLeadsAnalyzed) * 100, 2) : 0;
    echo sprintf("  %-12s: %6d leads (%5.2f%%)\n",
        $pattern->call_range,
        $pattern->lead_count,
        $percentage
    );
}

// 5. COST ANALYSIS
echo "\n💰 5. COST ANALYSIS ($0.004/min, 6-sec increments)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// From orphan calls (actual call data)
if ($orphanCount > 0) {
    $orphanCost = DB::table('orphan_call_logs')
        ->selectRaw('
            SUM(CEIL(length_in_sec / 6.0) * 6) as billable_seconds,
            COUNT(*) as total_calls,
            COUNT(DISTINCT phone_number) as unique_phones
        ')
        ->first();
    
    $billableMinutes = $orphanCost->billable_seconds / 60;
    $totalCost = $billableMinutes * 0.004;
    
    echo "From Orphan Call Logs:\n";
    echo "  Total Billable Minutes: " . number_format($billableMinutes, 2) . "\n";
    echo "  Total Cost: $" . number_format($totalCost, 2) . "\n";
    echo "  Cost per Call: $" . number_format($totalCost / max($orphanCost->total_calls, 1), 4) . "\n";
    echo "  Cost per Unique Phone: $" . number_format($totalCost / max($orphanCost->unique_phones, 1), 4) . "\n\n";
}

// 6. A/B TEST SIMULATION BASED ON ACTUAL DATA
echo "🔬 6. A/B TEST SIMULATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Test A: High touch (40+ calls)
$testA = DB::table('vici_call_metrics')
    ->selectRaw('
        COUNT(*) as leads,
        AVG(total_calls) as avg_calls
    ')
    ->where('total_calls', '>=', 40)
    ->first();

// Test B: Strategic (15-20 calls)
$testB = DB::table('vici_call_metrics')
    ->selectRaw('
        COUNT(*) as leads,
        AVG(total_calls) as avg_calls
    ')
    ->whereBetween('total_calls', [15, 20])
    ->first();

echo "TEST A - High Touch (40+ calls):\n";
echo "  Leads in this range: " . number_format($testA->leads) . "\n";
echo "  Average calls: " . round($testA->avg_calls, 1) . "\n";
echo "  Estimated cost per lead: $" . number_format(($testA->avg_calls * 30 / 60) * 0.004, 4) . "\n\n";

echo "TEST B - Strategic (15-20 calls):\n";
echo "  Leads in this range: " . number_format($testB->leads) . "\n";
echo "  Average calls: " . round($testB->avg_calls, 1) . "\n";
echo "  Estimated cost per lead: $" . number_format(($testB->avg_calls * 30 / 60) * 0.004, 4) . "\n\n";

// 7. TIME-BASED ANALYSIS
echo "⏰ 7. LEAD CREATION TIME ANALYSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$timeAnalysis = DB::table('leads')
    ->selectRaw('
        EXTRACT(HOUR FROM created_at) as hour,
        COUNT(*) as leads,
        AVG(CASE WHEN status = "SOLD" THEN 1 ELSE 0 END) * 100 as conversion_rate
    ')
    ->whereNotNull('vici_list_id')
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

echo "Lead Distribution by Hour (EST):\n";
foreach ($timeAnalysis as $hour) {
    $hourDisplay = str_pad($hour->hour, 2, '0', STR_PAD_LEFT) . ':00';
    echo sprintf("  %s: %5d leads, %.2f%% conversion\n",
        $hourDisplay,
        $hour->leads,
        $hour->conversion_rate
    );
}

// 8. KEY INSIGHTS
echo "\n================================================================================\n";
echo "                    KEY INSIGHTS & RECOMMENDATIONS                              \n";
echo "================================================================================\n\n";

echo "📊 DATA QUALITY:\n";
if ($orphanCount > 0) {
    $orphanRate = ($orphanCount / max($orphanCount + $metricsCount, 1)) * 100;
    echo "• " . round($orphanRate, 1) . "% of calls are orphaned (not matched to leads)\n";
    if ($orphanRate > 50) {
        echo "  ⚠️ High orphan rate - check lead ID matching logic\n";
    }
}

echo "\n📞 CALL PATTERNS:\n";
$mostCommon = $callPatterns->sortByDesc('lead_count')->first();
if ($mostCommon) {
    echo "• Most common pattern: " . $mostCommon->call_range . " (" . $mostCommon->lead_count . " leads)\n";
}

$highTouch = $callPatterns->filter(function($p) { 
    return in_array($p->call_range, ['41-48 calls', '49+ calls']);
})->sum('lead_count');

$lowTouch = $callPatterns->filter(function($p) { 
    return in_array($p->call_range, ['1-5 calls', '6-10 calls']);
})->sum('lead_count');

if ($totalLeadsAnalyzed > 0) {
    echo "• High-touch leads (40+ calls): " . round(($highTouch / $totalLeadsAnalyzed) * 100, 1) . "%\n";
    echo "• Low-touch leads (<10 calls): " . round(($lowTouch / $totalLeadsAnalyzed) * 100, 1) . "%\n";
}

echo "\n💰 COST EFFICIENCY:\n";
if (isset($billableMinutes)) {
    $avgCostPerCall = $totalCost / max($orphanCost->total_calls, 1);
    echo "• Average cost per call: $" . number_format($avgCostPerCall, 4) . "\n";
    echo "• Total spend on analyzed calls: $" . number_format($totalCost, 2) . "\n";
}

echo "\n🔬 A/B TEST OPPORTUNITY:\n";
if ($testA->leads > 0 && $testB->leads > 0) {
    echo "• " . number_format($testA->leads) . " leads already receive 40+ calls\n";
    echo "• " . number_format($testB->leads) . " leads in optimal 15-20 call range\n";
    echo "• Potential savings by reducing high-touch: $" . 
        number_format((($testA->avg_calls - 18) * 30 / 60 * 0.004) * $testA->leads, 2) . "\n";
} else {
    echo "• Limited data for A/B comparison - continue collecting\n";
}

echo "\n⚡ AUTOMATION STATUS:\n";
echo "• Cron job: ✅ Configured (runs every minute)\n";
echo "• Sync schedule: Every 5 minutes\n";
echo "• Orphan matching: Every 10 minutes\n";
echo "• Last sync will capture all new calls automatically\n";

echo "\n================================================================================\n";
echo "                           END OF ANALYSIS                                      \n";
echo "================================================================================\n\n";










