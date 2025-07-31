<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $metrics;

    public function __construct(array $metrics)
    {
        $this->metrics = $metrics;
    }

    public function build()
    {
        return $this->subject('Weekly Report')
                    ->markdown('emails.weekly_report', ['metrics' => $this->metrics]);
    }
}
