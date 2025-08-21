<?php
/**
 * Import Vici Call Logs from CORRECT Database
 * Database: Q6hdjl67GRigMofv (not asterisk!)
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n================================================================================\n";
echo "           VICI CALL LOG IMPORT - CORRECT DATABASE                             \n";
echo "================================================================================\n\n";

$startTime = microtime(true);

// CORRECT database name
$DB_NAME = 'Q6hdjl67GRigMofv';

echo "📊 Database: $DB_NAME\n";
echo "📅 Importing last 90 days of call logs\n\n";

// Calculate date range
$endDate = Carbon::now()->format('Y-m-d H:i:s');
$startDate = Carbon::now()->subDays(90)->format('Y-m-d H:i:s');

echo "Date Range: $startDate to $endDate\n\n";

// First, get a count of what we're importing
$countQuery = "
    SELECT 
        COUNT(*) as total,
        MIN(call_date) as oldest,
        MAX(call_date) as newest
    FROM vicidial_log 
    WHERE campaign_id IN ('AUTODIAL', 'AUTO2')
    AND call_date >= '$startDate'
";

try {
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -e " . escapeshellarg($countQuery)
    ]);
    
    if ($response->successful()) {
        $output = json_decode($response->body(), true)['output'] ?? '';
        echo "Available data to import:\n";
        echo $output . "\n";
    }
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "Starting import process...\n\n";

// Process in daily chunks
$totalImported = 0;
$currentDate = Carbon::parse($startDate);
$endDateCarbon = Carbon::parse($endDate);

while ($currentDate->lte($endDateCarbon)) {
    $dayStart = $currentDate->format('Y-m-d 00:00:00');
    $dayEnd = $currentDate->format('Y-m-d 23:59:59');
    
    echo "Processing " . $currentDate->format('Y-m-d') . "... ";
    
    // Query for this day's data
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
            vl.user as agent_user,
            vl.term_reason,
            vlist.vendor_lead_code
        FROM vicidial_log vl
        LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
        WHERE vl.call_date BETWEEN '$dayStart' AND '$dayEnd'
        AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
        LIMIT 10000
    ";
    
    try {
        // Use CSV export for efficiency
        $csvQuery = "
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
                vlist.vendor_lead_code
            INTO OUTFILE '/tmp/vici_export_" . $currentDate->format('Ymd') . ".csv'
            FIELDS TERMINATED BY ',' 
            ENCLOSED BY '\"' 
            LINES TERMINATED BY '\\n'
            FROM vicidial_log vl
            LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
            WHERE vl.call_date BETWEEN '$dayStart' AND '$dayEnd'
            AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
        ";
        
        // Execute query directly
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv $DB_NAME -N -B -e " . escapeshellarg($query)
        ]);
        
        if ($response->successful()) {
            $output = json_decode($response->body(), true)['output'] ?? '';
            
            // Parse the tab-separated output
            $lines = explode("\n", trim($output));
            $dayCount = 0;
            
            foreach ($lines as $line) {
                if (empty(trim($line)) || strpos($line, 'Could not create') !== false || strpos($line, 'Failed to add') !== false) {
                    continue;
                }
                
                $data = explode("\t", $line);
                
                if (count($data) >= 10) {
                    try {
                        DB::table('orphan_call_logs')->insertOrIgnore([
                            'call_date' => $data[0],
                            'vici_lead_id' => $data[1] ?: null,
                            'list_id' => $data[2] ?: null,
                            'phone_number' => $data[3],
                            'campaign_id' => $data[4],
                            'status' => $data[5],
                            'length_in_sec' => $data[6] ?: 0,
                            'uniqueid' => $data[7] ?: null,
                            'agent_id' => $data[8] ?: null,
                            'term_reason' => $data[9] ?: null,
                            'vendor_lead_code' => $data[10] ?? null,
                            'source_table' => 'vicidial_log',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $dayCount++;
                    } catch (\Exception $e) {
                        // Skip duplicates
                    }
                }
            }
            
            echo "✅ $dayCount calls imported\n";
            $totalImported += $dayCount;
            
        } else {
            echo "❌ Error\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    // Move to next day
    $currentDate->addDay();
    
    // Small delay to avoid overwhelming the server
    if ($totalImported > 0 && $totalImported % 10000 == 0) {
        echo "  [Imported $totalImported so far...]\n";
    }
}

// Final statistics
echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ IMPORT COMPLETE\n";
echo "  • Total calls imported: " . number_format($totalImported) . "\n";

// Get statistics from our database
$stats = DB::table('orphan_call_logs')
    ->selectRaw('
        COUNT(*) as total,
        MIN(call_date) as oldest,
        MAX(call_date) as newest,
        COUNT(DISTINCT phone_number) as unique_phones,
        COUNT(DISTINCT vici_lead_id) as unique_leads,
        COUNT(DISTINCT list_id) as unique_lists
    ')
    ->first();

echo "\n📊 DATABASE STATISTICS:\n";
echo "  • Total call logs: " . number_format($stats->total) . "\n";
echo "  • Date range: " . $stats->oldest . " to " . $stats->newest . "\n";
echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n";
echo "  • Unique lists: " . number_format($stats->unique_lists) . "\n\n";

// Status distribution
echo "📈 TOP 10 CALL STATUSES:\n";
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($statuses as $status) {
    $statusName = $status->status ?: 'NULL';
    $percentage = $stats->total > 0 ? round(($status->count / $stats->total) * 100, 1) : 0;
    echo "  • {$statusName}: " . number_format($status->count) . " ({$percentage}%)\n";
}

// List distribution
echo "\n📋 CALLS BY LIST:\n";
$lists = DB::table('orphan_call_logs')
    ->select('list_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('list_id')
    ->where('list_id', '>', 0)
    ->groupBy('list_id')
    ->orderBy('list_id')
    ->limit(20)
    ->get();

foreach ($lists as $list) {
    echo "  • List {$list->list_id}: " . number_format($list->count) . " calls\n";
}

$executionTime = round(microtime(true) - $startTime, 2);
echo "\n⏱️ Execution time: {$executionTime} seconds\n";
echo "================================================================================\n";


