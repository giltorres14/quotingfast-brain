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

class ImportLqfBulkCsvFast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lqf:bulk-import-fast 
                            {file : The CSV file to import}
                            {--limit=0 : Limit number of records to import (0 = all)}
                            {--batch-size=1000 : Number of records to insert at once}
                            {--skip-duplicates : Skip duplicate phone numbers}
                            {--dry-run : Preview import without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import LQF (LeadsQuotingFast) CSV export file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $limit = (int) $this->option('limit');
        $skipDuplicates = $this->option('skip-duplicates');
        $dryRun = $this->option('dry-run');
        
        $this->info("========================================");
        $this->info("LQF CSV IMPORT");
        $this->info("========================================");
        $this->newLine();
        
        $this->info("📁 File: $file");
        $this->info("📋 Limit: " . ($limit > 0 ? $limit : 'All records'));
        $this->info("🔄 Skip Duplicates: " . ($skipDuplicates ? 'YES' : 'NO'));
        $this->info("🧪 Dry Run: " . ($dryRun ? 'YES (preview only)' : 'NO'));
        $this->newLine();
        
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }
        
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Could not open file: $file");
            return 1;
        }
        
        // Read headers
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if (!$headers) {
            $this->error("Could not read headers from file");
            fclose($handle);
            return 1;
        }
        
        $this->info("📊 Found " . count($headers) . " columns");
        $this->newLine();
        
        // Map headers to array indices
        $columnMap = $this->mapColumns($headers);
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $rowNumber = 1;
        
        // Load existing phones if needed
        $existingPhones = [];
        if ($skipDuplicates) {
            $this->info("Loading existing phone numbers...");
            $existingPhones = Lead::pluck('phone')->flip()->toArray();
            $this->info("Found " . count($existingPhones) . " existing phone numbers");
            $this->newLine();
        }
        
        $this->info("Processing records...");
        
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rowNumber++;
            
            // Apply limit if specified
            if ($limit > 0 && $imported >= $limit) {
                break;
            }
            
            try {
                // Extract phone
                $phone = $this->extractPhone($row, $columnMap);
                if (!$phone) {
                    $skipped++;
                    continue;
                }
                
                // Check for existing lead
                $existingLead = Lead::where('phone', $phone)->first();
                
                // Build lead data
                $leadData = $this->buildLeadData($row, $columnMap);
                
                if ($existingLead) {
                    // Check if it's a Suraj lead that should be replaced
                    if (in_array($existingLead->source, ['SURAJ_BULK', 'SURAJ'])) {
                        if ($dryRun) {
                            if ($imported < 3) {
                                $this->info("Would REPLACE Suraj lead:");
                                $this->info("  Existing ID: " . $existingLead->external_lead_id);
                                $this->info("  Name: " . $leadData['name']);
                                $this->info("  Phone: " . $leadData['phone']);
                                $this->info("  Old Source: " . $existingLead->source . " → LQF_BULK");
                                $this->newLine();
                            }
                        } else {
                            // Store old data in meta for audit
                            $meta = json_decode($leadData['meta'], true) ?? [];
                            $meta['replaced_from_suraj'] = [
                                'old_source' => $existingLead->source,
                                'old_name' => $existingLead->name,
                                'replaced_at' => now()->toIso8601String()
                            ];
                            $leadData['meta'] = json_encode($meta);
                            
                            // Update source to LQF_BULK
                            $leadData['source'] = 'LQF_BULK';
                            
                            // Keep the existing external_lead_id
                            unset($leadData['external_lead_id']);
                            
                            // Update the existing lead
                            $existingLead->update($leadData);
                            
                            $this->info("✓ REPLACED Suraj lead → LQF: {$existingLead->name} (ID: {$existingLead->external_lead_id})");
                        }
                        $imported++;
                    } else if ($skipDuplicates) {
                        // It's a duplicate from another source (like LQF), skip it
                        $skipped++;
                        if ($imported < 10) {
                            $this->warn("  Skipping duplicate (source: {$existingLead->source}): {$phone}");
                        }
                        continue;
                    } else {
                        // Not skipping duplicates, create anyway
                        if (!$dryRun) {
                            $this->createLead($leadData);
                            $this->warn("⚠ Created duplicate lead: {$leadData['name']} ({$phone})");
                        }
                        $imported++;
                    }
                } else {
                    // No existing lead, create new one
                    if ($dryRun) {
                        if ($imported < 3) {
                            $this->info("Preview NEW Record #" . ($imported + 1) . ":");
                            $this->info("  Name: " . $leadData['name']);
                            $this->info("  Phone: " . $leadData['phone']);
                            $this->info("  Email: " . ($leadData['email'] ?? 'N/A'));
                            $this->info("  Source: " . $leadData['source']);
                            $this->info("  Vendor: " . ($leadData['vendor_name'] ?? 'N/A'));
                            $this->info("  Buyer: " . ($leadData['buyer_name'] ?? 'N/A'));
                            $this->info("  Campaign ID: " . ($leadData['campaign_id'] ?? 'N/A'));
                            $this->newLine();
                        }
                    } else {
                        $this->createLead($leadData);
                        
                        // Add to existing phones cache
                        if ($skipDuplicates) {
                            $existingPhones[$phone] = true;
                        }
                    }
                    $imported++;
                }
                
                $imported++;
                
                // Show progress
                if ($imported % 100 == 0) {
                    $this->info("  Progress: $imported imported...");
                }
                
            } catch (\Exception $e) {
                $errors++;
                Log::error("LQF import error in row $rowNumber: " . $e->getMessage());
                if (!$dryRun) {
                    $this->error("Error in row $rowNumber: " . $e->getMessage());
                }
            }
        }
        
        fclose($handle);
        
        $this->newLine();
        $this->info("========================================");
        $this->info($dryRun ? "PREVIEW COMPLETE" : "IMPORT COMPLETE");
        $this->info("========================================");
        $this->info("✅ " . ($dryRun ? "Would import" : "Imported") . ": $imported");
        $this->info("🚫 Skipped: $skipped");
        $this->info("❌ Errors: $errors");
        
        return 0;
    }
    
    /**
     * Map column headers to indices
     */
    private function mapColumns($headers)
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim($header));
            $map[$normalized] = $index;
        }
        return $map;
    }
    
    /**
     * Extract phone number from row
     */
    private function extractPhone($row, $columnMap)
    {
        $phoneIndex = $columnMap['phone'] ?? null;
        if ($phoneIndex === null || !isset($row[$phoneIndex])) {
            return null;
        }
        
        $phone = preg_replace('/[^0-9]/', '', $row[$phoneIndex]);
        if (strlen($phone) != 10) {
            return null;
        }
        
        return $phone;
    }
    
    /**
     * Build lead data from CSV row
     */
    private function buildLeadData($row, $columnMap)
    {
        // Extract basic fields
        $leadData = [
            'external_lead_id' => Lead::generateExternalLeadId(),
            'jangle_lead_id' => $this->getValue($row, $columnMap, 'lead id'), // Column A
            'leadid_code' => $this->getValue($row, $columnMap, 'leadid code'), // Column P
            'source' => 'LQF',
            'type' => $this->extractType($row, $columnMap),
            'tenant_id' => 1,
            'tcpa_compliant' => $this->extractTcpa($row, $columnMap),
        ];
        
        // Personal information
        $leadData['first_name'] = $this->getValue($row, $columnMap, 'first name');
        $leadData['last_name'] = $this->getValue($row, $columnMap, 'last name');
        $leadData['name'] = trim($leadData['first_name'] . ' ' . $leadData['last_name']);
        $leadData['email'] = $this->getValue($row, $columnMap, 'email');
        $leadData['phone'] = $this->extractPhone($row, $columnMap);
        
        // Address information
        $leadData['address'] = $this->getValue($row, $columnMap, 'address');
        $leadData['city'] = $this->getValue($row, $columnMap, 'city');
        $leadData['state'] = $this->getValue($row, $columnMap, 'state');
        $leadData['zip_code'] = $this->getValue($row, $columnMap, 'zip code');
        
        // Vendor/Buyer information
        $leadData['vendor_name'] = $this->getValue($row, $columnMap, 'vendor');
        $leadData['buyer_name'] = $this->getValue($row, $columnMap, 'buyer');
        
        // Campaign information
        $buyerCampaign = $this->getValue($row, $columnMap, 'buyer campaign');
        if ($buyerCampaign) {
            // Extract campaign ID from buyer campaign string (usually contains ID)
            if (preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
                $leadData['campaign_id'] = $matches[1];
            }
        }
        
        // TCPA and tracking fields
        $leadData['trusted_form_cert'] = $this->getValue($row, $columnMap, 'trusted form cert url'); // Column Q
        $leadData['landing_page_url'] = $this->getValue($row, $columnMap, 'landing page url'); // Column S
        $leadData['tcpa_consent_text'] = $this->getValue($row, $columnMap, 'tcpa consent text'); // Column U
        $leadData['ip_address'] = $this->getValue($row, $columnMap, 'ip address');
        
        // Opt-in date from "Originally Created" field
        $originallyCreated = $this->getValue($row, $columnMap, 'originally created');
        if ($originallyCreated) {
            try {
                $leadData['opt_in_date'] = Carbon::parse($originallyCreated)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // If parse fails, use timestamp field
                $timestamp = $this->getValue($row, $columnMap, 'timestamp');
                if ($timestamp) {
                    try {
                        $leadData['opt_in_date'] = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
                    } catch (\Exception $e2) {
                        // Skip if both fail
                    }
                }
            }
        }
        
        // Build payload with all original data
        $payload = [];
        foreach ($columnMap as $field => $index) {
            if (isset($row[$index])) {
                $value = $row[$index];
                
                // Clean up numeric IDs in payload
                if (in_array($field, ['vendor_id', 'buyer_id', 'vendor campaign', 'buyer campaign', 'lead id', 'source id'])) {
                    if (is_numeric($value) && strpos($value, '.') !== false) {
                        $value = rtrim(rtrim($value, '0'), '.');
                    }
                    if (stripos($value, 'e+') !== false || stripos($value, 'e-') !== false) {
                        $value = sprintf("%.0f", floatval($value));
                    }
                }
                
                $payload[$field] = $value;
            }
        }
        
        // Parse and include the "Data" field which contains JSON
        $dataIndex = $columnMap['data'] ?? null;
        if ($dataIndex !== null && isset($row[$dataIndex])) {
            $jsonData = $row[$dataIndex];
            try {
                $parsedData = json_decode($jsonData, true);
                if ($parsedData) {
                    $payload['parsed_data'] = $parsedData;
                    
                    // Extract drivers and vehicles for storage
                    if (isset($parsedData['drivers'])) {
                        $leadData['drivers'] = json_encode($parsedData['drivers']);
                    }
                    if (isset($parsedData['vehicles'])) {
                        $leadData['vehicles'] = json_encode($parsedData['vehicles']);
                    }
                    if (isset($parsedData['requested_policy'])) {
                        $leadData['current_policy'] = json_encode($parsedData['requested_policy']);
                    }
                }
            } catch (\Exception $e) {
                // If JSON parse fails, store as is
            }
        }
        
        $leadData['payload'] = json_encode($payload);
        
        // Build meta information
        $meta = [
            'lead_id' => $this->getValue($row, $columnMap, 'lead id'),
            'vendor_campaign' => $this->getValue($row, $columnMap, 'vendor campaign'),
            'vendor_status' => $this->getValue($row, $columnMap, 'vendor status'),
            'buyer_campaign' => $this->getValue($row, $columnMap, 'buyer campaign'),
            'buyer_status' => $this->getValue($row, $columnMap, 'buyer status'),
            'buy_price' => $this->getValue($row, $columnMap, 'buy price'),
            'sell_price' => $this->getValue($row, $columnMap, 'sell price'),
            'source_id' => $this->getValue($row, $columnMap, 'source id'),
            'offer_id' => $this->getValue($row, $columnMap, 'offer id'),
            'leadid_code' => $this->getValue($row, $columnMap, 'leadid code'),
            'trusted_form_cert' => $this->getValue($row, $columnMap, 'trusted form cert url'),
            'tcpa_consent_text' => $this->getValue($row, $columnMap, 'tcpa consent text'),
            'landing_page' => $this->getValue($row, $columnMap, 'landing page url'),
            'ip_address' => $this->getValue($row, $columnMap, 'ip address'),
            'user_agent' => $this->getValue($row, $columnMap, 'user agent'),
            'import_source' => 'LQF Bulk Import',
            'import_date' => now()->toISOString()
        ];
        
        $leadData['meta'] = json_encode(array_filter($meta));
        
        return $leadData;
    }
    
    /**
     * Get value from row by column name
     */
    private function getValue($row, $columnMap, $columnName)
    {
        $index = $columnMap[$columnName] ?? null;
        if ($index === null || !isset($row[$index])) {
            return null;
        }
        
        $value = trim($row[$index]);
        
        // Clean up numeric IDs that Excel converted to floats
        if (in_array($columnName, ['lead id', 'vendor campaign', 'buyer campaign', 'source id', 'vendor_id', 'buyer_id', 'campaign_id'])) {
            // Remove .0 from the end if it's there
            if (is_numeric($value) && strpos($value, '.') !== false) {
                $value = rtrim(rtrim($value, '0'), '.');
            }
            // Convert scientific notation if present
            if (stripos($value, 'e+') !== false || stripos($value, 'e-') !== false) {
                $value = sprintf("%.0f", floatval($value));
            }
        }
        
        return $value === '' ? null : $value;
    }
    
    /**
     * Extract lead type from vertical field
     */
    private function extractType($row, $columnMap)
    {
        $vertical = $this->getValue($row, $columnMap, 'vertical');
        if (!$vertical) {
            return 'auto'; // Default
        }
        
        $vertical = strtolower($vertical);
        if (strpos($vertical, 'auto') !== false) {
            return 'auto';
        } elseif (strpos($vertical, 'home') !== false) {
            return 'home';
        } elseif (strpos($vertical, 'health') !== false) {
            return 'health';
        } elseif (strpos($vertical, 'life') !== false) {
            return 'life';
        }
        
        return 'auto'; // Default
    }
    
    /**
     * Extract TCPA compliance
     */
    private function extractTcpa($row, $columnMap)
    {
        $tcpa = $this->getValue($row, $columnMap, 'tcpa');
        if (!$tcpa) {
            return false;
        }
        
        $tcpa = strtolower($tcpa);
        return in_array($tcpa, ['yes', 'true', '1']);
    }
    
    /**
     * Create lead record
     */
    private function createLead($leadData)
    {
        // Create or find vendor
        if (!empty($leadData['vendor_name'])) {
            Vendor::firstOrCreate(
                ['name' => $leadData['vendor_name']],
                ['active' => true, 'notes' => 'Auto-created from LQF import']
            );
        }
        
        // Create or find buyer
        if (!empty($leadData['buyer_name'])) {
            // Check if buyer exists
            $buyer = Buyer::where('name', $leadData['buyer_name'])->first();
            
            if (!$buyer) {
                // Parse name for buyers table requirements
                $nameParts = explode(' - ', $leadData['buyer_name'], 2);
                $companyName = trim($nameParts[0]);
                
                // For names like "QF (QUOTING FAST LEADS TO TRANSFER)"
                if (strpos($companyName, '(') !== false) {
                    $companyName = trim(explode('(', $companyName)[0]);
                }
                
                $buyer = Buyer::create([
                    'name' => $leadData['buyer_name'],
                    'first_name' => $companyName,
                    'last_name' => '',
                    'company' => $leadData['buyer_name'],
                    'status' => 'active',
                    'active' => true,
                    'notes' => 'Auto-created from LQF import'
                ]);
            }
        }
        
        // Create or find campaign
        if (!empty($leadData['campaign_id']) && !empty($leadData['buyer_name'])) {
            Campaign::firstOrCreate(
                ['campaign_id' => $leadData['campaign_id']],
                [
                    'campaign_id' => $leadData['campaign_id'],
                    'name' => $leadData['buyer_name'] . ' - Campaign ' . $leadData['campaign_id'],
                    'display_name' => $leadData['buyer_name'],
                    'status' => 'active',
                    'is_auto_created' => true,
                    'description' => 'Auto-created from LQF import'
                ]
            );
        }
        
        // Create lead
        return Lead::create($leadData);
    }
}
}