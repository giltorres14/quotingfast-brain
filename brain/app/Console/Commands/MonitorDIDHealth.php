<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitorDIDHealth extends Command
{
    protected $signature = 'did:monitor {--force : Force immediate check}';
    protected $description = 'Monitor DID health and detect spam-likely numbers';
    
    // Thresholds
    const CRITICAL_ANSWER_RATE = 5;    // Below this = likely spam
    const WARNING_ANSWER_RATE = 10;    // Below this = possible spam
    const HEALTHY_ANSWER_RATE = 15;    // Above this = good
    const MAX_DAILY_CALLS = 45;        // Maximum calls per day
    const OPTIMAL_DAILY_CALLS = 40;    // Target calls per day
    const MIN_DAILY_CALLS = 10;        // Minimum to be efficient
    const REST_PERIOD_DAYS = 21;       // 3 weeks rest period
    
    public function handle()
    {
        $this->info('Starting DID Health Monitoring...');
        $this->info('Time: ' . Carbon::now('America/New_York')->format('Y-m-d H:i:s T'));
        
        // Step 1: Collect current metrics
        $this->collectDIDMetrics();
        
        // Step 2: Calculate health scores
        $this->calculateHealthScores();
        
        // Step 3: Identify DIDs needing rotation
        $this->identifyRotationCandidates();
        
        // Step 4: Check for alerts
        $this->checkAlerts();
        
        // Step 5: Generate summary
        $this->generateSummary();
        
        $this->info('DID Health Monitoring Complete!');
    }
    
    /**
     * Collect metrics from ViciDial for each DID
     */
    private function collectDIDMetrics()
    {
        $this->info('Collecting DID metrics from ViciDial...');
        
        try {
            // Get today's call data grouped by outbound CID
            // Note: This would connect to ViciDial database
            $query = "
                SELECT 
                    campaign_id,
                    phone_number as did_number,
                    COUNT(*) as total_calls,
                    SUM(CASE WHEN status IN ('A', 'XFER', 'XFERA') THEN 1 ELSE 0 END) as answered_calls,
                    SUM(CASE WHEN status IN ('NA') THEN 1 ELSE 0 END) as no_answer_calls,
                    SUM(CASE WHEN status IN ('AM', 'AL') THEN 1 ELSE 0 END) as voicemail_calls,
                    SUM(CASE WHEN status IN ('B') THEN 1 ELSE 0 END) as busy_calls,
                    SUM(CASE WHEN status IN ('DROP', 'PDROP') THEN 1 ELSE 0 END) as dropped_calls,
                    AVG(length_in_sec) as avg_talk_time,
                    MIN(call_date) as first_call,
                    MAX(call_date) as last_call
                FROM vicidial_log
                WHERE DATE(call_date) = CURRENT_DATE
                    AND campaign_id IN ('AUTODIAL', 'AUTO2')
                GROUP BY campaign_id, phone_number
            ";
            
            // For now, create mock data for demonstration
            $mockDIDs = $this->generateMockData();
            
            foreach ($mockDIDs as $did) {
                $this->updateDIDHealth($did);
            }
            
            $this->info('✓ Collected metrics for ' . count($mockDIDs) . ' DIDs');
            
        } catch (\Exception $e) {
            $this->error('Failed to collect DID metrics: ' . $e->getMessage());
            Log::error('DID metric collection failed', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Update DID health record
     */
    private function updateDIDHealth($didData)
    {
        // Calculate answer rate
        $answerRate = $didData['total_calls'] > 0 
            ? ($didData['answered_calls'] / $didData['total_calls']) * 100 
            : 0;
        
        // Determine spam risk level
        $spamRisk = 'LOW';
        if ($answerRate < self::CRITICAL_ANSWER_RATE) {
            $spamRisk = 'CRITICAL';
        } elseif ($answerRate < self::WARNING_ANSWER_RATE) {
            $spamRisk = 'HIGH';
        } elseif ($answerRate < self::HEALTHY_ANSWER_RATE) {
            $spamRisk = 'MEDIUM';
        }
        
        // Store in database
        DB::table('did_health_monitor')->updateOrInsert(
            [
                'did_number' => $didData['did_number'],
                'date' => Carbon::today()
            ],
            [
                'campaign_id' => $didData['campaign_id'],
                'area_code' => substr($didData['did_number'], 0, 3),
                'state' => $this->getStateFromAreaCode(substr($didData['did_number'], 0, 3)),
                'total_calls' => $didData['total_calls'],
                'answered_calls' => $didData['answered_calls'],
                'answer_rate' => $answerRate,
                'avg_talk_time' => $didData['avg_talk_time'] ?? 0,
                'spam_risk_level' => $spamRisk,
                'last_spam_check' => now(),
                'updated_at' => now()
            ]
        );
    }
    
    /**
     * Calculate health scores for all DIDs
     */
    private function calculateHealthScores()
    {
        $this->info('Calculating health scores...');
        
        $dids = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->where('status', 'ACTIVE')
            ->get();
        
        foreach ($dids as $did) {
            $score = 100;
            
            // Answer rate impact (40 points)
            if ($did->answer_rate < 5) {
                $score -= 40;
            } elseif ($did->answer_rate < 10) {
                $score -= 30;
            } elseif ($did->answer_rate < 15) {
                $score -= 20;
            } elseif ($did->answer_rate < 20) {
                $score -= 10;
            }
            
            // Daily volume impact (20 points)
            if ($did->total_calls > 50) {
                $score -= 20;
            } elseif ($did->total_calls > 45) {
                $score -= 10;
            } elseif ($did->total_calls < 10) {
                $score -= 5;
            }
            
            // Update health score
            DB::table('did_health_monitor')
                ->where('id', $did->id)
                ->update(['health_score' => max(0, $score)]);
        }
        
        $this->info('✓ Health scores calculated');
    }
    
    /**
     * Identify DIDs that need rotation
     */
    private function identifyRotationCandidates()
    {
        $this->info('Identifying DIDs needing rotation...');
        
        // Find DIDs with critical health scores
        $criticalDIDs = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->where(function($query) {
                $query->where('health_score', '<', 30)
                      ->orWhere('answer_rate', '<', self::CRITICAL_ANSWER_RATE)
                      ->orWhere('total_calls', '>', self::MAX_DAILY_CALLS);
            })
            ->where('status', 'ACTIVE')
            ->get();
        
        foreach ($criticalDIDs as $did) {
            $this->line("  🔴 DID {$did->did_number} needs immediate rotation");
            $this->line("     Health: {$did->health_score}, Answer Rate: {$did->answer_rate}%");
            
            // Mark for rotation
            DB::table('did_health_monitor')
                ->where('id', $did->id)
                ->update([
                    'status' => 'NEEDS_REST',
                    'rest_start_date' => Carbon::tomorrow(),
                    'rest_end_date' => Carbon::tomorrow()->addDays(self::REST_PERIOD_DAYS)
                ]);
        }
        
        // Check for DIDs ready to return from rest
        $restedDIDs = DB::table('did_health_monitor')
            ->where('status', 'RESTING')
            ->where('rest_end_date', '<=', Carbon::today())
            ->get();
        
        foreach ($restedDIDs as $did) {
            $this->line("  🟢 DID {$did->did_number} ready to return to service");
            
            DB::table('did_health_monitor')
                ->where('id', $did->id)
                ->update([
                    'status' => 'ACTIVE',
                    'health_score' => 100,
                    'spam_risk_level' => 'LOW'
                ]);
        }
        
        $this->info('✓ Rotation candidates identified');
    }
    
    /**
     * Check for alerts
     */
    private function checkAlerts()
    {
        $this->info('Checking for alerts...');
        
        $alerts = [];
        
        // Critical alerts
        $criticalDIDs = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->where('spam_risk_level', 'CRITICAL')
            ->where('status', 'ACTIVE')
            ->count();
        
        if ($criticalDIDs > 0) {
            $alerts[] = "🚨 CRITICAL: {$criticalDIDs} DIDs likely marked as spam!";
        }
        
        // Coverage alerts
        $areaCoverage = DB::table('did_health_monitor')
            ->select('area_code', DB::raw('COUNT(*) as active_count'))
            ->where('status', 'ACTIVE')
            ->where('date', Carbon::today())
            ->groupBy('area_code')
            ->having('active_count', '<', 3)
            ->get();
        
        foreach ($areaCoverage as $area) {
            $alerts[] = "⚠️ WARNING: Area code {$area->area_code} has only {$area->active_count} active DIDs";
        }
        
        // Display alerts
        if (!empty($alerts)) {
            $this->error('═══ ALERTS ═══');
            foreach ($alerts as $alert) {
                $this->line($alert);
            }
        } else {
            $this->info('✓ No alerts');
        }
        
        // Store alerts for UI display
        foreach ($alerts as $alert) {
            DB::table('system_alerts')->insert([
                'type' => 'DID_HEALTH',
                'severity' => strpos($alert, 'CRITICAL') !== false ? 'critical' : 'warning',
                'message' => $alert,
                'created_at' => now()
            ]);
        }
    }
    
    /**
     * Generate summary report
     */
    private function generateSummary()
    {
        $this->info("\n═══ DID HEALTH SUMMARY ═══");
        
        $stats = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->selectRaw('
                COUNT(*) as total_dids,
                COUNT(CASE WHEN status = "ACTIVE" THEN 1 END) as active_dids,
                COUNT(CASE WHEN status = "RESTING" THEN 1 END) as resting_dids,
                AVG(answer_rate) as avg_answer_rate,
                AVG(health_score) as avg_health_score,
                SUM(total_calls) as total_calls_today,
                COUNT(CASE WHEN spam_risk_level = "CRITICAL" THEN 1 END) as spam_dids
            ')
            ->first();
        
        $this->line("Total DIDs: {$stats->total_dids}");
        $this->line("Active: {$stats->active_dids} | Resting: {$stats->resting_dids}");
        $this->line("Average Answer Rate: " . number_format($stats->avg_answer_rate, 1) . "%");
        $this->line("Average Health Score: " . number_format($stats->avg_health_score, 0));
        $this->line("Total Calls Today: " . number_format($stats->total_calls_today));
        
        if ($stats->spam_dids > 0) {
            $this->error("⚠️ {$stats->spam_dids} DIDs likely marked as spam!");
        }
        
        // Top performing DIDs
        $topDIDs = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->where('status', 'ACTIVE')
            ->orderBy('answer_rate', 'desc')
            ->limit(3)
            ->get(['did_number', 'answer_rate', 'total_calls']);
        
        $this->info("\n🏆 Top Performing DIDs:");
        foreach ($topDIDs as $did) {
            $this->line("  {$did->did_number}: {$did->answer_rate}% ({$did->total_calls} calls)");
        }
        
        // Worst performing DIDs
        $worstDIDs = DB::table('did_health_monitor')
            ->where('date', Carbon::today())
            ->where('status', 'ACTIVE')
            ->orderBy('answer_rate', 'asc')
            ->limit(3)
            ->get(['did_number', 'answer_rate', 'total_calls']);
        
        $this->error("\n⚠️ Worst Performing DIDs:");
        foreach ($worstDIDs as $did) {
            $this->line("  {$did->did_number}: {$did->answer_rate}% ({$did->total_calls} calls)");
        }
    }
    
    /**
     * Generate mock data for testing
     */
    private function generateMockData()
    {
        $areaCodes = ['305', '786', '954', '561', '407', '321', '813', '727'];
        $mockDIDs = [];
        
        foreach ($areaCodes as $areaCode) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                $totalCalls = rand(20, 60);
                $answerRate = rand(5, 25) / 100;
                
                $mockDIDs[] = [
                    'did_number' => $areaCode . rand(1000000, 9999999),
                    'campaign_id' => rand(0, 1) ? 'AUTODIAL' : 'AUTO2',
                    'total_calls' => $totalCalls,
                    'answered_calls' => round($totalCalls * $answerRate),
                    'avg_talk_time' => rand(30, 180)
                ];
            }
        }
        
        return $mockDIDs;
    }
    
    /**
     * Get state from area code
     */
    private function getStateFromAreaCode($areaCode)
    {
        $areaCodes = [
            '305' => 'FL', '786' => 'FL', '954' => 'FL', '561' => 'FL',
            '407' => 'FL', '321' => 'FL', '813' => 'FL', '727' => 'FL',
            '212' => 'NY', '718' => 'NY', '917' => 'NY', '347' => 'NY',
            '310' => 'CA', '323' => 'CA', '415' => 'CA', '510' => 'CA',
            // Add more as needed
        ];
        
        return $areaCodes[$areaCode] ?? 'XX';
    }
}









