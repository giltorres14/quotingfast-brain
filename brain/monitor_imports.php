<?php

// Import monitoring script
// Tracks progress of Suraj and LQF imports

$dbConfig = [
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'port' => 5432,
    'dbname' => 'brain_production',
    'user' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
];

try {
    $pdo = new PDO(
        "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $logFile = 'import_monitor.log';
    
    echo "Starting import monitor... (Press Ctrl+C to stop)\n";
    echo "Logging to: $logFile\n\n";
    
    $lastCounts = [];
    $startTime = time();
    
    while (true) {
        // Get current counts
        $stats = $pdo->query("
            SELECT 
                source, 
                COUNT(*) as count,
                MAX(created_at) as last_import
            FROM leads 
            GROUP BY source 
            ORDER BY source
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $output = "\n" . str_repeat("=", 60) . "\n";
        $output .= date('Y-m-d H:i:s') . " EST\n";
        $output .= "Runtime: " . gmdate("H:i:s", time() - $startTime) . "\n";
        $output .= str_repeat("-", 60) . "\n";
        
        $totalLeads = 0;
        $hasChanges = false;
        
        foreach ($stats as $stat) {
            $source = $stat['source'];
            $count = $stat['count'];
            $totalLeads += $count;
            
            // Calculate rate if count changed
            $rate = '';
            if (isset($lastCounts[$source])) {
                $diff = $count - $lastCounts[$source];
                if ($diff > 0) {
                    $rate = " (+$diff in last 30s = " . round($diff * 2, 1) . "/min)";
                    $hasChanges = true;
                }
            }
            
            $output .= sprintf(
                "%-20s: %8s leads%s\n",
                $source,
                number_format($count),
                $rate
            );
            
            $lastCounts[$source] = $count;
        }
        
        $output .= str_repeat("-", 60) . "\n";
        $output .= sprintf("TOTAL LEADS: %s\n", number_format($totalLeads));
        
        // Estimate completion for Suraj
        if (isset($lastCounts['SURAJ_BULK'])) {
            $surajCount = $lastCounts['SURAJ_BULK'];
            $targetCount = 279000;
            $remaining = $targetCount - $surajCount;
            $percentComplete = round(($surajCount / $targetCount) * 100, 2);
            
            $output .= "\nSURAJ IMPORT PROGRESS:\n";
            $output .= "  Completed: $percentComplete%\n";
            $output .= "  Remaining: " . number_format($remaining) . " leads\n";
            
            // Estimate time remaining based on recent rate
            if (isset($lastCounts['SURAJ_BULK']) && $diff > 0) {
                $leadsPerHour = $diff * 120; // 30 seconds * 120 = 1 hour
                if ($leadsPerHour > 0) {
                    $hoursRemaining = $remaining / $leadsPerHour;
                    $output .= "  Est. Time Remaining: " . round($hoursRemaining, 1) . " hours\n";
                }
            }
        }
        
        // Display output
        echo $output;
        
        // Log to file if there are changes
        if ($hasChanges) {
            file_put_contents($logFile, $output, FILE_APPEND);
        }
        
        // Check for any recent errors in Laravel log
        $laravelLog = '../storage/logs/laravel.log';
        if (file_exists($laravelLog)) {
            $recentErrors = `tail -n 100 $laravelLog | grep -c "ERROR"`;
            if (trim($recentErrors) > 0) {
                echo "\n⚠️  Recent errors detected in Laravel log: $recentErrors\n";
            }
        }
        
        // Wait 30 seconds before next check
        sleep(30);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

