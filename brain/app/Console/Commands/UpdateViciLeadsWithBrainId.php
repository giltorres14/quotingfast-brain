<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\ViciDialerService;
use Illuminate\Support\Facades\Log;

class UpdateViciLeadsWithBrainId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:update-brain-ids 
                            {--test : Run in test mode with only 10 leads}
                            {--phone= : Update specific phone number}
                            {--batch=100 : Number of leads to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing Vici leads with Brain Lead IDs (13-digit external_lead_id)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Vici Lead ID Update Process');
        $this->info('This will update vendor_lead_code in Vici with Brain\'s 13-digit external_lead_id');
        
        $viciService = new ViciDialerService();
        
        // Build query
        $query = Lead::query();
        
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
        if (!$this->option('test') && !$this->confirm("Do you want to update {$totalLeads} leads in Vici?")) {
            $this->info('Operation cancelled');
            return 0;
        }
        
        $batchSize = (int) $this->option('batch');
        $processed = 0;
        $updated = 0;
        $failed = 0;
        $notFound = 0;
        
        $this->info("Processing in batches of {$batchSize}...\n");
        
        // Process in batches
        $query->chunk($batchSize, function ($leads) use (&$processed, &$updated, &$failed, &$notFound, $viciService) {
            foreach ($leads as $lead) {
                $processed++;
                
                // Show progress
                $this->line("Processing [{$processed}]: {$lead->name} - Phone: {$lead->phone}");
                
                // Ensure lead has external_lead_id
                if (empty($lead->external_lead_id) || strlen($lead->external_lead_id) !== 13) {
                    $this->warn("  ⚠️  Generating missing 13-digit ID for lead {$lead->id}");
                    $lead->external_lead_id = Lead::generateExternalLeadId();
                    $lead->save();
                }
                
                // Update in Vici
                $result = $viciService->updateViciLeadWithBrainId($lead);
                
                if ($result['success']) {
                    $updated++;
                    $this->info("  ✅ Updated - Brain ID: {$result['brain_lead_id']} | Vici ID: {$result['vici_lead_id']}");
                    if (isset($result['old_vendor_code'])) {
                        $this->line("     Old vendor_code: {$result['old_vendor_code']}");
                    }
                } elseif (strpos($result['message'], 'not found') !== false) {
                    $notFound++;
                    $this->warn("  ⚠️  Not found in Vici - Brain ID: {$result['brain_lead_id']}");
                } else {
                    $failed++;
                    $this->error("  ❌ Failed: {$result['message']}");
                }
                
                // Show progress every 10 leads
                if ($processed % 10 === 0) {
                    $this->info("\n📊 Progress: {$processed}/{$this->argument('0')} processed | {$updated} updated | {$notFound} not found | {$failed} failed\n");
                }
            }
        });
        
        // Final summary
        $this->info("\n" . str_repeat('=', 60));
        $this->info('🎯 VICI LEAD UPDATE COMPLETE');
        $this->info(str_repeat('=', 60));
        $this->info("Total Processed: {$processed}");
        $this->info("✅ Successfully Updated: {$updated}");
        $this->warn("⚠️  Not Found in Vici: {$notFound}");
        if ($failed > 0) {
            $this->error("❌ Failed Updates: {$failed}");
        }
        
        // Log summary
        Log::info('Vici Lead ID Update Complete', [
            'total_processed' => $processed,
            'updated' => $updated,
            'not_found' => $notFound,
            'failed' => $failed
        ]);
        
        return 0;
    }
}

