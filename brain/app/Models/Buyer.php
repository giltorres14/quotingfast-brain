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

    /**
     * Adjust buyer balance with logging
     */
    public function adjustBalance($amount, $description = null)
    {
        if ($amount > 0) {
            $this->increment('balance', $amount);
        } else {
            $this->decrement('balance', abs($amount));
        }
        
        // Log the balance change
        \Illuminate\Support\Facades\Log::info("Buyer balance adjusted", [
            'buyer_id' => $this->id,
            'amount' => $amount,
            'new_balance' => $this->fresh()->balance,
            'description' => $description
        ]);
        
        return $this->fresh()->balance;
    }
    
    /**
     * Purchase a lead (deduct from balance)
     */
    public function purchaseLead($amount, $description = 'Lead purchase')
    {
        if ($this->balance < $amount) {
            return false; // Insufficient funds
        }
        
        $this->decrement('balance', $amount);
        
        // Check if auto-reload is needed
        if ($this->needsAutoReload()) {
            $this->triggerAutoReload();
        }
        
        return true;
    }
    
    /**
     * Trigger auto-reload if enabled and conditions are met
     */
    public function triggerAutoReload()
    {
        if (!$this->auto_reload_enabled || !$this->auto_reload_amount) {
            return false;
        }
        
        try {
            // In a real implementation, this would call the payment processor
            // For now, we'll simulate successful auto-reload
            $this->increment('balance', $this->auto_reload_amount);
            
            // Create payment record
            $this->payments()->create([
                'transaction_id' => 'AUTO_' . uniqid(),
                'type' => 'credit',
                'amount' => $this->auto_reload_amount,
                'status' => 'completed',
                'payment_method' => 'auto_reload',
                'payment_processor' => 'quickbooks',
                'description' => "Auto-reload: $" . number_format($this->auto_reload_amount, 2),
                'processed_at' => now()
            ]);
            
            \Illuminate\Support\Facades\Log::info("Auto-reload triggered", [
                'buyer_id' => $this->id,
                'amount' => $this->auto_reload_amount,
                'new_balance' => $this->fresh()->balance
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Auto-reload failed", [
                'buyer_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
