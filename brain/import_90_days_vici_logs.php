<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 IMPORTING 90 DAYS OF VICI CALL LOGS\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$startTime = microtime(true);
$viciProxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// Calculate date range
$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(90);

echo "📅 Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n\n";

// Process in daily chunks to avoid memory issues
$totalImported = 0;
$currentDate = clone $startDate;

while ($currentDate->lte($endDate)) {
    $dayStart = $currentDate->format('Y-m-d 00:00:00');
    $dayEnd = $currentDate->format('Y-m-d 23:59:59');
    
    echo "Processing {$currentDate->format('Y-m-d')}... ";
    
    // Query 1: Get calls from vicidial_log
    $sql1 = "
        SELECT 
            vl.call_date,
            vl.lead_id,
            vl.list_id,
            vl.phone_number,
            vl.campaign_id,
            vl.status,
            vl.length_in_sec,
            vl.user as agent_user,
            vl.term_reason,
            vlist.vendor_lead_code,
            vl.uniqueid,
            'vicidial_log' as source_table
        FROM vicidial_log vl
        LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
        WHERE vl.call_date BETWEEN '$dayStart' AND '$dayEnd'
        AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
    ";
    
    // Query 2: Get actual dials from vicidial_dial_log
    $sql2 = "
        SELECT 
            vdl.call_date,
            vdl.lead_id,
            vdl.list_id,
            vdl.phone_number,
            vdl.campaign_id,
            vdl.status,
            vdl.length_in_sec,
            vdl.user as agent_user,
            vdl.term_reason,
            vlist.vendor_lead_code,
            vdl.uniqueid,
            'vicidial_dial_log' as source_table
        FROM vicidial_dial_log vdl
        LEFT JOIN vicidial_list vlist ON vdl.lead_id = vlist.lead_id
        WHERE vdl.call_date BETWEEN '$dayStart' AND '$dayEnd'
        AND vdl.campaign_id IN ('AUTODIAL', 'AUTO2')
    ";
    
    try {
        // Fetch from vicidial_log
        $response1 = Http::timeout(60)->post($viciProxyUrl, [
            'query' => $sql1
        ]);
        
        $calls1 = [];
        if ($response1->successful()) {
            $result = $response1->json();
            $calls1 = $result['data'] ?? [];
        }
        
        // Fetch from vicidial_dial_log
        $response2 = Http::timeout(60)->post($viciProxyUrl, [
            'query' => $sql2
        ]);
        
        $calls2 = [];
        if ($response2->successful()) {
            $result = $response2->json();
            $calls2 = $result['data'] ?? [];
        }
        
        // Combine and deduplicate
        $allCalls = array_merge($calls1, $calls2);
        $uniqueCalls = [];
        $seen = [];
        
        foreach ($allCalls as $call) {
            $key = $call['uniqueid'] ?? ($call['call_date'] . '_' . $call['phone_number']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueCalls[] = $call;
            }
        }
        
        // Insert into orphan_call_logs
        $inserted = 0;
        foreach ($uniqueCalls as $call) {
            try {
                DB::table('orphan_call_logs')->insertOrIgnore([
                    'call_date' => $call['call_date'],
                    'vici_lead_id' => $call['lead_id'],
                    'list_id' => $call['list_id'],
                    'phone_number' => $call['phone_number'],
                    'campaign_id' => $call['campaign_id'],
                    'status' => $call['status'],
                    'length_in_sec' => $call['length_in_sec'],
                    'agent_user' => $call['agent_user'],
                    'term_reason' => $call['term_reason'],
                    'vendor_lead_code' => $call['vendor_lead_code'],
                    'uniqueid' => $call['uniqueid'],
                    'source_table' => $call['source_table'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $inserted++;
            } catch (\Exception $e) {
                // Ignore duplicates
            }
        }
        
        echo "✅ {$inserted} calls imported\n";
        $totalImported += $inserted;
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    // Move to next day
    $currentDate->addDay();
    
    // Small delay to avoid overwhelming the server
    usleep(100000); // 0.1 second
}

// Final statistics
echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ IMPORT COMPLETE\n";
echo "  • Total calls imported: " . number_format($totalImported) . "\n";

// Get statistics
$stats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total,
        MIN(call_date) as oldest,
        MAX(call_date) as newest,
        COUNT(DISTINCT phone_number) as unique_phones,
        COUNT(DISTINCT vici_lead_id) as unique_leads
    ')
    ->first();

echo "  • Date range: {$stats->oldest} to {$stats->newest}\n";
echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n";

// Status distribution
echo "\n📊 Status Distribution:\n";
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($statuses as $status) {
    $statusName = $status->status ?: 'NULL';
    echo "  • {$statusName}: " . number_format($status->count) . "\n";
}

// List distribution
echo "\n📋 List Distribution:\n";
$lists = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('list_id')
    ->groupBy('list_id')
    ->orderBy('list_id')
    ->get();

foreach ($lists as $list) {
    echo "  • List {$list->list_id}: " . number_format($list->count) . " calls\n";
}

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Execution time: {$executionTime} seconds\n";




