<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ViciCallLogService
{
    private $lastSyncKey = 'vici_last_call_sync_timestamp';
    private $syncHistoryKey = 'vici_sync_history';
    
    /**
     * Get the last sync timestamp
     */
    public function getLastSyncTime(): ?Carbon
    {
        $timestamp = Cache::get($this->lastSyncKey);
        return $timestamp ? Carbon::parse($timestamp) : null;
    }
    
    /**
     * Set the last sync timestamp
     */
    public function setLastSyncTime(Carbon $time): void
    {
        Cache::put($this->lastSyncKey, $time->toIso8601String(), now()->addDays(30));
        
        // Also track in history
        $history = Cache::get($this->syncHistoryKey, []);
        array_unshift($history, [
            'timestamp' => $time->toIso8601String(),
            'synced_at' => now()->toIso8601String()
        ]);
        
        // Keep only last 100 sync records
        $history = array_slice($history, 0, 100);
        Cache::put($this->syncHistoryKey, $history, now()->addDays(30));
    }
    
    /**
     * Smart sync - determines what needs to be synced
     */
    public function smartSync(): array
    {
        $lastSync = $this->getLastSyncTime();
        $now = Carbon::now();
        
        // Determine sync strategy
        if (!$lastSync) {
            // First sync - get last 24 hours
            Log::info('🔄 First sync detected, fetching last 24 hours');
            return $this->syncTimeRange($now->subDay(), $now);
        }
        
        $hoursSinceSync = $lastSync->diffInHours($now);
        
        if ($hoursSinceSync > 24) {
            // Been too long, do daily chunks
            Log::info('📅 Last sync over 24 hours ago, syncing in daily chunks');
            return $this->syncInChunks($lastSync, $now, 'day');
        } elseif ($hoursSinceSync > 1) {
            // Normal sync - get everything since last sync
            Log::info('✅ Normal sync, fetching since ' . $lastSync->format('Y-m-d H:i:s'));
            return $this->syncTimeRange($lastSync->addSecond(), $now);
        } else {
            // Recent sync - get last hour
            Log::info('⏰ Recent sync, fetching last hour only');
            return $this->syncTimeRange($now->subHour(), $now);
        }
    }
    
    /**
     * Sync a specific time range
     */
    public function syncTimeRange(Carbon $from, Carbon $to): array
    {
        $stats = [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'total_calls' => 0,
            'new_records' => 0,
            'updated_records' => 0,
            'leads_matched' => 0,
            'errors' => []
        ];
        
        try {
            // Fetch call logs from Vici
            $callLogs = $this->fetchCallLogs($from, $to);
            $stats['total_calls'] = count($callLogs);
            
            Log::info("📞 Processing {$stats['total_calls']} call logs");
            
            foreach ($callLogs as $log) {
                $result = $this->processCallLog($log);
                
                if ($result['success']) {
                    if ($result['created']) {
                        $stats['new_records']++;
                    } else {
                        $stats['updated_records']++;
                    }
                    
                    if ($result['lead_matched']) {
                        $stats['leads_matched']++;
                    }
                } else {
                    $stats['errors'][] = $result['error'];
                }
            }
            
            // Update last sync time
            $this->setLastSyncTime($to);
            
            Log::info('✅ Call log sync completed', $stats);
            
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
            Log::error('❌ Call log sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $stats;
    }
    
    /**
     * Sync in chunks (for catching up)
     */
    public function syncInChunks(Carbon $from, Carbon $to, string $chunkSize = 'hour'): array
    {
        $totalStats = [
            'chunks_processed' => 0,
            'total_calls' => 0,
            'new_records' => 0,
            'updated_records' => 0
        ];
        
        $current = $from->copy();
        
        while ($current->lt($to)) {
            $chunkEnd = $current->copy();
            
            switch ($chunkSize) {
                case 'hour':
                    $chunkEnd->addHour();
                    break;
                case 'day':
                    $chunkEnd->addDay();
                    break;
                default:
                    $chunkEnd->addHours(6);
            }
            
            // Don't go past the end time
            if ($chunkEnd->gt($to)) {
                $chunkEnd = $to;
            }
            
            Log::info("📦 Processing chunk: {$current->format('Y-m-d H:i:s')} to {$chunkEnd->format('Y-m-d H:i:s')}");
            
            $chunkStats = $this->syncTimeRange($current, $chunkEnd);
            
            $totalStats['chunks_processed']++;
            $totalStats['total_calls'] += $chunkStats['total_calls'];
            $totalStats['new_records'] += $chunkStats['new_records'];
            $totalStats['updated_records'] += $chunkStats['updated_records'];
            
            $current = $chunkEnd->copy()->addSecond();
            
            // Small delay to avoid overwhelming the API
            usleep(500000); // 0.5 seconds
        }
        
        return $totalStats;
    }
    
    /**
     * Fetch call logs from Vici API
     */
    private function fetchCallLogs(Carbon $from, Carbon $to): array
    {
        $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
        
        $params = [
            'source' => 'brain',
            'user' => 'apiuser',
            'pass' => 'UZPATJ59GJAVKG8ES6',
            'function' => 'call_log_report',
            'start_date' => $from->format('Y-m-d'),
            'end_date' => $to->format('Y-m-d'),
            'start_time' => $from->format('H:i:s'),
            'end_time' => $to->format('H:i:s'),
            'search_archived_data' => 'YES',
            'header' => 'NO'
        ];
        
        try {
            $response = Http::timeout(30)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                return $this->parseApiResponse($response->body());
            }
            
            Log::warning('Vici API returned non-success', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch call logs from Vici', [
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }
    
    /**
     * Parse Vici API response
     */
    private function parseApiResponse(string $response): array
    {
        $logs = [];
        $lines = explode("\n", trim($response));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'ERROR') !== false) continue;
            
            // Parse pipe-delimited format
            $fields = explode('|', $line);
            
            if (count($fields) >= 8) {
                $logs[] = [
                    'call_date' => $fields[0] ?? null,
                    'phone_number' => $fields[1] ?? null,
                    'status' => $fields[2] ?? null,
                    'user' => $fields[3] ?? null,
                    'campaign_id' => $fields[4] ?? null,
                    'vendor_lead_code' => $fields[5] ?? null,
                    'length_in_sec' => intval($fields[6] ?? 0),
                    'list_id' => $fields[7] ?? null,
                    'lead_id' => $fields[8] ?? null,
                    'term_reason' => $fields[9] ?? null
                ];
            }
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
            $lead = $this->findLeadFromCallLog($log);
            
            if (!$lead) {
                return [
                    'success' => false,
                    'error' => 'Lead not found',
                    'lead_matched' => false
                ];
            }
            
            // Update or create call metrics
            $metrics = ViciCallMetrics::firstOrNew([
                'lead_id' => $lead->id
            ]);
            
            $created = !$metrics->exists;
            
            // Update metrics
            $this->updateCallMetrics($metrics, $log);
            $metrics->save();
            
            // Update lead status if needed
            $this->updateLeadFromCallLog($lead, $log);
            
            return [
                'success' => true,
                'created' => $created,
                'lead_matched' => true
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'lead_matched' => false
            ];
        }
    }
    
    /**
     * Find lead from call log data
     */
    private function findLeadFromCallLog(array $log): ?Lead
    {
        // Try vendor_lead_code first (most reliable)
        if (!empty($log['vendor_lead_code'])) {
            if (preg_match('/BRAIN_(\d+)/', $log['vendor_lead_code'], $matches)) {
                $lead = Lead::find($matches[1]);
                if ($lead) return $lead;
            }
        }
        
        // Try phone number
        if (!empty($log['phone_number'])) {
            $phone = preg_replace('/\D/', '', $log['phone_number']);
            if (strlen($phone) == 10) {
                $lead = Lead::where('phone', $phone)
                           ->orderBy('created_at', 'desc')
                           ->first();
                if ($lead) return $lead;
            }
        }
        
        return null;
    }
    
    /**
     * Update call metrics with log data
     */
    private function updateCallMetrics(ViciCallMetrics &$metrics, array $log): void
    {
        $metrics->phone_number = $log['phone_number'] ?? $metrics->phone_number;
        $metrics->campaign_id = $log['campaign_id'] ?? $metrics->campaign_id;
        $metrics->agent_id = $log['user'] ?? $metrics->agent_id;
        $metrics->status = $log['status'] ?? $metrics->status;
        $metrics->last_call_date = $log['call_date'] ?? now();
        
        // Increment call count
        $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
        
        // Update talk time
        $talkTime = intval($log['length_in_sec'] ?? 0);
        if ($talkTime > 0) {
            $metrics->talk_time = ($metrics->talk_time ?? 0) + $talkTime;
            $metrics->connected = true;
        }
        
        // Track call attempts
        $attempts = json_decode($metrics->call_attempts ?? '[]', true);
        $attempts[] = [
            'date' => $log['call_date'],
            'status' => $log['status'],
            'duration' => $talkTime,
            'agent' => $log['user']
        ];
        $metrics->call_attempts = json_encode($attempts);
    }
    
    /**
     * Update lead based on call log
     */
    private function updateLeadFromCallLog(Lead &$lead, array $log): void
    {
        $viciStatus = $log['status'] ?? '';
        
        // Map important Vici statuses to lead status
        $statusMap = [
            'SALE' => 'sold',
            'DNC' => 'dnc',
            'XFER' => 'transferred',
            'CALLBK' => 'callback',
            'NI' => 'not_interested',
            'DAIR' => 'disconnected'
        ];
        
        if (isset($statusMap[$viciStatus])) {
            $lead->status = $statusMap[$viciStatus];
            $lead->save();
        }
    }
    
    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        $lastSync = $this->getLastSyncTime();
        $history = Cache::get($this->syncHistoryKey, []);
        
        return [
            'last_sync' => $lastSync ? $lastSync->toIso8601String() : null,
            'last_sync_human' => $lastSync ? $lastSync->diffForHumans() : 'Never',
            'next_sync_due' => $lastSync ? $lastSync->addHour()->toIso8601String() : now()->toIso8601String(),
            'sync_history' => array_slice($history, 0, 10),
            'total_call_records' => ViciCallMetrics::count(),
            'calls_today' => ViciCallMetrics::whereDate('last_call_date', today())->count()
        ];
    }
}
