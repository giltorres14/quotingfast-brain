<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuplicateLeadQueue extends Model
{
    protected $table = 'duplicate_lead_queue';

    protected $fillable = [
        'phone_normalized', 'vendor', 'source', 'payload_json', 'ip', 'user_agent',
        'original_lead_id', 'original_external_lead_id', 'original_received_at',
        'days_since_original', 'match_reason', 'status', 'decision_by', 'decision_at',
        'applied_at', 'applied_action'
    ];
    
    protected $casts = [
        'original_received_at' => 'datetime',
        'decision_at' => 'datetime',
        'applied_at' => 'datetime',
        'payload_json' => 'array'
    ];
    
    public function originalLead()
    {
        return $this->belongsTo(Lead::class, 'original_lead_id');
    }
}


