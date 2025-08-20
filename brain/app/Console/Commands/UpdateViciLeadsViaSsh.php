<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\ViciSshTunnelService;
use Illuminate\Support\Facades\Log;

class UpdateViciLeadsViaSsh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:update-via-ssh 
                            {--test : Run in test mode with only 10 leads}
                            {--phone= : Update specific phone number}
                            {--batch=25 : Number of leads to process per batch}
                            {--source= : Filter by lead source (e.g., LQF_BULK, SURAJ_BULK)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Vici leads with Brain Lead IDs using SSH tunnel (port 11845)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Vici Lead ID Update via SSH (Port 11845)');
        $this->info('This will update vendor_lead_code in Vici using SSH tunnel');
        $this->newLine();
        
        $sshService = new ViciSshTunnelService();
        
        // Build query
        $query = Lead::query();
        
        // Filter by source if provided
        if ($source = $this->option('source')) {
            $query->where('source', $source);
            $this->info("Filtering by source: {$source}");
        }
        
        // If specific phone provided
        if ($phone = $this->option('phone')) {
            $query->where('phone', $phone);
            $this->info("Updating lead with phone: {$phone}");
        }
        
        // If test mode, limit to 10
        if ($this->option('test')) {
            $query->limit(10);
            $this->warn('TEST MODE: Processing only 10 leads');
        }
        
        // Get total count
        $totalLeads = $query->count();
        $this->info("Total leads to process: {$totalLeads}");
        
        if ($totalLeads === 0) {
            $this->warn('No leads found to process');
            return 0;
        }
        
        // Confirm before proceeding
        if (!$this->option('test') && $totalLeads > 100) {
            if (!$this->confirm("Do you want to update {$totalLeads} leads in Vici via SSH?")) {
                $this->info('Operation cancelled');
                return 0;
            }
        }
        
        $batchSize = (int) $this->option('batch');
        $processed = 0;
        $updated = 0;
        $failed = 0;
        $notFound = 0;
        
        $this->info("Processing in batches of {$batchSize}...\n");
        
        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalLeads);
        $progressBar->start();
        
        // Process in batches
        $query->chunk($batchSize, function ($leads) use (&$processed, &$updated, &$failed, &$notFound, $sshService, $progressBar) {
            foreach ($leads as $lead) {
                $processed++;
                
                // Ensure lead has external_lead_id
                if (empty($lead->external_lead_id) || strlen($lead->external_lead_id) !== 13) {
                    $lead->external_lead_id = Lead::generateExternalLeadId();
                    $lead->save();
                }
                
                // Update in Vici via SSH
                $result = $sshService->updateViciLeadWithBrainId($lead);
                
                if ($result['success']) {
                    $updated++;
                    Log::info("Vici SSH Update Success", [
                        'brain_id' => $result['brain_lead_id'],
                        'vici_id' => $result['vici_lead_id'] ?? null,
                        'old_vendor_code' => $result['old_vendor_code'] ?? null
                    ]);
                } elseif (strpos($result['message'], 'not found') !== false) {
                    $notFound++;
                    Log::warning("Lead not found in Vici", [
                        'brain_id' => $result['brain_lead_id'],
                        'phone' => $lead->phone
                    ]);
                } else {
                    $failed++;
                    Log::error("Vici SSH Update Failed", [
                        'brain_id' => $result['brain_lead_id'] ?? null,
                        'message' => $result['message']
                    ]);
                }
                
                // Update progress bar
                $progressBar->advance();
                
                // Small delay to avoid overwhelming SSH
                usleep(200000); // 0.2 second delay
            }
        });
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Final summary
        $this->info(str_repeat('=', 60));
        $this->info('🎯 VICI SSH UPDATE COMPLETE');
        $this->info(str_repeat('=', 60));
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $processed],
                ['✅ Successfully Updated', $updated],
                ['⚠️ Not Found in Vici', $notFound],
                ['❌ Failed Updates', $failed],
            ]
        );
        
        // Success rate
        if ($processed > 0) {
            $successRate = round(($updated / $processed) * 100, 2);
            $this->info("Success Rate: {$successRate}%");
        }
        
        // Log summary
        Log::info('Vici SSH Update Complete', [
            'total_processed' => $processed,
            'updated' => $updated,
            'not_found' => $notFound,
            'failed' => $failed
        ]);
        
        return 0;
    }
}






