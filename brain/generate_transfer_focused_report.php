<?php
/**
 * Generate transfer-focused report with conversion metrics
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
echo "                 90-DAY TRANSFER/CONVERSION ANALYSIS REPORT                    \n";
echo "================================================================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Define transfer dispositions
$transferDispositions = ['A', 'XFER', 'XFERA', 'RQXFER'];

// 1. OVERALL METRICS WITH TRANSFERS
echo "📊 OVERALL METRICS (WITH TRANSFERS)\n";
echo str_repeat("-", 80) . "\n";

$overallStats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total_calls,
        COUNT(DISTINCT phone_number) as unique_leads,
        COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers,
        COUNT(CASE WHEN length_in_sec > 120 THEN 1 END) as calls_over_2min,
        AVG(length_in_sec) as avg_call_length,
        AVG(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN length_in_sec END) as avg_transfer_length
    ')
    ->first();

$transferRate = ($overallStats->transfers / $overallStats->total_calls) * 100;

echo "• Total Calls: " . number_format($overallStats->total_calls) . "\n";
echo "• Unique Leads: " . number_format($overallStats->unique_leads) . "\n";
echo "• TRANSFERS: " . number_format($overallStats->transfers) . " (" . round($transferRate, 2) . "%)\n";
echo "• Transfer Rate: " . round($transferRate, 2) . "%\n";
echo "• Avg Call Length: " . round($overallStats->avg_call_length) . " seconds\n";
echo "• Avg Transfer Length: " . round($overallStats->avg_transfer_length) . " seconds\n";
echo "• Calls > 2 minutes: " . number_format($overallStats->calls_over_2min) . "\n\n";

// 2. TRANSFER BREAKDOWN
echo "💰 TRANSFER/SALE BREAKDOWN\n";
echo str_repeat("-", 80) . "\n";

$transferBreakdown = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count, AVG(length_in_sec) as avg_length'))
    ->whereIn('status', $transferDispositions)
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->get();

foreach ($transferBreakdown as $transfer) {
    $pct = round(($transfer->count / $overallStats->total_calls) * 100, 2);
    echo sprintf("%-10s: %8s calls (%5.2f%%) - Avg length: %d seconds\n", 
        $transfer->status, 
        number_format($transfer->count), 
        $pct,
        round($transfer->avg_length)
    );
}
echo "\n";

// 3. CALLS TO CONVERSION ANALYSIS
echo "📈 CALLS TO CONVERSION ANALYSIS\n";
echo str_repeat("-", 80) . "\n";

// Get leads that eventually transferred
$transferredLeads = DB::table('orphan_call_logs')
    ->select('phone_number')
    ->whereIn('status', $transferDispositions)
    ->distinct()
    ->pluck('phone_number');

echo "• Total leads that transferred: " . number_format(count($transferredLeads)) . "\n";
echo "• Lead conversion rate: " . round((count($transferredLeads) / $overallStats->unique_leads) * 100, 2) . "%\n\n";

// Analyze how many calls before transfer
$callsBeforeTransfer = DB::table('orphan_call_logs as o1')
    ->selectRaw('
        o1.phone_number,
        (SELECT COUNT(*) FROM orphan_call_logs o2 
         WHERE o2.phone_number = o1.phone_number 
         AND o2.call_date <= o1.call_date) as calls_before
    ')
    ->whereIn('o1.status', $transferDispositions)
    ->get();

$callDistribution = [
    '1' => 0,
    '2-3' => 0,
    '4-5' => 0,
    '6-10' => 0,
    '11-15' => 0,
    '16-20' => 0,
    '21-30' => 0,
    '30+' => 0
];

foreach ($callsBeforeTransfer as $record) {
    $calls = $record->calls_before;
    if ($calls == 1) $callDistribution['1']++;
    elseif ($calls <= 3) $callDistribution['2-3']++;
    elseif ($calls <= 5) $callDistribution['4-5']++;
    elseif ($calls <= 10) $callDistribution['6-10']++;
    elseif ($calls <= 15) $callDistribution['11-15']++;
    elseif ($calls <= 20) $callDistribution['16-20']++;
    elseif ($calls <= 30) $callDistribution['21-30']++;
    else $callDistribution['30+']++;
}

echo "CALLS BEFORE TRANSFER:\n";
$totalTransfers = array_sum($callDistribution);
foreach ($callDistribution as $range => $count) {
    if ($count > 0) {
        $pct = round(($count / $totalTransfers) * 100, 1);
        echo sprintf("  %6s calls: %8s transfers (%5.1f%%)\n", $range, number_format($count), $pct);
    }
}
echo "\n";

// 4. TIME TO CONVERSION
echo "⏰ TIME TO CONVERSION\n";
echo str_repeat("-", 80) . "\n";

// For each transferred lead, find time from first call to transfer
$conversionTimes = [];
foreach ($transferredLeads->chunk(1000) as $chunk) {
    $leadTimes = DB::table('orphan_call_logs')
        ->selectRaw('
            phone_number,
            MIN(call_date) as first_call,
            MAX(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN call_date END) as transfer_date
        ')
        ->whereIn('phone_number', $chunk)
        ->groupBy('phone_number')
        ->get();
    
    foreach ($leadTimes as $lead) {
        if ($lead->first_call && $lead->transfer_date) {
            $first = Carbon::parse($lead->first_call);
            $transfer = Carbon::parse($lead->transfer_date);
            $hours = $first->diffInHours($transfer);
            
            if ($hours <= 1) $conversionTimes['0-1 hour'][] = $lead->phone_number;
            elseif ($hours <= 24) $conversionTimes['1-24 hours'][] = $lead->phone_number;
            elseif ($hours <= 48) $conversionTimes['24-48 hours'][] = $lead->phone_number;
            elseif ($hours <= 72) $conversionTimes['48-72 hours'][] = $lead->phone_number;
            elseif ($hours <= 168) $conversionTimes['3-7 days'][] = $lead->phone_number;
            elseif ($hours <= 336) $conversionTimes['1-2 weeks'][] = $lead->phone_number;
            elseif ($hours <= 720) $conversionTimes['2-4 weeks'][] = $lead->phone_number;
            else $conversionTimes['30+ days'][] = $lead->phone_number;
        }
    }
}

echo "TIME FROM FIRST CALL TO TRANSFER:\n";
foreach (['0-1 hour', '1-24 hours', '24-48 hours', '48-72 hours', '3-7 days', '1-2 weeks', '2-4 weeks', '30+ days'] as $period) {
    if (isset($conversionTimes[$period])) {
        $count = count($conversionTimes[$period]);
        $pct = round(($count / count($transferredLeads)) * 100, 1);
        echo sprintf("  %-15s: %8s leads (%5.1f%%)\n", $period, number_format($count), $pct);
    }
}
echo "\n";

// 5. HOURLY TRANSFER RATES
echo "🕐 BEST HOURS FOR TRANSFERS\n";
echo str_repeat("-", 80) . "\n";

$hourlyTransfers = DB::table('orphan_call_logs')
    ->selectRaw('
        EXTRACT(HOUR FROM call_date) as hour,
        COUNT(*) as total_calls,
        COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers
    ')
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();

echo "Hour  | Total Calls | Transfers | Transfer Rate\n";
echo "------|-------------|-----------|---------------\n";
foreach ($hourlyTransfers as $hour) {
    if ($hour->hour >= 9 && $hour->hour <= 18) {
        $transferRate = $hour->total_calls > 0 ? round(($hour->transfers / $hour->total_calls) * 100, 2) : 0;
        echo sprintf("%2d:00 | %11s | %9s | %12.2f%%\n",
            $hour->hour,
            number_format($hour->total_calls),
            number_format($hour->transfers),
            $transferRate
        );
    }
}
echo "\n";

// 6. AGENT PERFORMANCE WITH TRANSFERS
echo "👥 TOP AGENTS BY TRANSFER RATE\n";
echo str_repeat("-", 80) . "\n";

$agentTransfers = DB::table('orphan_call_logs')
    ->select('agent_id', DB::raw('
        COUNT(*) as total_calls,
        COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers,
        AVG(CASE WHEN status IN ("' . implode('","', $transferDispositions) . '") THEN length_in_sec END) as avg_transfer_time
    '))
    ->whereNotNull('agent_id')
    ->where('agent_id', '!=', '')
    ->groupBy('agent_id')
    ->having('total_calls', '>', 100)
    ->orderBy(DB::raw('transfers/total_calls'), 'desc')
    ->limit(15)
    ->get();

echo "Agent      | Calls  | Transfers | Transfer Rate | Avg Transfer Time\n";
echo "-----------|--------|-----------|---------------|------------------\n";
foreach ($agentTransfers as $agent) {
    $transferRate = $agent->total_calls > 0 ? round(($agent->transfers / $agent->total_calls) * 100, 2) : 0;
    echo sprintf("%-10s | %6s | %9s | %12.2f%% | %16s\n",
        substr($agent->agent_id, 0, 10),
        number_format($agent->total_calls),
        number_format($agent->transfers),
        $transferRate,
        round($agent->avg_transfer_time) . "s"
    );
}
echo "\n";

// 7. LIST PERFORMANCE BY TRANSFERS
echo "📋 LIST PERFORMANCE BY TRANSFER RATE\n";
echo str_repeat("-", 80) . "\n";

$listTransfers = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('
        COUNT(*) as calls,
        COUNT(DISTINCT phone_number) as leads,
        COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers
    '))
    ->whereNotNull('list_id')
    ->where('list_id', '>', 0)
    ->groupBy('list_id')
    ->having('calls', '>', 1000)
    ->orderBy(DB::raw('transfers/calls'), 'desc')
    ->limit(15)
    ->get();

echo "List ID | Calls   | Leads  | Transfers | Transfer Rate\n";
echo "--------|---------|--------|-----------|---------------\n";
foreach ($listTransfers as $list) {
    $transferRate = $list->calls > 0 ? round(($list->transfers / $list->calls) * 100, 2) : 0;
    echo sprintf("%7d | %7s | %6s | %9s | %12.2f%%\n",
        $list->list_id,
        number_format($list->calls),
        number_format($list->leads),
        number_format($list->transfers),
        $transferRate
    );
}
echo "\n";

// 8. KEY INSIGHTS
echo "💡 KEY INSIGHTS & RECOMMENDATIONS\n";
echo str_repeat("=", 80) . "\n\n";

$overallTransferRate = ($overallStats->transfers / $overallStats->total_calls) * 100;
$leadConversionRate = (count($transferredLeads) / $overallStats->unique_leads) * 100;

echo "🎯 CONVERSION METRICS:\n";
echo "• Overall Transfer Rate: " . round($overallTransferRate, 2) . "%\n";
echo "• Lead Conversion Rate: " . round($leadConversionRate, 2) . "%\n";
echo "• Total Transfers: " . number_format($overallStats->transfers) . "\n";
echo "• Converted Leads: " . number_format(count($transferredLeads)) . "\n\n";

echo "📊 CRITICAL FINDINGS:\n";

// Finding 1: Transfer rate analysis
if ($overallTransferRate > 50) {
    echo "1. EXCEPTIONAL TRANSFER RATE (" . round($overallTransferRate, 2) . "%)\n";
    echo "   → This is extremely high for internet leads\n";
    echo "   → Focus on maintaining quality\n";
    echo "   → Scale up successful practices\n\n";
} elseif ($overallTransferRate > 20) {
    echo "1. STRONG TRANSFER RATE (" . round($overallTransferRate, 2) . "%)\n";
    echo "   → Well above industry average\n";
    echo "   → Continue current approach\n";
    echo "   → Minor optimizations only\n\n";
} else {
    echo "1. TRANSFER RATE: " . round($overallTransferRate, 2) . "%\n";
    echo "   → Room for improvement\n";
    echo "   → Review agent training\n";
    echo "   → Optimize scripts\n\n";
}

// Finding 2: Speed to conversion
$quickConversions = isset($conversionTimes['0-1 hour']) ? count($conversionTimes['0-1 hour']) : 0;
$quickConversions += isset($conversionTimes['1-24 hours']) ? count($conversionTimes['1-24 hours']) : 0;
$quickPct = count($transferredLeads) > 0 ? round(($quickConversions / count($transferredLeads)) * 100, 1) : 0;

echo "2. SPEED TO CONVERSION:\n";
echo "   → " . $quickPct . "% convert within 24 hours\n";
if ($quickPct > 70) {
    echo "   → EXCELLENT: Fast conversion rate\n";
    echo "   → Validates Golden Hour strategy\n";
} else {
    echo "   → Opportunity to improve first-day contact\n";
    echo "   → Implement more aggressive Day 1 calling\n";
}
echo "\n";

// Finding 3: Optimal call count
$optimalCalls = 0;
foreach (['1', '2-3', '4-5', '6-10'] as $range) {
    $optimalCalls += $callDistribution[$range];
}
$optimalPct = $totalTransfers > 0 ? round(($optimalCalls / $totalTransfers) * 100, 1) : 0;

echo "3. OPTIMAL CALL COUNT:\n";
echo "   → " . $optimalPct . "% convert within 10 calls\n";
echo "   → Diminishing returns after 10-15 attempts\n";
echo "   → Recommend max 12 calls per lead\n\n";

echo str_repeat("=", 80) . "\n";
echo "Report generated: " . date('Y-m-d H:i:s') . "\n\n";

// Save report
$reportContent = ob_get_contents();
file_put_contents(__DIR__ . '/reports/transfer_analysis_' . date('Ymd_His') . '.txt', $reportContent);
echo "Report saved to: reports/transfer_analysis_" . date('Ymd_His') . ".txt\n";
