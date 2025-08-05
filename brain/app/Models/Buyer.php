<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Buyer extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name', 
        'company',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'status',
        'balance',
        'auto_reload_amount',
        'auto_reload_threshold',
        'auto_reload_enabled',
        'permissions',
        'contract_signed',
        'contract_signed_at',
        'contract_ip',
        'preferences',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'auto_reload_amount' => 'decimal:2',
        'auto_reload_threshold' => 'decimal:2',
        'auto_reload_enabled' => 'boolean',
        'permissions' => 'array',
        'contract_signed' => 'boolean',
        'contract_signed_at' => 'datetime',
        'preferences' => 'array',
        'last_login_at' => 'datetime',
    ];

    // Relationships
    public function leads(): HasMany
    {
        return $this->hasMany(BuyerLead::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BuyerPayment::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(BuyerContract::class)->where('is_active', true);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(BuyerContract::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format($this->balance, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function canPurchaseLeads(): bool
    {
        return $this->status === 'active' && $this->contract_signed && $this->balance > 0;
    }

    public function needsAutoReload(): bool
    {
        return $this->auto_reload_enabled && 
               $this->auto_reload_threshold && 
               $this->balance <= $this->auto_reload_threshold;
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    public function deductBalance(float $amount): bool
    {
        if ($this->balance >= $amount) {
            $this->decrement('balance', $amount);
            return true;
        }
        
        return false;
    }

    public function addBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }
}
