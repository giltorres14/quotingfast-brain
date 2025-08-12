<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\ViciDialerService;
use Illuminate\Support\Facades\Log;

class PushPendingLeadsToVici extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:push-pending 
                            {--limit=100 : Number of leads to push per batch}
                            {--dry-run : Preview what would be pushed}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push pending leads to Vici that were stored during migration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('  PUSH PENDING LEADS TO VICI');
        $this->info('========================================');
        
        // Check if push is enabled
        if (!config('services.vici.push_enabled', false) && !$this->option('force')) {
            $this->warn('⚠️ VICI_PUSH_ENABLED is currently FALSE');
            $this->warn('This command will temporarily override that setting.');
            
            if (!$this->confirm('Do you want to continue?')) {
                return Command::SUCCESS;
            }
        }
        
        // Get pending leads
        $query = Lead::where('status', 'pending_vici_push')
                    ->orWhere('status', 'new')
                    ->whereNull('external_lead_id')
                    ->orderBy('created_at', 'asc');
        
        $totalPending = $query->count();
        
        if ($totalPending == 0) {
            $this->info('✅ No pending leads to push');
            return Command::SUCCESS;
        }
        
        $this->info("📊 Found {$totalPending} pending leads");
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No leads will be pushed');
            
            // Show sample
            $sample = $query->limit(10)->get();
            $this->table(
                ['ID', 'Phone', 'Name', 'Created', 'Status'],
                $sample->map(function($lead) {
                    return [
                        $lead->id,
                        substr($lead->phone, 0, 3) . '****' . substr($lead->phone, -4),
                        $lead->first_name . ' ' . $lead->last_name,
                        $lead->created_at->format('Y-m-d H:i'),
                        $lead->status
                    ];
                })
            );
            
            return Command::SUCCESS;
        }
        
        // Confirm push
        if (!$this->option('force')) {
            if (!$this->confirm("Push {$totalPending} leads to Vici?")) {
                return Command::SUCCESS;
            }
        }
        
        // Process in batches
        $limit = $this->option('limit');
        $viciService = app(ViciDialerService::class);
        
        $stats = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        $bar = $this->output->createProgressBar($totalPending);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        
        $query->chunk($limit, function($leads) use ($viciService, &$stats, $bar) {
            foreach ($leads as $lead) {
                $bar->setMessage("Pushing lead #{$lead->id}");
                
                // Temporarily enable push
                config(['services.vici.push_enabled' => true]);
                
                $result = $viciService->pushLead($lead);
                
                // Restore setting
                config(['services.vici.push_enabled' => false]);
                
                if ($result['success']) {
                    $stats['success']++;
                    
                    // Update lead status
                    $lead->status = 'pushed_to_vici';
                    $lead->save();
                } else {
                    $stats['failed']++;
                    $stats['errors'][] = "Lead {$lead->id}: " . $result['message'];
                    
                    Log::error('Failed to push lead to Vici', [
                        'lead_id' => $lead->id,
                        'error' => $result['message']
                    ]);
                }
                
                $bar->advance();
                
                // Small delay to avoid overwhelming
                usleep(100000); // 0.1 second
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->info('========================================');
        $this->info('           PUSH RESULTS');
        $this->info('========================================');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Successfully Pushed', $stats['success']],
                ['Failed', $stats['failed']],
                ['Total Processed', $stats['success'] + $stats['failed']]
            ]
        );
        
        if ($stats['failed'] > 0) {
            $this->error("\n❌ {$stats['failed']} leads failed to push:");
            foreach (array_slice($stats['errors'], 0, 10) as $error) {
                $this->line("  - {$error}");
            }
            
            if (count($stats['errors']) > 10) {
                $this->line("  ... and " . (count($stats['errors']) - 10) . " more");
            }
        }
        
        $this->newLine();
        $this->info('✅ Push operation completed');
        
        return Command::SUCCESS;
    }
}
