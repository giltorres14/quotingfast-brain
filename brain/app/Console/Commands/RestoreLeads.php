<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\AllstateTestLog;
use App\Models\LeadQueue;
use Illuminate\Support\Facades\DB;

class RestoreLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:restore {backup? : Backup file name (optional, uses latest if not specified)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore leads from a backup file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $backupDir = storage_path('app/backups');
        
        if (!file_exists($backupDir)) {
            $this->error('No backup directory found.');
            return 1;
        }
        
        // Get backup file
        $backupFile = $this->argument('backup');
        
        if (!$backupFile) {
            // Get latest backup
            $files = glob($backupDir . '/leads_backup_*.json');
            if (empty($files)) {
                $this->error('No backup files found.');
                return 1;
            }
            
            // Sort by modification time and get the latest
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backupFile = basename($files[0]);
            $this->info("Using latest backup: {$backupFile}");
        }
        
        $backupPath = $backupDir . '/' . $backupFile;
        
        if (!file_exists($backupPath)) {
            $this->error("Backup file not found: {$backupFile}");
            return 1;
        }
        
        // Load backup data
        $this->info("Loading backup from: {$backupFile}");
        $backupData = json_decode(file_get_contents($backupPath), true);
        
        if (!$backupData) {
            $this->error('Invalid backup file format.');
            return 1;
        }
        
        $this->info('Backup details:');
        $this->info("  • Created: {$backupData['timestamp']}");
        $this->info("  • Leads: " . count($backupData['leads'] ?? []));
        $this->info("  • Test Logs: " . count($backupData['test_logs'] ?? []));
        $this->info("  • Queue Items: " . count($backupData['queue'] ?? []));
        $this->info('');
        
        if (!$this->confirm('Do you want to restore this backup? This will REPLACE current data!')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $this->info('Starting restoration...');
        
        try {
            DB::beginTransaction();
            
            // Clear existing data first
            $this->info('1. Clearing existing data...');
            AllstateTestLog::query()->delete();
            try {
                LeadQueue::query()->delete();
            } catch (\Exception $e) {
                // Table might not exist
            }
            Lead::query()->delete();
            
            // Restore leads
            if (!empty($backupData['leads'])) {
                $this->info('2. Restoring leads...');
                foreach ($backupData['leads'] as $leadData) {
                    // Remove timestamps that Laravel will handle
                    unset($leadData['created_at']);
                    unset($leadData['updated_at']);
                    
                    Lead::create($leadData);
                }
            }
            
            // Restore test logs
            if (!empty($backupData['test_logs'])) {
                $this->info('3. Restoring test logs...');
                foreach ($backupData['test_logs'] as $logData) {
                    unset($logData['created_at']);
                    unset($logData['updated_at']);
                    
                    AllstateTestLog::create($logData);
                }
            }
            
            // Restore queue items
            if (!empty($backupData['queue'])) {
                try {
                    $this->info('4. Restoring queue items...');
                    foreach ($backupData['queue'] as $queueData) {
                        unset($queueData['created_at']);
                        unset($queueData['updated_at']);
                        
                        LeadQueue::create($queueData);
                    }
                } catch (\Exception $e) {
                    $this->warn('Could not restore queue items: ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            $this->info('');
            $this->info('✅ Restoration completed successfully!');
            $this->info('');
            $this->info('Restored counts:');
            $this->info('  • Leads: ' . Lead::count());
            $this->info('  • Test Logs: ' . AllstateTestLog::count());
            
            try {
                $this->info('  • Queue Items: ' . LeadQueue::count());
            } catch (\Exception $e) {
                // Table might not exist
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Restoration failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}


