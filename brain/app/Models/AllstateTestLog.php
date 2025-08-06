<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllstateTestLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'lead_id',
        'external_lead_id',
        'lead_name',
        'lead_type',
        'lead_phone',
        'lead_email',
        'qualification_data',
        'data_sources',
        'allstate_payload',
        'allstate_endpoint',
        'response_status',
        'allstate_response',
        'success',
        'error_message',
        'validation_errors',
        'sent_at',
        'response_time_ms',
        'test_environment',
        'test_session',
        'notes'
    ];
    
    protected $casts = [
        'qualification_data' => 'array',
        'data_sources' => 'array',
        'allstate_payload' => 'array',
        'allstate_response' => 'array',
        'validation_errors' => 'array',
        'success' => 'boolean',
        'sent_at' => 'datetime'
    ];
    
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
    
    public function getStatusBadgeAttribute()
    {
        if ($this->success) {
            return '<span class="badge badge-success">Success</span>';
        } else {
            return '<span class="badge badge-error">Failed</span>';
        }
    }
    
    public function getResponseTimeAttribute()
    {
        if ($this->response_time_ms) {
            return $this->response_time_ms . 'ms';
        }
        return 'N/A';
    }
}
