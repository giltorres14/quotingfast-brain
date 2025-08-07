<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeadQueue;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class ProcessLeadQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:process-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued leads that came in during downtime';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing lead queue...');
        
        $queuedLeads = LeadQueue::pending()
                               ->orderBy('created_at', 'asc')
                               ->limit(100)
                               ->get();
        
        if ($queuedLeads->isEmpty()) {
            $this->info('No pending leads in queue.');
            return 0;
        }
        
        $this->info("Found {$queuedLeads->count()} leads to process.");
        
        foreach ($queuedLeads as $queuedLead) {
            try {
                $queuedLead->markAsProcessing();
                
                // Process the lead using the same logic as webhook
                $data = $queuedLead->payload;
                $contact = $data['contact'] ?? [];
                
                // Generate external lead ID
                $externalLeadId = $this->generateLeadId();
                
                $leadData = [
                    'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
                    'first_name' => $contact['first_name'] ?? null,
                    'last_name' => $contact['last_name'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'email' => $contact['email'] ?? null,
                    'address' => $contact['address'] ?? null,
                    'city' => $contact['city'] ?? null,
                    'state' => $contact['state'] ?? null,
                    'zip_code' => $contact['zip_code'] ?? null,
                    'source' => $queuedLead->source,
                    'type' => $data['type'] ?? 'auto',
                    'external_lead_id' => $externalLeadId,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'drivers' => json_encode($data['data']['drivers'] ?? []),
                    'vehicles' => json_encode($data['data']['vehicles'] ?? []),
                    'current_policy' => json_encode($data['data']['current_policy'] ?? null),
                    'payload' => json_encode($data),
                    'received_at' => $queuedLead->created_at, // Use original receive time
                    'joined_at' => now(),
                ];
                
                $lead = Lead::create($leadData);
                
                $queuedLead->markAsCompleted();
                
                $this->info("✓ Processed lead: {$lead->name} (ID: {$externalLeadId})");
                
                Log::info('Lead processed from queue', [
                    'queue_id' => $queuedLead->id,
                    'lead_id' => $lead->id,
                    'external_lead_id' => $externalLeadId
                ]);
                
            } catch (\Exception $e) {
                $queuedLead->markAsFailed($e->getMessage());
                
                $this->error("✗ Failed to process queue item {$queuedLead->id}: " . $e->getMessage());
                
                Log::error('Failed to process queued lead', [
                    'queue_id' => $queuedLead->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info('Queue processing complete.');
        return 0;
    }
    
    /**
     * Generate lead ID (same as in routes/web.php)
     */
    private function generateLeadId()
    {
        $timestamp = time();
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return $timestamp . $random;
    }
}
