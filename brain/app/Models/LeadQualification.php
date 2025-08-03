<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'currently_insured',
        'current_provider',
        'insurance_duration',
        'active_license',
        'dui_sr22',
        'dui_timeframe',
        'state',
        'zip_code',
        'num_vehicles',
        'home_status',
        'allstate_quote',
        'ready_to_speak',
        'enrichment_type',
        'enriched_at',
        'enrichment_data'
    ];

    protected $casts = [
        'enrichment_data' => 'array',
        'enriched_at' => 'datetime'
    ];

    /**
     * Get the lead that owns the qualification
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }
}