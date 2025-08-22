<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "================================================================================\n";
echo "                    90-DAY CALL METRICS ANALYSIS                               \n";
echo "                    " . Carbon::now()->format('Y-m-d H:i:s') . "                \n";
echo "================================================================================\n\n";

$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(90);

echo "Analysis Period: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n\n";

// 1. OVERALL CALL VOLUME
echo "1. OVERALL CALL VOLUME\n";
echo "----------------------\n";

$totalCalls = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->count();

$uniqueLeads = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->distinct('lead_id')
    ->count('lead_id');

$avgCallsPerLead = $uniqueLeads > 0 ? round($totalCalls / $uniqueLeads, 1) : 0;

echo "Total Calls Made: " . number_format($totalCalls) . "\n";
echo "Unique Leads Called: " . number_format($uniqueLeads) . "\n";
echo "Average Calls per Lead: " . $avgCallsPerLead . "\n\n";

// 2. CALL OUTCOME DISTRIBUTION
echo "2. CALL OUTCOME DISTRIBUTION\n";
echo "-----------------------------\n";

$outcomes = DB::table('vicidial_log')
    ->select('status', DB::raw('COUNT(*) as count'), DB::raw('ROUND(COUNT(*) * 100.0 / ' . ($totalCalls ?: 1) . ', 2) as percentage'))
    ->whereBetween('call_date', [$startDate, $endDate])
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($outcomes as $outcome) {
    echo sprintf("%-15s: %6d calls (%5.2f%%)\n", $outcome->status, $outcome->count, $outcome->percentage);
}

// Get key metrics
$connects = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->whereIn('status', ['SALE', 'DNC', 'NI', 'XFER'])
    ->count();

$noAnswers = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->whereIn('status', ['NA', 'B', 'N'])
    ->count();

$voicemails = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->whereIn('status', ['AL', 'AM', 'AA'])
    ->count();

$connectRate = $totalCalls > 0 ? round(($connects / $totalCalls) * 100, 2) : 0;
$vmRate = $totalCalls > 0 ? round(($voicemails / $totalCalls) * 100, 2) : 0;
$naRate = $totalCalls > 0 ? round(($noAnswers / $totalCalls) * 100, 2) : 0;

echo "\nKey Metrics:\n";
echo "Connect Rate: " . $connectRate . "%\n";
echo "Voicemail Rate: " . $vmRate . "%\n";
echo "No Answer Rate: " . $naRate . "%\n\n";

// 3. TIME TO FIRST CONTACT (GOLDEN HOUR ANALYSIS)
echo "3. GOLDEN HOUR ANALYSIS\n";
echo "------------------------\n";

