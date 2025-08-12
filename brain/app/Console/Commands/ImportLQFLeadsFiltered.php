<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Exception;

class ImportLQFLeadsFiltered extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'leads:import-lqf-filtered 
                            {file : Path to CSV file}
                            {--campaigns=Auto2,Autodial : Comma-separated list of campaigns to import}
                            {--skip-duplicates : Skip all duplicates}
                            {--update-vici : Update Vici vendor codes via API}
                            {--dry-run : Preview what would be imported}
                            {--limit=0 : Limit number of rows to import (0 = all)}
                            {--show-columns : Just show CSV columns and exit}';

    protected $description = 'Import leads from LQF CSV with campaign filtering and smart duplicate handling';

    public function handle()
    {
        $csvFile = $this->argument('file');
        $campaigns = explode(',', $this->option('campaigns'));
        $showColumns = $this->option('show-columns');
        
        if (!file_exists($csvFile)) {
            $this->error("File not found: $csvFile");
            return 1;
        }
        
        $this->info('========================================');
        $this->info('LQF Lead Import Tool (Filtered)');
        $this->info('========================================');
        $this->info("File: $csvFile");
        $this->info("Target campaigns: " . implode(', ', $campaigns));
        
        // Open CSV
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->error("Cannot open file");
            return 1;
        }
        
        // Get headers
        $headers = fgetcsv($handle);
        
        $this->info("\nCSV Columns Found (" . count($headers) . " columns):");
        $this->info("========================================");
        
        foreach ($headers as $index => $header) {
            $this->line(sprintf("%2d. %s", $index + 1, $header));
        }
        
        if ($showColumns) {
            fclose($handle);
            
            // Show mapping suggestions
            $this->info("\n========================================");
            $this->info("Suggested Field Mappings:");
            $this->info("========================================");
            
            $mappings = $this->suggestMappings($headers);
            foreach ($mappings as $brainField => $csvField) {
                if ($csvField) {
                    $this->line("  $brainField => $csvField");
                }
            }
            
            return 0;
        }
        
        // Continue with import...
        $this->processImport($handle, $headers, $campaigns);
        
        fclose($handle);
        return 0;
    }
    
    protected function suggestMappings($headers)
    {
        $mappings = [
            'phone' => null,
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'zip_code' => null,
            'campaign' => null,
            'created_at' => null,
            'lead_id' => null,
        ];
        
        // Try to auto-detect common field names
        foreach ($headers as $header) {
            $lower = strtolower(trim($header));
            
            if (strpos($lower, 'phone') !== false) {
                $mappings['phone'] = $header;
            } elseif (strpos($lower, 'first') !== false && strpos($lower, 'name') !== false) {
                $mappings['first_name'] = $header;
            } elseif (strpos($lower, 'last') !== false && strpos($lower, 'name') !== false) {
                $mappings['last_name'] = $header;
            } elseif (strpos($lower, 'email') !== false) {
                $mappings['email'] = $header;
            } elseif (strpos($lower, 'address') !== false || strpos($lower, 'street') !== false) {
                $mappings['address'] = $header;
            } elseif (strpos($lower, 'city') !== false) {
                $mappings['city'] = $header;
            } elseif (strpos($lower, 'state') !== false && strlen($header) < 20) {
                $mappings['state'] = $header;
            } elseif (strpos($lower, 'zip') !== false || strpos($lower, 'postal') !== false) {
                $mappings['zip_code'] = $header;
            } elseif (strpos($lower, 'campaign') !== false) {
                $mappings['campaign'] = $header;
            } elseif (strpos($lower, 'created') !== false || strpos($lower, 'date') !== false) {
                $mappings['created_at'] = $header;
            } elseif (strpos($lower, 'lead') !== false && strpos($lower, 'id') !== false) {
                $mappings['lead_id'] = $header;
            }
        }
        
        return $mappings;
    }
    
    protected function processImport($handle, $headers, $targetCampaigns)
    {
        $isDryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Get field mappings
        $mappings = $this->suggestMappings($headers);
        $campaignField = $mappings['campaign'];
        
        if (!$campaignField) {
            $this->warn("⚠️  No campaign field detected - will import ALL leads");
            if (!$this->confirm('Continue without campaign filtering?')) {
                return;
            }
        }
        
        $stats = [
            'total' => 0,
            'filtered' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];
        
        $rowNum = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            
            if ($limit > 0 && $stats['imported'] >= $limit) {
                break;
            }
            
            $stats['total']++;
            
            try {
                $data = array_combine($headers, $row);
                
                // Check campaign filter
                if ($campaignField && isset($data[$campaignField])) {
                    $leadCampaign = trim($data[$campaignField]);
                    $matchFound = false;
                    
                    foreach ($targetCampaigns as $targetCampaign) {
                        if (stripos($leadCampaign, trim($targetCampaign)) !== false) {
                            $matchFound = true;
                            break;
                        }
                    }
                    
                    if (!$matchFound) {
                        $stats['filtered']++;
                        continue;
                    }
                }
                
                // Process the lead
                $phone = preg_replace('/[^0-9]/', '', $data[$mappings['phone']] ?? '');
                
                if (empty($phone) || strlen($phone) < 10) {
                    $this->line("Row $rowNum: Invalid phone - skipping");
                    $stats['errors']++;
                    continue;
                }
                
                // Check for duplicates
                $existingLead = Lead::where('phone', 'LIKE', '%' . substr($phone, -10))
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($existingLead) {
                    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
                    
                    if ($daysSinceCreated < 30) {
                        $this->line("Row $rowNum: Recent duplicate - skipping");
                        $stats['skipped']++;
                        continue;
                    }
                }
                
                if (!$isDryRun) {
                    // Create the lead
                    $lead = $this->createLead($data, $mappings);
                    $this->info("Row $rowNum: Imported - {$lead->name} (Campaign: " . ($data[$campaignField] ?? 'N/A') . ")");
                } else {
                    $name = trim(($data[$mappings['first_name']] ?? '') . ' ' . ($data[$mappings['last_name']] ?? ''));
                    $this->info("Row $rowNum: Would import - $name (Campaign: " . ($data[$campaignField] ?? 'N/A') . ")");
                }
                
                $stats['imported']++;
                
                // Progress update
                if ($stats['total'] % 100 == 0) {
                    $this->showProgress($stats);
                }
                
            } catch (Exception $e) {
                $this->error("Row $rowNum: " . $e->getMessage());
                $stats['errors']++;
            }
        }
        
        // Final summary
        $this->info("\n========================================");
        $this->info("IMPORT SUMMARY");
        $this->info("========================================");
        $this->info("Total rows: " . number_format($stats['total']));
        $this->info("✅ Imported: " . number_format($stats['imported']));
        $this->info("🔍 Filtered out: " . number_format($stats['filtered']));
        $this->info("⏭️  Skipped (duplicates): " . number_format($stats['skipped']));
        $this->info("⚠️  Errors: " . number_format($stats['errors']));
        
        if ($campaignField) {
            $this->info("\nCampaign filter: " . implode(', ', $targetCampaigns));
            $this->info("Pass rate: " . round(($stats['imported'] / max(1, $stats['total'])) * 100, 1) . "%");
        }
    }
    
    protected function createLead($data, $mappings)
    {
        $lead = new Lead();
        
        $lead->external_lead_id = time() . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $lead->phone = preg_replace('/[^0-9]/', '', $data[$mappings['phone']] ?? '');
        $lead->first_name = $data[$mappings['first_name']] ?? '';
        $lead->last_name = $data[$mappings['last_name']] ?? '';
        $lead->name = trim($lead->first_name . ' ' . $lead->last_name) ?: 'Unknown';
        $lead->email = $data[$mappings['email']] ?? '';
        $lead->address = $data[$mappings['address']] ?? '';
        $lead->city = $data[$mappings['city']] ?? '';
        $lead->state = $data[$mappings['state']] ?? '';
        $lead->zip_code = $data[$mappings['zip_code']] ?? '';
        $lead->source = 'LQF_IMPORT';
        $lead->type = 'auto';
        $lead->campaign_id = $data[$mappings['campaign']] ?? null;
        
        $meta = [
            'import_date' => now()->toISOString(),
            'lqf_lead_id' => $data[$mappings['lead_id']] ?? null,
            'original_data' => $data
        ];
        $lead->meta = json_encode($meta);
        $lead->payload = json_encode($data);
        
        $lead->save();
        
        return $lead;
    }
    
    protected function showProgress($stats)
    {
        $this->line(sprintf(
            "Progress: %d processed | %d imported | %d filtered | %d skipped",
            $stats['total'],
            $stats['imported'],
            $stats['filtered'],
            $stats['skipped']
        ));
    }
}


