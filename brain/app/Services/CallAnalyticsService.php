<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use App\Models\LeadConversion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CallAnalyticsService
{
    /**
     * Get comprehensive analytics for a date range
     */
    public static function getAnalytics(string $startDate, string $endDate, array $filters = []): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'days' => $start->diffInDays($end) + 1
            ],
            'lead_metrics' => self::getLeadMetrics($start, $end, $filters),
            'call_metrics' => self::getCallMetrics($start, $end, $filters),
            'conversion_metrics' => self::getConversionMetrics($start, $end, $filters),
            'timing_metrics' => self::getTimingMetrics($start, $end, $filters),
            'agent_performance' => self::getAgentPerformance($start, $end, $filters),
            'buyer_performance' => self::getBuyerPerformance($start, $end, $filters),
            'daily_breakdown' => self::getDailyBreakdown($start, $end, $filters)
        ];
    }

    /**
     * Get lead metrics
     */
    private static function getLeadMetrics(Carbon $start, Carbon $end, array $filters): array
    {
        $query = Lead::whereBetween('created_at', [$start, $end]);
        
        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }
        
        $totalLeads = $query->count();
        $leadsWithCalls = $query->whereHas('viciCallMetrics')->count();
        $leadsWithTransfers = $query->whereHas('viciCallMetrics', function($q) {
            $q->where('transfer_requested', true);
        })->count();
        
        return [
            'total_leads' => $totalLeads,
            'leads_with_calls' => $leadsWithCalls,
            'leads_with_transfers' => $leadsWithTransfers,
            'call_rate' => $totalLeads > 0 ? round(($leadsWithCalls / $totalLeads) * 100, 2) : 0,
            'transfer_rate' => $leadsWithCalls > 0 ? round(($leadsWithTransfers / $leadsWithCalls) * 100, 2) : 0
        ];
    }

    /**
     * Get call metrics
     */
    private static function getCallMetrics(Carbon $start, Carbon $end, array $filters): array
    {
        $query = ViciCallMetrics::whereBetween('created_at', [$start, $end]);
        
        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        
        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }
        
        $metrics = $query->selectRaw('
            COUNT(*) as total_call_records,
            SUM(call_attempts) as total_attempts,
            COUNT(CASE WHEN connected_time IS NOT NULL THEN 1 END) as connected_calls,
            COUNT(CASE WHEN transfer_requested = 1 THEN 1 END) as transfer_requests,
            AVG(call_duration) as avg_call_duration,
            AVG(talk_time) as avg_talk_time,
            SUM(talk_time) as total_talk_time
        ')->first();
        
        $connectionRate = $metrics->total_attempts > 0 ? 
            round(($metrics->connected_calls / $metrics->total_attempts) * 100, 2) : 0;
            
        $transferRate = $metrics->connected_calls > 0 ? 
            round(($metrics->transfer_requests / $metrics->connected_calls) * 100, 2) : 0;
        
        return [
            'total_call_records' => $metrics->total_call_records,
            'total_call_attempts' => $metrics->total_attempts,
            'connected_calls' => $metrics->connected_calls,
            'transfer_requests' => $metrics->transfer_requests,
            'connection_rate' => $connectionRate,
            'transfer_rate' => $transferRate,
            'avg_call_duration' => round($metrics->avg_call_duration ?? 0, 2),
            'avg_talk_time' => round($metrics->avg_talk_time ?? 0, 2),
            'total_talk_time' => $metrics->total_talk_time ?? 0
        ];
    }

    /**
     * Get conversion metrics
     */
    private static function getConversionMetrics(Carbon $start, Carbon $end, array $filters): array
    {
        $query = LeadConversion::whereBetween('created_at', [$start, $end]);
        
        if (isset($filters['buyer_name'])) {
            $query->where('buyer_name', $filters['buyer_name']);
        }
        
        $conversions = $query->selectRaw('
            COUNT(*) as total_conversions,
            COUNT(CASE WHEN converted = 1 THEN 1 END) as successful_conversions,
            SUM(CASE WHEN converted = 1 THEN conversion_value ELSE 0 END) as total_revenue,
            AVG(CASE WHEN converted = 1 THEN conversion_value ELSE NULL END) as avg_conversion_value
        ')->first();
        
        $conversionRate = $conversions->total_conversions > 0 ? 
            round(($conversions->successful_conversions / $conversions->total_conversions) * 100, 2) : 0;
        
        return [
            'total_conversions' => $conversions->total_conversions,
            'successful_conversions' => $conversions->successful_conversions,
            'conversion_rate' => $conversionRate,
            'total_revenue' => round($conversions->total_revenue ?? 0, 2),
            'avg_conversion_value' => round($conversions->avg_conversion_value ?? 0, 2),
            'revenue_per_lead' => $conversions->total_conversions > 0 ? 
                round(($conversions->total_revenue ?? 0) / $conversions->total_conversions, 2) : 0
        ];
    }

    /**
     * Get timing metrics
     */
    private static function getTimingMetrics(Carbon $start, Carbon $end, array $filters): array
    {
        $query = LeadConversion::whereBetween('created_at', [$start, $end])
            ->whereNotNull('time_to_first_call');
        
        $timing = $query->selectRaw('
            AVG(time_to_first_call) as avg_time_to_first_call,
            AVG(time_to_transfer) as avg_time_to_transfer,
            AVG(time_to_conversion) as avg_time_to_conversion,
            MIN(time_to_first_call) as min_time_to_first_call,
            MAX(time_to_first_call) as max_time_to_first_call
        ')->first();
        
        return [
            'avg_time_to_first_call' => round($timing->avg_time_to_first_call ?? 0, 2),
            'avg_time_to_transfer' => round($timing->avg_time_to_transfer ?? 0, 2),
            'avg_time_to_conversion' => round($timing->avg_time_to_conversion ?? 0, 2),
            'min_time_to_first_call' => $timing->min_time_to_first_call ?? 0,
            'max_time_to_first_call' => $timing->max_time_to_first_call ?? 0,
            'avg_time_to_first_call_minutes' => round(($timing->avg_time_to_first_call ?? 0) / 60, 2),
            'avg_time_to_transfer_minutes' => round(($timing->avg_time_to_transfer ?? 0) / 60, 2)
        ];
    }

    /**
     * Get agent performance metrics
     */
    private static function getAgentPerformance(Carbon $start, Carbon $end, array $filters): array
    {
        $query = ViciCallMetrics::whereBetween('created_at', [$start, $end])
            ->whereNotNull('agent_id');
        
        return $query->selectRaw('
            agent_id,
            COUNT(*) as total_calls,
            SUM(call_attempts) as total_attempts,
            COUNT(CASE WHEN connected_time IS NOT NULL THEN 1 END) as connected_calls,
            COUNT(CASE WHEN transfer_requested = 1 THEN 1 END) as transfers,
            AVG(talk_time) as avg_talk_time,
            SUM(talk_time) as total_talk_time
        ')
        ->groupBy('agent_id')
        ->orderByDesc('total_calls')
        ->get()
        ->map(function($agent) {
            $connectionRate = $agent->total_attempts > 0 ? 
                round(($agent->connected_calls / $agent->total_attempts) * 100, 2) : 0;
            $transferRate = $agent->connected_calls > 0 ? 
                round(($agent->transfers / $agent->connected_calls) * 100, 2) : 0;
                
            return [
                'agent_id' => $agent->agent_id,
                'total_calls' => $agent->total_calls,
                'total_attempts' => $agent->total_attempts,
                'connected_calls' => $agent->connected_calls,
                'transfers' => $agent->transfers,
                'connection_rate' => $connectionRate,
                'transfer_rate' => $transferRate,
                'avg_talk_time' => round($agent->avg_talk_time ?? 0, 2),
                'total_talk_time' => $agent->total_talk_time ?? 0
            ];
        })->toArray();
    }

    /**
     * Get buyer performance metrics
     */
    private static function getBuyerPerformance(Carbon $start, Carbon $end, array $filters): array
    {
        $query = LeadConversion::whereBetween('created_at', [$start, $end])
            ->whereNotNull('buyer_name');
        
        return $query->selectRaw('
            buyer_name,
            COUNT(*) as total_leads,
            COUNT(CASE WHEN converted = 1 THEN 1 END) as conversions,
            SUM(CASE WHEN converted = 1 THEN conversion_value ELSE 0 END) as revenue,
            AVG(CASE WHEN converted = 1 THEN conversion_value ELSE NULL END) as avg_value
        ')
        ->groupBy('buyer_name')
        ->orderByDesc('revenue')
        ->get()
        ->map(function($buyer) {
            $conversionRate = $buyer->total_leads > 0 ? 
                round(($buyer->conversions / $buyer->total_leads) * 100, 2) : 0;
                
            return [
                'buyer_name' => $buyer->buyer_name,
                'total_leads' => $buyer->total_leads,
                'conversions' => $buyer->conversions,
                'conversion_rate' => $conversionRate,
                'revenue' => round($buyer->revenue ?? 0, 2),
                'avg_value' => round($buyer->avg_value ?? 0, 2)
            ];
        })->toArray();
    }

    /**
     * Get daily breakdown
     */
    private static function getDailyBreakdown(Carbon $start, Carbon $end, array $filters): array
    {
        $days = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();
            
            // Leads for this day
            $leads = Lead::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            
            // Calls for this day
            $calls = ViciCallMetrics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->selectRaw('
                    COUNT(*) as total_calls,
                    COUNT(CASE WHEN transfer_requested = 1 THEN 1 END) as transfers
                ')->first();
            
            // Conversions for this day
            $conversions = LeadConversion::whereBetween('created_at', [$dayStart, $dayEnd])
                ->selectRaw('
                    COUNT(CASE WHEN converted = 1 THEN 1 END) as conversions,
                    SUM(CASE WHEN converted = 1 THEN conversion_value ELSE 0 END) as revenue
                ')->first();
            
            $days[] = [
                'date' => $current->toDateString(),
                'leads' => $leads,
                'calls' => $calls->total_calls ?? 0,
                'transfers' => $calls->transfers ?? 0,
                'conversions' => $conversions->conversions ?? 0,
                'revenue' => round($conversions->revenue ?? 0, 2)
            ];
            
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Get predefined date ranges
     */
    public static function getDateRanges(): array
    {
        $now = Carbon::now();
        
        return [
            'today' => [
                'start' => $now->copy()->startOfDay()->toDateString(),
                'end' => $now->copy()->endOfDay()->toDateString(),
                'label' => 'Today'
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay()->toDateString(),
                'end' => $now->copy()->subDay()->endOfDay()->toDateString(),
                'label' => 'Yesterday'
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek()->toDateString(),
                'end' => $now->copy()->endOfWeek()->toDateString(),
                'label' => 'This Week'
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek()->toDateString(),
                'end' => $now->copy()->subWeek()->endOfWeek()->toDateString(),
                'label' => 'Last Week'
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth()->toDateString(),
                'end' => $now->copy()->endOfMonth()->toDateString(),
                'label' => 'This Month'
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth()->toDateString(),
                'end' => $now->copy()->subMonth()->endOfMonth()->toDateString(),
                'label' => 'Last Month'
            ],
            'last_30_days' => [
                'start' => $now->copy()->subDays(30)->toDateString(),
                'end' => $now->copy()->toDateString(),
                'label' => 'Last 30 Days'
            ]
        ];
    }
}