<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportLQFCsv extends Command
{
    protected $signature = 'leads:import-csv 
                           {file : Path to CSV file}
                           {--dry-run : Preview without importing}
                           {--limit=0 : Limit number of leads to import (0=all)}
                           {--update-vici : Update Vici after import}
                           {--campaign=* : Filter by Vici campaigns (Auto2, Autodial)}';
    
    protected $description = 'Import leads from LQF CSV with smart duplicate handling';
    
    private $stats = [
        'total' => 0,
        'new' => 0,
        'updated' => 0,
        're_engagement' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    // Map CSV columns to our database fields
    private $columnMap = [
        'Lead ID' => 'lqf_lead_id',
        'Timestamp' => 'created_at',
        'First Name' => 'first_name',
        'Last Name' => 'last_name',
        'Email' => 'email',
        'Phone' => 'phone',
        'Address' => 'address',
        'City' => 'city',
        'State' => 'state',
        'ZIP Code' => 'zip_code',
        'IP Address' => 'ip_address',
        'Landing Page URL' => 'landing_page',
        'Trusted Form Cert URL' => 'trustedform_cert',
        'TCPA' => 'tcpa',
        'Data' => 'data' // JSON field with drivers, vehicles, policies
    ];
    
    public function handle()
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $updateVici = $this->option('update-vici');
        $campaigns = $this->option('campaign') ?: ['Auto2', 'Autodial'];
        
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }
        
        $this->info("Processing CSV: $file");
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }
        
        // Open CSV
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Could not open file: $file");
            return 1;
        }
        
        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            $this->error("Could not read CSV header");
            fclose($handle);
            return 1;
        }
        
        // Map header positions
        $columnPositions = [];
        foreach ($header as $index => $column) {
            $column = trim($column);
            if (isset($this->columnMap[$column])) {
                $columnPositions[$this->columnMap[$column]] = $index;
            }
        }
        
        // Verify required columns
        $requiredColumns = ['phone', 'first_name', 'last_name', 'data'];
        foreach ($requiredColumns as $required) {
            if (!isset($columnPositions[$required])) {
                $this->error("Required column not found: $required");
                fclose($handle);
                return 1;
            }
        }
        
        $this->info("Starting import...");
        $bar = $this->output->createProgressBar();
        
        // Process rows
        while (($row = fgetcsv($handle)) !== false) {
            $this->stats['total']++;
            
            if ($limit > 0 && $this->stats['total'] > $limit) {
                break;
            }
            
            try {
                $leadData = $this->parseRow($row, $columnPositions);
                
                if (!$leadData) {
                    $this->stats['skipped']++;
                    continue;
                }
                
                // Check for duplicates
                $result = $this->processLead($leadData, $dryRun);
                
                if ($result) {
                    $this->stats[$result]++;
                }
                
            } catch (\Exception $e) {
                $this->stats['errors']++;
                Log::error("Import error on row {$this->stats['total']}: " . $e->getMessage());
            }
            
            $bar->advance();
            
            // Show progress every 100 records
            if ($this->stats['total'] % 100 == 0) {
                $this->showProgress();
            }
        }
        
        fclose($handle);
        $bar->finish();
        $this->newLine();
        
        // Show final stats
        $this->showFinalStats();
        
        // Update Vici if requested
        if ($updateVici && !$dryRun && ($this->stats['new'] > 0 || $this->stats['updated'] > 0)) {
            $this->info("\nUpdating Vici with new vendor_lead_codes...");
            
            // Filter by campaigns if specified
            $campaignFilter = !empty($campaigns) ? '--campaign=' . implode(',', $campaigns) : '';
            
            $this->call('vici:update-vendor-codes', [
                '--campaigns' => $campaigns
            ]);
        }
        
        return 0;
    }
    
    private function parseRow($row, $columnPositions)
    {
        $leadData = [];
        
        foreach ($columnPositions as $field => $index) {
            if (isset($row[$index])) {
                $value = trim($row[$index]);
                
                // Parse JSON data field
                if ($field === 'data' && !empty($value)) {
                    try {
                        $jsonData = json_decode($value, true);
                        if ($jsonData) {
                            // Extract and flatten important fields
                            $leadData['drivers'] = json_encode($jsonData['drivers'] ?? []);
                            $leadData['vehicles'] = json_encode($jsonData['vehicles'] ?? []);
                            $leadData['current_policy'] = json_encode($jsonData['current_policy'] ?? []);
                            
                            // Extract key fields for easy access
                            if (isset($jsonData['drivers'][0])) {
                                $driver = $jsonData['drivers'][0];
                                $leadData['date_of_birth'] = $driver['birth_date'] ?? null;
                                $leadData['gender'] = $this->mapGender($driver['gender'] ?? '');
                                $leadData['marital_status'] = $driver['marital_status'] ?? null;
                                $leadData['education'] = $driver['education'] ?? null;
                                $leadData['occupation'] = $driver['occupation'] ?? null;
                            }
                            
                            if (isset($jsonData['current_policy'])) {
                                $policy = $jsonData['current_policy'];
                                $leadData['currently_insured'] = !empty($policy['insurance_company']);
                                $leadData['current_insurance_company'] = $policy['insurance_company'] ?? null;
                            }
                            
                            // Store full payload
                            $leadData['payload'] = $value;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Could not parse JSON data: " . $e->getMessage());
                    }
                } elseif ($field === 'tcpa') {
                    // Convert TCPA to boolean
                    $leadData[$field] = in_array(strtolower($value), ['yes', 'true', '1']);
                } elseif ($field === 'created_at' && !empty($value)) {
                    // Parse timestamp
                    try {
                        $leadData[$field] = Carbon::parse($value);
                    } catch (\Exception $e) {
                        $leadData[$field] = now();
                    }
                } else {
                    $leadData[$field] = $value;
                }
            }
        }
        
        // Clean phone number
        if (isset($leadData['phone'])) {
            $leadData['phone'] = preg_replace('/[^0-9]/', '', $leadData['phone']);
            if (strlen($leadData['phone']) === 10) {
                $leadData['phone'] = '1' . $leadData['phone'];
            }
        }
        
        // Set defaults
        $leadData['source'] = 'LQF_CSV_IMPORT';
        $leadData['type'] = 'auto-insurance';
        
        return $leadData;
    }
    
    private function mapGender($gender)
    {
        $gender = strtoupper(substr($gender, 0, 1));
        return $gender === 'M' ? 'male' : ($gender === 'F' ? 'female' : 'unknown');
    }
    
    private function processLead($leadData, $dryRun)
    {
        // Check for existing lead by phone
        $existingLead = Lead::where('phone', $leadData['phone'])->first();
        
        if (!$existingLead) {
            // New lead
            if (!$dryRun) {
                // Generate external_lead_id
                $leadData['external_lead_id'] = $this->generateExternalLeadId();
                
                Lead::create($leadData);
            }
            return 'new';
        }
        
        // Calculate age of existing lead
        $daysSinceCreated = $existingLead->created_at->diffInDays(now());
        
        if ($daysSinceCreated < 30) {
            // Recent lead - update with new info
            if (!$dryRun) {
                // Keep original external_lead_id
                unset($leadData['external_lead_id']);
                $existingLead->update($leadData);
            }
            return 'updated';
            
        } elseif ($daysSinceCreated <= 90) {
            // Re-engagement lead
            if (!$dryRun) {
                // Create new lead marked as re-engagement
                $leadData['external_lead_id'] = $this->generateExternalLeadId();
                $leadData['is_reengagement'] = true;
                $leadData['original_lead_id'] = $existingLead->id;
                $leadData['notes'] = "Re-engagement lead. Original from " . $existingLead->created_at->format('Y-m-d');
                
                Lead::create($leadData);
            }
            return 're_engagement';
            
        } else {
            // Old lead - treat as new
            if (!$dryRun) {
                $leadData['external_lead_id'] = $this->generateExternalLeadId();
                Lead::create($leadData);
            }
            return 'new';
        }
    }
    
    private function generateExternalLeadId()
    {
        // Use timestamp-based ID (13 digits)
        return (string) round(microtime(true) * 1000);
    }
    
    private function showProgress()
    {
        $this->newLine();
        $this->info(sprintf(
            "Progress: %d total | %d new | %d updated | %d re-engagement | %d skipped | %d errors",
            $this->stats['total'],
            $this->stats['new'],
            $this->stats['updated'],
            $this->stats['re_engagement'],
            $this->stats['skipped'],
            $this->stats['errors']
        ));
    }
    
    private function showFinalStats()
    {
        $this->newLine(2);
        $this->info("=== IMPORT COMPLETE ===");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $this->stats['total']],
                ['New Leads', $this->stats['new']],
                ['Updated Leads', $this->stats['updated']],
                ['Re-engagement Leads', $this->stats['re_engagement']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );
    }
}


