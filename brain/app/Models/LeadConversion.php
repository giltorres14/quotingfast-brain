<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'vici_call_metrics_id',
        'ringba_call_id',
        'ringba_campaign_id',
        'ringba_publisher_id',
        'converted',
        'conversion_time',
        'buyer_name',
        'buyer_id',
        'conversion_value',
        'conversion_type',
        'time_to_first_call',
        'time_to_transfer',
        'time_to_conversion',
        'total_call_attempts',
        'total_talk_time',
        'final_disposition',
        'agent_id',
        'campaign_id',
        'ringba_payload',
        'notes'
    ];

    protected $casts = [
        'converted' => 'boolean',
        'conversion_time' => 'datetime',
        'conversion_value' => 'decimal:2',
        'ringba_payload' => 'array'
    ];

    /**
     * Get the lead associated with this conversion
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the Vici call metrics associated with this conversion
     */
    public function viciCallMetrics(): BelongsTo
    {
        return $this->belongsTo(ViciCallMetrics::class);
    }

    /**
     * Calculate conversion metrics
     */
    public function calculateTimingMetrics(ViciCallMetrics $callMetrics): void
    {
        $leadCreated = $this->lead?->created_at;
        $firstCall = $callMetrics->first_call_time;
        $transferTime = $callMetrics->transfer_time;
        $conversionTime = $this->conversion_time;

        if ($leadCreated && $firstCall) {
            $this->time_to_first_call = $leadCreated->diffInSeconds($firstCall);
        }

        if ($firstCall && $transferTime) {
            $this->time_to_transfer = $firstCall->diffInSeconds($transferTime);
        }

        if ($transferTime && $conversionTime) {
            $this->time_to_conversion = $transferTime->diffInSeconds($conversionTime);
        }

        $this->total_call_attempts = $callMetrics->call_attempts;
        $this->total_talk_time = $callMetrics->talk_time;
        $this->final_disposition = $callMetrics->disposition;
        $this->agent_id = $callMetrics->agent_id;
        $this->campaign_id = $callMetrics->campaign_id;

        $this->save();
    }

    /**
     * Get conversion funnel metrics
     */
    public function getFunnelMetrics(): array
    {
        return [
            'lead_to_first_call' => $this->time_to_first_call,
            'first_call_to_transfer' => $this->time_to_transfer,
            'transfer_to_conversion' => $this->time_to_conversion,
            'total_lead_to_conversion' => ($this->time_to_first_call ?? 0) + 
                                        ($this->time_to_transfer ?? 0) + 
                                        ($this->time_to_conversion ?? 0),
            'call_attempts' => $this->total_call_attempts,
            'talk_time' => $this->total_talk_time,
            'conversion_value' => $this->conversion_value,
            'buyer' => $this->buyer_name
        ];
    }

    /**
     * Scope for converted leads only
     */
    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    /**
     * Scope for specific buyer
     */
    public function scopeByBuyer($query, string $buyer)
    {
        return $query->where('buyer_name', $buyer);
    }

    /**
     * Scope for date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('conversion_time', [$startDate, $endDate]);
    }
}