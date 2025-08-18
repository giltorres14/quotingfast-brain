<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViciCallMetrics;
use App\Models\Lead;
use App\Models\OrphanCallLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncViciAgentStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:sync-agent-stats 
                            {--from= : Start date (defaults to today)}
                            {--to= : End date (defaults to today)}
                            {--days=1 : Number of days to sync if no from/to specified}
                            {--dry-run : Preview what would be synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync agent call statistics from Vici using agent_stats_export';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   VICI AGENT STATS SYNC');
        $this->info('========================================');
        
        // Determine time range
        $timeRange = $this->determineTimeRange();
        
        $this->info("📅 Syncing from: {$timeRange['from']}");
        $this->info("📅 Syncing to: {$timeRange['to']}");
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No data will be saved');
        }
        
        // Fetch agent stats from Vici
        $agentStats = $this->fetchAgentStats($timeRange);
        
        if (empty($agentStats)) {
            $this->info('No agent stats found for the specified period.');
            return Command::SUCCESS;
        }
        
        $this->info("📞 Found " . count($agentStats) . " agent records to process");
        
        // Process stats
        $stats = [
            'agents_processed' => 0,
            'calls_recorded' => 0,
            'leads_matched' => 0,
            'orphans_created' => 0
        ];
        
        $bar = $this->output->createProgressBar(count($agentStats));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        foreach ($agentStats as $agentData) {
            $bar->setMessage("Processing agent: {$agentData['agent_name']}");
            
            if (!$this->option('dry-run')) {
                $result = $this->processAgentStats($agentData, $timeRange);
                $stats['agents_processed']++;
                $stats['calls_recorded'] += $result['calls'];
                $stats['leads_matched'] += $result['matched'];
                $stats['orphans_created'] += $result['orphans'];
            } else {
                $stats['agents_processed']++;
                $stats['calls_recorded'] += $agentData['total_calls'] ?? 0;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats);
        
        return Command::SUCCESS;
    }
    
    /**
     * Determine time range for sync
     */
    private function determineTimeRange(): array
    {
        if ($this->option('from') && $this->option('to')) {
            return [
                'from' => Carbon::parse($this->option('from'))->format('Y-m-d H:i:s'),
                'to' => Carbon::parse($this->option('to'))->format('Y-m-d H:i:s')
            ];
        }
        
        $days = $this->option('days') ?? 1;
        $to = Carbon::now();
        $from = $to->copy()->subDays($days)->startOfDay();
        
        return [
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Fetch agent stats from Vici API
     */
    private function fetchAgentStats(array $timeRange): array
    {
        $this->info('🔄 Fetching agent stats from Vici...');
        
        $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
        
        $params = [
            'source' => 'test',  // Use 'test' as source since 'brain' was rejected
            'user' => 'apiuser',
            'pass' => 'UZPATJ59GJAVKG8ES6',
            'function' => 'agent_stats_export',
            'datetime_start' => $timeRange['from'],
            'datetime_end' => $timeRange['to'],
            'agent_user' => '',  // Empty to get all agents
            'campaign' => ''     // Empty to get all campaigns
        ];
        
        try {
            $response = Http::timeout(30)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                return $this->parseAgentStats($response->body());
            }
            
            $this->error('API request failed: ' . $response->body());
            
        } catch (\Exception $e) {
            $this->error('Failed to fetch agent stats: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Parse agent stats response
     */
    private function parseAgentStats(string $response): array
    {
        $agents = [];
        $lines = explode("\n", trim($response));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'ERROR') !== false) continue;
            
            // Parse pipe-delimited format
            // Format: agent_id|agent_name|campaign|calls|talk_time|pause_time|dead_time|wait_time|...
            $fields = explode('|', $line);
            
            if (count($fields) >= 10) {
                $agents[] = [
                    'agent_id' => $fields[0] ?? null,
                    'agent_name' => $fields[1] ?? null,
                    'campaign' => $fields[2] ?? null,
                    'total_calls' => intval($fields[3] ?? 0),
                    'talk_time' => $this->parseTime($fields[4] ?? '0:00:00'),
                    'pause_time' => $this->parseTime($fields[5] ?? '0:00:00'),
                    'dead_time' => $this->parseTime($fields[6] ?? '0:00:00'),
                    'wait_time' => $this->parseTime($fields[7] ?? '0:00:00'),
                    'wrap_time' => $this->parseTime($fields[14] ?? '0:00:00'),
                    'customer_time' => $this->parseTime($fields[15] ?? '0:00:00'),
                ];
            }
        }
        
        return $agents;
    }
    
    /**
     * Parse time string to seconds
     */
    private function parseTime(string $time): int
    {
        // Format: H:MM:SS or MM:SS
        $parts = explode(':', $time);
        if (count($parts) == 3) {
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } elseif (count($parts) == 2) {
            return ($parts[0] * 60) + $parts[1];
        }
        return 0;
    }
    
    /**
     * Process agent stats and create/update metrics
     */
    private function processAgentStats(array $agentData, array $timeRange): array
    {
        $result = [
            'calls' => 0,
            'matched' => 0,
            'orphans' => 0
        ];
        
        // Create or update ViciCallMetrics record
        // Since we don't have individual call details, we'll create summary records
        
        // Check if we already have a summary for this agent/campaign/date
        $existingMetric = ViciCallMetrics::where('agent_id', $agentData['agent_id'])
            ->where('campaign_id', $agentData['campaign'])
            ->whereDate('created_at', Carbon::parse($timeRange['from'])->toDateString())
            ->first();
        
        if ($existingMetric) {
            // Update existing
            $existingMetric->total_calls = $agentData['total_calls'];
            $existingMetric->talk_time = $agentData['talk_time'];
            $existingMetric->connected = $agentData['total_calls'] > 0;
            $existingMetric->save();
        } else {
            // Create new summary record
            ViciCallMetrics::create([
                'agent_id' => $agentData['agent_id'],
                'campaign_id' => $agentData['campaign'],
                'total_calls' => $agentData['total_calls'],
                'talk_time' => $agentData['talk_time'],
                'connected' => $agentData['total_calls'] > 0,
                'status' => 'SUMMARY',
                'first_call_time' => Carbon::parse($timeRange['from']),
                'last_call_time' => Carbon::parse($timeRange['to']),
                'notes' => 'Agent stats summary for ' . $agentData['agent_name']
            ]);
        }
        
        $result['calls'] = $agentData['total_calls'];
        
        // Since we don't have phone numbers in agent stats, we can't match to leads
        // This would need to be done with a different API call
        
        return $result;
    }
    
    /**
     * Display results
     */
    private function displayResults(array $stats): void
    {
        $this->info('========================================');
        $this->info('           SYNC RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Agents Processed', number_format($stats['agents_processed'])],
                ['Total Calls Recorded', number_format($stats['calls_recorded'])],
                ['Leads Matched', number_format($stats['leads_matched'])],
                ['Orphan Calls Created', number_format($stats['orphans_created'])],
            ]
        );
        
        $this->newLine();
        $this->info('✅ Agent stats sync completed successfully!');
        $this->info('📊 View reports at: /admin/vici-reports');
    }
}



namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViciCallMetrics;
use App\Models\Lead;
use App\Models\OrphanCallLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncViciAgentStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:sync-agent-stats 
                            {--from= : Start date (defaults to today)}
                            {--to= : End date (defaults to today)}
                            {--days=1 : Number of days to sync if no from/to specified}
                            {--dry-run : Preview what would be synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync agent call statistics from Vici using agent_stats_export';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('   VICI AGENT STATS SYNC');
        $this->info('========================================');
        
        // Determine time range
        $timeRange = $this->determineTimeRange();
        
        $this->info("📅 Syncing from: {$timeRange['from']}");
        $this->info("📅 Syncing to: {$timeRange['to']}");
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No data will be saved');
        }
        
        // Fetch agent stats from Vici
        $agentStats = $this->fetchAgentStats($timeRange);
        
        if (empty($agentStats)) {
            $this->info('No agent stats found for the specified period.');
            return Command::SUCCESS;
        }
        
        $this->info("📞 Found " . count($agentStats) . " agent records to process");
        
        // Process stats
        $stats = [
            'agents_processed' => 0,
            'calls_recorded' => 0,
            'leads_matched' => 0,
            'orphans_created' => 0
        ];
        
        $bar = $this->output->createProgressBar(count($agentStats));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        foreach ($agentStats as $agentData) {
            $bar->setMessage("Processing agent: {$agentData['agent_name']}");
            
            if (!$this->option('dry-run')) {
                $result = $this->processAgentStats($agentData, $timeRange);
                $stats['agents_processed']++;
                $stats['calls_recorded'] += $result['calls'];
                $stats['leads_matched'] += $result['matched'];
                $stats['orphans_created'] += $result['orphans'];
            } else {
                $stats['agents_processed']++;
                $stats['calls_recorded'] += $agentData['total_calls'] ?? 0;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats);
        
        return Command::SUCCESS;
    }
    
    /**
     * Determine time range for sync
     */
    private function determineTimeRange(): array
    {
        if ($this->option('from') && $this->option('to')) {
            return [
                'from' => Carbon::parse($this->option('from'))->format('Y-m-d H:i:s'),
                'to' => Carbon::parse($this->option('to'))->format('Y-m-d H:i:s')
            ];
        }
        
        $days = $this->option('days') ?? 1;
        $to = Carbon::now();
        $from = $to->copy()->subDays($days)->startOfDay();
        
        return [
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Fetch agent stats from Vici API
     */
    private function fetchAgentStats(array $timeRange): array
    {
        $this->info('🔄 Fetching agent stats from Vici...');
        
        $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
        
        $params = [
            'source' => 'test',  // Use 'test' as source since 'brain' was rejected
            'user' => 'apiuser',
            'pass' => 'UZPATJ59GJAVKG8ES6',
            'function' => 'agent_stats_export',
            'datetime_start' => $timeRange['from'],
            'datetime_end' => $timeRange['to'],
            'agent_user' => '',  // Empty to get all agents
            'campaign' => ''     // Empty to get all campaigns
        ];
        
        try {
            $response = Http::timeout(30)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                return $this->parseAgentStats($response->body());
            }
            
            $this->error('API request failed: ' . $response->body());
            
        } catch (\Exception $e) {
            $this->error('Failed to fetch agent stats: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Parse agent stats response
     */
    private function parseAgentStats(string $response): array
    {
        $agents = [];
        $lines = explode("\n", trim($response));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'ERROR') !== false) continue;
            
            // Parse pipe-delimited format
            // Format: agent_id|agent_name|campaign|calls|talk_time|pause_time|dead_time|wait_time|...
            $fields = explode('|', $line);
            
            if (count($fields) >= 10) {
                $agents[] = [
                    'agent_id' => $fields[0] ?? null,
                    'agent_name' => $fields[1] ?? null,
                    'campaign' => $fields[2] ?? null,
                    'total_calls' => intval($fields[3] ?? 0),
                    'talk_time' => $this->parseTime($fields[4] ?? '0:00:00'),
                    'pause_time' => $this->parseTime($fields[5] ?? '0:00:00'),
                    'dead_time' => $this->parseTime($fields[6] ?? '0:00:00'),
                    'wait_time' => $this->parseTime($fields[7] ?? '0:00:00'),
                    'wrap_time' => $this->parseTime($fields[14] ?? '0:00:00'),
                    'customer_time' => $this->parseTime($fields[15] ?? '0:00:00'),
                ];
            }
        }
        
        return $agents;
    }
    
    /**
     * Parse time string to seconds
     */
    private function parseTime(string $time): int
    {
        // Format: H:MM:SS or MM:SS
        $parts = explode(':', $time);
        if (count($parts) == 3) {
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } elseif (count($parts) == 2) {
            return ($parts[0] * 60) + $parts[1];
        }
        return 0;
    }
    
    /**
     * Process agent stats and create/update metrics
     */
    private function processAgentStats(array $agentData, array $timeRange): array
    {
        $result = [
            'calls' => 0,
            'matched' => 0,
            'orphans' => 0
        ];
        
        // Create or update ViciCallMetrics record
        // Since we don't have individual call details, we'll create summary records
        
        // Check if we already have a summary for this agent/campaign/date
        $existingMetric = ViciCallMetrics::where('agent_id', $agentData['agent_id'])
            ->where('campaign_id', $agentData['campaign'])
            ->whereDate('created_at', Carbon::parse($timeRange['from'])->toDateString())
            ->first();
        
        if ($existingMetric) {
            // Update existing
            $existingMetric->total_calls = $agentData['total_calls'];
            $existingMetric->talk_time = $agentData['talk_time'];
            $existingMetric->connected = $agentData['total_calls'] > 0;
            $existingMetric->save();
        } else {
            // Create new summary record
            ViciCallMetrics::create([
                'agent_id' => $agentData['agent_id'],
                'campaign_id' => $agentData['campaign'],
                'total_calls' => $agentData['total_calls'],
                'talk_time' => $agentData['talk_time'],
                'connected' => $agentData['total_calls'] > 0,
                'status' => 'SUMMARY',
                'first_call_time' => Carbon::parse($timeRange['from']),
                'last_call_time' => Carbon::parse($timeRange['to']),
                'notes' => 'Agent stats summary for ' . $agentData['agent_name']
            ]);
        }
        
        $result['calls'] = $agentData['total_calls'];
        
        // Since we don't have phone numbers in agent stats, we can't match to leads
        // This would need to be done with a different API call
        
        return $result;
    }
    
    /**
     * Display results
     */
    private function displayResults(array $stats): void
    {
        $this->info('========================================');
        $this->info('           SYNC RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Agents Processed', number_format($stats['agents_processed'])],
                ['Total Calls Recorded', number_format($stats['calls_recorded'])],
                ['Leads Matched', number_format($stats['leads_matched'])],
                ['Orphan Calls Created', number_format($stats['orphans_created'])],
            ]
        );
        
        $this->newLine();
        $this->info('✅ Agent stats sync completed successfully!');
        $this->info('📊 View reports at: /admin/vici-reports');
    }
}


