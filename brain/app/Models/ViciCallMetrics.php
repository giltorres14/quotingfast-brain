<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViciCallMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'vici_lead_id',
        'campaign_id',
        'list_id',
        'agent_id',
        'phone_number',
        'call_status',
        'disposition',
        'call_attempts',
        'first_call_time',
        'last_call_time',
        'connected_time',
        'hangup_time',
        'call_duration',
        'talk_time',
        'transfer_requested',
        'transfer_time',
        'transfer_destination',
        'transfer_status',
        'connection_rate',
        'transfer_rate',
        'call_history',
        'vici_payload',
        'notes'
    ];

    protected $casts = [
        'first_call_time' => 'datetime',
        'last_call_time' => 'datetime',
        'connected_time' => 'datetime',
        'hangup_time' => 'datetime',
        'transfer_time' => 'datetime',
        'transfer_requested' => 'boolean',
        'call_history' => 'array',
        'vici_payload' => 'array',
        'connection_rate' => 'decimal:2',
        'transfer_rate' => 'decimal:2'
    ];

    /**
     * Get the lead associated with this call metric
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Calculate connection rate based on call attempts
     */
    public function calculateConnectionRate(): float
    {
        if ($this->call_attempts === 0) {
            return 0.0;
        }
        
        $connected = $this->connected_time ? 1 : 0;
        return round(($connected / $this->call_attempts) * 100, 2);
    }

    /**
     * Add a new call attempt to the history
     */
    public function addCallAttempt(array $callData): void
    {
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