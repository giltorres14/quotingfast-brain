<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadJourneyAnalytics
{
    /**
     * Test groups with different calling strategies
     */
    private const TEST_GROUPS = [
        'A_HYPER' => [
            'name' => 'Hyper-Aggressive',
            'day1_calls' => 10,
            'week1_calls' => 25,
            'week2_calls' => 15,
            'total_calls' => 45,
            'list_id' => 101
        ],
        'B_FRONT' => [
            'name' => 'Front-Loaded', 
            'day1_calls' => 6,
            'week1_calls' => 15,
            'week2_calls' => 8,
            'total_calls' => 25,
            'list_id' => 201
        ],
        'C_STEADY' => [
            'name' => 'Steady Persistence',
            'day1_calls' => 3,
            'week1_calls' => 10,
            'week2_calls' => 7,
            'total_calls' => 18,
            'list_id' => 301
        ],
        'D_CONSERV' => [
            'name' => 'Conservative',
            'day1_calls' => 2,
            'week1_calls' => 7,
            'week2_calls' => 4,
            'total_calls' => 12,
            'list_id' => 401
        ]
    ];

    /**
     * Assign lead to random test group
     */
    public function assignTestGroup($leadId)
    {
        $groups = array_keys(self::TEST_GROUPS);
        $randomGroup = $groups[array_rand($groups)];
        $config = self::TEST_GROUPS[$randomGroup];
        
        // Update lead with test group
        DB::table('leads')->where('id', $leadId)->update([
            'test_group' => $randomGroup,
            'vici_list_id' => $config['list_id'],
            'max_attempts' => $config['total_calls']
        ]);
        
        // Log the assignment
        DB::table('lead_journey_log')->insert([
            'lead_id' => $leadId,
            'event_type' => 'TEST_ASSIGNED',
            'event_data' => json_encode($config),
            'created_at' => now()
        ]);
        
        return $randomGroup;
    }

    /**
     * Get comprehensive analytics for all test groups
     */
    public function getTestGroupAnalytics()
    {
        $results = [];
        
        foreach (self::TEST_GROUPS as $groupCode => $config) {
            $results[$groupCode] = [
                'config' => $config,
                'metrics' => $this->calculateGroupMetrics($groupCode),
                'roi' => $this->calculateROI($groupCode),
                'optimal_attempts' => $this->findOptimalAttempts($groupCode)
            ];
        }
        
        return $results;
    }

    /**
     * Calculate key metrics for a test group
     */
    private function calculateGroupMetrics($groupCode)
    {
        return DB::table('leads as l')
            ->leftJoin('lead_journey_log as j', 'l.id', '=', 'j.lead_id')
            ->where('l.test_group', $groupCode)
            ->selectRaw('
                COUNT(DISTINCT l.id) as total_leads,
                AVG(j.attempt_number) as avg_attempts,
                SUM(CASE WHEN j.contacted = 1 THEN 1 ELSE 0 END) as total_contacts,
                SUM(CASE WHEN j.converted = 1 THEN 1 ELSE 0 END) as total_conversions,
                SUM(CASE WHEN l.status = "DNC" THEN 1 ELSE 0 END) as dnc_count,
                AVG(CASE WHEN j.contacted = 1 THEN j.attempt_number END) as avg_contact_attempt,
                AVG(CASE WHEN j.converted = 1 THEN j.attempt_number END) as avg_conversion_attempt,
                ROUND(SUM(CASE WHEN j.contacted = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT l.id), 2) as contact_rate,
                ROUND(SUM(CASE WHEN j.converted = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT l.id), 2) as conversion_rate
            ')
            ->first();
    }

    /**
     * Calculate ROI for each test group
     */
    private function calculateROI($groupCode)
    {
        $costPerCall = 0.50;
        $revenuePerSale = 500;
        
        $data = DB::table('leads as l')
            ->leftJoin('lead_journey_log as j', 'l.id', '=', 'j.lead_id')
            ->where('l.test_group', $groupCode)
            ->selectRaw('
                COUNT(j.id) as total_calls,
                SUM(CASE WHEN j.converted = 1 THEN 1 ELSE 0 END) as conversions,
                COUNT(DISTINCT l.id) as total_leads
            ')
            ->first();
        
        $totalCost = $data->total_calls * $costPerCall;
        $totalRevenue = $data->conversions * $revenuePerSale;
        $roi = $totalCost > 0 ? round(($totalRevenue - $totalCost) / $totalCost * 100, 2) : 0;
        
        return [
            'total_cost' => $totalCost,
            'total_revenue' => $totalRevenue,
            'net_profit' => $totalRevenue - $totalCost,
            'roi_percentage' => $roi,
            'cost_per_lead' => round($totalCost / max($data->total_leads, 1), 2),
            'cost_per_conversion' => $data->conversions > 0 ? round($totalCost / $data->conversions, 2) : 0
        ];
    }

    /**
     * Find the optimal number of attempts (where ROI peaks)
     */
    private function findOptimalAttempts($groupCode)
    {
        $results = DB::select('
            WITH attempt_analysis AS (
                SELECT 
                    attempt_number,
                    SUM(CASE WHEN contacted = 1 THEN 1 ELSE 0 END) as contacts_at_attempt,
                    SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions_at_attempt,
                    COUNT(*) as calls_made,
                    SUM(SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END)) OVER (ORDER BY attempt_number) as cumulative_conversions
                FROM lead_journey_log j
                JOIN leads l ON j.lead_id = l.id
                WHERE l.test_group = ?
                GROUP BY attempt_number
            )
            SELECT 
                attempt_number,
                contacts_at_attempt,
                conversions_at_attempt,
                cumulative_conversions,
                calls_made,
                (conversions_at_attempt * 500) - (calls_made * 0.50) as net_value_this_attempt,
                SUM((conversions_at_attempt * 500) - (calls_made * 0.50)) OVER (ORDER BY attempt_number) as cumulative_net_value
            FROM attempt_analysis
            ORDER BY attempt_number
        ', [$groupCode]);
        
        // Find the attempt number with highest cumulative ROI
        $maxROI = 0;
        $optimalAttempt = 0;
        
        foreach ($results as $row) {
            if ($row->cumulative_net_value > $maxROI) {
                $maxROI = $row->cumulative_net_value;
                $optimalAttempt = $row->attempt_number;
            }
        }
        
        return [
            'optimal_attempts' => $optimalAttempt,
            'max_roi' => $maxROI,
            'detail_by_attempt' => $results
        ];
    }

    /**
     * Get heat map data for best calling times
     */
    public function getCallTimeHeatMap()
    {
        return DB::select('
            SELECT 
                DAYOFWEEK(call_date) as day_of_week,
                HOUR(call_date) as hour_of_day,
                COUNT(*) as total_calls,
                SUM(contacted) as contacts,
                SUM(converted) as conversions,
                ROUND(AVG(contacted) * 100, 2) as contact_rate,
                ROUND(AVG(converted) * 100, 2) as conversion_rate
            FROM lead_journey_log
            GROUP BY DAYOFWEEK(call_date), HOUR(call_date)
            ORDER BY day_of_week, hour_of_day
        ');
    }

    /**
     * Track every call attempt with full context
     */
    public function logCallAttempt($leadId, $status, $attemptNumber, $didUsed, $agentId = null)
    {
        $contacted = in_array($status, ['XFER', 'XFERA', 'SALE', 'NI', 'DNC', 'CALLBK']);
        $converted = in_array($status, ['SALE', 'XFER', 'XFERA']);
        
        DB::table('lead_journey_log')->insert([
            'lead_id' => $leadId,
            'attempt_number' => $attemptNumber,
            'call_date' => now(),
            'did_used' => $didUsed,
            'status' => $status,
            'agent_id' => $agentId,
            'contacted' => $contacted,
            'converted' => $converted,
            'created_at' => now()
        ]);
        
        // Update lead summary
        if ($contacted) {
            DB::table('leads')
                ->where('id', $leadId)
                ->update([
                    'first_contact_attempt' => DB::raw('COALESCE(first_contact_attempt, ' . $attemptNumber . ')'),
                    'last_contact_date' => now()
                ]);
        }
        
        if ($converted) {
            DB::table('leads')
                ->where('id', $leadId)
                ->update([
                    'conversion_attempt' => $attemptNumber,
                    'conversion_date' => now()
                ]);
        }
    }

    /**
     * Generate comprehensive report comparing all strategies
     */
    public function generateComparisonReport()
    {
        $report = [
            'generated_at' => now(),
            'test_groups' => $this->getTestGroupAnalytics(),
            'best_times' => $this->getCallTimeHeatMap(),
            'recommendations' => $this->generateRecommendations()
        ];
        
        return $report;
    }

    /**
     * Generate data-driven recommendations
     */
    private function generateRecommendations()
    {
        $analytics = $this->getTestGroupAnalytics();
        
        // Find best performing group
        $bestROI = 0;
        $bestGroup = null;
        $bestConversion = 0;
        $bestConversionGroup = null;
        
        foreach ($analytics as $groupCode => $data) {
            if ($data['roi']['roi_percentage'] > $bestROI) {
                $bestROI = $data['roi']['roi_percentage'];
                $bestGroup = $groupCode;
            }
            if ($data['metrics']->conversion_rate > $bestConversion) {
                $bestConversion = $data['metrics']->conversion_rate;
                $bestConversionGroup = $groupCode;
            }
        }
        
        return [
            'best_roi_strategy' => $bestGroup,
            'best_roi_value' => $bestROI,
            'best_conversion_strategy' => $bestConversionGroup,
            'best_conversion_rate' => $bestConversion,
            'optimal_attempts' => $analytics[$bestGroup]['optimal_attempts']['optimal_attempts'] ?? 0,
            'insights' => $this->generateInsights($analytics)
        ];
    }

    /**
     * Generate specific insights from the data
     */
    private function generateInsights($analytics)
    {
        $insights = [];
        
        // Compare aggressive vs conservative
        if (isset($analytics['A_HYPER']) && isset($analytics['D_CONSERV'])) {
            $hyperROI = $analytics['A_HYPER']['roi']['roi_percentage'];
            $conservROI = $analytics['D_CONSERV']['roi']['roi_percentage'];
            
            if ($hyperROI > $conservROI) {
                $insights[] = "Aggressive calling (" . $analytics['A_HYPER']['config']['total_calls'] . " calls) produces " . 
                             round($hyperROI - $conservROI, 2) . "% better ROI than conservative approach";
            } else {
                $insights[] = "Conservative calling (" . $analytics['D_CONSERV']['config']['total_calls'] . " calls) produces " . 
                             round($conservROI - $hyperROI, 2) . "% better ROI than aggressive approach";
            }
        }
        
        // Find diminishing returns point
        foreach ($analytics as $group => $data) {
            if (isset($data['optimal_attempts']['optimal_attempts'])) {
                $optimal = $data['optimal_attempts']['optimal_attempts'];
                $total = $data['config']['total_calls'];
                if ($optimal < $total * 0.5) {
                    $insights[] = "For " . $data['config']['name'] . " strategy, ROI peaks at attempt #" . $optimal . 
                                 " - remaining " . ($total - $optimal) . " calls may not be worth it";
                }
            }
        }
        
        return $insights;
    }
}
