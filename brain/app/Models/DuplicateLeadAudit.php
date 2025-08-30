<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuplicateLeadAudit extends Model
{
    protected $table = 'duplicate_lead_audit';

    protected $fillable = [
        'queue_id', 'action', 'actor', 'details_json'
    ];
}



