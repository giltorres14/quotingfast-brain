<?php

namespace App\Services;

use App\Models\Client;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use App\Mail\OnboardingEmail;
use App\Models\FailedNotification;

class NotificationService
{
    protected $twilio;
    protected $fromNumber;
    protected $failedNotificationModel;

    public function __construct(\Twilio\Rest\Client $twilioClient = null)
    {
        $this->fromNumber = config('services.twilio.from') ?: env('TWILIO_FROM');
        if ($twilioClient) {
            $this->twilio = $twilioClient;
        } else {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            if ($sid && $token) {
                $this->twilio = new TwilioClient($sid, $token);
            }
        }
    }

    public function sendOnboardingEmail(Client $client)
    {
        $template = EmailTemplate::where('type', 'onboarding')->first();
        if (! $template) {
            Log::warning('Onboarding template not found');
            return;
        }
        try {
            Mail::to($client->email)->send(new OnboardingEmail($template, $client));
            $client->update(['last_notified_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Failed to send onboarding email: ' . $e->getMessage());
            // Record failed notification
            FailedNotification::create([
                'client_id' => $client->id,
                'type' => 'email',
                'error_message' => $e->getMessage(),
                'payload' => json_encode(['template_id' => $template->id]),
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);
        }
    }

    public function sendReminderSMS(Client $client, string $mediaUrl = null)
    {
        $template = EmailTemplate::where('type', 'reminder')->first();
        if (! $template) {
            Log::warning('Reminder template not found');
        }
        $body = str_replace('{name}', $client->name, $template->body ?? '');
        $payload = [
            'from' => $this->fromNumber,
            'body' => $body,
        ];
        if ($mediaUrl) {
            $payload['mediaUrl'] = [$mediaUrl];
        }
        try {
            if ($this->twilio) {
                $this->twilio->messages()->create($client->phone, $payload);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send reminder SMS: ' . $e->getMessage());
            // Record failed notification
            FailedNotification::create([
                'client_id' => $client->id,
                'type' => 'sms',
                'error_message' => $e->getMessage(),
                'payload' => json_encode($payload),
                'attempts' => 1,
                'last_attempt_at' => now(),
            ]);
        }
        // Update notification timestamp regardless
        $client->update(['last_notified_at' => now()]);
    }
} 