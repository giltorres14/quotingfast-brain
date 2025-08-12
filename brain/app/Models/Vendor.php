<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'campaigns',
        'contact_info',
        'total_leads',
        'total_cost',
        'active',
        'notes'
    ];

    protected $casts = [
        'campaigns' => 'array',
        'contact_info' => 'array',
        'total_cost' => 'decimal:2',
        'active' => 'boolean'
    ];

    /**
     * Get all leads from this vendor
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'vendor_name', 'name');
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
        $this->total_cost = $this->leads()->sum('cost');
        $this->save();
    }
}

