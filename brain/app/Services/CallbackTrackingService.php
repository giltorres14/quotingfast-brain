<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CallbackTrackingService
{
    /**
     * Track callback effectiveness for missed calls and voicemails
     */
    public function getCallbackStats()
    {
        // Get missed call callback rate
        $missedCallStats = $this->getMissedCallCallbackRate();
        
        // Get voicemail callback rate
        $voicemailStats = $this->getVoicemailCallbackRate();
        
        // Get average callback times
        $timingStats = $this->getCallbackTimingStats();
        
        // Get conversion rates
        $conversionStats = $this->getCallbackConversionRates();
        
        return array_merge(
            $missedCallStats,
            $voicemailStats,
            $timingStats,
            $conversionStats
        );
    }
    
    /**
     * Calculate callback rate for missed calls (no voicemail left)
     */
    private function getMissedCallCallbackRate()
    {
        $results = DB::select("
            SELECT 
                COUNT(DISTINCT lead_id) as missed_call_count,
                SUM(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) as missed_callbacks,
                ROUND(AVG(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) * 100, 1) as missed_call_callback_rate
            FROM (
                SELECT 
                    vl.lead_id,
                    vl.phone_number,
                    vl.call_date as missed_call_time,
                    -- Check if lead called back within 48 hours
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM vicidial_closer_log vcl
                            WHERE vcl.phone_number = vl.phone_number
                            AND vcl.call_date > vl.call_date
                            AND vcl.call_date <= DATE_ADD(vl.call_date, INTERVAL 48 HOUR)
                            AND vcl.status IN ('XFER', 'SALE', 'CALLBK')
                        ) THEN 1 
                        ELSE 0 
                    END as callback_received
                FROM vicidial_log vl
                WHERE vl.status IN ('NA', 'B', 'CANCEL')
                    AND vl.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    -- Exclude calls where voicemail was left
                    AND NOT EXISTS (
                        SELECT 1 FROM vicidial_log vm
                        WHERE vm.lead_id = vl.lead_id
                        AND vm.call_date = vl.call_date
                        AND vm.status IN ('VM', 'VMREC', 'VMMSG')
                    )
            ) missed_calls
        ");
        
        $result = $results[0] ?? null;
        
        return [
            'missed_call_count' => number_format($result->missed_call_count ?? 0),
            'missed_callbacks' => number_format($result->missed_callbacks ?? 0),
            'missed_call_callbacks' => round($result->missed_call_callback_rate ?? 0, 1)
        ];
    }
    
    /**
     * Calculate callback rate after voicemail
     */
    private function getVoicemailCallbackRate()
    {
        $results = DB::select("
            SELECT 
                COUNT(DISTINCT lead_id) as voicemails_left,
                SUM(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) as vm_callbacks,
                ROUND(AVG(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) * 100, 1) as voicemail_callback_rate
            FROM (
                SELECT 
                    vl.lead_id,
                    vl.phone_number,
                    vl.call_date as vm_time,
                    -- Check if lead called back within 48 hours of VM
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM vicidial_closer_log vcl
                            WHERE vcl.phone_number = vl.phone_number
                            AND vcl.call_date > vl.call_date
                            AND vcl.call_date <= DATE_ADD(vl.call_date, INTERVAL 48 HOUR)
                            AND vcl.status IN ('XFER', 'SALE', 'CALLBK')
                        ) THEN 1 
                        ELSE 0 
                    END as callback_received
                FROM vicidial_log vl
                WHERE vl.status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG')
                    AND vl.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ) voicemails
        ");
        
        $result = $results[0] ?? null;
        
        return [
            'voicemails_left' => number_format($result->voicemails_left ?? 0),
            'vm_callbacks' => number_format($result->vm_callbacks ?? 0),
            'voicemail_callbacks' => round($result->voicemail_callback_rate ?? 0, 1)
        ];
    }
    
    /**
     * Get timing statistics for callbacks
     */
    private function getCallbackTimingStats()
    {
        $results = DB::select("
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, vm.call_date, cb.call_date)) as avg_callback_hours,
                MIN(TIMESTAMPDIFF(MINUTE, vm.call_date, cb.call_date)) as fastest_callback_minutes,
                MAX(TIMESTAMPDIFF(HOUR, vm.call_date, cb.call_date)) as slowest_callback_hours
            FROM vicidial_log vm
            INNER JOIN vicidial_closer_log cb ON cb.phone_number = vm.phone_number
            WHERE vm.status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG', 'NA', 'B')
                AND cb.call_date > vm.call_date
                AND cb.call_date <= DATE_ADD(vm.call_date, INTERVAL 48 HOUR)
                AND cb.status IN ('XFER', 'SALE', 'CALLBK')
                AND vm.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $result = $results[0] ?? null;
        
        $avgHours = round($result->avg_callback_hours ?? 0, 1);
        $fastestMin = $result->fastest_callback_minutes ?? 0;
        $slowestHours = $result->slowest_callback_hours ?? 0;
        
        return [
            'avg_callback_time' => $avgHours,
            'fastest_callback' => $fastestMin < 60 ? "{$fastestMin} min" : round($fastestMin/60, 1) . " hrs",
            'slowest_callback' => "{$slowestHours} hrs"
        ];
    }
    
    /**
     * Get conversion rates for callbacks
     */
    private function getCallbackConversionRates()
    {
        // VM callbacks that convert to sales
        $vmConversions = DB::select("
            SELECT 
                COUNT(DISTINCT cb.lead_id) as total_vm_callbacks,
                SUM(CASE WHEN cb.status IN ('SALE', 'XFER', 'XFERA') THEN 1 ELSE 0 END) as vm_callback_sales,
                ROUND(
                    SUM(CASE WHEN cb.status IN ('SALE', 'XFER', 'XFERA') THEN 1 ELSE 0 END) * 100.0 / 
                    NULLIF(COUNT(DISTINCT cb.lead_id), 0), 
                    1
                ) as vm_conversion_rate
            FROM vicidial_log vm
            INNER JOIN vicidial_closer_log cb ON cb.phone_number = vm.phone_number
            WHERE vm.status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG')
                AND cb.call_date > vm.call_date
                AND cb.call_date <= DATE_ADD(vm.call_date, INTERVAL 48 HOUR)
                AND vm.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $result = $vmConversions[0] ?? null;
        
        return [
            'vm_callback_sales' => $result->vm_callback_sales ?? 0,
            'vm_conversion' => round($result->vm_conversion_rate ?? 0, 1)
        ];
    }
    
    /**
     * Track specific callback for A/B testing
     */
    public function trackCallback($leadId, $originalCallType, $callbackTime, $testGroup = null)
    {
        DB::table('callback_tracking')->insert([
            'lead_id' => $leadId,
            'original_call_type' => $originalCallType, // 'missed_call' or 'voicemail'
            'original_call_time' => Carbon::now(),
            'callback_received' => true,
            'callback_time' => $callbackTime,
            'time_to_callback_hours' => Carbon::now()->diffInHours($callbackTime),
            'test_group' => $testGroup,
            'created_at' => now()
        ]);
    }
    
    /**
     * Get A/B test specific callback stats
     */
    public function getABTestCallbackStats()
    {
        return DB::select("
            SELECT 
                test_group,
                original_call_type,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) as callbacks,
                ROUND(AVG(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) * 100, 1) as callback_rate,
                AVG(time_to_callback_hours) as avg_callback_time
            FROM callback_tracking
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY test_group, original_call_type
            ORDER BY test_group, original_call_type
        ");
    }
    
    /**
     * Compare voicemail effectiveness between test groups
     */
    public function compareVoicemailStrategies()
    {
        return [
            'test_a' => [
                'strategy' => 'Traditional (after 3 NA)',
                'vm_count' => $this->getVMCountForList([103, 105]),
                'callback_rate' => $this->getVMCallbackRateForList([103, 105]),
                'conversion_rate' => $this->getVMConversionRateForList([103, 105])
            ],
            'test_b' => [
                'strategy' => 'Strategic (end of golden hour)',
                'vm_count' => $this->getVMCountForList([205]),
                'callback_rate' => $this->getVMCallbackRateForList([205]),
                'conversion_rate' => $this->getVMConversionRateForList([205])
            ]
        ];
    }
    
    private function getVMCountForList($listIds)
    {
        $result = DB::selectOne("
            SELECT COUNT(*) as count
            FROM vicidial_log
            WHERE list_id IN (" . implode(',', $listIds) . ")
            AND status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG')
            AND call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return $result->count ?? 0;
    }
    
    private function getVMCallbackRateForList($listIds)
    {
        $result = DB::selectOne("
            SELECT 
                ROUND(AVG(CASE WHEN callback_received = 1 THEN 1 ELSE 0 END) * 100, 1) as rate
            FROM (
                SELECT 
                    vl.lead_id,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM vicidial_closer_log vcl
                            WHERE vcl.phone_number = vl.phone_number
                            AND vcl.call_date > vl.call_date
                            AND vcl.call_date <= DATE_ADD(vl.call_date, INTERVAL 48 HOUR)
                        ) THEN 1 
                        ELSE 0 
                    END as callback_received
                FROM vicidial_log vl
                WHERE vl.list_id IN (" . implode(',', $listIds) . ")
                    AND vl.status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG')
                    AND vl.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ) vm_callbacks
        ");
        
        return $result->rate ?? 0;
    }
    
    private function getVMConversionRateForList($listIds)
    {
        $result = DB::selectOne("
            SELECT 
                ROUND(
                    SUM(CASE WHEN cb.status IN ('SALE', 'XFER') THEN 1 ELSE 0 END) * 100.0 / 
                    NULLIF(COUNT(DISTINCT cb.lead_id), 0), 
                    1
                ) as rate
            FROM vicidial_log vm
            INNER JOIN vicidial_closer_log cb ON cb.phone_number = vm.phone_number
            WHERE vm.list_id IN (" . implode(',', $listIds) . ")
                AND vm.status IN ('VM', 'VMREC', 'VMMSG', 'LVMSG')
                AND cb.call_date > vm.call_date
                AND cb.call_date <= DATE_ADD(vm.call_date, INTERVAL 48 HOUR)
                AND vm.call_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return $result->rate ?? 0;
    }
}
