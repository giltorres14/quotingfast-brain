#!/usr/bin/env php
<?php
/**
 * Start Vici Call Log Sync System
 * This script initializes the automatic 5-minute sync
 */

echo "\n=== VICI CALL LOG SYNC INITIALIZATION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

echo "📊 Checking current sync status...\n";

// Check last sync time
$lastSync = Cache::get('vici_last_incremental_sync');
if ($lastSync) {
    echo "✅ Last sync: " . Carbon::parse($lastSync)->format('Y-m-d H:i:s') . "\n";
    echo "   (" . Carbon::parse($lastSync)->diffForHumans() . ")\n";
} else {
    echo "⚠️  No previous sync found - this will be the first sync\n";
}

// Check for existing call logs
$callCount = \App\Models\ViciCallMetrics::count();
echo "\n📞 Current call logs in database: " . number_format($callCount) . "\n";

// Ask for confirmation
echo "\n🚀 Ready to start automatic Vici sync?\n";
echo "This will:\n";
echo "  1. Run an initial sync to catch up on recent calls\n";
echo "  2. Set up automatic syncing every 5 minutes\n";
echo "  3. Enable orphan call matching every 10 minutes\n";
echo "\nProceed? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim(strtolower($line));

if ($answer !== 'yes' && $answer !== 'y') {
    echo "\n❌ Sync initialization cancelled.\n\n";
    exit(0);
}

echo "\n🔄 Starting initial sync...\n";

// Run initial sync with 24-hour lookback for first run
$lookbackMinutes = $lastSync ? 10 : 1440; // 24 hours if first run, 10 minutes otherwise
echo "Looking back $lookbackMinutes minutes for call logs...\n\n";

// Execute the sync command
$output = shell_exec("php artisan vici:sync-incremental --minutes=$lookbackMinutes 2>&1");
echo $output;

// Update the last sync time
Cache::put('vici_last_incremental_sync', Carbon::now(), now()->addDays(7));

echo "\n✅ Initial sync complete!\n";
echo "\n📅 Automatic sync schedule:\n";
echo "  • Call logs: Every 5 minutes\n";
echo "  • Orphan matching: Every 10 minutes\n";

// Check if scheduler is running
echo "\n🔍 Checking scheduler status...\n";
$schedulerLog = storage_path('logs/scheduler.log');
if (file_exists($schedulerLog)) {
    $lastModified = filemtime($schedulerLog);
    if (time() - $lastModified < 120) {
        echo "✅ Scheduler is running (last activity: " . date('H:i:s', $lastModified) . ")\n";
    } else {
        echo "⚠️  Scheduler may not be running (last activity: " . date('Y-m-d H:i:s', $lastModified) . ")\n";
        echo "   Run this command to start it manually:\n";
        echo "   nohup bash -c 'while true; do php artisan schedule:run >> storage/logs/scheduler.log 2>&1; sleep 60; done' &\n";
    }
} else {
    echo "⚠️  Scheduler log not found. The scheduler may not be running.\n";
    echo "   To start the scheduler manually, run:\n";
    echo "   nohup bash -c 'while true; do php artisan schedule:run >> storage/logs/scheduler.log 2>&1; sleep 60; done' &\n";
}

echo "\n📊 To monitor sync activity:\n";
echo "  • View sync log: tail -f storage/logs/vici_sync.log\n";
echo "  • View scheduler log: tail -f storage/logs/scheduler.log\n";
echo "  • Check last sync: php artisan tinker --execute=\"echo Cache::get('vici_last_incremental_sync');\"\n";

echo "\n🎯 Next automatic sync will run in ~5 minutes.\n\n";


<?php
/**
 * Start Vici Call Log Sync System
 * This script initializes the automatic 5-minute sync
 */

echo "\n=== VICI CALL LOG SYNC INITIALIZATION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

echo "📊 Checking current sync status...\n";

// Check last sync time
$lastSync = Cache::get('vici_last_incremental_sync');
if ($lastSync) {
    echo "✅ Last sync: " . Carbon::parse($lastSync)->format('Y-m-d H:i:s') . "\n";
    echo "   (" . Carbon::parse($lastSync)->diffForHumans() . ")\n";
} else {
    echo "⚠️  No previous sync found - this will be the first sync\n";
}

// Check for existing call logs
$callCount = \App\Models\ViciCallMetrics::count();
echo "\n📞 Current call logs in database: " . number_format($callCount) . "\n";

// Ask for confirmation
echo "\n🚀 Ready to start automatic Vici sync?\n";
echo "This will:\n";
echo "  1. Run an initial sync to catch up on recent calls\n";
echo "  2. Set up automatic syncing every 5 minutes\n";
echo "  3. Enable orphan call matching every 10 minutes\n";
echo "\nProceed? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim(strtolower($line));

if ($answer !== 'yes' && $answer !== 'y') {
    echo "\n❌ Sync initialization cancelled.\n\n";
    exit(0);
}

echo "\n🔄 Starting initial sync...\n";

// Run initial sync with 24-hour lookback for first run
$lookbackMinutes = $lastSync ? 10 : 1440; // 24 hours if first run, 10 minutes otherwise
echo "Looking back $lookbackMinutes minutes for call logs...\n\n";

// Execute the sync command
$output = shell_exec("php artisan vici:sync-incremental --minutes=$lookbackMinutes 2>&1");
echo $output;

// Update the last sync time
Cache::put('vici_last_incremental_sync', Carbon::now(), now()->addDays(7));

echo "\n✅ Initial sync complete!\n";
echo "\n📅 Automatic sync schedule:\n";
echo "  • Call logs: Every 5 minutes\n";
echo "  • Orphan matching: Every 10 minutes\n";

// Check if scheduler is running
echo "\n🔍 Checking scheduler status...\n";
$schedulerLog = storage_path('logs/scheduler.log');
if (file_exists($schedulerLog)) {
    $lastModified = filemtime($schedulerLog);
    if (time() - $lastModified < 120) {
        echo "✅ Scheduler is running (last activity: " . date('H:i:s', $lastModified) . ")\n";
    } else {
        echo "⚠️  Scheduler may not be running (last activity: " . date('Y-m-d H:i:s', $lastModified) . ")\n";
        echo "   Run this command to start it manually:\n";
        echo "   nohup bash -c 'while true; do php artisan schedule:run >> storage/logs/scheduler.log 2>&1; sleep 60; done' &\n";
    }
} else {
    echo "⚠️  Scheduler log not found. The scheduler may not be running.\n";
    echo "   To start the scheduler manually, run:\n";
    echo "   nohup bash -c 'while true; do php artisan schedule:run >> storage/logs/scheduler.log 2>&1; sleep 60; done' &\n";
}

echo "\n📊 To monitor sync activity:\n";
echo "  • View sync log: tail -f storage/logs/vici_sync.log\n";
echo "  • View scheduler log: tail -f storage/logs/scheduler.log\n";
echo "  • Check last sync: php artisan tinker --execute=\"echo Cache::get('vici_last_incremental_sync');\"\n";

echo "\n🎯 Next automatic sync will run in ~5 minutes.\n\n";






