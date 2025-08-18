<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'status',
        'first_seen_at',
        'last_lead_received_at',
        'total_leads',
        'is_auto_created',
        'tenant_id',
        'display_name',
        'buyer_id',
        'buyer_name'
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_lead_received_at' => 'datetime',
        'is_auto_created' => 'boolean'
    ];

    // Relationship with leads
    public function leads()
    {
        return $this->hasMany(Lead::class, 'campaign_id', 'campaign_id');
    }

    // Many-to-many relationship with buyers
    public function buyers()
    {
        return $this->belongsToMany(Buyer::class, 'campaign_buyer')
                    ->withPivot('buyer_campaign_id', 'is_primary')
                    ->withTimestamps();
    }
    
    // Get primary buyer (if designated)
    public function primaryBuyer()
    {
        return $this->buyers()->wherePivot('is_primary', true)->first();
    }
    
    // Add a buyer to this campaign
    public function addBuyer($buyer, $buyerCampaignId = null, $isPrimary = false)
    {
        return $this->buyers()->attach($buyer->id ?? $buyer, [
            'buyer_campaign_id' => $buyerCampaignId,
            'is_primary' => $isPrimary
        ]);
    }

    // Auto-create campaign when new ID detected
    public static function autoCreateFromId($campaignId)
    {
        return self::firstOrCreate(
            ['campaign_id' => $campaignId],
            [
                'name' => "Campaign #{$campaignId}",
                'description' => 'Auto-created from incoming lead',
                'status' => 'auto_detected',
                'first_seen_at' => now(),
                'last_lead_received_at' => now(),
                'total_leads' => 1,
                'is_auto_created' => true,
                'tenant_id' => 1, // QuotingFast tenant
                'display_name' => "Campaign #{$campaignId}"
            ]
        );
    }

    // Update campaign with lead activity
    public function recordLeadActivity()
    {
        $this->update([
            'last_lead_received_at' => now(),
            'total_leads' => $this->leads()->count()
        ]);
    }

    // Update campaign name and convert auto-created to managed
    public function updateWithName($name, $description = null)
    {
        $oldCampaignId = $this->campaign_id;
        
        $this->update([
            'name' => $name,
            'display_name' => $name,
            'description' => $description ?? $this->description,
            'status' => 'active',
            'is_auto_created' => false
        ]);

        // Update all leads that were showing Campaign ID to show Campaign Name
        Lead::where('campaign_id', $oldCampaignId)->update(['updated_at' => now()]);
        
        return $this;
    }

    // Get display name (name or fallback to ID)
    public function getDisplayNameAttribute()
    {
        return $this->is_auto_created ? "Campaign #{$this->campaign_id}" : $this->name;
    }

    // Check if campaign needs attention (auto-created)
    public function scopeNeedsAttention($query)
    {
        return $query->where('is_auto_created', true)->where('status', 'auto_detected');
    }

    // Get campaigns with recent activity
    public function scopeRecentActivity($query, $days = 7)
    {
        return $query->where('last_lead_received_at', '>=', now()->subDays($days));
    }
}

            'description' => $description ?? $this->description,
            'status' => 'active',
            'is_auto_created' => false
        ]);

        // Update all leads that were showing Campaign ID to show Campaign Name
        Lead::where('campaign_id', $oldCampaignId)->update(['updated_at' => now()]);
        
        return $this;
    }

    // Get display name (name or fallback to ID)
    public function getDisplayNameAttribute()
    {
        return $this->is_auto_created ? "Campaign #{$this->campaign_id}" : $this->name;
    }

    // Check if campaign needs attention (auto-created)
    public function scopeNeedsAttention($query)
    {
        return $query->where('is_auto_created', true)->where('status', 'auto_detected');
    }

    // Get campaigns with recent activity
    public function scopeRecentActivity($query, $days = 7)
    {
        return $query->where('last_lead_received_at', '>=', now()->subDays($days));
    }
}
