<?php

namespace App\Jobs;

use App\Models\FailedNotification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationRetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public FailedNotification $failed;

    public function __construct(FailedNotification $failed)
    {
        $this->failed = $failed;
    }

    public function handle(NotificationService $notifications)
    {
        // Retry single failed notification
        $failed = $this->failed;
        
        try {
            if ($failed->type === 'email') {
                $notifications->sendOnboardingEmail($failed->client);
            } elseif ($failed->type === 'sms') {
                $payload = json_decode($failed->payload, true);
                $mediaUrl = $payload['mediaUrl'][0] ?? null;
                $notifications->sendReminderSMS($failed->client, $mediaUrl);
            }
            $failed->delete();
        } catch (\Exception $e) {
            $failed->attempts++;
            $failed->last_attempt_at = now();
            $failed->error_message = $e->getMessage();
            $failed->save();
        }
    }
}
