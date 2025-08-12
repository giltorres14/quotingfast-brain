<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ImportViciSurajLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:import-suraj-leads 
                           {--list-id=101 : Vici list ID}
                           {--source=Suraj : Source name to filter}
                           {--start-date= : Start date (Y-m-d)}
                           {--end-date= : End date (Y-m-d)}
                           {--dry-run : Preview without importing}
                           {--update-existing : Update if lead exists}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Suraj leads from Vici into Brain database';
    
    private $stats = [
        'total' => 0,
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    private $viciConfig;
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $listId = $this->option('list-id');
        $source = $this->option('source');
        $startDate = $this->option('start-date') ? Carbon::parse($this->option('start-date')) : Carbon::now()->subMonths(3);
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : Carbon::now();
        $dryRun = $this->option('dry-run');
        $updateExisting = $this->option('update-existing');
        
        // Initialize Vici configuration
        $this->viciConfig = [
            'server' => env('VICI_SERVER', 'philli.callix.ai'),
            'user' => env('VICI_API_USER', 'apiuser'),
            'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'),
            'api_endpoint' => env('VICI_API_ENDPOINT', '/vicidial/non_agent_api.php')
        ];
        
        $this->info("========================================");
        $this->info("IMPORTING SURAJ LEADS FROM VICI");
        $this->info("========================================");
        $this->newLine();
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be saved");
        } else {
            $this->info("💾 LIVE MODE - Data will be imported");
        }
        
        $this->info("📅 Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("📋 List ID: $listId");
        $this->info("🏷️  Source: $source");
        $this->newLine();
        
        // Fetch leads from Vici
        $this->info("📥 Fetching Suraj leads from Vici...");
        $leads = $this->fetchViciLeads($listId, $source, $startDate, $endDate);
        
        if (empty($leads)) {
            $this->warn("No Suraj leads found in Vici for the specified criteria");
            return 0;
        }
        
        $this->info("Found " . count($leads) . " Suraj leads to process");
        $this->newLine();
        
        // Process each lead
        $bar = $this->output->createProgressBar(count($leads));
        $bar->start();
        
        foreach ($leads as $viciLead) {
            $this->processLead($viciLead, $dryRun, $updateExisting);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Show final statistics
        $this->showFinalStatistics($dryRun);
        
        return 0;
    }
    
    /**
     * Fetch leads from Vici API
     */
    private function fetchViciLeads($listId, $source, $startDate, $endDate)
    {
        $params = [
            'source' => 'brain',
            'user' => $this->viciConfig['user'],
            'pass' => $this->viciConfig['pass'],
            'function' => 'export_list',
            'list_id' => $listId,
            'header' => 'YES',
            'format' => 'pipe-standard',
            'custom_fields' => 'YES',
            'call_notes' => 'YES'
        ];
        
        try {
            $url = "https://{$this->viciConfig['server']}{$this->viciConfig['api_endpoint']}";
            
            $response = Http::timeout(60)
                ->asForm()
                ->post($url, $params);
            
            if ($response->successful()) {
                return $this->parseViciExport($response->body(), $source);
            } else {
                $this->error("Failed to fetch Vici leads: " . $response->status());
                
                // Try alternative method
                return $this->fetchViciLeadsAlternative($listId, $source);
            }
        } catch (\Exception $e) {
            $this->error("Error fetching Vici leads: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Alternative method using search function
     */
    private function fetchViciLeadsAlternative($listId, $source)
    {
        $params = [
            'source' => 'brain',
            'user' => $this->viciConfig['user'],
            'pass' => $this->viciConfig['pass'],
            'function' => 'search_list',
            'list_id' => $listId,
            'source_id' => $source,
            'header' => 'YES'
        ];
        
        try {
            $url = "https://{$this->viciConfig['server']}{$this->viciConfig['api_endpoint']}";
            
            $response = Http::timeout(60)
                ->asForm()
                ->post($url, $params);
            
            if ($response->successful()) {
                return $this->parseViciResponse($response->body(), $source);
            }
        } catch (\Exception $e) {
            $this->error("Alternative fetch also failed: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Parse Vici export data
     */
    private function parseViciExport($responseBody, $sourceFilter)
    {
        $leads = [];
        $lines = explode("\n", $responseBody);
        $headers = [];
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) continue;
            
            // Parse pipe-delimited data
            $parts = explode('|', $line);
            
            // First line should be headers
            if ($index === 0 && strpos(strtolower($line), 'lead_id') !== false) {
                $headers = array_map('trim', $parts);
                continue;
            }
            
            // Build lead data
            if (!empty($headers) && count($parts) === count($headers)) {
                $leadData = array_combine($headers, $parts);
                
                // Filter by source if specified
                if ($sourceFilter) {
                    $leadSource = $leadData['source_id'] ?? $leadData['source'] ?? '';
                    if (stripos($leadSource, $sourceFilter) === false) {
                        continue;
                    }
                }
                
                $leads[] = $this->normalizeViciLead($leadData);
            } elseif (count($parts) >= 10) {
                // Fallback parsing without headers
                $leads[] = $this->parseViciLeadWithoutHeaders($parts);
            }
        }
        
        return $leads;
    }
    
    /**
     * Parse Vici response data
     */
    private function parseViciResponse($responseBody, $sourceFilter)
    {
        $leads = [];
        $lines = explode("\n", $responseBody);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Skip error messages
            if (strpos($line, 'ERROR') !== false) continue;
            
            $parts = explode('|', $line);
            if (count($parts) >= 10) {
                $lead = $this->parseViciLeadWithoutHeaders($parts);
                
                // Filter by source
                if ($sourceFilter && stripos($lead['source'] ?? '', $sourceFilter) === false) {
                    continue;
                }
                
                $leads[] = $lead;
            }
        }
        
        return $leads;
    }
    
    /**
     * Parse lead without headers (positional)
     */
    private function parseViciLeadWithoutHeaders($parts)
    {
        // Standard Vici export format positions
        return [
            'lead_id' => $parts[0] ?? null,
            'phone_number' => $parts[1] ?? null,
            'status' => $parts[2] ?? null,
            'vendor_lead_code' => $parts[3] ?? null,
            'source_id' => $parts[4] ?? 'Suraj',
            'list_id' => $parts[5] ?? null,
            'gmt_offset_now' => $parts[6] ?? null,
            'called_since_last_reset' => $parts[7] ?? null,
            'phone_code' => $parts[8] ?? '1',
            'title' => $parts[9] ?? null,
            'first_name' => $parts[10] ?? null,
            'middle_initial' => $parts[11] ?? null,
            'last_name' => $parts[12] ?? null,
            'address1' => $parts[13] ?? null,
            'address2' => $parts[14] ?? null,
            'address3' => $parts[15] ?? null,
            'city' => $parts[16] ?? null,
            'state' => $parts[17] ?? null,
            'province' => $parts[18] ?? null,
            'postal_code' => $parts[19] ?? null,
            'country_code' => $parts[20] ?? null,
            'gender' => $parts[21] ?? null,
            'date_of_birth' => $parts[22] ?? null,
            'alt_phone' => $parts[23] ?? null,
            'email' => $parts[24] ?? null,
            'security_phrase' => $parts[25] ?? null,
            'comments' => $parts[26] ?? null,
            'called_count' => $parts[27] ?? 0,
            'last_local_call_time' => $parts[28] ?? null,
            'rank' => $parts[29] ?? null,
            'owner' => $parts[30] ?? null,
            'entry_date' => $parts[31] ?? null,
            'modify_date' => $parts[32] ?? null,
            'called_since_last_reset' => $parts[33] ?? null
        ];
    }
    
    /**
     * Normalize Vici lead data
     */
    private function normalizeViciLead($leadData)
    {
        return [
            'vici_lead_id' => $leadData['lead_id'] ?? null,
            'phone_number' => $leadData['phone_number'] ?? $leadData['phone'] ?? null,
            'first_name' => $leadData['first_name'] ?? null,
            'last_name' => $leadData['last_name'] ?? null,
            'email' => $leadData['email'] ?? null,
            'address' => $leadData['address1'] ?? null,
            'city' => $leadData['city'] ?? null,
            'state' => $leadData['state'] ?? null,
            'zip_code' => $leadData['postal_code'] ?? $leadData['zip'] ?? null,
            'source' => $leadData['source_id'] ?? $leadData['source'] ?? 'Suraj',
            'vendor_lead_code' => $leadData['vendor_lead_code'] ?? null,
            'status' => $leadData['status'] ?? null,
            'comments' => $leadData['comments'] ?? null,
            'entry_date' => $leadData['entry_date'] ?? null,
            'called_count' => $leadData['called_count'] ?? 0,
            'raw_data' => $leadData
        ];
    }
    
    /**
     * Process a single lead
     */
    private function processLead($viciLead, $dryRun, $updateExisting)
    {
        $this->stats['total']++;
        
        try {
            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $viciLead['phone_number']);
            if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
                $phone = substr($phone, 1);
            }
            
            if (strlen($phone) !== 10) {
                $this->stats['errors']++;
                return;
            }
            
            // Check if lead exists
            $existingLead = Lead::where('phone', $phone)
                ->orWhere('phone', '1' . $phone)
                ->first();
            
            if ($existingLead && !$updateExisting) {
                $this->stats['skipped']++;
                return;
            }
            
            // Build lead data
            $leadData = [
                'phone' => $phone,
                'first_name' => $viciLead['first_name'],
                'last_name' => $viciLead['last_name'],
                'name' => trim(($viciLead['first_name'] ?? '') . ' ' . ($viciLead['last_name'] ?? '')),
                'email' => $viciLead['email'],
                'address' => $viciLead['address'],
                'city' => $viciLead['city'],
                'state' => $viciLead['state'],
                'zip_code' => $viciLead['zip_code'],
                'source' => 'SURAJ_VICI',
                'type' => 'auto',
                'campaign_id' => 'SURAJ_IMPORT',
                'vici_lead_id' => $viciLead['vici_lead_id'],
                'meta' => json_encode([
                    'vici_import' => true,
                    'original_source' => 'Suraj',
                    'vici_status' => $viciLead['status'],
                    'called_count' => $viciLead['called_count'],
                    'import_date' => now()->toISOString()
                ])
            ];
            
            // Set external_lead_id
            if (!empty($viciLead['vendor_lead_code'])) {
                $leadData['external_lead_id'] = $viciLead['vendor_lead_code'];
            } else {
                $leadData['external_lead_id'] = Lead::generateExternalLeadId();
            }
            
            // Set created_at if available
            if (!empty($viciLead['entry_date'])) {
                try {
                    $leadData['created_at'] = Carbon::parse($viciLead['entry_date']);
                } catch (\Exception $e) {
                    // Use current time if parse fails
                }
            }
            
            if (!$dryRun) {
                if ($existingLead) {
                    $existingLead->update($leadData);
                    $this->stats['updated']++;
                } else {
                    Lead::create($leadData);
                    $this->stats['imported']++;
                }
            } else {
                if ($existingLead) {
                    $this->stats['updated']++;
                } else {
                    $this->stats['imported']++;
                }
            }
            
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("Error processing lead: " . $e->getMessage());
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
                ['Total Processed', $this->stats['total']],
                ['✅ Imported', $this->stats['imported']],
                ['🔄 Updated', $this->stats['updated']],
                ['⏭️  Skipped', $this->stats['skipped']],
                ['❌ Errors', $this->stats['errors']],
            ]
        );
        
        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN - no data was imported");
            $this->info("To perform actual import, run without --dry-run");
        } else if ($this->stats['imported'] > 0) {
            $this->newLine();
            $this->info("✅ Successfully imported {$this->stats['imported']} Suraj leads!");
            $this->newLine();
            
            // Update vendor codes in Vici
            $this->info("Next step: Update vendor_lead_codes in Vici");
            $this->line("Run: php artisan vici:update-vendor-codes");
        }
    }
}
