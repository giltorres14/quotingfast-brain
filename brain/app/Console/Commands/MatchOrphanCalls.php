<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrphanCallLog;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\Log;

class MatchOrphanCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:match-orphan-calls 
                            {--limit=1000 : Number of orphan calls to process}
                            {--dry-run : Preview what would be matched}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match orphan call logs to leads after bulk imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   ORPHAN CALL MATCHING');
        $this->info('========================================');
        
        // Get unmatched orphan calls
        $orphanCount = OrphanCallLog::unmatched()->count();
        $this->info("📞 Found {$orphanCount} unmatched orphan calls");
        
        if ($orphanCount === 0) {
            $this->info('✅ No orphan calls to process!');
            return Command::SUCCESS;
        }
        
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No data will be saved');
        }
        
        // Process orphan calls
        $orphans = OrphanCallLog::unmatched()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
        
        $stats = [
            'processed' => 0,
            'matched' => 0,
            'unmatched' => 0,
            'errors' => 0
        ];
        
        $bar = $this->output->createProgressBar(count($orphans));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        foreach ($orphans as $orphan) {
            $bar->setMessage("Processing call for {$orphan->phone_number}");
            
            try {
                if (!$dryRun) {
                    if ($orphan->tryMatch()) {
                        $stats['matched']++;
                        $this->logMatch($orphan);
                    } else {
                        $stats['unmatched']++;
                    }
                } else {
                    // Dry run - just check if we could match
                    if ($this->checkMatch($orphan)) {
                        $stats['matched']++;
                    } else {
                        $stats['unmatched']++;
                    }
                }
                
                $stats['processed']++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Failed to match orphan call', [
                    'orphan_id' => $orphan->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats, $orphanCount);
        
        // Check if we should run again
        if ($stats['matched'] > 0 && !$dryRun) {
            $remaining = OrphanCallLog::unmatched()->count();
            if ($remaining > 0) {
                $this->info("📌 {$remaining} orphan calls still unmatched");
                $this->info("   Run this command again to process more");
            }
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Check if orphan could be matched (for dry run)
     */
    private function checkMatch(OrphanCallLog $orphan): bool
    {
        // Try phone number
        if ($orphan->phone_number) {
            $phone = preg_replace('/\D/', '', $orphan->phone_number);
            if (strlen($phone) == 10) {
                $lead = Lead::where('phone', $phone)->first();
                if ($lead) {
                    return true;
                }
            }
        }
        
        // Try vendor_lead_code
        if ($orphan->vendor_lead_code) {
            $lead = Lead::where('external_lead_id', $orphan->vendor_lead_code)->first();
            if ($lead) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log successful match
     */
    private function logMatch(OrphanCallLog $orphan): void
    {
        if ($orphan->lead) {
            $this->line("  ✓ Matched call to lead: {$orphan->lead->name} (ID: {$orphan->lead->id})");
        }
    }
    
    /**
     * Display results
     */
    private function displayResults(array $stats, int $totalOrphans): void
    {
        $this->info('========================================');
        $this->info('           MATCHING RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Orphan Calls', number_format($totalOrphans)],
                ['Processed', number_format($stats['processed'])],
                ['Successfully Matched', number_format($stats['matched'])],
                ['Still Unmatched', number_format($stats['unmatched'])],
                ['Errors', number_format($stats['errors'])],
            ]
        );
        
        if ($stats['matched'] > 0) {
            $matchRate = round(($stats['matched'] / $stats['processed']) * 100, 1);
            $this->newLine();
            $this->info("✅ Match Rate: {$matchRate}%");
            $this->info("📊 Call metrics have been updated for matched leads");
        }
        
        if ($stats['unmatched'] > 0) {
            $this->newLine();
            $this->warn("⚠️ {$stats['unmatched']} calls could not be matched");
            $this->warn("   These may be for leads not yet imported");
        }
    }
}



namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrphanCallLog;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\Log;

class MatchOrphanCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:match-orphan-calls 
                            {--limit=1000 : Number of orphan calls to process}
                            {--dry-run : Preview what would be matched}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match orphan call logs to leads after bulk imports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   ORPHAN CALL MATCHING');
        $this->info('========================================');
        
        // Get unmatched orphan calls
        $orphanCount = OrphanCallLog::unmatched()->count();
        $this->info("📞 Found {$orphanCount} unmatched orphan calls");
        
        if ($orphanCount === 0) {
            $this->info('✅ No orphan calls to process!');
            return Command::SUCCESS;
        }
        
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No data will be saved');
        }
        
        // Process orphan calls
        $orphans = OrphanCallLog::unmatched()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
        
        $stats = [
            'processed' => 0,
            'matched' => 0,
            'unmatched' => 0,
            'errors' => 0
        ];
        
        $bar = $this->output->createProgressBar(count($orphans));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        foreach ($orphans as $orphan) {
            $bar->setMessage("Processing call for {$orphan->phone_number}");
            
            try {
                if (!$dryRun) {
                    if ($orphan->tryMatch()) {
                        $stats['matched']++;
                        $this->logMatch($orphan);
                    } else {
                        $stats['unmatched']++;
                    }
                } else {
                    // Dry run - just check if we could match
                    if ($this->checkMatch($orphan)) {
                        $stats['matched']++;
                    } else {
                        $stats['unmatched']++;
                    }
                }
                
                $stats['processed']++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Failed to match orphan call', [
                    'orphan_id' => $orphan->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats, $orphanCount);
        
        // Check if we should run again
        if ($stats['matched'] > 0 && !$dryRun) {
            $remaining = OrphanCallLog::unmatched()->count();
            if ($remaining > 0) {
                $this->info("📌 {$remaining} orphan calls still unmatched");
                $this->info("   Run this command again to process more");
            }
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Check if orphan could be matched (for dry run)
     */
    private function checkMatch(OrphanCallLog $orphan): bool
    {
        // Try phone number
        if ($orphan->phone_number) {
            $phone = preg_replace('/\D/', '', $orphan->phone_number);
            if (strlen($phone) == 10) {
                $lead = Lead::where('phone', $phone)->first();
                if ($lead) {
                    return true;
                }
            }
        }
        
        // Try vendor_lead_code
        if ($orphan->vendor_lead_code) {
            $lead = Lead::where('external_lead_id', $orphan->vendor_lead_code)->first();
            if ($lead) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log successful match
     */
    private function logMatch(OrphanCallLog $orphan): void
    {
        if ($orphan->lead) {
            $this->line("  ✓ Matched call to lead: {$orphan->lead->name} (ID: {$orphan->lead->id})");
        }
    }
    
    /**
     * Display results
     */
    private function displayResults(array $stats, int $totalOrphans): void
    {
        $this->info('========================================');
        $this->info('           MATCHING RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Orphan Calls', number_format($totalOrphans)],
                ['Processed', number_format($stats['processed'])],
                ['Successfully Matched', number_format($stats['matched'])],
                ['Still Unmatched', number_format($stats['unmatched'])],
                ['Errors', number_format($stats['errors'])],
            ]
        );
        
        if ($stats['matched'] > 0) {
            $matchRate = round(($stats['matched'] / $stats['processed']) * 100, 1);
            $this->newLine();
            $this->info("✅ Match Rate: {$matchRate}%");
            $this->info("📊 Call metrics have been updated for matched leads");
        }
        
        if ($stats['unmatched'] > 0) {
            $this->newLine();
            $this->warn("⚠️ {$stats['unmatched']} calls could not be matched");
            $this->warn("   These may be for leads not yet imported");
        }
    }
}


