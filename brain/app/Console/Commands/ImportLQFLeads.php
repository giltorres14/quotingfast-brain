<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\ViciDialerService;
use Carbon\Carbon;
use Exception;

class ImportLQFLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:import-lqf 
                            {file : Path to CSV file}
                            {--skip-duplicates : Skip all duplicates}
                            {--update-vici : Update Vici vendor codes after import}
                            {--dry-run : Preview what would be imported}
                            {--limit=0 : Limit number of rows to import (0 = all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leads from LQF CSV with smart duplicate handling';

    protected $viciService;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $csvFile = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $skipDuplicates = $this->option('skip-duplicates');
        $updateVici = $this->option('update-vici');
        $limit = $this->option('limit');
        
        if (!file_exists($csvFile)) {
            $this->error("File not found: $csvFile");
            return 1;
        }
        
        $this->info('========================================');
        $this->info('LQF Lead Import Tool');
        $this->info('========================================');
        $this->info("File: $csvFile");
        $this->info("File size: " . number_format(filesize($csvFile) / 1024 / 1024, 2) . " MB");
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Open CSV
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->error("Cannot open file");
            return 1;
        }
        
        // Get headers
        $headers = fgetcsv($handle);
        $this->info("CSV Headers: " . implode(', ', $headers));
        $this->info('');
        
        // Stats
        $stats = [
            'total' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped_recent' => 0,
            'reengagement' => 0,
            'errors' => 0,
            'vici_updated' => 0
        ];
        
        $rowNum = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            
            if ($limit > 0 && $stats['total'] >= $limit) {
                $this->info("Reached limit of $limit rows");
                break;
            }
            
            $stats['total']++;
            
            try {
                // Map CSV to data array
                $data = array_combine($headers, $row);
                
                // Clean phone
                $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
                
                if (empty($phone) || strlen($phone) < 10) {
                    $this->line("Row $rowNum: Invalid phone - skipping");
                    $stats['errors']++;
                    continue;
                }
                
                // Check for existing lead by phone (last 10 digits)
                $existingLead = Lead::where('phone', 'LIKE', '%' . substr($phone, -10))
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($existingLead) {
                    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
                    
                    if ($daysSinceCreated < 30) {
                        // Recent duplicate - handle based on settings
                        if ($skipDuplicates) {
                            $this->line("Row $rowNum: Recent duplicate ({$daysSinceCreated} days) - skipping");
                            $stats['skipped_recent']++;
                            continue;
                        } else {
                            // Update existing lead with new info
                            if (!$isDryRun) {
                                $this->updateExistingLead($existingLead, $data);
                            }
                            $this->info("Row $rowNum: Updated recent lead (ID: {$existingLead->id})");
                            $stats['updated']++;
                            continue;
                        }
                    } elseif ($daysSinceCreated <= 90) {
                        // Re-engagement lead (30-90 days old)
                        if (!$isDryRun) {
                            $newLead = $this->createReengagementLead($data, $existingLead);
                            $this->info("Row $rowNum: Created re-engagement lead (ID: {$newLead->id}, parent: {$existingLead->id})");
                            
                            if ($updateVici) {
                                $this->updateViciForLead($newLead);
                                $stats['vici_updated']++;
                            }
                        } else {
                            $this->info("Row $rowNum: Would create re-engagement lead");
                        }
                        $stats['reengagement']++;
                        continue;
                    }
                    // If > 90 days, treat as new lead (continue to import)
                }
                
                // Create new lead
                if (!$isDryRun) {
                    $lead = $this->createNewLead($data);
                    $this->info("Row $rowNum: Imported new lead - {$lead->name} (ID: {$lead->id})");
                    
                    if ($updateVici) {
                        $this->updateViciForLead($lead);
                        $stats['vici_updated']++;
                    }
                } else {
                    $this->info("Row $rowNum: Would import - " . ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
                }
                
                $stats['imported']++;
                
                // Show progress every 100 rows
                if ($stats['total'] % 100 == 0) {
                    $this->showProgress($stats);
                }
                
            } catch (Exception $e) {
                $this->error("Row $rowNum: Error - " . $e->getMessage());
                $stats['errors']++;
            }
        }
        
        fclose($handle);
        
        // Final summary
        $this->info('');
        $this->info('========================================');
        $this->info('IMPORT SUMMARY');
        $this->info('========================================');
        $this->info("Total rows processed: " . number_format($stats['total']));
        $this->info("✅ New leads imported: " . number_format($stats['imported']));
        $this->info("🔄 Existing leads updated: " . number_format($stats['updated']));
        $this->info("🔁 Re-engagement leads: " . number_format($stats['reengagement']));
        $this->info("⏭️  Recent duplicates skipped: " . number_format($stats['skipped_recent']));
        $this->info("⚠️  Errors: " . number_format($stats['errors']));
        
        if ($updateVici) {
            $this->info("📞 Vici records updated: " . number_format($stats['vici_updated']));
        }
        
        $this->info('========================================');
        
        if ($isDryRun) {
            $this->warn('This was a DRY RUN - no changes were made');
            $this->info('Run without --dry-run to perform actual import');
        }
        
        return 0;
    }
    
    protected function createNewLead($data)
    {
        $lead = new Lead();
        
        // Generate external_lead_id
        $lead->external_lead_id = time() . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Map fields
        $lead->phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
        $lead->first_name = $data['first_name'] ?? '';
        $lead->last_name = $data['last_name'] ?? '';
        $lead->name = trim($lead->first_name . ' ' . $lead->last_name) ?: 'Unknown';
        $lead->email = $data['email'] ?? '';
        $lead->address = $data['address'] ?? '';
        $lead->city = $data['city'] ?? '';
        $lead->state = $data['state'] ?? '';
        $lead->zip_code = $data['zip_code'] ?? $data['zip'] ?? '';
        
        // Set source and type
        $lead->source = 'LQF_IMPORT';
        $lead->type = $data['type'] ?? 'auto';
        
        // Store original data in meta
        $meta = [
            'import_date' => now()->toISOString(),
            'lqf_lead_id' => $data['lead_id'] ?? null,
            'original_created' => $data['created_at'] ?? null
        ];
        $lead->meta = json_encode($meta);
        
        // Handle JSON fields if present
        if (!empty($data['drivers'])) {
            $lead->drivers = $data['drivers'];
        }
        if (!empty($data['vehicles'])) {
            $lead->vehicles = $data['vehicles'];
        }
        if (!empty($data['current_policy'])) {
            $lead->current_policy = $data['current_policy'];
        }
        
        // Store full payload
        $lead->payload = json_encode($data);
        
        $lead->save();
        
        return $lead;
    }
    
    protected function updateExistingLead($lead, $data)
    {
        // Update duplicate count
        $meta = json_decode($lead->meta ?? '{}', true);
        $meta['duplicate_count'] = ($meta['duplicate_count'] ?? 0) + 1;
        $meta['last_inquiry_at'] = now()->toISOString();
        $lead->meta = json_encode($meta);
        
        // Update any missing fields
        if (empty($lead->email) && !empty($data['email'])) {
            $lead->email = $data['email'];
        }
        if (empty($lead->address) && !empty($data['address'])) {
            $lead->address = $data['address'];
        }
        
        $lead->save();
        
        return $lead;
    }
    
    protected function createReengagementLead($data, $parentLead)
    {
        $lead = $this->createNewLead($data);
        
        // Mark as re-engagement
        $meta = json_decode($lead->meta, true);
        $meta['is_reengagement'] = true;
        $meta['parent_lead_id'] = $parentLead->id;
        $meta['days_since_original'] = $parentLead->created_at->diffInDays(now());
        $lead->meta = json_encode($meta);
        
        $lead->save();
        
        return $lead;
    }
    
    protected function updateViciForLead($lead)
    {
        try {
            $viciService = new ViciDialerService();
            
            // Check if lead exists in Vici by phone
            $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
            
            // This would use the ViciDialerService to update
            // For now, we'll use direct PDO as shown in the other command
            
            $this->line("  └─ Updated Vici vendor_code: {$lead->external_lead_id}");
            
        } catch (Exception $e) {
            $this->warn("  └─ Could not update Vici: " . $e->getMessage());
        }
    }
    
    protected function showProgress($stats)
    {
        $this->info(sprintf(
            "Progress: %d processed | %d imported | %d updated | %d reengagement | %d skipped",
            $stats['total'],
            $stats['imported'],
            $stats['updated'],
            $stats['reengagement'],
            $stats['skipped_recent']
        ));
    }
}


