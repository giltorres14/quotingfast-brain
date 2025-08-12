<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WatchSurajFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suraj:watch-folder 
                           {folder : Path to watch folder for Suraj CSV files}
                           {--interval=60 : Check interval in seconds}
                           {--process-existing : Process existing files in folder}
                           {--move-processed : Move processed files to processed/ subfolder}
                           {--push-to-vici : Automatically push to Vici}
                           {--once : Run once instead of continuous watching}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch folder for new Suraj CSV files and auto-import with LQF duplicate rules';
    
    private $stats = [
        'files_processed' => 0,
        'total_imported' => 0,
        'total_updated' => 0,
        'total_reengagement' => 0,
        'total_skipped' => 0
    ];
    
    private $processedFiles = [];
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folder = $this->argument('folder');
        $interval = (int) $this->option('interval');
        $processExisting = $this->option('process-existing');
        $moveProcessed = $this->option('move-processed');
        $pushToVici = $this->option('push-to-vici');
        $runOnce = $this->option('once');
        
        // Validate folder
        if (!is_dir($folder)) {
            $this->error("Folder not found: $folder");
            $this->info("Creating folder: $folder");
            mkdir($folder, 0755, true);
        }
        
        // Create processed folder if needed
        $processedFolder = $folder . '/processed';
        if ($moveProcessed && !is_dir($processedFolder)) {
            mkdir($processedFolder, 0755, true);
        }
        
        // Create tracking file for processed files
        $trackingFile = $folder . '/.suraj_processed.json';
        if (file_exists($trackingFile)) {
            $this->processedFiles = json_decode(file_get_contents($trackingFile), true);
        }
        
        $this->info("========================================");
        $this->info("SURAJ FOLDER WATCHER");
        $this->info("========================================");
        $this->newLine();
        
        $this->info("📁 Watch Folder: $folder");
        $this->info("⏱️  Check Interval: {$interval} seconds");
        $this->info("📤 Auto Push to Vici: " . ($pushToVici ? 'Yes' : 'No'));
        $this->info("🔄 Duplicate Rules: LQF Mode (0-10 update, 11-90 re-engage, 91+ new)");
        $this->newLine();
        
        if ($processExisting) {
            $this->info("Processing existing files in folder...");
            $this->processFolder($folder, $moveProcessed, $pushToVici);
        }
        
        if ($runOnce) {
            $this->info("Single run complete.");
            $this->showStatistics();
            return 0;
        }
        
        $this->info("👀 Watching for new CSV files... (Press Ctrl+C to stop)");
        $this->newLine();
        
        // Continuous watching loop
        while (true) {
            $this->processFolder($folder, $moveProcessed, $pushToVici);
            
            // Save tracking file
            file_put_contents($trackingFile, json_encode($this->processedFiles, JSON_PRETTY_PRINT));
            
            sleep($interval);
        }
    }
    
    /**
     * Process all CSV files in folder
     */
    private function processFolder($folder, $moveProcessed, $pushToVici)
    {
        $files = glob($folder . '/*.csv');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $fileHash = md5_file($file);
            
            // Check if already processed
            if (isset($this->processedFiles[$filename])) {
                if ($this->processedFiles[$filename]['hash'] === $fileHash) {
                    continue; // Already processed
                }
            }
            
            $this->info("📄 New file detected: $filename");
            $this->processFile($file, $pushToVici);
            
            // Track as processed
            $this->processedFiles[$filename] = [
                'hash' => $fileHash,
                'processed_at' => now()->toISOString(),
                'size' => filesize($file),
                'imported' => $this->stats['total_imported']
            ];
            
            // Move to processed folder if requested
            if ($moveProcessed) {
                $processedPath = $folder . '/processed/' . $filename;
                rename($file, $processedPath);
                $this->info("   Moved to: processed/$filename");
            }
            
            $this->stats['files_processed']++;
        }
    }
    
    /**
     * Process a single CSV file with LQF duplicate rules
     */
    private function processFile($filePath, $pushToVici)
    {
        $filename = basename($filePath);
        $fileStats = [
            'rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'reengagement' => 0,
            'skipped' => 0,
            'errors' => 0
        ];
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Cannot open file: $filePath");
            return;
        }
        
        // Get headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            $this->warn("File appears to be empty");
            fclose($handle);
            return;
        }
        
        // Auto-map columns
        $columnMap = $this->autoMapColumns($headers);
        
        if (!isset($columnMap['phone'])) {
            $this->error("Could not find phone column in file");
            fclose($handle);
            return;
        }
        
        $this->info("   Processing rows...");
        
        $importedLeadIds = [];
        $rowNumber = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $fileStats['rows']++;
            
            try {
                // Extract and clean phone
                $phone = $this->extractPhone($row, $columnMap);
                
                if (!$phone) {
                    $fileStats['skipped']++;
                    continue;
                }
                
                // Build lead data
                $leadData = $this->buildLeadData($row, $columnMap, $phone, $filename);
                
                // APPLY LQF DUPLICATE RULES
                $existingLead = Lead::where('phone', $phone)
                    ->orWhere('phone', '1' . $phone)
                    ->first();
                
                if ($existingLead) {
                    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
                    
                    if ($daysSinceCreated <= 10) {
                        // 0-10 days: Update existing lead
                        $leadData['status'] = 'DUPLICATE_UPDATED';
                        $leadData['meta'] = json_encode(array_merge(
                            json_decode($leadData['meta'] ?? '{}', true),
                            [
                                'duplicate_action' => 'updated',
                                'original_created_at' => $existingLead->created_at->toIso8601String(),
                                'days_since_original' => $daysSinceCreated,
                                'source_file' => $filename
                            ]
                        ));
                        
                        unset($leadData['external_lead_id']); // Keep original ID
                        $existingLead->update($leadData);
                        $lead = $existingLead;
                        $fileStats['updated']++;
                        
                        Log::info('✅ Updated existing Suraj lead (≤ 10 days)', [
                            'lead_id' => $lead->id,
                            'phone' => $phone,
                            'days_old' => $daysSinceCreated
                        ]);
                        
                    } elseif ($daysSinceCreated <= 90) {
                        // 11-90 days: Create re-engagement lead
                        $leadData['status'] = 'RE_ENGAGEMENT';
                        $leadData['meta'] = json_encode(array_merge(
                            json_decode($leadData['meta'] ?? '{}', true),
                            [
                                're_engagement' => true,
                                'original_lead_id' => $existingLead->id,
                                'original_created_at' => $existingLead->created_at->toIso8601String(),
                                'days_since_original' => $daysSinceCreated,
                                'source_file' => $filename
                            ]
                        ));
                        
                        $lead = Lead::create($leadData);
                        $fileStats['reengagement']++;
                        
                        Log::info('🔄 Created re-engagement Suraj lead (11-90 days)', [
                            'new_lead_id' => $lead->id,
                            'original_lead_id' => $existingLead->id,
                            'phone' => $phone,
                            'days_since_original' => $daysSinceCreated
                        ]);
                        
                    } else {
                        // Over 90 days: Create as new lead
                        $leadData['status'] = 'NEW_AFTER_90_DAYS';
                        $lead = Lead::create($leadData);
                        $fileStats['imported']++;
                        
                        Log::info('🆕 Created new Suraj lead (over 90 days)', [
                            'lead_id' => $lead->id,
                            'phone' => $phone
                        ]);
                    }
                } else {
                    // No existing lead - create new
                    $lead = Lead::create($leadData);
                    $fileStats['imported']++;
                    
                    Log::info('✨ New Suraj lead created', [
                        'lead_id' => $lead->id,
                        'phone' => $phone
                    ]);
                }
                
                $importedLeadIds[] = $lead->id;
                
            } catch (\Exception $e) {
                $fileStats['errors']++;
                Log::error("Error processing row $rowNumber: " . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        // Update global stats
        $this->stats['total_imported'] += $fileStats['imported'];
        $this->stats['total_updated'] += $fileStats['updated'];
        $this->stats['total_reengagement'] += $fileStats['reengagement'];
        $this->stats['total_skipped'] += $fileStats['skipped'];
        
        // Show file summary
        $this->info("   ✅ Processed: {$fileStats['rows']} rows");
        $this->line("      New: {$fileStats['imported']} | Updated: {$fileStats['updated']} | Re-engage: {$fileStats['reengagement']} | Skipped: {$fileStats['skipped']}");
        
        // Push to Vici if requested
        if ($pushToVici && !empty($importedLeadIds)) {
            $this->pushToVici($importedLeadIds);
        }
    }
    
    /**
     * Auto-map columns
     */
    private function autoMapColumns($headers)
    {
        $columnMap = [];
        
        $mappings = [
            'phone' => ['phone', 'phone_number', 'telephone', 'mobile', 'cell'],
            'first_name' => ['first_name', 'firstname', 'fname', 'first'],
            'last_name' => ['last_name', 'lastname', 'lname', 'last'],
            'email' => ['email', 'email_address'],
            'address' => ['address', 'address1', 'street'],
            'city' => ['city', 'town'],
            'state' => ['state', 'province', 'st'],
            'zip' => ['zip', 'zip_code', 'zipcode', 'postal_code']
        ];
        
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($mappings as $field => $variations) {
                foreach ($variations as $variation) {
                    if (strpos($headerLower, $variation) !== false) {
                        $columnMap[$field] = $index;
                        break 2;
                    }
                }
            }
        }
        
        return $columnMap;
    }
    
    /**
     * Extract phone number
     */
    private function extractPhone($row, $columnMap)
    {
        if (!isset($columnMap['phone']) || !isset($row[$columnMap['phone']])) {
            return null;
        }
        
        $phone = preg_replace('/[^0-9]/', '', $row[$columnMap['phone']]);
        
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
            $phone = substr($phone, 1);
        }
        
        if (strlen($phone) !== 10) {
            return null;
        }
        
        return $phone;
    }
    
    /**
     * Build lead data
     */
    private function buildLeadData($row, $columnMap, $phone, $filename)
    {
        $leadData = [
            'phone' => $phone,
            'source' => 'SURAJ_AUTO',
            'type' => 'auto',
            'campaign_id' => 'SURAJ_' . date('Y-m-d'),
            'external_lead_id' => Lead::generateExternalLeadId()
        ];
        
        // Map fields
        $fieldMapping = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'zip_code'
        ];
        
        foreach ($fieldMapping as $csvField => $dbField) {
            if (isset($columnMap[$csvField]) && isset($row[$columnMap[$csvField]])) {
                $value = trim($row[$columnMap[$csvField]]);
                if (!empty($value)) {
                    $leadData[$dbField] = $value;
                }
            }
        }
        
        // Build name
        if (isset($leadData['first_name']) || isset($leadData['last_name'])) {
            $leadData['name'] = trim(
                ($leadData['first_name'] ?? '') . ' ' . 
                ($leadData['last_name'] ?? '')
            );
        }
        
        // Metadata
        $leadData['meta'] = json_encode([
            'import_file' => $filename,
            'import_date' => now()->toISOString(),
            'source' => 'Suraj Auto Import'
        ]);
        
        return $leadData;
    }
    
    /**
     * Push to Vici
     */
    private function pushToVici($leadIds)
    {
        $this->info("   📤 Pushing " . count($leadIds) . " leads to Vici...");
        
        try {
            $viciService = app(\App\Services\ViciDialerService::class);
            $pushed = 0;
            
            foreach ($leadIds as $leadId) {
                $lead = Lead::find($leadId);
                if ($lead) {
                    $viciService->pushLead($lead);
                    $pushed++;
                }
            }
            
            $this->line("      Pushed: $pushed/" . count($leadIds));
            
        } catch (\Exception $e) {
            $this->error("   Error pushing to Vici: " . $e->getMessage());
        }
    }
    
    /**
     * Show statistics
     */
    private function showStatistics()
    {
        $this->newLine();
        $this->info("📊 Statistics:");
        $this->line("   Files Processed: {$this->stats['files_processed']}");
        $this->line("   Total Imported: {$this->stats['total_imported']}");
        $this->line("   Total Updated: {$this->stats['total_updated']}");
        $this->line("   Total Re-engagement: {$this->stats['total_reengagement']}");
        $this->line("   Total Skipped: {$this->stats['total_skipped']}");
    }
}
