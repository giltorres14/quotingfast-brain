<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\AllstateTestLog;
use App\Models\LeadQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SafeLeadDeletionService
{
    private $backupPath;
    private $verificationCode;
    
    /**
     * ULTRA-SAFE lead deletion with multiple safety checks
     */
    public function safelyClearAllLeads($skipBackup = false)
    {
        // SAFETY CHECK 1: Environment check
        if (app()->environment('production') && !request()->has('confirm_production')) {
            throw new \Exception('Production deletion requires explicit confirmation');
        }
        
        // SAFETY CHECK 2: Generate verification code
        $this->verificationCode = strtoupper(substr(md5(time()), 0, 6));
        Log::warning("⚠️ DELETION VERIFICATION CODE: {$this->verificationCode}");
        
        // SAFETY CHECK 3: Count what we're about to delete
        $counts = $this->getCurrentCounts();
        
        if ($counts['leads'] === 0) {
            return [
                'success' => true,
                'message' => 'No leads to delete',
                'counts' => $counts
            ];
        }
        
        // SAFETY CHECK 4: Create comprehensive backup
        if (!$skipBackup) {
            $this->backupPath = $this->createComprehensiveBackup($counts);
            
            // Verify backup was created and is valid
            if (!$this->verifyBackup($this->backupPath, $counts)) {
                throw new \Exception('Backup verification failed - aborting deletion');
            }
        }
        
        // SAFETY CHECK 5: Use database transaction
        DB::beginTransaction();
        
        try {
            // SAFETY CHECK 6: Delete in correct dependency order
            $deletedCounts = $this->performDeletion();
            
            // SAFETY CHECK 7: Verify deletion counts match
            if ($deletedCounts['leads'] !== $counts['leads']) {
                throw new \Exception(sprintf(
                    'Deletion count mismatch! Expected %d leads, deleted %d',
                    $counts['leads'],
                    $deletedCounts['leads']
                ));
            }
            
            // SAFETY CHECK 8: Final verification - ensure tables are empty
            $finalCounts = $this->getCurrentCounts();
            if ($finalCounts['leads'] > 0) {
                throw new \Exception('Deletion verification failed - some leads remain');
            }
            
            // All checks passed - commit the transaction
            DB::commit();
            
            // Log the successful deletion with full audit trail
            Log::warning('✅ SAFE DELETION COMPLETED', [
                'verification_code' => $this->verificationCode,
                'deleted_counts' => $deletedCounts,
                'backup_file' => $this->backupPath ?? 'none',
                'timestamp' => now()->toIso8601String(),
                'user_ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            return [
                'success' => true,
                'message' => 'All test leads safely deleted',
                'deleted_counts' => $deletedCounts,
                'backup_file' => $this->backupPath ? basename($this->backupPath) : null,
                'verification_code' => $this->verificationCode
            ];
            
        } catch (\Exception $e) {
            // SAFETY CHECK 9: Rollback on ANY error
            DB::rollBack();
            
            Log::error('❌ DELETION FAILED - ROLLED BACK', [
                'error' => $e->getMessage(),
                'verification_code' => $this->verificationCode,
                'backup_file' => $this->backupPath ?? 'none'
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get current counts of all data
     */
    private function getCurrentCounts()
    {
        $counts = [
            'leads' => Lead::count(),
            'test_logs' => AllstateTestLog::count(),
            'queue' => 0
        ];
        
        try {
            $counts['queue'] = LeadQueue::count();
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        return $counts;
    }
    
    /**
     * Create comprehensive backup with multiple formats
     */
    private function createComprehensiveBackup($counts)
    {
        $timestamp = date('Y-m-d_His');
        $backupDir = storage_path('app/backups');
        
        // Ensure backup directory exists with proper permissions
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Create main JSON backup
        $jsonBackupPath = "{$backupDir}/leads_backup_{$timestamp}.json";
        
        $backupData = [
            'metadata' => [
                'timestamp' => now()->toIso8601String(),
                'verification_code' => $this->verificationCode,
                'environment' => app()->environment(),
                'counts' => $counts,
                'database' => config('database.default'),
                'user_ip' => request()->ip()
            ],
            'data' => [
                'leads' => Lead::all()->toArray(),
                'test_logs' => AllstateTestLog::all()->toArray(),
                'queue' => []
            ]
        ];
        
        try {
            $backupData['data']['queue'] = LeadQueue::all()->toArray();
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // Write JSON backup
        $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($jsonBackupPath, $jsonContent);
        
        // Create compressed backup for large datasets
        $compressedPath = "{$backupDir}/leads_backup_{$timestamp}.json.gz";
        $gz = gzopen($compressedPath, 'w9');
        gzwrite($gz, $jsonContent);
        gzclose($gz);
        
        // Create SQL dump if possible (for MySQL/PostgreSQL)
        try {
            $this->createSqlBackup($backupDir, $timestamp);
        } catch (\Exception $e) {
            Log::info('SQL backup skipped: ' . $e->getMessage());
        }
        
        // Log backup creation
        Log::info('📦 COMPREHENSIVE BACKUP CREATED', [
            'json_backup' => $jsonBackupPath,
            'compressed_backup' => $compressedPath,
            'size_bytes' => filesize($jsonBackupPath),
            'compressed_size_bytes' => filesize($compressedPath),
            'counts' => $counts
        ]);
        
        return $jsonBackupPath;
    }
    
    /**
     * Create SQL backup for database-level restoration
     */
    private function createSqlBackup($backupDir, $timestamp)
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        $sqlBackupPath = "{$backupDir}/leads_backup_{$timestamp}.sql";
        
        if (config('database.default') === 'mysql') {
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s leads allstate_test_logs lead_queue > %s 2>/dev/null',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['database'],
                $sqlBackupPath
            );
            exec($command);
        } elseif (config('database.default') === 'pgsql') {
            $command = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -t leads -t allstate_test_logs -t lead_queue > %s 2>/dev/null',
                $dbConfig['password'],
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['username'],
                $dbConfig['database'],
                $sqlBackupPath
            );
            exec($command);
        }
        
        if (file_exists($sqlBackupPath) && filesize($sqlBackupPath) > 0) {
            Log::info('SQL backup created', ['path' => $sqlBackupPath]);
        }
    }
    
    /**
     * Verify backup integrity
     */
    private function verifyBackup($backupPath, $expectedCounts)
    {
        if (!file_exists($backupPath)) {
            return false;
        }
        
        $backupContent = file_get_contents($backupPath);
        $backupData = json_decode($backupContent, true);
        
        if (!$backupData) {
            Log::error('Backup JSON decode failed');
            return false;
        }
        
        // Verify counts match
        $backupLeadCount = count($backupData['data']['leads'] ?? []);
        if ($backupLeadCount !== $expectedCounts['leads']) {
            Log::error('Backup lead count mismatch', [
                'expected' => $expectedCounts['leads'],
                'actual' => $backupLeadCount
            ]);
            return false;
        }
        
        // Verify critical fields are present
        foreach ($backupData['data']['leads'] as $lead) {
            if (!isset($lead['id']) || !isset($lead['external_lead_id'])) {
                Log::error('Backup missing critical lead fields');
                return false;
            }
        }
        
        Log::info('✅ Backup verification passed', [
            'path' => $backupPath,
            'lead_count' => $backupLeadCount
        ]);
        
        return true;
    }
    
    /**
     * Perform the actual deletion
     */
    private function performDeletion()
    {
        $deletedCounts = [
            'test_logs' => 0,
            'queue' => 0,
            'leads' => 0
        ];
        
        // Delete in dependency order
        
        // 1. Delete test logs first (has foreign key to leads)
        $deletedCounts['test_logs'] = AllstateTestLog::query()->delete();
        Log::info("Deleted {$deletedCounts['test_logs']} test logs");
        
        // 2. Delete queue items
        try {
            $deletedCounts['queue'] = LeadQueue::query()->delete();
            Log::info("Deleted {$deletedCounts['queue']} queue items");
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        // 3. Finally delete leads
        $deletedCounts['leads'] = Lead::query()->delete();
        Log::info("Deleted {$deletedCounts['leads']} leads");
        
        return $deletedCounts;
    }
    
    /**
     * Emergency restore from backup
     */
    public function restoreFromBackup($backupFile = null)
    {
        if (!$backupFile) {
            // Find latest backup
            $backupDir = storage_path('app/backups');
            $files = glob($backupDir . '/leads_backup_*.json');
            if (empty($files)) {
                throw new \Exception('No backup files found');
            }
            
            // Sort by modification time and get the latest
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backupFile = $files[0];
        } else {
            $backupFile = storage_path('app/backups/' . $backupFile);
        }
        
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup file not found: {$backupFile}");
        }
        
        $backupData = json_decode(file_get_contents($backupFile), true);
        
        if (!$backupData) {
            throw new \Exception('Invalid backup file format');
        }
        
        DB::beginTransaction();
        
        try {
            // Clear existing data first
            AllstateTestLog::query()->delete();
            try {
                LeadQueue::query()->delete();
            } catch (\Exception $e) {
                // Table might not exist
            }
            Lead::query()->delete();
            
            // Restore data
            $restoredCounts = [
                'leads' => 0,
                'test_logs' => 0,
                'queue' => 0
            ];
            
            // Restore leads
            foreach ($backupData['data']['leads'] as $leadData) {
                unset($leadData['created_at'], $leadData['updated_at']);
                Lead::create($leadData);
                $restoredCounts['leads']++;
            }
            
            // Restore test logs
            foreach ($backupData['data']['test_logs'] as $logData) {
                unset($logData['created_at'], $logData['updated_at']);
                AllstateTestLog::create($logData);
                $restoredCounts['test_logs']++;
            }
            
            // Restore queue if exists
            if (!empty($backupData['data']['queue'])) {
                foreach ($backupData['data']['queue'] as $queueData) {
                    unset($queueData['created_at'], $queueData['updated_at']);
                    LeadQueue::create($queueData);
                    $restoredCounts['queue']++;
                }
            }
            
            DB::commit();
            
            Log::info('✅ BACKUP RESTORED', [
                'backup_file' => basename($backupFile),
                'restored_counts' => $restoredCounts
            ]);
            
            return [
                'success' => true,
                'message' => 'Backup restored successfully',
                'restored_counts' => $restoredCounts
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ RESTORE FAILED', [
                'error' => $e->getMessage(),
                'backup_file' => $backupFile
            ]);
            
            throw $e;
        }
    }
}



