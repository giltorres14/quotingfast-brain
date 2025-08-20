<?php
/**
 * MONITORED Vici Call Log Import
 * Imports from actual Vici database with progress monitoring
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$startTime = microtime(true);
$logFile = storage_path('logs/vici_import_' . date('Y-m-d_H-i-s') . '.log');

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo $message . "\n";
}

logMessage("================================================================================", $logFile);
logMessage("                    VICI CALL LOG IMPORT WITH MONITORING                       ", $logFile);
logMessage("================================================================================", $logFile);
logMessage("Log file: $logFile", $logFile);

// First, let's check what data is actually available in Vici
$viciProxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

logMessage("\n📊 Checking Vici database for available data...", $logFile);

// Check what tables exist
$tablesQuery = "SHOW TABLES LIKE 'vicidial%'";
$response = Http::timeout(30)->post($viciProxyUrl, ['query' => $tablesQuery]);

if ($response->successful()) {
    $tables = $response->json()['data'] ?? [];
    logMessage("Found " . count($tables) . " Vici tables", $logFile);
} else {
    logMessage("❌ Failed to query Vici tables: " . $response->body(), $logFile);
    exit(1);
}

// Check for actual call data in different tables
$tablesToCheck = [
    'vicidial_log' => "SELECT COUNT(*) as count, MIN(call_date) as oldest, MAX(call_date) as newest FROM vicidial_log WHERE call_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
    'vicidial_dial_log' => "SELECT COUNT(*) as count, MIN(call_date) as oldest, MAX(call_date) as newest FROM vicidial_dial_log WHERE call_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
    'vicidial_closer_log' => "SELECT COUNT(*) as count, MIN(call_date) as oldest, MAX(call_date) as newest FROM vicidial_closer_log WHERE call_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
    'vicidial_agent_log' => "SELECT COUNT(*) as count, MIN(event_time) as oldest, MAX(event_time) as newest FROM vicidial_agent_log WHERE event_time >= DATE_SUB(NOW(), INTERVAL 90 DAY)"
];

$availableData = [];
foreach ($tablesToCheck as $table => $query) {
    $response = Http::timeout(30)->post($viciProxyUrl, ['query' => $query]);
    if ($response->successful()) {
        $data = $response->json()['data'][0] ?? [];
        if ($data['count'] > 0) {
            $availableData[$table] = $data;
            logMessage("  ✅ $table: " . number_format($data['count']) . " records from {$data['oldest']} to {$data['newest']}", $logFile);
        } else {
            logMessage("  ⚠️ $table: No data in last 90 days", $logFile);
        }
    } else {
        logMessage("  ❌ $table: Query failed", $logFile);
    }
}

if (empty($availableData)) {
    logMessage("\n❌ NO DATA FOUND in any Vici tables for the last 90 days!", $logFile);
    logMessage("This could mean:", $logFile);
    logMessage("  1. The Vici system is new/empty", $logFile);
    logMessage("  2. Data has been purged", $logFile);
    logMessage("  3. Different campaign names are being used", $logFile);
    
    // Check what campaigns exist
    $campaignQuery = "SELECT DISTINCT campaign_id FROM vicidial_log LIMIT 10";
    $response = Http::timeout(30)->post($viciProxyUrl, ['query' => $campaignQuery]);
    if ($response->successful()) {
        $campaigns = $response->json()['data'] ?? [];
        if (!empty($campaigns)) {
            logMessage("\nFound campaigns in vicidial_log:", $logFile);
            foreach ($campaigns as $campaign) {
                logMessage("  - " . $campaign['campaign_id'], $logFile);
            }
        }
    }
    
    exit(1);
}

// Now import the data we found
logMessage("\n🔄 Starting import process...", $logFile);

// Ensure our table has all needed columns
DB::statement("
    CREATE TABLE IF NOT EXISTS orphan_call_logs (
        id BIGSERIAL PRIMARY KEY,
        call_date TIMESTAMP,
        vici_lead_id BIGINT,
        list_id INT,
        phone_number VARCHAR(20),
        campaign_id VARCHAR(50),
        status VARCHAR(10),
        length_in_sec INT,
        agent_user VARCHAR(50),
        term_reason VARCHAR(50),
        vendor_lead_code VARCHAR(255),
        uniqueid VARCHAR(50),
        source_table VARCHAR(50),
        disposition VARCHAR(50),
        talk_time INT,
        agent_id VARCHAR(50),
        call_data TEXT,
        matched_lead_id BIGINT,
        matched_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(uniqueid)
    )
");

$totalImported = 0;
$errors = 0;

// Import from each table that has data
foreach ($availableData as $table => $info) {
    logMessage("\n📥 Importing from $table...", $logFile);
    
    $imported = 0;
    $offset = 0;
    $batchSize = 1000;
    
    while ($offset < $info['count']) {
        // Build appropriate query based on table
        if ($table == 'vicidial_log' || $table == 'vicidial_dial_log') {
            $query = "
                SELECT 
                    call_date,
                    lead_id as vici_lead_id,
                    list_id,
                    phone_number,
                    campaign_id,
                    status,
                    length_in_sec,
                    user as agent_user,
                    term_reason,
                    uniqueid,
                    '$table' as source_table
                FROM $table
                WHERE call_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                ORDER BY call_date DESC
                LIMIT $batchSize OFFSET $offset
            ";
        } elseif ($table == 'vicidial_closer_log') {
            $query = "
                SELECT 
                    call_date,
                    lead_id as vici_lead_id,
                    list_id,
                    phone_number,
                    campaign_id,
                    status,
                    length_in_sec,
                    user as agent_user,
                    term_reason,
                    uniqueid,
                    '$table' as source_table
                FROM $table
                WHERE call_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                ORDER BY call_date DESC
                LIMIT $batchSize OFFSET $offset
            ";
        } else {
            // Skip agent_log for now as it has different structure
            break;
        }
        
        $response = Http::timeout(60)->post($viciProxyUrl, ['query' => $query]);
        
        if ($response->successful()) {
            $records = $response->json()['data'] ?? [];
            
            if (empty($records)) {
                break; // No more records
            }
            
            foreach ($records as $record) {
                try {
                    DB::table('orphan_call_logs')->insertOrIgnore([
                        'call_date' => $record['call_date'] ?? null,
                        'vici_lead_id' => $record['vici_lead_id'] ?? null,
                        'list_id' => $record['list_id'] ?? null,
                        'phone_number' => $record['phone_number'] ?? null,
                        'campaign_id' => $record['campaign_id'] ?? null,
                        'status' => $record['status'] ?? null,
                        'length_in_sec' => $record['length_in_sec'] ?? 0,
                        'agent_user' => $record['agent_user'] ?? null,
                        'term_reason' => $record['term_reason'] ?? null,
                        'uniqueid' => $record['uniqueid'] ?? null,
                        'source_table' => $record['source_table'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $imported++;
                    $totalImported++;
                } catch (\Exception $e) {
                    $errors++;
                    if ($errors <= 5) {
                        logMessage("  ⚠️ Error inserting record: " . $e->getMessage(), $logFile);
                    }
                }
            }
            
            logMessage("  Progress: " . number_format($offset + count($records)) . " / " . number_format($info['count']) . " records", $logFile);
        } else {
            logMessage("  ❌ Failed to fetch batch at offset $offset", $logFile);
            $errors++;
            if ($errors > 10) {
                logMessage("  Too many errors, stopping import from $table", $logFile);
                break;
            }
        }
        
        $offset += $batchSize;
        
        // Small delay to avoid overwhelming the server
        usleep(100000); // 0.1 second
    }
    
    logMessage("  ✅ Imported $imported records from $table", $logFile);
}

// Final statistics
logMessage("\n================================================================================", $logFile);
logMessage("✅ IMPORT COMPLETE", $logFile);
logMessage("  • Total records imported: " . number_format($totalImported), $logFile);
logMessage("  • Errors encountered: $errors", $logFile);

// Get statistics from our database
$stats = DB::table('orphan_call_logs')->selectRaw('
    COUNT(*) as total,
    MIN(call_date) as oldest,
    MAX(call_date) as newest,
    COUNT(DISTINCT phone_number) as unique_phones,
    COUNT(DISTINCT vici_lead_id) as unique_leads,
    COUNT(DISTINCT list_id) as unique_lists
')->first();

logMessage("  • Date range: {$stats->oldest} to {$stats->newest}", $logFile);
logMessage("  • Unique phone numbers: " . number_format($stats->unique_phones), $logFile);
logMessage("  • Unique lead IDs: " . number_format($stats->unique_leads), $logFile);
logMessage("  • Unique lists: " . number_format($stats->unique_lists), $logFile);

// Status distribution
logMessage("\n📊 Status Distribution:", $logFile);
$statuses = DB::table('orphan_call_logs')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->whereNotNull('status')
    ->groupBy('status')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($statuses as $status) {
    logMessage("  • {$status->status}: " . number_format($status->count), $logFile);
}

// List distribution
if ($stats->unique_lists > 0) {
    logMessage("\n📋 List Distribution:", $logFile);
    $lists = DB::table('orphan_call_logs')
        ->select('list_id', DB::raw('COUNT(*) as count'))
        ->whereNotNull('list_id')
        ->groupBy('list_id')
        ->orderBy('list_id')
        ->limit(20)
        ->get();
    
    foreach ($lists as $list) {
        logMessage("  • List {$list->list_id}: " . number_format($list->count) . " calls", $logFile);
    }
}

$executionTime = round(microtime(true) - $startTime, 2);
logMessage("\n⏱️ Execution time: {$executionTime} seconds", $logFile);
logMessage("📁 Full log saved to: $logFile", $logFile);

// Alert if no data was imported
if ($totalImported == 0) {
    logMessage("\n⚠️ WARNING: No data was imported! Please check:", $logFile);
    logMessage("  1. Vici database connection", $logFile);
    logMessage("  2. Campaign names (AUTODIAL, AUTO2)", $logFile);
    logMessage("  3. Date ranges in Vici", $logFile);
    
    // Send alert to user
    echo "\n\n🚨 ALERT: Import completed but NO DATA was imported!\n";
    echo "Check the log file for details: $logFile\n";
}

