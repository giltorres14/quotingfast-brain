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
        // CORRECT DATABASE: Q6hdjl67GRigMofv (not asterisk!)
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
            AND vl.campaign_id IN ('AUTODIAL', 'AUTO2')
            ORDER BY vl.call_date ASC
            LIMIT 5000
        ";
        
        // Execute via proxy with CORRECT database
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -N -B -e " . escapeshellarg($sql)
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch data from Vici');
        }
        
        // Get the raw output from the proxy response
        $responseBody = json_decode($response->body(), true);
        $output = $responseBody['output'] ?? '';
        
        // Parse the tab-separated output (using -N -B flags for clean output)
        $lines = explode("\n", $output);
        $processedCount = 0;
        
        foreach ($lines as $line) {
            // Skip empty lines and SSH warnings
            if (empty(trim($line)) || 
                strpos($line, 'Could not create') !== false || 
                strpos($line, 'Failed to add') !== false) {
                continue;
            }
            
            // Parse tab-separated values
            $values = explode("\t", $line);
            
            // We expect 11 fields from our SELECT query
            if (count($values) < 11) {
                continue;
            }
            
            // Map to expected structure
            $record = [
                'call_date' => $values[0],
                'lead_id' => $values[1],
                'list_id' => $values[2],
                'phone_number' => $values[3],
                'campaign_id' => $values[4],
                'status' => $values[5],
                'length_in_sec' => $values[6],
                'user' => $values[7],
                'term_reason' => $values[8],
                'vendor_lead_code' => $values[9],
                'uniqueid' => $values[10]
            ];
            
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
            
            $processedCount++;
        }
        
        if ($processedCount == 0) {
            $this->info('  No new call logs found');
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



