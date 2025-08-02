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
        $this->baseUrl = config('services.vici.api_url', 'https://vici-server.com/api');
        $this->apiKey = config('services.vici.api_key', 'mock');
        
        // Database connection details
        $this->mysqlHost = config('services.vici.mysql_host', '37.27.138.222');
        $this->mysqlDb = config('services.vici.mysql_db', 'asterisk');
        $this->mysqlUser = config('services.vici.mysql_user', 'Superman');
        $this->mysqlPass = config('services.vici.mysql_pass', '8ZDWGAAQRD');
        $this->mysqlPort = config('services.vici.mysql_port', 3306);
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
            Log::info('Vici Lead Push: Starting direct database insert', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'campaign_id' => $campaignId,
                'target_list' => '101'
            ]);

            // Use database connection if available, otherwise fallback to API
            if ($this->mysqlUser && $this->mysqlPass) {
                return $this->pushLeadToDatabase($lead, $campaignId);
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
        return [
            'lead_id' => null, // Auto-increment
            'phone_number' => $lead->phone,
            'first_name' => $lead->first_name ?? $this->extractFirstName($lead->name),
            'last_name' => $lead->last_name ?? $this->extractLastName($lead->name),
            'address1' => $lead->address ?? '',
            'city' => $lead->city ?? '',
            'state' => $lead->state ?? '',
            'postal_code' => $lead->zip_code ?? '',
            'list_id' => '101', // Target list as specified
            'status' => 'NEW',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'modify_date' => now()->format('Y-m-d H:i:s'),
            'vendor_lead_code' => 'BRAIN_' . $lead->id,
            'source_id' => 'BRAIN',
            'comments' => "Lead from Brain System - ID: {$lead->id} - Source: {$lead->source}"
        ];
    }

    /**
     * Fallback API method (original implementation)
     */
    private function pushLeadViaAPI(Lead $lead, string $campaignId = null): array
    {
        // Format lead data for API
        $payload = $this->formatLeadForViciAPI($lead, $campaignId);

        // Create call metrics record
        $callMetrics = ViciCallMetrics::create([
            'lead_id' => $lead->id,
            'vici_lead_id' => $lead->external_lead_id ?? $lead->id,
            'campaign_id' => $campaignId,
            'phone_number' => $lead->phone,
            'call_status' => 'QUEUED',
            'vici_payload' => $payload
        ]);

        // Make API call
        $response = $this->makeApiCall('/leads', $payload);

        // Update call metrics
        $callMetrics->addCallAttempt([
            'action' => 'lead_pushed',
            'status' => $response['success'] ? 'success' : 'failed',
            'response' => $response
        ]);

        return [
            'success' => $response['success'] ?? false,
            'method' => 'api',
            'vici_response' => $response,
            'call_metrics_id' => $callMetrics->id,
            'pushed_at' => now()->toISOString()
        ];
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
            'list_id' => config('services.vici.default_list'),
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

    /**
     * Find or create call metrics record
     */
    private function findOrCreateCallMetrics(array $data): ViciCallMetrics
    {
        // Try to find by vici_lead_id or brain_lead_id
        $callMetrics = ViciCallMetrics::where('vici_lead_id', $data['lead_id'] ?? null)
            ->orWhere('lead_id', $data['brain_lead_id'] ?? null)
            ->first();

        if (!$callMetrics) {
            $callMetrics = ViciCallMetrics::create([
                'vici_lead_id' => $data['lead_id'] ?? null,
                'lead_id' => $data['brain_lead_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'agent_id' => $data['agent_id'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'call_status' => $data['status'] ?? 'UNKNOWN',
                'vici_payload' => $data
            ]);
        }

        return $callMetrics;
    }

    /**
     * Update call metrics based on webhook data
     */
    private function updateCallMetrics(ViciCallMetrics $callMetrics, array $data): void
    {
        $updates = [
            'call_status' => $data['status'] ?? $callMetrics->call_status,
            'disposition' => $data['disposition'] ?? $callMetrics->disposition,
            'agent_id' => $data['agent_id'] ?? $callMetrics->agent_id,
            'vici_payload' => array_merge($callMetrics->vici_payload ?? [], $data)
        ];

        // Handle different call statuses
        switch ($data['status'] ?? '') {
            case 'INCALL':
            case 'CONNECTED':
                $callMetrics->markConnected($data['talk_time'] ?? null);
                break;
            
            case 'HANGUP':
            case 'DISPO':
                $updates['hangup_time'] = now();
                $updates['call_duration'] = $data['call_length'] ?? null;
                $updates['talk_time'] = $data['talk_time'] ?? null;
                break;
        }

        $callMetrics->update($updates);

        // Add to call history
        $callMetrics->addCallAttempt([
            'status' => $data['status'] ?? 'unknown',
            'disposition' => $data['disposition'] ?? null,
            'agent_id' => $data['agent_id'] ?? null,
            'webhook_data' => $data
        ]);
    }

    /**
     * Check if webhook data triggers any actions
     */
    private function checkTriggerActions(ViciCallMetrics $callMetrics, array $data): array
    {
        $actions = [];

        // Example triggers (customize based on your needs)
        if (($data['disposition'] ?? '') === 'TRANSFER_REQUEST') {
            $actions[] = 'transfer_requested';
            $callMetrics->requestTransfer('ringba');
        }

        if (($data['status'] ?? '') === 'CONNECTED' && !$callMetrics->connected_time) {
            $actions[] = 'first_connection';
        }

        return $actions;
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
}