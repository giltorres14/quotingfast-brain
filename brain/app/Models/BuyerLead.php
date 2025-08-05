<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerLead extends Model
{
    protected $fillable = [
        'buyer_id',
        'lead_id',
        'external_lead_id',
        'vertical',
        'price',
        'status',
        'return_reason',
        'return_notes',
        'delivered_at',
        'returned_at',
        'lead_data',
        'refund_amount',
        'refund_processed_at',
        'quality_scored',
        'quality_rating',
        'quality_feedback'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'lead_data' => 'array',
        'refund_amount' => 'decimal:2',
        'refund_processed_at' => 'datetime',
        'quality_scored' => 'boolean',
    ];

    // Relationships
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Scopes
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopeByVertical($query, $vertical)
    {
        return $query->where('vertical', $vertical);
    }

    // Methods
    public function canReturn(): bool
    {
        if ($this->status !== 'delivered') {
            return false;
        }

        // Can return within 24 hours of delivery
        return $this->delivered_at->diffInHours(now()) <= 24;
    }

    public function returnLead(string $reason, string $notes = null): bool
    {
        if (!$this->canReturn()) {
            return false;
        }

        $this->update([
            'status' => 'returned',
            'return_reason' => $reason,
            'return_notes' => $notes,
            'returned_at' => now(),
            'refund_amount' => $this->price
        ]);

        // Add balance back to buyer
        $this->buyer->addBalance($this->price);

        return true;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'delivered' => '<span class="badge badge-success">Delivered</span>',
            'returned' => '<span class="badge badge-warning">Returned</span>',
            'disputed' => '<span class="badge badge-danger">Disputed</span>',
            default => '<span class="badge badge-secondary">Unknown</span>'
        };
    }
}
