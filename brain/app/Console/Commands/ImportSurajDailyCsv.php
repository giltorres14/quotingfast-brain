<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSurajDailyCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suraj:import-daily 
                           {file : Path to Suraj CSV file}
                           {--dry-run : Preview without importing}
                           {--skip-duplicates : Skip duplicate phone numbers}
                           {--push-to-vici : Push imported leads to Vici after import}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import daily Suraj CSV file into Brain and optionally push to Vici';
    
    private $stats = [
        'total_rows' => 0,
        'imported' => 0,
        'duplicates' => 0,
        'invalid' => 0,
        'errors' => 0
    ];
    
    private $duplicatePhones = [];
    
    /**
     * Common Suraj CSV column mappings
     * Update these based on your actual headers
     */
    private $surajColumnMap = [
        // Common variations - will auto-detect
        'phone' => ['phone', 'phone_number', 'telephone', 'mobile', 'cell'],
        'first_name' => ['first_name', 'firstname', 'fname', 'first'],
        'last_name' => ['last_name', 'lastname', 'lname', 'last'],
        'email' => ['email', 'email_address', 'e-mail'],
        'address' => ['address', 'address1', 'street', 'street_address'],
        'city' => ['city', 'town'],
        'state' => ['state', 'province', 'st'],
        'zip' => ['zip', 'zip_code', 'zipcode', 'postal_code', 'postal'],
        'dob' => ['dob', 'date_of_birth', 'birthdate', 'birth_date'],
        'gender' => ['gender', 'sex'],
        'source' => ['source', 'lead_source', 'origin'],
        'id' => ['id', 'lead_id', 'record_id', 'suraj_id']
    ];
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $skipDuplicates = $this->option('skip-duplicates');
        $pushToVici = $this->option('push-to-vici');
        
        // Validate file
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }
        
        $this->info("========================================");
        $this->info("SURAJ DAILY CSV IMPORT");
        $this->info("========================================");
        $this->newLine();
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be saved");
        } else {
            $this->info("💾 LIVE MODE - Data will be imported");
        }
        
        $this->info("📁 CSV File: $file");
        $this->info("📋 Source: Suraj");
        $this->info("🔄 Skip Duplicates: " . ($skipDuplicates ? 'Yes' : 'No'));
        $this->info("📤 Push to Vici: " . ($pushToVici ? 'Yes' : 'No'));
        $this->newLine();
        
        // Process CSV
        $this->processCSV($file, $dryRun, $skipDuplicates);
        
        // Show final statistics
        $this->showFinalStatistics($dryRun);
        
        // Push to Vici if requested
        if ($pushToVici && !$dryRun && $this->stats['imported'] > 0) {
            $this->pushToVici();
        }
        
        return 0;
    }
    
    /**
     * Process the CSV file
     */
    private function processCSV($file, $dryRun, $skipDuplicates)
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
        
        // Show detected headers
        $this->info("📋 CSV Headers Detected:");
        foreach ($headers as $index => $header) {
            $this->line("   [$index] $header");
        }
        $this->newLine();
        
        // Map columns automatically
        $columnMap = $this->autoMapColumns($headers);
        
        if (!isset($columnMap['phone'])) {
            $this->error("Could not find phone column in CSV");
            $this->info("Headers found: " . implode(', ', $headers));
            $this->info("Please ensure CSV has a column with 'phone' in the name");
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
        
        // Load existing phones if skipping duplicates
        $existingPhones = [];
        if ($skipDuplicates) {
            $this->info("📱 Loading existing phone numbers...");
            $existingPhones = $this->loadExistingPhones();
            $this->info("   Found " . count($existingPhones) . " existing phone numbers");
            $this->newLine();
        }
        
        // Process rows
        $this->info("🔄 Processing CSV rows...");
        
        $bar = $this->output->createProgressBar();
        $bar->start();
        
        $rowNumber = 1;
        $importedLeadIds = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $this->stats['total_rows']++;
            
            $result = $this->processRow($row, $columnMap, $rowNumber, $dryRun, $skipDuplicates, $existingPhones);
            
            if ($result && !$dryRun) {
                $importedLeadIds[] = $result;
            }
            
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
        
        // Store imported lead IDs for Vici push
        if (!empty($importedLeadIds)) {
            $this->importedLeadIds = $importedLeadIds;
        }
    }
    
    /**
     * Auto-map columns based on header names
     */
    private function autoMapColumns($headers)
    {
        $columnMap = [];
        
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            // Check each field type
            foreach ($this->surajColumnMap as $field => $variations) {
                foreach ($variations as $variation) {
                    if (strpos($headerLower, $variation) !== false) {
                        $columnMap[$field] = $index;
                        break 2; // Found match, move to next header
                    }
                }
            }
        }
        
        return $columnMap;
    }
    
    /**
     * Load existing phone numbers
     */
    private function loadExistingPhones()
    {
        return Lead::pluck('phone')
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
            ->flip()
            ->toArray();
    }
    
    /**
     * Process a single CSV row
     */
    private function processRow($row, $columnMap, $rowNumber, $dryRun, $skipDuplicates, &$existingPhones)
    {
        try {
            // Extract and clean phone
            $phone = $this->extractPhone($row, $columnMap);
            
            if (!$phone) {
                $this->stats['invalid']++;
                if ($this->output->isVerbose()) {
                    $this->warn("Row $rowNumber: Invalid phone number");
                }
                return null;
            }
            
            // Check for duplicate
            if ($skipDuplicates && isset($existingPhones[$phone])) {
                $this->stats['duplicates']++;
                $this->duplicatePhones[] = $phone;
                
                if ($this->output->isVerbose()) {
                    $this->warn("Row $rowNumber: Duplicate phone $phone - SKIPPED");
                }
                return null;
            }
            
            // Build lead data
            $leadData = $this->buildLeadData($row, $columnMap, $phone);
            
            // Import the lead
            if (!$dryRun) {
                $lead = Lead::create($leadData);
                
                // Add to existing phones
                if ($skipDuplicates) {
                    $existingPhones[$phone] = true;
                }
                
                $this->stats['imported']++;
                
                if ($this->output->isVerbose()) {
                    $this->info("✅ Row $rowNumber: Imported - Lead #{$lead->id}");
                }
                
                return $lead->id;
            } else {
                $this->stats['imported']++;
                return null;
            }
            
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("Row $rowNumber: " . $e->getMessage());
            return null;
        }
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
            'source' => 'SURAJ_DAILY',
            'type' => 'auto',
            'campaign_id' => 'SURAJ_' . date('Y-m-d'),
            'external_lead_id' => Lead::generateExternalLeadId(),
            'created_at' => now()
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
            'gender' => 'gender'
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
        
        // Store Suraj ID if available
        if (isset($columnMap['id']) && isset($row[$columnMap['id']])) {
            $surajId = trim($row[$columnMap['id']]);
            if (!empty($surajId)) {
                $leadData['meta'] = json_encode([
                    'suraj_id' => $surajId,
                    'import_date' => now()->toISOString(),
                    'import_file' => basename($this->argument('file'))
                ]);
            }
        }
        
        return $leadData;
    }
    
    /**
     * Push imported leads to Vici
     */
    private function pushToVici()
    {
        $this->newLine();
        $this->info("📤 Pushing imported leads to Vici...");
        
        try {
            // Get today's imported leads
            $leads = Lead::where('source', 'SURAJ_DAILY')
                ->where('campaign_id', 'SURAJ_' . date('Y-m-d'))
                ->whereDate('created_at', today())
                ->get();
            
            if ($leads->isEmpty()) {
                $this->warn("No leads to push to Vici");
                return;
            }
            
            $this->info("Pushing {$leads->count()} leads to Vici List 101...");
            
            // Use ViciDialerService to push leads
            $viciService = app(\App\Services\ViciDialerService::class);
            $pushed = 0;
            
            foreach ($leads as $lead) {
                try {
                    $viciService->pushLead($lead);
                    $pushed++;
                } catch (\Exception $e) {
                    $this->error("Failed to push lead #{$lead->id}: " . $e->getMessage());
                }
            }
            
            $this->info("✅ Successfully pushed $pushed leads to Vici");
            
        } catch (\Exception $e) {
            $this->error("Error pushing to Vici: " . $e->getMessage());
        }
    }
    
    /**
     * Show final statistics
     */
    private function showFinalStatistics($dryRun)
    {
        $this->newLine();
        $this->info("========================================");
        $this->info("IMPORT COMPLETE");
        $this->info("========================================");
        $this->newLine();
        
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
        
        if ($this->stats['duplicates'] > 0 && $this->output->isVerbose()) {
            $this->newLine();
            $this->warn("Duplicate Phone Numbers Found:");
            $uniqueDuplicates = array_unique($this->duplicatePhones);
            foreach (array_slice($uniqueDuplicates, 0, 10) as $phone) {
                $this->line("  $phone");
            }
            if (count($uniqueDuplicates) > 10) {
                $this->line("  ... and " . (count($uniqueDuplicates) - 10) . " more");
            }
        }
        
        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN - no data was imported");
            $this->info("To perform actual import, run without --dry-run");
        } else if ($this->stats['imported'] > 0) {
            $this->newLine();
            $this->info("✅ Successfully imported {$this->stats['imported']} Suraj leads!");
            
            if (!$this->option('push-to-vici')) {
                $this->newLine();
                $this->info("To push these leads to Vici, run:");
                $this->line("php artisan vici:push-new-leads --source=SURAJ_DAILY");
            }
        }
    }
}


