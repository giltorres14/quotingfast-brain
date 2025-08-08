<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadQueue extends Model
{
    protected $table = 'lead_queue';
    
    protected $fillable = [
        'payload',
        'source',
        'status',
        'attempts',
        'error_message',
        'processed_at'
    ];
    
    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime'
    ];
    
    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where('attempts', '<', 3);
    }
    
    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'attempts' => $this->attempts + 1
        ]);
    }
    
    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);
    }
    
    /**
     * Mark as failed
     */
    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'processed_at' => now()
        ]);
    }
}



