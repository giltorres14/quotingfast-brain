<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run Vici export script every 5 minutes (once IPs are whitelisted)
        $schedule->command('vici:run-export')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/vici_export.log'));
            
        // Incremental Vici call log sync every 5 minutes with overlap protection
        $schedule->command('vici:sync-incremental')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/vici_sync.log'));
            
        // Reprocess orphan calls every 10 minutes
        $schedule->command('vici:match-orphans')
            ->everyTenMinutes()
            ->withoutOverlapping();
            
        // Archive old Vici leads daily at 2 AM
        $schedule->command('vici:archive-old-leads')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/vici_archive.log'));
            
        // Execute Vici lead flow movements every 5 minutes
        $schedule->command('vici:execute-lead-flow')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/vici_lead_flow.log'));
            
        // Test A Lead Flow with corrected disposition logic
        $schedule->command('vici:test-a-flow')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/vici_test_a_flow.log'));
            
        // System health check every minute
        $schedule->command('system:health-check')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/health_check.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}