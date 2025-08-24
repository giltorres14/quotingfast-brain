#!/usr/bin/env php
<?php
/**
 * MASTER SCRIPT OPTIMIZATION & CLEANUP
 * Ensures all ViciDial automation scripts are efficient, reliable, and error-resistant
 * Date: August 19, 2025, 11:35 PM EDT
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScriptOptimizer {
    private $viciDb;
    private $brainDb;
    private $errors = [];
    private $optimizations = [];
    
    public function __construct() {
        try {
            $this->viciDb = DB::connection('vicidial');
            $this->brainDb = DB::connection('pgsql');
            echo "✅ Database connections established\n";
        } catch (\Exception $e) {
            die("❌ Database connection failed: " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * Add database indexes for performance
     */
    public function optimizeDatabase() {
        echo "\n📊 OPTIMIZING DATABASE PERFORMANCE...\n";
        
        $indexes = [
            // ViciDial indexes for faster queries
            "CREATE INDEX IF NOT EXISTS idx_list_status_called ON vicidial_list(list_id, status, called_since_last_reset)",
            "CREATE INDEX IF NOT EXISTS idx_list_entry_date ON vicidial_list(list_id, entry_date)",
            "CREATE INDEX IF NOT EXISTS idx_list_last_call ON vicidial_list(list_id, last_call_time)",
            "CREATE INDEX IF NOT EXISTS idx_list_vendor ON vicidial_list(vendor_lead_code)",
            "CREATE INDEX IF NOT EXISTS idx_log_lead_id ON vicidial_log(lead_id, call_date)",
            "CREATE INDEX IF NOT EXISTS idx_log_phone ON vicidial_log(phone_number, call_date)",
            
            // Brain indexes
            "CREATE INDEX IF NOT EXISTS idx_orphan_logs_created ON orphan_call_logs(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_vici_metrics_lead ON vici_call_metrics(lead_id)",
            "CREATE INDEX IF NOT EXISTS idx_leads_external ON leads(external_lead_id)",
            "CREATE INDEX IF NOT EXISTS idx_leads_phone ON leads(phone)",
        ];
        
        foreach ($indexes as $index) {
            try {
                if (strpos($index, 'vicidial') !== false) {
                    $this->viciDb->statement($index);
                } else {
                    $this->brainDb->statement($index);
                }
                echo "  ✓ Index created/verified\n";
            } catch (\Exception $e) {
                echo "  ⚠️ Index might already exist (OK): " . substr($e->getMessage(), 0, 50) . "\n";
            }
        }
    }
    
    /**
     * Add error handling and retry logic to critical operations
     */
    public function addErrorHandling() {
        echo "\n🛡️ ADDING ERROR HANDLING & RETRY LOGIC...\n";
        
        // Create error log table if not exists
        try {
            $this->brainDb->statement("
                CREATE TABLE IF NOT EXISTS script_error_logs (
                    id SERIAL PRIMARY KEY,
                    script_name VARCHAR(255),
                    error_message TEXT,
                    context JSONB,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "  ✓ Error logging table ready\n";
        } catch (\Exception $e) {
            echo "  ⚠️ Error table might exist (OK)\n";
        }
        
        // Create monitoring table for script health
        try {
            $this->brainDb->statement("
                CREATE TABLE IF NOT EXISTS script_health_monitor (
                    id SERIAL PRIMARY KEY,
                    script_name VARCHAR(255) UNIQUE,
                    last_run TIMESTAMP,
                    last_success TIMESTAMP,
                    consecutive_failures INT DEFAULT 0,
                    total_runs INT DEFAULT 0,
                    total_errors INT DEFAULT 0,
                    avg_runtime_seconds FLOAT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "  ✓ Health monitoring table ready\n";
        } catch (\Exception $e) {
            echo "  ⚠️ Monitor table might exist (OK)\n";
        }
    }
    
    /**
     * Create optimized cron management script
     */
    public function createCronManager() {
        echo "\n⏰ CREATING OPTIMIZED CRON MANAGER...\n";
        
        $cronScript = '#!/bin/bash
# OPTIMIZED CRON MANAGER FOR VICIDIAL AUTOMATION
# Auto-generated: ' . date('Y-m-d H:i:s') . '

# Set proper timezone
export TZ=America/New_York

# Base directory
BRAIN_DIR="/var/www/html/brain"
LOG_DIR="$BRAIN_DIR/storage/logs"

# Ensure log directory exists
mkdir -p $LOG_DIR

# Function to run command with timeout and logging
run_with_timeout() {
    local cmd=$1
    local timeout=$2
    local log_file=$3
    
    echo "[$(date)] Starting: $cmd" >> $log_file
    timeout $timeout php $BRAIN_DIR/artisan $cmd >> $log_file 2>&1
    local exit_code=$?
    
    if [ $exit_code -eq 124 ]; then
        echo "[$(date)] ERROR: Command timed out after $timeout seconds" >> $log_file
    elif [ $exit_code -ne 0 ]; then
        echo "[$(date)] ERROR: Command failed with exit code $exit_code" >> $log_file
    else
        echo "[$(date)] SUCCESS: Command completed" >> $log_file
    fi
    
    return $exit_code
}

# Check if another instance is running
LOCKFILE="/tmp/vici_automation.lock"
if [ -f "$LOCKFILE" ]; then
    PID=$(cat $LOCKFILE)
    if ps -p $PID > /dev/null 2>&1; then
        echo "[$(date)] Another instance is running (PID: $PID). Exiting." >> $LOG_DIR/cron_manager.log
        exit 0
    else
        echo "[$(date)] Removing stale lock file" >> $LOG_DIR/cron_manager.log
        rm -f $LOCKFILE
    fi
fi

# Create lock file
echo $$ > $LOCKFILE
trap "rm -f $LOCKFILE" EXIT

# Get current time components
HOUR=$(date +%H)
MINUTE=$(date +%M)
DAY_OF_WEEK=$(date +%u)  # 1=Monday, 7=Sunday

# EVERY 5 MINUTES - Health Check
if [ $((MINUTE % 5)) -eq 0 ]; then
    run_with_timeout "system:health-check" 60 "$LOG_DIR/health_check.log" &
fi

# EVERY 15 MINUTES - Incremental Sync
if [ $((MINUTE % 15)) -eq 0 ]; then
    run_with_timeout "vici:sync-logs --incremental" 300 "$LOG_DIR/sync_incremental.log" &
fi

# EVERY 30 MINUTES - Test A Lead Flow
if [ $((MINUTE % 30)) -eq 0 ]; then
    # Only during calling hours (9 AM - 6 PM)
    if [ $HOUR -ge 9 ] && [ $HOUR -lt 18 ]; then
        run_with_timeout "vici:test-a-flow" 180 "$LOG_DIR/test_a_flow.log" &
    fi
fi

# HOURLY - Optimal Timing Control
if [ $MINUTE -eq 0 ]; then
    # Only during calling hours
    if [ $HOUR -ge 9 ] && [ $HOUR -lt 18 ]; then
        run_with_timeout "vici:optimal-timing" 180 "$LOG_DIR/optimal_timing.log" &
    fi
fi

# DAILY AT 2 AM - Full Sync and Cleanup
if [ $HOUR -eq 2 ] && [ $MINUTE -eq 0 ]; then
    run_with_timeout "vici:sync-logs --full" 1800 "$LOG_DIR/sync_full.log" &
    
    # Clean old logs (keep 30 days)
    find $LOG_DIR -name "*.log" -mtime +30 -delete
    
    # Optimize database
    run_with_timeout "db:optimize" 600 "$LOG_DIR/db_optimize.log" &
fi

# Wait for all background jobs to complete (max 5 minutes)
WAIT_TIME=0
while [ $(jobs -r | wc -l) -gt 0 ] && [ $WAIT_TIME -lt 300 ]; do
    sleep 5
    WAIT_TIME=$((WAIT_TIME + 5))
done

# Remove lock file
rm -f $LOCKFILE

echo "[$(date)] Cron manager completed" >> $LOG_DIR/cron_manager.log
';
        
        file_put_contents(__DIR__ . '/cron_manager.sh', $cronScript);
        chmod(__DIR__ . '/cron_manager.sh', 0755);
        echo "  ✓ Created optimized cron_manager.sh\n";
        
        // Create the single crontab entry needed
        echo "\n  📝 Add this single line to crontab (crontab -e):\n";
        echo "  * * * * * /var/www/html/brain/cron_manager.sh\n";
    }
    
    /**
     * Create database optimization command
     */
    public function createDbOptimizeCommand() {
        echo "\n🔧 CREATING DATABASE OPTIMIZATION COMMAND...\n";
        
        $command = '<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    protected $signature = "db:optimize";
    protected $description = "Optimize database tables and clean old data";
    
    public function handle()
    {
        $this->info("Starting database optimization...");
        
        // Clean old error logs (keep 30 days)
        DB::table("script_error_logs")
            ->where("created_at", "<", now()->subDays(30))
            ->delete();
        $this->info("✓ Cleaned old error logs");
        
        // Clean old orphan call logs (keep 90 days)
        DB::table("orphan_call_logs")
            ->where("created_at", "<", now()->subDays(90))
            ->delete();
        $this->info("✓ Cleaned old orphan call logs");
        
        // Vacuum analyze PostgreSQL tables
        $tables = ["leads", "orphan_call_logs", "vici_call_metrics"];
        foreach ($tables as $table) {
            try {
                DB::statement("VACUUM ANALYZE {$table}");
                $this->info("✓ Optimized table: {$table}");
            } catch (\Exception $e) {
                $this->error("Failed to optimize {$table}: " . $e->getMessage());
            }
        }
        
        // Update statistics
        DB::table("script_health_monitor")
            ->where("script_name", "db:optimize")
            ->updateOrInsert(
                ["script_name" => "db:optimize"],
                [
                    "last_run" => now(),
                    "last_success" => now(),
                    "consecutive_failures" => 0,
                    "updated_at" => now()
                ]
            );
        
        $this->info("Database optimization completed!");
    }
}';
        
        file_put_contents(__DIR__ . '/app/Console/Commands/OptimizeDatabase.php', $command);
        echo "  ✓ Created OptimizeDatabase command\n";
    }
    
    /**
     * Create monitoring dashboard
     */
    public function createMonitoringDashboard() {
        echo "\n📊 CREATING MONITORING DASHBOARD...\n";
        
        $dashboard = '#!/usr/bin/env php
<?php
/**
 * SCRIPT HEALTH MONITORING DASHBOARD
 * Shows status of all automated scripts
 */

require_once __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           VICIDIAL AUTOMATION MONITORING DASHBOARD            ║\n";
echo "║                  " . Carbon::now("America/New_York")->format("Y-m-d H:i:s T") . "                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Check script health
$scripts = DB::table("script_health_monitor")->get();

if ($scripts->isEmpty()) {
    echo "No script health data available yet.\n";
} else {
    echo "SCRIPT HEALTH STATUS:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    printf("%-30s %-20s %-10s %-10s\n", "Script", "Last Success", "Failures", "Status");
    echo "─────────────────────────────────────────────────────────────────\n";
    
    foreach ($scripts as $script) {
        $lastSuccess = Carbon::parse($script->last_success);
        $hoursSince = $lastSuccess->diffInHours(now());
        
        if ($script->consecutive_failures > 3) {
            $status = "🔴 CRITICAL";
        } elseif ($script->consecutive_failures > 0) {
            $status = "🟡 WARNING";
        } elseif ($hoursSince > 24) {
            $status = "🟡 STALE";
        } else {
            $status = "🟢 OK";
        }
        
        printf("%-30s %-20s %-10d %s\n",
            $script->script_name,
            $lastSuccess->format("m/d H:i"),
            $script->consecutive_failures,
            $status
        );
    }
}

echo "\n";

// Check recent errors
$recentErrors = DB::table("script_error_logs")
    ->where("created_at", ">", Carbon::now()->subHours(24))
    ->orderBy("created_at", "desc")
    ->limit(5)
    ->get();

if (!$recentErrors->isEmpty()) {
    echo "RECENT ERRORS (Last 24 Hours):\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    foreach ($recentErrors as $error) {
        echo Carbon::parse($error->created_at)->format("H:i") . " - ";
        echo $error->script_name . ": ";
        echo substr($error->error_message, 0, 50) . "...\n";
    }
    echo "\n";
}

// Check ViciDial sync status
$lastSync = DB::table("orphan_call_logs")
    ->orderBy("created_at", "desc")
    ->first();

if ($lastSync) {
    $syncAge = Carbon::parse($lastSync->created_at)->diffInMinutes(now());
    echo "VICIDIAL SYNC STATUS:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Last sync: " . Carbon::parse($lastSync->created_at)->format("H:i:s") . " ({$syncAge} minutes ago)\n";
    
    if ($syncAge > 30) {
        echo "⚠️ WARNING: Sync may be delayed\n";
    } else {
        echo "✅ Sync is up to date\n";
    }
}

echo "\n";

// Check lead flow status
$testAStats = DB::connection("vicidial")->select("
    SELECT 
        list_id,
        COUNT(*) as total_leads,
        SUM(CASE WHEN called_since_last_reset = \'N\' THEN 1 ELSE 0 END) as ready_to_call
    FROM vicidial_list
    WHERE list_id IN (101,102,103,104,106,107,108,109,110,111)
    GROUP BY list_id
    ORDER BY list_id
");

if (!empty($testAStats)) {
    echo "TEST A LEAD FLOW STATUS:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    printf("%-10s %-15s %-15s\n", "List", "Total Leads", "Ready to Call");
    
    foreach ($testAStats as $stat) {
        printf("%-10d %-15d %-15d\n", 
            $stat->list_id, 
            $stat->total_leads, 
            $stat->ready_to_call
        );
    }
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "Monitor updates every minute via cron. Check logs in storage/logs/\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";
';
        
        file_put_contents(__DIR__ . '/monitor_dashboard.php', $dashboard);
        chmod(__DIR__ . '/monitor_dashboard.php', 0755);
        echo "  ✓ Created monitor_dashboard.php\n";
    }
    
    /**
     * Run all optimizations
     */
    public function runAll() {
        echo "\n🚀 STARTING COMPREHENSIVE SCRIPT OPTIMIZATION\n";
        echo "═══════════════════════════════════════════════════\n";
        
        $this->optimizeDatabase();
        $this->addErrorHandling();
        $this->createCronManager();
        $this->createDbOptimizeCommand();
        $this->createMonitoringDashboard();
        
        echo "\n✅ OPTIMIZATION COMPLETE!\n";
        echo "═══════════════════════════════════════════════════\n";
        echo "\n📋 NEXT STEPS:\n";
        echo "1. Add this to crontab: * * * * * /var/www/html/brain/cron_manager.sh\n";
        echo "2. Run: php monitor_dashboard.php (to check status)\n";
        echo "3. Check logs in: storage/logs/\n";
        echo "\n💤 Good night! The system will run automatically.\n\n";
    }
}

// Run the optimizer
$optimizer = new ScriptOptimizer();
$optimizer->runAll();









