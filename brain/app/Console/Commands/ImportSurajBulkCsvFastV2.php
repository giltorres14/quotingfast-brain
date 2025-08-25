<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSurajBulkCsvFastV2 extends Command
{
    protected $signature = 'suraj:bulk-import-v2 
                            {folder : The folder containing CSV files}
                            {--pattern=*.csv : File pattern to match}
                            {--batch-size=500 : Number of records to insert at once}
                            {--skip-duplicates : Skip duplicate phone numbers}
                            {--check-realtime : Check duplicates in real-time (more accurate but slower)}';

    protected $description = 'Import Suraj bulk CSV files - Version 2 with better duplicate handling';

    private $vendorsCache = [];
    private $buyersCache = [];
    private $campaignsCache = [];
    private $processedPhones = []; // Track phones processed in THIS run
    private $checkRealtime = false;

    public function handle()
    {
        $folder = $this->argument('folder');
        $pattern = $this->option('pattern');
        $batchSize = (int) $this->option('batch-size');
        $skipDuplicates = $this->option('skip-duplicates');
        $this->checkRealtime = $this->option('check-realtime');
        
        $this->info("========================================");
        $this->info("SURAJ BULK CSV IMPORT - V2 FAST");
        $this->info("========================================");
        $this->newLine();
        
        $this->info("📁 Folder: $folder");
        $this->info("📋 Pattern: " . (is_array($pattern) ? implode(', ', $pattern) : $pattern));
        $this->info("⚡ Batch Size: $batchSize");
        $this->info("🔄 Skip Duplicates: " . ($skipDuplicates ? 'YES' : 'NO'));
        $this->info("🔍 Real-time Check: " . ($this->checkRealtime ? 'YES' : 'NO'));
        $this->newLine();
        
        // Get CSV files
        $files = $this->getCSVFiles($folder, $pattern);
        
        if (empty($files)) {
            $this->error("No CSV files found matching pattern: " . (is_array($pattern) ? implode(', ', $pattern) : $pattern));
            return 1;
        }
        
        $this->info("📂 Found " . count($files) . " CSV files to process");
        $this->newLine();
        
        // Pre-load caches
        $this->preloadCaches();
        
        $totalImported = 0;
        $totalDuplicates = 0;
        $totalErrors = 0;
        $startTime = microtime(true);
        
        foreach ($files as $index => $file) {
            $this->info("📄 File " . ($index + 1) . "/" . count($files) . ": " . basename($file));
            $result = $this->processFile($file, $batchSize, $skipDuplicates);
            $totalImported += $result['imported'];
            $totalDuplicates += $result['duplicates'];
            $totalErrors += $result['errors'];
            
            // Show progress
            $this->info("   Cumulative: {$totalImported} imported, {$totalDuplicates} duplicates");
            $this->newLine();
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
        $this->info("⚡ Speed: " . round($totalImported / max($duration, 1), 2) . " leads/second");
        
        return 0;
    }
    
    private function getCSVFiles($folder, $pattern)
    {
        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $files = [];
        
        foreach ($patterns as $p) {
            $path = rtrim($folder, '/') . '/' . $p;
            $found = glob($path);
            if ($found) {
                $files = array_merge($files, $found);
            }
        }
        
        return array_unique($files);
    }
    
    private function preloadCaches()
    {
        $this->info("📦 Pre-loading caches...");
        
        // Load vendors
        $vendors = Vendor::pluck('id', 'name')->toArray();
        $this->vendorsCache = $vendors;
        $this->info("   Vendors: " . count($vendors));
        
        // Load buyers
        $buyers = Buyer::pluck('id', 'name')->toArray();
        $this->buyersCache = $buyers;
        $this->info("   Buyers: " . count($buyers));
        
        // Load campaigns
        $campaigns = Campaign::pluck('id', 'campaign_id')->toArray();
        $this->campaignsCache = $campaigns;
        $this->info("   Campaigns: " . count($campaigns));
        
        $this->newLine();
    }
    
    private function processFile($filepath, $batchSize, $skipDuplicates)
    {
        $filename = basename($filepath);
        $fileDate = $this->extractDateFromFilename($filename);
        
        $this->info("   Processing: Size " . $this->formatBytes(filesize($filepath)));
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $this->error("   Failed to open file");
            return ['imported' => 0, 'duplicates' => 0, 'errors' => 0];
        }
        
        // Read headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['imported' => 0, 'duplicates' => 0, 'errors' => 0];
        }
        
        $columnMap = $this->autoMapColumns($headers);
        
        $batch = [];
        $imported = 0;
        $duplicates = 0;
        $errors = 0;
        $rowNumber = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            try {
                // Extract phone
                $phone = $this->extractPhone($row, $columnMap);
                if (!$phone) {
                    continue;
                }
                
                // Check duplicate
                if ($skipDuplicates) {
                    $isDuplicate = false;
                    
                    // First check our in-memory processed phones
                    if (isset($this->processedPhones[$phone])) {
                        $isDuplicate = true;
                    }
                    // Then check database if real-time checking is enabled
                    elseif ($this->checkRealtime && Lead::where('phone', $phone)->exists()) {
                        $isDuplicate = true;
                    }
                    
                    if ($isDuplicate) {
                        $duplicates++;
                        continue;
                    }
                }
                
                // Build lead data
                $leadData = $this->buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers);
                
                // Add to batch
                $batch[] = $leadData;
                $this->processedPhones[$phone] = true;
                
                // Insert batch when it reaches the batch size
                if (count($batch) >= $batchSize) {
                    $inserted = $this->insertBatchSmart($batch, $skipDuplicates);
                    $imported += $inserted['success'];
                    $duplicates += $inserted['duplicates'];
                    $errors += $inserted['errors'];
                    $batch = [];
                    
                    // Show progress every batch
                    if ($imported % 1000 == 0) {
                        $this->info("   Progress: {$imported} imported...");
                    }
                }
                
            } catch (\Exception $e) {
                $errors++;
                Log::error("Error in row $rowNumber: " . $e->getMessage());
            }
        }
        
        // Insert remaining batch
        if (!empty($batch)) {
            $inserted = $this->insertBatchSmart($batch, $skipDuplicates);
            $imported += $inserted['success'];
            $duplicates += $inserted['duplicates'];
            $errors += $inserted['errors'];
        }
        
        fclose($handle);
        
        $this->info("   ✅ Imported: $imported | 🚫 Duplicates: $duplicates | ❌ Errors: $errors");
        
        return [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'errors' => $errors
        ];
    }
    
    /**
     * Smart batch insert with duplicate handling
     */
    private function insertBatchSmart($batch, $skipDuplicates)
    {
        if (empty($batch)) {
            return ['success' => 0, 'duplicates' => 0, 'errors' => 0];
        }
        
        $success = 0;
        $duplicates = 0;
        $errors = 0;
        $now = Carbon::now();
        
        // Prepare data
        $insertData = [];
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
        
        if ($skipDuplicates) {
            // Use upsert to handle duplicates at database level
            // This will insert new records and ignore duplicates
            try {
                // PostgreSQL/MySQL compatible upsert
                $chunks = array_chunk($insertData, 100); // Smaller chunks for upsert
                foreach ($chunks as $chunk) {
                    $inserted = DB::table('leads')->insertOrIgnore($chunk);
                    $success += $inserted;
                    $duplicates += (count($chunk) - $inserted);
                }
            } catch (\Exception $e) {
                Log::error("Batch upsert failed: " . $e->getMessage());
                // Fall back to individual inserts
                foreach ($insertData as $data) {
                    try {
                        if (DB::table('leads')->where('phone', $data['phone'])->doesntExist()) {
                            DB::table('leads')->insert($data);
                            $success++;
                        } else {
                            $duplicates++;
                        }
                    } catch (\Exception $e2) {
                        $errors++;
                        Log::error("Failed to insert lead: " . $e2->getMessage());
                    }
                }
            }
        } else {
            // Regular insert without duplicate checking
            try {
                DB::table('leads')->insert($insertData);
                $success = count($insertData);
            } catch (\Exception $e) {
                Log::error("Batch insert failed: " . $e->getMessage());
                // Try individual inserts
                foreach ($insertData as $data) {
                    try {
                        DB::table('leads')->insert($data);
                        $success++;
                    } catch (\Exception $e2) {
                        $errors++;
                    }
                }
            }
        }
        
        return ['success' => $success, 'duplicates' => $duplicates, 'errors' => $errors];
    }
    
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
                // Ignore
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
                // Ignore
            }
        }
        
        // Store vendor_campaign_id in meta if it exists
        if (!empty($leadData['vendor_campaign_id'])) {
            $meta = isset($leadData['meta']) ? (is_array($leadData['meta']) ? $leadData['meta'] : json_decode($leadData['meta'], true)) : [];
            $meta['vendor_campaign_id'] = $leadData['vendor_campaign_id'];
            $leadData['meta'] = json_encode($meta);
            unset($leadData['vendor_campaign_id']); // Remove from main data
        }
        
        // Handle campaign
        if (!empty($leadData['campaign_id'])) {
            $campaignId = $leadData['campaign_id'];
            if (!isset($this->campaignsCache[$campaignId])) {
                $campaign = Campaign::autoCreateFromId($campaignId);
                $this->campaignsCache[$campaignId] = $campaign->id;
                
                // Link campaign to buyer if we have buyer info
                if (!empty($leadData['buyer_name']) && $campaign) {
                    $buyer = Buyer::where('name', $leadData['buyer_name'])->first();
                    if ($buyer) {
                        $campaign->addBuyer($buyer, $campaignId);
                    }
                }
            }
        }
    }
    
    // Copy other helper methods from original file...
    private function extractPhone($row, $columnMap) 
    {
        $phoneIndex = $columnMap['phone'] ?? $columnMap['phone_number'] ?? null;
        if ($phoneIndex === null || !isset($row[$phoneIndex])) {
            return null;
        }
        
        $phone = preg_replace('/[^0-9]/', '', $row[$phoneIndex]);
        if (strlen($phone) == 10) {
            return $phone;
        }
        if (strlen($phone) == 11 && $phone[0] == '1') {
            return substr($phone, 1);
        }
        
        return null;
    }
    
    private function autoMapColumns($headers)
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim($header));
            $normalized = str_replace(['_', '-', ' '], '', $normalized);
            
            // Map common variations
            if (in_array($normalized, ['phone', 'phonenumber', 'phonenum', 'cellphone', 'mobile'])) {
                $map['phone'] = $index;
            } elseif (in_array($normalized, ['firstname', 'fname'])) {
                $map['first_name'] = $index;
            } elseif (in_array($normalized, ['lastname', 'lname'])) {
                $map['last_name'] = $index;
            } elseif ($normalized == 'email') {
                $map['email'] = $index;
            } elseif ($normalized == 'timestamp') {
                $map['timestamp'] = $index;
            } elseif ($normalized == 'buyercampaignid') {
                $map['campaign_id'] = $index;
            } elseif ($normalized == 'buyerid') {
                $map['buyer_id'] = $index;
            } elseif ($normalized == 'buyername') {
                $map['buyer_name'] = $index;
            } elseif ($normalized == 'vendorid') {
                $map['vendor_id'] = $index;
            } elseif ($normalized == 'vendorname') {
                $map['vendor_name'] = $index;
            } elseif ($normalized == 'vendorcampaignid') {
                $map['vendor_campaign_id'] = $index;
            }
        }
        
        return $map;
    }
    
    private function buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers)
    {
        $leadData = [
            'external_lead_id' => Lead::generateExternalLeadId(),
            'phone' => $phone,
            'source' => 'SURAJ_BULK',
            'type' => 'auto',
            'received_at' => $fileDate ? Carbon::parse($fileDate) : now(),
            'joined_at' => now(),
            'tenant_id' => 1,
            'tcpa_compliant' => true,
        ];
        
        // Map fields
        $fieldMapping = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'campaign_id' => 'campaign_id',
            'buyer_id' => 'buyer_id',
            'buyer_name' => 'buyer_name',
            'vendor_id' => 'vendor_id',
            'vendor_name' => 'vendor_name',
            'vendor_campaign_id' => 'vendor_campaign_id',
        ];
        
        foreach ($fieldMapping as $csvField => $dbField) {
            if (isset($columnMap[$csvField])) {
                $value = trim($row[$columnMap[$csvField]] ?? '');
                if ($value !== '') {
                    // Clean numeric IDs that have .0 suffix
                    if (in_array($csvField, ['campaign_id', 'buyer_id', 'vendor_id', 'vendor_campaign_id']) && is_numeric($value)) {
                        $value = rtrim(rtrim($value, '0'), '.');
                    }
                    $leadData[$dbField] = $value;
                }
            }
        }
        
        // Build name
        $firstName = $leadData['first_name'] ?? '';
        $lastName = $leadData['last_name'] ?? '';
        $leadData['name'] = trim("$firstName $lastName") ?: 'Unknown';
        
        // Store metadata
        $meta = [
            'source_file' => $filename,
            'file_date' => $fileDate,
            'vendor_id' => $leadData['vendor_id'] ?? null,
            'buyer_id' => $leadData['buyer_id'] ?? null,
        ];
        
        // Handle opt_in_date from timestamp
        if (isset($columnMap['timestamp'])) {
            $timestamp = trim($row[$columnMap['timestamp']] ?? '');
            if ($timestamp) {
                try {
                    $leadData['opt_in_date'] = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // Skip if can't parse
                }
            }
        }
        
        $leadData['meta'] = $meta;
        
        // Store full row as payload
        $payload = [];
        foreach ($headers as $index => $header) {
            if (isset($row[$index])) {
                $payload[$header] = $row[$index];
            }
        }
        $leadData['payload'] = $payload;
        
        // Remove vendor_id and buyer_id from main data (they're in meta)
        unset($leadData['vendor_id']);
        unset($leadData['buyer_id']);
        
        return $leadData;
    }
    
    private function extractDateFromFilename($filename)
    {
        if (preg_match('/(\d{4})[-_](\d{2})[-_](\d{2})/', $filename, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }
        return null;
    }
    
    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;




