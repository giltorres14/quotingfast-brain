<?php
/**
 * OPTIMIZED 90-Day Import - Processes in small chunks to avoid memory issues
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n================================================================================\n";
echo "           OPTIMIZED 90-DAY VICI IMPORT (Memory Efficient)                     \n";
echo "================================================================================\n\n";

$startTime = microtime(true);
$DB_NAME = 'Q6hdjl67GRigMofv';

// Import parameters
$endDate = Carbon::now();
$startDate = Carbon::now()->subDays(90);

echo "📊 Database: $DB_NAME\n";
echo "📅 Date Range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n";
echo "🎯 Campaigns: AUTODIAL, AUTO2\n\n";

// Process day by day to avoid memory issues
$totalImported = 0;
$currentDate = clone $startDate;
$dayNum = 1;

echo "Processing day by day (90 days total)...\n";
echo str_repeat("-", 80) . "\n";

while ($currentDate->lte($endDate)) {
    $dayStart = $currentDate->format('Y-m-d 00:00:00');
    $dayEnd = $currentDate->format('Y-m-d 23:59:59');
    
    // Progress indicator
    if ($dayNum % 7 == 1) {
        echo "\nWeek " . ceil($dayNum / 7) . ":\n";
    }
    
    echo "  " . $currentDate->format('M d') . ": ";
    
    // Process this day in smaller chunks (500 records at a time)
    $offset = 0;
    $dayCount = 0;
    $hasMore = true;
    
    while ($hasMore) {
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
                IFNULL(vlist.vendor_lead_code, '') as vendor_lead_code
            FROM vicidial_log vl
            LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
            WHERE vl.call_date BETWEEN '$dayStart' AND '$dayEnd'
            AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
            LIMIT 500 OFFSET $offset
        ";
        
        try {
            $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -N -B -e " . escapeshellarg($query)
            ]);
            
            if ($response->successful()) {
                $output = json_decode($response->body(), true)['output'] ?? '';
                $lines = explode("\n", $output);
                
                $batch = [];
                $recordCount = 0;
                
                foreach ($lines as $line) {
                    if (empty(trim($line)) || 
                        strpos($line, 'Could not create') !== false || 
                        strpos($line, 'Failed to add') !== false) {
                        continue;
                    }
                    
                    $data = explode("\t", $line);
                    
                    if (count($data) >= 11) {
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
                            'vendor_lead_code' => $data[10] ?: null,
                            'source_table' => 'vicidial_log',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        $recordCount++;
                    }
                }
                
                // Insert batch
                if (!empty($batch)) {
                    DB::table('orphan_call_logs')->insertOrIgnore($batch);
                    $dayCount += count($batch);
                    echo ".";
                }
                
                // Check if there are more records
                if ($recordCount < 500) {
                    $hasMore = false;
                } else {
                    $offset += 500;
                }
                
                // Clear memory
                unset($batch, $lines, $output);
                
            } else {
                $hasMore = false;
            }
            
        } catch (\Exception $e) {
            echo " Error: " . substr($e->getMessage(), 0, 30);
            $hasMore = false;
        }
    }
    
    echo " " . number_format($dayCount) . " calls\n";
    $totalImported += $dayCount;
    
    // Move to next day
    $currentDate->addDay();
    $dayNum++;
    
    // Periodic status update
    if ($dayNum % 10 == 0) {
        echo "  [Progress: Day $dayNum/90, Total imported: " . number_format($totalImported) . "]\n";
        
        // Clear Laravel query log to free memory
        DB::connection()->disableQueryLog();
        
        // Force garbage collection
        gc_collect_cycles();
    }
}

echo str_repeat("-", 80) . "\n";
echo "✅ IMPORT COMPLETE!\n";
echo "  • Total calls imported: " . number_format($totalImported) . "\n\n";

// Get final statistics
$stats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total,
        MIN(call_date) as oldest,
        MAX(call_date) as newest,
        COUNT(DISTINCT phone_number) as unique_phones,
        COUNT(DISTINCT vici_lead_id) as unique_leads
    ')
    ->first();

echo "📊 FINAL DATABASE STATISTICS:\n";
echo "  • Total call logs: " . number_format($stats->total) . "\n";
echo "  • Date range: " . substr($stats->oldest, 0, 10) . " to " . substr($stats->newest, 0, 10) . "\n";
echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n\n";

// Status distribution
echo "📊 TOP STATUSES:\n";
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->whereIn('campaign_id', ['AUTODIAL', 'AUTO2'])
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($statuses as $status) {
    $statusName = $status->status ?: 'NULL';
    echo "  • {$statusName}: " . number_format($status->count) . "\n";
}

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Total execution time: " . round($executionTime / 60, 1) . " minutes\n";
echo "================================================================================\n";











