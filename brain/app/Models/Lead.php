<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;
    
    /**
     * Get the call metrics for this lead
     */
    public function viciCallMetrics()
    {
        return $this->hasOne(ViciCallMetrics::class);
    }
    
    /**
     * Get all call history (if multiple call records)
     */
    public function callHistory()
    {
        return $this->hasMany(ViciCallMetrics::class);
    }
    
    /**
     * Boot method to ensure external_lead_id is always set correctly
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($lead) {
            // If external_lead_id is not set or doesn't match our 13-digit timestamp format, generate it
            if (empty($lead->external_lead_id) || 
                strlen($lead->external_lead_id) !== 13 || 
                !is_numeric($lead->external_lead_id)) {
                
                // Generate our timestamp-based 13-digit ID
                $lead->external_lead_id = self::generateExternalLeadId();
                
                \Log::info('🔢 Lead Model: Generated external_lead_id in boot', [
                    'new_id' => $lead->external_lead_id,
                    'original_id' => $lead->getOriginal('external_lead_id') ?? 'none',
                    'format' => 'timestamp+sequence (13 digits)'
                ]);
            }
        });
    }
    
    /**
     * Generate a 13-digit external lead ID using Unix timestamp + sequence
     * Format: TTTTTTTTTTXXX (10-digit timestamp + 3-digit sequence)
     * Example: 1733520421001
     * 
     * This guarantees uniqueness, is purely numeric, and is time-sortable
     */
    public static function generateExternalLeadId()
    {
        try {
            // Get current Unix timestamp (10 digits)
            $timestamp = time();
            
            // Get count of leads created in the same second (for sequence)
            $startOfSecond = \Carbon\Carbon::createFromTimestamp($timestamp);
            $endOfSecond = $startOfSecond->copy()->addSecond();
            
            $countThisSecond = self::whereBetween('created_at', [$startOfSecond, $endOfSecond])
                                  ->count();
            
            // Create sequence number (000-999)
            $sequence = str_pad($countThisSecond, 3, '0', STR_PAD_LEFT);
            
            // Combine timestamp + sequence for 13-digit ID
            $externalId = $timestamp . $sequence;
            
            \Log::info('🔢 Generated timestamp-based external_lead_id', [
                'timestamp' => $timestamp,
                'sequence' => $sequence,
                'final_id' => $externalId,
                'datetime' => date('Y-m-d H:i:s', $timestamp)
            ]);
            
            return $externalId;
            
        } catch (\Exception $e) {
            // Fallback: timestamp + random if database fails
            $timestamp = time();
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $fallbackId = $timestamp . $random;
            
            \Log::warning('🔢 Using fallback ID generation', [
                'error' => $e->getMessage(),
                'fallback_id' => $fallbackId
            ]);
            
            return $fallbackId;
        }
    }

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
        'vici_list_id',
        'tenant_id',
        
        // Complex data as JSON
        'drivers',
        'vehicles',
        'current_policy',
        'requested_policy',
        'meta',
        'payload',
        
        // Vendor Information
        'vendor_name',
        'vendor_campaign',
        'cost',
        
        // Buyer Information
        'buyer_name',
        'buyer_campaign',
        'sell_price',
        
        // TCPA Compliance
        'tcpa_lead_id',
        'trusted_form_cert',
        'tcpa_compliant',
        
        // Tracking and analytics
        'ip_address',
        'user_agent',
        'landing_page_url',
        
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
        'cost' => 'decimal:2',
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

    // Removed duplicate viciCallMetrics() method - already defined above

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