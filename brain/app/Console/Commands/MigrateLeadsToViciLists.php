<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use App\Services\ViciDialerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateLeadsToViciLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:migrate-to-lists 
                            {--dry-run : Preview changes without updating}
                            {--start-date= : Process leads from this date}
                            {--end-date= : Process leads until this date}
                            {--update-vici : Also update leads in Vici system}
                            {--verbose-output : Show detailed information for each lead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze existing leads and assign them to appropriate Vici lists based on call history and status';

    /**
     * List assignments based on your flow strategy
     */
    private $listDefinitions = [
        101 => 'Fresh/New leads - Never called or < 3 attempts',
        102 => 'Retry leads - 3-5 attempts, no contact',
        103 => 'Callback leads - Scheduled callbacks or warm leads',
        104 => 'Qualified leads - Passed screening',
        105 => 'Hot leads - High interest, multiple contacts',
        106 => 'Stale leads - 6+ attempts or > 30 days old',
        199 => 'DNC/Bad numbers - Do not call or disconnected'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $updateVici = $this->option('update-vici');
        $verboseOutput = $this->option('verbose-output');
        
        $this->info('========================================');
        $this->info('  LEAD TO VICI LIST MIGRATION TOOL');
        $this->info('========================================');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }
        
        // Build query
        $query = Lead::with(['viciCallMetrics', 'callHistory']);
        
        // Date filters
        if ($startDate = $this->option('start-date')) {
            $query->where('created_at', '>=', $startDate);
            $this->info("📅 Processing leads from: $startDate");
        }
        
        if ($endDate = $this->option('end-date')) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
            $this->info("📅 Processing leads until: $endDate");
        }
        
        $totalLeads = $query->count();
        $this->info("📊 Total leads to process: " . number_format($totalLeads));
        $this->newLine();
        
        // Process statistics
        $stats = [
            'list_101' => 0,  // Fresh
            'list_102' => 0,  // Retry
            'list_103' => 0,  // Callback
            'list_104' => 0,  // Qualified
            'list_105' => 0,  // Hot
            'list_106' => 0,  // Stale
            'list_199' => 0,  // DNC
            'unchanged' => 0,
            'errors' => 0
        ];
        
        $migrationPlan = [];
        
        // Process leads in chunks
        $bar = $this->output->createProgressBar($totalLeads);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        $query->chunk(100, function ($leads) use (&$stats, &$migrationPlan, $isDryRun, $updateVici, $verboseOutput, $bar) {
            foreach ($leads as $lead) {
                $bar->setMessage("Processing lead #{$lead->id}");
                
                // Determine appropriate list
                $newListId = $this->determineListId($lead);
                $currentListId = $lead->vici_list_id;
                
                if ($verboseOutput) {
                    $this->outputLeadAnalysis($lead, $newListId);
                }
                
                // Track migration
                if ($currentListId != $newListId) {
                    $migrationPlan[] = [
                        'lead_id' => $lead->id,
                        'external_lead_id' => $lead->external_lead_id,
                        'phone' => $lead->phone,
                        'current_list' => $currentListId ?? 'none',
                        'new_list' => $newListId,
                        'reason' => $this->getAssignmentReason($lead, $newListId)
                    ];
                    
                    $stats["list_$newListId"]++;
                    
                    if (!$isDryRun) {
                        // Update lead in database
                        $lead->vici_list_id = $newListId;
                        $lead->save();
                        
                        // Update in Vici if requested
                        if ($updateVici && $lead->external_lead_id) {
                            $this->updateLeadInVici($lead, $newListId);
                        }
                    }
                } else {
                    $stats['unchanged']++;
                }
                
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats, $migrationPlan, $isDryRun);
        
        // Save migration report
        if (!$isDryRun && count($migrationPlan) > 0) {
            $this->saveMigrationReport($migrationPlan);
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Determine which list a lead should be in based on call history and status
     */
    private function determineListId($lead)
    {
        // Priority 1: Check for DNC/Bad status
        if (in_array($lead->status, ['dnc', 'bad_number', 'disconnected', 'do_not_call'])) {
            return 199; // DNC list
        }
        
        // Priority 2: Check if qualified
        if ($lead->status == 'qualified' || $lead->status == 'sold' || $lead->status == 'transferred') {
            return 104; // Qualified list
        }
        
        // Priority 3: Check for scheduled callbacks
        if ($lead->status == 'callback' || $lead->status == 'scheduled') {
            return 103; // Callback list
        }
        
        // Get call metrics
        $callMetrics = $lead->viciCallMetrics;
        $totalCalls = $callMetrics->total_calls ?? 0;
        $connected = $callMetrics->connected ?? false;
        $lastCallDate = $callMetrics->last_call_date ?? null;
        
        // Calculate lead age
        $leadAge = Carbon::parse($lead->created_at)->diffInDays(now());
        
        // Priority 4: Hot leads (connected multiple times, recent activity)
        if ($connected && $totalCalls >= 2 && $leadAge <= 7) {
            return 105; // Hot leads list
        }
        
        // Priority 5: Stale leads (too many attempts or too old)
        if ($totalCalls >= 6 || $leadAge > 30) {
            return 106; // Stale leads list
        }
        
        // Priority 6: Retry leads (3-5 attempts, no connection)
        if ($totalCalls >= 3 && $totalCalls <= 5 && !$connected) {
            return 102; // Retry list
        }
        
        // Priority 7: Fresh/New leads (less than 3 attempts)
        if ($totalCalls < 3) {
            return 101; // Fresh leads list
        }
        
        // Default to retry list if uncertain
        return 102;
    }
    
    /**
     * Get human-readable reason for list assignment
     */
    private function getAssignmentReason($lead, $listId)
    {
        $callMetrics = $lead->viciCallMetrics;
        $totalCalls = $callMetrics->total_calls ?? 0;
        $connected = $callMetrics->connected ?? false;
        $leadAge = Carbon::parse($lead->created_at)->diffInDays(now());
        
        switch ($listId) {
            case 101:
                return "Fresh lead with {$totalCalls} attempts";
            case 102:
                return "Retry lead with {$totalCalls} attempts, no contact";
            case 103:
                return "Callback scheduled or warm lead";
            case 104:
                return "Qualified lead - status: {$lead->status}";
            case 105:
                return "Hot lead - {$totalCalls} calls, connected, {$leadAge} days old";
            case 106:
                return "Stale lead - {$totalCalls} attempts or {$leadAge} days old";
            case 199:
                return "DNC/Bad number - status: {$lead->status}";
            default:
                return "Based on call history and status";
        }
    }
    
    /**
     * Output detailed lead analysis
     */
    private function outputLeadAnalysis($lead, $newListId)
    {
        $this->newLine();
        $this->info("Lead #{$lead->id} - Phone: {$lead->phone}");
        
        $callMetrics = $lead->viciCallMetrics;
        if ($callMetrics) {
            $this->line("  📞 Calls: {$callMetrics->total_calls}");
            $this->line("  🔗 Connected: " . ($callMetrics->connected ? 'Yes' : 'No'));
            $this->line("  📅 Last Call: " . ($callMetrics->last_call_date ?? 'Never'));
        } else {
            $this->line("  📞 No call history");
        }
        
        $this->line("  📋 Status: {$lead->status}");
        $this->line("  🗓️ Age: " . Carbon::parse($lead->created_at)->diffInDays(now()) . " days");
        $this->line("  ➡️ Assigned to: List {$newListId} - {$this->listDefinitions[$newListId]}");
        $this->line("  💡 Reason: " . $this->getAssignmentReason($lead, $newListId));
    }
    
    /**
     * Update lead in Vici system
     */
    private function updateLeadInVici($lead, $newListId)
    {
        try {
            $viciService = app(ViciDialerService::class);
            
            // Build update data
            $updateData = [
                'lead_id' => $lead->external_lead_id,
                'list_id' => $newListId,
                'modify_list_id' => 'Y'
            ];
            
            // Call Vici API to update lead
            $result = $viciService->updateLead($lead, $updateData);
            
            if ($result['success']) {
                Log::info("Updated lead {$lead->external_lead_id} to list {$newListId} in Vici");
            } else {
                Log::warning("Failed to update lead {$lead->external_lead_id} in Vici", $result);
            }
            
        } catch (\Exception $e) {
            Log::error("Error updating lead in Vici", [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Display migration results
     */
    private function displayResults($stats, $migrationPlan, $isDryRun)
    {
        $this->info('========================================');
        $this->info('           MIGRATION RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['List ID', 'Description', 'Lead Count'],
            [
                ['101', 'Fresh/New leads', number_format($stats['list_101'])],
                ['102', 'Retry leads', number_format($stats['list_102'])],
                ['103', 'Callback leads', number_format($stats['list_103'])],
                ['104', 'Qualified leads', number_format($stats['list_104'])],
                ['105', 'Hot leads', number_format($stats['list_105'])],
                ['106', 'Stale leads', number_format($stats['list_106'])],
                ['199', 'DNC/Bad numbers', number_format($stats['list_199'])],
                ['---', 'Unchanged', number_format($stats['unchanged'])],
            ]
        );
        
        $totalMigrated = array_sum([
            $stats['list_101'], $stats['list_102'], $stats['list_103'],
            $stats['list_104'], $stats['list_105'], $stats['list_106'],
            $stats['list_199']
        ]);
        
        $this->newLine();
        $this->info("📊 Total leads to migrate: " . number_format($totalMigrated));
        $this->info("✅ Leads unchanged: " . number_format($stats['unchanged']));
        
        if ($isDryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. No changes were made.');
            $this->info('Run without --dry-run to apply changes.');
            
            // Show sample of migration plan
            if (count($migrationPlan) > 0) {
                $this->newLine();
                $this->info('Sample migrations (first 10):');
                $sample = array_slice($migrationPlan, 0, 10);
                
                $this->table(
                    ['Lead ID', 'Phone', 'Current List', 'New List', 'Reason'],
                    array_map(function($m) {
                        return [
                            $m['lead_id'],
                            substr($m['phone'], 0, 3) . '****' . substr($m['phone'], -4),
                            $m['current_list'],
                            $m['new_list'],
                            substr($m['reason'], 0, 40) . '...'
                        ];
                    }, $sample)
                );
            }
        } else {
            $this->newLine();
            $this->info('✅ Migration completed successfully!');
        }
    }
    
    /**
     * Save migration report to file
     */
    private function saveMigrationReport($migrationPlan)
    {
        $filename = 'migration_report_' . date('Y-m-d_H-i-s') . '.json';
        $path = storage_path('app/migrations/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        // Save report
        file_put_contents($path, json_encode([
            'timestamp' => now()->toIso8601String(),
            'total_migrated' => count($migrationPlan),
            'migrations' => $migrationPlan
        ], JSON_PRETTY_PRINT));
        
        $this->info("📄 Migration report saved to: storage/app/migrations/{$filename}");
    }
}
