#!/usr/bin/env php
<?php
/**
 * COMPREHENSIVE VICI SCRIPT AUDIT
 * Finds ALL scripts, crons, and processes touching ViciDial
 */

echo "\n" . str_repeat("=", 80) . "\n";
echo "VICIDIAL AUTOMATION AUDIT - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n\n";

// Colors for output
$RED = "\033[0;31m";
$GREEN = "\033[0;32m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

// ===========================
// 1. CHECK CRONTAB ENTRIES
// ===========================
echo "{$BLUE}[1] CHECKING CRONTAB ENTRIES:{$NC}\n";
echo str_repeat("-", 40) . "\n";

// Check user crontab
$userCron = shell_exec('crontab -l 2>/dev/null');
if ($userCron) {
    echo "{$YELLOW}User Crontab:{$NC}\n";
    $viciRelated = [];
    foreach (explode("\n", $userCron) as $line) {
        if (stripos($line, 'vici') !== false || 
            stripos($line, 'lead') !== false || 
            stripos($line, 'call') !== false ||
            stripos($line, 'Q6hdjl67GRigMofv') !== false) {
            $viciRelated[] = $line;
        }
    }
    if ($viciRelated) {
        foreach ($viciRelated as $cron) {
            echo "  • $cron\n";
        }
    } else {
        echo "  No Vici-related crons in user crontab\n";
    }
}

// Check system crontabs
echo "\n{$YELLOW}System Crontabs:{$NC}\n";
$systemCrons = [
    '/etc/crontab',
    '/etc/cron.d/*',
    '/var/spool/cron/*'
];

foreach ($systemCrons as $cronPath) {
    if (file_exists($cronPath) || glob($cronPath)) {
        $files = is_dir($cronPath) ? glob($cronPath) : [$cronPath];
        foreach ($files as $file) {
            if (is_file($file)) {
                $content = file_get_contents($file);
                if (stripos($content, 'vici') !== false || stripos($content, 'lead') !== false) {
                    echo "  Found in: $file\n";
                }
            }
        }
    }
}

// ===========================
// 2. CHECK RUNNING PROCESSES
// ===========================
echo "\n{$BLUE}[2] CURRENTLY RUNNING PROCESSES:{$NC}\n";
echo str_repeat("-", 40) . "\n";

// Check for PHP scripts
$processes = shell_exec("ps aux | grep -E '(vici|lead|call|sync)' | grep -v grep");
if ($processes) {
    $lines = explode("\n", trim($processes));
    foreach ($lines as $process) {
        if (!empty($process)) {
            // Extract just the command part
            preg_match('/\d+:\d+\s+(.+)$/', $process, $matches);
            $command = isset($matches[1]) ? $matches[1] : $process;
            echo "  • " . substr($command, 0, 100) . "\n";
        }
    }
} else {
    echo "  No Vici-related processes currently running\n";
}

// ===========================
// 3. LARAVEL SCHEDULED COMMANDS
// ===========================
echo "\n{$BLUE}[3] LARAVEL SCHEDULED COMMANDS:{$NC}\n";
echo str_repeat("-", 40) . "\n";

// Check Kernel.php
$kernelPath = __DIR__ . '/app/Console/Kernel.php';
if (file_exists($kernelPath)) {
    $kernelContent = file_get_contents($kernelPath);
    preg_match_all('/\$schedule->command\([\'"]([^\'"]+)[\'"]\)([^;]+);/s', $kernelContent, $matches);
    
    if ($matches[1]) {
        foreach ($matches[1] as $index => $command) {
            $schedule = $matches[2][$index];
            
            // Extract frequency
            preg_match('/->(everyMinute|everyFiveMinutes|hourly|daily|weekly)\(\)/', $schedule, $freq);
            $frequency = isset($freq[1]) ? $freq[1] : 'custom';
            
            // Check if Vici-related
            if (stripos($command, 'vici') !== false || 
                stripos($command, 'sync') !== false || 
                stripos($command, 'lead') !== false) {
                echo "  {$GREEN}✓{$NC} $command ({$frequency})\n";
            }
        }
    }
}

// ===========================
// 4. PHP SCRIPTS IN PROJECT
// ===========================
echo "\n{$BLUE}[4] PHP SCRIPTS IN PROJECT:{$NC}\n";
echo str_repeat("-", 40) . "\n";

$scriptDirs = [
    __DIR__ . '/',
    __DIR__ . '/scripts/',
    __DIR__ . '/cron/',
    __DIR__ . '/vici/'
];

$foundScripts = [];
foreach ($scriptDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (stripos($content, 'vicidial_list') !== false || 
                stripos($content, 'Q6hdjl67GRigMofv') !== false ||
                stripos($content, '162.243.139.69') !== false) {
                $foundScripts[] = basename($file);
            }
        }
    }
}

if ($foundScripts) {
    foreach (array_unique($foundScripts) as $script) {
        echo "  • $script\n";
    }
}

