<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportSurajBulkCsvFast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suraj:bulk-import-fast 
                            {folder : The folder containing CSV files}
                            {--pattern=*.csv : File pattern to match}
                            {--batch-size=500 : Number of records to insert at once}
                            {--skip-duplicates : Skip duplicate phone numbers}
                            {--push-to-vici : Push imported leads to Vici}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FAST bulk import Suraj CSV files using batch inserts';

    /**
     * Existing phone numbers to check for duplicates
     */
    private $existingPhones = [];
    
    /**
     * Vendors and buyers cache
     */
    private $vendorsCache = [];
    private $buyersCache = [];
    private $campaignsCache = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folder = $this->argument('folder');
        $pattern = $this->option('pattern');
        $batchSize = (int) $this->option('batch-size');
        $skipDuplicates = $this->option('skip-duplicates');
        $pushToVici = $this->option('push-to-vici');
        
        $this->info("========================================");
        $this->info("SURAJ BULK CSV IMPORT - FAST VERSION");
        $this->info("========================================");
        $this->newLine();
        
        $this->info("📁 Folder: $folder");
        $this->info("📋 Pattern: " . (is_array($pattern) ? implode(', ', $pattern) : $pattern));
        $this->info("⚡ Batch Size: $batchSize");
        $this->info("🔄 Skip Duplicates: " . ($skipDuplicates ? 'YES' : 'NO'));
        $this->newLine();
        
        // Get CSV files
        $files = $this->getCSVFiles($folder, $pattern);
        
        if (empty($files)) {
            $this->error("No CSV files found matching pattern: $pattern");
            return 1;
        }
        
        $this->info("📂 Found " . count($files) . " CSV files to process");
        $this->newLine();
        
        // Load existing phone numbers if needed
        if ($skipDuplicates) {
            $this->loadExistingPhones();
        }
        
        // Pre-load vendors, buyers, campaigns
        $this->preloadCaches();
        
        $totalImported = 0;
        $totalDuplicates = 0;
        $totalErrors = 0;
        $startTime = microtime(true);
        
        foreach ($files as $file) {
            $result = $this->processFile($file, $batchSize, $skipDuplicates);
            $totalImported += $result['imported'];
            $totalDuplicates += $result['duplicates'];
            $totalErrors += $result['errors'];
        }
        
        $duration = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info("========================================");
        $this->info("IMPORT COMPLETE");
        $this->info("========================================");
        $this->info("✅ Imported: $totalImported");
        $this->info("🚫 Duplicates: $totalDuplicates");
        $this->info("❌ Errors: $totalErrors");
        $this->info("⏱️  Duration: {$duration} seconds");
        $this->info("📊 Speed: " . round($totalImported / max($duration, 1), 2) . " leads/second");
        
        return 0;
    }
    
    /**
     * Get CSV files from folder
     */
    private function getCSVFiles($folder, $pattern)
    {
        $fullPath = realpath($folder);
        if (!$fullPath || !is_dir($fullPath)) {
            return [];
        }
        
        // Handle array pattern
        if (is_array($pattern)) {
            $pattern = $pattern[0] ?? '*.csv';
        }
        
        $files = glob($fullPath . '/' . $pattern);
        
        // Sort by modification time, newest first
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return $files;
    }
    
    /**
     * Load existing phone numbers
     */
    private function loadExistingPhones()
    {
        $this->info("📱 Loading existing phone numbers...");
        
        // Use chunking for memory efficiency
        $count = 0;
        Lead::select('phone')->chunk(10000, function($leads) use (&$count) {
            foreach ($leads as $lead) {
                $this->existingPhones[$lead->phone] = true;
                $count++;
            }
        });
        
        $this->info("   Found " . number_format($count) . " existing phone numbers");
        $this->newLine();
    }
    
    /**
     * Preload caches
     */
    private function preloadCaches()
    {
        $this->info("📦 Preloading vendors, buyers, and campaigns...");
        
        // Load vendors
        $vendors = Vendor::all();
        foreach ($vendors as $vendor) {
            $this->vendorsCache[$vendor->name] = $vendor->id;
        }
        
        // Load buyers
        $buyers = Buyer::all();
        foreach ($buyers as $buyer) {
            $this->buyersCache[$buyer->name] = $buyer->id;
        }
        
        // Load campaigns
        $campaigns = Campaign::all();
        foreach ($campaigns as $campaign) {
            $this->campaignsCache[$campaign->id] = $campaign;
        }
        
        $this->info("   Loaded: " . count($this->vendorsCache) . " vendors, " . 
                   count($this->buyersCache) . " buyers, " . 
                   count($this->campaignsCache) . " campaigns");
        $this->newLine();
    }
    
    /**
     * Process a single CSV file
     */
    private function processFile($filepath, $batchSize, $skipDuplicates)
    {
        $filename = basename($filepath);
        $fileDate = $this->extractDateFromFilename($filename);
        
        $this->info("📄 Processing: $filename");
        $this->info("   Date: $fileDate | Size: " . $this->formatBytes(filesize($filepath)));
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $this->error("   Failed to open file");
            return ['imported' => 0, 'duplicates' => 0, 'errors' => 1];
        }
        
        // Read headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['imported' => 0, 'duplicates' => 0, 'errors' => 1];
        }
        
        $columnMap = $this->autoMapColumns($headers);
        
        $batch = [];
        $imported = 0;
        $duplicates = 0;
        $errors = 0;
        $rowNumber = 1;
        
        // Process in batches
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            try {
                // Get phone number
                $phone = $this->extractPhone($row, $columnMap);
                if (!$phone) {
                    continue;
                }
                
                // Check duplicate
                if ($skipDuplicates && isset($this->existingPhones[$phone])) {
                    $duplicates++;
                    continue;
                }
                
                // Build lead data
                $leadData = $this->buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers);
                
                // Add to batch
                $batch[] = $leadData;
                $this->existingPhones[$phone] = true;
                
                // Insert batch when it reaches the batch size
                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch);
                    $imported += count($batch);
                    $batch = [];
                    
                    // Show progress
                    if ($imported % 1000 == 0) {
                        $this->info("   Progress: " . number_format($imported) . " imported...");
                    }
                }
                
            } catch (\Exception $e) {
                $errors++;
                Log::error("Error in row $rowNumber: " . $e->getMessage());
            }
        }
        
        // Insert remaining batch
        if (!empty($batch)) {
            $this->insertBatch($batch);
            $imported += count($batch);
        }
        
        fclose($handle);
        
        $this->info("   ✅ Imported: $imported | 🚫 Duplicates: $duplicates | ❌ Errors: $errors");
        $this->newLine();
        
        return [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'errors' => $errors
        ];
    }
    
    /**
     * Insert batch of leads
     */
    private function insertBatch($batch)
    {
        if (empty($batch)) {
            return;
        }
        
        // Prepare data for bulk insert
        $insertData = [];
        $now = Carbon::now();
        
        foreach ($batch as $lead) {
            // Handle vendor/buyer creation
            $this->handleVendorBuyer($lead);
            
            // Add timestamps
            $lead['created_at'] = $now;
            $lead['updated_at'] = $now;
            
            // Ensure all fields are properly formatted
            if (isset($lead['payload']) && is_array($lead['payload'])) {
                $lead['payload'] = json_encode($lead['payload']);
            }
            if (isset($lead['meta']) && is_array($lead['meta'])) {
                $lead['meta'] = json_encode($lead['meta']);
            }
            
            $insertData[] = $lead;
        }
        
        // Use DB::table for faster insertion
        try {
            DB::table('leads')->insert($insertData);
        } catch (\Exception $e) {
            // If batch fails, try inserting one by one
            foreach ($insertData as $data) {
                try {
                    DB::table('leads')->insert($data);
                } catch (\Exception $e2) {
                    Log::error("Failed to insert lead: " . $e2->getMessage());
                }
            }
        }
    }
    
    /**
     * Handle vendor/buyer creation
     */
    private function handleVendorBuyer(&$leadData)
    {
        // Handle vendor
        if (!empty($leadData['vendor_name']) && !isset($this->vendorsCache[$leadData['vendor_name']])) {
            try {
                $vendor = Vendor::firstOrCreate(
                    ['name' => $leadData['vendor_name']],
                    ['active' => true, 'notes' => 'Auto-created from Suraj import']
                );
                $this->vendorsCache[$leadData['vendor_name']] = $vendor->id;
            } catch (\Exception $e) {
                // Ignore duplicate errors
            }
        }
        
        // Handle buyer
        if (!empty($leadData['buyer_name']) && !isset($this->buyersCache[$leadData['buyer_name']])) {
            try {
                $buyer = Buyer::firstOrCreate(
                    ['name' => $leadData['buyer_name']],
                    ['active' => true, 'notes' => 'Auto-created from Suraj import']
                );
                $this->buyersCache[$leadData['buyer_name']] = $buyer->id;
            } catch (\Exception $e) {
                // Ignore duplicate errors
            }
        }
        
        // Remove fields that don't exist in leads table
        unset($leadData['vendor_id']);
        unset($leadData['buyer_id']);
        unset($leadData['buyer_campaign_id']);
    }
    
    /**
     * Auto-map columns
     */
    private function autoMapColumns($headers)
    {
        $columnMap = [];
        
        $mappings = [
            'phone' => ['phonenumber', 'phone_number', 'phone'],
            'first_name' => ['firstname', 'first_name'],
            'last_name' => ['lastname', 'last_name'],
            'email' => ['emailaddress', 'email_address', 'email'],
            'address' => ['mailaddress1', 'address'],
            'city' => ['cityname', 'city'],
            'state' => ['provincestatename', 'state'],
            'zip' => ['postalzipcode', 'zip', 'zip_code'],
            'gender' => ['gender'],
            'vendor_id' => ['vendor_id'],
            'vendor_name' => ['vendor_name'],
            'vendor_campaign_id' => ['vendor_campaign_id'],
            'campaign_id' => ['buyer_campaign_id'],  // Column L
            'buyer_id' => ['buyer_id'],
            'buyer_name' => ['buyer_name'],
            'opt_in_date' => ['timestamp']  // Column B
        ];
        
        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($mappings as $field => $variations) {
                if (in_array($headerLower, $variations)) {
                    $columnMap[$field] = $index;
                    break;
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
        if (!isset($columnMap['phone'])) {
            return null;
        }
        
        $phone = trim($row[$columnMap['phone']] ?? '');
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) != 10) {
            return null;
        }
        
        return $phone;
    }
    
    /**
     * Build lead data
     */
    private function buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers)
    {
        $leadData = [
            'phone' => $phone,
            'source' => 'SURAJ_BULK',
            'type' => 'auto',
            'external_lead_id' => Lead::generateExternalLeadId(),
            'tcpa_compliant' => true,
            'tenant_id' => 1
        ];
        
        // Map fields
        $fieldMapping = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'zip_code',
            'vendor_name' => 'vendor_name',
            'vendor_campaign_id' => 'vendor_campaign_id',
            'campaign_id' => 'campaign_id',
            'buyer_name' => 'buyer_name',
            'opt_in_date' => 'opt_in_date'
        ];
        
        foreach ($fieldMapping as $csvField => $dbField) {
            if (isset($columnMap[$csvField]) && isset($row[$columnMap[$csvField]])) {
                $value = trim($row[$columnMap[$csvField]]);
                if (!empty($value)) {
                    if ($dbField === 'opt_in_date') {
                        // Parse the timestamp
                        try {
                            $date = new \DateTime($value);
                            $leadData[$dbField] = $date->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            // Skip if can't parse
                        }
                    } else {
                        $leadData[$dbField] = $value;
                    }
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
        
        // Build payload (store full row data)
        $payload = [];
        foreach ($headers as $index => $header) {
            if (isset($row[$index])) {
                $payload[$header] = $row[$index];
            }
        }
        $leadData['payload'] = json_encode($payload);
        
        // Build meta
        $leadData['meta'] = json_encode([
            'import_file' => $filename,
            'import_date' => now()->toISOString(),
            'file_date' => $fileDate,
            'source' => 'Suraj Bulk Import'
        ]);
        
        return $leadData;
    }
    
    /**
     * Extract date from filename
     */
    private function extractDateFromFilename($filename)
    {
        // Try patterns like "10jun" or "2025-06-10"
        if (preg_match('/(\d{1,2})(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i', $filename, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = date('m', strtotime($matches[2]));
            $year = date('Y');
            return "$year-$month-$day";
        }
        
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $filename, $matches)) {
            return $matches[0];
        }
        
        return date('Y-m-d');
    }
    
    /**
     * Format bytes
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
