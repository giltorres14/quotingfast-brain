<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    protected $fillable = [
        'name',
        'endpoint', 
        'method',
        'type',
        'status',
        'description',
        'features',
        'test_url',
        'category',
        'sort_order',
        'is_system'
    ];

    protected $casts = [
        'features' => 'array',
        'is_system' => 'boolean'
    ];

    // Scopes for easy filtering
    public function scopeWebhooks($query)
    {
        return $query->where('type', 'webhook');
    }

    public function scopeApis($query)
    {
        return $query->where('type', 'api');
    }

    public function scopeTests($query)
    {
        return $query->where('type', 'test');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function getFullUrlAttribute()
    {
        return url($this->endpoint);
    }

    public function getMethodColorAttribute()
    {
        return match($this->method) {
            'GET' => 'blue',
            'POST' => 'yellow', 
            'PUT' => 'orange',
            'DELETE' => 'red',
            'PATCH' => 'purple',
            default => 'gray'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'testing' => 'yellow',
            'inactive' => 'red',
            default => 'gray'
        };
    }
}
