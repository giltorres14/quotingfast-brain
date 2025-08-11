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
                            {--campaigns=* : Filter by Vici campaigns (default: Auto2,Autodial)}
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
        $campaigns = $this->option('campaigns') ?: ['Auto2', 'Autodial'];
        
        $this->info('========================================');
        $this->info('Vici Vendor Code Update Tool');
        $this->info('========================================');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Filtering by campaigns: ' . implode(', ', $campaigns));
        
        try {
            // First, ensure we're whitelisted
            $this->info('Ensuring Vici whitelist...');
            $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
            $ch = curl_init($whitelistUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $whitelistResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $this->info('✅ Whitelisted successfully');
            } else {
                $this->warn('⚠️  Whitelist may have failed (HTTP ' . $httpCode . ')');
            }
            
            // Connect to Vici database
            $viciDb = new PDO(
                'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
                env('VICI_DB_USER', 'cron'),
                env('VICI_DB_PASS', '1234')
            );
            
            $this->info('✅ Connected to Vici database');
            
            // Build campaign filter for SQL
            $campaignPlaceholders = array_map(function($i) { return ':campaign' . $i; }, range(0, count($campaigns) - 1));
            $campaignParams = [];
            foreach ($campaigns as $i => $campaign) {
                // Add both uppercase and lowercase versions
                $campaignParams['campaign' . $i] = $campaign;
            }
            
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
            $wrongCampaign = 0;
            $errors = 0;
            
            // Process in batches
            $query->chunk($batchSize, function ($leads) use ($viciDb, $isDryRun, $campaigns, $campaignPlaceholders, $campaignParams, &$updated, &$skipped, &$notInVici, &$wrongCampaign, &$errors) {
                foreach ($leads as $lead) {
                    try {
                        // Clean phone for matching
                        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
                        
                        // Check if lead exists in Vici with campaign filter
                        $sql = "
                            SELECT lead_id, vendor_lead_code, campaign_id, first_name, last_name, phone_number
                            FROM vicidial_list 
                            WHERE phone_number = :phone
                            AND UPPER(campaign_id) IN (" . implode(',', array_map(function($c) { return "UPPER(:$c)"; }, array_keys($campaignParams))) . ")
                            ORDER BY lead_id DESC
                        ";
                        
                        $checkStmt = $viciDb->prepare($sql);
                        $params = array_merge(['phone' => $cleanPhone], $campaignParams);
                        $checkStmt->execute($params);
                        $viciLeads = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($viciLeads)) {
                            // Check if in Vici but wrong campaign
                            $checkAllStmt = $viciDb->prepare("
                                SELECT campaign_id FROM vicidial_list 
                                WHERE phone_number = :phone LIMIT 1
                            ");
                            $checkAllStmt->execute(['phone' => $cleanPhone]);
                            $anyVici = $checkAllStmt->fetch();
                            
                            if ($anyVici) {
                                $this->line("⚠️  Wrong campaign: {$lead->name} - {$lead->phone} (in {$anyVici['campaign_id']})");
                                $wrongCampaign++;
                            } else {
                                $this->line("❌ Not in Vici: {$lead->name} - {$lead->phone}");
                                $notInVici++;
                            }
                            continue;
                        }
                        
                        foreach ($viciLeads as $viciLead) {
                            // Check if already has correct vendor_lead_code
                            if ($viciLead['vendor_lead_code'] == $lead->external_lead_id) {
                                $this->line("✓ Already synced: {$lead->name} - Vici#{$viciLead['lead_id']} ({$viciLead['campaign_id']})");
                                $skipped++;
                                continue;
                            }
                            
                            if ($isDryRun) {
                                $this->info("Would update: Vici#{$viciLead['lead_id']} ({$viciLead['campaign_id']}) - Set vendor_lead_code to {$lead->external_lead_id}");
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
                                
                                $this->info("✅ Updated: Vici#{$viciLead['lead_id']} ({$viciLead['campaign_id']}) - {$lead->name} - vendor_code: {$lead->external_lead_id}");
                                
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
                $this->info("Progress: Updated: $updated | Skipped: $skipped | Not in Vici: $notInVici | Wrong Campaign: $wrongCampaign | Errors: $errors");
            });
            
            // Final summary
            $this->info('');
            $this->info('========================================');
            $this->info('SUMMARY');
            $this->info('========================================');
            $this->info("✅ Updated: $updated");
            $this->info("⏭️  Skipped (already synced): $skipped");
            $this->info("❌ Not found in Vici: $notInVici");
            $this->info("⚠️  In wrong campaign: $wrongCampaign");
            $this->info("⚠️  Errors: $errors");
            $this->info('========================================');
            
            if ($isDryRun) {
                $this->warn('This was a DRY RUN - no changes were made');
                $this->info('Run without --dry-run to apply changes');
            }
            
        } catch (Exception $e) {
            $this->error('Failed to connect to Vici: ' . $e->getMessage());
            $this->error('Make sure Vici database is accessible and credentials are correct');
            return 1;
        }
        
        return 0;
    }
}