<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use PDO;
use Exception;

class UpdateViciVendorCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:update-vendor-codes 
                            {--phone= : Update specific phone number}
                            {--batch=100 : Number of leads to process per batch}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Vici leads with Brain external_lead_id as vendor_lead_code';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = $this->option('batch');
        $specificPhone = $this->option('phone');
        
        $this->info('========================================');
        $this->info('Vici Vendor Code Update Tool');
        $this->info('========================================');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        try {
            // Connect to Vici database
            $viciDb = new PDO(
                'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
                env('VICI_DB_USER', 'cron'),
                env('VICI_DB_PASS', '1234')
            );
            
            $this->info('✅ Connected to Vici database');
            
            // Get Brain leads that need to be synced
            $query = Lead::query();
            
            if ($specificPhone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $specificPhone);
                $query->where('phone', 'LIKE', '%' . substr($cleanPhone, -10));
                $this->info("Filtering for phone: $specificPhone");
            }
            
            $totalLeads = $query->count();
            $this->info("Found $totalLeads leads in Brain to process");
            
            $updated = 0;
            $skipped = 0;
            $notInVici = 0;
            $errors = 0;
            
            // Process in batches
            $query->chunk($batchSize, function ($leads) use ($viciDb, $isDryRun, &$updated, &$skipped, &$notInVici, &$errors) {
                foreach ($leads as $lead) {
                    try {
                        // Clean phone for matching
                        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
                        
                        // Check if lead exists in Vici
                        $checkStmt = $viciDb->prepare("
                            SELECT lead_id, vendor_lead_code, first_name, last_name, phone_number
                            FROM vicidial_list 
                            WHERE phone_number = :phone
                            ORDER BY lead_id DESC
                        ");
                        
                        $checkStmt->execute(['phone' => $cleanPhone]);
                        $viciLeads = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($viciLeads)) {
                            $this->line("❌ Not in Vici: {$lead->name} - {$lead->phone}");
                            $notInVici++;
                            continue;
                        }
                        
                        foreach ($viciLeads as $viciLead) {
                            // Check if already has correct vendor_lead_code
                            if ($viciLead['vendor_lead_code'] == $lead->external_lead_id) {
                                $this->line("✓ Already synced: {$lead->name} - Vici#{$viciLead['lead_id']}");
                                $skipped++;
                                continue;
                            }
                            
                            if ($isDryRun) {
                                $this->info("Would update: Vici#{$viciLead['lead_id']} - Set vendor_lead_code to {$lead->external_lead_id}");
                            } else {
                                // Update Vici with Brain's external_lead_id
                                $updateStmt = $viciDb->prepare("
                                    UPDATE vicidial_list 
                                    SET vendor_lead_code = :vendor_code
                                    WHERE lead_id = :lead_id
                                ");
                                
                                $updateStmt->execute([
                                    'vendor_code' => $lead->external_lead_id,
                                    'lead_id' => $viciLead['lead_id']
                                ]);
                                
                                $this->info("✅ Updated: Vici#{$viciLead['lead_id']} - {$lead->name} - vendor_code: {$lead->external_lead_id}");
                                
                                // Also update Brain with Vici lead_id if not stored
                                $meta = json_decode($lead->meta ?? '{}', true);
                                if (!isset($meta['vici_lead_id'])) {
                                    $meta['vici_lead_id'] = $viciLead['lead_id'];
                                    $lead->meta = json_encode($meta);
                                    $lead->save();
                                    $this->line("  └─ Stored Vici ID in Brain meta");
                                }
                            }
                            
                            $updated++;
                        }
                        
                    } catch (Exception $e) {
                        $this->error("Error processing lead {$lead->id}: " . $e->getMessage());
                        $errors++;
                    }
                }
                
                // Show progress
                $this->info("Progress: Updated: $updated | Skipped: $skipped | Not in Vici: $notInVici | Errors: $errors");
            });
            
            // Final summary
            $this->info('');
            $this->info('========================================');
            $this->info('SUMMARY');
            $this->info('========================================');
            $this->info("✅ Updated: $updated");
            $this->info("⏭️  Skipped (already synced): $skipped");
            $this->info("❌ Not found in Vici: $notInVici");
            $this->info("⚠️  Errors: $errors");
            $this->info('========================================');
            
            if ($isDryRun) {
                $this->warn('This was a DRY RUN - no changes were made');
                $this->info('Run without --dry-run to apply changes');
            }
            
        } catch (Exception $e) {
            $this->error('Failed to connect to Vici: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
