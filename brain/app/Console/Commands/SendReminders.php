<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Jobs\SendReminderSMSJob;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send {--days=7 : Number of days since last notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders to clients not notified recently';

    public function handle()
    {
        $days = (int) $this->option('days');
        $threshold = now()->subDays($days);

        $clients = Client::where(function ($query) use ($threshold) {
            $query->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', $threshold);
        })->get();

        foreach ($clients as $client) {
            SendReminderSMSJob::dispatch($client);
            $this->info("Reminder job queued for {$client->email}");
        }

        return 0;
    }
}
