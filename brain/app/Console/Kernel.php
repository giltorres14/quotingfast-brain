<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessLeadQueue::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Process lead queue every minute
        $schedule->command('leads:process-queue')
                ->everyMinute()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/lead-queue.log'));
        
        // Optional: Clean up old completed queue items daily
        $schedule->call(function () {
            \App\Models\LeadQueue::where('status', 'completed')
                ->where('processed_at', '<', now()->subDays(7))
                ->delete();
        })->daily();
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



