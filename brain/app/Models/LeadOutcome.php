<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadOutcome extends Model
{
    protected $fillable = [
        'lead_id',
        'buyer_id',
        'external_lead_id',
        'crm_lead_id',
        'status',
        'outcome',
        'sale_amount',
        'commission_amount',
        'quality_rating',
        'contact_attempts',
        'first_contact_at',
        'last_contact_at',
        'closed_at',
        'notes',
        'feedback',
        'source_system',
        'reported_via',
        'metadata'
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'quality_rating' => 'integer',
        'contact_attempts' => 'integer',
        'first_contact_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Lead statuses
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL_SENT = 'proposal_sent';
    const STATUS_NEGOTIATING = 'negotiating';
    const STATUS_CLOSED_WON = 'closed_won';
    const STATUS_CLOSED_LOST = 'closed_lost';
    const STATUS_NOT_INTERESTED = 'not_interested';
    const STATUS_BAD_LEAD = 'bad_lead';
    const STATUS_DUPLICATE = 'duplicate';

    // Lead outcomes
    const OUTCOME_PENDING = 'pending';
    const OUTCOME_SOLD = 'sold';
    const OUTCOME_NOT_SOLD = 'not_sold';
    const OUTCOME_BAD_LEAD = 'bad_lead';
    const OUTCOME_DUPLICATE = 'duplicate';

    // Quality ratings (1-5 scale)
    const QUALITY_EXCELLENT = 5;
    const QUALITY_GOOD = 4;
    const QUALITY_AVERAGE = 3;
    const QUALITY_POOR = 2;
    const QUALITY_TERRIBLE = 1;

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    // Accessors
    public function getFormattedSaleAmountAttribute(): string
    {
        return '$' . number_format($this->sale_amount ?? 0, 2);
    }

    public function getFormattedCommissionAmountAttribute(): string
    {
        return '$' . number_format($this->commission_amount ?? 0, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_NEW => '🆕',
            self::STATUS_CONTACTED => '📞',
            self::STATUS_QUALIFIED => '✅',
            self::STATUS_PROPOSAL_SENT => '📄',
            self::STATUS_NEGOTIATING => '🤝',
            self::STATUS_CLOSED_WON => '🎉',
            self::STATUS_CLOSED_LOST => '❌',
            self::STATUS_NOT_INTERESTED => '🚫',
            self::STATUS_BAD_LEAD => '⚠️',
            self::STATUS_DUPLICATE => '🔄'
        ];

        return $badges[$this->status] ?? '❓';
    }

    public function getOutcomeBadgeAttribute(): string
    {
        $badges = [
            self::OUTCOME_PENDING => '⏳',
            self::OUTCOME_SOLD => '💰',
            self::OUTCOME_NOT_SOLD => '❌',
            self::OUTCOME_BAD_LEAD => '⚠️',
            self::OUTCOME_DUPLICATE => '🔄'
        ];

        return $badges[$this->outcome] ?? '❓';
    }

    public function getQualityStarsAttribute(): string
    {
        if (!$this->quality_rating) {
            return '⭐ Not Rated';
        }

        $stars = str_repeat('⭐', $this->quality_rating);
        $empty = str_repeat('☆', 5 - $this->quality_rating);
        
        return $stars . $empty . " ({$this->quality_rating}/5)";
    }

    public function getTimeTakenAttribute(): ?string
    {
        if (!$this->first_contact_at || !$this->closed_at) {
            return null;
        }

        $diff = $this->first_contact_at->diffInHours($this->closed_at);
        
        if ($diff < 24) {
            return $diff . ' hours';
        } elseif ($diff < 168) { // 7 days
            return round($diff / 24, 1) . ' days';
        } else {
            return round($diff / 168, 1) . ' weeks';
        }
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByOutcome($query, $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeSold($query)
    {
        return $query->where('outcome', self::OUTCOME_SOLD);
    }

    public function scopeNotSold($query)
    {
        return $query->where('outcome', self::OUTCOME_NOT_SOLD);
    }

    public function scopeBadLeads($query)
    {
        return $query->where('outcome', self::OUTCOME_BAD_LEAD);
    }

    public function scopeHighQuality($query)
    {
        return $query->where('quality_rating', '>=', 4);
    }

    public function scopeLowQuality($query)
    {
        return $query->where('quality_rating', '<=', 2);
    }

    public function scopeRecentlyUpdated($query, $days = 7)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    // Static methods
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New Lead',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_PROPOSAL_SENT => 'Proposal Sent',
            self::STATUS_NEGOTIATING => 'Negotiating',
            self::STATUS_CLOSED_WON => 'Closed Won',
            self::STATUS_CLOSED_LOST => 'Closed Lost',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_BAD_LEAD => 'Bad Lead',
            self::STATUS_DUPLICATE => 'Duplicate'
        ];
    }

    public static function getOutcomeOptions(): array
    {
        return [
            self::OUTCOME_PENDING => 'Pending',
            self::OUTCOME_SOLD => 'Sold',
            self::OUTCOME_NOT_SOLD => 'Not Sold',
            self::OUTCOME_BAD_LEAD => 'Bad Lead',
            self::OUTCOME_DUPLICATE => 'Duplicate'
        ];
    }

    public static function getQualityOptions(): array
    {
        return [
            5 => 'Excellent (5 stars)',
            4 => 'Good (4 stars)',
            3 => 'Average (3 stars)',
            2 => 'Poor (2 stars)',
            1 => 'Terrible (1 star)'
        ];
    }

    // Methods
    public function updateStatus($status, $notes = null, $metadata = [])
    {
        $this->update([
            'status' => $status,
            'notes' => $notes ?: $this->notes,
            'metadata' => array_merge($this->metadata ?? [], $metadata),
            'last_contact_at' => now()
        ]);

        // Set first contact if this is the first time
        if (!$this->first_contact_at && $status !== self::STATUS_NEW) {
            $this->update(['first_contact_at' => now()]);
        }

        // Set closed date for final statuses
        if (in_array($status, [self::STATUS_CLOSED_WON, self::STATUS_CLOSED_LOST, self::STATUS_NOT_INTERESTED, self::STATUS_BAD_LEAD])) {
            $this->update(['closed_at' => now()]);
        }

        return $this;
    }

    public function markAsSold($saleAmount = null, $commissionAmount = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_CLOSED_WON,
            'outcome' => self::OUTCOME_SOLD,
            'sale_amount' => $saleAmount,
            'commission_amount' => $commissionAmount,
            'notes' => $notes ?: $this->notes,
            'closed_at' => now()
        ]);

        return $this;
    }

    public function markAsNotSold($reason = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_CLOSED_LOST,
            'outcome' => self::OUTCOME_NOT_SOLD,
            'notes' => $notes ?: $reason,
            'closed_at' => now()
        ]);

        return $this;
    }

    public function markAsBadLead($reason = null, $qualityRating = 1)
    {
        $this->update([
            'status' => self::STATUS_BAD_LEAD,
            'outcome' => self::OUTCOME_BAD_LEAD,
            'quality_rating' => $qualityRating,
            'notes' => $reason,
            'closed_at' => now()
        ]);

        return $this;
    }

    public function rateQuality($rating, $feedback = null)
    {
        $this->update([
            'quality_rating' => max(1, min(5, $rating)),
            'feedback' => $feedback ?: $this->feedback
        ]);

        return $this;
    }

    public function incrementContactAttempts()
    {
        $this->increment('contact_attempts');
        $this->update(['last_contact_at' => now()]);

        if (!$this->first_contact_at) {
            $this->update(['first_contact_at' => now()]);
        }

        return $this;
    }
}