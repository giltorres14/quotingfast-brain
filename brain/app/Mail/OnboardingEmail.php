<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;
use App\Models\Client;

class OnboardingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $client;

    public function __construct(EmailTemplate $template, Client $client)
    {
        $this->template = $template;
        $this->client = $client;
    }

    public function build()
    {
        return $this->subject($this->template->subject)
                    ->markdown('emails.onboarding', [
                        'body' => str_replace('{name}', $this->client->name, $this->template->body),
                    ]);
    }
}
