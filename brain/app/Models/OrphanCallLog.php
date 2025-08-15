<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrphanCallLog extends Model
{
    use HasFactory;

    protected $table = 'orphan_call_logs';

    protected $fillable = [
        'phone_number',
        'vendor_lead_code',
        'vici_lead_id',
        'campaign_id',
        'agent_id',
        'status',
        'disposition',
        'call_date',
        'talk_time',
        'call_data',
        'matched_lead_id',
        'matched_at'
    ];

    protected $casts = [
        'call_data' => 'array',
        'call_date' => 'datetime',
        'matched_at' => 'datetime'
    ];

    /**
     * Relationship to lead (once matched)
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'matched_lead_id');
    }

    /**
     * Scope for unmatched orphan calls
     */
    public function scopeUnmatched($query)
    {
        return $query->whereNull('matched_lead_id');
    }

    /**
     * Scope for matched orphan calls
     */
    public function scopeMatched($query)
    {
        return $query->whereNotNull('matched_lead_id');
    }

    /**
     * Try to match this orphan call to a lead
     */
    public function tryMatch(): bool
    {
        // Try phone number first
        if ($this->phone_number) {
            $phone = preg_replace('/\D/', '', $this->phone_number);
            if (strlen($phone) == 10) {
                $lead = Lead::where('phone', $phone)->first();
                if ($lead) {
                    $this->matched_lead_id = $lead->id;
                    $this->matched_at = now();
                    $this->save();
                    
                    // Create or update ViciCallMetrics
                    $this->createCallMetrics($lead);
                    
                    return true;
                }
            }
        }
        
        // Try vendor_lead_code
        if ($this->vendor_lead_code) {
            // Check if it's an external_lead_id
            $lead = Lead::where('external_lead_id', $this->vendor_lead_code)->first();
            if ($lead) {
                $this->matched_lead_id = $lead->id;
                $this->matched_at = now();
                $this->save();
                
                // Create or update ViciCallMetrics
                $this->createCallMetrics($lead);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Create call metrics from orphan call
     */
    private function createCallMetrics(Lead $lead): void
    {
        $metrics = ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
        
        $metrics->phone_number = $this->phone_number;
        $metrics->campaign_id = $this->campaign_id;
        $metrics->agent_id = $this->agent_id;
        $metrics->status = $this->status;
        $metrics->last_call_time = $this->call_date;
        
        // Increment call count
        $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
        
        // Update talk time
        if ($this->talk_time > 0) {
            $metrics->talk_time = ($metrics->talk_time ?? 0) + $this->talk_time;
            $metrics->connected = true;
        }
        
        // Add to call history
        $history = json_decode($metrics->call_attempts ?? '[]', true);
        $history[] = [
            'date' => $this->call_date,
            'status' => $this->status,
            'duration' => $this->talk_time,
            'agent' => $this->agent_id,
            'orphan_matched' => true,
            'matched_date' => now()->toIso8601String()
        ];
        $metrics->call_attempts = json_encode($history);
        
        $metrics->save();
    }
}

