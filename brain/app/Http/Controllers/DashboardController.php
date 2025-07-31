<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\DialerLog;
use App\Models\SmsMessage;
use App\Models\EnrichmentLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'admin') {
            // Admin sees all metrics
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
                
                // User metrics
                'total_users'     => User::count(),
                'active_users'    => User::where('is_active', true)->count(),
            ];
        } else {
            // Client sees only their assigned leads
            $userLeads = $user ? Lead::where('assigned_user_id', $user->id) : Lead::where('id', 0);
            
            $metrics = [
                'vici_total'      => $userLeads->count(),
                'vici_new'        => $userLeads->whereDate('created_at', today())->count(),
                'vici_contacted'  => $userLeads->whereNotNull('contacted_at')->count(),
                'vici_converted'  => $userLeads->whereNotNull('converted_at')->count(),
                
                // Limited SMS metrics for client
                'sms_sent'        => 0, // Clients don't see SMS metrics
                'sms_delivered'   => 0,
                'ringba_sent'     => 0,
                'ringba_converted'=> 0,
                'total_users'     => 1,
                'active_users'    => 1,
            ];
        }

        return view('dashboard', compact('metrics', 'user'));
    }

    public function api()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        if ($user->role === 'admin') {
            $leads = Lead::with('assignedUser')->get();
        } else {
            $leads = Lead::where('assigned_user_id', $user->id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'leads' => $leads,
                'user' => $user,
                'stats' => [
                    'total_leads' => $leads->count(),
                    'new_leads' => $leads->where('status', 'new')->count(),
                    'qualified_leads' => $leads->where('status', 'qualified')->count(),
                    'won_leads' => $leads->where('status', 'won')->count(),
                ]
            ]
        ]);
    }
} 