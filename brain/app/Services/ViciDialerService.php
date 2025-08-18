<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PDO;
use PDOException;

class ViciDialerService
{
    private string $baseUrl;
    private string $apiKey;
    private bool $isTestMode;
    
    // FIXED: Hard-coded list ID to prevent wrong list assignment
    private int $targetListId = 101; // ALWAYS use list 101 for LeadsQuotingFast leads
    
    // Database connection details
    private string $mysqlHost;
    private string $mysqlDb;
    private string $mysqlUser;
    private string $mysqlPass;
    private int $mysqlPort;
    private ?PDO $viciConnection = null;

    public function __construct()
    {
        $this->isTestMode = config('services.vici.test_mode', config('app.env') !== 'production');
        
        // Use Non-Agent API for lead insertion
        $viciServer = config('services.vici.web_server', 'philli.callix.ai');
        $this->baseUrl = "https://{$viciServer}/vicidial/non_agent_api.php";
        $this->apiKey = config('services.vici.api_key', 'mock');
        
        // Database connection details (for fallback/testing)
        $this->mysqlHost = config('services.vici.mysql_host', '37.27.138.222');
        $this->mysqlDb = config('services.vici.mysql_db', 'asterisk');
        $this->mysqlUser = config('services.vici.mysql_user', 'Superman');
        $this->mysqlPass = config('services.vici.mysql_pass', '8ZDWGAAQRD');
        $this->mysqlPort = config('services.vici.mysql_port', 3306);
        
        // Log configuration on startup to verify list ID
        Log::info('ViciDialerService initialized', [
            'target_list_id' => $this->targetListId,
            'server' => $this->mysqlHost,
            'database' => $this->mysqlDb,
            'test_mode' => $this->isTestMode
        ]);
    }

    /**
     * Get direct database connection to Vici MySQL
     */
    private function getViciConnection(): PDO
    {
        if ($this->viciConnection === null) {
            try {
                $dsn = "mysql:host={$this->mysqlHost};dbname={$this->mysqlDb};port={$this->mysqlPort};charset=utf8mb4";
                
                $this->viciConnection = new PDO($dsn, $this->mysqlUser, $this->mysqlPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10,
                ]);
                
                Log::info('Vici Database: Connection established', [
                    'host' => $this->mysqlHost,
                    'database' => $this->mysqlDb,
                    'user' => $this->mysqlUser
                ]);
                
            } catch (PDOException $e) {
                Log::error('Vici Database: Connection failed', [
                    'error' => $e->getMessage(),
                    'host' => $this->mysqlHost,
                    'database' => $this->mysqlDb
                ]);
                throw $e;
            }
        }
        
