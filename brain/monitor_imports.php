<?php
/**
 * Monitor and auto-restart import processes
 * Checks every 5 minutes and restarts if needed
 */

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logFile = __DIR__ . '/monitor.log';

function log_status($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

function check_process($processName) {
    $output = shell_exec("ps aux | grep '$processName' | grep -v grep");
    return !empty(trim($output));
}

function get_import_progress() {
    // Check orphan_call_logs count
    $count = DB::table('orphan_call_logs')->count();
    
    // Check last import time
    $lastImport = DB::table('orphan_call_logs')
        ->orderBy('created_at', 'desc')
        ->first();
    
    return [
        'count' => $count,
        'last_import' => $lastImport ? $lastImport->created_at : null,
        'target' => 800736
    ];
}

function get_vendor_sync_progress() {
    // Check vendor_sync.log for progress
    if (file_exists(__DIR__ . '/vendor_sync.log')) {
        $lastLines = shell_exec('tail -5 ' . __DIR__ . '/vendor_sync.log');
        if (strpos($lastLines, 'SYNC COMPLETE') !== false) {
            return ['status' => 'complete'];
        }
        // Extract progress from log
        if (preg_match('/Progress: Processed (\d+) Brain leads/', $lastLines, $matches)) {
            return [
                'status' => 'running',
                'processed' => $matches[1],
                'total' => 237654
            ];
        }
    }
    return ['status' => 'unknown'];
}

function restart_import() {
    log_status("⚠️ Restarting 90-day import...");
    
    // Kill any existing import process
    shell_exec("pkill -f 'import_90_days_optimized.php'");
    sleep(2);
    
    // Get current progress to resume
    $progress = get_import_progress();
    $resumePoint = floor($progress['count'] / 10000) * 10000; // Round down to nearest 10k
    
    // Restart the import
    $cmd = "cd " . __DIR__ . " && nohup php import_90_days_optimized.php --resume=$resumePoint > import_optimized.log 2>&1 &";
    shell_exec($cmd);
    
    log_status("✅ Import restarted from record $resumePoint");
}

function restart_vendor_sync() {
    log_status("⚠️ Restarting vendor sync...");
    
    // Kill any existing sync process
    shell_exec("pkill -f 'batch_sync_vendor_codes.php'");
    sleep(2);
    
    // Restart the sync
    $cmd = "cd " . __DIR__ . " && nohup php batch_sync_vendor_codes.php > vendor_sync.log 2>&1 &";
    shell_exec($cmd);
    
    log_status("✅ Vendor sync restarted");
}

// Main monitoring loop
log_status("🚀 Starting automated monitoring (checks every 5 minutes)");

$importComplete = false;
$vendorComplete = false;
$lastImportCount = 0;
$stuckCounter = 0;

while (!$importComplete || !$vendorComplete) {
    log_status("=" . str_repeat("=", 60));
    log_status("📊 Checking status...");
    
    // Check 90-day import
    if (!$importComplete) {
        $importRunning = check_process('import_90_days_optimized.php');
        $importProgress = get_import_progress();
        
        log_status(sprintf(
            "Import: %s | Records: %s / %s (%.1f%%)",
            $importRunning ? "RUNNING" : "STOPPED",
            number_format($importProgress['count']),
            number_format($importProgress['target']),
            ($importProgress['count'] / $importProgress['target']) * 100
        ));
        
        // Check if import is complete
        if ($importProgress['count'] >= $importProgress['target'] * 0.95) { // 95% is good enough
            $importComplete = true;
            log_status("✅ Import COMPLETE!");
        }
        // Check if import is stuck
        elseif (!$importRunning) {
            log_status("❌ Import not running - restarting...");
            restart_import();
        }
        elseif ($importProgress['count'] == $lastImportCount) {
            $stuckCounter++;
            if ($stuckCounter >= 3) { // Stuck for 15 minutes
                log_status("⚠️ Import appears stuck - restarting...");
                restart_import();
                $stuckCounter = 0;
            }
        } else {
            $stuckCounter = 0; // Reset if progress is made
        }
        
        $lastImportCount = $importProgress['count'];
    }
    
    // Check vendor sync
    if (!$vendorComplete) {
        $vendorRunning = check_process('batch_sync_vendor_codes.php');
        $vendorProgress = get_vendor_sync_progress();
        
        if ($vendorProgress['status'] == 'complete') {
            $vendorComplete = true;
            log_status("✅ Vendor sync COMPLETE!");
        } elseif ($vendorProgress['status'] == 'running') {
            log_status(sprintf(
                "Vendor Sync: RUNNING | Processed: %s / %s",
                number_format($vendorProgress['processed']),
                number_format($vendorProgress['total'])
            ));
        } elseif (!$vendorRunning && !$vendorComplete) {
            log_status("❌ Vendor sync not running - restarting...");
            restart_vendor_sync();
        }
    }
    
    // If both complete, exit
    if ($importComplete && $vendorComplete) {
        log_status("🎉 ALL PROCESSES COMPLETE!");
        
        // Final summary
        $finalImport = get_import_progress();
        log_status(sprintf(
            "Final Stats: %s call logs imported",
            number_format($finalImport['count'])
        ));
        
        // Update health check cache
        \Cache::put('import_complete', true, 3600);
        
        break;
    }
    
    // Wait 5 minutes before next check
    log_status("💤 Waiting 5 minutes before next check...\n");
    sleep(300); // 5 minutes
}

log_status("Monitor script completed successfully!");











