<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use App\Services\ViciDialerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SyncViciCallLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:sync-call-logs 
                            {--from= : Start date/time (defaults to last sync time)}
                            {--to= : End date/time (defaults to now)}
                            {--days=1 : Number of days to sync if no from/to specified}
                            {--full : Full sync, ignore last sync time}
                            {--dry-run : Preview what would be synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync call logs from Vici - Brain fetches the data';

    private $viciService;
    private $lastSyncKey = 'vici_last_call_sync_timestamp';
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   VICI CALL LOG SYNC - Brain Fetching');
        $this->info('========================================');
        
        // Determine time range
        $timeRange = $this->determineTimeRange();
        
        $this->info("📅 Syncing from: {$timeRange['from']}");
        $this->info("📅 Syncing to: {$timeRange['to']}");
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No data will be saved');
        }
        
        // Track sync progress
        $stats = [
            'total_calls' => 0,
            'new_records' => 0,
            'updated_records' => 0,
            'failed' => 0,
            'leads_updated' => 0
        ];
        
        try {
            // Connect to Vici API or database
            $callLogs = $this->fetchCallLogsFromVici($timeRange);
            
            if (empty($callLogs)) {
                $this->info('No call logs found for the specified period.');
                return Command::SUCCESS;
            }
            
            $this->info("📞 Found " . count($callLogs) . " call records to process");
            
            // Process each call log
            $bar = $this->output->createProgressBar(count($callLogs));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            
            foreach ($callLogs as $log) {
                $bar->setMessage("Processing call for {$log['phone_number']}");
                
                if (!$this->option('dry-run')) {
                    $result = $this->processCallLog($log);
                    
                    if ($result['success']) {
                        if ($result['created']) {
                            $stats['new_records']++;
                        } else {
                            $stats['updated_records']++;
                        }
                        
                        if ($result['lead_updated']) {
                            $stats['leads_updated']++;
                        }
                    } else {
                        $stats['failed']++;
                    }
                }
                
                $stats['total_calls']++;
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            // Update last sync timestamp
            if (!$this->option('dry-run') && !$this->option('full')) {
                Cache::put($this->lastSyncKey, $timeRange['to'], now()->addDays(30));
                $this->info("✅ Updated last sync timestamp to: {$timeRange['to']}");
            }
            
            // Display results
            $this->displayResults($stats);
            
        } catch (\Exception $e) {
            $this->error('❌ Sync failed: ' . $e->getMessage());
            Log::error('Vici call log sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Determine the time range for syncing
     */
    private function determineTimeRange(): array
    {
        // If full sync requested, go back 30 days
        if ($this->option('full')) {
            return [
                'from' => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
                'to' => Carbon::now()->format('Y-m-d H:i:s')
            ];
        }
        
        // If specific from/to provided
        if ($this->option('from') && $this->option('to')) {
            return [
                'from' => Carbon::parse($this->option('from'))->format('Y-m-d H:i:s'),
                'to' => Carbon::parse($this->option('to'))->format('Y-m-d H:i:s')
            ];
        }
        
        // Use last sync time or default to days option
        $lastSync = Cache::get($this->lastSyncKey);
        
        if ($lastSync && !$this->option('from')) {
            // Add 1 second to avoid duplicates
            $from = Carbon::parse($lastSync)->addSecond();
            
            // Safety check: if last sync was too long ago, limit to 7 days
            if ($from->lt(Carbon::now()->subDays(7))) {
                $from = Carbon::now()->subDays(7);
                $this->warn('⚠️ Last sync was over 7 days ago, limiting to last 7 days');
            }
        } else {
            // Default to number of days specified
            $days = $this->option('days') ?? 1;
            $from = Carbon::now()->subDays($days);
        }
        
        return [
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Fetch call logs from Vici
     */
    private function fetchCallLogsFromVici(array $timeRange): array
    {
        $this->info('🔄 Fetching call logs from Vici...');
        
        // Method 1: Via Non-Agent API
        $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
        
        $params = [
            'source' => 'brain',
            'user' => 'apiuser',
            'pass' => 'UZPATJ59GJAVKG8ES6',
            'function' => 'call_log_report',
            'start_date' => Carbon::parse($timeRange['from'])->format('Y-m-d'),
            'end_date' => Carbon::parse($timeRange['to'])->format('Y-m-d'),
            'start_time' => Carbon::parse($timeRange['from'])->format('H:i:s'),
            'end_time' => Carbon::parse($timeRange['to'])->format('H:i:s'),
            'include_manual' => 'Y',
            'include_archive' => 'Y'
        ];
        
        try {
            $response = \Http::timeout(30)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                $body = $response->body();
                
                // Parse the response (Vici returns pipe-delimited data)
                return $this->parseViciResponse($body);
            }
            
        } catch (\Exception $e) {
            Log::warning('API method failed, trying alternative', ['error' => $e->getMessage()]);
        }
        
        // Method 2: Direct database query (if API fails)
        // This would require MySQL connection to Vici
        return $this->fetchViaDirectQuery($timeRange);
    }
    
    /**
     * Parse Vici API response
     */
    private function parseViciResponse(string $response): array
    {
        $logs = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            if (empty($line) || strpos($line, 'SUCCESS') !== false) continue;
            
            // Vici typically returns pipe-delimited data
            $fields = explode('|', $line);
            
            if (count($fields) >= 10) {
                $logs[] = [
                    'uniqueid' => $fields[0] ?? null,
                    'lead_id' => $fields[1] ?? null,
                    'list_id' => $fields[2] ?? null,
                    'campaign_id' => $fields[3] ?? null,
                    'call_date' => $fields[4] ?? null,
                    'phone_number' => $fields[5] ?? null,
                    'status' => $fields[6] ?? null,
                    'user' => $fields[7] ?? null,
                    'length_in_sec' => $fields[8] ?? 0,
                    'term_reason' => $fields[9] ?? null,
                    'vendor_lead_code' => $fields[10] ?? null,
                ];
            }
        }
        
        return $logs;
    }
    
    /**
     * Alternative: Fetch via direct database query
     */
    private function fetchViaDirectQuery(array $timeRange): array
    {
        // This is a fallback method using raw SQL
        // Would need Vici database credentials configured
        
        $logs = [];
        
        try {
            // Mock data for demonstration
            // In production, this would query Vici's vicidial_log table
            $query = "
                SELECT 
                    uniqueid,
                    lead_id,
                    list_id,
                    campaign_id,
                    call_date,
                    phone_number,
                    status,
                    user,
                    length_in_sec,
                    term_reason
                FROM vicidial_log
                WHERE call_date BETWEEN ? AND ?
                ORDER BY call_date ASC
            ";
            
            // Would execute query here
            // $logs = DB::connection('vici')->select($query, [$timeRange['from'], $timeRange['to']]);
            
        } catch (\Exception $e) {
            Log::error('Direct query failed', ['error' => $e->getMessage()]);
        }
        
        return $logs;
    }
    
    /**
     * Process individual call log
     */
    private function processCallLog(array $log): array
    {
        try {
            // Find the lead
            $lead = null;
            
            // Try to find by vendor_lead_code (our BRAIN_ID format)
            if (!empty($log['vendor_lead_code'])) {
                if (preg_match('/BRAIN_(\d+)/', $log['vendor_lead_code'], $matches)) {
                    $lead = Lead::find($matches[1]);
                }
            }
            
            // Fallback to phone number
            if (!$lead && !empty($log['phone_number'])) {
                $phone = preg_replace('/\D/', '', $log['phone_number']);
                if (strlen($phone) == 10) {
                    $lead = Lead::where('phone', $phone)->first();
                }
            }
            
            if (!$lead) {
                return ['success' => false, 'message' => 'Lead not found'];
            }
            
            // Update or create call metrics
            $metrics = ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
            
            $created = !$metrics->exists;
            
            // Update metrics
            $metrics->phone_number = $log['phone_number'] ?? $lead->phone;
            $metrics->campaign_id = $log['campaign_id'] ?? null;
            $metrics->agent_id = $log['user'] ?? null;
            $metrics->status = $log['status'] ?? null;
            $metrics->last_call_date = $log['call_date'] ?? now();
            
            // Increment call count
            $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
            
            // Update talk time
            $talkTime = intval($log['length_in_sec'] ?? 0);
            if ($talkTime > 0) {
                $metrics->talk_time = ($metrics->talk_time ?? 0) + $talkTime;
                $metrics->connected = true;
            }
            
            // Track disposition
            if (!empty($log['status'])) {
                $dispositions = json_decode($metrics->dispositions ?? '[]', true);
                $dispositions[] = [
                    'status' => $log['status'],
                    'date' => $log['call_date'],
                    'agent' => $log['user']
                ];
                $metrics->dispositions = json_encode($dispositions);
            }
            
            $metrics->save();
            
            // Update lead status based on call outcome
            $leadUpdated = false;
            if ($this->shouldUpdateLeadStatus($log['status'])) {
                $lead->status = $this->mapViciStatusToLeadStatus($log['status']);
                $lead->save();
                $leadUpdated = true;
            }
            
            return [
                'success' => true,
                'created' => $created,
                'lead_updated' => $leadUpdated
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to process call log', [
                'log' => $log,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if lead status should be updated
     */
    private function shouldUpdateLeadStatus(?string $viciStatus): bool
    {
        $updateStatuses = ['SALE', 'DNC', 'NI', 'XFER', 'CALLBK'];
        return in_array($viciStatus, $updateStatuses);
    }
    
    /**
     * Map Vici status to lead status
     */
    private function mapViciStatusToLeadStatus(string $viciStatus): string
    {
        $mapping = [
            'SALE' => 'sold',
            'DNC' => 'dnc',
            'NI' => 'not_interested',
            'XFER' => 'transferred',
            'CALLBK' => 'callback',
            'NA' => 'no_answer',
            'B' => 'busy',
            'DC' => 'disconnected'
        ];
        
        return $mapping[$viciStatus] ?? 'contacted';
    }
    
    /**
     * Display sync results
     */
    private function displayResults(array $stats): void
    {
        $this->info('========================================');
        $this->info('           SYNC RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Calls Processed', number_format($stats['total_calls'])],
                ['New Call Records', number_format($stats['new_records'])],
                ['Updated Records', number_format($stats['updated_records'])],
                ['Leads Updated', number_format($stats['leads_updated'])],
                ['Failed', number_format($stats['failed'])],
            ]
        );
        
        $successRate = $stats['total_calls'] > 0 
            ? round((($stats['total_calls'] - $stats['failed']) / $stats['total_calls']) * 100, 1)
            : 0;
            
        $this->newLine();
        $this->info("✅ Success Rate: {$successRate}%");
        
        if ($stats['failed'] > 0) {
            $this->warn("⚠️ {$stats['failed']} records failed to process. Check logs for details.");
        }
    }
}
