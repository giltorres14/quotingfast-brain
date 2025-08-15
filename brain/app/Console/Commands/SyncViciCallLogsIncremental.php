<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ViciCallMetrics;
use App\Models\Lead;
use App\Models\OrphanCallLog;
use Carbon\Carbon;

class SyncViciCallLogsIncremental extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:sync-incremental 
                            {--minutes=10 : Number of minutes to look back}
                            {--dry-run : Preview without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incrementally sync Vici call logs (runs every 5 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        // Get last sync time from cache
        $lastSyncKey = 'vici_last_incremental_sync';
        $lastSync = Cache::get($lastSyncKey, Carbon::now()->subMinutes(10));
        
        // Determine time range (with 1 minute overlap for safety)
        $fromTime = Carbon::parse($lastSync)->subMinute();
        $toTime = Carbon::now();
        
        // Don't go back more than specified minutes (default 10)
        $maxMinutesBack = $this->option('minutes');
        $earliestTime = Carbon::now()->subMinutes($maxMinutesBack);
        if ($fromTime->lt($earliestTime)) {
            $fromTime = $earliestTime;
        }
        
        $this->info("🔄 Incremental Vici Sync - " . now()->format('Y-m-d H:i:s'));
        $this->info("📅 Range: {$fromTime->format('Y-m-d H:i:s')} to {$toTime->format('Y-m-d H:i:s')}");
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }
        
        try {
            // Fetch call logs via Vici proxy
            $stats = $this->fetchAndProcessCallLogs($fromTime, $toTime);
            
            // Update last sync time
            if (!$this->option('dry-run') && $stats['total'] > 0) {
                Cache::put($lastSyncKey, $toTime, now()->addDays(7));
            }
            
            // Log performance metrics
            $executionTime = round(microtime(true) - $startTime, 2);
            
            $this->info("✅ Sync Complete:");
            $this->info("  • Calls processed: {$stats['total']}");
            $this->info("  • New records: {$stats['new']}");
            $this->info("  • Updated: {$stats['updated']}");
            $this->info("  • Orphaned: {$stats['orphaned']}");
            $this->info("  • Execution time: {$executionTime}s");
            
            // Log to file for monitoring
            Log::channel('vici')->info('Incremental sync completed', [
                'stats' => $stats,
                'execution_time' => $executionTime,
                'memory_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
            ]);
            
            // Alert if taking too long
            if ($executionTime > 60) {
                Log::warning('Vici sync taking longer than expected', [
                    'execution_time' => $executionTime,
                    'records' => $stats['total']
                ]);
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            Log::error('Vici incremental sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('❌ Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Fetch and process call logs
     */
    private function fetchAndProcessCallLogs(Carbon $fromTime, Carbon $toTime): array
    {
        $stats = [
            'total' => 0,
            'new' => 0,
            'updated' => 0,
            'orphaned' => 0,
            'skipped' => 0
        ];
        
        // Build SQL query for Vici - JOIN with vicidial_list to get vendor_lead_code
        $sql = "
            SELECT 
                vl.call_date,
                vl.lead_id,
                vl.list_id,
                vl.phone_number,
                vl.campaign_id,
                vl.status,
                vl.length_in_sec,
                vl.user,
                vl.term_reason,
                vlist.vendor_lead_code,
                vl.uniqueid
            FROM vicidial_log vl
            LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id
            WHERE vl.call_date BETWEEN '{$fromTime->format('Y-m-d H:i:s')}' 
                AND '{$toTime->format('Y-m-d H:i:s')}'
            AND vl.campaign_id IS NOT NULL 
            AND vl.campaign_id != ''
            ORDER BY vl.call_date ASC
            LIMIT 5000
        ";
        
        // Execute via proxy
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($sql) . " 2>&1"
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch data from Vici');
        }
        
        $output = $response->json()['output'] ?? '';
        
        // Parse the output
        $lines = explode("\n", $output);
        $headers = [];
        
        foreach ($lines as $lineNum => $line) {
            // Skip empty lines and errors
            if (empty($line) || strpos($line, 'Could not create') !== false || 
                strpos($line, 'Failed to add') !== false) {
                continue;
            }
            
            // First data line is headers
            if (empty($headers)) {
                $headers = preg_split('/\s+/', trim($line));
                continue;
            }
            
            // Parse data line
            $values = preg_split('/\s+/', trim($line), count($headers));
            
            if (count($values) < count($headers)) {
                continue;
            }
            
            $record = array_combine($headers, $values);
            $stats['total']++;
            
            // Process in batch mode if not dry run
            if (!$this->option('dry-run')) {
                $result = $this->processCallRecord($record);
                $stats[$result]++;
            }
            
            // Show progress every 100 records
            if ($stats['total'] % 100 == 0) {
                $this->info("  Processing... {$stats['total']} records");
            }
        }
        
        return $stats;
    }
    
    /**
     * Process individual call record
     */
    private function processCallRecord(array $record): string
    {
        // Try to find lead by vendor_lead_code (Brain ID)
        $lead = null;
        
        if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
            $lead = Lead::where('external_lead_id', $record['vendor_lead_code'])->first();
        }
        
        // Fallback to phone number
        if (!$lead && !empty($record['phone_number'])) {
            $cleanPhone = preg_replace('/\D/', '', $record['phone_number']);
            if (strlen($cleanPhone) >= 10) {
                $lead = Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10))->first();
            }
        }
        
        // If no lead found, store as orphan
        if (!$lead) {
            OrphanCallLog::updateOrCreate(
                [
                    'vici_lead_id' => $record['lead_id'],
                    'call_date' => $record['call_date']
                ],
                [
                    'phone_number' => $record['phone_number'] ?? null,
                    'vendor_lead_code' => $record['vendor_lead_code'] ?? null,
                    'campaign_id' => $record['campaign_id'] ?? null,
                    'agent_id' => $record['user'] ?? null,
                    'status' => $record['status'] ?? null,
                    'talk_time' => intval($record['length_in_sec'] ?? 0),
                    'call_data' => json_encode($record)
                ]
            );
            return 'orphaned';
        }
        
        // Update or create call metrics
        $metrics = ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
        $isNew = !$metrics->exists;
        
        // Update metrics efficiently
        $metrics->phone_number = $record['phone_number'] ?? $lead->phone;
        $metrics->campaign_id = $record['campaign_id'] ?? $metrics->campaign_id;
        $metrics->agent_id = $record['user'] ?? $metrics->agent_id;
        $metrics->status = $record['status'] ?? $metrics->status;
        $metrics->last_call_date = $record['call_date'] ?? now();
        
        // Increment counters
        $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
        
        $talkTime = intval($record['length_in_sec'] ?? 0);
        if ($talkTime > 0) {
            $metrics->talk_time = ($metrics->talk_time ?? 0) + $talkTime;
            $metrics->connected = true;
        }
        
        // Update dispositions efficiently (limit to last 100)
        $dispositions = json_decode($metrics->dispositions ?? '[]', true);
        $dispositions[] = [
            'status' => $record['status'] ?? '',
            'date' => $record['call_date'] ?? now()->toDateTimeString(),
            'agent' => $record['user'] ?? ''
        ];
        
        // Keep only last 100 dispositions to prevent bloat
        if (count($dispositions) > 100) {
            $dispositions = array_slice($dispositions, -100);
        }
        
        $metrics->dispositions = json_encode($dispositions);
        $metrics->save();
        
        return $isNew ? 'new' : 'updated';
    }
}
