<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CallixWhitelistService
{
    private $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
    private $userId;
    private $password;
    
    public function __construct()
    {
        $this->userId = env('CALLIX_USER_ID', 'UploadAPI');
        $this->password = env('CALLIX_PASSWORD', '8ZDWGAAQRD');
    }
    
    /**
     * Authenticate with Callix to whitelist our IP
     * This needs to be called periodically (every X minutes)
     */
    public function refreshWhitelist()
    {
        try {
            Log::info('🔄 Refreshing Callix whitelist...');
            
            // Prepare the authentication request
            $ch = curl_init($this->whitelistUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'user_id' => $this->userId,
                'password' => $this->password,
                'action' => 'validate'
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For self-signed cert
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                Log::error('❌ Callix whitelist refresh failed', [
                    'error' => $error
                ]);
                return false;
            }
            
            if ($httpCode === 200) {
                Log::info('✅ Callix whitelist refreshed successfully');
                Cache::put('callix_whitelist_last_refresh', now(), 3600);
                Cache::put('callix_whitelist_status', 'active', 3600);
                return true;
            } else {
                Log::warning('⚠️ Callix whitelist refresh returned unexpected code', [
                    'http_code' => $httpCode,
                    'response' => substr($response, 0, 500)
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Callix whitelist refresh exception', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check if whitelist needs refresh (called before Vici operations)
     */
    public function ensureWhitelisted()
    {
        $lastRefresh = Cache::get('callix_whitelist_last_refresh');
        
        // Refresh if never done or older than 5 minutes
        if (!$lastRefresh || $lastRefresh->diffInMinutes(now()) > 5) {
            return $this->refreshWhitelist();
        }
        
        return true;
    }
    
    /**
     * Test Vici connectivity after whitelisting
     */
    public function testViciConnection()
    {
        $testParams = [
            'source' => 'brain',
            'user' => 'UploadAPI',
            'pass' => '8ZDWGAAQRD',
            'function' => 'version'
        ];
        
        $viciUrl = 'http://162.241.97.210/vicidial/non_agent_api.php';
        $ch = curl_init($viciUrl . '?' . http_build_query($testParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 && strpos($response, 'VERSION') !== false;
    }
    
    /**
     * Full refresh cycle with retry logic
     */
    public function refreshWithRetry($maxRetries = 3)
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            // Step 1: Refresh whitelist
            if ($this->refreshWhitelist()) {
                // Step 2: Wait a moment for it to take effect
                sleep(2);
                
                // Step 3: Test Vici connection
                if ($this->testViciConnection()) {
                    Log::info('✅ Whitelist refresh and Vici test successful');
                    return true;
                }
            }
            
            if ($i < $maxRetries - 1) {
                Log::warning("⏳ Retry {$i} failed, waiting before next attempt...");
                sleep(5);
            }
        }
        
        Log::error('❌ Failed to refresh whitelist after ' . $maxRetries . ' attempts');
        return false;
    }
}


