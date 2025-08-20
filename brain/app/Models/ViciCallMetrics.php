<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViciCallMetrics extends Model
{
    use HasFactory;

    protected $table = 'vici_call_metrics';

    protected $fillable = [
        'lead_id',
        'vendor_lead_code',
        'uniqueid',
        'call_date',
        'phone_number',
        'status',
        'user',
        'campaign_id',
        'list_id',
        'length_in_sec',
        'call_status',
        'matched_lead_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'call_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'length_in_sec' => 'integer',
        'matched_lead_id' => 'integer',
        'list_id' => 'integer'
    ];

    /**
     * Get the lead associated with this call metric
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'matched_lead_id');
    }

    /**
     * Scope for today's calls
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for connected calls
     */
    public function scopeConnected($query)
    {
        return $query->where('call_status', 'XFER');
    }

    /**
     * Scope for orphan calls (not matched to a lead)
     */
    public function scopeOrphan($query)
    {
        return $query->whereNull('matched_lead_id');
    }
}
        $history = $this->call_history ?? [];
        $history[] = array_merge($callData, [
            'attempt_number' => count($history) + 1,
            'timestamp' => now()->toISOString()
        ]);
        
        $this->update([
            'call_history' => $history,
            'call_attempts' => count($history),
            'last_call_time' => now()
        ]);
        
        if (!$this->first_call_time) {
            $this->update(['first_call_time' => now()]);
        }
    }

    /**
     * Mark as connected
     */
    public function markConnected(int $talkTime = null): void
    {
        $this->update([
            'connected_time' => now(),
            'talk_time' => $talkTime,
            'connection_rate' => $this->calculateConnectionRate()
        ]);
    }

    /**
     * Mark transfer as requested
     */
    public function requestTransfer(string $destination): void
    {
        $this->update([
            'transfer_requested' => true,
            'transfer_time' => now(),
            'transfer_destination' => $destination
        ]);
    }

    /**
     * Get metrics summary
     */
    public function getMetricsSummary(): array
    {
        return [
            'total_attempts' => $this->call_attempts,
            'connected' => $this->connected_time ? true : false,
            'connection_rate' => $this->connection_rate ?? 0,
            'talk_time' => $this->talk_time ?? 0,
            'transfer_requested' => $this->transfer_requested,
            'final_disposition' => $this->disposition,
            'agent_id' => $this->agent_id,
            'campaign_id' => $this->campaign_id
        ];
    }
}