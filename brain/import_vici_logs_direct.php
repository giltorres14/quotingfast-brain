<?php
/**
 * Direct Import of Vici Call Logs for Analysis
 * Creates sample data if real Vici connection is not available
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n================================================================================\n";
echo "                    VICI CALL LOG IMPORT & ANALYSIS                            \n";
echo "================================================================================\n\n";

// Check if we can connect to Vici database
$viciConnection = null;
try {
    // Try to establish Vici connection
    config(['database.connections.vici' => [
        'driver' => 'mysql',
        'host' => env('VICI_DB_HOST', '167.172.143.47'),
        'port' => env('VICI_DB_PORT', '3306'),
        'database' => env('VICI_DB_DATABASE', 'asterisk'),
        'username' => env('VICI_DB_USERNAME', 'cron'),
        'password' => env('VICI_DB_PASSWORD', '1234'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    $viciConnection = DB::connection('vici');
    $test = $viciConnection->select('SELECT 1');
    echo "✅ Connected to Vici database successfully!\n\n";
} catch (\Exception $e) {
    echo "⚠️ Cannot connect to Vici database: " . $e->getMessage() . "\n";
    echo "📊 Generating sample data for analysis demonstration...\n\n";
    $viciConnection = null;
}

if ($viciConnection) {
    // REAL DATA IMPORT
    echo "Importing last 90 days of call logs...\n";
    
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(90);
    
    // Create table if not exists
    DB::statement("
        CREATE TABLE IF NOT EXISTS vici_call_logs (
            id BIGSERIAL PRIMARY KEY,
            uniqueid VARCHAR(50),
            lead_id BIGINT,
            list_id INT,
            campaign_id VARCHAR(50),
            call_date TIMESTAMP,
            start_epoch BIGINT,
            end_epoch BIGINT,
            length_in_sec INT,
            status VARCHAR(10),
            phone_number VARCHAR(20),
            agent_user VARCHAR(50),
            comments TEXT,
            processed INT DEFAULT 0,
            queue_seconds INT DEFAULT 0,
            user_group VARCHAR(50),
            term_reason VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Import in chunks
    $offset = 0;
    $limit = 5000;
    $totalImported = 0;
    
    while (true) {
        $logs = $viciConnection->select("
            SELECT 
                uniqueid,
                lead_id,
                list_id,
                campaign_id,
                call_date,
                start_epoch,
                end_epoch,
                length_in_sec,
                status,
                phone_number,
                user,
                comments,
                processed,
                queue_seconds,
                user_group,
                term_reason
            FROM vicidial_log
            WHERE call_date BETWEEN ? AND ?
            ORDER BY call_date
            LIMIT ? OFFSET ?
        ", [$startDate, $endDate, $limit, $offset]);
        
        if (empty($logs)) {
            break;
        }
        
        // Insert into our database
        foreach ($logs as $log) {
            DB::table('vici_call_logs')->insertOrIgnore([
                'uniqueid' => $log->uniqueid,
                'lead_id' => $log->lead_id,
                'list_id' => $log->list_id,
                'campaign_id' => $log->campaign_id,
                'call_date' => $log->call_date,
                'start_epoch' => $log->start_epoch,
                'end_epoch' => $log->end_epoch,
                'length_in_sec' => $log->length_in_sec,
                'status' => $log->status,
                'phone_number' => $log->phone_number,
                'user' => $log->user,
                'comments' => $log->comments,
                'processed' => $log->processed,
                'queue_seconds' => $log->queue_seconds,
                'user_group' => $log->user_group,
                'term_reason' => $log->term_reason,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $totalImported += count($logs);
        echo "Imported " . number_format($totalImported) . " call logs...\r";
        
        $offset += $limit;
    }
    
    echo "\n✅ Successfully imported " . number_format($totalImported) . " call logs!\n\n";
    
} else {
    // SAMPLE DATA GENERATION
    echo "Creating sample call log data based on typical patterns...\n";
    
    // Create table
    DB::statement("
        CREATE TABLE IF NOT EXISTS vici_call_logs (
            id BIGSERIAL PRIMARY KEY,
            uniqueid VARCHAR(50),
            lead_id BIGINT,
            list_id INT,
            campaign_id VARCHAR(50),
            call_date TIMESTAMP,
            start_epoch BIGINT,
            end_epoch BIGINT,
            length_in_sec INT,
            status VARCHAR(10),
            phone_number VARCHAR(20),
            agent_user VARCHAR(50),
            comments TEXT,
            processed INT DEFAULT 0,
            queue_seconds INT DEFAULT 0,
            user_group VARCHAR(50),
            term_reason VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Clear existing sample data
    DB::table('vici_call_logs')->truncate();
    
    // Generate 90 days of sample data
    $statuses = ['NA' => 60, 'SALE' => 5, 'NI' => 10, 'AL' => 15, 'B' => 5, 'DNC' => 2, 'XFER' => 3];
    $lists = [101, 102, 103, 104, 105, 106, 107, 108, 109, 110];
    $campaigns = ['AUTODIAL', 'AUTO2'];
    
    $totalGenerated = 0;
    $batchSize = 1000;
    $batch = [];
    
    for ($days = 90; $days >= 0; $days--) {
        $date = Carbon::now()->subDays($days);
        $dailyCalls = rand(800, 1500); // Random calls per day
        
        for ($i = 0; $i < $dailyCalls; $i++) {
            $leadId = rand(1000000, 9999999);
            $status = weightedRandom($statuses);
            $listId = $lists[array_rand($lists)];
            
            // Determine call duration based on status
            $duration = 0;
            if ($status == 'SALE' || $status == 'XFER') {
                $duration = rand(120, 600); // 2-10 minutes for sales
            } elseif ($status == 'NI' || $status == 'DNC') {
                $duration = rand(30, 120); // 30 sec - 2 min for contacts
            } elseif ($status == 'AL') {
                $duration = rand(20, 40); // 20-40 sec for voicemail
            } else {
                $duration = rand(3, 15); // 3-15 sec for no answer
            }
            
            $callTime = $date->copy()->addHours(rand(9, 18))->addMinutes(rand(0, 59));
            
            $batch[] = [
                'uniqueid' => $date->format('Ymd') . '.' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'lead_id' => $leadId,
                'list_id' => $listId,
                'campaign_id' => $campaigns[array_rand($campaigns)],
                'call_date' => $callTime,
                'start_epoch' => $callTime->timestamp,
                'end_epoch' => $callTime->timestamp + $duration,
                'length_in_sec' => $duration,
                'status' => $status,
                'phone_number' => '9' . rand(100, 999) . rand(1000000, 9999999),
                'agent_user' => 'VDAD',
                'comments' => 'AUTO',
                'processed' => 0,
                'queue_seconds' => rand(0, 30),
                'user_group' => 'AGENTS',
                'term_reason' => 'AGENT',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if (count($batch) >= $batchSize) {
                DB::table('vici_call_logs')->insert($batch);
                $totalGenerated += count($batch);
                echo "Generated " . number_format($totalGenerated) . " sample call logs...\r";
                $batch = [];
            }
        }
    }
    
    // Insert remaining batch
    if (!empty($batch)) {
        DB::table('vici_call_logs')->insert($batch);
        $totalGenerated += count($batch);
    }
    
    echo "\n✅ Generated " . number_format($totalGenerated) . " sample call logs!\n\n";
}

// Helper function for weighted random selection
function weightedRandom($weights) {
    $rand = rand(1, array_sum($weights));
    foreach ($weights as $key => $weight) {
        $rand -= $weight;
        if ($rand <= 0) {
            return $key;
        }
    }
    return array_key_first($weights);
}

echo "================================================================================\n";
echo "                    GENERATING COMPREHENSIVE REPORTS                            \n";
echo "================================================================================\n\n";

// Now generate the reports
include 'generate_vici_reports.php';
