<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'campaigns',
        'contact_info',
        'total_leads',
        'total_revenue',
        'active',
        'api_credentials',
        'notes'
    ];

    protected $casts = [
        'campaigns' => 'array',
        'contact_info' => 'array',
        'api_credentials' => 'encrypted:array',
        'total_revenue' => 'decimal:2',
        'active' => 'boolean'
    ];

    /**
     * Get all leads sold to this buyer
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'buyer_name', 'name');
    }
    
    /**
     * Many-to-many relationship with campaigns
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_buyer')
                    ->withPivot('buyer_campaign_id', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Add a campaign if it doesn't exist
     */
    public function addCampaign($campaignName)
    {
        $campaigns = $this->campaigns ?? [];
        if (!in_array($campaignName, $campaigns)) {
            $campaigns[] = $campaignName;
            $this->campaigns = $campaigns;
            $this->save();
        }
    }

    /**
     * Update statistics
     */
    public function updateStats()
    {
        $this->total_leads = $this->leads()->count();
        $this->total_revenue = $this->leads()->sum('sell_price');
        $this->save();
    }
}