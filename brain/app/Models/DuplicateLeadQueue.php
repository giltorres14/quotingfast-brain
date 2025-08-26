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
}


