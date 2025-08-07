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
    protected $signature = 'leads:clear-test {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely clear all test leads from the system (use with caution!)';

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
            
            // Reset auto-increment counters if using MySQL
            if (config('database.default') === 'mysql') {
                $this->info('4. Resetting auto-increment counters...');
                DB::statement('ALTER TABLE leads AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE allstate_test_logs AUTO_INCREMENT = 1');
                
                try {
                    DB::statement('ALTER TABLE lead_queue AUTO_INCREMENT = 1');
                } catch (\Exception $e) {
                    // Table might not exist
                }
            }
            
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
