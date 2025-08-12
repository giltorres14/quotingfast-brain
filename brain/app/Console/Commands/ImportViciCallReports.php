<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ImportViciCallReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:import-call-reports 
                           {--start-date= : Start date for report (Y-m-d format)}
                           {--end-date= : End date for report (Y-m-d format)}
                           {--list-id=101 : Vici list ID to pull reports from}
                           {--campaign=* : Filter by Vici campaigns}
                           {--dry-run : Preview without importing}
                           {--update-existing : Update existing call metrics}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Vici call reports and metrics into Brain database';
    
    private $stats = [
        'total_calls' => 0,
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'leads_matched' => 0,
        'leads_not_found' => 0
    ];
    
    private $viciConfig;
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startDate = $this->option('start-date') ? Carbon::parse($this->option('start-date')) : Carbon::now()->subDays(30);
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : Carbon::now();
        $listId = $this->option('list-id');
        $campaigns = $this->option('campaign');
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
        $this->info("VICI CALL REPORTS IMPORT");
        $this->info("========================================");
        $this->newLine();
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No data will be saved");
        } else {
            $this->info("💾 LIVE MODE - Data will be imported");
        }
        
        $this->info("📅 Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("📋 List ID: $listId");
        if (!empty($campaigns)) {
            $this->info("🎯 Campaigns: " . implode(', ', $campaigns));
        }
        $this->newLine();
        
        // Fetch call reports from Vici
        $this->info("📞 Fetching call reports from Vici...");
        $callReports = $this->fetchViciCallReports($startDate, $endDate, $listId, $campaigns);
        
        if (empty($callReports)) {
            $this->warn("No call reports found for the specified criteria");
            return 0;
        }
        
        $this->info("Found " . count($callReports) . " call records");
        $this->newLine();
        
        // Process each call report
        $bar = $this->output->createProgressBar(count($callReports));
        $bar->start();
        
        foreach ($callReports as $report) {
            $this->processCallReport($report, $dryRun, $updateExisting);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Show final statistics
        $this->showFinalStatistics($dryRun);
        
        return 0;
    }
    
    /**
     * Fetch call reports from Vici API
     */
    private function fetchViciCallReports($startDate, $endDate, $listId, $campaigns)
    {
        // Build API request parameters
        $params = [
            'source' => 'brain',
            'user' => $this->viciConfig['user'],
            'pass' => $this->viciConfig['pass'],
            'function' => 'call_report',
            'list_id' => $listId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'header' => 'YES'
        ];
        
        // Add campaign filter if specified
        if (!empty($campaigns)) {
            $params['campaigns'] = implode('|', $campaigns);
        }
        
        try {
            // Make API call to Vici
            $url = "https://{$this->viciConfig['server']}{$this->viciConfig['api_endpoint']}";
            
            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);
            
            if ($response->successful()) {
                $data = $this->parseViciResponse($response->body());
                return $data;
            } else {
                $this->error("Failed to fetch Vici call reports: " . $response->status());
                
                // Fallback: Try to get from export report function
                return $this->fetchViciExportReport($startDate, $endDate, $listId, $campaigns);
            }
        } catch (\Exception $e) {
            $this->error("Error fetching Vici reports: " . $e->getMessage());
            
            // Fallback to export report
            return $this->fetchViciExportReport($startDate, $endDate, $listId, $campaigns);
        }
    }
    
    /**
     * Alternative method: Fetch export report from Vici
     */
    private function fetchViciExportReport($startDate, $endDate, $listId, $campaigns)
    {
        $params = [
            'source' => 'brain',
            'user' => $this->viciConfig['user'],
            'pass' => $this->viciConfig['pass'],
            'function' => 'export_list',
            'list_id' => $listId,
            'header' => 'YES',
            'format' => 'pipe-standard'
        ];
        
        try {
            $url = "https://{$this->viciConfig['server']}{$this->viciConfig['api_endpoint']}";
            
            $response = Http::timeout(60)
                ->asForm()
                ->post($url, $params);
            
            if ($response->successful()) {
                return $this->parseExportData($response->body());
            }
        } catch (\Exception $e) {
            $this->error("Export report also failed: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Parse Vici API response
     */
    private function parseViciResponse($responseBody)
    {
        $reports = [];
        
        // Parse based on Vici response format
        $lines = explode("\n", $responseBody);
        $headers = [];
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) continue;
            
            // First line should be headers
            if ($index === 0 && strpos($line, 'lead_id') !== false) {
                $headers = str_getcsv($line, '|');
                continue;
            }
            
            // Parse data lines
            $data = str_getcsv($line, '|');
            if (count($data) === count($headers) && count($headers) > 0) {
                $report = array_combine($headers, $data);
                $reports[] = $this->normalizeCallReport($report);
            }
        }
        
        return $reports;
    }
    
    /**
     * Parse export data format
     */
    private function parseExportData($responseBody)
    {
        $reports = [];
        $lines = explode("\n", $responseBody);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Parse pipe-delimited data
            $parts = explode('|', $line);
            
            if (count($parts) >= 10) {
                $reports[] = [
                    'lead_id' => $parts[0] ?? null,
                    'phone_number' => $parts[1] ?? null,
                    'status' => $parts[2] ?? null,
                    'vendor_lead_code' => $parts[3] ?? null,
                    'list_id' => $parts[4] ?? null,
                    'campaign_id' => $parts[5] ?? null,
                    'agent' => $parts[6] ?? null,
                    'last_call_time' => $parts[7] ?? null,
                    'called_count' => $parts[8] ?? null,
                    'comments' => $parts[9] ?? null
                ];
            }
        }
        
        return $reports;
    }
    
    /**
     * Normalize call report data
     */
    private function normalizeCallReport($report)
    {
        return [
            'vici_lead_id' => $report['lead_id'] ?? $report['vicidial_id'] ?? null,
            'phone_number' => $report['phone_number'] ?? $report['phone'] ?? null,
            'vendor_lead_code' => $report['vendor_lead_code'] ?? $report['vendor_id'] ?? null,
            'status' => $report['status'] ?? null,
            'disposition' => $report['status'] ?? $report['disposition'] ?? null,
            'list_id' => $report['list_id'] ?? null,
            'campaign_id' => $report['campaign_id'] ?? $report['campaign'] ?? null,
            'agent_id' => $report['user'] ?? $report['agent'] ?? null,
            'call_date' => $report['call_date'] ?? $report['last_call_time'] ?? null,
            'called_count' => $report['called_count'] ?? $report['call_attempts'] ?? 0,
            'talk_time' => $report['talk_time'] ?? $report['length_in_sec'] ?? 0,
            'comments' => $report['comments'] ?? null,
            'raw_data' => $report
        ];
    }
    
    /**
     * Process a single call report
     */
    private function processCallReport($report, $dryRun, $updateExisting)
    {
        $this->stats['total_calls']++;
        
        try {
            // Find the lead in Brain database
            $lead = $this->findLeadByReport($report);
            
            if (!$lead) {
                $this->stats['leads_not_found']++;
                
                if ($this->output->isVerbose()) {
                    $this->warn("Lead not found for Vici ID: {$report['vici_lead_id']}, Phone: {$report['phone_number']}");
                }
                return;
            }
            
            $this->stats['leads_matched']++;
            
            // Check if call metrics already exist
            $existingMetrics = ViciCallMetrics::where('lead_id', $lead->id)
                ->where('vici_lead_id', $report['vici_lead_id'])
                ->first();
            
            if ($existingMetrics && !$updateExisting) {
                $this->stats['skipped']++;
                return;
            }
            
            // Build call metrics data
            $metricsData = $this->buildCallMetricsData($lead, $report);
            
            if (!$dryRun) {
                if ($existingMetrics) {
                    // Update existing metrics
                    $existingMetrics->update($metricsData);
                    $this->stats['updated']++;
                    
                    // Add to call history
                    $existingMetrics->addCallAttempt([
                        'status' => $report['status'],
                        'agent' => $report['agent_id'],
                        'talk_time' => $report['talk_time'],
                        'date' => $report['call_date']
                    ]);
                } else {
                    // Create new metrics
                    $metrics = ViciCallMetrics::create($metricsData);
                    $this->stats['imported']++;
                    
                    // Initialize call history
                    $metrics->addCallAttempt([
                        'status' => $report['status'],
                        'agent' => $report['agent_id'],
                        'talk_time' => $report['talk_time'],
                        'date' => $report['call_date']
                    ]);
                }
            } else {
                if ($existingMetrics) {
                    $this->stats['updated']++;
                } else {
                    $this->stats['imported']++;
                }
            }
            
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("Error processing call report: " . $e->getMessage());
        }
    }
    
    /**
     * Find lead by Vici report data
     */
    private function findLeadByReport($report)
    {
        // First try by vendor_lead_code (Brain's external_lead_id)
        if (!empty($report['vendor_lead_code'])) {
            $lead = Lead::where('external_lead_id', $report['vendor_lead_code'])->first();
            if ($lead) return $lead;
        }
        
        // Then try by phone number
        if (!empty($report['phone_number'])) {
            $phone = preg_replace('/[^0-9]/', '', $report['phone_number']);
            
            // Try 10-digit
            if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
                $phone = substr($phone, 1);
            }
            
            $lead = Lead::where('phone', $phone)
                ->orWhere('phone', '1' . $phone)
                ->first();
                
            if ($lead) return $lead;
        }
        
        return null;
    }
    
    /**
     * Build call metrics data
     */
    private function buildCallMetricsData($lead, $report)
    {
        $callDate = null;
        if (!empty($report['call_date'])) {
            try {
                $callDate = Carbon::parse($report['call_date']);
            } catch (\Exception $e) {
                $callDate = now();
            }
        }
        
        return [
            'lead_id' => $lead->id,
            'vici_lead_id' => $report['vici_lead_id'],
            'campaign_id' => $report['campaign_id'],
            'list_id' => $report['list_id'],
            'agent_id' => $report['agent_id'],
            'phone_number' => $report['phone_number'],
            'call_status' => $report['status'],
            'disposition' => $report['disposition'],
            'call_attempts' => (int) ($report['called_count'] ?? 1),
            'last_call_time' => $callDate,
            'talk_time' => (int) ($report['talk_time'] ?? 0),
            'connected_time' => $report['talk_time'] > 0 ? $callDate : null,
            'call_duration' => (int) ($report['talk_time'] ?? 0),
            'vici_payload' => $report['raw_data'],
            'notes' => $report['comments']
        ];
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
        
        // Statistics table
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Call Records', $this->stats['total_calls']],
                ['✅ Imported', $this->stats['imported']],
                ['🔄 Updated', $this->stats['updated']],
                ['⏭️  Skipped', $this->stats['skipped']],
                ['❌ Errors', $this->stats['errors']],
                ['',  ''],
                ['Leads Matched', $this->stats['leads_matched']],
                ['Leads Not Found', $this->stats['leads_not_found']],
            ]
        );
        
        if ($this->stats['leads_not_found'] > 0) {
            $this->newLine();
            $this->warn("⚠️  {$this->stats['leads_not_found']} call records could not be matched to leads in Brain");
            $this->info("These may be old leads not yet imported to Brain");
        }
        
        if ($dryRun) {
            $this->newLine();
            $this->warn("This was a DRY RUN - no data was imported");
            $this->info("To perform actual import, run without --dry-run");
        } else if ($this->stats['imported'] > 0 || $this->stats['updated'] > 0) {
            $this->newLine();
            $this->info("✅ Successfully processed " . ($this->stats['imported'] + $this->stats['updated']) . " call records!");
        }
    }
}

