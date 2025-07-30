<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class FailedNotification extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'type', 'error_message', 'payload', 'attempts', 'last_attempt_at', 'resolved_at'];

    protected $casts = ['last_attempt_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
