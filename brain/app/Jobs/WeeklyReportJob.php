<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyReportEmail;
use Illuminate\Support\Facades\DB;
use App\Models\FailedNotification;

class WeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Compile metrics
        $leadsPerDay = DB::table('leads')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();

        $sourceBreakdown = DB::table('leads')
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        $errorCount = FailedNotification::count();

        $metrics = [
            'leadsPerDay' => $leadsPerDay,
            'sourceBreakdown' => $sourceBreakdown,
            'errorCount' => $errorCount,
        ];

        // Send report
        $email = new WeeklyReportEmail($metrics);
        Mail::to(config('mail.admin_address', env('ADMIN_EMAIL')))
            ->send($email);
    }
}
