<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\AllstateTestLog;
use App\Models\LeadQueue;
use Illuminate\Support\Facades\DB;

class ClearTestLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:clear-test {--force : Skip confirmation} {--backup : Create backup before clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[PRODUCTION ONLY] Safely clear all test leads from the system (use with caution!)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->warn('⚠️  WARNING: This will delete ALL leads from the database!');
        $this->info('');
        
        // Show current counts
        $leadCount = Lead::count();
        $testLogCount = AllstateTestLog::count();
        $queueCount = 0;
        
        try {
            $queueCount = LeadQueue::count();
        } catch (\Exception $e) {
            $this->info('Lead queue table not yet created.');
        }
        
        $this->info('Current counts:');
        $this->info("  • Leads: {$leadCount}");
        $this->info("  • Allstate Test Logs: {$testLogCount}");
        $this->info("  • Lead Queue: {$queueCount}");
        $this->info('');
        
        if ($leadCount == 0) {
            $this->info('✅ No leads to clear.');
            return 0;
        }
        
        // Confirm unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all {$leadCount} leads? This cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        // Create backup if requested
        if ($this->option('backup')) {
            $this->info('Creating backup...');
            $backupFile = storage_path('app/backups/leads_backup_' . date('Y-m-d_His') . '.json');
            
            // Ensure backup directory exists
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            $backupData = [
                'timestamp' => now()->toIso8601String(),
                'leads' => Lead::all()->toArray(),
                'test_logs' => AllstateTestLog::all()->toArray(),
            ];
            
            try {
                $backupData['queue'] = LeadQueue::all()->toArray();
            } catch (\Exception $e) {
                $backupData['queue'] = [];
            }
            
            file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
            $this->info("✅ Backup created: {$backupFile}");
        }
        
        $this->info('Starting safe deletion process...');
        
        try {
            DB::beginTransaction();
            
            // Delete in correct order to avoid foreign key issues
            $this->info('1. Clearing Allstate test logs...');
            AllstateTestLog::query()->delete();
            
            // Clear lead queue if table exists
            try {
                $this->info('2. Clearing lead queue...');
                LeadQueue::query()->delete();
            } catch (\Exception $e) {
                $this->info('   (Lead queue table not found, skipping)');
            }
            
            // Clear all leads
            $this->info('3. Clearing all leads...');
            Lead::query()->delete();
            
            // DO NOT reset auto-increment counters - we use timestamp-based IDs
            // Keeping the internal auto-increment as is to avoid any conflicts
            $this->info('4. Keeping ID counters as-is (using timestamp-based external IDs)');
            
            DB::commit();
            
            $this->info('');
            $this->info('✅ Successfully cleared all test data!');
            $this->info('');
            $this->info('Final counts:');
            $this->info('  • Leads: ' . Lead::count());
            $this->info('  • Allstate Test Logs: ' . AllstateTestLog::count());
            
            try {
                $this->info('  • Lead Queue: ' . LeadQueue::count());
            } catch (\Exception $e) {
                // Table might not exist
            }
            
            $this->info('');
            $this->info('🎉 Database is now clean and ready for production testing!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error occurred: ' . $e->getMessage());
            $this->error('Database rolled back - no changes were made.');
            return 1;
        }
        
        return 0;
    }
}
