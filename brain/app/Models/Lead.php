<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic contact information
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        
        // Lead metadata
        'source',
        'type',
        'status',
        'received_at',
        'joined_at',
        
        // Assignment and routing
        'assigned_user_id',
        'campaign_id',
        'external_lead_id',
        
        // Complex data as JSON
        'drivers',
        'vehicles',
        'current_policy',
        'requested_policy',
        'meta',
        'payload',
        
        // Tracking and analytics
        'sell_price',
        'ip_address',
        'user_agent',
        'landing_page_url',
        'tcpa_compliant',
        
        // Allstate integration
        'allstate_transfer_id',
        'allstate_transferred_at',
        'allstate_response',
    ];

    protected $casts = [
        'drivers' => 'array',
        'vehicles' => 'array',
        'current_policy' => 'array',
        'requested_policy' => 'array',
        'meta' => 'array',
        'payload' => 'array',
        'allstate_response' => 'array',
        'received_at' => 'datetime',
        'joined_at' => 'datetime',
        'allstate_transferred_at' => 'datetime',
        'tcpa_compliant' => 'boolean',
        'sell_price' => 'decimal:2',
    ];

    /**
     * Get the user assigned to this lead
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Scope for leads from a specific source
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for leads with a specific status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent leads (within last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->name;
    }

    /**
     * Check if lead has been transferred to Allstate
     */
    public function isTransferredToAllstate(): bool
    {
        return $this->status === 'transferred_to_allstate' && !is_null($this->allstate_transferred_at);
    }

    /**
     * Get driver count
     */
    public function getDriverCountAttribute(): int
    {
        return is_array($this->drivers) ? count($this->drivers) : 0;
    }

    /**
     * Get vehicle count
     */
    public function getVehicleCountAttribute(): int
    {
        return is_array($this->vehicles) ? count($this->vehicles) : 0;
    }

    /**
     * Get Vici call metrics relationship
     */
    public function viciCallMetrics()
    {
        return $this->hasMany(ViciCallMetrics::class, 'lead_id', 'id');
    }

    /**
     * Get lead conversions relationship
     */
    public function conversions()
    {
        return $this->hasMany(LeadConversion::class, 'lead_id', 'id');
    }

    /**
     * Get latest conversion
     */
    public function latestConversion()
    {
        return $this->hasOne(LeadConversion::class, 'lead_id', 'id')->latest();
    }

    /**
     * Get lead qualifications relationship
     */
    public function qualifications()
    {
        return $this->hasMany(LeadQualification::class, 'lead_id', 'id');
    }

    /**
     * Check if lead has external ID
     */
    public function hasExternalId(): bool
    {
        return !is_null($this->external_lead_id);
    }

    /**
     * Get display ID (external if available, otherwise database ID)
     */
    public function getDisplayIdAttribute(): string
    {
        return $this->external_lead_id ?? $this->id;
    }
}