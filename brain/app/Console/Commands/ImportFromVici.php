<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use PDO;
use Exception;

class ImportFromVici extends Command
{
    protected $signature = 'vici:import-to-brain 
                            {--campaigns=Auto2,Autodial : Comma-separated list of campaigns}
                            {--limit= : Limit number of leads to import}
                            {--dry-run : Preview without making changes}
                            {--phone= : Import specific phone number only}';

    protected $description = 'Import leads FROM Vici INTO Brain to avoid duplicates';

    private $stats = [
        'total' => 0,
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];

    public function handle()
    {
        $campaigns = explode(',', $this->option('campaigns'));
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        $phone = $this->option('phone');

        $this->info('=== IMPORTING FROM VICI TO BRAIN ===');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        try {
            // Trigger whitelist
            $this->info('Triggering Vici whitelist...');
            $this->triggerWhitelist();
            sleep(2);

            // Connect to Vici
            $this->info('Connecting to Vici database...');
            $viciDb = $this->connectToVici();

            // Build query
            $query = "
                SELECT 
                    lead_id,
                    list_id,
                    phone_number,
                    first_name,
                    last_name,
                    email,
                    address1 as address,
                    city,
                    state,
                    postal_code as zip_code,
                    vendor_lead_code,
                    campaign_id,
                    status,
                    last_local_call_time,
                    called_count,
                    date_of_birth,
                    owner,
                    comments
                FROM vicidial_list 
                WHERE campaign_id IN (" . implode(',', array_fill(0, count($campaigns), '?')) . ")
            ";

            $params = $campaigns;

            if ($phone) {
                $query .= " AND phone_number = ?";
                $params[] = $phone;
            }

            if ($limit) {
                $query .= " LIMIT " . (int)$limit;
            }

            $stmt = $viciDb->prepare($query);
            $stmt->execute($params);

            $this->info('Processing Vici leads...');
            $bar = $this->output->createProgressBar($stmt->rowCount());

            while ($viciLead = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->stats['total']++;
                $this->processViciLead($viciLead, $dryRun);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Display results
            $this->displayResults();

        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function processViciLead($viciLead, $dryRun)
    {
        try {
            // Check if lead exists in Brain
            $existingLead = Lead::where('phone', $viciLead['phone_number'])->first();

            // Calculate days since last call
            $lastCallDays = null;
            if ($viciLead['last_local_call_time']) {
                $lastCall = \Carbon\Carbon::parse($viciLead['last_local_call_time']);
                $lastCallDays = $lastCall->diffInDays(now());
            }

            // Prepare lead data
            $leadData = [
                'name' => trim($viciLead['first_name'] . ' ' . $viciLead['last_name']) ?: 'Unknown',
                'first_name' => $viciLead['first_name'],
                'last_name' => $viciLead['last_name'],
                'phone' => $viciLead['phone_number'],
                'email' => $viciLead['email'],
                'address' => $viciLead['address'],
                'city' => $viciLead['city'],
                'state' => $viciLead['state'],
                'zip_code' => $viciLead['zip_code'],
                'source' => 'vici_import',
                'type' => $this->determineLeadType($viciLead),
                'campaign_id' => $viciLead['campaign_id'],
                'status' => $this->mapViciStatus($viciLead['status']),
                'meta' => json_encode([
                    'vici_lead_id' => $viciLead['lead_id'],
                    'vici_list_id' => $viciLead['list_id'],
                    'vici_status' => $viciLead['status'],
                    'vici_called_count' => $viciLead['called_count'],
                    'last_call_date' => $viciLead['last_local_call_time'],
                    'days_since_call' => $lastCallDays,
                    'origin' => 'vici_import',
                    'imported_at' => now()->toIso8601String(),
                    'vendor_lead_code' => $viciLead['vendor_lead_code']
                ])
            ];

            if ($existingLead) {
                // Lead exists in Brain
                if ($lastCallDays !== null && $lastCallDays < 30) {
                    // Recently called - just update metadata
                    $leadData['status'] = 'RECENTLY_CALLED';
                }

                if (!$dryRun) {
                    $existingLead->update($leadData);
                    
                    // Ensure external_lead_id is set
                    if (!$existingLead->external_lead_id) {
                        $existingLead->external_lead_id = Lead::generateExternalLeadId();
                        $existingLead->save();
                    }
                }
                
                $this->stats['updated']++;
                
                $this->info(PHP_EOL . "Updated: {$viciLead['phone_number']} (Brain ID: {$existingLead->id})");
                
            } else {
                // New lead for Brain
                // Generate external_lead_id or use vendor_lead_code if valid
                $externalId = $viciLead['vendor_lead_code'];
                if (!$externalId || strlen($externalId) < 10) {
                    $externalId = Lead::generateExternalLeadId();
                }
                $leadData['external_lead_id'] = $externalId;

                if (!$dryRun) {
                    $lead = Lead::create($leadData);
                    
                    // Update Vici with the external_lead_id if needed
                    if ($viciLead['vendor_lead_code'] != $externalId) {
                        $this->updateViciVendorCode($viciLead['lead_id'], $externalId);
                    }
                }
                
                $this->stats['imported']++;
                
                $this->info(PHP_EOL . "Imported: {$viciLead['phone_number']} (Vici ID: {$viciLead['lead_id']})");
            }

        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->error(PHP_EOL . "Error processing {$viciLead['phone_number']}: " . $e->getMessage());
        }
    }

    private function mapViciStatus($viciStatus)
    {
        $statusMap = [
            'NEW' => 'RECEIVED',
            'DROP' => 'ATTEMPTED',
            'NA' => 'NO_ANSWER',
            'SALE' => 'SOLD',
            'DNC' => 'DO_NOT_CALL',
            'CALLBK' => 'CALLBACK',
            'A' => 'ANSWERED',
            'AA' => 'ANSWERED',
            'B' => 'BUSY',
            'DC' => 'DISCONNECTED',
            'DEC' => 'DECLINED'
        ];

        return $statusMap[$viciStatus] ?? 'UNKNOWN';
    }

    private function determineLeadType($viciLead)
    {
        // Try to determine from comments or owner field
        if (stripos($viciLead['comments'] ?? '', 'insurance') !== false) {
            return 'insurance';
        }
        if (stripos($viciLead['owner'] ?? '', 'auto') !== false) {
            return 'auto';
        }
        return 'general';
    }

    private function connectToVici()
    {
        $host = env('VICI_DB_HOST', '148.72.213.125');
        $port = env('VICI_DB_PORT', '3306');
        $database = env('VICI_DB_NAME', 'asterisk');
        $username = env('VICI_DB_USER', 'cron');
        $password = env('VICI_DB_PASS', '1234');

        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]);
    }

    private function triggerWhitelist()
    {
        $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
        $ch = curl_init($whitelistUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $this->info('✓ Whitelist triggered successfully');
        } else {
            $this->warn('⚠ Whitelist trigger returned: ' . $httpCode);
        }
    }

    private function updateViciVendorCode($leadId, $vendorCode)
    {
        try {
            $viciDb = $this->connectToVici();
            $stmt = $viciDb->prepare("
                UPDATE vicidial_list 
                SET vendor_lead_code = :vendor_code 
                WHERE lead_id = :lead_id
            ");
            $stmt->execute([
                'vendor_code' => $vendorCode,
                'lead_id' => $leadId
            ]);
        } catch (Exception $e) {
            $this->warn("Could not update Vici vendor code: " . $e->getMessage());
        }
    }

    private function displayResults()
    {
        $this->newLine(2);
        $this->info('=== IMPORT COMPLETE ===');
        
        $headers = ['Metric', 'Count'];
        $rows = [
            ['Total Processed', $this->stats['total']],
            ['Imported (New)', $this->stats['imported']],
            ['Updated (Existing)', $this->stats['updated']],
            ['Skipped', $this->stats['skipped']],
            ['Errors', $this->stats['errors']]
        ];
        
        $this->table($headers, $rows);
        
        if ($this->stats['imported'] > 0) {
            $this->info("✓ Successfully imported {$this->stats['imported']} new leads from Vici");
        }
        if ($this->stats['updated'] > 0) {
            $this->info("✓ Updated {$this->stats['updated']} existing leads with Vici data");
        }
    }
}

