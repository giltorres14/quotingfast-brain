<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrphanCallLog extends Model
{
    use HasFactory;

    protected $table = 'orphan_call_logs';

    protected $fillable = [
        'uniqueid',
        'lead_id',
        'list_id',
        'campaign_id',
        'call_date',
        'start_epoch',
        'end_epoch',
        'length_in_sec',
        'status',
        'phone_code',
        'phone_number',
        'user',
        'comments',
        'processed',
        'term_reason',
        'vendor_lead_code',
        'source_id',
        'matched',
        'matched_lead_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'call_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_epoch' => 'integer',
        'end_epoch' => 'integer',
        'length_in_sec' => 'integer',
        'matched' => 'boolean',
        'matched_lead_id' => 'integer',
        'list_id' => 'integer'
    ];

    /**
     * Get the matched lead if any
     */
    public function matchedLead()
    {
        return $this->belongsTo(Lead::class, 'matched_lead_id');
    }

    /**
     * Scope for unmatched orphan calls
     */
    public function scopeUnmatched($query)
    {
        return $query->whereNull('matched_at');
    }

    /**
     * Scope for matched orphan calls
     */
    public function scopeMatched($query)
    {
        return $query->whereNotNull('matched_at');
    }
