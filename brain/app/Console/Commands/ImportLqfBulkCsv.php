<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Services\ViciDialerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportLqfBulkCsv extends Command
{
    protected $signature = 'lqf:import-bulk 
                          {--folder= : Folder path (default: ~/Downloads/LQF)}
                          {--dry-run : Run without actually importing}
                          {--push-to-vici : Also push leads to Vici after import}';

    protected $description = 'Bulk import historical LQF CSV files from a folder';

    private $viciService;
    private $stats = [
        'total_files' => 0,
        'total_rows' => 0,
        'imported' => 0,
        'skipped_duplicates' => 0,
        'errors' => 0,
        'vendors_created' => 0,
        'buyers_created' => 0
    ];

    public function __construct(ViciDialerService $viciService)
    {
        parent::__construct();
        $this->viciService = $viciService;
    }

    public function handle()
    {
        $folder = $this->option('folder') ?: '~/Downloads/LQF';
        $folder = str_replace('~', $_SERVER['HOME'], $folder);
        
        if (!is_dir($folder)) {
            $this->error("Folder not found: $folder");
            return 1;
        }

        $this->info("Starting LQF bulk import from: $folder");
        $this->info("Mode: " . ($this->option('dry-run') ? 'DRY RUN' : 'LIVE IMPORT'));
        
        // Get all CSV files
        $files = glob($folder . '/*.csv');
        
        if (empty($files)) {
            $this->error("No CSV files found in $folder");
            return 1;
        }

        $this->stats['total_files'] = count($files);
        $this->info("Found {$this->stats['total_files']} CSV files to process");

        // Get existing phone numbers for duplicate check
        $existingPhones = Lead::pluck('phone')->toArray();
        $this->info("Loaded " . count($existingPhones) . " existing phone numbers for duplicate check");

        // Process each file
        foreach ($files as $index => $file) {
            $fileNum = $index + 1;
            $this->info("\n[$fileNum/{$this->stats['total_files']}] Processing: " . basename($file));
            $this->processFile($file, $existingPhones);
        }

        // Display final stats
        $this->displayStats();

        return 0;
    }

    private function processFile($filepath, &$existingPhones)
    {
        if (!file_exists($filepath)) {
            $this->error("File not found: $filepath");
            $this->stats['errors']++;
            return;
        }

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $this->error("Cannot open file: $filepath");
            $this->stats['errors']++;
            return;
        }

        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            $this->error("Cannot read header from: $filepath");
            fclose($handle);
            $this->stats['errors']++;
            return;
        }

        // Map headers to indices
        $map = $this->mapHeaders($header);
        
        $fileStats = ['imported' => 0, 'skipped' => 0, 'errors' => 0];
        $rowNum = 1;

        // Process each row
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $this->stats['total_rows']++;

            try {
                // Extract phone
                $phone = $this->cleanPhone($row[$map['phone']] ?? '');
                
                if (empty($phone)) {
                    $this->warn("Row $rowNum: Missing phone number, skipping");
                    $fileStats['errors']++;
                    $this->stats['errors']++;
                    continue;
                }

                // Check for duplicate
                if (in_array($phone, $existingPhones)) {
                    $fileStats['skipped']++;
                    $this->stats['skipped_duplicates']++;
                    continue;
                }

                if (!$this->option('dry-run')) {
                    // Create lead data
                    $leadData = $this->mapRowToLead($row, $map);
                    
                    // Handle vendor/buyer creation
                    $this->handleVendorBuyer($leadData);
                    
                    // Create lead
                    $lead = Lead::create($leadData);
                    
                    // Push to Vici if requested
                    if ($this->option('push-to-vici')) {
                        $this->viciService->pushLead($lead);
                    }
                    
                    // Add to existing phones list
                    $existingPhones[] = $phone;
                }

                $fileStats['imported']++;
                $this->stats['imported']++;

            } catch (\Exception $e) {
                $this->error("Row $rowNum error: " . $e->getMessage());
                $fileStats['errors']++;
                $this->stats['errors']++;
            }
        }

        fclose($handle);

        $this->info(sprintf(
            "  → Imported: %d, Skipped: %d, Errors: %d",
            $fileStats['imported'],
            $fileStats['skipped'],
            $fileStats['errors']
        ));
    }

    private function mapHeaders($header)
    {
        $map = [];
        
        // Clean headers and create mapping
        foreach ($header as $index => $column) {
            $column = trim(strtolower($column));
            
            // Map LQF CSV columns to our fields
            if (str_contains($column, 'lead id')) $map['lead_id'] = $index;
            if (str_contains($column, 'timestamp')) $map['timestamp'] = $index;
            if (str_contains($column, 'vertical')) $map['vertical'] = $index;
            if (str_contains($column, 'buy price')) $map['cost'] = $index;
            if (str_contains($column, 'sell price')) $map['sell_price'] = $index;
            if (str_contains($column, 'vendor') && !str_contains($column, 'campaign') && !str_contains($column, 'status')) $map['vendor'] = $index;
            if (str_contains($column, 'vendor campaign')) $map['vendor_campaign'] = $index;
            if (str_contains($column, 'buyer') && !str_contains($column, 'campaign') && !str_contains($column, 'status')) $map['buyer'] = $index;
            if (str_contains($column, 'buyer campaign')) $map['buyer_campaign'] = $index;
            if (str_contains($column, 'leadid code')) $map['tcpa_lead_id'] = $index;
            if (str_contains($column, 'trusted form')) $map['trusted_form_cert'] = $index;
            if ($column === 'tcpa') $map['tcpa_compliant'] = $index;
            if (str_contains($column, 'first name')) $map['first_name'] = $index;
            if (str_contains($column, 'last name')) $map['last_name'] = $index;
            if ($column === 'email') $map['email'] = $index;
            if ($column === 'phone' && !isset($map['phone'])) $map['phone'] = $index;
            if ($column === 'address' && !str_contains($column, '2')) $map['address'] = $index;
            if ($column === 'city') $map['city'] = $index;
            if ($column === 'state') $map['state'] = $index;
            if (str_contains($column, 'zip')) $map['zip_code'] = $index;
            if (str_contains($column, 'ip address')) $map['ip_address'] = $index;
            if (str_contains($column, 'user agent')) $map['user_agent'] = $index;
            if (str_contains($column, 'landing page')) $map['landing_page_url'] = $index;
            if (str_contains($column, 'source id')) $map['source_id'] = $index;
            if (str_contains($column, 'offer id')) $map['offer_id'] = $index;
        }

        return $map;
    }

    private function mapRowToLead($row, $map)
    {
        // Parse timestamp
        $timestamp = null;
        if (isset($map['timestamp']) && !empty($row[$map['timestamp']])) {
            try {
                $timestamp = Carbon::parse($row[$map['timestamp']]);
            } catch (\Exception $e) {
                $timestamp = now();
            }
        }

        // Parse vertical/type
        $vertical = $row[$map['vertical']] ?? '';
        $type = 'auto'; // default
        if (stripos($vertical, 'home') !== false) {
            $type = 'home';
        } elseif (stripos($vertical, 'auto') !== false) {
            $type = 'auto';
        }

        // Build lead data
        $leadData = [
            'source' => 'LQF',
            'type' => $type,
            'status' => 'new',
            
            // Contact info
            'first_name' => $row[$map['first_name']] ?? null,
            'last_name' => $row[$map['last_name']] ?? null,
            'phone' => $this->cleanPhone($row[$map['phone']] ?? ''),
            'email' => $row[$map['email']] ?? null,
            'address' => $row[$map['address']] ?? null,
            'city' => $row[$map['city']] ?? null,
            'state' => $row[$map['state']] ?? null,
            'zip_code' => $row[$map['zip_code']] ?? null,
            
            // Vendor/Buyer info
            'vendor_name' => $this->cleanVendorName($row[$map['vendor']] ?? null),
            'vendor_campaign' => $row[$map['vendor_campaign']] ?? null,
            'cost' => $this->parseDecimal($row[$map['cost']] ?? null),
            'buyer_name' => $this->cleanBuyerName($row[$map['buyer']] ?? null),
            'buyer_campaign' => $row[$map['buyer_campaign']] ?? null,
            'sell_price' => $this->parseDecimal($row[$map['sell_price']] ?? null),
            
            // TCPA
            'tcpa_lead_id' => $row[$map['tcpa_lead_id']] ?? null,
            'trusted_form_cert' => $row[$map['trusted_form_cert']] ?? null,
            'tcpa_compliant' => $this->parseTcpa($row[$map['tcpa_compliant']] ?? null),
            
            // Tracking
            'ip_address' => $row[$map['ip_address']] ?? null,
            'user_agent' => $row[$map['user_agent']] ?? null,
            'landing_page_url' => $row[$map['landing_page_url']] ?? null,
            
            // Timestamps
            'created_at' => $timestamp ?: now(),
            'updated_at' => now(),
        ];

        // Store additional data in meta
        $meta = [];
        if (isset($map['source_id']) && !empty($row[$map['source_id']])) {
            $meta['source_id'] = $row[$map['source_id']];
        }
        if (isset($map['offer_id']) && !empty($row[$map['offer_id']])) {
            $meta['offer_id'] = $row[$map['offer_id']];
        }
        if (isset($map['lead_id']) && !empty($row[$map['lead_id']])) {
            $meta['lqf_lead_id'] = $row[$map['lead_id']];
        }
        
        if (!empty($meta)) {
            $leadData['meta'] = json_encode($meta);
        }

        // Generate external lead ID
        $leadData['external_lead_id'] = Lead::generateExternalLeadId();

        return $leadData;
    }

    private function cleanPhone($phone)
    {
        // Remove all non-digits
        $phone = preg_replace('/\D/', '', $phone);
        
        // Remove leading 1 if 11 digits
        if (strlen($phone) == 11 && $phone[0] == '1') {
            $phone = substr($phone, 1);
        }
        
        // Return only if 10 digits
        return strlen($phone) == 10 ? $phone : '';
    }

    private function cleanVendorName($name)
    {
        if (empty($name)) return null;
        
        // Remove extra info after dash (e.g., "Quinn Street - Quinn Street Auto 2" -> "Quinn Street")
        if (strpos($name, ' - ') !== false) {
            $parts = explode(' - ', $name);
            return trim($parts[0]);
        }
        
        return trim($name);
    }

    private function cleanBuyerName($name)
    {
        if (empty($name)) return null;
        
        // Remove parentheses content (e.g., "What If Media Group, LLC () - Auto" -> "What If Media Group, LLC")
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        
        // Remove " - Auto" or " - Home" suffixes
        $name = preg_replace('/\s*-\s*(Auto|Home)\s*$/i', '', $name);
        
        return trim($name);
    }

    private function parseDecimal($value)
    {
        if (empty($value)) return null;
        
        // Remove dollar signs and commas
        $value = str_replace(['$', ','], '', $value);
        
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseTcpa($value)
    {
        if (empty($value)) return false;
        
        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', '1', 'y']);
    }

    private function handleVendorBuyer($leadData)
    {
        // Auto-create vendor if doesn't exist
        if (!empty($leadData['vendor_name'])) {
            $vendor = Vendor::firstOrCreate(
                ['name' => $leadData['vendor_name']],
                [
                    'campaigns' => [],
                    'active' => true
                ]
            );
            
            if ($vendor->wasRecentlyCreated) {
                $this->stats['vendors_created']++;
                $this->info("  → Created new vendor: {$leadData['vendor_name']}");
            }
            
            // Add campaign to vendor
            if (!empty($leadData['vendor_campaign'])) {
                $vendor->addCampaign($leadData['vendor_campaign']);
            }
        }
        
        // Auto-create buyer if doesn't exist (using the existing Buyer model)
        if (!empty($leadData['buyer_name'])) {
            $buyer = Buyer::firstOrCreate(
                ['name' => $leadData['buyer_name']],
                [
                    'campaigns' => [],
                    'active' => true
                ]
            );
            
            if ($buyer->wasRecentlyCreated) {
                $this->stats['buyers_created']++;
                $this->info("  → Created new buyer: {$leadData['buyer_name']}");
            }
            
            // Add campaign to buyer
            if (!empty($leadData['buyer_campaign'])) {
                $buyer->addCampaign($leadData['buyer_campaign']);
            }
        }
    }

    private function displayStats()
    {
        $this->newLine();
        $this->info("=== IMPORT COMPLETE ===");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Files Processed', $this->stats['total_files']],
                ['Total Rows', $this->stats['total_rows']],
                ['Imported', $this->stats['imported']],
                ['Skipped (Duplicates)', $this->stats['skipped_duplicates']],
                ['Errors', $this->stats['errors']],
                ['New Vendors', $this->stats['vendors_created']],
                ['New Buyers', $this->stats['buyers_created']],
            ]
        );
        
        if ($this->option('dry-run')) {
            $this->warn("This was a DRY RUN - no data was actually imported");
        }
    }
}
