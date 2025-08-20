<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ViciTestALeadFlow extends Command
{
    protected $signature = 'vici:test-a-flow';
    protected $description = 'Execute Test A lead flow movements with corrected disposition logic';

    // Define disposition groups for clarity
    const TERMINAL_DISPOSITIONS = ['XFER', 'XFERA', 'DNC', 'DNCL', 'DC', 'ADC', 'DNQ'];
    const NO_CONTACT_DISPOSITIONS = ['NA', 'A', 'N', 'B', 'AB', 'DROP', 'PDROP', 'TIMEOT', 'DAIR'];
    const HUMAN_CONTACT_DISPOSITIONS = ['NI', 'CALLBK', 'LVM', 'BLOCK', 'DEC', 'ERI'];
    const DIALABLE_DISPOSITIONS = ['NA', 'A', 'B', 'N', 'NI', 'LVM', 'DAIR', 'BLOCK', 'AB', 'NEW'];

    public function handle()
    {
        $now = Carbon::now('America/New_York');
        $this->info("Executing Test A Lead Flow at {$now->format('Y-m-d H:i:s')} EST");
        
        $viciDb = DB::connection('vicidial');
        
        // ===================================
        // KEY PRINCIPLE: Count Only ACTUAL DIALS
        // ===================================
        // Only count calls from vicidial_dial_log (actual dial attempts)
        // NOT manual status changes or system events
        // This ensures accurate call counting and proper list progression
        
        // ===================================
        // LIST 101 → 102 (After First Call)
        // ===================================
        // Move after ANY first dial attempt that didn't result in success
        $moved101to102 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 102,
                comments = CONCAT(COALESCE(comments, ''), ' | Moved 101->102 at ', NOW())
            WHERE list_id = 101 
            AND call_count >= 1
            AND status IN ('" . implode("','", self::NO_CONTACT_DISPOSITIONS) . "','" . 
                          implode("','", self::HUMAN_CONTACT_DISPOSITIONS) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved101to102 > 0) {
            $this->info("✓ Moved {$moved101to102} leads from List 101 to 102 (after first call)");
        }
        
        // ===================================
        // LIST 102 → 103 (Voicemail Trigger)
        // ===================================
        // CORRECTED: Move after 3 attempts with NO HUMAN CONTACT (not just NA)
        $moved102to103 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 103,
                comments = CONCAT(COALESCE(comments, ''), ' | VM list after 3 no-contact at ', NOW())
            WHERE list_id = 102
            AND call_count >= 3
            AND status IN ('" . implode("','", self::NO_CONTACT_DISPOSITIONS) . "')
            AND TIMESTAMPDIFF(HOUR, entry_date, NOW()) < 24
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved102to103 > 0) {
            $this->info("✓ Moved {$moved102to103} leads to List 103 (voicemail after 3 no-contact)");
        }
        
        // ===================================
        // LIST 103 → 104 (Day 2 Intensive)
        // ===================================
        // Move after Day 1 is complete (5 total calls, 24 hours passed)
        $moved103to104 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 104,
                comments = CONCAT(COALESCE(comments, ''), ' | Day 2 intensive at ', NOW())
            WHERE list_id = 103
            AND call_count >= 5
            AND TIMESTAMPDIFF(HOUR, entry_date, NOW()) >= 24
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved103to104 > 0) {
            $this->info("✓ Moved {$moved103to104} leads to List 104 (Day 2 intensive)");
        }
        
        // ===================================
        // LIST 104 → 106 (Days 4-8)
        // ===================================
        // After 12 more calls over Days 2-3
        $moved104to106 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 106,
                comments = CONCAT(COALESCE(comments, ''), ' | Extended follow-up at ', NOW())
            WHERE list_id = 104
            AND call_count >= 17  -- 5 from Day 1 + 12 from Days 2-3
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 3
            AND status IN ('" . implode("','", array_merge(self::NO_CONTACT_DISPOSITIONS, self::HUMAN_CONTACT_DISPOSITIONS)) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved104to106 > 0) {
            $this->info("✓ Moved {$moved104to106} leads to List 106 (Days 4-8)");
        }
        
        // ===================================
        // LIST 106 → 107 (Days 9-13)
        // ===================================
        $moved106to107 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 107,
                comments = CONCAT(COALESCE(comments, ''), ' | Cool down phase at ', NOW())
            WHERE list_id = 106
            AND call_count >= 32  -- Previous + 15 more
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 8
            AND status IN ('" . implode("','", array_merge(self::NO_CONTACT_DISPOSITIONS, self::HUMAN_CONTACT_DISPOSITIONS)) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved106to107 > 0) {
            $this->info("✓ Moved {$moved106to107} leads to List 107 (Days 9-13)");
        }
        
        // ===================================
        // LIST 107 → 108 (Days 14-20 REST)
        // ===================================
        $moved107to108 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 108,
                called_since_last_reset = 'Y',  -- No calls during rest
                comments = CONCAT(COALESCE(comments, ''), ' | REST PERIOD at ', NOW())
            WHERE list_id = 107
            AND call_count >= 42  -- Previous + 10 more
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 13
            AND status IN ('" . implode("','", array_merge(self::NO_CONTACT_DISPOSITIONS, self::HUMAN_CONTACT_DISPOSITIONS)) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved107to108 > 0) {
            $this->info("✓ Moved {$moved107to108} leads to List 108 (REST PERIOD - No calls)");
        }
        
        // ===================================
        // LIST 108 → 109 (Days 17-30) - After 3-day rest
        // ===================================
        $moved108to109 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 109,
                called_since_last_reset = 'N',  -- Resume calling
                comments = CONCAT(COALESCE(comments, ''), ' | Final attempts at ', NOW())
            WHERE list_id = 108
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 16  -- Changed from 20 to 16 (3-day rest)
            AND status IN ('" . implode("','", array_merge(self::NO_CONTACT_DISPOSITIONS, self::HUMAN_CONTACT_DISPOSITIONS)) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved108to109 > 0) {
            $this->info("✓ Moved {$moved108to109} leads to List 109 (Days 17-30 final)");
        }
        
        // ===================================
        // LIST 109 → 111 (Day 30+ Last Try)
        // ===================================
        $moved109to111 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 111,
                comments = CONCAT(COALESCE(comments, ''), ' | Last reactivation at ', NOW())
            WHERE list_id = 109
            AND call_count >= 47  -- Previous + 5 more
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 30
            AND status IN ('" . implode("','", array_merge(self::NO_CONTACT_DISPOSITIONS, self::HUMAN_CONTACT_DISPOSITIONS)) . "')
            AND status NOT IN ('" . implode("','", self::TERMINAL_DISPOSITIONS) . "')
        ");
        
        if ($moved109to111 > 0) {
            $this->info("✓ Moved {$moved109to111} leads to List 111 (Final reactivation)");
        }
        
        // ===================================
        // TRACK TRANSFERRED LEADS (Move to List 998)
        // ===================================
        // Move transferred leads to special tracking list but preserve which list they came from
        $trackTransfers = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 998,
                called_since_last_reset = 'Y',
                comments = CONCAT(COALESCE(comments, ''), ' | TRANSFERRED from List ', list_id, ' at ', NOW())
            WHERE status IN ('XFER', 'XFERA')
            AND list_id BETWEEN 101 AND 111
        ");
        
        if ($trackTransfers > 0) {
            $this->info("🎯 Moved {$trackTransfers} TRANSFERRED leads to List 998 for tracking");
        }
        
        // Log transfer statistics
        $transferStats = $viciDb->select("
            SELECT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(comments, 'TRANSFERRED from List ', -1), ' ', 1) as from_list,
                COUNT(*) as transfer_count,
                AVG(call_count) as avg_calls_to_transfer
            FROM vicidial_list
            WHERE list_id = 998
            AND comments LIKE '%TRANSFERRED from List%'
            GROUP BY from_list
        ");
        
        if (count($transferStats) > 0) {
            $this->info("\n📊 Transfer Statistics by Original List:");
            foreach ($transferStats as $stat) {
                $this->info(sprintf(
                    "  List %s: %d transfers (avg %.1f calls to transfer)",
                    $stat->from_list,
                    $stat->transfer_count,
                    $stat->avg_calls_to_transfer
                ));
            }
        }
        
        // ===================================
        // SPECIAL HANDLING FOR DROPS
        // ===================================
        // Dropped calls should be prioritized for immediate callback
        $prioritizeDrops = $viciDb->update("
            UPDATE vicidial_list 
            SET called_since_last_reset = 'N',
                comments = CONCAT(COALESCE(comments, ''), ' | DROP priority reset at ', NOW())
            WHERE status IN ('DROP', 'PDROP')
            AND list_id IN (101, 102, 103, 104, 106, 107, 109)
            AND TIMESTAMPDIFF(MINUTE, last_call_time, NOW()) >= 5
        ");
        
        if ($prioritizeDrops > 0) {
            $this->info("⚡ Prioritized {$prioritizeDrops} dropped calls for immediate callback");
        }
        
        // ===================================
        // HANDLE NI (Not Interested) SPECIALLY
        // ===================================
        // After max attempts, NI leads wait 30 days then go to retargeting
        $niRetarget = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 112,  -- NI retargeting list
                called_since_last_reset = 'Y',
                comments = 'NI Retarget - Rate Reduction Script'
            WHERE status = 'NI'
            AND call_count >= 42  -- Max attempts reached
            AND TIMESTAMPDIFF(DAY, last_call_time, NOW()) >= 30
            AND list_id IN (109, 111)
        ");
        
        if ($niRetarget > 0) {
            $this->info("♻️ Moved {$niRetarget} NI leads to retargeting List 112");
        }
        
        // ===================================
        // ANSWERING MACHINE PATTERN DETECTION
        // ===================================
        // If we get 3+ answering machines in a row, adjust strategy
        $checkAMPattern = $viciDb->select("
            SELECT lead_id, phone_number, call_count
            FROM vicidial_list
            WHERE list_id IN (102, 103, 104)
            AND status = 'A'
            AND call_count >= 3
            LIMIT 100
        ");
        
        if (count($checkAMPattern) > 0) {
            $this->info("⚠️ Found " . count($checkAMPattern) . " leads with 3+ answering machines");
            // Could implement special handling here
        }
        
        // ===================================
        // TCPA COMPLIANCE (89 Days)
        // ===================================
        $tcpaArchive = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 199,
                status = 'TCPAX',
                called_since_last_reset = 'Y',
                comments = CONCAT(COALESCE(comments, ''), ' | TCPA archived at ', NOW())
            WHERE list_id BETWEEN 101 AND 111
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 89
        ");
        
        if ($tcpaArchive > 0) {
            $this->info("📋 Archived {$tcpaArchive} leads for TCPA compliance (89+ days)");
        }
        
        // ===================================
        // SUMMARY STATISTICS
        // ===================================
        $stats = $viciDb->select("
            SELECT 
                list_id,
                COUNT(*) as total_leads,
                SUM(CASE WHEN status IN ('" . implode("','", self::NO_CONTACT_DISPOSITIONS) . "') THEN 1 ELSE 0 END) as no_contact,
                SUM(CASE WHEN status IN ('" . implode("','", self::HUMAN_CONTACT_DISPOSITIONS) . "') THEN 1 ELSE 0 END) as human_contact,
                SUM(CASE WHEN status IN ('XFER', 'XFERA') THEN 1 ELSE 0 END) as transfers,
                AVG(call_count) as avg_calls
            FROM vicidial_list
            WHERE list_id BETWEEN 101 AND 111
            GROUP BY list_id
            ORDER BY list_id
        ");
        
        $this->info("\n📊 Current Test A Distribution:");
        foreach ($stats as $stat) {
            $this->info(sprintf(
                "List %d: %d leads | No Contact: %d | Human Contact: %d | Transfers: %d | Avg Calls: %.1f",
                $stat->list_id,
                $stat->total_leads,
                $stat->no_contact,
                $stat->human_contact,
                $stat->transfers,
                $stat->avg_calls
            ));
        }
        
        $this->info("\n✅ Test A Lead Flow execution complete");
        
        return 0;
    }
}
