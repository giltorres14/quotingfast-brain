<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ABTestService
{
    private $viciService;
    
    public function __construct()
    {
        $this->viciService = app(ViciDialerService::class);
    }
    
    /**
     * Assign incoming lead to A or B test group
     * This is THE critical function - must be perfectly balanced
     */
    public function assignLeadToTest($leadId)
    {
        // Get current distribution to ensure 50/50 split
        $counts = DB::table('ab_test_leads')
            ->selectRaw('test_group, COUNT(*) as count')
            ->whereDate('assigned_at', '>=', Carbon::today())
            ->groupBy('test_group')
            ->pluck('count', 'test_group')
            ->toArray();
        
        $countA = $counts['A'] ?? 0;
        $countB = $counts['B'] ?? 0;
        
        // Assign to group with fewer leads (maintains balance)
        // If equal, use random
        if ($countA < $countB) {
            $group = 'A';
        } elseif ($countB < $countA) {
            $group = 'B';
        } else {
            $group = rand(0, 1) ? 'A' : 'B';
        }
        
        // Get configuration for assigned group
        $config = DB::table('ab_test_config')
            ->where('group', $group)
            ->where('active', true)
            ->first();
        
        if (!$config) {
            Log::error("No active config for group $group");
            return null;
        }
        
        $listFlow = json_decode($config->list_flow);
        $startingList = $listFlow[0];
        
        // Record the assignment
        DB::table('ab_test_leads')->insert([
            'lead_id' => $leadId,
            'test_group' => $group,
            'test_name' => $config->test_name,
            'assigned_at' => now(),
            'starting_list_id' => $startingList,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Update the lead with test group and Vici list
        DB::table('leads')->where('id', $leadId)->update([
            'test_group' => $group,
            'vici_list_id' => $startingList
        ]);
        
        // Send to Vici with appropriate list
        $this->sendToViciList($leadId, $startingList);
        
        Log::info("Lead $leadId assigned to Test Group $group (List $startingList)", [
            'strategy' => $config->strategy_name,
            'daily_balance' => ['A' => $countA, 'B' => $countB + ($group === 'B' ? 1 : 0)]
        ]);
        
        return $group;
    }
    
    /**
     * Send lead to specific Vici list based on test group
     */
    private function sendToViciList($leadId, $listId)
    {
        $lead = Lead::find($leadId);
        if (!$lead) return;
        
        // Prepare lead data for Vici
        $viciData = [
            'list_id' => $listId,
            'phone_number' => $lead->phone,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'address1' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'postal_code' => $lead->zip_code,
            'vendor_lead_code' => $lead->external_lead_id,
            'source_id' => 'AB_TEST_' . $lead->test_group
        ];
        
        // Send to Vici
        try {
            $this->viciService->addLead($viciData);
        } catch (\Exception $e) {
            Log::error("Failed to send lead $leadId to Vici list $listId: " . $e->getMessage());
        }
    }
    
    /**
     * Record each call attempt for tracking
     */
    public function recordAttempt($leadId, $status, $attemptNumber, $listId, $didUsed, $agent = null)
    {
        // Get test group
        $testLead = DB::table('ab_test_leads')->where('lead_id', $leadId)->first();
        if (!$testLead) return;
        
        $lead = Lead::find($leadId);
        $hoursSinceEntry = Carbon::now()->diffInHours($lead->created_at);
        $daysSinceEntry = Carbon::now()->diffInDays($lead->created_at);
        
        // Determine outcomes
        $answered = !in_array($status, ['NA', 'B', 'N', 'CANCEL', 'CONGESTION']);
        $contacted = in_array($status, ['XFER', 'XFERA', 'SALE', 'NI', 'DNC', 'CALLBK']);
        $positive = in_array($status, ['XFER', 'XFERA', 'SALE']);
        
        // Record the attempt
        DB::table('ab_test_attempts')->insert([
            'lead_id' => $leadId,
            'test_group' => $testLead->test_group,
            'attempt_number' => $attemptNumber,
            'list_id' => $listId,
            'call_time' => now(),
            'did_used' => $didUsed,
            'status' => $status,
            'agent' => $agent,
            'hours_since_entry' => $hoursSinceEntry,
            'days_since_entry' => $daysSinceEntry,
            'previous_attempts' => $attemptNumber - 1,
            'answered' => $answered,
            'contacted' => $contacted,
            'positive_outcome' => $positive,
            'created_at' => now()
        ]);
        
        // Update lead summary
        $updates = [
            'total_attempts' => DB::raw('total_attempts + 1'),
            'updated_at' => now()
        ];
        
        if ($daysSinceEntry == 0) {
            $updates['day1_attempts'] = DB::raw('day1_attempts + 1');
        }
        if ($daysSinceEntry < 7) {
            $updates['week1_attempts'] = DB::raw('week1_attempts + 1');
        } elseif ($daysSinceEntry < 14) {
            $updates['week2_attempts'] = DB::raw('week2_attempts + 1');
        }
        
        if ($contacted && !$testLead->contacted) {
            $updates['contacted'] = true;
            $updates['first_contact_attempt'] = $attemptNumber;
            $updates['first_contact_time'] = now();
        }
        
        if ($positive && !$testLead->converted) {
            $updates['converted'] = true;
            $updates['conversion_attempt'] = $attemptNumber;
            $updates['conversion_time'] = now();
            $updates['revenue'] = 500; // Or actual revenue
        }
        
        if ($status === 'DNC') {
            $updates['dnc_requested'] = true;
        }
        
        $updates['final_status'] = $status;
        $updates['total_cost'] = DB::raw('total_attempts * 0.50');
        
        DB::table('ab_test_leads')
            ->where('lead_id', $leadId)
            ->update($updates);
        
        // Update hourly stats
        $this->updateHourlyStats($testLead->test_group);
    }
    
    /**
     * Update aggregated hourly statistics
     */
    private function updateHourlyStats($testGroup)
    {
        $hourBucket = Carbon::now()->startOfHour();
        
        // Calculate stats for this hour
        $stats = DB::table('ab_test_attempts')
            ->where('test_group', $testGroup)
            ->whereBetween('call_time', [$hourBucket, $hourBucket->copy()->addHour()])
            ->selectRaw('
                COUNT(DISTINCT lead_id) as unique_leads_called,
                COUNT(*) as total_attempts,
                SUM(answered) as answers,
                SUM(contacted) as contacts,
                SUM(positive_outcome) as conversions
            ')
            ->first();
        
        $newLeads = DB::table('ab_test_leads')
            ->where('test_group', $testGroup)
            ->whereBetween('assigned_at', [$hourBucket, $hourBucket->copy()->addHour()])
            ->count();
        
        $dncRequests = DB::table('ab_test_leads')
            ->where('test_group', $testGroup)
            ->where('dnc_requested', true)
            ->whereBetween('updated_at', [$hourBucket, $hourBucket->copy()->addHour()])
            ->count();
        
        // Calculate rates
        $contactRate = $stats->total_attempts > 0 ? 
            round(($stats->contacts / $stats->total_attempts) * 100, 2) : 0;
        $conversionRate = $stats->unique_leads_called > 0 ? 
            round(($stats->conversions / $stats->unique_leads_called) * 100, 2) : 0;
        $answerRate = $stats->total_attempts > 0 ? 
            round(($stats->answers / $stats->total_attempts) * 100, 2) : 0;
        
        // Financial calculations
        $hourlyCost = $stats->total_attempts * 0.50;
        $hourlyRevenue = $stats->conversions * 500;
        $hourlyROI = $hourlyCost > 0 ? 
            round((($hourlyRevenue - $hourlyCost) / $hourlyCost) * 100, 2) : 0;
        
        // Upsert hourly stats
        DB::table('ab_test_hourly_stats')->updateOrInsert(
            [
                'hour_bucket' => $hourBucket,
                'test_group' => $testGroup
            ],
            [
                'new_leads' => $newLeads,
                'total_attempts' => $stats->total_attempts,
                'unique_leads_called' => $stats->unique_leads_called,
                'contacts' => $stats->contacts,
                'conversions' => $stats->conversions,
                'dnc_requests' => $dncRequests,
                'contact_rate' => $contactRate,
                'conversion_rate' => $conversionRate,
                'answer_rate' => $answerRate,
                'hourly_cost' => $hourlyCost,
                'hourly_revenue' => $hourlyRevenue,
                'hourly_roi' => $hourlyROI,
                'updated_at' => now()
            ]
        );
    }
    
    /**
     * Get real-time comparison data for dashboard
     */
    public function getRealtimeComparison()
    {
        $groupA = $this->getGroupStats('A');
        $groupB = $this->getGroupStats('B');
        
        // Calculate statistical significance
        $significance = $this->calculateSignificance($groupA, $groupB);
        
        // Determine winner (if any)
        $winner = null;
        if ($significance['is_significant']) {
            $winner = $groupA['conversion_rate'] > $groupB['conversion_rate'] ? 'A' : 'B';
        }
        
        return [
            'group_a' => $groupA,
            'group_b' => $groupB,
            'significance' => $significance,
            'winner' => $winner,
            'insights' => $this->generateInsights($groupA, $groupB)
        ];
    }
    
    /**
     * Get comprehensive stats for a test group
     */
    private function getGroupStats($group)
    {
        $stats = DB::table('ab_test_leads')
            ->where('test_group', $group)
            ->selectRaw('
                COUNT(*) as total_leads,
                SUM(contacted) as contacted_count,
                SUM(converted) as converted_count,
                SUM(dnc_requested) as dnc_count,
                AVG(total_attempts) as avg_attempts,
                AVG(CASE WHEN contacted = 1 THEN first_contact_attempt END) as avg_contact_attempt,
                AVG(CASE WHEN converted = 1 THEN conversion_attempt END) as avg_conversion_attempt,
                SUM(revenue) as total_revenue,
                SUM(total_cost) as total_cost
            ')
            ->first();
        
        $config = DB::table('ab_test_config')
            ->where('group', $group)
            ->where('active', true)
            ->first();
        
        return [
            'group' => $group,
            'strategy_name' => $config->strategy_name ?? 'Unknown',
            'total_leads' => $stats->total_leads,
            'contacted' => $stats->contacted_count,
            'converted' => $stats->converted_count,
            'dnc_requests' => $stats->dnc_count,
            'contact_rate' => $stats->total_leads > 0 ? 
                round(($stats->contacted_count / $stats->total_leads) * 100, 2) : 0,
            'conversion_rate' => $stats->total_leads > 0 ? 
                round(($stats->converted_count / $stats->total_leads) * 100, 2) : 0,
            'dnc_rate' => $stats->total_leads > 0 ? 
                round(($stats->dnc_count / $stats->total_leads) * 100, 2) : 0,
            'avg_attempts' => round($stats->avg_attempts, 1),
            'avg_contact_attempt' => round($stats->avg_contact_attempt, 1),
            'avg_conversion_attempt' => round($stats->avg_conversion_attempt, 1),
            'total_revenue' => $stats->total_revenue,
            'total_cost' => $stats->total_cost,
            'roi' => $stats->total_cost > 0 ? 
                round((($stats->total_revenue - $stats->total_cost) / $stats->total_cost) * 100, 2) : 0,
            'cost_per_lead' => $stats->total_leads > 0 ? 
                round($stats->total_cost / $stats->total_leads, 2) : 0,
            'cost_per_conversion' => $stats->converted_count > 0 ? 
                round($stats->total_cost / $stats->converted_count, 2) : 0
        ];
    }
    
    /**
     * Calculate statistical significance between groups
     */
    private function calculateSignificance($groupA, $groupB)
    {
        $nA = $groupA['total_leads'];
        $nB = $groupB['total_leads'];
        
        if ($nA < 30 || $nB < 30) {
            return [
                'is_significant' => false,
                'confidence' => 0,
                'message' => 'Need at least 30 leads per group for significance'
            ];
        }
        
        $pA = $groupA['conversion_rate'] / 100;
        $pB = $groupB['conversion_rate'] / 100;
        
        // Calculate pooled probability
        $pPool = (($pA * $nA) + ($pB * $nB)) / ($nA + $nB);
        
        // Calculate standard error
        $se = sqrt($pPool * (1 - $pPool) * ((1/$nA) + (1/$nB)));
        
        if ($se == 0) {
            return [
                'is_significant' => false,
                'confidence' => 0,
                'message' => 'Unable to calculate significance'
            ];
        }
        
        // Calculate z-score
        $z = abs($pA - $pB) / $se;
        
        // Determine significance (z > 1.96 for 95% confidence)
        $isSignificant = $z > 1.96;
        $confidence = $this->zToConfidence($z);
        
        return [
            'is_significant' => $isSignificant,
            'confidence' => $confidence,
            'z_score' => round($z, 2),
            'message' => $isSignificant ? 
                "Results are statistically significant at {$confidence}% confidence" :
                "Results are not yet statistically significant ({$confidence}% confidence)"
        ];
    }
    
    /**
     * Convert z-score to confidence percentage
     */
    private function zToConfidence($z)
    {
        if ($z >= 2.58) return 99;
        if ($z >= 1.96) return 95;
        if ($z >= 1.64) return 90;
        if ($z >= 1.28) return 80;
        return round(50 + ($z * 20), 0);
    }
    
    /**
     * Generate actionable insights from the data
     */
    private function generateInsights($groupA, $groupB)
    {
        $insights = [];
        
        // Contact rate comparison
        if (abs($groupA['contact_rate'] - $groupB['contact_rate']) > 5) {
            $better = $groupA['contact_rate'] > $groupB['contact_rate'] ? 'A' : 'B';
            $worse = $better === 'A' ? 'B' : 'A';
            $diff = abs($groupA['contact_rate'] - $groupB['contact_rate']);
            $insights[] = "Group $better has {$diff}% higher contact rate - " .
                         ($better === 'A' ? 'aggressive calling connects more' : 'strategic timing works better');
        }
        
        // Conversion comparison
        if ($groupA['conversion_rate'] > 0 || $groupB['conversion_rate'] > 0) {
            $better = $groupA['conversion_rate'] > $groupB['conversion_rate'] ? 'A' : 'B';
            if ($groupA['conversion_rate'] != $groupB['conversion_rate']) {
                $lift = $groupB['conversion_rate'] > 0 ? 
                    round((($groupA['conversion_rate'] / $groupB['conversion_rate']) - 1) * 100, 1) :
                    100;
                $insights[] = "Group $better converts " . abs($lift) . "% better";
            }
        }
        
        // ROI comparison
        if ($groupA['roi'] != $groupB['roi']) {
            $better = $groupA['roi'] > $groupB['roi'] ? 'A' : 'B';
            $insights[] = "Group $better has better ROI: " . 
                         ($better === 'A' ? $groupA['roi'] : $groupB['roi']) . "% vs " .
                         ($better === 'A' ? $groupB['roi'] : $groupA['roi']) . "%";
        }
        
        // DNC rate warning
        if ($groupA['dnc_rate'] > 5 || $groupB['dnc_rate'] > 5) {
            $higher = $groupA['dnc_rate'] > $groupB['dnc_rate'] ? 'A' : 'B';
            $insights[] = "⚠️ Group $higher has high DNC rate ({$groupA['dnc_rate']}%) - may need adjustment";
        }
        
        // Efficiency insight
        if ($groupA['avg_attempts'] > 0 && $groupB['avg_attempts'] > 0) {
            $moreAttempts = $groupA['avg_attempts'] > $groupB['avg_attempts'] ? 'A' : 'B';
            $fewerAttempts = $moreAttempts === 'A' ? 'B' : 'A';
            $attemptDiff = abs($groupA['avg_attempts'] - $groupB['avg_attempts']);
            $insights[] = "Group $moreAttempts makes {$attemptDiff} more calls per lead on average";
        }
        
        return $insights;
    }
}

