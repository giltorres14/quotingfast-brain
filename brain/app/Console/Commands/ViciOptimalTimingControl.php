<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ViciOptimalTimingControl extends Command
{
    protected $signature = 'vici:optimal-timing';
    protected $description = 'Control ViciDial lead calling at optimal times';

    public function handle()
    {
        $now = Carbon::now('America/New_York');
        $hour = $now->hour;
        $minute = $now->minute;
        
        $this->info("Running optimal timing control at {$now->format('H:i')} EST");
        
        // Get Vici DB connection
        $viciDb = DB::connection('vicidial');
        
        // STRATEGY: Control lead availability by resetting the "called_since_last_reset" flag
        // This makes leads available to the hopper at specific times
        
        // =======================
        // LIST 150 - GOLDEN HOUR
        // =======================
        // These leads should be called immediately and frequently in first 4 hours
        // No special timing needed - they're always available until 5 calls reached
        
        $viciDb->statement("
            UPDATE vicidial_list 
            SET called_since_last_reset = 'N'
            WHERE list_id = 150 
            AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
            AND call_count < 5
        ");
        
        // =======================
        // LIST 151 - DAY 2 MOMENTUM
        // =======================
        // Only make available at 10 AM and 2 PM
        
        if (($hour == 10 && $minute < 5) || ($hour == 14 && $minute < 5)) {
            $this->info("Activating List 151 for optimal calling window");
            
            $viciDb->statement("
                UPDATE vicidial_list 
                SET called_since_last_reset = 'N'
                WHERE list_id = 151 
                AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
                AND call_count < 7
                AND (last_local_call_time < CURDATE() 
                     OR last_local_call_time IS NULL)
            ");
            
            // After the optimal window, set them back to Y
        } elseif (($hour == 11 && $minute < 5) || ($hour == 15 && $minute < 5)) {
            $this->info("Deactivating List 151 after optimal window");
            
            $viciDb->statement("
                UPDATE vicidial_list 
                SET called_since_last_reset = 'Y'
                WHERE list_id = 151
            ");
        }
        
        // =======================
        // LIST 152 - DAYS 3-5 PERSISTENCE
        // =======================
        // One call per day between 10-12 or 2-4
        
        if ($hour == 10 || $hour == 11 || $hour == 14 || $hour == 15) {
            if ($minute < 5) { // Only run once per hour
                $this->info("Checking List 152 for daily call");
                
                // Only reset if they haven't been called today
                $viciDb->statement("
                    UPDATE vicidial_list 
                    SET called_since_last_reset = 'N'
                    WHERE list_id = 152 
                    AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
                    AND call_count < 10
                    AND (
                        last_local_call_time < CURDATE() 
                        OR last_local_call_time IS NULL
                        OR TIMESTAMPDIFF(HOUR, last_local_call_time, NOW()) >= 24
                    )
                    LIMIT 100
                ");
            }
        }
        
        // Close the window after optimal times
        if ($hour == 12 || $hour == 16) {
            if ($minute < 5) {
                $this->info("Closing List 152 calling window");
                
                $viciDb->statement("
                    UPDATE vicidial_list 
                    SET called_since_last_reset = 'Y'
                    WHERE list_id = 152
                    AND call_count > 0
                ");
            }
        }
        
        // =======================
        // LIST 153 - DAYS 6-10 FINAL
        // =======================
        // Only 2 calls total, spaced 3 days apart
        
        if (($hour == 11 && $minute < 5) || ($hour == 14 && $minute < 5)) {
            $this->info("Checking List 153 for final attempts");
            
            $viciDb->statement("
                UPDATE vicidial_list 
                SET called_since_last_reset = 'N'
                WHERE list_id = 153 
                AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
                AND call_count < 12
                AND (
                    last_local_call_time IS NULL 
                    OR TIMESTAMPDIFF(DAY, last_local_call_time, NOW()) >= 3
                )
                LIMIT 50
            ");
        }
        
        // =======================
        // MOVE LEADS BETWEEN LISTS
        // =======================
        // This handles progression through the lists
        
        // Move from 150 to 151 after 5 calls
        // CORRECTED: Include all terminal dispositions
        $moved150to151 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 151,
                called_since_last_reset = 'Y'
            WHERE list_id = 150 
            AND call_count >= 5
            AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'DC', 'ADC', 'DNQ')
        ");
        
        if ($moved150to151 > 0) {
            $this->info("Moved {$moved150to151} leads from List 150 to 151");
        }
        
        // Move from 151 to 152 after 7 calls (end of Day 2)
        if ($hour == 17) { // End of day
            $moved151to152 = $viciDb->update("
                UPDATE vicidial_list 
                SET list_id = 152,
                    called_since_last_reset = 'Y'
                WHERE list_id = 151 
                AND call_count >= 7
                AND status NOT IN ('XFER', 'XFERA')
                AND TIMESTAMPDIFF(HOUR, last_local_call_time, NOW()) >= 20
            ");
            
            if ($moved151to152 > 0) {
                $this->info("Moved {$moved151to152} leads from List 151 to 152");
            }
        }
        
        // Move from 152 to 153 after 10 calls (end of Day 5)
        $moved152to153 = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 153,
                called_since_last_reset = 'Y'
            WHERE list_id = 152 
            AND call_count >= 10
            AND status NOT IN ('XFER', 'XFERA')
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 5
        ");
        
        if ($moved152to153 > 0) {
            $this->info("Moved {$moved152to153} leads from List 152 to 153");
        }
        
        // Move NI leads to retargeting list after 30 days
        $movedToNI = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 160,
                called_since_last_reset = 'Y'
            WHERE list_id = 153 
            AND status = 'NI'
            AND call_count >= 12
            AND TIMESTAMPDIFF(DAY, last_local_call_time, NOW()) >= 30
        ");
        
        if ($movedToNI > 0) {
            $this->info("Moved {$movedToNI} NI leads to List 160 for retargeting");
        }
        
        // Archive TCPA expired leads
        $archivedTCPA = $viciDb->update("
            UPDATE vicidial_list 
            SET list_id = 199,
                called_since_last_reset = 'Y'
            WHERE list_id IN (150, 151, 152, 153)
            AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 89
        ");
        
        if ($archivedTCPA > 0) {
            $this->info("Archived {$archivedTCPA} TCPA expired leads to List 199");
        }
        
        $this->info("Optimal timing control completed");
        
        return 0;
    }
}
