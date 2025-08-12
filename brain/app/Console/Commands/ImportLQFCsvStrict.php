<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportLQFCsvStrict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:import-strict 
                           {file : Path to CSV file}
                           {--dry-run : Preview without importing}
                           {--skip-existing : Skip ALL existing phone numbers (default)}
                           {--show-duplicates : Show details of duplicate phone numbers}
                           {--limit=0 : Limit number of rows to process (0=all)}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import LQF CSV with STRICT duplicate prevention - NO duplicates allowed';
    
    private $stats = [
        'total_rows' => 0,
        'imported' => 0,
        'duplicates' => 0,
        'invalid' => 0,
        'errors' => 0
    ];
    
    private $duplicatePhones = [];
    private $existingPhonesSet = [];
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $showDuplicates = $this->option('show-duplicates');
        $limit = (int) $this->option('limit');
        
        // Validate file
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }
        
        $this->info("========================================");
        $this->info("LQF CSV IMPORT - STRICT NO DUPLICATES");
        $this->info("========================================");
        $this->newLine();
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be saved");
        } else {
            $this->info("💾 LIVE MODE - Data will be imported");
        }
        
        $this->info("📁 CSV File: $file");
        $this->info("📏 Limit: " . ($limit > 0 ? $limit : 'No limit'));
        $this->newLine();
        
        // Load existing phone numbers
        $this->loadExistingPhones();
        
        // Process CSV
        $this->processCSV($file, $dryRun, $showDuplicates, $limit);
        
        // Show final statistics
        $this->showFinalStatistics($dryRun, $showDuplicates);
        
        return 0;
    }
    
    /**
     * Load all existing phone numbers for duplicate checking
     */
    private function loadExistingPhones()
    {
        $this->info("📱 Loading existing phone numbers...");
        
        $existingPhones = Lead::pluck('phone')
            ->map(function($phone) {
                // Clean phone to 10 digits for comparison
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
        
        // Create hashmap for O(1) lookup
        $this->existingPhonesSet = array_flip($existingPhones);
        
        $this->info("   Found " . count($existingPhones) . " existing phone numbers in database");
        $this->newLine();
    }
    
    /**
     * Process the CSV file
     */
    private function processCSV($file, $dryRun, $showDuplicates, $limit)
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Cannot open file: $file");
            return;
        }
        
        // Get headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            $this->error("CSV file appears to be empty");
            fclose($handle);
            return;
        }
        
        // Map columns
        $columnMap = $this->mapColumns($headers);
        
        if (!isset($columnMap['phone'])) {
            $this->error("Could not find phone column in CSV");
            $this->error("Headers found: " . implode(', ', $headers));
            fclose($handle);
            return;
        }
        
        $this->info("✅ Column Mapping:");
        $this->table(
            ['Field', 'CSV Column'],
            array_map(function($field, $index) use ($headers) {
                return [$field, $headers[$index] ?? 'Unknown'];
            }, array_keys($columnMap), $columnMap)
        );
        $this->newLine();
        
        // Process rows
        $this->info("🔄 Processing CSV rows...");
        
        $bar = $this->output->createProgressBar();
        $bar->start();
        
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $this->stats['total_rows']++;
            
            // Check limit
            if ($limit > 0 && $this->stats['total_rows'] > $limit) {
                break;
            }
            
            // Process row
            $this->processRow($row, $columnMap, $rowNumber, $dryRun, $showDuplicates);
            
            $bar->advance();
            
            // Show progress every 100 rows
            if ($this->stats['total_rows'] % 100 == 0) {
                $this->newLine();
                $this->info("Progress: {$this->stats['total_rows']} rows processed");
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        fclose($handle);
    }
    
    /**
     * Map CSV headers to database fields
     */
    private function mapColumns($headers)
    {
        $columnMap = [];
        
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            // Phone (REQUIRED)
            if (strpos($headerLower, 'phone') !== false) {
                $columnMap['phone'] = $index;
            }
            // Names
            elseif (strpos($headerLower, 'first') !== false && strpos($headerLower, 'name') !== false) {
                $columnMap['first_name'] = $index;
            }
            elseif (strpos($headerLower, 'last') !== false && strpos($headerLower, 'name') !== false) {
                $columnMap['last_name'] = $index;
            }
            elseif ($headerLower === 'name' || strpos($headerLower, 'full') !== false) {
                $columnMap['name'] = $index;
            }
            // Contact info
            elseif (strpos($headerLower, 'email') !== false) {
                $columnMap['email'] = $index;
            }
            elseif (strpos($headerLower, 'address') !== false && strpos($headerLower, 'email') === false) {
                $columnMap['address'] = $index;
            }
            elseif (strpos($headerLower, 'city') !== false) {
                $columnMap['city'] = $index;
            }
            elseif (strpos($headerLower, 'state') !== false && strpos($headerLower, 'united') === false) {
                $columnMap['state'] = $index;
            }
            elseif (strpos($headerLower, 'zip') !== false || strpos($headerLower, 'postal') !== false) {
                $columnMap['zip_code'] = $index;
            }
            // Data fields
            elseif ($headerLower === 'data' || strpos($headerLower, 'json') !== false || strpos($headerLower, 'payload') !== false) {
                $columnMap['data'] = $index;
            }
            // Timestamps
            elseif (strpos($headerLower, 'timestamp') !== false || strpos($headerLower, 'created') !== false || strpos($headerLower, 'date') !== false) {
                $columnMap['created_at'] = $index;
            }
            // IDs
            elseif (strpos($headerLower, 'lead') !== false && strpos($headerLower, 'id') !== false) {
                $columnMap['lqf_lead_id'] = $index;
            }
            // Source/Campaign
            elseif (strpos($headerLower, 'source') !== false || strpos($headerLower, 'utm_source') !== false) {
                $columnMap['source'] = $index;
            }
            elseif (strpos($headerLower, 'campaign') !== false) {
                $columnMap['campaign_id'] = $index;
            }
        }
        
        return $columnMap;
    }
    
    /**
     * Process a single CSV row
     */
    private function processRow($row, $columnMap, $rowNumber, $dryRun, $showDuplicates)
    {
        try {
            // Extract and clean phone number
            $phone = $this->extractPhone($row, $columnMap);
            
            if (!$phone) {
                $this->stats['invalid']++;
                return;
            }
            
            // CHECK FOR DUPLICATE - STRICT MODE
            if (isset($this->existingPhonesSet[$phone])) {
                $this->stats['duplicates']++;
                $this->duplicatePhones[] = $phone;
                
                if ($showDuplicates) {
                    $existingLead = Lead::where('phone', $phone)
                        ->orWhere('phone', '1' . $phone)
                        ->first();
                    
                    $this->warn("🚫 Row $rowNumber: DUPLICATE - Phone $phone" . 
                        ($existingLead ? " (Lead #{$existingLead->id}, Created: {$existingLead->created_at->format('Y-m-d')})" : ""));
                }
                
                return;
            }
            
            // Build lead data
            $leadData = $this->buildLeadData($row, $columnMap, $phone);
            
            // Import the lead
            if (!$dryRun) {
                $lead = Lead::create($leadData);
                
                // Add to existing phones set
                $this->existingPhonesSet[$phone] = true;
                
                if ($this->output->isVerbose()) {
                    $this->info("✅ Row $rowNumber: Imported - Lead #{$lead->id}, Phone: $phone");
                }
            }
            
            $this->stats['imported']++;
            
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("Row $rowNumber: " . $e->getMessage());
        }
    }
    
    /**
     * Extract and clean phone number from row
     */
    private function extractPhone($row, $columnMap)
    {
        if (!isset($columnMap['phone']) || !isset($row[$columnMap['phone']])) {
            return null;
        }
        
        $phone = preg_replace('/[^0-9]/', '', $row[$columnMap['phone']]);
        
        // Remove country code if present
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
            $phone = substr($phone, 1);
        }
        
        // Validate length
        if (strlen($phone) !== 10) {
            return null;
        }
        
        return $phone;
    }
    
    /**
     * Build lead data from CSV row
     */
    private function buildLeadData($row, $columnMap, $phone)
    {
        $leadData = [
            'phone' => $phone,
            'source' => 'LQF_CSV',
            'type' => 'auto',
            'campaign_id' => 'CSV_IMPORT_' . date('Y-m-d'),
            'external_lead_id' => Lead::generateExternalLeadId()
        ];
        
        // Map all fields
        foreach ($columnMap as $field => $index) {
            if ($field !== 'phone' && isset($row[$index]) && !empty(trim($row[$index]))) {
                $value = trim($row[$index]);
                
                if ($field === 'data') {
                    // Parse JSON data
                    $this->parseJsonData($value, $leadData);
                } elseif ($field === 'created_at') {
                    // Parse date
                    try {
                        $leadData['created_at'] = Carbon::parse($value);
                    } catch (\Exception $e) {
                        $leadData['created_at'] = now();
                    }
                } elseif ($field === 'source' && !empty($value)) {
                    // Override source if provided
                    $leadData['source'] = $value;
                } else {
                    $leadData[$field] = $value;
                }
            }
        }
        
        // Build full name if needed
        if (!isset($leadData['name'])) {
            if (isset($leadData['first_name']) || isset($leadData['last_name'])) {
                $leadData['name'] = trim(
                    ($leadData['first_name'] ?? '') . ' ' . 
                    ($leadData['last_name'] ?? '')
                );
            }
        }
        
        return $leadData;
    }
    
    /**
     * Parse JSON data field
     */
    private function parseJsonData($value, &$leadData)
    {
        try {
            $jsonData = json_decode($value, true);
            if ($jsonData) {
                $leadData['drivers'] = json_encode($jsonData['drivers'] ?? []);
                $leadData['vehicles'] = json_encode($jsonData['vehicles'] ?? []);
                $leadData['current_policy'] = json_encode($jsonData['current_policy'] ?? []);
                $leadData['payload'] = $value;
                
                // Extract key fields
                if (isset($jsonData['drivers'][0])) {
                    $driver = $jsonData['drivers'][0];
                    $leadData['date_of_birth'] = $driver['birth_date'] ?? $driver['dob'] ?? null;
                    $leadData['gender'] = $driver['gender'] ?? null;
                }
            }
        } catch (\Exception $e) {
            $leadData['payload'] = $value;
        }
    }
    
    /**
     * Show final statistics
     */
    private function showFinalStatistics($dryRun, $showDuplicates)
    {
        $this->newLine();
        $this->info("========================================");
        $this->info("IMPORT COMPLETE");
        $this->info("========================================");
        $this->newLine();
        
        // Statistics table
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Rows Processed', $this->stats['total_rows']],
                ['✅ Imported', $this->stats['imported']],
                ['🚫 Duplicates Skipped', $this->stats['duplicates']],
                ['⚠️  Invalid Phone Numbers', $this->stats['invalid']],
                ['❌ Errors', $this->stats['errors']],
            ]
        );
        
        // Show duplicate details if requested
        if ($showDuplicates && $this->stats['duplicates'] > 0) {
            $this->newLine();
            $this->warn("Duplicate Phone Numbers Found:");
            
            $uniqueDuplicates = array_unique($this->duplicatePhones);
            $sampleSize = min(20, count($uniqueDuplicates));
            
            foreach (array_slice($uniqueDuplicates, 0, $sampleSize) as $phone) {
                $lead = Lead::where('phone', $phone)
                    ->orWhere('phone', '1' . $phone)
                    ->first();
                    
                if ($lead) {
                    $this->line("  $phone - Lead #{$lead->id} ({$lead->name}), Created: {$lead->created_at->format('Y-m-d')}");
                }
            }
            
            if (count($uniqueDuplicates) > $sampleSize) {
                $this->line("  ... and " . (count($uniqueDuplicates) - $sampleSize) . " more");
            }
        }
        
        // Database summary
        $this->newLine();
        $totalLeads = Lead::count();
        $csvImportLeads = Lead::whereIn('source', ['LQF_CSV', 'LQF_CSV_IMPORT'])->count();
        
        $this->info("📈 Database Status:");
        $this->line("   Total Leads: $totalLeads");
        $this->line("   CSV Import Leads: $csvImportLeads");
        
        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN - no data was imported");
            $this->info("To perform actual import, run without --dry-run");
        } else if ($this->stats['imported'] > 0) {
            $this->newLine();
            $this->info("✅ Successfully imported {$this->stats['imported']} leads!");
            $this->newLine();
            $this->info("Next Steps:");
            $this->line("1. Review imported leads: https://quotingfast-brain-ohio.onrender.com/leads");
            $this->line("2. Push to Vici: php artisan vici:push-new-leads");
            $this->line("3. Update vendor codes: php artisan vici:update-vendor-codes");
        }
    }
}

