<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EnhancedCallixWhitelistService
{
    private $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
    private $viciApiUrl = 'http://162.241.97.210/vicidial/non_agent_api.php';
    private $maxRetries = 3;
    
    /**
     * Get credentials from environment or use defaults
     */
    private function getCredentials()
    {
        return [
            'user_id' => env('VICI_API_USER', 'UploadAPI'),
            'password' => env('VICI_API_PASS', '8ZDWGAAQRD')
        ];
    }
    
    /**
     * Perform the actual whitelist authentication
     * FIXED: Use correct form field names (user_id, password)
     */
    public function authenticate()
    {
        $credentials = $this->getCredentials();
        
        Log::info('🔐 Attempting Callix whitelist authentication', [
            'url' => $this->whitelistUrl,
            'user' => $credentials['user_id'],
            'timestamp' => now()->toISOString()
        ]);
        
        try {
            // Method 1: Using Laravel HTTP client
            $response = Http::asForm()
                ->withOptions([
                    'verify' => false, // Disable SSL verification for self-signed cert
                    'timeout' => 10,
                    'connect_timeout' => 5
                ])
                ->post($this->whitelistUrl, [
                    'user_id' => $credentials['user_id'],
                    'password' => $credentials['password']
                ]);
            
            if ($response->successful()) {
                Log::info('✅ Callix whitelist authentication successful (HTTP client)', [
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 100)
                ]);
                $this->updateCache(true);
                return true;
            }
            
        } catch (\Exception $e) {
            Log::warning('⚠️ HTTP client failed, trying CURL', [
                'error' => $e->getMessage()
            ]);
        }
        
        // Method 2: Fallback to CURL (more control)
        return $this->authenticateWithCurl($credentials);
    }
    
    /**
     * CURL-based authentication with full control
     */
    private function authenticateWithCurl($credentials)
    {
        $ch = curl_init();
        
        // Build form data
        $postData = http_build_query([
            'user_id' => $credentials['user_id'],
            'password' => $credentials['password']
        ]);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->whitelistUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: Brain/1.0 (Render; Whitelist Client)'
            ],
            CURLOPT_VERBOSE => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        if ($error) {
            Log::error('❌ CURL whitelist failed', [
                'error' => $error,
                'http_code' => $httpCode
            ]);
            return false;
        }
        
        // Check various success conditions
        $success = ($httpCode === 200 || $httpCode === 302) || 
                   strpos($response, 'success') !== false ||
                   strpos($response, 'authenticated') !== false ||
                   !strpos($response, 'error');
        
        if ($success) {
            Log::info('✅ Callix whitelist authentication successful (CURL)', [
                'http_code' => $httpCode,
                'response_preview' => substr($response, 0, 100),
                'redirect_url' => $info['redirect_url'] ?? null
            ]);
            $this->updateCache(true);
            return true;
        }
        
        Log::error('❌ Callix whitelist authentication failed', [
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500)
        ]);
        
        return false;
    }
    
    /**
     * Test if Vici API is accessible (proves whitelist worked)
     */
    public function testViciAccess()
    {
        $testParams = [
            'source' => 'brain',
            'user' => 'UploadAPI',
            'pass' => '8ZDWGAAQRD',
            'function' => 'version'
        ];
        
        $url = $this->viciApiUrl . '?' . http_build_query($testParams);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $accessible = $httpCode === 200 && 
                     (strpos($response, 'VERSION') !== false || 
                      strpos($response, 'SUCCESS') !== false);
        
        Log::info('🔍 Vici API access test', [
            'accessible' => $accessible,
            'http_code' => $httpCode,
            'response_preview' => substr($response, 0, 50)
        ]);
        
        return $accessible;
    }
    
    /**
     * Full whitelist refresh with verification
     */
    public function refreshAndVerify()
    {
        Log::info('🔄 Starting full whitelist refresh and verification');
        
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            Log::info("📍 Attempt $attempt of $this->maxRetries");
            
            // Step 1: Authenticate with Callix
            if ($this->authenticate()) {
                // Step 2: Wait for propagation
                sleep(2);
                
                // Step 3: Test Vici access
                if ($this->testViciAccess()) {
                    Log::info('🎉 Whitelist refresh SUCCESSFUL and VERIFIED!');
                    return true;
                }
                
                Log::warning('⚠️ Whitelist seemed successful but Vici not accessible yet');
            }
            
            if ($attempt < $this->maxRetries) {
                $waitTime = $attempt * 3; // Progressive backoff
                Log::info("⏳ Waiting {$waitTime} seconds before retry...");
                sleep($waitTime);
            }
        }
        
        Log::error('💀 Failed to whitelist after all attempts');
        return false;
    }
    
    /**
     * Check if we need to refresh (called before Vici operations)
     */
    public function ensureWhitelisted()
    {
        $lastSuccess = Cache::get('callix_whitelist_verified');
        $needsRefresh = !$lastSuccess || 
                       now()->diffInMinutes($lastSuccess) > 5;
        
        if ($needsRefresh) {
            Log::info('🔄 Whitelist needs refresh (>5 min or never done)');
            return $this->refreshAndVerify();
        }
        
        // Do a quick Vici test to ensure still working
        if (!$this->testViciAccess()) {
            Log::warning('⚠️ Whitelist expired, refreshing...');
            return $this->refreshAndVerify();
        }
        
        return true;
    }
    
    /**
     * Update cache with success status
     */
    private function updateCache($success)
    {
        if ($success) {
            Cache::put('callix_whitelist_verified', now(), 3600);
            Cache::put('callix_whitelist_status', 'active', 3600);
            Cache::put('render_ip_whitelisted', '3.129.111.220', 3600);
        } else {
            Cache::forget('callix_whitelist_verified');
            Cache::put('callix_whitelist_status', 'failed', 60);
        }
    }
    
    /**
     * Get current whitelist status for monitoring
     */
    public function getStatus()
    {
        return [
            'whitelisted' => Cache::has('callix_whitelist_verified'),
            'last_success' => Cache::get('callix_whitelist_verified'),
            'status' => Cache::get('callix_whitelist_status', 'unknown'),
            'render_ip' => '3.129.111.220',
            'vici_accessible' => $this->testViciAccess(),
            'next_refresh' => Cache::has('callix_whitelist_verified') 
                ? Cache::get('callix_whitelist_verified')->addMinutes(5)
                : 'needed'
        ];
    }
}


