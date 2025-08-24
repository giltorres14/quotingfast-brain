<?php
/**
 * Import Vici Call Logs via CSV Export
 * Uses the bash script approach to export from Vici then import to Brain
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
echo "                    VICI CALL LOG IMPORT VIA CSV EXPORT                        \n";
echo "================================================================================\n\n";

// First, let's fix the orphan_call_logs table to have all needed columns
echo "📋 Checking/updating orphan_call_logs table structure...\n";

try {
    // Add missing columns if they don't exist
    $columns = DB::getSchemaBuilder()->getColumnListing('orphan_call_logs');
    
    if (!in_array('list_id', $columns)) {
        DB::statement('ALTER TABLE orphan_call_logs ADD COLUMN list_id INTEGER NULL');
        echo "  ✅ Added list_id column\n";
    }
    
    if (!in_array('length_in_sec', $columns)) {
        DB::statement('ALTER TABLE orphan_call_logs ADD COLUMN length_in_sec INTEGER NULL');
        echo "  ✅ Added length_in_sec column\n";
    }
    
    if (!in_array('uniqueid', $columns)) {
        DB::statement('ALTER TABLE orphan_call_logs ADD COLUMN uniqueid VARCHAR(255) NULL');
        echo "  ✅ Added uniqueid column\n";
    }
    
    if (!in_array('term_reason', $columns)) {
        DB::statement('ALTER TABLE orphan_call_logs ADD COLUMN term_reason VARCHAR(50) NULL');
        echo "  ✅ Added term_reason column\n";
    }
    
    if (!in_array('source_table', $columns)) {
        DB::statement('ALTER TABLE orphan_call_logs ADD COLUMN source_table VARCHAR(50) NULL');
        echo "  ✅ Added source_table column\n";
    }
    
    echo "✅ Table structure updated\n\n";
} catch (\Exception $e) {
    echo "❌ Error updating table: " . $e->getMessage() . "\n";
    exit(1);
}

// Now let's create the bash script on the Vici server
$bashScript = '#!/bin/bash
# Export Vici call logs for the last 90 days

DB_NAME="asterisk"
START_DATE=$(date -d "90 days ago" +"%Y-%m-%d 00:00:00")
END_DATE=$(date +"%Y-%m-%d 23:59:59")

# Generate filename
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vicidial_data_${now}.csv"

# Define paths
REPORT_PATH="/tmp/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Create the export
mysql -u root -pQ6hdjl67GRigMofv -N -B <<EOF
USE ${DB_NAME};
SET SESSION group_concat_max_len = 1000000;
(
  SELECT "call_date", "lead_id", "list_id", "phone_number", "campaign_id", "status", "length_in_sec", "uniqueid", "user", "term_reason"
)
UNION ALL
(
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
    vl.term_reason
  FROM vicidial_log vl
  WHERE vl.call_date BETWEEN "${START_DATE}" AND "${END_DATE}"
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ""
  LIMIT 100000
)
INTO OUTFILE "${MYSQL_OUTPUT}"
FIELDS TERMINATED BY "," ENCLOSED BY "\"" ESCAPED BY "\\\\\\\\"
LINES TERMINATED BY "\\n";
EOF

# Move file and output contents
mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}" 2>/dev/null
cat "${FINAL_OUTPUT}"
rm -f "${FINAL_OUTPUT}"
';

echo "📊 Executing export on Vici server...\n";

try {
    // Execute the export script via proxy
    $response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "echo " . escapeshellarg($bashScript) . " | bash"
    ]);
    
    if (!$response->successful()) {
        throw new Exception("Failed to execute export: " . $response->body());
    }
    
    $csvContent = $response->body();
    $lines = explode("\n", trim($csvContent));
    
    if (count($lines) < 2) {
        echo "⚠️ No data returned from Vici. Checking for any available data...\n";
        
        // Try a simpler query to see what's available
        $testResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'query' => "SELECT COUNT(*) as count, MIN(call_date) as oldest, MAX(call_date) as newest FROM vicidial_log WHERE campaign_id IS NOT NULL"
        ]);
        
        if ($testResponse->successful()) {
            $testData = $testResponse->json()['data'][0] ?? [];
            echo "  Vici has " . ($testData['count'] ?? 0) . " total calls\n";
            if ($testData['oldest']) {
                echo "  Date range: " . $testData['oldest'] . " to " . $testData['newest'] . "\n";
            }
        }
        
        echo "\n⚠️ No call logs to import from the specified date range.\n";
        exit(0);
    }
    
    echo "✅ Received " . (count($lines) - 1) . " call records\n";
    echo "📥 Importing to orphan_call_logs table...\n";
    
    // Skip header row
    array_shift($lines);
    
    $imported = 0;
    $failed = 0;
    $batch = [];
    
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        // Parse CSV line
        $data = str_getcsv($line);
        
        if (count($data) < 10) {
            $failed++;
            continue;
        }
        
        $batch[] = [
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
            'source_table' => 'vicidial_log',
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Insert in batches of 1000
        if (count($batch) >= 1000) {
            DB::table('orphan_call_logs')->insertOrIgnore($batch);
            $imported += count($batch);
            echo "  Imported $imported records...\r";
            $batch = [];
        }
    }
    
    // Insert remaining batch
    if (!empty($batch)) {
        DB::table('orphan_call_logs')->insertOrIgnore($batch);
        $imported += count($batch);
    }
    
    echo "\n✅ Import complete!\n";
    echo "  • Records imported: " . number_format($imported) . "\n";
    echo "  • Failed records: " . number_format($failed) . "\n\n";
    
    // Get statistics
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
    
    echo "📊 DATABASE STATISTICS:\n";
    echo "  • Total call logs: " . number_format($stats->total) . "\n";
    echo "  • Date range: " . $stats->oldest . " to " . $stats->newest . "\n";
    echo "  • Unique phone numbers: " . number_format($stats->unique_phones) . "\n";
    echo "  • Unique lead IDs: " . number_format($stats->unique_leads) . "\n";
    echo "  • Unique lists: " . number_format($stats->unique_lists) . "\n\n";
    
    // Status distribution
    echo "📈 STATUS DISTRIBUTION:\n";
    $statuses = DB::table('orphan_call_logs')
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
    
    foreach ($statuses as $status) {
        $statusName = $status->status ?: 'NULL';
        $percentage = round(($status->count / $stats->total) * 100, 1);
        echo "  • {$statusName}: " . number_format($status->count) . " ({$percentage}%)\n";
    }
    
    // List distribution
    echo "\n📋 LIST DISTRIBUTION:\n";
    $lists = DB::table('orphan_call_logs')
        ->select('list_id', DB::raw('COUNT(*) as count'))
        ->whereNotNull('list_id')
        ->groupBy('list_id')
        ->orderBy('list_id')
        ->get();
    
    if ($lists->isEmpty()) {
        echo "  No list data available\n";
    } else {
        foreach ($lists as $list) {
            echo "  • List {$list->list_id}: " . number_format($list->count) . " calls\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Import failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ IMPORT PROCESS COMPLETE!\n";
echo "================================================================================\n";










