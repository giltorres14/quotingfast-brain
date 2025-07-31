<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    // Source constants for Filament
    const SOURCES = [
        'LQF' => 'LeadsQuotingFast',
        'leadsquotingfast' => 'LeadsQuotingFast',
        'facebook' => 'Facebook',
        'google' => 'Google',
        'organic' => 'Organic',
        'referral' => 'Referral',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'zip_code',
        'birth_date',
        'marital_status',
        'gender',
        'occupation',
        'education',
        'sr22_required',
        'bankruptcy',
        'license_status',
        'license_state',
        'license_suspended',
        'age_licensed',
        'residence_type',
        'months_at_residence',
        'months_at_employer',
        'no_tickets',
        'no_major_violations',
        'no_accidents',
        'no_claims',
        'vertical',
        'source',
        'publisher_id',
        'user_agent',
        'ip_address',
        'trusted_form_url',
        'leadid_code',
        'contactable_status',
        'exclusive_flag',
        'sold_count',
        'type',
        'received_at',
        'joined_at',
        'drivers',
        'vehicles',
        'current_policy',
        'payload',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'vin',
        'insurance_company',
        'coverage_type',
        'assigned_to',
        'client_id',
        'status',
        'estimated_value',
        'contacted_at',
        'qualified_at',
        'notes',
    ];

    protected $casts = [
        'type' => 'array',
        'drivers' => 'array',
        'vehicles' => 'array',
        'current_policy' => 'array',
        'payload' => 'json',
        'received_at' => 'datetime',
        'joined_at' => 'datetime',
        'birth_date' => 'date',
        'sr22_required' => 'boolean',
        'bankruptcy' => 'boolean',
        'license_suspended' => 'boolean',
        'no_tickets' => 'boolean',
        'no_major_violations' => 'boolean',
        'no_accidents' => 'boolean',
        'no_claims' => 'boolean',
        'exclusive_flag' => 'boolean',
        'estimated_value' => 'decimal:2',
        'contacted_at' => 'datetime',
        'qualified_at' => 'datetime',
    ];

    protected $dates = [
        'received_at',
        'joined_at',
        'birth_date',
    ];

    // Types method for Filament
    public static function types(): array
    {
        return [
            'auto' => 'Auto Insurance',
            'home' => 'Home Insurance',
            'life' => 'Life Insurance',
            'health' => 'Health Insurance',
            'commercial' => 'Commercial Insurance',
            'internet' => 'Internet Lead',
        ];
    }

    // Scopes for filtering
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return null;
        
        $phone = preg_replace('/\D/', '', $this->phone);
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        }
        return $this->phone;
    }

    // User relationships
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Additional scopes for user management
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
} 