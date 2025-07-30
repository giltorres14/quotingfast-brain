<?php

namespace App\Http\Controllers;

use App\Models\FailedNotification;
use App\Jobs\NotificationRetryJob;
use Illuminate\Http\Request;
 use Symfony\Component\HttpFoundation\StreamedResponse;

class ErrorDashboardController extends Controller
{
    public function index()
    {
        $errors = FailedNotification::with('client')->paginate(20);
        return view('notifications.errors', compact('errors'));
    }

    public function retry($id)
    {
        $error = FailedNotification::findOrFail($id);
        NotificationRetryJob::dispatch($error);
        return back();
    }

    public function resolve($id)
    {
        $error = FailedNotification::findOrFail($id);
        $error->resolved_at = now();
        $error->save();
        return back();
    }

    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="failed_notifications.csv"',
        ];

        $records = FailedNotification::with('client')->get();
        $lines = [];
        $lines[] = 'ID,Client,Type,Error Message,Attempts,Last Attempt,Resolved At';
        foreach ($records as $record) {
            $lines[] = implode(',', [
                $record->id,
                $record->client?->name,
                $record->type,
                str_replace(',', ';', $record->error_message),
                $record->attempts,
                $record->last_attempt_at?->toDateTimeString(),
                $record->resolved_at?->toDateTimeString(),
            ]);
        }
        $csv = implode("\n", $lines);
        return response($csv, 200, $headers);
    }
}
