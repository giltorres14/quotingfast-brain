<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadFlowController extends Controller
{
    /**
     * Display the lead flow visualization panel
     */
    public function index(Request $request)
    {
        // Date range filter
        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get flow statistics for each stage
        $flowData = $this->getFlowData($startDate, $endDate);
        
        // Get hourly intake data for chart
        $hourlyIntake = $this->getHourlyIntake($startDate, $endDate);
        
        // Get conversion funnel data
        $funnelData = $this->getConversionFunnel($startDate, $endDate);
        
        // Get list-specific breakdowns
        $listBreakdown = $this->getListBreakdown($startDate, $endDate);
        
        // Get agent performance
        $agentPerformance = $this->getAgentPerformance($startDate, $endDate);
        
        return view('admin.lead-flow', compact(
            'flowData',
            'hourlyIntake',
            'funnelData',
            'listBreakdown',
            'agentPerformance',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get lead flow data for each stage
     */
    private function getFlowData($startDate, $endDate)
    {
        $data = [];
        
        // Stage 1: New Leads (List 101 - Fresh)
        $data['new_leads'] = [
            'label' => 'New Leads (List 101)',
            'count' => Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                          ->where('status', 'new')
                          ->count(),
            'description' => 'Fresh leads from LQF, Suraj, etc.',
            'list_id' => 101,
            'color' => 'blue'
        ];
        
        // Stage 2: In Dialer Queue
        $data['in_queue'] = [
            'label' => 'In Dialer Queue',
            'count' => Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                          ->whereIn('status', ['queued', 'ready'])
                          ->count(),
            'description' => 'Waiting for agent contact',
            'list_id' => 101,
            'color' => 'yellow'
        ];
        
        // Stage 3: Contact Attempted
        $data['attempted'] = [
            'label' => 'Contact Attempted',
            'count' => ViciCallMetrics::whereHas('lead', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
                          })
                          ->where('total_calls', '>', 0)
                          ->count(),
            'description' => 'At least 1 call attempt made',
            'color' => 'orange'
        ];
        
        // Stage 4: Connected with Agent
        $data['connected'] = [
            'label' => 'Connected',
            'count' => ViciCallMetrics::whereHas('lead', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
                          })
                          ->where('connected', true)
                          ->count(),
            'description' => 'Successfully reached customer',
            'color' => 'green'
        ];
        
        // Stage 5: Qualified
        $data['qualified'] = [
            'label' => 'Qualified',
            'count' => Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                          ->where('status', 'qualified')
                          ->count(),
            'description' => 'Passed Top 13 Questions',
            'color' => 'purple'
        ];
        
        // Stage 6: Transferred to Buyer
        $data['transferred'] = [
            'label' => 'Transferred',
            'count' => Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                          ->whereIn('status', ['transferred', 'sold'])
                          ->count(),
            'description' => 'Sent to Allstate or other buyers',
            'color' => 'success'
        ];
        
        // Stage 7: No Answer/Voicemail
        $data['no_answer'] = [
            'label' => 'No Answer',
            'count' => ViciCallMetrics::whereHas('lead', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
                          })
                          ->whereIn('status', ['NA', 'VM', 'B'])
                          ->count(),
            'description' => 'No answer, voicemail, or busy',
            'color' => 'gray'
        ];
        
        // Stage 8: DNC/Bad Number
        $data['dnc'] = [
            'label' => 'DNC/Bad',
            'count' => Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                          ->whereIn('status', ['dnc', 'bad_number', 'disconnected'])
                          ->count(),
            'description' => 'Do not call or invalid number',
            'color' => 'danger'
        ];
        
        // Calculate conversion rates
        $totalLeads = $data['new_leads']['count'];
        if ($totalLeads > 0) {
            foreach ($data as $key => &$stage) {
                $stage['percentage'] = round(($stage['count'] / $totalLeads) * 100, 1);
            }
        }
        
        return $data;
    }
    
    /**
     * Get hourly intake data for chart
     */
    private function getHourlyIntake($startDate, $endDate)
    {
        return Lead::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                   ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                   ->groupBy('hour')
                   ->orderBy('hour')
                   ->get()
                   ->map(function($item) {
                       return [
                           'hour' => sprintf('%02d:00', $item->hour),
                           'count' => $item->count
                       ];
                   });
    }
    
    /**
     * Get conversion funnel data
     */
    private function getConversionFunnel($startDate, $endDate)
    {
        $totalLeads = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count();
        
        $funnel = [
            [
                'stage' => 'Total Leads',
                'count' => $totalLeads,
                'percentage' => 100
            ]
        ];
        
        // Attempted contact
        $attempted = ViciCallMetrics::whereHas('lead', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        })->where('total_calls', '>', 0)->count();
        
        $funnel[] = [
            'stage' => 'Contact Attempted',
            'count' => $attempted,
            'percentage' => $totalLeads > 0 ? round(($attempted / $totalLeads) * 100, 1) : 0
        ];
        
        // Connected
        $connected = ViciCallMetrics::whereHas('lead', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        })->where('connected', true)->count();
        
        $funnel[] = [
            'stage' => 'Connected',
            'count' => $connected,
            'percentage' => $totalLeads > 0 ? round(($connected / $totalLeads) * 100, 1) : 0
        ];
        
        // Qualified
        $qualified = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->where('status', 'qualified')
                        ->count();
        
        $funnel[] = [
            'stage' => 'Qualified',
            'count' => $qualified,
            'percentage' => $totalLeads > 0 ? round(($qualified / $totalLeads) * 100, 1) : 0
        ];
        
        // Sold
        $sold = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                    ->whereIn('status', ['transferred', 'sold'])
                    ->count();
        
        $funnel[] = [
            'stage' => 'Sold',
            'count' => $sold,
            'percentage' => $totalLeads > 0 ? round(($sold / $totalLeads) * 100, 1) : 0
        ];
        
        return $funnel;
    }
    
    /**
     * Get breakdown by Vici lists/campaigns
     */
    private function getListBreakdown($startDate, $endDate)
    {
        // Since we're using List 101 primarily, let's break down by source/campaign
        $breakdown = [];
        
        // By Source
        $sources = Lead::selectRaw('source, COUNT(*) as total, 
                                   SUM(CASE WHEN status = "qualified" THEN 1 ELSE 0 END) as qualified,
                                   SUM(CASE WHEN status IN ("transferred", "sold") THEN 1 ELSE 0 END) as sold')
                      ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                      ->groupBy('source')
                      ->get();
        
        foreach ($sources as $source) {
            $breakdown[] = [
                'name' => $source->source ?: 'Unknown',
                'type' => 'Source',
                'total' => $source->total,
                'qualified' => $source->qualified,
                'sold' => $source->sold,
                'qualification_rate' => $source->total > 0 ? round(($source->qualified / $source->total) * 100, 1) : 0,
                'conversion_rate' => $source->total > 0 ? round(($source->sold / $source->total) * 100, 1) : 0
            ];
        }
        
        // By Campaign
        $campaigns = Lead::selectRaw('campaign_id, COUNT(*) as total,
                                     SUM(CASE WHEN status = "qualified" THEN 1 ELSE 0 END) as qualified,
                                     SUM(CASE WHEN status IN ("transferred", "sold") THEN 1 ELSE 0 END) as sold')
                        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->whereNotNull('campaign_id')
                        ->groupBy('campaign_id')
                        ->get();
        
        foreach ($campaigns as $campaign) {
            $breakdown[] = [
                'name' => $campaign->campaign_id,
                'type' => 'Campaign',
                'total' => $campaign->total,
                'qualified' => $campaign->qualified,
                'sold' => $campaign->sold,
                'qualification_rate' => $campaign->total > 0 ? round(($campaign->qualified / $campaign->total) * 100, 1) : 0,
                'conversion_rate' => $campaign->total > 0 ? round(($campaign->sold / $campaign->total) * 100, 1) : 0
            ];
        }
        
        return $breakdown;
    }
    
    /**
     * Get agent performance metrics
     */
    private function getAgentPerformance($startDate, $endDate)
    {
        return ViciCallMetrics::selectRaw('agent_id, 
                                          COUNT(DISTINCT lead_id) as leads_handled,
                                          SUM(total_calls) as total_calls,
                                          AVG(talk_time) as avg_talk_time,
                                          SUM(CASE WHEN connected = 1 THEN 1 ELSE 0 END) as connected,
                                          SUM(CASE WHEN transfer_requested = 1 THEN 1 ELSE 0 END) as transfers')
                             ->whereHas('lead', function($q) use ($startDate, $endDate) {
                                 $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
                             })
                             ->whereNotNull('agent_id')
                             ->groupBy('agent_id')
                             ->orderBy('leads_handled', 'desc')
                             ->limit(10)
                             ->get()
                             ->map(function($agent) {
                                 $agent->connection_rate = $agent->total_calls > 0 
                                     ? round(($agent->connected / $agent->total_calls) * 100, 1) 
                                     : 0;
                                 $agent->transfer_rate = $agent->connected > 0 
                                     ? round(($agent->transfers / $agent->connected) * 100, 1) 
                                     : 0;
                                 $agent->avg_talk_time = round($agent->avg_talk_time / 60, 1); // Convert to minutes
                                 return $agent;
                             });
    }
}
