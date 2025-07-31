<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(NotificationService $notifications)
    {
        $notifications->sendReminderSMS($this->client);
    }
}
