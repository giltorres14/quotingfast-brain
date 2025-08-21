<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExecuteViciLeadFlow extends Command
{
    protected $signature = 'vici:execute-lead-flow 
                            {--dry-run : Preview movements without executing}
                            {--movement= : Run specific movement only (e.g., 101_to_102)}';

    protected $description = 'Execute Vici lead flow movements via SQL queries';

    private $viciProxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

    /**
     * SQL queries for each movement
     */
    private function getMovementQueries()
    {
        return [
            '101_to_102' => [
                'description' => 'Move from 101 to 102 after first call',
                'frequency' => '15_minutes',
                'sql' => "
                    -- Move leads from List 101 to 102 after first call
                    UPDATE vicidial_list vl
                    INNER JOIN (
                        SELECT DISTINCT vl.lead_id
                        FROM vicidial_list vl
                        INNER JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id
                        WHERE vl.list_id = 101
                        AND vl.status NOT IN ('SALE', 'DNC', 'NI', 'CALLBK')
                        AND vdl.call_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                        GROUP BY vl.lead_id
                        HAVING COUNT(vdl.uniqueid) >= 1
                    ) qualified ON vl.lead_id = qualified.lead_id
                    SET vl.list_id = 102,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                "
            ],

            '102_to_103' => [
                'description' => 'Move from 102 to 103 after 3 NA attempts',
                'frequency' => 'hourly',
                'sql' => "
                    -- Move leads from List 102 to 103 after 3 NA attempts
                    UPDATE vicidial_list vl
                    INNER JOIN (
                        SELECT vl.lead_id
                        FROM vicidial_list vl
                        INNER JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id
                        WHERE vl.list_id = 102
                        AND vl.status NOT IN ('SALE', 'DNC', 'NI', 'CALLBK')
                        AND DAYOFWEEK(NOW()) BETWEEN 2 AND 6  -- Monday to Friday
                        GROUP BY vl.lead_id
                        HAVING COUNT(CASE WHEN vdl.status = 'NA' THEN 1 END) >= 3
                    ) qualified ON vl.lead_id = qualified.lead_id
                    SET vl.list_id = 103,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                "
            ],

            '103_to_104' => [
                'description' => 'Move from 103 to 104 after voicemail left',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 103 to 104 after 1 day (VM left)
                    UPDATE vicidial_list vl
                    SET vl.list_id = 104,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 103
                    AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 1 DAY)
                "
            ],

            '104_to_105' => [
                'description' => 'Move from 104 to 105 after 12 calls (3 days)',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 104 to 105 after 12 calls
                    UPDATE vicidial_list vl
                    INNER JOIN (
                        SELECT vl.lead_id
                        FROM vicidial_list vl
                        INNER JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id
                        WHERE vl.list_id = 104
                        AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                        GROUP BY vl.lead_id
                        HAVING COUNT(vdl.uniqueid) >= 12
                    ) qualified ON vl.lead_id = qualified.lead_id
                    SET vl.list_id = 105,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                "
            ],

            '105_to_106' => [
                'description' => 'Move from 105 to 106 after second VM',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 105 to 106 after 3 days (second VM)
                    UPDATE vicidial_list vl
                    SET vl.list_id = 106,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 105
                    AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 3 DAY)
                "
            ],

            '106_to_107' => [
                'description' => 'Move from 106 to 107 for phase 2',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 106 to 107 after 4 days
                    UPDATE vicidial_list vl
                    SET vl.list_id = 107,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 106
                    AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 4 DAY)
                "
            ],

            '107_to_108' => [
                'description' => 'Move from 107 to 108 for cooldown',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 107 to 108 after 7 days
                    UPDATE vicidial_list vl
                    SET vl.list_id = 108,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 107
                    AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                "
            ],

            '108_to_110' => [
                'description' => 'Move from 108 to 110 for final attempts',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads from List 108 to 110 after 7 days
                    UPDATE vicidial_list vl
                    SET vl.list_id = 110,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 108
                    AND vl.status NOT IN ('SALE', 'DNC', 'NI')
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                "
            ],

            'tcpa_compliance' => [
                'description' => 'Move TCPA expired leads to List 199',
                'frequency' => 'daily',
                'sql' => "
                    -- Move leads older than 89 days to TCPA expired list
                    UPDATE vicidial_list vl
                    SET vl.list_id = 199,
                        vl.status = 'TCPAEXP',
                        vl.modify_date = NOW()
                    WHERE vl.list_id BETWEEN 101 AND 120
                    AND vl.entry_date <= DATE_SUB(NOW(), INTERVAL 89 DAY)
                "
            ],

            'ni_to_112' => [
                'description' => 'Move NI (Not Interested) leads to List 112 for retargeting',
                'frequency' => 'hourly',
                'sql' => "
                    -- Move NI leads to List 112 for retargeting
                    UPDATE vicidial_list vl
                    SET vl.list_id = 112,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.status = 'NI'
                    AND vl.list_id BETWEEN 101 AND 110
                    AND vl.modify_date <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                "
            ],

            'aged_to_120' => [
                'description' => 'Move aged but valid leads to List 120 for Auto2 training',
                'frequency' => 'daily',
                'sql' => "
                    -- Move aged but valid leads to List 120 for training
                    UPDATE vicidial_list vl
                    SET vl.list_id = 120,
                        vl.status = 'NEW',
                        vl.called_since_last_reset = 'N',
                        vl.modify_date = NOW()
                    WHERE vl.list_id = 110
                    AND vl.entry_date BETWEEN DATE_SUB(NOW(), INTERVAL 89 DAY) 
                                          AND DATE_SUB(NOW(), INTERVAL 60 DAY)
                "
            ]
        ];
    }

    public function handle()
    {
        $startTime = microtime(true);
        $specificMovement = $this->option('movement');
        $dryRun = $this->option('dry-run');

        $this->info("🔄 VICI LEAD FLOW EXECUTION - " . now()->format('Y-m-d H:i:s'));
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - Showing SQL queries only");
        }

        $movements = $this->getMovementQueries();
        $totalMoved = 0;

        foreach ($movements as $key => $movement) {
            // Skip if specific movement requested and this isn't it
            if ($specificMovement && $key !== $specificMovement) {
                continue;
            }

            // Check if this movement should run based on frequency
            if (!$this->shouldRun($movement['frequency'])) {
                continue;
            }

            $this->info("\n📋 Processing: " . $movement['description']);
            
            if ($dryRun) {
                $this->line("SQL Query:");
                $this->line(str_replace("\n", "\n  ", trim($movement['sql'])));
                continue;
            }

            // Execute via Vici proxy
            try {
                $response = Http::timeout(30)->post($this->viciProxyUrl, [
                    'query' => $movement['sql']
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $affected = $result['affected_rows'] ?? 0;
                    $totalMoved += $affected;
                    
                    $this->info("  ✅ Moved {$affected} leads");
                    
                    // Log the movement
                    Log::info("Vici lead flow: {$key}", [
                        'description' => $movement['description'],
                        'affected' => $affected
                    ]);
                } else {
                    $this->error("  ❌ Failed to execute: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                Log::error("Vici lead flow error: {$key}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Also update Brain database with list changes
        if (!$dryRun) {
            $this->syncBrainDatabase();
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->info("\n✅ LEAD FLOW COMPLETE");
        $this->info("  Total leads moved: $totalMoved");
        $this->info("  Execution time: {$executionTime}s");

        return Command::SUCCESS;
    }

    /**
     * Determine if movement should run based on frequency
     */
    private function shouldRun($frequency)
    {
        $minute = (int) date('i');
        $hour = (int) date('G');

        switch ($frequency) {
            case '15_minutes':
                return $minute % 15 == 0;
            case 'hourly':
                return $minute == 0;
            case 'daily':
                return $hour == 0 && $minute == 0;
            default:
                return true;
        }
    }

    /**
     * Sync list changes back to Brain database
     */
    private function syncBrainDatabase()
    {
        $this->info("\n🔄 Syncing list changes to Brain database...");

        try {
            // Get list assignments from Vici
            $response = Http::timeout(30)->post($this->viciProxyUrl, [
                'query' => "
                    SELECT vendor_lead_code, list_id, status
                    FROM vicidial_list
                    WHERE vendor_lead_code IS NOT NULL
                    AND vendor_lead_code != ''
                    AND list_id BETWEEN 101 AND 199
                "
            ]);

            if ($response->successful()) {
                $viciLeads = $response->json()['data'] ?? [];
                $updated = 0;

                foreach ($viciLeads as $viciLead) {
                    // Update Brain database
                    $affected = DB::table('leads')
                        ->where('id', $viciLead['vendor_lead_code'])
                        ->update([
                            'vici_list_id' => $viciLead['list_id'],
                            'status' => $viciLead['status'],
                            'updated_at' => now()
                        ]);
                    
                    $updated += $affected;
                }

                $this->info("  ✅ Synced {$updated} leads to Brain database");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Sync error: " . $e->getMessage());
        }
    }
}