        return $this->viciConnection;
    }

    /**
     * Push lead directly to Vici database (List 101)
     */
    public function pushLead(Lead $lead, string $campaignId = null): array
    {
        try {
            // MIGRATION CHECK: Don't push to Vici if disabled
            if (!config('services.vici.push_enabled', false)) {
                Log::info('⏸️ Vici push DISABLED during migration - Lead stored in Brain only', [
                    'lead_id' => $lead->id,
                    'phone' => $lead->phone,
                    'reason' => 'VICI_PUSH_ENABLED is false to prevent duplicates'
                ]);
                
                // Still create call metrics record for tracking
                $callMetrics = ViciCallMetrics::firstOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'phone_number' => $lead->phone,
                        'status' => 'pending_push',
                        'campaign_id' => $campaignId ?? 'AUTODIAL',
                        'total_calls' => 0,
                        'connected' => false
                    ]
                );
                
                // Mark lead as pending Vici push
                $lead->status = 'pending_vici_push';
                $lead->save();
                
                return [
                    'success' => true,
                    'message' => 'Lead stored in Brain only (Vici push disabled during migration)',
                    'vici_lead_id' => null,
                    'call_metrics_id' => $callMetrics->id,
                    'push_status' => 'disabled_for_migration'
                ];
            }
            
            Log::info('Vici Lead Push: Starting direct database insert', [
                'brain_lead_id' => $lead->external_lead_id,
                'internal_id' => $lead->id,
                'lead_name' => $lead->name,
                'campaign_id' => $campaignId,
                'target_list' => '101'
            ]);

            // Check if we're in test/local mode
            if ($this->isTestMode || config('app.env') === 'local') {
                // Mock mode for local testing
                Log::info('Vici Mock Mode: Simulating lead push', [
                    'lead_id' => $lead->id,
                    'campaign' => $campaignId,
                    'list' => $this->targetListId
                ]);
                
                $mockViciId = 'MOCK_VICI_' . $lead->id . '_' . time();
                
                // Create mock call metrics
                $callMetrics = ViciCallMetrics::create([
                    'lead_id' => $lead->id,
                    'vici_lead_id' => $mockViciId,
                    'campaign_id' => $campaignId ?? 'AUTODIAL',
                    'list_id' => $this->targetListId,
                    'phone_number' => $lead->phone,
                    'call_status' => 'NEW',
                    'vici_payload' => [
                        'mock_mode' => true,
                        'timestamp' => now()->toIso8601String()
                    ]
                ]);
                
                return [
                    'success' => true,
                    'method' => 'mock',
                    'vici_lead_id' => $mockViciId,
                    'call_metrics_id' => $callMetrics->id,
                    'list_id' => $this->targetListId,
                    'campaign_id' => $campaignId ?? 'AUTODIAL',
                    'pushed_at' => now()->toISOString(),
                    'message' => 'Lead successfully queued (MOCK MODE)'
                ];
            }
            
            // Production mode - try database first if on server, otherwise API
            $isProductionServer = gethostname() === 'brain-server' || file_exists('/var/www/brain');
            
            if ($isProductionServer && $this->mysqlUser && $this->mysqlPass) {
                try {
                    return $this->pushLeadToDatabase($lead, $campaignId);
                } catch (\Exception $e) {
                    Log::warning('Database method failed, trying API', ['error' => $e->getMessage()]);
                    return $this->pushLeadViaAPI($lead, $campaignId);
                }
            } else {
                return $this->pushLeadViaAPI($lead, $campaignId);
            }

        } catch (\Exception $e) {
            Log::error('Vici Lead Push: Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'failed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Update existing Vici lead with Brain Lead ID
     */
    public function updateViciLeadWithBrainId(Lead $lead): array
    {
        try {
            // Ensure we have the 13-digit external_lead_id
            $brainLeadId = $lead->external_lead_id;
            if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
                Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                    'lead_id' => $lead->id,
                    'current_external_id' => $brainLeadId
                ]);
                $brainLeadId = Lead::generateExternalLeadId();
                $lead->external_lead_id = $brainLeadId;
                $lead->save();
            }
            
            $connection = $this->getViciConnection();
            
            // First, try to find the lead in Vici by phone number
            $findSql = "SELECT lead_id, vendor_lead_code, source_id, comments 
                       FROM vicidial_list 
                       WHERE phone_number = :phone 
                       ORDER BY lead_id DESC 
                       LIMIT 1";
            
            $stmt = $connection->prepare($findSql);
            $stmt->execute(['phone' => $lead->phone]);
            $viciLead = $stmt->fetch();
            
            if (!$viciLead) {
                Log::warning('Lead not found in Vici for update', [
                    'brain_lead_id' => $brainLeadId,
                    'phone' => $lead->phone
                ]);
                return [
                    'success' => false,
                    'message' => 'Lead not found in Vici',
                    'brain_lead_id' => $brainLeadId
                ];
            }
            
            // Update the Vici lead with Brain Lead ID
            $updateSql = "UPDATE vicidial_list 
                         SET vendor_lead_code = :vendor_code,
                             source_id = :source_id,
                             comments = :comments,
                             modify_date = :modify_date
                         WHERE lead_id = :vici_lead_id";
            
            $updateParams = [
                'vendor_code' => $brainLeadId, // Store 13-digit Brain Lead ID
                'source_id' => 'BRAIN_' . $brainLeadId,
                'comments' => "Brain Lead ID: {$brainLeadId} | Updated: " . now()->format('Y-m-d H:i:s') . " | Original: " . ($viciLead['comments'] ?? ''),
                'modify_date' => now()->format('Y-m-d H:i:s'),
                'vici_lead_id' => $viciLead['lead_id']
            ];
            
            $stmt = $connection->prepare($updateSql);
            $result = $stmt->execute($updateParams);
            
            if ($result) {
                // Update our ViciCallMetrics if it exists
                $callMetrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
                if ($callMetrics) {
                    $callMetrics->vici_lead_id = $viciLead['lead_id'];
                    $callMetrics->save();
                }
                
                Log::info('✅ Vici Lead Updated with Brain ID', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLead['lead_id'],
                    'phone' => $lead->phone,
                    'old_vendor_code' => $viciLead['vendor_lead_code']
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Vici lead updated with Brain Lead ID',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLead['lead_id'],
                    'old_vendor_code' => $viciLead['vendor_lead_code']
                ];
            } else {
                Log::error('Failed to update Vici lead', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLead['lead_id']
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to update Vici lead',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLead['lead_id']
                ];
            }
            
        } catch (PDOException $e) {
            Log::error('Vici Database Update Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'brain_lead_id' => $lead->external_lead_id
            ];
        }
    }
    
    /**
     * Push lead directly to Vici database (fastest method)
     */
    private function pushLeadToDatabase(Lead $lead, string $campaignId = null): array
    {
        try {
            $connection = $this->getViciConnection();
            
            // Format data for Vici vicidial_list table
            $viciData = $this->formatLeadForViciDatabase($lead, $campaignId);
            
            // Insert into vicidial_list table
            $sql = "INSERT INTO vicidial_list (
                lead_id, phone_number, first_name, last_name, 
                address1, city, state, postal_code, 
                list_id, status, entry_date, modify_date,
                vendor_lead_code, source_id, comments
            ) VALUES (
                :lead_id, :phone_number, :first_name, :last_name,
                :address1, :city, :state, :postal_code,
                :list_id, :status, :entry_date, :modify_date,
                :vendor_lead_code, :source_id, :comments
            )";
            
            $stmt = $connection->prepare($sql);
            $result = $stmt->execute($viciData);
            
            $viciLeadId = $connection->lastInsertId();
            
            // Create call metrics record in our system
            $callMetrics = ViciCallMetrics::create([
                'lead_id' => $lead->id,
                'vici_lead_id' => $viciLeadId,
                'campaign_id' => $campaignId ?? config('services.vici.default_campaign'),
                'list_id' => '101',
                'phone_number' => $lead->phone,
                'call_status' => 'NEW',
                'vici_payload' => $viciData
            ]);

            Log::info('Vici Lead Push: Database insert successful', [
                'lead_id' => $lead->id,
                'vici_lead_id' => $viciLeadId,
                'call_metrics_id' => $callMetrics->id,
                'list_id' => '101'
            ]);

            return [
                'success' => true,
                'method' => 'database',
                'vici_lead_id' => $viciLeadId,
                'call_metrics_id' => $callMetrics->id,
                'list_id' => '101',
                'campaign_id' => $campaignId ?? config('services.vici.default_campaign'),
                'pushed_at' => now()->toISOString(),
                'message' => 'Lead successfully inserted into Vici List 101'
            ];

        } catch (PDOException $e) {
            Log::error('Vici Database Insert Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode()
            ]);

            // Fallback to API method if database fails
            Log::info('Vici: Falling back to API method');
            return $this->pushLeadViaAPI($lead, $campaignId);
        }
    }

    /**
     * Format lead data for Vici database
     */
    private function formatLeadForViciDatabase(Lead $lead, string $campaignId = null): array
    {
        // Ensure we have the 13-digit external_lead_id
        $brainLeadId = $lead->external_lead_id;
        if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
            Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                'lead_id' => $lead->id,
                'current_external_id' => $brainLeadId
            ]);
            $brainLeadId = Lead::generateExternalLeadId();
            $lead->external_lead_id = $brainLeadId;
            $lead->save();
        }
        
        // CRITICAL: Use the actual opt-in date from the lead, not current time!
        // This is essential for TCPA compliance (90-day limit)
        $optInDate = $lead->opt_in_date ?? $lead->created_at;
        
        return [
            'lead_id' => null, // Auto-increment
            'phone_number' => $lead->phone,
            'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
            'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
            'address1' => $lead->address ?? '',
            'city' => $lead->city ?? '',
            'state' => $lead->state ?? '',
            'postal_code' => $lead->zip_code ?? '',
            'list_id' => $this->targetListId, // FIXED: Use hard-coded list 101
            'status' => 'NEW',
            'entry_date' => $optInDate->format('Y-m-d H:i:s'), // USE OPT-IN DATE FOR TCPA!
            'modify_date' => now()->format('Y-m-d H:i:s'),
            'vendor_lead_code' => $brainLeadId, // Use 13-digit Brain Lead ID
            'source_id' => 'BRAIN_' . $brainLeadId, // Include Brain ID in source for clarity
            'comments' => "Brain Lead ID: {$brainLeadId} | Internal ID: {$lead->id} | Source: {$lead->source} | Opt-in: {$optInDate->format('Y-m-d H:i:s')}"
        ];
    }

    /**
     * Push lead via ViciDial Non-Agent API
     */
    private function pushLeadViaAPI(Lead $lead, string $campaignId = null): array
    {
        try {
            // Ensure we have the 13-digit external_lead_id
            $brainLeadId = $lead->external_lead_id;
            if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
                Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                    'lead_id' => $lead->id,
                    'current_external_id' => $brainLeadId
                ]);
                $brainLeadId = Lead::generateExternalLeadId();
                $lead->external_lead_id = $brainLeadId;
                $lead->save();
            }
            
            // Format for Non-Agent API add_lead function
            // FIXED: Use correct apiuser credentials that were working on August 8th
            $params = [
                'source' => 'brain',
                'user' => 'apiuser',
                'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'), // Use environment variable or fallback
                'function' => 'add_lead',
                'phone_number' => $lead->phone,
                'phone_code' => '1', // US country code
                'list_id' => $this->targetListId,
                'campaign_id' => $campaignId ?? 'AUTODIAL',
                'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
                'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
                'address1' => $lead->address ?? '',
                'city' => $lead->city ?? '',
                'state' => $lead->state ?? '',
                'postal_code' => $lead->zip_code ?? '',
                'vendor_lead_code' => $brainLeadId, // Use 13-digit Brain Lead ID
                'source_id' => 'BRAIN_' . $brainLeadId, // Include Brain ID in source for clarity
                'comments' => "Brain Lead ID: {$brainLeadId} | Internal ID: {$lead->id} | Source: {$lead->source}"
            ];

            // Add email if available
            if ($lead->email) {
                $params['email'] = $lead->email;
            }

            Log::info('Vici Non-Agent API: Sending lead', [
                'lead_id' => $lead->id,
                'params' => array_merge($params, ['pass' => 'HIDDEN'])
            ]);

            // Make API call using GET parameters (Non-Agent API uses GET)
            $url = $this->baseUrl . '?' . http_build_query($params);
            $response = Http::timeout(30)->get($url);
            
            $responseBody = $response->body();
            
            // Parse response (Non-Agent API returns plain text)
            if (strpos($responseBody, 'SUCCESS') !== false) {
                // Extract lead ID from response if available
                preg_match('/lead_id: (\d+)/', $responseBody, $matches);
                $viciLeadId = $matches[1] ?? null;
                
                // Create call metrics record
                $callMetrics = ViciCallMetrics::create([
                    'lead_id' => $lead->id,
                    'vici_lead_id' => $viciLeadId,
                    'campaign_id' => $campaignId,
                    'list_id' => $this->targetListId,
                    'phone_number' => $lead->phone,
                    'call_status' => 'NEW',
                    'vici_payload' => $params
                ]);
                
                Log::info('Vici Non-Agent API: Lead added successfully', [
                    'lead_id' => $lead->id,
                    'vici_lead_id' => $viciLeadId,
                    'response' => $responseBody
                ]);
                
                return [
                    'success' => true,
                    'method' => 'non_agent_api',
                    'vici_lead_id' => $viciLeadId,
                    'call_metrics_id' => $callMetrics->id,
                    'list_id' => $this->targetListId,
                    'campaign_id' => $campaignId,
                    'pushed_at' => now()->toISOString(),
                    'response' => $responseBody
                ];
            } else {
                Log::error('Vici Non-Agent API: Failed to add lead', [
                    'lead_id' => $lead->id,
                    'response' => $responseBody
                ]);
                
                return [
                    'success' => false,
                    'method' => 'non_agent_api',
                    'error' => $responseBody,
                    'pushed_at' => now()->toISOString()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Vici Non-Agent API: Exception', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'method' => 'non_agent_api',
                'error' => $e->getMessage(),
                'pushed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Format lead data for API (original method)
     */
    private function formatLeadForViciAPI(Lead $lead, string $campaignId = null): array
    {
        return [
            'lead_id' => $lead->external_lead_id ?? $lead->id,
            'phone_number' => $lead->phone,
            'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
            'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
            'address1' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'postal_code' => $lead->zip_code,
            'campaign_id' => $campaignId ?? config('services.vici.default_campaign'),
            'list_id' => $this->targetListId, // FIXED: Use hard-coded list 101
            'source' => 'brain-system',
            'brain_lead_id' => $lead->id,
            'created_at' => now()->toISOString()
        ];
    }

    /**
     * Get call metrics for a lead from Vici database
     */
    public function getCallMetrics(int $leadId): array
    {
        try {
            $connection = $this->getViciConnection();
            
            // Get call log data from Vici
            $sql = "SELECT vl.lead_id, vl.phone_number, vl.first_name, vl.last_name, 
                           vl.status, vl.called_count, vl.last_local_call_time,
                           COUNT(vcl.lead_id) as total_calls,
                           MAX(vcl.call_date) as last_call_date,
                           SUM(vcl.length_in_sec) as total_talk_time
                    FROM vicidial_list vl
                    LEFT JOIN vicidial_log vcl ON vl.lead_id = vcl.lead_id
                    WHERE vl.vendor_lead_code = :vendor_lead_code
                    GROUP BY vl.lead_id";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute(['vendor_lead_code' => 'BRAIN_' . $leadId]);
            
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'success' => true,
                    'data' => $result,
                    'retrieved_at' => now()->toISOString()
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Lead not found in Vici system',
                'lead_id' => $leadId
            ];
            
        } catch (PDOException $e) {
            Log::error('Vici Call Metrics Retrieval Failed', [
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'lead_id' => $leadId
            ];
        }
    }

    /**
     * Find or create call metrics record
     */
    private function findOrCreateCallMetrics(array $data): ViciCallMetrics
    {
        $leadId = $data['lead_id'] ?? null;
        $viciLeadId = $data['vici_lead_id'] ?? $data['lead_id'] ?? null;
        $phoneNumber = $data['phone_number'] ?? $data['phone'] ?? null;
        
        // Try to find existing record
        $metrics = ViciCallMetrics::where('lead_id', $leadId)
            ->orWhere('vici_lead_id', $viciLeadId)
            ->orWhere('phone_number', $phoneNumber)
            ->first();
        
        if (!$metrics) {
            $metrics = ViciCallMetrics::create([
                'lead_id' => $leadId,
                'vici_lead_id' => $viciLeadId,
                'campaign_id' => $data['campaign_id'] ?? null,
                'list_id' => $data['list_id'] ?? null,
                'agent_id' => $data['agent_id'] ?? null,
                'phone_number' => $phoneNumber,
                'call_status' => $data['call_status'] ?? 'UNKNOWN',
                'disposition' => $data['disposition'] ?? null,
                'vici_payload' => $data
            ]);
        }
        
        return $metrics;
    }

    /**
     * Update call metrics based on webhook data
     */
    private function updateCallMetrics(ViciCallMetrics $metrics, array $data): void
    {
        $updates = [];
        
        // Update basic fields
        if (isset($data['call_status'])) {
            $updates['call_status'] = $data['call_status'];
        }
        
        if (isset($data['disposition'])) {
            $updates['disposition'] = $data['disposition'];
        }
        
        // Handle call timing
        if (isset($data['call_time']) || isset($data['start_time'])) {
            $callTime = $data['call_time'] ?? $data['start_time'];
            if (!$metrics->first_call_time) {
                $updates['first_call_time'] = $callTime;
            }
            $updates['last_call_time'] = $callTime;
        }
        
        if (isset($data['connected_time']) || $data['call_status'] === 'INCALL') {
            $updates['connected_time'] = $data['connected_time'] ?? now();
        }
        
        if (isset($data['hangup_time']) || $data['call_status'] === 'PAUSED') {
            $updates['hangup_time'] = $data['hangup_time'] ?? now();
        }
        
        if (isset($data['call_duration'])) {
            $updates['call_duration'] = $data['call_duration'];
        }
        
        if (isset($data['talk_time'])) {
            $updates['talk_time'] = $data['talk_time'];
        }
        
        // Handle transfers
        if (isset($data['transfer']) && $data['transfer'] === 'Y') {
            $updates['transfer_requested'] = true;
            $updates['transfer_time'] = now();
            $updates['transfer_destination'] = $data['transfer_destination'] ?? 'ringba';
        }
        
        // Add call attempt to history
        if (isset($data['call_status'])) {
            $metrics->addCallAttempt([
                'status' => $data['call_status'],
                'disposition' => $data['disposition'] ?? null,
                'duration' => $data['call_duration'] ?? null,
                'agent_id' => $data['agent_id'] ?? null
            ]);
        }
        
        // Update payload
        $updates['vici_payload'] = array_merge($metrics->vici_payload ?? [], $data);
        
        if (!empty($updates)) {
            $metrics->update($updates);
        }
    }

    /**
     * Check if webhook data triggers any actions
     */
    private function checkTriggerActions(ViciCallMetrics $metrics, array $data): array
    {
        $actions = [];
        
        // Check for transfer triggers
        if ($metrics->transfer_requested && !$metrics->transfer_status) {
            $actions[] = [
                'type' => 'transfer_ready',
                'destination' => $metrics->transfer_destination,
                'lead_id' => $metrics->lead_id,
                'metrics_id' => $metrics->id
            ];
            
            // Mark transfer as processed
            $metrics->update(['transfer_status' => 'processed']);
        }
        
        // Check for conversion tracking setup
        if ($data['disposition'] === 'SALE' || $data['disposition'] === 'TRANSFER') {
            $actions[] = [
                'type' => 'setup_conversion_tracking',
                'lead_id' => $metrics->lead_id,
                'disposition' => $data['disposition']
            ];
        }
        
        return $actions;
    }

    /**
     * Process webhook data from Vici
     */
    public function processWebhookData(array $data): array
    {
        try {
            Log::info('Vici Webhook Processing', [
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);

            // Find or create call metrics record
            $callMetrics = $this->findOrCreateCallMetrics($data);

            // Update metrics based on webhook data
            $this->updateCallMetrics($callMetrics, $data);

            // Check if this triggers any actions (like Allstate transfer)
            $actions = $this->checkTriggerActions($callMetrics, $data);

            return [
                'success' => true,
                'call_metrics_id' => $callMetrics->id,
                'actions_triggered' => $actions,
                'processed_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Vici Webhook Processing Failed', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'failed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Make API call to Vici (fallback method)
     */
    private function makeApiCall(string $endpoint, array $payload): array
    {
        // MOCK MODE: Return simulated response
        if (empty($this->apiKey) || $this->apiKey === 'mock') {
            Log::info('Vici Mock Mode: Simulating API call', [
                'endpoint' => $endpoint,
                'payload_size' => count($payload),
                'mock_mode' => true
            ]);

            return [
                'success' => true,
                'data' => [
                    'vici_lead_id' => 'VICI_' . uniqid(),
                    'status' => 'queued',
                    'message' => 'Lead successfully queued for dialing (MOCK MODE)',
                    'campaign_id' => $payload['campaign_id'] ?? 'default',
                    'estimated_call_time' => now()->addMinutes(5)->toISOString(),
                    'queue_position' => rand(1, 50)
                ],
                'status_code' => 200,
                'mock_mode' => true
            ];
        }

        // PRODUCTION MODE: Make actual API call
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->timeout(30)
        ->post($this->baseUrl . $endpoint, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
            'status_code' => $response->status(),
        ];
    }





    // Helper methods
    private function extractFirstName(string $fullName): string
    {
        return explode(' ', trim($fullName))[0] ?? 'Unknown';
    }

    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? end($parts) : '';
    }
    
    /**
     * Move a lead to a different Vici list through API
     * YES - The Brain CAN move leads between lists!
     */
    public function moveLeadToList(Lead $lead, int $newListId, string $reason = ''): array
    {
        try {
            // Ensure we have the external lead ID
            if (!$lead->external_lead_id) {
                return [
                    'success' => false,
                    'message' => 'Lead has no external_lead_id for Vici'
                ];
            }
            
            Log::info('🔄 Moving lead to new Vici list via API', [
                'lead_id' => $lead->id,
                'external_lead_id' => $lead->external_lead_id,
                'current_list' => $lead->vici_list_id ?? 'unknown',
                'new_list' => $newListId,
                'reason' => $reason
            ]);
            
            // Use Non-Agent API to update lead's list
            $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
            
            $params = [
                'source' => 'brain',
                'user' => 'apiuser',
                'pass' => 'UZPATJ59GJAVKG8ES6',
                'function' => 'update_lead',
                'vendor_lead_code' => "BRAIN_{$lead->id}",
                'list_id_field' => $newListId,
                'reset_called_count' => 'N', // Keep call history
                'custom_fields' => 'Y'
            ];
            
            // Make API call
            $response = Http::timeout(15)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                $body = $response->body();
                
                if (strpos($body, 'SUCCESS') !== false || strpos($body, 'lead has been updated') !== false) {
                    // Update local database
                    $oldListId = $lead->vici_list_id;
                    $lead->vici_list_id = $newListId;
                    
                    // Track the move in meta
                    $meta = json_decode($lead->meta ?? '{}', true);
                    $meta['list_moves'] = $meta['list_moves'] ?? [];
                    $meta['list_moves'][] = [
                        'from' => $oldListId ?? 'unknown',
                        'to' => $newListId,
                        'reason' => $reason,
                        'timestamp' => now()->toIso8601String()
                    ];
                    $lead->meta = json_encode($meta);
                    $lead->save();
                    
                    Log::info('✅ Successfully moved lead to list ' . $newListId);
                    
                    return [
                        'success' => true,
                        'message' => "Lead moved to list {$newListId}",
                        'old_list_id' => $oldListId,
                        'new_list_id' => $newListId
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Failed to move lead in Vici'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception moving lead', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a lead from Vici through API
     * Note: This removes the lead from Vici but can keep it in Brain for records
     */
    public function deleteLeadFromVici(Lead $lead, bool $keepInBrain = true): array
    {
        try {
            if (!$lead->external_lead_id) {
                return [
                    'success' => false,
                    'message' => 'Lead has no external_lead_id for Vici'
                ];
            }
            
            Log::info('🗑️ Deleting lead from Vici', [
                'lead_id' => $lead->id,
                'external_lead_id' => $lead->external_lead_id,
                'keep_in_brain' => $keepInBrain
            ]);
            
            $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
            
            $params = [
                'source' => 'brain',
                'user' => 'apiuser',
                'pass' => 'UZPATJ59GJAVKG8ES6',
                'function' => 'update_lead',
                'vendor_lead_code' => "BRAIN_{$lead->id}",
                'status' => 'DELETE', // Mark as deleted
                'delete_lead' => 'Y'   // Actually delete from Vici
            ];
            
            $response = Http::timeout(15)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                if ($keepInBrain) {
                    // Mark as deleted in Brain but keep record
                    $lead->status = 'deleted_from_vici';
                    $lead->vici_list_id = null;
                    $meta = json_decode($lead->meta ?? '{}', true);
                    $meta['deleted_from_vici'] = now()->toIso8601String();
                    $lead->meta = json_encode($meta);
                    $lead->save();
                } else {
                    // Delete from Brain too
                    $lead->delete();
                }
                
                Log::info('✅ Lead deleted from Vici');
                
                return [
                    'success' => true,
                    'message' => 'Lead deleted from Vici',
                    'kept_in_brain' => $keepInBrain
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete lead from Vici'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception deleting lead', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Automatically assign lead to appropriate list based on status
     */
    public function autoAssignLeadToList(Lead $lead): array
    {
        $newListId = 101; // Default
        
        // Determine list based on status
        if (in_array($lead->status, ['dnc', 'bad_number'])) {
            $newListId = 199; // DNC list
        } elseif ($lead->status == 'qualified') {
            $newListId = 104; // Qualified list
        } elseif ($lead->status == 'callback') {
            $newListId = 103; // Callback list
        } elseif (in_array($lead->status, ['no_answer', 'busy'])) {
            $newListId = 102; // Retry list
        }
        
        return $this->moveLeadToList($lead, $newListId, 'Auto-assigned based on status');
    }
}
            ];
            
            // Make API call
            $response = Http::timeout(15)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                $body = $response->body();
                
                if (strpos($body, 'SUCCESS') !== false || strpos($body, 'lead has been updated') !== false) {
                    // Update local database
                    $oldListId = $lead->vici_list_id;
                    $lead->vici_list_id = $newListId;
                    
                    // Track the move in meta
                    $meta = json_decode($lead->meta ?? '{}', true);
                    $meta['list_moves'] = $meta['list_moves'] ?? [];
                    $meta['list_moves'][] = [
                        'from' => $oldListId ?? 'unknown',
                        'to' => $newListId,
                        'reason' => $reason,
                        'timestamp' => now()->toIso8601String()
                    ];
                    $lead->meta = json_encode($meta);
                    $lead->save();
                    
                    Log::info('✅ Successfully moved lead to list ' . $newListId);
                    
                    return [
                        'success' => true,
                        'message' => "Lead moved to list {$newListId}",
                        'old_list_id' => $oldListId,
                        'new_list_id' => $newListId
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Failed to move lead in Vici'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception moving lead', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a lead from Vici through API
     * Note: This removes the lead from Vici but can keep it in Brain for records
     */
    public function deleteLeadFromVici(Lead $lead, bool $keepInBrain = true): array
    {
        try {
            if (!$lead->external_lead_id) {
                return [
                    'success' => false,
                    'message' => 'Lead has no external_lead_id for Vici'
                ];
            }
            
            Log::info('🗑️ Deleting lead from Vici', [
                'lead_id' => $lead->id,
                'external_lead_id' => $lead->external_lead_id,
                'keep_in_brain' => $keepInBrain
            ]);
            
            $apiUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
            
            $params = [
                'source' => 'brain',
                'user' => 'apiuser',
                'pass' => 'UZPATJ59GJAVKG8ES6',
                'function' => 'update_lead',
                'vendor_lead_code' => "BRAIN_{$lead->id}",
                'status' => 'DELETE', // Mark as deleted
                'delete_lead' => 'Y'   // Actually delete from Vici
            ];
            
            $response = Http::timeout(15)->asForm()->post($apiUrl, $params);
            
            if ($response->successful()) {
                if ($keepInBrain) {
                    // Mark as deleted in Brain but keep record
                    $lead->status = 'deleted_from_vici';
                    $lead->vici_list_id = null;
                    $meta = json_decode($lead->meta ?? '{}', true);
                    $meta['deleted_from_vici'] = now()->toIso8601String();
                    $lead->meta = json_encode($meta);
                    $lead->save();
                } else {
                    // Delete from Brain too
                    $lead->delete();
                }
                
                Log::info('✅ Lead deleted from Vici');
                
                return [
                    'success' => true,
                    'message' => 'Lead deleted from Vici',
                    'kept_in_brain' => $keepInBrain
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to delete lead from Vici'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception deleting lead', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Automatically assign lead to appropriate list based on status
     */
    public function autoAssignLeadToList(Lead $lead): array
    {
        $newListId = 101; // Default
        
        // Determine list based on status
        if (in_array($lead->status, ['dnc', 'bad_number'])) {
            $newListId = 199; // DNC list
        } elseif ($lead->status == 'qualified') {
            $newListId = 104; // Qualified list
        } elseif ($lead->status == 'callback') {
            $newListId = 103; // Callback list
        } elseif (in_array($lead->status, ['no_answer', 'busy'])) {
            $newListId = 102; // Retry list
        }
        
        return $this->moveLeadToList($lead, $newListId, 'Auto-assigned based on status');
    }
}