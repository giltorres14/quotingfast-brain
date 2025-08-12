<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSurajBulkCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suraj:bulk-import 
                           {folder : Path to folder containing Suraj CSV files}
                           {--pattern=*.csv : File pattern to match}
                           {--dry-run : Preview without importing}
                           {--oldest-first : Process files from oldest to newest}
                           {--push-to-vici : Push to Vici after each file}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import all Suraj CSV files from a folder - STRICT no duplicates for initial import';
    
    private $globalStats = [
        'total_files' => 0,
        'processed_files' => 0,
        'total_rows' => 0,
        'imported' => 0,
        'duplicates' => 0,
        'invalid' => 0,
        'errors' => 0
    ];
    
    private $existingPhones = [];
    private $processedFiles = [];
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folder = $this->argument('folder');
        $pattern = $this->option('pattern');
        $dryRun = $this->option('dry-run');
        $oldestFirst = $this->option('oldest-first');
        $pushToVici = $this->option('push-to-vici');
        
        // Validate folder
        if (!is_dir($folder)) {
            $this->error("Folder not found: $folder");
            return 1;
        }
        
        $this->info("========================================");
        $this->info("SURAJ BULK CSV IMPORT");
        $this->info("========================================");
        $this->newLine();
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be saved");
        } else {
            $this->info("💾 LIVE MODE - Data will be imported");
        }
        
        $this->info("📁 Folder: $folder");
        $this->info("📋 Pattern: " . (is_array($pattern) ? implode(', ', $pattern) : $pattern));
        $this->info("🔄 Processing Order: " . ($oldestFirst ? 'Oldest First' : 'Newest First'));
        $this->info("⚠️  Duplicate Rule: STRICT - Skip ALL duplicates");
        $this->newLine();
        
        // Get all CSV files
        $files = $this->getCSVFiles($folder, $pattern, $oldestFirst);
        
        if (empty($files)) {
            $patternStr = is_array($pattern) ? implode(', ', $pattern) : $pattern;
            $this->warn("No CSV files found matching pattern: $patternStr");
            return 0;
        }
        
        $this->globalStats['total_files'] = count($files);
        $this->info("📂 Found {$this->globalStats['total_files']} CSV files to process");
        $this->newLine();
        
        // Load ALL existing phone numbers upfront
        $this->loadExistingPhones();
        
        // Process each file
        foreach ($files as $index => $file) {
            $fileNumber = $index + 1;
            $this->processFile($file, $fileNumber, $dryRun, $pushToVici);
        }
        
        // Show final statistics
        $this->showFinalStatistics($dryRun);
        
        return 0;
    }
    
    /**
     * Get all CSV files from folder
     */
    private function getCSVFiles($folder, $pattern, $oldestFirst)
    {
        // Handle pattern as array or string
        if (is_array($pattern)) {
            $files = [];
            foreach ($pattern as $p) {
                $files = array_merge($files, glob($folder . '/' . $p));
            }
        } else {
            $files = glob($folder . '/' . $pattern);
        }
        
        if (empty($files)) {
            return [];
        }
        
        // Sort by modification time
        usort($files, function($a, $b) use ($oldestFirst) {
            $timeA = filemtime($a);
            $timeB = filemtime($b);
            return $oldestFirst ? ($timeA - $timeB) : ($timeB - $timeA);
        });
        
        return $files;
    }
    
    /**
     * Load all existing phone numbers
     */
    private function loadExistingPhones()
    {
        $this->info("📱 Loading existing phone numbers from database...");
        
        $phones = Lead::pluck('phone')
            ->map(function($phone) {
                $cleaned = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
                    $cleaned = substr($cleaned, 1);
                }
                return $cleaned;
            })
            ->filter(function($phone) {
                return strlen($phone) === 10;
            })
            ->toArray();
        
        $this->existingPhones = array_flip($phones);
        $this->info("   Found " . count($this->existingPhones) . " existing phone numbers");
        $this->newLine();
    }
    
    /**
     * Process a single CSV file
     */
    private function processFile($filePath, $fileNumber, $dryRun, $pushToVici)
    {
        $filename = basename($filePath);
        $fileDate = date('Y-m-d', filemtime($filePath));
        $fileSize = $this->formatBytes(filesize($filePath));
        
        $this->info("────────────────────────────────────────");
        $this->info("📄 File {$fileNumber}/{$this->globalStats['total_files']}: $filename");
        $this->info("   Date: $fileDate | Size: $fileSize");
        $this->newLine();
        
        $fileStats = [
            'rows' => 0,
            'imported' => 0,
            'duplicates' => 0,
            'invalid' => 0,
            'errors' => 0
        ];
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Cannot open file: $filePath");
            $this->globalStats['errors']++;
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
            $this->info("Headers: " . implode(', ', $headers));
            fclose($handle);
            return;
        }
        
        // Show column mapping for first file only
        if ($this->globalStats['processed_files'] === 0) {
            $this->info("Column Mapping:");
            foreach ($columnMap as $field => $index) {
                $this->line("   $field => " . ($headers[$index] ?? 'Unknown'));
            }
            $this->newLine();
        }
        
        // Process rows
        $bar = $this->output->createProgressBar();
        $bar->start();
        
        $importedLeadIds = [];
        $rowNumber = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $fileStats['rows']++;
            
            try {
                // Extract and clean phone
                $phone = $this->extractPhone($row, $columnMap);
                
                if (!$phone) {
                    $fileStats['invalid']++;
                    continue;
                }
                
                // CHECK FOR DUPLICATE - STRICT MODE
                if (isset($this->existingPhones[$phone])) {
                    $fileStats['duplicates']++;
                    continue;
                }
                
                // Build lead data
                $leadData = $this->buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers);
                
                // Import the lead
                if (!$dryRun) {
                    $lead = Lead::create($leadData);
                    $importedLeadIds[] = $lead->id;
                    
                    // Add to existing phones for subsequent files
                    $this->existingPhones[$phone] = true;
                }
                
                $fileStats['imported']++;
                
            } catch (\Exception $e) {
                $fileStats['errors']++;
                Log::error("Error in file $filename row $rowNumber: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        fclose($handle);
        
        // Update global stats
        $this->globalStats['processed_files']++;
        $this->globalStats['total_rows'] += $fileStats['rows'];
        $this->globalStats['imported'] += $fileStats['imported'];
        $this->globalStats['duplicates'] += $fileStats['duplicates'];
        $this->globalStats['invalid'] += $fileStats['invalid'];
        $this->globalStats['errors'] += $fileStats['errors'];
        
        // Show file summary
        $this->info("File Summary:");
        $this->line("   Rows: {$fileStats['rows']}");
        $this->line("   Imported: {$fileStats['imported']}");
        $this->line("   Duplicates: {$fileStats['duplicates']}");
        $this->line("   Invalid: {$fileStats['invalid']}");
        
        // Push to Vici if requested
        if ($pushToVici && !$dryRun && !empty($importedLeadIds)) {
            $this->pushToVici($importedLeadIds);
        }
        
        // Track processed file
        $this->processedFiles[] = [
            'file' => $filename,
            'date' => $fileDate,
            'rows' => $fileStats['rows'],
            'imported' => $fileStats['imported'],
            'duplicates' => $fileStats['duplicates']
        ];
        
        $this->newLine();
    }
    
    /**
     * Auto-map columns based on header names
     */
    private function autoMapColumns($headers)
    {
        $columnMap = [];
        
        $mappings = [
            'phone' => ['phonenumber', 'phone_number', 'phone', 'telephone', 'mobile', 'cell', 'contact'],
            'first_name' => ['firstname', 'first_name', 'fname', 'first'],
            'last_name' => ['lastname', 'last_name', 'lname', 'last'],
            'email' => ['emailaddress', 'email_address', 'email', 'e-mail'],
            'address' => ['mailaddress1', 'address', 'address1', 'street', 'street_address'],
            'city' => ['city', 'town'],
            'state' => ['provincestatename', 'state', 'province', 'st'],
            'zip' => ['zip', 'zip_code', 'zipcode', 'postal_code', 'postal'],
            'dob' => ['dob', 'date_of_birth', 'birthdate', 'birth_date'],
            'gender' => ['gender', 'sex'],
            'vendor_id' => ['vendor_id', 'vendorid', 'vendor_code'],
            'vendor_name' => ['vendor_name', 'vendorname', 'vendor'],
            'campaign_id' => ['vendor_campaign_id', 'campaign_id', 'campaign'],
            'buyer_id' => ['buyer_id', 'buyerid', 'buyer_code'],
            'buyer_name' => ['buyer_name', 'buyername', 'buyer'],
            'buyer_campaign_id' => ['buyer_campaign_id', 'buyercampaignid']
        ];
        
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($mappings as $field => $variations) {
                foreach ($variations as $variation) {
                    // Try exact match first
                    if ($headerLower === $variation) {
                        $columnMap[$field] = $index;
                        break 2;
                    }
                }
            }
        }
        
        // If we didn't find exact matches, try partial matches for remaining fields
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($mappings as $field => $variations) {
                // Skip if already mapped
                if (isset($columnMap[$field])) {
                    continue;
                }
                
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
     * Extract and clean phone number
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
     * Build lead data from CSV row
     */
    private function buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers = [])
    {
        $leadData = [
            'phone' => $phone,
            'source' => 'SURAJ_BULK',
            'type' => 'auto',
            'campaign_id' => 'SURAJ_' . $fileDate,
            'external_lead_id' => Lead::generateExternalLeadId()
        ];
        
        // Map available fields
        $fieldMapping = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'zip_code',
            'dob' => 'date_of_birth',
            'gender' => 'gender',
            'vendor_id' => 'vendor_id',
            'vendor_name' => 'vendor_name',
            'campaign_id' => 'campaign_id',
            'buyer_id' => 'buyer_id',
            'buyer_name' => 'buyer_name',
            'buyer_campaign_id' => 'buyer_campaign_id'
        ];
        
        foreach ($fieldMapping as $csvField => $dbField) {
            if (isset($columnMap[$csvField]) && isset($row[$columnMap[$csvField]])) {
                $value = trim($row[$columnMap[$csvField]]);
                if (!empty($value)) {
                    $leadData[$dbField] = $value;
                }
            }
        }
        
        // Build full name
        if (isset($leadData['first_name']) || isset($leadData['last_name'])) {
            $leadData['name'] = trim(
                ($leadData['first_name'] ?? '') . ' ' . 
                ($leadData['last_name'] ?? '')
            );
        }
        
        // Auto-create vendor if we have vendor_name
        if (!empty($leadData['vendor_name'])) {
            try {
                $vendor = \App\Models\Vendor::firstOrCreate(
                    ['name' => $leadData['vendor_name']],
                    [
                        'active' => true,
                        'notes' => 'Auto-created from Suraj import'
                    ]
                );
                
                // Increment lead count
                $vendor->increment('total_leads');
                
                // Add vendor_id to metadata if provided
                if (!empty($leadData['vendor_id'])) {
                    $contactInfo = $vendor->contact_info ?? [];
                    $contactInfo['vendor_id'] = $leadData['vendor_id'];
                    $vendor->contact_info = $contactInfo;
                    $vendor->save();
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create vendor: " . $e->getMessage());
            }
        }
        
        // Auto-create buyer if we have buyer_name
        $buyerId = null;
        if (!empty($leadData['buyer_name'])) {
            try {
                $buyer = \App\Models\Buyer::firstOrCreate(
                    ['name' => $leadData['buyer_name']],
                    [
                        'active' => true,
                        'notes' => 'Auto-created from Suraj import'
                    ]
                );
                
                // Increment lead count
                $buyer->increment('total_leads');
                $buyerId = $buyer->id;
                
                // Add buyer_id to metadata if provided
                if (!empty($leadData['buyer_id'])) {
                    $contactInfo = $buyer->contact_info ?? [];
                    $contactInfo['buyer_id'] = $leadData['buyer_id'];
                    $buyer->contact_info = $contactInfo;
                    $buyer->save();
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create buyer: " . $e->getMessage());
            }
        }
        
        // Create or update campaign with buyer info
        if (!empty($leadData['buyer_campaign_id'])) {
            try {
                // Use vendor_campaign_id if available, otherwise use buyer_campaign_id
                $campaignId = $leadData['campaign_id'] ?? $leadData['buyer_campaign_id'];
                
                $campaign = \App\Models\Campaign::firstOrCreate(
                    ['campaign_id' => $campaignId],
                    [
                        'name' => "Campaign #{$campaignId}",
                        'display_name' => "Campaign #{$campaignId}",
                        'description' => 'Auto-created from Suraj import',
                        'status' => 'active',
                        'is_auto_created' => true,
                        'tenant_id' => 1
                    ]
                );
                
                // Link buyer to campaign if we have a buyer
                if ($buyerId !== null) {
                    // Check if this buyer is already linked to this campaign
                    if (!$campaign->buyers()->where('buyer_id', $buyerId)->exists()) {
                        $campaign->buyers()->attach($buyerId, [
                            'buyer_campaign_id' => $leadData['buyer_campaign_id'],
                            'is_primary' => false
                        ]);
                    }
                }
                
                // Use the campaign_id for the lead
                $leadData['campaign_id'] = $campaignId;
                
            } catch (\Exception $e) {
                Log::warning("Failed to create/update campaign: " . $e->getMessage());
            }
        }
        
        // Create labeled CSV row for payload
        $csvRowData = [];
        if (!empty($headers)) {
            foreach ($headers as $index => $header) {
                if (isset($row[$index])) {
                    $csvRowData[$header] = $row[$index];
                }
            }
        }
        
        // Store full payload with CSV row data
        $leadData['payload'] = json_encode($csvRowData);
        
        // Store metadata
        $leadData['meta'] = json_encode([
            'import_file' => $filename,
            'import_date' => now()->toISOString(),
            'file_date' => $fileDate,
            'source' => 'Suraj Bulk Import',
            'vendor_id' => $leadData['vendor_id'] ?? null,
            'buyer_id' => $leadData['buyer_id'] ?? null,
            'vendor_name' => $leadData['vendor_name'] ?? null,
            'buyer_name' => $leadData['buyer_name'] ?? null
        ]);
        
        // Remove fields that don't exist as columns in leads table
        // Keep vendor_name and buyer_name as they are now columns
        unset($leadData['vendor_id']);
        unset($leadData['buyer_id']);
        unset($leadData['buyer_campaign_id']);
        
        return $leadData;
    }
    
    /**
     * Push leads to Vici
     */
    private function pushToVici($leadIds)
    {
        $this->info("📤 Pushing " . count($leadIds) . " leads to Vici...");
        
        try {
            $viciService = app(\App\Services\ViciDialerService::class);
            $pushed = 0;
            
            foreach ($leadIds as $leadId) {
                try {
                    $lead = Lead::find($leadId);
                    if ($lead) {
                        $viciService->pushLead($lead);
                        $pushed++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to push lead #{$leadId} to Vici: " . $e->getMessage());
                }
            }
            
            $this->line("   Pushed: $pushed/" . count($leadIds));
            
        } catch (\Exception $e) {
            $this->error("Error pushing to Vici: " . $e->getMessage());
        }
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Show final statistics
     */
    private function showFinalStatistics($dryRun)
    {
        $this->newLine();
        $this->info("========================================");
        $this->info("BULK IMPORT COMPLETE");
        $this->info("========================================");
        $this->newLine();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Files Processed', $this->globalStats['processed_files'] . '/' . $this->globalStats['total_files']],
                ['Total Rows', $this->globalStats['total_rows']],
                ['✅ Imported', $this->globalStats['imported']],
                ['🚫 Duplicates Skipped', $this->globalStats['duplicates']],
                ['⚠️  Invalid Phones', $this->globalStats['invalid']],
                ['❌ Errors', $this->globalStats['errors']],
            ]
        );
        
        if (count($this->processedFiles) > 0) {
            $this->newLine();
            $this->info("Files Processed:");
            
            $summary = array_map(function($file) {
                return [
                    $file['file'],
                    $file['date'],
                    $file['rows'],
                    $file['imported'],
                    $file['duplicates']
                ];
            }, array_slice($this->processedFiles, 0, 10));
            
            $this->table(
                ['File', 'Date', 'Rows', 'Imported', 'Dupes'],
                $summary
            );
            
            if (count($this->processedFiles) > 10) {
                $this->line("... and " . (count($this->processedFiles) - 10) . " more files");
            }
        }
        
        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN - no data was imported");
            $this->info("To perform actual import, run without --dry-run");
        } else if ($this->globalStats['imported'] > 0) {
            $this->newLine();
            $this->info("✅ Successfully imported {$this->globalStats['imported']} Suraj leads from {$this->globalStats['processed_files']} files!");
            
            $this->newLine();
            $this->info("Next Steps:");
            $this->line("1. Review imported leads: https://quotingfast-brain-ohio.onrender.com/leads");
            $this->line("2. Push all to Vici: php artisan vici:push-new-leads --source=SURAJ_BULK");
        }
    }
}


