<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use App\Models\OrphanCallLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ViciReportsService
{
    /**
     * 1. Lead Journey Timeline Report
     * Shows the complete journey of a lead through the system
     */
    public function getLeadJourneyTimeline($leadId = null, $dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::with('lead');
        
        if ($leadId) {
            $query->where('lead_id', $leadId);
        }
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $metrics = $query->get();
        
        $timeline = [];
        foreach ($metrics as $metric) {
            $dispositions = json_decode($metric->dispositions ?? '[]', true);
            
            $timeline[] = [
                'lead_id' => $metric->lead_id,
                'lead_name' => $metric->lead->name ?? 'Unknown',
                'phone' => $metric->phone_number,
                'first_contact' => $metric->created_at,
                'last_contact' => $metric->last_call_date,
                'total_calls' => $metric->total_calls,
                'total_talk_time' => $metric->talk_time,
                'current_status' => $metric->status,
                'journey' => $dispositions,
                'campaign' => $metric->campaign_id,
                'agent' => $metric->agent_id
            ];
        }
        
        return $timeline;
    }
    
    /**
     * 2. Agent Leaderboard & Scorecard
     * Performance metrics for each agent
     */
    public function getAgentScorecard($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->select('agent_id')
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('SUM(talk_time) as total_talk_time')
            ->selectRaw('AVG(talk_time) as avg_talk_time')
            ->selectRaw('SUM(CASE WHEN connected = true THEN 1 ELSE 0 END) as connected_calls')
            ->selectRaw('COUNT(DISTINCT lead_id) as unique_leads')
            ->groupBy('agent_id');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $agents = $query->get();
        
        $scorecard = [];
        foreach ($agents as $agent) {
            $scorecard[] = [
                'agent' => $agent->agent_id ?? 'Unknown',
                'total_calls' => $agent->total_calls,
                'connected_calls' => $agent->connected_calls,
                'connection_rate' => $agent->total_calls > 0 
                    ? round(($agent->connected_calls / $agent->total_calls) * 100, 2) 
                    : 0,
                'total_talk_time' => $agent->total_talk_time,
                'avg_talk_time' => round($agent->avg_talk_time, 2),
                'unique_leads' => $agent->unique_leads,
                'calls_per_lead' => $agent->unique_leads > 0 
                    ? round($agent->total_calls / $agent->unique_leads, 2) 
                    : 0
            ];
        }
        
        // Sort by total calls descending
        usort($scorecard, function($a, $b) {
            return $b['total_calls'] - $a['total_calls'];
        });
        
        return $scorecard;
    }
    
    /**
     * 3. Campaign ROI Dashboard
     * Shows performance and ROI metrics by campaign
     */
    public function getCampaignROI($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->select('campaign_id')
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('COUNT(DISTINCT lead_id) as total_leads')
            ->selectRaw('SUM(talk_time) as total_talk_time')
            ->selectRaw('SUM(CASE WHEN connected = true THEN 1 ELSE 0 END) as connected_calls')
            ->selectRaw('SUM(CASE WHEN status = "SALE" THEN 1 ELSE 0 END) as sales')
            ->selectRaw('SUM(CASE WHEN status = "XFER" THEN 1 ELSE 0 END) as transfers')
            ->groupBy('campaign_id');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $campaigns = $query->get();
        
        $roi = [];
        foreach ($campaigns as $campaign) {
            $roi[] = [
                'campaign' => $campaign->campaign_id ?? 'Unknown',
                'total_calls' => $campaign->total_calls,
                'total_leads' => $campaign->total_leads,
                'connected_calls' => $campaign->connected_calls,
                'connection_rate' => $campaign->total_calls > 0 
                    ? round(($campaign->connected_calls / $campaign->total_calls) * 100, 2) 
                    : 0,
                'sales' => $campaign->sales,
                'conversion_rate' => $campaign->connected_calls > 0 
                    ? round(($campaign->sales / $campaign->connected_calls) * 100, 2) 
                    : 0,
                'transfers' => $campaign->transfers,
                'total_talk_time' => $campaign->total_talk_time,
                'avg_talk_time' => $campaign->total_calls > 0 
                    ? round($campaign->total_talk_time / $campaign->total_calls, 2) 
                    : 0
            ];
        }
        
        return $roi;
    }
    
    /**
     * 4. Speed to Lead Report
     * Measures how quickly leads are contacted after creation
     */
    public function getSpeedToLead($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->join('leads', 'vici_call_metrics.lead_id', '=', 'leads.id')
            ->select('vici_call_metrics.*', 'leads.created_at as lead_created');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('leads.created_at', [$dateFrom, $dateTo]);
        }
        
        $metrics = $query->get();
        
        $speedStats = [
            'under_5_min' => 0,
            'under_30_min' => 0,
            'under_1_hour' => 0,
            'under_24_hours' => 0,
            'over_24_hours' => 0,
            'never_contacted' => 0,
            'total_leads' => 0,
            'avg_time_to_contact' => 0
        ];
        
        $totalTime = 0;
        $contactedCount = 0;
        
        foreach ($metrics as $metric) {
            $speedStats['total_leads']++;
            
            if ($metric->created_at) {
                $leadTime = Carbon::parse($metric->lead_created);
                $firstContact = Carbon::parse($metric->created_at);
                $minutesToContact = $leadTime->diffInMinutes($firstContact);
                
                if ($minutesToContact < 5) {
                    $speedStats['under_5_min']++;
                } elseif ($minutesToContact < 30) {
                    $speedStats['under_30_min']++;
                } elseif ($minutesToContact < 60) {
                    $speedStats['under_1_hour']++;
                } elseif ($minutesToContact < 1440) {
                    $speedStats['under_24_hours']++;
                } else {
                    $speedStats['over_24_hours']++;
                }
                
                $totalTime += $minutesToContact;
                $contactedCount++;
            } else {
                $speedStats['never_contacted']++;
            }
        }
        
        if ($contactedCount > 0) {
            $speedStats['avg_time_to_contact'] = round($totalTime / $contactedCount, 2);
        }
        
        return $speedStats;
    }
    
    /**
     * 5. Call Failure Diagnostics
     * Identifies patterns in failed calls
     */
    public function getCallFailureDiagnostics($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->whereIn('status', ['NA', 'B', 'DC', 'DEAD', 'DROP'])
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('AVG(talk_time) as avg_duration')
            ->groupBy('status');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $failures = $query->get();
        
        $diagnostics = [
            'failure_types' => [],
            'total_failures' => 0,
            'failure_rate' => 0
        ];
        
        foreach ($failures as $failure) {
            $diagnostics['failure_types'][] = [
                'status' => $failure->status,
                'count' => $failure->count,
                'avg_duration' => round($failure->avg_duration, 2),
                'description' => $this->getStatusDescription($failure->status)
            ];
            $diagnostics['total_failures'] += $failure->count;
        }
        
        // Calculate failure rate
        $totalCalls = ViciCallMetrics::count();
        if ($totalCalls > 0) {
            $diagnostics['failure_rate'] = round(($diagnostics['total_failures'] / $totalCalls) * 100, 2);
        }
        
        return $diagnostics;
    }
    
    /**
     * 6. Optimal Call Time Analysis
     * Determines best times to call based on connection rates
     */
    public function getOptimalCallTimeAnalysis($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->selectRaw('HOUR(last_call_date) as hour')
            ->selectRaw('DAYOFWEEK(last_call_date) as day_of_week')
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('SUM(CASE WHEN connected = true THEN 1 ELSE 0 END) as connected_calls')
            ->selectRaw('SUM(CASE WHEN status = "SALE" THEN 1 ELSE 0 END) as sales')
            ->groupBy('hour', 'day_of_week');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $timeData = $query->get();
        
        $analysis = [
            'by_hour' => [],
            'by_day' => [],
            'best_times' => []
        ];
        
        // Process by hour
        $hourlyStats = [];
        foreach ($timeData as $data) {
            $hour = $data->hour;
            if (!isset($hourlyStats[$hour])) {
                $hourlyStats[$hour] = [
                    'total_calls' => 0,
                    'connected_calls' => 0,
                    'sales' => 0
                ];
            }
            $hourlyStats[$hour]['total_calls'] += $data->total_calls;
            $hourlyStats[$hour]['connected_calls'] += $data->connected_calls;
            $hourlyStats[$hour]['sales'] += $data->sales;
        }
        
        foreach ($hourlyStats as $hour => $stats) {
            $analysis['by_hour'][] = [
                'hour' => $hour,
                'total_calls' => $stats['total_calls'],
                'connection_rate' => $stats['total_calls'] > 0 
                    ? round(($stats['connected_calls'] / $stats['total_calls']) * 100, 2) 
                    : 0,
                'conversion_rate' => $stats['connected_calls'] > 0 
                    ? round(($stats['sales'] / $stats['connected_calls']) * 100, 2) 
                    : 0
            ];
        }
        
        // Sort by connection rate to find best times
        usort($analysis['by_hour'], function($a, $b) {
            return $b['connection_rate'] <=> $a['connection_rate'];
        });
        
        $analysis['best_times'] = array_slice($analysis['by_hour'], 0, 5);
        
        return $analysis;
    }
    
    /**
     * 7. Lead Recycling Intelligence
     * Identifies leads that should be recycled based on patterns
     */
    public function getLeadRecyclingIntelligence($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->where('total_calls', '<', 5)
            ->where('connected', false)
            ->whereNotIn('status', ['DNC', 'SALE', 'NI']);
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $recyclable = $query->count();
        
        // Get stale leads (not called in 30+ days)
        $staleLeads = ViciCallMetrics::query()
            ->where('last_call_date', '<', Carbon::now()->subDays(30))
            ->whereNotIn('status', ['DNC', 'SALE', 'NI'])
            ->count();
        
        // Get callback leads
        $callbacks = ViciCallMetrics::query()
            ->where('status', 'CALLBK')
            ->count();
        
        return [
            'recyclable_leads' => $recyclable,
            'stale_leads' => $staleLeads,
            'pending_callbacks' => $callbacks,
            'total_opportunity' => $recyclable + $staleLeads + $callbacks,
            'recommendations' => [
                'immediate_recycle' => $recyclable,
                'schedule_callbacks' => $callbacks,
                'reactivate_stale' => $staleLeads
            ]
        ];
    }
    
    /**
     * 8. Transfer Success Analytics
     * Analyzes transfer success rates
     */
    public function getTransferSuccessAnalytics($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->where('status', 'XFER')
            ->select('campaign_id', 'agent_id')
            ->selectRaw('COUNT(*) as transfer_count')
            ->selectRaw('AVG(talk_time) as avg_talk_time')
            ->groupBy('campaign_id', 'agent_id');
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $transfers = $query->get();
        
        return [
            'total_transfers' => $transfers->sum('transfer_count'),
            'by_campaign' => $transfers->groupBy('campaign_id')->map(function($group) {
                return [
                    'count' => $group->sum('transfer_count'),
                    'avg_talk_time' => round($group->avg('avg_talk_time'), 2)
                ];
            }),
            'by_agent' => $transfers->groupBy('agent_id')->map(function($group) {
                return [
                    'count' => $group->sum('transfer_count'),
                    'avg_talk_time' => round($group->avg('avg_talk_time'), 2)
                ];
            })
        ];
    }
    
    /**
     * 9. Real-Time Operations Dashboard
     * Current operational metrics
     */
    public function getRealTimeOperationsDashboard()
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $hourAgo = $now->copy()->subHour();
        
        return [
            'current_hour' => [
                'calls' => ViciCallMetrics::where('last_call_date', '>=', $hourAgo)->count(),
                'connected' => ViciCallMetrics::where('last_call_date', '>=', $hourAgo)
                    ->where('connected', true)->count(),
                'sales' => ViciCallMetrics::where('last_call_date', '>=', $hourAgo)
                    ->where('status', 'SALE')->count()
            ],
            'today' => [
                'calls' => ViciCallMetrics::where('last_call_date', '>=', $todayStart)->count(),
                'connected' => ViciCallMetrics::where('last_call_date', '>=', $todayStart)
                    ->where('connected', true)->count(),
                'sales' => ViciCallMetrics::where('last_call_date', '>=', $todayStart)
                    ->where('status', 'SALE')->count(),
                'unique_leads' => ViciCallMetrics::where('last_call_date', '>=', $todayStart)
                    ->distinct('lead_id')->count()
            ],
            'active_agents' => ViciCallMetrics::where('last_call_date', '>=', $hourAgo)
                ->distinct('agent_id')->count(),
            'orphan_calls' => OrphanCallLog::where('created_at', '>=', $todayStart)->count()
        ];
    }
    
    /**
     * 10. Lead Waste Finder
     * Identifies wasted leads (high attempts, no connection)
     */
    public function getLeadWasteFinder($dateFrom = null, $dateTo = null)
    {
        $query = ViciCallMetrics::query()
            ->where('total_calls', '>=', 10)
            ->where('connected', false);
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('last_call_date', [$dateFrom, $dateTo]);
        }
        
        $wastedLeads = $query->get();
        
        $waste = [
            'total_wasted_leads' => $wastedLeads->count(),
            'total_wasted_calls' => $wastedLeads->sum('total_calls'),
            'avg_attempts_per_lead' => $wastedLeads->count() > 0 
                ? round($wastedLeads->avg('total_calls'), 2) 
                : 0,
            'top_waste_campaigns' => []
        ];
        
        // Group by campaign
        $byCampaign = $wastedLeads->groupBy('campaign_id');
        foreach ($byCampaign as $campaign => $leads) {
            $waste['top_waste_campaigns'][] = [
                'campaign' => $campaign,
                'wasted_leads' => $leads->count(),
                'wasted_calls' => $leads->sum('total_calls')
            ];
        }
        
        // Sort by wasted calls
        usort($waste['top_waste_campaigns'], function($a, $b) {
            return $b['wasted_calls'] - $a['wasted_calls'];
        });
        
        return $waste;
    }
    
    /**
     * 11. Predictive Lead Scoring
     * Scores leads based on historical patterns
     */
    public function getPredictiveLeadScoring($limit = 100)
    {
        $leads = Lead::query()
            ->leftJoin('vici_call_metrics', 'leads.id', '=', 'vici_call_metrics.lead_id')
            ->select('leads.*', 'vici_call_metrics.*')
            ->whereNull('vici_call_metrics.status')
            ->orWhereNotIn('vici_call_metrics.status', ['SALE', 'DNC', 'NI'])
            ->limit($limit)
            ->get();
        
        $scoredLeads = [];
        
        foreach ($leads as $lead) {
            $score = 100; // Base score
            
            // Reduce score for multiple failed attempts
            if ($lead->total_calls > 0 && !$lead->connected) {
                $score -= ($lead->total_calls * 10);
            }
            
            // Increase score for recent leads
            $daysSinceCreation = Carbon::parse($lead->created_at)->diffInDays(Carbon::now());
            if ($daysSinceCreation < 7) {
                $score += 20;
            } elseif ($daysSinceCreation > 30) {
                $score -= 20;
            }
            
            // Adjust based on time zone (if available)
            // Add more scoring logic based on your business rules
            
            $scoredLeads[] = [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'score' => max(0, min(100, $score)),
                'attempts' => $lead->total_calls ?? 0,
                'last_attempt' => $lead->last_call_date,
                'recommendation' => $score > 70 ? 'High Priority' : ($score > 40 ? 'Medium Priority' : 'Low Priority')
            ];
        }
        
        // Sort by score descending
        usort($scoredLeads, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $scoredLeads;
    }
    
    /**
     * 12. Executive Summary Report
     * High-level metrics for management
     */
    public function getExecutiveSummary($dateFrom = null, $dateTo = null)
    {
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfMonth();
        }
        if (!$dateTo) {
            $dateTo = Carbon::now();
        }
        
        $totalCalls = ViciCallMetrics::whereBetween('last_call_date', [$dateFrom, $dateTo])->count();
        $connectedCalls = ViciCallMetrics::whereBetween('last_call_date', [$dateFrom, $dateTo])
            ->where('connected', true)->count();
        $sales = ViciCallMetrics::whereBetween('last_call_date', [$dateFrom, $dateTo])
            ->where('status', 'SALE')->count();
        $uniqueLeads = ViciCallMetrics::whereBetween('last_call_date', [$dateFrom, $dateTo])
            ->distinct('lead_id')->count();
        
        return [
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d')
            ],
            'overview' => [
                'total_calls' => $totalCalls,
                'connected_calls' => $connectedCalls,
                'connection_rate' => $totalCalls > 0 
                    ? round(($connectedCalls / $totalCalls) * 100, 2) . '%'
                    : '0%',
                'total_sales' => $sales,
                'conversion_rate' => $connectedCalls > 0 
                    ? round(($sales / $connectedCalls) * 100, 2) . '%'
                    : '0%',
                'unique_leads_contacted' => $uniqueLeads
            ],
            'top_performers' => $this->getAgentScorecard($dateFrom, $dateTo),
            'campaign_performance' => $this->getCampaignROI($dateFrom, $dateTo),
            'speed_to_lead' => $this->getSpeedToLead($dateFrom, $dateTo),
            'recycling_opportunity' => $this->getLeadRecyclingIntelligence($dateFrom, $dateTo)
        ];
    }
    
    /**
     * Helper: Get status description
     */
    private function getStatusDescription($status)
    {
        $descriptions = [
            'NA' => 'No Answer',
            'B' => 'Busy',
            'DC' => 'Disconnected',
            'DEAD' => 'Dead Line',
            'DROP' => 'Call Dropped',
            'SALE' => 'Sale Made',
            'DNC' => 'Do Not Call',
            'NI' => 'Not Interested',
            'XFER' => 'Transferred',
            'CALLBK' => 'Callback Scheduled'
        ];
        
        return $descriptions[$status] ?? $status;
    }
}




