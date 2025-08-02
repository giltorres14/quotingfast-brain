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

        // Add webhook stats for admin users
        $webhookStats = [];
        if ($user->role === 'admin') {
            $webhookStats = [
                'total_webhooks' => Lead::count(),
                'lqf_leads' => Lead::where('source', 'leadsquotingfast')->count(),
                'ringba_leads' => Lead::where('source', 'ringba')->count(),
                'vici_leads' => Lead::where('source', 'vici')->count(),
                'twilio_leads' => Lead::where('source', 'twilio')->count(),
                'today_leads' => Lead::whereDate('created_at', today())->count(),
                'recent_activity' => Lead::latest()->take(10)->get(['id', 'name', 'source', 'created_at']),
            ];
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
                ],
                'webhook_stats' => $webhookStats
            ]
        ]);
    }

    // Webhook monitoring endpoint
    public function webhooks()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized - Admin access required'], 401);
        }

        $webhookStats = [
            'sources' => [
                'leadsquotingfast' => [
                    'name' => 'LeadsQuotingFast',
                    'endpoint' => '/webhook.php',
                    'total_leads' => Lead::where('source', 'leadsquotingfast')->count(),
                    'today_leads' => Lead::where('source', 'leadsquotingfast')->whereDate('created_at', today())->count(),
                    'last_received' => Lead::where('source', 'leadsquotingfast')->latest()->first()?->created_at,
                    'active' => true,
                    'description' => 'Auto insurance lead capture'
                ],
                'ringba' => [
                    'name' => 'Ringba',
                    'endpoint' => '/webhook/ringba',
                    'total_leads' => Lead::where('source', 'ringba')->count(),
                    'today_leads' => Lead::where('source', 'ringba')->whereDate('created_at', today())->count(),
                    'last_received' => Lead::where('source', 'ringba')->latest()->first()?->created_at,
                    'active' => true,
                    'description' => 'Call tracking and routing'
                ],
                'vici' => [
                    'name' => 'Vici',
                    'endpoint' => '/webhook/vici',
                    'total_leads' => Lead::where('source', 'vici')->count(),
                    'today_leads' => Lead::where('source', 'vici')->whereDate('created_at', today())->count(),
                    'last_received' => Lead::where('source', 'vici')->latest()->first()?->created_at,
                    'active' => true,
                    'description' => 'Dialer system integration'
                ],
                'twilio' => [
                    'name' => 'Twilio',
                    'endpoint' => '/webhook/twilio',
                    'total_leads' => Lead::where('source', 'twilio')->count(),
                    'today_leads' => Lead::where('source', 'twilio')->whereDate('created_at', today())->count(),
                    'last_received' => Lead::where('source', 'twilio')->latest()->first()?->created_at,
                    'active' => true,
                    'description' => 'SMS/Voice webhook integration'
                ],
                'allstate' => [
                    'name' => 'Allstate',
                    'endpoint' => '/webhook/allstate',
                    'total_leads' => Lead::where('source', 'allstate_ready')->count(),
                    'today_leads' => Lead::where('source', 'allstate_ready')->whereDate('created_at', today())->count(),
                    'last_received' => Lead::where('source', 'allstate_ready')->latest()->first()?->created_at,
                    'transferred_leads' => Lead::where('source', 'allstate_ready')->where('status', 'transferred_to_allstate')->count(),
                    'failed_transfers' => Lead::where('source', 'allstate_ready')->where('status', 'transfer_failed')->count(),
                    'transfer_success_rate' => $this->calculateTransferSuccessRate(),
                    'active' => true,
                    'auto_transfer' => true,
                    'description' => 'Auto-transfer leads to Allstate Lead Marketplace'
                ]
            ],
            'recent_activity' => Lead::with(['assignedUser'])
                ->latest()
                ->take(20)
                ->get(['id', 'name', 'phone', 'source', 'type', 'created_at', 'assigned_user_id']),
            'summary' => [
                'total_leads' => Lead::count(),
                'today_leads' => Lead::whereDate('created_at', today())->count(),
                'active_sources' => Lead::distinct('source')->count('source'),
                'last_activity' => Lead::latest()->first()?->created_at
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $webhookStats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Calculate Allstate transfer success rate
     */
    private function calculateTransferSuccessRate(): float
    {
        $totalAllstateLeads = Lead::where('source', 'allstate_ready')->count();
        
        if ($totalAllstateLeads === 0) {
            return 0.0;
        }
        
        $successfulTransfers = Lead::where('source', 'allstate_ready')
            ->where('status', 'transferred_to_allstate')
            ->count();
        
        return round(($successfulTransfers / $totalAllstateLeads) * 100, 2);
    }
} 