$goldenHourQuery = "
    SELECT 
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, l.created_at, vl.call_date) <= 5 THEN '0-5 min'
            WHEN TIMESTAMPDIFF(MINUTE, l.created_at, vl.call_date) <= 15 THEN '6-15 min'
            WHEN TIMESTAMPDIFF(MINUTE, l.created_at, vl.call_date) <= 30 THEN '16-30 min'
            WHEN TIMESTAMPDIFF(MINUTE, l.created_at, vl.call_date) <= 60 THEN '31-60 min'
            WHEN TIMESTAMPDIFF(HOUR, l.created_at, vl.call_date) <= 24 THEN '1-24 hours'
            ELSE '24+ hours'
        END as time_bracket,
        COUNT(DISTINCT vl.lead_id) as leads,
        SUM(CASE WHEN vl.status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as conversions,
        ROUND(AVG(CASE WHEN vl.status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) * 100, 2) as conversion_rate
    FROM leads l
    INNER JOIN (
        SELECT lead_id, MIN(call_date) as call_date, status
        FROM vicidial_log
        WHERE call_date BETWEEN ? AND ?
        GROUP BY lead_id
    ) vl ON l.external_lead_id = vl.lead_id
    WHERE l.created_at BETWEEN ? AND ?
    GROUP BY time_bracket
    ORDER BY 
        CASE time_bracket
            WHEN '0-5 min' THEN 1
            WHEN '6-15 min' THEN 2
            WHEN '16-30 min' THEN 3
            WHEN '31-60 min' THEN 4
            WHEN '1-24 hours' THEN 5
            ELSE 6
        END
";

$goldenHourResults = DB::select($goldenHourQuery, [$startDate, $endDate, $startDate, $endDate]);

echo "Time to First Contact vs Conversion Rate:\n";
foreach ($goldenHourResults as $result) {
    echo sprintf("%-12s: %5d leads, %3d sales, %5.2f%% conversion\n", 
        $result->time_bracket, 
        $result->leads, 
        $result->conversions, 
        $result->conversion_rate
    );
}

// 4. CALL FREQUENCY PATTERNS
echo "\n4. CALL FREQUENCY PATTERNS\n";
echo "---------------------------\n";

$callPatterns = DB::table('vicidial_log')
    ->select('lead_id', DB::raw('COUNT(*) as total_calls'))
    ->whereBetween('call_date', [$startDate, $endDate])
    ->groupBy('lead_id')
    ->get();

$callDistribution = [
    '1-5' => 0,
    '6-10' => 0,
    '11-20' => 0,
    '21-30' => 0,
    '31-40' => 0,
    '41-50' => 0,
    '50+' => 0
];

foreach ($callPatterns as $pattern) {
    if ($pattern->total_calls <= 5) $callDistribution['1-5']++;
    elseif ($pattern->total_calls <= 10) $callDistribution['6-10']++;
    elseif ($pattern->total_calls <= 20) $callDistribution['11-20']++;
    elseif ($pattern->total_calls <= 30) $callDistribution['21-30']++;
    elseif ($pattern->total_calls <= 40) $callDistribution['31-40']++;
    elseif ($pattern->total_calls <= 50) $callDistribution['41-50']++;
    else $callDistribution['50+']++;
}

echo "Leads by Total Call Attempts:\n";
foreach ($callDistribution as $range => $count) {
    $percentage = $uniqueLeads > 0 ? round(($count / $uniqueLeads) * 100, 2) : 0;
    echo sprintf("%-10s calls: %6d leads (%5.2f%%)\n", $range, $count, $percentage);
}

// 5. CONVERSION BY CALL NUMBER
echo "\n5. CONVERSION BY CALL NUMBER\n";
echo "-----------------------------\n";

$conversionByCallQuery = "
    SELECT 
        call_number,
        COUNT(*) as attempts,
        SUM(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) as conversions,
        ROUND(SUM(CASE WHEN status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as conversion_rate
    FROM (
        SELECT 
            lead_id,
            status,
            ROW_NUMBER() OVER (PARTITION BY lead_id ORDER BY call_date) as call_number
        FROM vicidial_log
        WHERE call_date BETWEEN ? AND ?
    ) numbered_calls
    WHERE call_number <= 20
    GROUP BY call_number
    ORDER BY call_number
";

$conversionByCalls = DB::select($conversionByCallQuery, [$startDate, $endDate]);

echo "Call # | Attempts | Conversions | Rate\n";
echo "-------|----------|-------------|------\n";
foreach ($conversionByCalls as $call) {
    echo sprintf("%6d | %8d | %11d | %5.2f%%\n", 
        $call->call_number, 
        $call->attempts, 
        $call->conversions, 
        $call->conversion_rate
    );
}

// 6. CALL DURATION ANALYSIS
echo "\n6. CALL DURATION ANALYSIS\n";
echo "--------------------------\n";

$durationStats = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->where('length_in_sec', '>', 0)
    ->selectRaw('
        AVG(length_in_sec) as avg_duration,
        MAX(length_in_sec) as max_duration,
        MIN(length_in_sec) as min_duration,
        SUM(length_in_sec) as total_seconds
    ')
    ->first();

$totalMinutes = round($durationStats->total_seconds / 60, 2);
$totalCost = $totalMinutes * 0.004;

echo "Average Call Duration: " . round($durationStats->avg_duration) . " seconds\n";
echo "Total Talk Time: " . number_format($totalMinutes) . " minutes\n";
echo "Total Cost (at $0.004/min): $" . number_format($totalCost, 2) . "\n";
echo "Cost per Lead: $" . ($uniqueLeads > 0 ? number_format($totalCost / $uniqueLeads, 4) : '0') . "\n";

// 7. CALLBACK EFFECTIVENESS
echo "\n7. CALLBACK EFFECTIVENESS\n";
echo "--------------------------\n";

// Look for inbound calls after outbound attempts
$callbackQuery = "
    SELECT 
        COUNT(DISTINCT vcl.lead_id) as callbacks_received,
        COUNT(DISTINCT CASE WHEN vcl.status IN ('SALE', 'XFER') THEN vcl.lead_id END) as callback_conversions
    FROM vicidial_closer_log vcl
    WHERE vcl.call_date BETWEEN ? AND ?
    AND EXISTS (
        SELECT 1 FROM vicidial_log vl 
        WHERE vl.lead_id = vcl.lead_id 
        AND vl.call_date < vcl.call_date
        AND vl.status IN ('NA', 'AL', 'AM')
    )
";

$callbackStats = DB::selectOne($callbackQuery, [$startDate, $endDate]);

$callbackRate = $noAnswers > 0 ? round(($callbackStats->callbacks_received / $noAnswers) * 100, 2) : 0;
$callbackConversionRate = $callbackStats->callbacks_received > 0 
    ? round(($callbackStats->callback_conversions / $callbackStats->callbacks_received) * 100, 2) 
    : 0;

echo "Callbacks Received: " . $callbackStats->callbacks_received . "\n";
echo "Callback Rate (from NA/VM): " . $callbackRate . "%\n";
echo "Callbacks → Sales: " . $callbackStats->callback_conversions . "\n";
echo "Callback Conversion Rate: " . $callbackConversionRate . "%\n";

// 8. TIME OF DAY ANALYSIS
echo "\n8. TIME OF DAY ANALYSIS\n";
echo "------------------------\n";

$hourlyStats = DB::table('vicidial_log')
    ->selectRaw('
        HOUR(call_date) as hour,
        COUNT(*) as calls,
        SUM(CASE WHEN status IN ("SALE", "XFER") THEN 1 ELSE 0 END) as conversions,
        ROUND(AVG(CASE WHEN status IN ("SALE", "XFER") THEN 1 ELSE 0 END) * 100, 2) as conversion_rate
    ')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

echo "Hour | Calls  | Sales | Rate\n";
echo "-----|--------|-------|------\n";
foreach ($hourlyStats as $hour) {
    $hourDisplay = str_pad($hour->hour, 2, '0', STR_PAD_LEFT) . ':00';
    echo sprintf("%s | %6d | %5d | %5.2f%%\n", 
        $hourDisplay, 
        $hour->calls, 
        $hour->conversions, 
        $hour->conversion_rate
    );
}

// 9. LATE-DAY LEAD PERFORMANCE
echo "\n9. LATE-DAY LEAD PERFORMANCE\n";
echo "-----------------------------\n";

$lateLeadQuery = "
    SELECT 
        CASE 
            WHEN HOUR(l.created_at) < 14 THEN 'Morning (9am-2pm)'
            WHEN HOUR(l.created_at) < 16 THEN 'Afternoon (2pm-4pm)'
            WHEN HOUR(l.created_at) < 18 THEN 'Late Day (4pm-6pm)'
            ELSE 'After Hours (6pm+)'
        END as arrival_time,
        COUNT(DISTINCT l.external_lead_id) as leads,
        AVG(CASE WHEN vl.status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) * 100 as conversion_rate,
        AVG(vl.total_calls) as avg_calls
    FROM leads l
    LEFT JOIN (
        SELECT 
            lead_id,
            COUNT(*) as total_calls,
            MAX(status) as status
        FROM vicidial_log
        WHERE call_date BETWEEN ? AND ?
        GROUP BY lead_id
    ) vl ON l.external_lead_id = vl.lead_id
    WHERE l.created_at BETWEEN ? AND ?
    GROUP BY arrival_time
    ORDER BY 
        CASE arrival_time
            WHEN 'Morning (9am-2pm)' THEN 1
            WHEN 'Afternoon (2pm-4pm)' THEN 2
            WHEN 'Late Day (4pm-6pm)' THEN 3
            ELSE 4
        END
";

$lateLeadStats = DB::select($lateLeadQuery, [$startDate, $endDate, $startDate, $endDate]);

echo "Lead Arrival Time Performance:\n";
foreach ($lateLeadStats as $stat) {
    echo sprintf("%-20s: %5d leads, %5.2f%% conversion, %.1f avg calls\n", 
        $stat->arrival_time,
        $stat->leads,
        $stat->conversion_rate,
        $stat->avg_calls
    );
}

// 10. A/B TEST SIMULATION
echo "\n10. A/B TEST SIMULATION (Based on Current Data)\n";
echo "-------------------------------------------------\n";

// Simulate Test A (Current - 48 calls)
$testALeads = DB::table('vicidial_log')
    ->select('lead_id')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->groupBy('lead_id')
    ->havingRaw('COUNT(*) >= 40')
    ->count();

$testAConversions = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->whereIn('status', ['SALE', 'XFER'])
    ->whereIn('lead_id', function($query) use ($startDate, $endDate) {
        $query->select('lead_id')
            ->from('vicidial_log')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->groupBy('lead_id')
            ->havingRaw('COUNT(*) >= 40');
    })
    ->distinct('lead_id')
    ->count('lead_id');

// Simulate Test B (Strategic - 18 calls)
$testBLeads = DB::table('vicidial_log')
    ->select('lead_id')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->groupBy('lead_id')
    ->havingRaw('COUNT(*) BETWEEN 15 AND 20')
    ->count();

$testBConversions = DB::table('vicidial_log')
    ->whereBetween('call_date', [$startDate, $endDate])
    ->whereIn('status', ['SALE', 'XFER'])
    ->whereIn('lead_id', function($query) use ($startDate, $endDate) {
        $query->select('lead_id')
            ->from('vicidial_log')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->groupBy('lead_id')
            ->havingRaw('COUNT(*) BETWEEN 15 AND 20');
    })
    ->distinct('lead_id')
    ->count('lead_id');

$testARate = $testALeads > 0 ? round(($testAConversions / $testALeads) * 100, 2) : 0;
$testBRate = $testBLeads > 0 ? round(($testBConversions / $testBLeads) * 100, 2) : 0;

echo "Test A Simulation (40+ calls):\n";
echo "  Leads: " . $testALeads . "\n";
echo "  Conversions: " . $testAConversions . "\n";
echo "  Conversion Rate: " . $testARate . "%\n";
echo "  Cost per Lead: $0.092\n";
echo "  Cost per Sale: $" . ($testAConversions > 0 ? number_format(($testALeads * 0.092) / $testAConversions, 2) : 'N/A') . "\n\n";

echo "Test B Simulation (15-20 calls):\n";
echo "  Leads: " . $testBLeads . "\n";
echo "  Conversions: " . $testBConversions . "\n";
echo "  Conversion Rate: " . $testBRate . "%\n";
echo "  Cost per Lead: $0.044\n";
echo "  Cost per Sale: $" . ($testBConversions > 0 ? number_format(($testBLeads * 0.044) / $testBConversions, 2) : 'N/A') . "\n\n";

// SUMMARY RECOMMENDATIONS
echo "================================================================================\n";
echo "                           KEY FINDINGS & RECOMMENDATIONS                       \n";
echo "================================================================================\n\n";

echo "1. GOLDEN HOUR IMPACT: ";
if (isset($goldenHourResults[0]) && $goldenHourResults[0]->conversion_rate > 0) {
    echo "Leads contacted in 0-5 minutes convert at " . $goldenHourResults[0]->conversion_rate . "%\n";
} else {
    echo "Insufficient data for golden hour analysis\n";
}

echo "2. OPTIMAL CALL COUNT: Most conversions happen by call #";
$peakCall = 1;
$peakRate = 0;
foreach ($conversionByCalls as $call) {
    if ($call->conversion_rate > $peakRate) {
        $peakRate = $call->conversion_rate;
        $peakCall = $call->call_number;
    }
}
echo $peakCall . " (" . $peakRate . "% rate)\n";

echo "3. CALLBACK OPPORTUNITY: Current callback rate is " . $callbackRate . "% - ";
echo ($callbackRate < 10 ? "room for improvement with better VM strategy\n" : "performing well\n");

echo "4. COST EFFICIENCY: Average cost per lead is $" . ($uniqueLeads > 0 ? number_format($totalCost / $uniqueLeads, 4) : '0');
echo " with " . $avgCallsPerLead . " calls per lead\n";

echo "5. LATE-DAY STRATEGY: ";
$lateDayData = array_filter($lateLeadStats, fn($s) => strpos($s->arrival_time, '4pm-6pm') !== false);
if (!empty($lateDayData)) {
    $lateDayStat = reset($lateDayData);
    echo "Late-day leads (4-6pm) show " . round($lateDayStat->conversion_rate, 2) . "% conversion - ";
    echo "speed-first approach recommended\n";
} else {
    echo "Insufficient late-day lead data\n";
}

echo "\n================================================================================\n";
echo "                              END OF ANALYSIS                                   \n";
echo "================================================================================\n\n";





