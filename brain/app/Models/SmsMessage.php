<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'from', 'body', 'received_at'];

    protected $casts = ['received_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
