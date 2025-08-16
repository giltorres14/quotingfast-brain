<?php
// Continuous Vici Sync Worker for Render
// This runs as a background process and syncs every 5 minutes

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;

echo "Vici Sync Worker Started\n";
echo "Will sync every 5 minutes\n";
echo "=====================================\n";

while (true) {
    try {
        $startTime = time();
        echo "[" . date('Y-m-d H:i:s') . "] Starting sync...\n";
        
        // Run your sync script here
        // Once you provide the script, we'll integrate it
        
        // For now, just log that we're ready
        Log::info('Vici sync worker cycle', [
            'timestamp' => now(),
            'status' => 'ready_for_script'
        ]);
        
        echo "[" . date('Y-m-d H:i:s') . "] Sync completed\n";
        
        // Calculate how long to sleep (5 minutes - execution time)
        $executionTime = time() - $startTime;
        $sleepTime = max(1, 300 - $executionTime); // 300 seconds = 5 minutes
        
        echo "Sleeping for $sleepTime seconds...\n\n";
        sleep($sleepTime);
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        Log::error('Vici sync worker error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Wait 5 minutes before retrying
        sleep(300);
    }
}


