<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DialerLog extends Model
{
    protected $table = 'dialer_logs';
    protected $fillable = ['lead_id','status','created_at'];
} 