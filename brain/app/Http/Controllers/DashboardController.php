<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\DialerLog;
use App\Models\SmsMessage;
use App\Models\EnrichmentLog;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            // Vici dialer/lead metrics
            'vici_total'      => Lead::count(),
            'vici_new'        => Lead::whereDate('created_at', today())->count(),
            'vici_contacted'  => Lead::whereNotNull('contacted_at')->count(),
            'vici_sent'       => DialerLog::count(),
            'vici_converted'  => Lead::whereNotNull('converted_at')->count(),

            // Twilio SMS metrics
            'sms_sent'        => SmsMessage::count(),
            'sms_delivered'   => SmsMessage::where('status', 'delivered')->count(),

            // Ringba enrichment metrics
            'ringba_sent'     => EnrichmentLog::count(),
            'ringba_converted'=> EnrichmentLog::where('converted', true)->count(),
        ];

        return view('dashboard', compact('metrics'));
    }
} 