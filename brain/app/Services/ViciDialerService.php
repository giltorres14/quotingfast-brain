<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ViciDialerService
{
    private string $baseUrl;
    private string $apiKey;
    private bool $isTestMode;

    public function __construct()
    {
        $this->isTestMode = config('services.vici.test_mode', config('app.env') !== 'production');
        $this->baseUrl = config('services.vici.api_url', 'https://vici-server.com/api');
        $this->apiKey = config('services.vici.api_key', 'mock');
    }

    /**
     * Push lead to Vici dialer (name + address only)
     */
    public function pushLead(Lead $lead, string $campaignId = null): array
    {
        try {
            Log::info('Vici Lead Push: Starting', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'campaign_id' => $campaignId
            ]);

            // Format lead data for Vici (minimal data as requested)
            $payload = $this->formatLeadForVici($lead, $campaignId);

            // Create call metrics record
            $callMetrics = ViciCallMetrics::create([
                'lead_id' => $lead->id,
                'vici_lead_id' => $lead->external_lead_id ?? $lead->id,
                'campaign_id' => $campaignId,
                'phone_number' => $lead->phone,
                'call_status' => 'QUEUED',
                'vici_payload' => $payload
            ]);

            // Push to Vici (or mock)
            $response = $this->makeApiCall('/leads', $payload);

            // Update call metrics
            $callMetrics->addCallAttempt([
                'action' => 'lead_pushed',
                'status' => $response['success'] ? 'success' : 'failed',
                'response' => $response
            ]);

            Log::info('Vici Lead Push: Response received', [
                'lead_id' => $lead->id,
                'response' => $response,
                'call_metrics_id' => $callMetrics->id
            ]);

            return [
                'success' => $response['success'] ?? false,
                'vici_response' => $response,
                'call_metrics_id' => $callMetrics->id,
                'pushed_at' => now()->toISOString()
            ];

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
     * Format lead data for Vici (name + address only)
     */
    private function formatLeadForVici(Lead $lead, string $campaignId = null): array
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
            'brain_lead_id' => $lead->id, // For tracking back to our system
            'created_at' => now()->toISOString()
        ];
    }

    /**
     * Make API call to Vici
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