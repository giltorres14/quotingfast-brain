<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Lead;

class ArchiveOldViciLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vici:archive-old-leads 
                            {--days=90 : Number of days old to archive}
                            {--dry-run : Show what would be archived without actually doing it}
                            {--limit=1000 : Maximum number of leads to archive per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move leads older than 90 days from opt-in date to Archive list (199) in Vici';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("=== VICI LEAD ARCHIVAL ===");
        $this->info("Archiving leads older than {$days} days (before {$cutoffDate->format('Y-m-d')})");
        $this->info("Target list: 199 (Archive)");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }
        
        // First, ensure Archive list (199) exists in Vici
        if (!$dryRun) {
            $this->ensureArchiveListExists();
        }
        
        // Get old leads from Brain database
        $oldLeads = Lead::where('opt_in_date', '<', $cutoffDate)
            ->whereNotNull('external_lead_id')
            ->whereRaw("LENGTH(external_lead_id) = 13")
            ->whereRaw("external_lead_id ~ '^[0-9]+$'")
            ->select('id', 'external_lead_id', 'phone', 'opt_in_date')
            ->limit($limit)
            ->get();
        
        $this->info("Found " . $oldLeads->count() . " leads to archive");
        
        if ($oldLeads->isEmpty()) {
            $this->info("No leads need archiving");
            return Command::SUCCESS;
        }
        
        // Build SQL to move leads in Vici
        $totalMoved = 0;
        $totalFailed = 0;
        
        foreach ($oldLeads->chunk(100) as $chunk) {
            $brainIds = $chunk->pluck('external_lead_id')->toArray();
            $brainIdList = "'" . implode("','", $brainIds) . "'";
            
            // SQL to update list_id to 199 for these leads
            $sql = "UPDATE vicidial_list 
                    SET list_id = '199', 
                        modify_date = NOW(),
                        comments = CONCAT('Archived on " . date('Y-m-d') . " (90+ days old) | ', IFNULL(comments, ''))
                    WHERE vendor_lead_code IN ({$brainIdList})
                    AND list_id != '199'";
            
            if ($dryRun) {
                $this->info("Would execute: " . substr($sql, 0, 200) . "...");
                $totalMoved += count($brainIds);
            } else {
                try {
                    // Execute via proxy
                    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($sql) . " 2>&1"
                    ]);
                    
                    if ($response->successful()) {
                        $totalMoved += count($brainIds);
                        $this->info("✅ Archived batch of " . count($brainIds) . " leads");
                        
                        // Log the archival
                        foreach ($chunk as $lead) {
                            Log::info('Lead archived to Vici list 199', [
                                'brain_id' => $lead->id,
                                'external_lead_id' => $lead->external_lead_id,
                                'opt_in_date' => $lead->opt_in_date,
                                'days_old' => $cutoffDate->diffInDays($lead->opt_in_date)
                            ]);
                        }
                    } else {
                        $totalFailed += count($brainIds);
                        $this->error("❌ Failed to archive batch: " . ($response->json()['error'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    $totalFailed += count($brainIds);
                    $this->error("❌ Exception: " . $e->getMessage());
                }
            }
            
            // Small delay between batches
            usleep(100000); // 0.1 second
        }
        
        $this->info("");
        $this->info("=== ARCHIVAL COMPLETE ===");
        $this->info("✅ Successfully archived: " . number_format($totalMoved) . " leads");
        
        if ($totalFailed > 0) {
            $this->warn("❌ Failed to archive: " . number_format($totalFailed) . " leads");
        }
        
        if (!$dryRun) {
            // Verify the archive
            $this->verifyArchive();
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Ensure Archive list (199) exists in Vici
     */
    private function ensureArchiveListExists()
    {
        $checkSql = "SELECT list_id FROM vicidial_lists WHERE list_id = '199'";
        
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkSql) . " 2>&1"
        ]);
        
        $output = $response->json()['output'] ?? '';
        
        if (empty(trim($output))) {
            $this->info("Creating Archive list (199) in Vici...");
            
            $createSql = "INSERT INTO vicidial_lists 
                         (list_id, list_name, campaign_id, active, list_description) 
                         VALUES 
                         ('199', 'Archive - 90+ Days Old', 'AUTODIAL', 'N', 'Archived leads older than 90 days from opt-in')";
            
            Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($createSql) . " 2>&1"
            ]);
            
            $this->info("✅ Archive list (199) created");
        } else {
            $this->info("✅ Archive list (199) already exists");
        }
    }
    
    /**
     * Verify the archive operation
     */
    private function verifyArchive()
    {
        $verifySql = "SELECT COUNT(*) FROM vicidial_list WHERE list_id = '199'";
        
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($verifySql) . " 2>&1"
        ]);
        
        $output = $response->json()['output'] ?? '';
        $output = preg_replace('/Could not create.*\n|Failed to add.*\n/', '', $output);
        $count = intval(trim($output));
        
        $this->info("📊 Total leads in Archive list (199): " . number_format($count));
    }
}

