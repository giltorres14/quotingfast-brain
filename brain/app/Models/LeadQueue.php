<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadQueue extends Model
{
    protected $table = 'lead_queue';
    
    protected $fillable = [
        'payload',
        'source',
        'status',
        'attempts',
        'error_message',
        'processed_at',
        'lead_name',
        'phone'
    ];
    
    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Create a queue entry from failed webhook data
     */
    public static function createFromFailedWebhook($data, $source = 'webhook', $error = null)
    {
        // Extract contact info for display
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        $name = trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown';
        $phone = $contact['phone'] ?? null;
        
        return self::create([
            'payload' => $data,
            'source' => $source,
            'status' => 'failed',
            'attempts' => 1,
            'error_message' => $error,
            'lead_name' => $name,
            'phone' => $phone
        ]);
    }
    
    /**
     * Process a queued lead
     */
    public function process()
    {
        try {
            $this->update(['status' => 'processing']);
            
            // Determine lead type
            $type = $this->payload['type'] ?? detectLeadType($this->payload);
            
            // Process based on type
            if ($type === 'home') {
                $result = $this->processHomeLead();
            } else {
                $result = $this->processAutoLead();
            }
            
            if ($result['success']) {
                $this->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'error_message' => null
                ]);
                return true;
            } else {
                throw new \Exception($result['error'] ?? 'Processing failed');
            }
            
        } catch (\Exception $e) {
            $this->update([
                'status' => 'failed',
                'attempts' => $this->attempts + 1,
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Process as auto lead
     */
    protected function processAutoLead()
    {
        $data = $this->payload;
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        $phone = preg_replace('/[^0-9]/', '', $contact['phone'] ?? '');
        
        if (strlen($phone) !== 10) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $phone,
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => $data['source'] ?? 'lead_queue',
            'type' => 'auto',
            'received_at' => now(),
            'joined_at' => now(),
            'tenant_id' => 1, // QuotingFast tenant
            'external_lead_id' => \App\Models\Lead::generateExternalLeadId(),
            'meta' => json_encode($data['meta'] ?? []),
            'drivers' => json_encode($data['data']['drivers'] ?? []),
            'vehicles' => json_encode($data['data']['vehicles'] ?? []),
            'payload' => json_encode($data),
        ];
        
        $lead = \App\Models\Lead::create($leadData);
        
        return ['success' => true, 'lead_id' => $lead->id];
    }
    
    /**
     * Process as home lead
     */
    protected function processHomeLead()
    {
        $data = $this->payload;
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        $phone = preg_replace('/[^0-9]/', '', $contact['phone'] ?? '');
        
        if (strlen($phone) !== 10) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $phone,
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => $data['source'] ?? 'lead_queue',
            'type' => 'home',
            'received_at' => now(),
            'joined_at' => now(),
            'tenant_id' => 1, // QuotingFast tenant
            'external_lead_id' => \App\Models\Lead::generateExternalLeadId(),
            'meta' => json_encode($data['meta'] ?? []),
            'drivers' => json_encode($data['data']['drivers'] ?? []),
            'vehicles' => json_encode([]),
            'payload' => json_encode($data),
        ];
        
        $lead = \App\Models\Lead::create($leadData);
        
        return ['success' => true, 'lead_id' => $lead->id];
    }

    // Helper function
    protected function detectLeadType($data) {
        if (isset($data['type'])) {
            return strtolower($data['type']);
        }
        
        if (isset($data['data']['vehicles']) && !empty($data['data']['vehicles'])) {
            return 'auto';
        }
        
        if (isset($data['data']['properties']) || isset($data['data']['property'])) {
            return 'home';
        }
        
        return 'auto'; // Default
    }
    
    /**
     * Process as auto lead
     */
    
    /**
     * Process as home lead
     */
}

// Helper function
function detectLeadType($data) {
    if (isset($data['type'])) {
        return strtolower($data['type']);
    }
    
    if (isset($data['data']['vehicles']) && !empty($data['data']['vehicles'])) {
        return 'auto';
    }
    
    if (isset($data['data']['properties']) || isset($data['data']['property'])) {
        return 'home';
    }
    
    return 'auto'; // Default
    }
