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