<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateSystemTime extends Command
{
    protected $signature = 'system:update-time';
    protected $description = 'Update system time reference for accurate tracking';

    public function handle()
    {
        $currentTime = now('America/New_York');
        
        // Store in cache for quick access
        Cache::put('system_current_time', $currentTime->toIso8601String(), 86400); // 24 hours
        Cache::put('system_current_timestamp', $currentTime->timestamp, 86400);
        Cache::put('system_current_date', $currentTime->format('Y-m-d'), 86400);
        Cache::put('system_current_hour', $currentTime->hour, 3600); // 1 hour
        
        // Store timezone info
        Cache::put('system_timezone', 'America/New_York', 86400);
        Cache::put('system_timezone_offset', $currentTime->offsetHours, 86400);
        
        // Store ViciDial-specific time windows
        $isOptimalHour = in_array($currentTime->hour, [9, 10, 11, 15, 16, 17]); // 9-11 AM, 3-5 PM
        $isCallHours = $currentTime->hour >= 9 && $currentTime->hour < 18; // 9 AM - 6 PM
        $isTCPACompliant = $currentTime->hour >= 8 && $currentTime->hour < 21; // 8 AM - 9 PM
        
        Cache::put('vici_is_optimal_hour', $isOptimalHour, 3600);
        Cache::put('vici_is_call_hours', $isCallHours, 3600);
        Cache::put('vici_is_tcpa_compliant', $isTCPACompliant, 3600);
        
        // Log the update
        Log::info('System time updated', [
            'current_time' => $currentTime->format('Y-m-d H:i:s T'),
            'is_optimal' => $isOptimalHour,
            'is_call_hours' => $isCallHours
        ]);
        
        $this->info("System time updated: " . $currentTime->format('Y-m-d H:i:s T'));
        
        return 0;
    }
}











