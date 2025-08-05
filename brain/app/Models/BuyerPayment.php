<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerPayment extends Model
{
    protected $fillable = [
        'buyer_id',
        'transaction_id',
        'type',
        'amount',
        'status',
        'payment_method',
        'payment_processor',
        'processor_response',
        'description',
        'processed_at',
        'failure_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processor_response' => 'array',
        'processed_at' => 'datetime'
    ];

    // Relationships
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }
}