// ===========================
// 5. CHECK VICI DATABASE
// ===========================
echo "\n{$BLUE}[5] VICI DATABASE SCHEDULED EVENTS:{$NC}\n";
echo str_repeat("-", 40) . "\n";

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $viciDb = new PDO(
        "mysql:host=162.243.139.69;dbname=Q6hdjl67GRigMofv",
        $_ENV['VICI_DB_USER'] ?? 'cron',
        $_ENV['VICI_DB_PASS'] ?? 'hfIvWpOS4wRu2ZjYaLhbZ4lh4PNd7Y'
    );
    
    // Check for MySQL events
    $events = $viciDb->query("SHOW EVENTS")->fetchAll(PDO::FETCH_ASSOC);
    if ($events) {
        foreach ($events as $event) {
            echo "  • Event: {$event['Name']} - Status: {$event['Status']}\n";
        }
    } else {
        echo "  No scheduled events in database\n";
    }
    
    // Check for stored procedures that might be called
    $procedures = $viciDb->query("SHOW PROCEDURE STATUS WHERE Db='Q6hdjl67GRigMofv'")->fetchAll(PDO::FETCH_ASSOC);
    if ($procedures) {
        foreach ($procedures as $proc) {
            echo "  • Procedure: {$proc['Name']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "  {$RED}Could not connect to Vici database{$NC}\n";
}

// ===========================
// 6. CHECK SYSTEMD SERVICES
// ===========================
echo "\n{$BLUE}[6] SYSTEMD SERVICES & TIMERS:{$NC}\n";
echo str_repeat("-", 40) . "\n";

$services = shell_exec("systemctl list-units --type=service --all | grep -i vici");
$timers = shell_exec("systemctl list-timers --all | grep -i vici");

if ($services) {
    echo "{$YELLOW}Services:{$NC}\n";
    echo $services;
}

if ($timers) {
    echo "{$YELLOW}Timers:{$NC}\n";
    echo $timers;
}

if (!$services && !$timers) {
    echo "  No Vici-related systemd services or timers\n";
}

// ===========================
// 7. CHECK LOG FILES
// ===========================
echo "\n{$BLUE}[7] RECENT LOG ACTIVITY:{$NC}\n";
echo str_repeat("-", 40) . "\n";

$logDirs = [
    __DIR__ . '/storage/logs/',
    '/var/log/',
    __DIR__ . '/logs/'
];

foreach ($logDirs as $logDir) {
    if (is_dir($logDir)) {
        // Find logs modified in last 24 hours
        $recentLogs = shell_exec("find $logDir -name '*vici*' -type f -mtime -1 2>/dev/null");
        if ($recentLogs) {
            $files = explode("\n", trim($recentLogs));
            foreach ($files as $file) {
                if ($file) {
                    $size = filesize($file);
                    $modified = date('Y-m-d H:i', filemtime($file));
                    echo "  • " . basename($file) . " (Modified: $modified, Size: " . number_format($size) . " bytes)\n";
                }
            }
        }
    }
}

// ===========================
// 8. POTENTIAL CONFLICTS
// ===========================
echo "\n{$BLUE}[8] POTENTIAL CONFLICTS & ISSUES:{$NC}\n";
echo str_repeat("-", 40) . "\n";

$issues = [];

// Check for duplicate cron entries
if ($userCron) {
    $cronLines = explode("\n", $userCron);
    $cronCommands = [];
    foreach ($cronLines as $line) {
        if (!empty($line) && $line[0] !== '#') {
            preg_match('/[\s]+(.+)$/', $line, $matches);
            if (isset($matches[1])) {
                $cmd = $matches[1];
                if (isset($cronCommands[$cmd])) {
                    $issues[] = "Duplicate cron: $cmd";
                }
                $cronCommands[$cmd] = true;
            }
        }
    }
}

// Check for old scripts that might conflict
$oldScripts = [
    'sync_vici_logs.php',
    'import_vici_logs.php',
    'vici_lead_flow.php',
    'lead_movement.php'
];

foreach ($oldScripts as $oldScript) {
    if (file_exists(__DIR__ . '/' . $oldScript)) {
        $modified = date('Y-m-d', filemtime(__DIR__ . '/' . $oldScript));
        $issues[] = "Old script exists: $oldScript (modified: $modified)";
    }
}

if ($issues) {
    foreach ($issues as $issue) {
        echo "  {$RED}⚠{$NC} $issue\n";
    }
} else {
    echo "  {$GREEN}✓{$NC} No conflicts detected\n";
}

// ===========================
// 9. RECOMMENDATIONS
// ===========================
echo "\n{$BLUE}[9] RECOMMENDATIONS:{$NC}\n";
echo str_repeat("-", 40) . "\n";

echo "  1. {$YELLOW}Consolidate scripts:{$NC} Move all Vici automation to Laravel commands\n";
echo "  2. {$YELLOW}Central logging:{$NC} All scripts should log to storage/logs/vici/\n";
echo "  3. {$YELLOW}Single cron entry:{$NC} Use Laravel scheduler with * * * * * schedule:run\n";
echo "  4. {$YELLOW}Remove old scripts:{$NC} Delete unused PHP files to avoid confusion\n";
echo "  5. {$YELLOW}Document everything:{$NC} Update VICI_SQL_AUTOMATION_MASTER.md\n";

// ===========================
// 10. SUMMARY
// ===========================
echo "\n" . str_repeat("=", 80) . "\n";
echo "AUDIT COMPLETE\n";
echo str_repeat("=", 80) . "\n";

echo "\n{$GREEN}Next Steps:{$NC}\n";
echo "1. Review all found scripts and crons\n";
echo "2. Disable any duplicates or old scripts\n";
echo "3. Ensure only necessary automation is running\n";
echo "4. Check logs for any errors\n";
echo "5. Run this audit weekly\n\n";









