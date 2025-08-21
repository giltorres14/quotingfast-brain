<?php
/**
 * Comprehensive Vici Call Log Analysis Reports
 * Analyzes all the metrics we've been discussing for A/B testing
 */

if (!isset($app)) {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(90);

echo "\n================================================================================\n";
echo "                    90-DAY CALL METRICS ANALYSIS                               \n";
echo "                    " . Carbon::now()->format('Y-m-d H:i:s EST') . "              \n";
echo "================================================================================\n\n";

// 1. OVERALL METRICS
$totalCalls = DB::table('vici_call_logs')->count();
$uniqueLeads = DB::table('vici_call_logs')->distinct('lead_id')->count('lead_id');
$avgCallsPerLead = $uniqueLeads > 0 ? round($totalCalls / $uniqueLeads, 1) : 0;

echo "📊 1. OVERALL METRICS\n";
echo "━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total Calls: " . number_format($totalCalls) . "\n";
echo "Unique Leads: " . number_format($uniqueLeads) . "\n";
echo "Avg Calls/Lead: " . $avgCallsPerLead . "\n\n";

// 2. STATUS DISTRIBUTION
echo "📈 2. CALL OUTCOME DISTRIBUTION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$statusDist = DB::table('vici_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->get();

$totalForPercentage = $statusDist->sum('count');
foreach ($statusDist as $status) {
    $percentage = $totalForPercentage > 0 ? round(($status->count / $totalForPercentage) * 100, 2) : 0;
    echo sprintf("%-10s: %7s calls (%5.2f%%)\n", 
        $status->status, 
        number_format($status->count), 
        $percentage
    );
}

// Calculate key rates
$connects = DB::table('vici_call_logs')->whereIn('status', ['SALE', 'XFER', 'NI', 'DNC'])->count();
$sales = DB::table('vici_call_logs')->whereIn('status', ['SALE', 'XFER'])->count();
$noAnswers = DB::table('vici_call_logs')->whereIn('status', ['NA', 'B', 'N'])->count();
$voicemails = DB::table('vici_call_logs')->whereIn('status', ['AL', 'AM', 'AA'])->count();

echo "\nKey Rates:\n";
echo "• Contact Rate: " . ($totalCalls > 0 ? round(($connects / $totalCalls) * 100, 2) : 0) . "%\n";
echo "• Conversion Rate: " . ($totalCalls > 0 ? round(($sales / $totalCalls) * 100, 2) : 0) . "%\n";
echo "• No Answer Rate: " . ($totalCalls > 0 ? round(($noAnswers / $totalCalls) * 100, 2) : 0) . "%\n";
echo "• Voicemail Rate: " . ($totalCalls > 0 ? round(($voicemails / $totalCalls) * 100, 2) : 0) . "%\n\n";

// 3. GOLDEN HOUR ANALYSIS (Speed to Lead)
echo "⏰ 3. GOLDEN HOUR ANALYSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Since we don't have lead creation times in vici_call_logs, we'll analyze by first call of the day
$firstCallAnalysis = DB::select("
    WITH first_calls AS (
        SELECT 
            lead_id,
            MIN(call_date) as first_call,
            DATE(MIN(call_date)) as call_day,
            EXTRACT(HOUR FROM MIN(call_date)) as first_hour
        FROM vici_call_logs
        GROUP BY lead_id
    ),
    conversions AS (
        SELECT DISTINCT lead_id
        FROM vici_call_logs
        WHERE status IN ('SALE', 'XFER')
    )
    SELECT 
        CASE 
            WHEN fc.first_hour BETWEEN 9 AND 10 THEN '9-10 AM'
            WHEN fc.first_hour BETWEEN 10 AND 11 THEN '10-11 AM'
            WHEN fc.first_hour BETWEEN 11 AND 12 THEN '11-12 PM'
            WHEN fc.first_hour BETWEEN 12 AND 14 THEN '12-2 PM'
            WHEN fc.first_hour BETWEEN 14 AND 16 THEN '2-4 PM'
            WHEN fc.first_hour BETWEEN 16 AND 18 THEN '4-6 PM'
            ELSE 'After Hours'
        END as time_bracket,
        COUNT(DISTINCT fc.lead_id) as leads,
        COUNT(DISTINCT c.lead_id) as conversions
    FROM first_calls fc
    LEFT JOIN conversions c ON fc.lead_id = c.lead_id
    GROUP BY time_bracket
    ORDER BY 
        CASE time_bracket
            WHEN '9-10 AM' THEN 1
            WHEN '10-11 AM' THEN 2
            WHEN '11-12 PM' THEN 3
            WHEN '12-2 PM' THEN 4
            WHEN '2-4 PM' THEN 5
            WHEN '4-6 PM' THEN 6
            ELSE 7
        END
");

echo "First Contact Time vs Conversion:\n";
foreach ($firstCallAnalysis as $bracket) {
    $convRate = $bracket->leads > 0 ? round(($bracket->conversions / $bracket->leads) * 100, 2) : 0;
    echo sprintf("%-12s: %5s leads, %3s sales (%5.2f%% conversion)\n",
        $bracket->time_bracket,
        number_format($bracket->leads),
        $bracket->conversions,
        $convRate
    );
}

// 4. CALL FREQUENCY ANALYSIS
echo "\n📞 4. CALL FREQUENCY PATTERNS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$callFrequency = DB::select("
    SELECT 
        CASE 
            WHEN call_count <= 5 THEN '1-5 calls'
            WHEN call_count <= 10 THEN '6-10 calls'
            WHEN call_count <= 20 THEN '11-20 calls'
            WHEN call_count <= 30 THEN '21-30 calls'
            WHEN call_count <= 40 THEN '31-40 calls'
            WHEN call_count <= 48 THEN '41-48 calls'
            ELSE '49+ calls'
        END as call_range,
        COUNT(*) as lead_count,
        SUM(converted) as conversions
    FROM (
        SELECT 
            lead_id,
            COUNT(*) as call_count,
            MAX(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as converted
        FROM vici_call_logs
        GROUP BY lead_id
    ) lead_calls
    GROUP BY call_range
    ORDER BY 
        CASE call_range
            WHEN '1-5 calls' THEN 1
            WHEN '6-10 calls' THEN 2
            WHEN '11-20 calls' THEN 3
            WHEN '21-30 calls' THEN 4
            WHEN '31-40 calls' THEN 5
            WHEN '41-48 calls' THEN 6
            ELSE 7
        END
");

echo "Leads by Call Attempts:\n";
foreach ($callFrequency as $freq) {
    $convRate = $freq->lead_count > 0 ? round(($freq->conversions / $freq->lead_count) * 100, 2) : 0;
    echo sprintf("%-12s: %6s leads, %4s sales (%5.2f%% conversion)\n",
        $freq->call_range,
        number_format($freq->lead_count),
        $freq->conversions,
        $convRate
    );
}

// 5. CONVERSION BY CALL NUMBER
echo "\n🎯 5. CONVERSION BY CALL NUMBER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$conversionByCall = DB::select("
    WITH numbered_calls AS (
        SELECT 
            lead_id,
            status,
            ROW_NUMBER() OVER (PARTITION BY lead_id ORDER BY call_date) as call_number
        FROM vici_call_logs
    )
    SELECT 
        call_number,
        COUNT(*) as attempts,
        SUM(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as conversions
    FROM numbered_calls
    WHERE call_number <= 20
    GROUP BY call_number
    ORDER BY call_number
");

echo "Call # | Attempts | Sales | Conv Rate\n";
echo "-------|----------|-------|----------\n";
foreach ($conversionByCall as $call) {
    $rate = $call->attempts > 0 ? round(($call->conversions / $call->attempts) * 100, 2) : 0;
    echo sprintf("%6d | %8s | %5s | %7.2f%%\n",
        $call->call_number,
        number_format($call->attempts),
        $call->conversions,
        $rate
    );
}

// 6. COST ANALYSIS
echo "\n💰 6. COST ANALYSIS ($0.004/min, 6-sec increments)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$costAnalysis = DB::table('vici_call_logs')
    ->selectRaw('
        SUM(CEIL(length_in_sec / 6.0) * 6) as billable_seconds,
        AVG(length_in_sec) as avg_duration,
        COUNT(*) as total_calls,
        COUNT(DISTINCT lead_id) as unique_leads
    ')
    ->first();

$billableMinutes = $costAnalysis->billable_seconds / 60;
$totalCost = $billableMinutes * 0.004;
$costPerLead = $costAnalysis->unique_leads > 0 ? $totalCost / $costAnalysis->unique_leads : 0;
$costPerCall = $costAnalysis->total_calls > 0 ? $totalCost / $costAnalysis->total_calls : 0;

echo "Total Billable Minutes: " . number_format($billableMinutes, 2) . "\n";
echo "Total Cost: $" . number_format($totalCost, 2) . "\n";
echo "Cost per Lead: $" . number_format($costPerLead, 4) . "\n";
echo "Cost per Call: $" . number_format($costPerCall, 4) . "\n";
echo "Avg Call Duration: " . round($costAnalysis->avg_duration) . " seconds\n\n";

// 7. A/B TEST SIMULATION
echo "🔬 7. A/B TEST SIMULATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Test A: Current approach (40+ calls)
$testA = DB::select("
    SELECT 
        COUNT(DISTINCT lead_id) as leads,
        SUM(converted) as conversions,
        SUM(total_seconds) as total_seconds
    FROM (
        SELECT 
            lead_id,
            MAX(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as converted,
            SUM(CEIL(length_in_sec / 6.0) * 6) as total_seconds,
            COUNT(*) as call_count
        FROM vici_call_logs
        GROUP BY lead_id
        HAVING COUNT(*) >= 40
    ) high_touch_leads
")[0];

// Test B: Strategic approach (15-20 calls)
$testB = DB::select("
    SELECT 
        COUNT(DISTINCT lead_id) as leads,
        SUM(converted) as conversions,
        SUM(total_seconds) as total_seconds
    FROM (
        SELECT 
            lead_id,
            MAX(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as converted,
            SUM(CEIL(length_in_sec / 6.0) * 6) as total_seconds,
            COUNT(*) as call_count
        FROM vici_call_logs
        GROUP BY lead_id
        HAVING COUNT(*) BETWEEN 15 AND 20
    ) strategic_leads
")[0];

$testACost = ($testA->total_seconds / 60) * 0.004;
$testBCost = ($testB->total_seconds / 60) * 0.004;

echo "TEST A - Current (40+ calls):\n";
echo "  Leads: " . number_format($testA->leads) . "\n";
echo "  Conversions: " . $testA->conversions . "\n";
echo "  Conversion Rate: " . ($testA->leads > 0 ? round(($testA->conversions / $testA->leads) * 100, 2) : 0) . "%\n";
echo "  Total Cost: $" . number_format($testACost, 2) . "\n";
echo "  Cost per Lead: $" . ($testA->leads > 0 ? number_format($testACost / $testA->leads, 4) : '0') . "\n";
echo "  Cost per Sale: $" . ($testA->conversions > 0 ? number_format($testACost / $testA->conversions, 2) : 'N/A') . "\n\n";

echo "TEST B - Strategic (15-20 calls):\n";
echo "  Leads: " . number_format($testB->leads) . "\n";
echo "  Conversions: " . $testB->conversions . "\n";
echo "  Conversion Rate: " . ($testB->leads > 0 ? round(($testB->conversions / $testB->leads) * 100, 2) : 0) . "%\n";
echo "  Total Cost: $" . number_format($testBCost, 2) . "\n";
echo "  Cost per Lead: $" . ($testB->leads > 0 ? number_format($testBCost / $testB->leads, 4) : '0') . "\n";
echo "  Cost per Sale: $" . ($testB->conversions > 0 ? number_format($testBCost / $testB->conversions, 2) : 'N/A') . "\n\n";

// 8. LATE-DAY LEAD PERFORMANCE
echo "🌆 8. LATE-DAY LEAD PERFORMANCE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$lateDayAnalysis = DB::select("
    WITH first_calls AS (
        SELECT 
            lead_id,
            MIN(call_date) as first_call,
            EXTRACT(HOUR FROM MIN(call_date)) as first_hour
        FROM vici_call_logs
        GROUP BY lead_id
    ),
    lead_outcomes AS (
        SELECT 
            lead_id,
            MAX(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as converted,
            COUNT(*) as total_calls
        FROM vici_call_logs
        GROUP BY lead_id
    )
    SELECT 
        CASE 
            WHEN fc.first_hour < 14 THEN 'Morning (9am-2pm)'
            WHEN fc.first_hour < 16 THEN 'Afternoon (2-4pm)'
            WHEN fc.first_hour < 18 THEN 'Late Day (4-6pm)'
            ELSE 'After Hours'
        END as arrival_time,
        COUNT(DISTINCT fc.lead_id) as leads,
        SUM(lo.converted) as conversions,
        AVG(lo.total_calls) as avg_calls
    FROM first_calls fc
    JOIN lead_outcomes lo ON fc.lead_id = lo.lead_id
    GROUP BY arrival_time
    ORDER BY 
        CASE arrival_time
            WHEN 'Morning (9am-2pm)' THEN 1
            WHEN 'Afternoon (2-4pm)' THEN 2
            WHEN 'Late Day (4-6pm)' THEN 3
            ELSE 4
        END
");

foreach ($lateDayAnalysis as $timeSlot) {
    $convRate = $timeSlot->leads > 0 ? round(($timeSlot->conversions / $timeSlot->leads) * 100, 2) : 0;
    echo sprintf("%-20s: %5s leads, %5.2f%% conv, %.1f avg calls\n",
        $timeSlot->arrival_time,
        number_format($timeSlot->leads),
        $convRate,
        $timeSlot->avg_calls
    );
}

// 9. KEY INSIGHTS & RECOMMENDATIONS
echo "\n================================================================================\n";
echo "                    KEY INSIGHTS & RECOMMENDATIONS                              \n";
echo "================================================================================\n\n";

// Find peak conversion call number
$peakCall = 1;
$peakRate = 0;
foreach ($conversionByCall as $call) {
    $rate = $call->attempts > 0 ? ($call->conversions / $call->attempts) * 100 : 0;
    if ($rate > $peakRate) {
        $peakRate = $rate;
        $peakCall = $call->call_number;
    }
}

echo "🎯 CONVERSION INSIGHTS:\n";
echo "• Peak conversion at call #" . $peakCall . " (" . round($peakRate, 2) . "% rate)\n";
echo "• Overall conversion rate: " . ($totalCalls > 0 ? round(($sales / $totalCalls) * 100, 2) : 0) . "%\n";
echo "• Contact rate: " . ($totalCalls > 0 ? round(($connects / $totalCalls) * 100, 2) : 0) . "%\n\n";

echo "💰 COST EFFICIENCY:\n";
echo "• Current cost per lead: $" . number_format($costPerLead, 4) . "\n";
echo "• Average " . $avgCallsPerLead . " calls per lead\n";
echo "• Test A (40+ calls) costs " . ($testB->leads > 0 && $testA->leads > 0 ? 
    round(($testACost/$testA->leads) / ($testBCost/$testB->leads), 1) : 'N/A') . "x more than Test B\n\n";

echo "⏰ TIMING RECOMMENDATIONS:\n";
$morningData = array_filter($lateDayAnalysis, fn($d) => strpos($d->arrival_time, '9am-2pm') !== false);
$lateData = array_filter($lateDayAnalysis, fn($d) => strpos($d->arrival_time, '4-6pm') !== false);

if (!empty($morningData) && !empty($lateData)) {
    $morning = reset($morningData);
    $late = reset($lateData);
    $morningConv = $morning->leads > 0 ? ($morning->conversions / $morning->leads) * 100 : 0;
    $lateConv = $late->leads > 0 ? ($late->conversions / $late->leads) * 100 : 0;
    
    if ($lateConv >= $morningConv * 0.8) {
        echo "• Late-day leads (4-6pm) perform well - use Speed Priority strategy\n";
    } else {
        echo "• Late-day leads underperform - consider next-day priority approach\n";
    }
}

echo "• Optimal calling window based on data: ";
$bestTime = reset($firstCallAnalysis);
foreach ($firstCallAnalysis as $time) {
    if ($time->leads > 0 && $time->conversions > 0) {
        $rate = ($time->conversions / $time->leads) * 100;
        if ($rate > ($bestTime->conversions / max($bestTime->leads, 1)) * 100) {
            $bestTime = $time;
        }
    }
}
echo $bestTime->time_bracket . "\n\n";

echo "📊 A/B TEST RECOMMENDATION:\n";
if ($testA->conversions > 0 && $testB->conversions > 0) {
    $testAConvRate = ($testA->conversions / max($testA->leads, 1)) * 100;
    $testBConvRate = ($testB->conversions / max($testB->leads, 1)) * 100;
    
    if ($testAConvRate > $testBConvRate * 1.5) {
        echo "• Current high-touch approach (40+ calls) shows strong results\n";
        echo "• Continue Test A but monitor cost efficiency\n";
    } else {
        echo "• Strategic approach (15-20 calls) offers better ROI\n";
        echo "• Implement Test B with golden hour focus\n";
    }
} else {
    echo "• Insufficient conversion data for definitive recommendation\n";
    echo "• Suggest running controlled A/B test for 30 days\n";
}

echo "\n================================================================================\n";
echo "                           END OF ANALYSIS                                      \n";
echo "================================================================================\n\n";




