<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrichmentLog extends Model
{
    protected $table = 'enrichment_logs';
    protected $fillable = ['lead_id','converted','created_at'];
} 