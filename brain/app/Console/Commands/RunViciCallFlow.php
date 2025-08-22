<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RunViciCallFlow extends Command
{
    protected $signature = 'vici:run-call-flow 
                            {--dry-run : Preview movements without executing}
                            {--list= : Run specific list movement only}';

    protected $description = 'Execute Vici call flow movements between lists';

    /**
     * Define the movement rules
     */
    private $movements = [
        '101_to_102' => [
            'description' => 'Move from List 101 to 102 after first call',
            'from_list' => 101,
            'to_list' => 102,
            'condition' => "calls_today >= 1 AND status IN ('NA','B','AL')",
            'frequency' => 'every_20_minutes'
        ],
        '102_to_103' => [
            'description' => 'Move from List 102 to 103 after 3 NA on workdays',
            'from_list' => 102,
            'to_list' => 103,
            'condition' => "total_calls >= 3 AND last_status = 'NA' AND DAYOFWEEK(NOW()) BETWEEN 2 AND 6",
            'frequency' => 'hourly'
        ],
        '103_to_104' => [
            'description' => 'Move from List 103 to 104 after voicemail left',
            'from_list' => 103,
            'to_list' => 104,
            'condition' => "days_in_list >= 1",
            'frequency' => 'daily'
        ],
        '104_to_105' => [
            'description' => 'Move from List 104 to 105 after 12 calls',
            'from_list' => 104,
            'to_list' => 105,
            'condition' => "total_calls >= 12",
            'frequency' => 'daily'
        ],
        '105_to_106' => [
            'description' => 'Move from List 105 to 106 after second voicemail',
            'from_list' => 105,
            'to_list' => 106,
            'condition' => "days_in_list >= 3",
            'frequency' => 'daily'
        ],
        '106_to_107' => [
            'description' => 'Move from List 106 to 107 for phase 2',
            'from_list' => 106,
            'to_list' => 107,
            'condition' => "days_in_list >= 4",
            'frequency' => 'daily'
        ],
        '107_to_108' => [
            'description' => 'Move from List 107 to 108 for cooldown',
            'from_list' => 107,
            'to_list' => 108,
            'condition' => "days_in_list >= 7",
            'frequency' => 'daily'
        ],
        '108_to_110' => [
            'description' => 'Move from List 108 to 110 for final attempts',
            'from_list' => 108,
            'to_list' => 110,
            'condition' => "days_in_list >= 7",
            'frequency' => 'daily'
        ]
    ];

    public function handle()
    {
        $startTime = microtime(true);
        $specificList = $this->option('list');
        $dryRun = $this->option('dry-run');

        $this->info("🔄 VICI CALL FLOW EXECUTION - " . now()->format('Y-m-d H:i:s'));
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No actual movements will occur");
        }

        $totalMoved = 0;

        foreach ($this->movements as $key => $movement) {
            // Skip if specific list requested and this isn't it
            if ($specificList && $movement['from_list'] != $specificList) {
                continue;
            }

            // Check if this movement should run based on frequency
            if (!$this->shouldRun($movement['frequency'])) {
                continue;
            }

            $this->info("\n📋 Processing: " . $movement['description']);
            
            // Get leads that qualify for movement
            $qualifyingLeads = $this->getQualifyingLeads($movement);
            
            if ($qualifyingLeads->isEmpty()) {
                $this->line("  No leads qualify for movement");
                continue;
            }

            $this->info("  Found " . $qualifyingLeads->count() . " leads to move");

            if (!$dryRun) {
                $moved = $this->moveLeads($qualifyingLeads, $movement['to_list']);
                $totalMoved += $moved;
                $this->info("  ✅ Moved $moved leads to List " . $movement['to_list']);
            } else {
                $this->info("  Would move " . $qualifyingLeads->count() . " leads");
                foreach ($qualifyingLeads->take(5) as $lead) {
                    $this->line("    - Lead ID: {$lead->id}, Phone: {$lead->phone}");
                }
                if ($qualifyingLeads->count() > 5) {
                    $this->line("    ... and " . ($qualifyingLeads->count() - 5) . " more");
                }
            }
        }

        // Check for TCPA violations (leads older than 89 days)
        $this->checkTCPACompliance($dryRun);

        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->info("\n✅ CALL FLOW COMPLETE");
        $this->info("  Total leads moved: $totalMoved");
        $this->info("  Execution time: {$executionTime}s");

        // Log the execution
        Log::info('Vici call flow executed', [
            'moved' => $totalMoved,
            'dry_run' => $dryRun,
            'execution_time' => $executionTime
        ]);

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
            case 'every_20_minutes':
                return $minute % 20 == 0;
            case 'hourly':
                return $minute == 0;
            case 'daily':
                return $hour == 0 && $minute == 0;
            default:
                return true;
        }
    }

    /**
     * Get leads that qualify for movement
     */
    private function getQualifyingLeads($movement)
    {
        // Build the query based on movement conditions
        $query = DB::table('leads')
            ->where('vici_list_id', $movement['from_list'])
            ->whereNotIn('status', ['SOLD', 'DNC', 'NI']) // Never move these statuses
            ->limit(500); // Process in batches

        // Add specific conditions based on the movement type
        switch ($movement['from_list']) {
            case 101:
                // Check if lead has been called today
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('orphan_call_logs')
                        ->whereColumn('orphan_call_logs.phone_number', 'leads.phone')
                        ->whereDate('call_date', Carbon::today());
                });
                break;

            case 102:
            case 103:
                // Check total calls and last status
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('orphan_call_logs')
                        ->whereColumn('orphan_call_logs.phone_number', 'leads.phone')
                        ->where('status', 'NA')
                        ->havingRaw('COUNT(*) >= 3');
                });
                break;

            case 104:
            case 105:
            case 106:
            case 107:
            case 108:
                // Check days in current list
                $daysAgo = match($movement['from_list']) {
                    104 => 1,
                    105 => 3,
                    106 => 4,
                    107 => 7,
                    108 => 7,
                    default => 1
                };
                
                $query->where('updated_at', '<=', Carbon::now()->subDays($daysAgo));
                break;
        }

        return $query->get();
    }

    /**
     * Move leads to new list
     */
    private function moveLeads($leads, $toList)
    {
        $leadIds = $leads->pluck('id')->toArray();
        
        // Update in Brain database
        $updated = DB::table('leads')
            ->whereIn('id', $leadIds)
            ->update([
                'vici_list_id' => $toList,
                'updated_at' => now()
            ]);

        // Log the movement
        foreach ($leads as $lead) {
            Log::info('Lead moved between lists', [
                'lead_id' => $lead->id,
                'phone' => $lead->phone,
                'from_list' => $lead->vici_list_id,
                'to_list' => $toList
            ]);
        }

        // TODO: Also update in Vici database via API
        // This would require calling Vici API to move the leads

        return $updated;
    }

    /**
     * Check for TCPA violations
     */
    private function checkTCPACompliance($dryRun)
    {
        $tcpaViolations = DB::table('leads')
            ->whereNotNull('vici_list_id')
            ->where('vici_list_id', '!=', 199) // Not already in TCPA expired list
            ->where('created_at', '<=', Carbon::now()->subDays(89))
            ->count();

        if ($tcpaViolations > 0) {
            $this->warn("\n⚠️ TCPA WARNING: $tcpaViolations leads are older than 89 days!");
            
            if (!$dryRun) {
                // Move to List 199 (TCPA expired)
                $moved = DB::table('leads')
                    ->whereNotNull('vici_list_id')
                    ->where('vici_list_id', '!=', 199)
                    ->where('created_at', '<=', Carbon::now()->subDays(89))
                    ->update([
                        'vici_list_id' => 199,
                        'status' => 'TCPA_EXPIRED',
                        'updated_at' => now()
                    ]);
                
                $this->info("  Moved $moved leads to List 199 (TCPA Expired)");
                
                Log::warning('TCPA compliance: Moved expired leads', [
                    'count' => $moved
                ]);
            }
        }
    }
}






