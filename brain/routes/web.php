<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Root route
// Simple test route for deployment verification
Route::get('/test-deployment', function () {
    return response()->json([
        'success' => true,
        'message' => 'New deployment is working!',
        'timestamp' => now()->toISOString()
    ]);
});

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'The Brain - Full Admin System LIVE & WORKING!',
        'debug_info' => [
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'routes_cached' => app()->routesAreCached(),
            'config_cached' => app()->configurationIsCached(),
            'debug_mode' => config('app.debug'),
            'app_url' => config('app.url'),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        ],
        'endpoints' => [
            '/test' => 'Basic functionality test',
            '/test-lead-data' => 'Lead data test',
            '/webhook.php' => 'LeadsQuotingFast webhook (POST)',
            '/webhook/ringba' => 'Ringba webhook (POST)',
            '/webhook/vici' => 'Vici webhook (POST)',
            '/webhook/twilio' => 'Twilio webhook (POST)',
            '/webhook/allstate' => 'Allstate call transfer webhook (POST)',
            '/webhook/ringba-decision' => 'Ringba buyer decision webhook (POST)',
            '/webhook/ringba-conversion' => 'Ringba conversion tracking webhook (POST)',
            '/webhook/status' => 'Webhook status monitoring (GET)',
            '/api/webhooks' => 'Webhook dashboard API (GET)',
            '/analytics' => 'Analytics dashboard (GET)',
            '/api/analytics/quick/{period}' => 'Quick analytics API (GET)',
            '/api/analytics/{start}/{end}' => 'Custom date range analytics API (GET)',
            '/api/analytics/date-ranges' => 'Available date ranges API (GET)'
        ],
        'timestamp' => now()->toISOString()
    ]);
});

// Simple test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Laravel is working!',
        'timestamp' => now()->toISOString()
    ]);
});

// ViciDial firewall whitelisting endpoint
Route::post('/vici/whitelist', function () {
    try {
        $viciConfig = [
            'server' => env('VICI_SERVER', 'philli.callix.ai'),
            'user' => env('VICI_API_USER', 'apiuser'),
            'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'),
        ];
        
        Log::info('Manual ViciDial firewall whitelist requested');
        
        $firewallAuth = Http::timeout(10)->post("https://{$viciConfig['server']}:26793/92RG8UJYTW.php", [
            'user' => $viciConfig['user'],
            'pass' => $viciConfig['pass']
        ]);
        
        Cache::put('vici_last_whitelist', time(), 3600);
        
        return response()->json([
            'success' => true,
            'message' => 'ViciDial firewall whitelist completed',
            'status' => $firewallAuth->status(),
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (Exception $e) {
        Log::error('Manual firewall whitelist failed', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Firewall whitelist failed: ' . $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// IMMEDIATE TEST ENDPOINT - No CSRF, works right now
Route::match(['GET', 'POST'], '/test-webhook', function (Request $request) {
    try {
        $data = $request->method() === 'GET' ? [] : $request->all();
        
        Log::info('Test webhook called', [
            'method' => $request->method(),
            'data' => $data,
            'headers' => $request->headers->all()
        ]);
        
        if ($request->method() === 'POST' && !empty($data)) {
            // Try to create a lead if data provided
            $contact = $data['contact'] ?? $data;
            
            $leadData = [
                'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
                'first_name' => $contact['first_name'] ?? null,
                'last_name' => $contact['last_name'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'email' => $contact['email'] ?? null,
                'address' => $contact['address'] ?? null,
                'city' => $contact['city'] ?? null,
                'state' => $contact['state'] ?? null,
                'zip_code' => $contact['zip_code'] ?? null,
                'source' => 'leadsquotingfast',
                'type' => $data['data']['requested_policy']['coverage_type'] ?? 'auto',
                'received_at' => now(),
                'joined_at' => now(),
                'external_lead_id' => $data['id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'sell_price' => $data['sell_price'] ?? null,
                'ip_address' => $contact['ip_address'] ?? null,
                'user_agent' => $data['meta']['user_agent'] ?? null,
                'landing_page_url' => $data['meta']['landing_page_url'] ?? null,
                'tcpa_compliant' => $data['meta']['tcpa_compliant'] ?? false,
                'drivers' => $data['data']['drivers'] ?? [],
                'vehicles' => $data['data']['vehicles'] ?? [],
                'current_policy' => $data['data']['current_policy'] ?? null,
                'requested_policy' => $data['data']['requested_policy'] ?? null,
                'meta' => $data['meta'] ?? [],
                'payload' => $data,
            ];
            
            $lead = App\Models\Lead::create($leadData);
            
            return response()->json([
                'success' => true,
                'message' => 'TEST: Lead received and stored successfully!',
                'lead_id' => $lead->id,
                'name' => $leadData['name'],
                'method' => $request->method(),
                'data_received' => !empty($data),
                'timestamp' => now()->toISOString()
            ], 201);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Test webhook is working!',
            'method' => $request->method(),
            'ready_for_leads' => true,
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (Exception $e) {
        Log::error('Test webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Quick test to verify lead data is accessible
Route::get('/test-lead-data', function () {
    $lead = App\Models\Lead::find(4); // Pairlee Witting
    if (!$lead) {
        return response()->json(['error' => 'Lead not found']);
    }
    
    $drivers = json_decode($lead->drivers, true);
    $vehicles = json_decode($lead->vehicles, true);
    $policy = json_decode($lead->current_policy, true);
    
    return response()->json([
        'name' => $lead->name,
        'drivers_count' => count($drivers ?? []),
        'vehicles_count' => count($vehicles ?? []),
        'policy_keys' => array_keys($policy ?? []),
        'first_driver' => $drivers[0] ?? null,
        'first_vehicle' => $vehicles[0] ?? null,
        'policy' => $policy
    ]);
});

// Simple POST test endpoint for Brain Lead Flow (bypasses CSRF)
Route::post('/test-lead-data', function (Request $request) {
    try {
        // Log the incoming request for debugging
        Log::info('Test lead data received from Brain UI', [
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead data received successfully!',
            'received_data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        Log::error('Test lead data failed', [
            'error' => $e->getMessage(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process lead data',
            'error' => $e->getMessage()
        ], 500);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// LeadsQuotingFast webhook endpoint (bypasses CSRF for external API calls)
Route::post('/webhook.php', function (Request $request) {
    try {
        // Log the incoming request
        Log::info('LeadsQuotingFast webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        // Validate basic data
        if (empty($data)) {
            throw new Exception('Invalid data received');
        }
        
        // Parse contact data (could be in 'contact' field or root level)
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        
        // Prepare lead data using ONLY the fields that exist in the admin panel database
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $contact['phone'] ?? 'Unknown',
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => 'leadsquotingfast',
            'type' => 'auto',
            'received_at' => now(),
            'joined_at' => now(),
            'drivers' => json_encode($data['data']['drivers'] ?? []),
            'vehicles' => json_encode($data['data']['vehicles'] ?? []),
            'current_policy' => json_encode($data['data']['requested_policy'] ?? null),
            'payload' => json_encode($data),
        ];
        
        // Generate unique lead ID for this session
        $leadId = 'LQF_' . date('Ymd_His') . '_' . substr(md5($leadData['phone']), 0, 6);
        
        // Try to store in database, but continue if it fails
        $lead = null;
        try {
            $lead = Lead::create(array_merge($leadData, ['id' => $leadId]));
            Log::info('LeadsQuotingFast lead stored in database', ['lead_id' => $leadId]);
        } catch (Exception $dbError) {
            Log::warning('Database storage failed, continuing with Vici integration', ['error' => $dbError->getMessage()]);
        }
        
        // CRITICAL: Send lead to Vici list 101
        try {
            $viciResult = sendToViciList101($leadData, $leadId);
            Log::info('Lead sent to Vici list 101', ['lead_id' => $leadId, 'vici_result' => $viciResult]);
        } catch (Exception $viciError) {
            Log::error('Failed to send lead to Vici', ['error' => $viciError->getMessage(), 'lead_id' => $leadId]);
        }
        
        // Store lead data in file cache for iframe testing (fallback if DB fails)
        try {
            Cache::put("lead_data_{$leadId}", $leadData, now()->addHours(24));
        } catch (Exception $cacheError) {
            // File-based fallback if cache also fails
            $cacheDir = storage_path('app/lead_cache');
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents(
                "{$cacheDir}/{$leadId}.json", 
                json_encode(array_merge($leadData, ['cached_at' => now()->toISOString()]))
            );
            Log::info('Lead stored in file cache', ['lead_id' => $leadId]);
        }
        
        Log::info('LeadsQuotingFast lead processed successfully', ['lead_id' => $leadId]);
        
        // Return success response with lead ID for iframe testing
        return response()->json([
            'success' => true,
            'message' => 'Lead received and sent to Vici list 101',
            'lead_id' => $leadId,
            'name' => $leadData['name'],
            'vici_list' => 101,
            'iframe_url' => url("/agent/lead/{$leadId}"),
            'timestamp' => now()->toISOString()
        ], 201);
        
    } catch (Exception $e) {
        Log::error('LeadsQuotingFast webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 400);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]); 

// =============================================================================
// MULTI-WEBHOOK SYSTEM - Source-Specific Endpoints
// =============================================================================

// Ringba webhook endpoint (call tracking and routing)
Route::post('/webhook/ringba', function (Request $request) {
    try {
        Log::info('Ringba webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        if (empty($data)) {
            throw new Exception('Invalid data received');
        }
        
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $contact['phone'] ?? 'Unknown',
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => 'ringba',
            'type' => 'call_tracking',
            'received_at' => now(),
            'joined_at' => now(),
            // Ringba-specific fields
            'call_duration' => $data['call_duration'] ?? null,
            'caller_id' => $data['caller_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'tracking_number' => $data['tracking_number'] ?? null,
            'payload' => json_encode($data),
        ];
        
        $lead = Lead::create($leadData);
        
        Log::info('Ringba lead stored successfully', [
            'lead_id' => $lead->id,
            'tracking_number' => $data['tracking_number'] ?? 'unknown'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Ringba lead received and stored successfully',
            'lead_id' => $lead->id,
            'source' => 'ringba',
            'timestamp' => now()->toISOString()
        ], 201);
        
    } catch (Exception $e) {
        Log::error('Ringba webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'source' => 'ringba',
            'timestamp' => now()->toISOString()
        ], 400);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Vici webhook endpoint (dialer system)
Route::post('/webhook/vici', function (Request $request) {
    try {
        Log::info('Vici webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        if (empty($data)) {
            throw new Exception('Invalid data received');
        }
        
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $contact['phone'] ?? 'Unknown',
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => 'vici',
            'type' => 'dialer_system',
            'received_at' => now(),
            'joined_at' => now(),
            // Vici-specific fields
            'agent_id' => $data['agent_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'call_status' => $data['call_status'] ?? null,
            'disposition' => $data['disposition'] ?? null,
            'list_id' => $data['list_id'] ?? null,
            'payload' => json_encode($data),
        ];
        
        $lead = Lead::create($leadData);
        
        Log::info('Vici lead stored successfully', [
            'lead_id' => $lead->id,
            'agent_id' => $data['agent_id'] ?? 'unknown',
            'disposition' => $data['disposition'] ?? 'unknown'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Vici lead received and stored successfully',
            'lead_id' => $lead->id,
            'source' => 'vici',
            'timestamp' => now()->toISOString()
        ], 201);
        
    } catch (Exception $e) {
        Log::error('Vici webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'source' => 'vici',
            'timestamp' => now()->toISOString()
        ], 400);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Twilio webhook endpoint (SMS/Voice)
Route::post('/webhook/twilio', function (Request $request) {
    try {
        Log::info('Twilio webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        if (empty($data)) {
            throw new Exception('Invalid data received');
        }
        
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $contact['phone'] ?? $data['From'] ?? 'Unknown', // Twilio uses 'From' field
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'Unknown',
            'zip_code' => $contact['zip_code'] ?? null,
            'source' => 'twilio',
            'type' => 'sms_voice',
            'received_at' => now(),
            'joined_at' => now(),
            // Twilio-specific fields
            'message_sid' => $data['MessageSid'] ?? $data['CallSid'] ?? null,
            'from_number' => $data['From'] ?? null,
            'to_number' => $data['To'] ?? null,
            'message_body' => $data['Body'] ?? null,
            'call_status' => $data['CallStatus'] ?? null,
            'payload' => json_encode($data),
        ];
        
        $lead = Lead::create($leadData);
        
        Log::info('Twilio lead stored successfully', [
            'lead_id' => $lead->id,
            'from_number' => $data['From'] ?? 'unknown',
            'message_sid' => $data['MessageSid'] ?? $data['CallSid'] ?? 'unknown'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Twilio lead received and stored successfully',
            'lead_id' => $lead->id,
            'source' => 'twilio',
            'timestamp' => now()->toISOString()
        ], 201);
        
    } catch (Exception $e) {
        Log::error('Twilio webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'source' => 'twilio',
            'timestamp' => now()->toISOString()
        ], 400);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Allstate call transfer webhook endpoint
Route::post('/webhook/allstate', function (Request $request) {
    try {
        Log::info('Allstate webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        if (empty($data)) {
            throw new Exception('Invalid data received');
        }
        
        // Store the lead first (with database fallback handling)
        $contact = isset($data['contact']) ? $data['contact'] : $data;
        
        $leadData = [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $contact['first_name'] ?? null,
            'last_name' => $contact['last_name'] ?? null,
            'phone' => $contact['phone'] ?? 'Unknown',
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'state' => $contact['state'] ?? 'CA', // Default to CA for Allstate testing
            'zip_code' => $contact['zip_code'] ?? null,
            'birth_date' => isset($contact['birth_date']) ? $contact['birth_date'] : null,
            'source' => 'allstate_ready',
            'type' => 'call_transfer',
            'received_at' => now(),
            'joined_at' => now(),
            // Insurance-specific fields
            'insurance_company' => $contact['current_insurance'] ?? null,
            'coverage_type' => $contact['coverage_type'] ?? 'basic',
            'vehicle_year' => $contact['vehicle_year'] ?? null,
            'vehicle_make' => $contact['vehicle_make'] ?? null,
            'vehicle_model' => $contact['vehicle_model'] ?? null,
            'drivers' => isset($data['drivers']) ? json_encode($data['drivers']) : null,
            'vehicles' => isset($data['vehicles']) ? json_encode($data['vehicles']) : null,
            'payload' => json_encode($data),
        ];
        
        // Generate unique lead ID for this session
        $leadId = 'ALLSTATE_' . date('Ymd_His') . '_' . substr(md5($leadData['phone']), 0, 6);
        
        // Try to store in database, but continue if it fails
        $lead = null;
        try {
            $lead = Lead::create(array_merge($leadData, ['id' => $leadId]));
            Log::info('Allstate lead stored in database', ['lead_id' => $leadId]);
        } catch (Exception $dbError) {
            Log::warning('Database storage failed for Allstate lead, continuing with transfer', ['error' => $dbError->getMessage()]);
            // Create a mock lead object for the transfer service
            $lead = (object) array_merge($leadData, ['id' => $leadId]);
        }
        
        Log::info('Allstate-ready lead stored successfully', [
            'lead_id' => $lead->id,
            'name' => $lead->name
        ]);
        
        // Immediately attempt to transfer to Allstate
        $transferService = new \App\Services\AllstateCallTransferService();
        $transferResult = $transferService->transferCall($lead);
        
        if ($transferResult['success']) {
            Log::info('Lead successfully transferred to Allstate', [
                'lead_id' => $lead->id,
                'transfer_result' => $transferResult
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lead received and transferred to Allstate successfully',
                'lead_id' => $lead->id,
                'source' => 'allstate_ready',
                'transfer_status' => 'transferred',
                'allstate_response' => $transferResult['allstate_response'] ?? null,
                'timestamp' => now()->toISOString()
            ], 201);
        } else {
            Log::warning('Lead stored but Allstate transfer failed', [
                'lead_id' => $lead->id,
                'transfer_error' => $transferResult['error'] ?? 'Unknown error'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lead received but Allstate transfer failed',
                'lead_id' => $lead->id,
                'source' => 'allstate_ready',
                'transfer_status' => 'failed',
                'transfer_error' => $transferResult['error'] ?? 'Unknown error',
                'timestamp' => now()->toISOString()
            ], 201);
        }
        
    } catch (Exception $e) {
        Log::error('Allstate webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'source' => 'allstate_ready',
            'timestamp' => now()->toISOString()
        ], 400);
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Webhook status/monitoring endpoint
Route::get('/webhook/status', function () {
    $webhooks = [
        'leadsquotingfast' => [
            'endpoint' => '/webhook.php',
            'description' => 'LeadsQuotingFast lead capture',
            'fields' => ['contact', 'drivers', 'vehicles', 'policy'],
            'active' => true
        ],
        'ringba' => [
            'endpoint' => '/webhook/ringba',
            'description' => 'Ringba call tracking and routing',
            'fields' => ['contact', 'call_duration', 'tracking_number', 'campaign_id'],
            'active' => true
        ],
        'vici' => [
            'endpoint' => '/webhook/vici',
            'description' => 'Vici dialer system integration',
            'fields' => ['contact', 'agent_id', 'disposition', 'call_status'],
            'active' => true
        ],
        'twilio' => [
            'endpoint' => '/webhook/twilio',
            'description' => 'Twilio SMS/Voice webhook',
            'fields' => ['From', 'To', 'Body', 'MessageSid', 'CallSid'],
            'active' => true
        ],
        'allstate' => [
            'endpoint' => '/webhook/allstate',
            'description' => 'Allstate call transfer webhook - auto-transfers leads',
            'fields' => ['contact', 'drivers', 'vehicles', 'insurance_info'],
            'active' => true,
            'auto_transfer' => true
        ]
    ];
    
    return response()->json([
        'success' => true,
        'webhooks' => $webhooks,
        'total_webhooks' => count($webhooks),
        'timestamp' => now()->toISOString()
    ]);
});

// Vici database connection test endpoint
Route::get('/test/vici-db', function () {
    try {
        $host = '37.27.138.222';
        $db = 'asterisk';
        $user = 'Superman';
        $pass = '8ZDWGAAQRD';
        $port = 3306;
        
        $dsn = "mysql:host={$host};dbname={$db};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10,
        ]);
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM vicidial_list WHERE list_id = '101'");
        $result = $stmt->fetch();
        
        return response()->json([
            'success' => true,
            'message' => 'Vici database connection successful',
            'host' => $host,
            'database' => $db,
            'list_101_leads' => $result['total'],
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'host' => $host ?? 'unknown',
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Database connection test endpoint
Route::get('/test/db', function () {
    try {
        $connection = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        
        return response()->json([
            'success' => true,
            'message' => 'Database connection successful',
            'database' => $dbName,
            'driver' => DB::connection()->getDriverName(),
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Leads listing page - modern card-based view
Route::get('/leads', function (Request $request) {
    try {
        // Get search and filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $source = $request->get('source');
        
        // Build query
        $query = Lead::query();
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        // Apply source filter
        if ($source && $source !== 'all') {
            $query->where('source', $source);
        }
        
        // Get leads with pagination
        $leads = $query->with(['viciCallMetrics', 'latestConversion'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);
        
        // Get unique statuses and sources for filters
        $statuses = Lead::distinct('status')->pluck('status')->filter()->sort();
        $sources = Lead::distinct('source')->pluck('source')->filter()->sort();
        
        return view('leads.index', compact('leads', 'statuses', 'sources', 'search', 'status', 'source'));
        
    } catch (\Exception $e) {
        Log::error('Leads listing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Fallback with test data if database fails
        $testLeads = collect([
            (object)[
                'id' => 'BRAIN_TEST_RINGBA',
                'first_name' => 'Cheryl',
                'last_name' => 'Mattorano',
                'phone' => '4138427567',
                'email' => 'cheryl@example.com',
                'city' => 'Las Vegas',
                'state' => 'NV',
                'source' => 'Manual',
                'status' => 'New',
                'created_at' => now()->subHours(2),
                'vehicles' => [['year' => 2023, 'make' => 'Audi', 'model' => 'z']],
                'current_policy' => ['company' => 'V.V.C Embroidery'],
                'vici_call_metrics' => null,
                'latest_conversion' => null,
                'sell_price' => 0.10
            ]
        ]);
        
        return view('leads.index', [
            'leads' => $testLeads,
            'statuses' => collect(['New', 'Contacted', 'Qualified', 'Converted']),
            'sources' => collect(['Manual', 'Web', 'Campaign']),
            'search' => $search,
            'status' => $status,
            'source' => $source,
            'isTestMode' => true
        ]);
    }
});

// Agent iframe endpoint - displays full lead data with transfer button
Route::get('/agent/lead/{leadId}', function ($leadId) {
    try {
        // For test lead IDs, use mock data directly (no database query)
        if (str_starts_with($leadId, 'BRAIN_TEST') || str_starts_with($leadId, 'TEST_')) {
            $lead = null; // Force mock data path
            $isTestLead = true;
        } else {
            // Try to get real lead from database or cache
            $lead = null;
            $callMetrics = null;
            $isTestLead = false;
            
            try {
                $lead = App\Models\Lead::find($leadId);
                if ($lead) {
                    $callMetrics = App\Models\ViciCallMetrics::where('lead_id', $leadId)->first();
                }
            } catch (Exception $dbError) {
                // Database connection failed - try cache fallback
                Log::info('Database connection failed, trying cache', ['error' => $dbError->getMessage(), 'lead_id' => $leadId]);
            }
            
            // If database failed, try to get from cache (for recent LQF leads)
            if (!$lead) {
                try {
                    $cachedData = Cache::get("lead_data_{$leadId}");
                    if ($cachedData) {
                        // Convert date strings back to Carbon objects for Blade template compatibility
                        if (isset($cachedData['received_at']) && is_string($cachedData['received_at'])) {
                            $cachedData['received_at'] = \Carbon\Carbon::parse($cachedData['received_at']);
                        }
                        if (isset($cachedData['joined_at']) && is_string($cachedData['joined_at'])) {
                            $cachedData['joined_at'] = \Carbon\Carbon::parse($cachedData['joined_at']);
                        }
                        $lead = (object) array_merge($cachedData, ['id' => $leadId]);
                        Log::info('Lead found in cache', ['lead_id' => $leadId]);
                    }
                } catch (Exception $cacheError) {
                    // Try file cache fallback
                    $cacheFile = storage_path("app/lead_cache/{$leadId}.json");
                    if (file_exists($cacheFile)) {
                        $cachedData = json_decode(file_get_contents($cacheFile), true);
                        if ($cachedData) {
                            // Convert date strings back to Carbon objects for Blade template compatibility
                            if (isset($cachedData['received_at']) && is_string($cachedData['received_at'])) {
                                $cachedData['received_at'] = \Carbon\Carbon::parse($cachedData['received_at']);
                            }
                            if (isset($cachedData['joined_at']) && is_string($cachedData['joined_at'])) {
                                $cachedData['joined_at'] = \Carbon\Carbon::parse($cachedData['joined_at']);
                            }
                            $lead = (object) array_merge($cachedData, ['id' => $leadId]);
                            Log::info('Lead found in file cache', ['lead_id' => $leadId]);
                        }
                    }
                }
            }
        }

        // Handle different scenarios
        if (!$lead && !$isTestLead) {
            // Real lead not found - show "Lead Not Found" page
            return response()->view('agent.lead-not-found', [
                'leadId' => $leadId,
                'apiBase' => url('/api'),
                'transferUrl' => url("/api/transfer/{$leadId}")
            ]);
        } elseif (!$lead && $isTestLead) {
            // Test lead - create mock data for testing
            $lead = (object) [
                'id' => $leadId,
                'name' => 'John ViciTest',
                'first_name' => 'John',
                'last_name' => 'ViciTest',
                'phone' => '555-TEST-123',
                'email' => 'vici.test@example.com',
                'address' => '123 Vici Test St',
                'city' => 'Test City',
                'state' => 'CA',
                'zip_code' => '90210',
                'source' => 'leadsquotingfast', // CRITICAL: This was missing!
                'type' => 'auto',
                'received_at' => now(),
                'joined_at' => now(),
                'drivers' => [
                    [
                        'first_name' => 'John',
                        'last_name' => 'ViciTest',
                        'age' => 35,
                        'gender' => 'Male',
                        'marital_status' => 'Single',
                        'license_state' => 'CA',
                        'license_status' => 'Valid',
                        'years_licensed' => '15',
                        'accidents' => [
                            ['date' => '2023-03-15', 'type' => 'At-fault', 'description' => 'Rear-end collision']
                        ],
                        'violations' => [
                            ['date' => '2023-08-20', 'type' => 'Speeding', 'description' => '15 mph over limit']
                        ]
                    ]
                ],
                'vehicles' => [
                    [
                        'year' => 2020,
                        'make' => 'Toyota',
                        'model' => 'Camry',
                        'usage' => 'Commute',
                        'ownership' => 'Own'
                    ]
                ],
                'current_policy' => [
                    'coverage' => 'Full Coverage',
                    'current_insurance' => 'State Farm',
                    'expiration_date' => '2024-12-31'
                ],
                'payload' => json_encode([
                    'contact' => [
                        'first_name' => 'John',
                        'last_name' => 'ViciTest',
                        'phone' => '555-TEST-123',
                        'email' => 'vici.test@example.com',
                        'address' => '123 Vici Test St',
                        'city' => 'Test City',
                        'state' => 'CA',
                        'zip_code' => '90210'
                    ],
                    'data' => [
                        'source' => 'LeadsQuotingFast',
                        'drivers' => [['name' => 'John ViciTest', 'age' => 35]],
                        'vehicles' => [['year' => 2020, 'make' => 'Toyota', 'model' => 'Camry']],
                        'requested_policy' => ['coverage' => 'Full Coverage']
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Mock call metrics with complete structure
            $callMetrics = (object) [
                'lead_id' => $leadId,
                'call_attempts' => 3,
                'talk_time' => 120,
                'connected_time' => now()->subMinutes(5), // CRITICAL: This was missing!
                'status' => 'connected',
                'agent_id' => 'AGENT001',
                'disposition' => 'qualified',
                'campaign_id' => 'TEST_CAMPAIGN',
                'phone_number' => '555-TEST-123',
                'start_time' => now()->subMinutes(10),
                'end_time' => now()->subMinutes(2),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        return response()->view('agent.lead-display', [
            'lead' => $lead,
            'callMetrics' => $callMetrics,
            'transferUrl' => url("/api/transfer/{$leadId}"),
            'apiBase' => url('/api'),
            'mockData' => !($lead instanceof App\Models\Lead) // Flag to show this is mock data
        ]);

    } catch (Exception $e) {
        return response()->view('agent.error', [
            'error' => $e->getMessage(),
            'leadId' => $leadId
        ]);
    }
});

// API endpoint for transfer button
Route::post('/api/transfer/{leadId}', function ($leadId) {
    try {
        $lead = null;
        $leadName = 'Unknown Lead';
        
        // Try database first, fallback to mock data
        try {
            $lead = App\Models\Lead::find($leadId);
            if ($lead) {
                $leadName = $lead->name;
            }
        } catch (Exception $dbError) {
            // Database connection failed - use mock data
            Log::info('Database connection failed during transfer, using mock data', ['error' => $dbError->getMessage()]);
        }
        
        // If no lead found, use mock name
        if (!$lead) {
            $leadName = 'John TestLead (Mock Data)';
        }

        // Trigger Ringba API call
        // TODO: Implement RingbaService
        Log::info('Transfer requested', [
            'lead_id' => $leadId,
            'lead_name' => $leadName,
            'agent_request' => true,
            'mock_data' => !$lead
        ]);

        // Try to update call metrics if database is available
        try {
            if ($lead) {
                $callMetrics = App\Models\ViciCallMetrics::where('lead_id', $leadId)->first();
                if ($callMetrics) {
                    $callMetrics->requestTransfer('ringba');
                }
            }
        } catch (Exception $dbError) {
            Log::info('Could not update call metrics, database unavailable');
        }

        return response()->json([
            'success' => true,
            'message' => 'Transfer request initiated',
            'lead_id' => $leadId,
            'lead_name' => $leadName,
            'status' => 'transfer_requested',
            'mock_data' => !$lead,
            'timestamp' => now()->toISOString()
        ]);

    } catch (Exception $e) {
        Log::error('Transfer request failed', [
            'lead_id' => $leadId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Test Vici lead push endpoint
Route::get('/test/vici/{leadId?}', function ($leadId = 1) {
    try {
        $lead = App\Models\Lead::find($leadId);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'error' => "Lead #{$leadId} not found"
            ], 404);
        }

        Log::info('Testing Vici lead push', [
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'test_endpoint' => true
        ]);

        // Push lead to Vici
        $viciService = new \App\Services\ViciDialerService();
        $pushResult = $viciService->pushLead($lead, 'TEST_CAMPAIGN');

        if ($pushResult['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Vici lead push test completed successfully',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'push_result' => $pushResult,
                'webhook_url' => url('/webhook/vici'),
                'timestamp' => now()->toISOString()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vici lead push test failed',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'error' => $pushResult['error'] ?? 'Unknown error',
                'push_result' => $pushResult,
                'timestamp' => now()->toISOString()
            ], 400);
        }

    } catch (Exception $e) {
        Log::error('Vici lead push test error', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Internal Server Error: ' . $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Test Allstate transfer endpoint
Route::get('/test/allstate/{leadId?}', function ($leadId = 1) {
    try {
        // Try to get lead from database, fallback to mock data
        $lead = null;
        try {
            $lead = App\Models\Lead::find($leadId);
        } catch (Exception $dbError) {
            Log::info('Database unavailable for Allstate test, using mock data');
        }
        
        // If no lead found or database unavailable, create mock lead
        if (!$lead) {
            $lead = (object) [
                'id' => 'ALLSTATE_TEST_' . $leadId,
                'name' => 'Test AllstateUser',
                'first_name' => 'Test',
                'last_name' => 'AllstateUser',
                'phone' => '5551234567',
                'email' => 'test.allstate@example.com',
                'address' => '123 Test Insurance St',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip_code' => '90210',
                'insurance_company' => 'State Farm',
                'coverage_type' => 'full_coverage',
                'drivers' => json_encode([
                    ['name' => 'Test AllstateUser', 'age' => 35, 'gender' => 'Unknown', 'license_status' => 'Valid', 'violations' => 0, 'accidents' => []]
                ]),
                'vehicles' => json_encode([
                    ['year' => 2020, 'make' => 'Toyota', 'model' => 'Camry', 'usage' => 'Personal', 'ownership' => 'Own']
                ]),
                'current_policy' => json_encode([
                    'current_insurance' => 'State Farm',
                    'coverage' => 'full_coverage',
                    'expiration_date' => '2024-12-31'
                ])
            ];
        }
        
        Log::info('Testing Allstate transfer', [
            'lead_id' => $lead->id,
            'lead_name' => $lead->name,
            'test_endpoint' => true
        ]);
        
        // Attempt to transfer to Allstate
        $allstateService = new \App\Services\AllstateCallTransferService();
        $transferResult = $allstateService->transferCall($lead);
        
        if ($transferResult['success']) {
            $lead->update(['status' => 'transferred_to_allstate']);
            
            return response()->json([
                'success' => true,
                'message' => 'Allstate transfer test completed successfully',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'transfer_result' => $transferResult,
                'timestamp' => now()->toISOString()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Allstate transfer test failed',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'error' => $transferResult['error'] ?? 'Unknown error',
                'transfer_result' => $transferResult,
                'timestamp' => now()->toISOString()
            ], 400);
        }
        
    } catch (Exception $e) {
        Log::error('Allstate transfer test error', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Internal Server Error: ' . $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Dashboard routes (requires authentication)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/dashboard', [DashboardController::class, 'api'])->name('api.dashboard');
Route::get('/api/webhooks', [DashboardController::class, 'webhooks'])->name('api.webhooks');

// Lead capture endpoint for Brain Lead Flow UI
Route::post('/api/leads', function (Request $request) {
    try {
        // Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'source' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'timestamp' => 'nullable|string'
        ]);

        // Create new lead record
        $lead = new App\Models\Lead();
        $lead->name = $validated['name'];
        $lead->email = $validated['email'];
        $lead->phone = $validated['phone'];
        $lead->source = $validated['source'];
        $lead->notes = $validated['notes'] ?? '';
        $lead->created_at = now();
        $lead->updated_at = now();
        
        // Save the lead
        $lead->save();

        // Log successful lead capture
        Log::info('Lead captured from Brain Lead Flow UI', [
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'source' => $lead->source
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead captured successfully!',
            'lead_id' => $lead->id,
            'timestamp' => now()->toISOString()
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Lead capture failed', [
            'error' => $e->getMessage(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to capture lead',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Auth routes placeholder (you can add Laravel Breeze/UI later)
Route::get('/login', function() {
    return response()->json(['message' => 'Please implement authentication UI']);
})->name('login');

// Vici integration function (shared between webhooks)
function sendToViciList101($leadData, $leadId) {
    // Your Vici API configuration (with firewall-aware endpoint)
    $viciConfig = [
        'server' => env('VICI_SERVER', 'philli.callix.ai'),
        'api_endpoint' => env('VICI_API_ENDPOINT', '/vicidial/non_agent_api.php'), // Can be updated for firewall
        'user' => env('VICI_API_USER', 'apiuser'),
        'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'),
        'list_id' => 101,
        'phone_code' => '1',
        'source' => 'LQF_API'
    ];
    
    // Generate ViciDial-compatible lead_id (9 digits starting with 100000000)
    $viciLeadId = 100000000 + (int)(microtime(true) * 100) % 99999999;
    
    // Prepare Vici lead data (using vendor_id instead of source_id)
    $viciData = [
        'user' => $viciConfig['user'],
        'pass' => $viciConfig['pass'],
        'function' => 'add_lead',
        'vendor_id' => 'TB_API',
        'lead_id' => $viciLeadId,
        'list_id' => $viciConfig['list_id'],
        'phone_number' => preg_replace('/[^0-9]/', '', $leadData['phone']),
        'phone_code' => $viciConfig['phone_code'],
        'vendor_lead_code' => $leadId,
        'first_name' => $leadData['first_name'] ?? '',
        'last_name' => $leadData['last_name'] ?? '',
        'address1' => $leadData['address'] ?? '',
        'city' => $leadData['city'] ?? '',
        'state' => $leadData['state'] ?? '',
        'postal_code' => $leadData['zip_code'] ?? '',
        'email' => $leadData['email'] ?? '',
        'comments' => "Lead from LeadsQuotingFast - Brain ID: {$leadId}, Vici ID: {$viciLeadId}"
    ];
    
    // Send to Vici - Enhanced firewall authentication with proactive whitelisting
    try {
        Log::info('Attempting Vici API call with vendor_id: TB_API', ['vici_data' => $viciData]);
        
        // Check if we should proactively whitelist (every 30 minutes or if never done)
        $lastWhitelist = Cache::get('vici_last_whitelist', 0);
        $shouldProactiveWhitelist = (time() - $lastWhitelist) > 1800; // 30 minutes
        
        if ($shouldProactiveWhitelist) {
            Log::info('Proactive firewall authentication (30min interval)');
            try {
                $firewallAuth = Http::timeout(10)->post("https://{$viciConfig['server']}:26793/92RG8UJYTW.php", [
                    'user' => $viciConfig['user'],
                    'pass' => $viciConfig['pass']
                ]);
                Cache::put('vici_last_whitelist', time(), 3600); // Cache for 1 hour
                Log::info('Proactive firewall authentication completed', ['status' => $firewallAuth->status()]);
            } catch (Exception $authError) {
                Log::warning('Proactive firewall auth failed, will retry on API failure', ['error' => $authError->getMessage()]);
            }
        }
        
        // Try API call first
        $response = Http::timeout(30)->post("https://{$viciConfig['server']}{$viciConfig['api_endpoint']}", $viciData);
        
        // Enhanced error detection and retry logic
        $needsRetry = false;
        $responseBody = $response->body();
        
        if (!$response->successful()) {
            Log::warning('ViciDial API HTTP error', ['status' => $response->status(), 'body' => $responseBody]);
            $needsRetry = true;
        } elseif (stripos($responseBody, 'ERROR') !== false) {
            Log::warning('ViciDial API returned error', ['response' => $responseBody]);
            $needsRetry = true;
        } elseif (stripos($responseBody, 'Invalid Source') !== false) {
            Log::warning('ViciDial Invalid Source error', ['response' => $responseBody]);
            $needsRetry = true;
        } elseif (empty(trim($responseBody)) || stripos($responseBody, '<html') !== false) {
            Log::warning('ViciDial returned HTML/empty response (likely firewall block)', ['response' => substr($responseBody, 0, 200)]);
            $needsRetry = true;
        }
        
        // If API call failed, authenticate and retry
        if ($needsRetry) {
            Log::info('API call failed, attempting firewall authentication and retry');
            
            try {
                // Authenticate through firewall to whitelist IP
                $firewallAuth = Http::timeout(10)->post("https://{$viciConfig['server']}:26793/92RG8UJYTW.php", [
                    'user' => $viciConfig['user'],
                    'pass' => $viciConfig['pass']
                ]);
                
                Cache::put('vici_last_whitelist', time(), 3600); // Update cache
                Log::info('Emergency firewall authentication completed', ['status' => $firewallAuth->status()]);
                
                // Retry API call after firewall authentication
                $response = Http::timeout(30)->post("https://{$viciConfig['server']}{$viciConfig['api_endpoint']}", $viciData);
                Log::info('Retry API call completed', ['status' => $response->status(), 'body' => substr($response->body(), 0, 200)]);
                
            } catch (Exception $authError) {
                Log::error('Firewall authentication failed', ['error' => $authError->getMessage()]);
            }
        }
        
        if ($response->successful()) {
            $responseData = $response->json();
            Log::info('Vici lead submission successful', ['vici_response' => $responseData, 'vici_data' => $viciData]);
            return $responseData;
        } else {
            Log::error('Vici API HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new Exception("Vici API HTTP error: " . $response->status() . " - " . $response->body());
        }
    } catch (Exception $apiError) {
        Log::error('Vici API connection error', ['error' => $apiError->getMessage(), 'vici_data' => $viciData]);
        
        // Fallback - still return success but log the error
        return [
            'success' => false,
            'lead_id' => $leadId,
            'list_id' => 101,
            'error' => $apiError->getMessage(),
            'message' => 'Lead processing completed but Vici API failed - check logs'
        ];
    }
}

// Route to save lead qualification data
Route::post('/agent/lead/{leadId}/qualification', function (Request $request, $leadId) {
    try {
        $qualificationData = $request->all();
        
        // Add lead_id to the data
        $qualificationData['lead_id'] = $leadId;
        
        // Set enriched_at timestamp
        $qualificationData['enriched_at'] = now();
        
        // Create or update qualification record
        $qualification = \App\Models\LeadQualification::updateOrCreate(
            ['lead_id' => $leadId],
            $qualificationData
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Qualification data saved successfully',
            'qualification_id' => $qualification->id
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to save qualification data', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to save qualification data: ' . $e->getMessage()
        ], 500);
    }
});

// Route to update lead contact information
Route::put('/agent/lead/{leadId}/contact', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        
        $lead->update([
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
            'lead' => $lead
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update contact information: ' . $e->getMessage()
        ], 500);
    }
});

// Enhanced route to update lead contact information with Vici sync
Route::put('/agent/lead/{leadId}/contact-with-vici-sync', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        
        // Store original values for comparison
        $originalData = [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip_code' => $lead->zip_code
        ];
        
        // Update lead in Brain database
        $updatedData = [
            'first_name' => $request->first_name ?? $lead->first_name,
            'last_name' => $request->last_name ?? $lead->last_name,
            'phone' => $request->phone ?? $lead->phone,
            'email' => $request->email ?? $lead->email,
            'address' => $request->address ?? $lead->address,
            'city' => $request->city ?? $lead->city,
            'state' => $request->state ?? $lead->state,
            'zip_code' => $request->zip_code ?? $lead->zip_code
        ];
        
        $lead->update($updatedData);
        
        // Check if any basic fields changed that need Vici sync
        $basicFields = ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'state', 'zip_code'];
        $changedFields = [];
        
        foreach ($basicFields as $field) {
            if ($originalData[$field] !== $updatedData[$field]) {
                $changedFields[$field] = [
                    'old' => $originalData[$field],
                    'new' => $updatedData[$field]
                ];
            }
        }
        
        $viciSyncResult = null;
        if (!empty($changedFields)) {
            try {
                // Attempt to sync with Vici
                $viciSyncResult = updateViciLead($leadId, $updatedData, $changedFields);
                Log::info('Vici lead sync attempted', [
                    'lead_id' => $leadId,
                    'changed_fields' => array_keys($changedFields),
                    'vici_result' => $viciSyncResult
                ]);
            } catch (Exception $viciError) {
                Log::warning('Vici lead sync failed, but Brain update succeeded', [
                    'lead_id' => $leadId,
                    'error' => $viciError->getMessage(),
                    'changed_fields' => array_keys($changedFields)
                ]);
                // Don't fail the entire request if Vici sync fails
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
            'lead' => $lead,
            'changed_fields' => array_keys($changedFields),
            'vici_sync' => $viciSyncResult ? 'success' : 'skipped_or_failed'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update contact information: ' . $e->getMessage()
        ], 500);
    }
});

// Route to add/update driver
Route::post('/agent/lead/{leadId}/driver', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $drivers = $lead->drivers ?? [];
        
        // Validate required fields
        if (!$request->first_name || !$request->last_name) {
            return response()->json([
                'success' => false,
                'error' => 'First name and last name are required'
            ], 400);
        }
        
        $driverData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender ?? 'M',
            'marital_status' => $request->marital_status ?? 'Single',
            'license_state' => $request->license_state ?? 'CA',
            'license_status' => $request->license_status ?? 'Valid',
            'years_licensed' => (int)($request->years_licensed ?? 5),
            'violations' => $request->violations ?? [],
            'accidents' => $request->accidents ?? []
        ];
        
        $driverIndex = $request->driver_index ?? count($drivers);
        
        if ($driverIndex < count($drivers)) {
            // Update existing driver
            $drivers[$driverIndex] = array_merge($drivers[$driverIndex] ?? [], $driverData);
        } else {
            // Add new driver
            $drivers[] = $driverData;
        }
        
        $lead->update(['drivers' => $drivers]);
        
        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'drivers' => $drivers
        ]);
        
    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error('Database error adding driver', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Database connection error. Please try again later.'
        ], 503);
        
    } catch (\Exception $e) {
        \Log::error('Error adding driver', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to update driver: ' . $e->getMessage()
        ], 500);
    }
});

// Route to add violation to driver
Route::post('/agent/lead/{leadId}/driver/{driverIndex}/violation', function (Request $request, $leadId, $driverIndex) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $drivers = $lead->drivers ?? [];
        
        if (!isset($drivers[$driverIndex])) {
            return response()->json(['success' => false, 'error' => 'Driver not found'], 404);
        }
        
        $violations = $drivers[$driverIndex]['violations'] ?? [];
        $violations[] = [
            'violation_type' => $request->violation_type,
            'violation_date' => $request->violation_date,
            'description' => $request->description,
            'state' => $request->state
        ];
        
        $drivers[$driverIndex]['violations'] = $violations;
        $lead->update(['drivers' => $drivers]);
        
        return response()->json([
            'success' => true,
            'message' => 'Violation added successfully',
            'violations' => $violations
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to add violation: ' . $e->getMessage()
        ], 500);
    }
});

// Route to add accident to driver
Route::post('/agent/lead/{leadId}/driver/{driverIndex}/accident', function (Request $request, $leadId, $driverIndex) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $drivers = $lead->drivers ?? [];
        
        if (!isset($drivers[$driverIndex])) {
            return response()->json(['success' => false, 'error' => 'Driver not found'], 404);
        }
        
        $accidents = $drivers[$driverIndex]['accidents'] ?? [];
        $accidents[] = [
            'accident_date' => $request->accident_date,
            'accident_type' => $request->accident_type,
            'description' => $request->description,
            'at_fault' => $request->at_fault === 'true',
            'damage_amount' => $request->damage_amount
        ];
        
        $drivers[$driverIndex]['accidents'] = $accidents;
        $lead->update(['drivers' => $drivers]);
        
        return response()->json([
            'success' => true,
            'message' => 'Accident added successfully',
            'accidents' => $accidents
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to add accident: ' . $e->getMessage()
        ], 500);
    }
});

// Route to add/update vehicle
Route::post('/agent/lead/{leadId}/vehicle', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $vehicles = $lead->vehicles ?? [];
        
        $vehicleData = [
            'year' => $request->year,
            'make' => $request->make,
            'model' => $request->model,
            'vin' => $request->vin,
            'primary_use' => $request->primary_use,
            'annual_miles' => $request->annual_miles,
            'ownership' => $request->ownership,
            'garage' => $request->garage
        ];
        $vehicleIndex = $request->vehicle_index ?? count($vehicles);
        
        if ($vehicleIndex < count($vehicles)) {
            // Update existing vehicle
            $vehicles[$vehicleIndex] = array_merge($vehicles[$vehicleIndex] ?? [], $vehicleData);
        } else {
            // Add new vehicle
            $vehicles[] = $vehicleData;
        }
        
        $lead->update(['vehicles' => $vehicles]);
        
        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'vehicles' => $vehicles
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update vehicle: ' . $e->getMessage()
        ], 500);
    }
});

// Route to update insurance information
Route::put('/agent/lead/{leadId}/insurance', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        
        $insuranceData = [
            'insurance_company' => $request->insurance_company,
            'coverage_type' => $request->coverage_type,
            'expiration_date' => $request->expiration_date,
            'insured_since' => $request->insured_since
        ];
        
        $lead->update(['current_policy' => $insuranceData]);
        
        return response()->json([
            'success' => true,
            'message' => 'Insurance information updated successfully',
            'insurance' => $insuranceData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to update insurance information: ' . $e->getMessage()
        ], 500);
    }
});

// Route to save all lead data (comprehensive save)
Route::post('/agent/lead/{leadId}/save-all', function (Request $request, $leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        
        // Save qualification data
        if ($request->has('qualification')) {
            $qualificationData = $request->qualification;
            $qualificationData['lead_id'] = $leadId;
            $qualificationData['enriched_at'] = now();
            
            \App\Models\LeadQualification::updateOrCreate(
                ['lead_id' => $leadId],
                $qualificationData
            );
        }
        
        // Save contact information with Vici sync
        $viciSyncResult = null;
        if ($request->has('contact')) {
            $contactData = $request->contact;
            
            // Store original values for comparison
            $originalData = [
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'address' => $lead->address,
                'city' => $lead->city,
                'state' => $lead->state,
                'zip_code' => $lead->zip_code
            ];
            
            // Include first_name and last_name in contact updates
            $updatedData = [
                'first_name' => $contactData['first_name'] ?? $lead->first_name,
                'last_name' => $contactData['last_name'] ?? $lead->last_name,
                'phone' => $contactData['phone'],
                'email' => $contactData['email'],
                'address' => $contactData['address'],
                'city' => $contactData['city'],
                'state' => $contactData['state'],
                'zip_code' => $contactData['zip_code']
            ];
            
            $lead->update($updatedData);
            
            // Check for changes in basic fields that need Vici sync
            $basicFields = ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'state', 'zip_code'];
            $changedFields = [];
            
            foreach ($basicFields as $field) {
                if ($originalData[$field] !== $updatedData[$field]) {
                    $changedFields[$field] = [
                        'old' => $originalData[$field],
                        'new' => $updatedData[$field]
                    ];
                }
            }
            
            // Attempt Vici sync if fields changed
            if (!empty($changedFields)) {
                try {
                    $viciSyncResult = updateViciLead($leadId, $updatedData, $changedFields);
                    Log::info('Vici sync in save-all', [
                        'lead_id' => $leadId,
                        'changed_fields' => array_keys($changedFields),
                        'result' => $viciSyncResult
                    ]);
                } catch (Exception $viciError) {
                    Log::warning('Vici sync failed in save-all', [
                        'lead_id' => $leadId,
                        'error' => $viciError->getMessage()
                    ]);
                }
            }
        }
        
        // Save insurance information
        if ($request->has('insurance')) {
            $insuranceData = $request->insurance;
            $lead->update(['current_policy' => $insuranceData]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'All lead data saved successfully',
            'vici_sync' => $viciSyncResult ? 'success' : 'skipped_or_failed',
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to save all lead data', [
            'lead_id' => $leadId,
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to save lead data: ' . $e->getMessage()
        ], 500);
    }
});

// Validate lead for Allstate enrichment
Route::get('/agent/lead/{leadId}/validate-allstate', function ($leadId) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        
        // Perform Allstate validation
        $validation = \App\Services\AllstateValidationService::validateLeadForEnrichment($lead);
        $summary = \App\Services\AllstateValidationService::generateValidationSummary($validation);
        $fieldMapping = \App\Services\AllstateValidationService::getFieldMappingForFrontend();
        
        return response()->json([
            'lead_id' => $leadId,
            'validation' => $validation,
            'summary' => $summary,
            'field_mapping' => $fieldMapping,
            'required_fields' => \App\Services\AllstateValidationService::getRequiredFields()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Validation failed: ' . $e->getMessage()
        ], 500);
    }
});

// Ringba Decision Webhook - Automatic Allstate Transfer
Route::post('/webhook/ringba-decision', function (Request $request) {
    try {
        Log::info('Ringba decision webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        // Validate required fields
        $leadId = $request->input('lead_id');
        $decision = $request->input('decision');
        $ringbaData = $request->input('ringba_data', []);
        
        if (!$leadId) {
            Log::error('Ringba webhook missing lead_id', ['payload' => $request->all()]);
            return response()->json([
                'success' => false,
                'error' => 'lead_id is required'
            ], 400);
        }
        
        // Find the lead
        $lead = \App\Models\Lead::where('id', $leadId)->first();
        if (!$lead) {
            Log::error('Ringba webhook - lead not found', ['lead_id' => $leadId]);
            return response()->json([
                'success' => false,
                'error' => 'Lead not found'
            ], 404);
        }
        
        // Log the decision
        Log::info('Ringba decision processed', [
            'lead_id' => $leadId,
            'decision' => $decision,
            'lead_name' => $lead->name
        ]);
        
        // Handle Allstate decision
        if (strtolower($decision) === 'allstate') {
            // Validate lead is ready for Allstate
            $validation = \App\Services\AllstateValidationService::validateLeadForEnrichment($lead);
            
            if (!$validation['is_valid']) {
                Log::warning('Ringba selected Allstate but lead validation failed', [
                    'lead_id' => $leadId,
                    'missing_fields' => $validation['missing_fields'],
                    'errors' => $validation['errors']
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Lead validation failed for Allstate',
                    'validation_errors' => $validation['missing_fields'],
                    'message' => 'Lead does not meet Allstate requirements'
                ], 422);
            }
            
            // Lead is valid - proceed with Allstate transfer
            Log::info('Initiating automatic Allstate transfer', ['lead_id' => $leadId]);
            
            $allstateService = new \App\Services\AllstateCallTransferService();
            $transferResult = $allstateService->transferCall($lead);
            
            if ($transferResult['success']) {
                // Update lead status
                $lead->update([
                    'status' => 'transferred_to_allstate',
                    'allstate_transfer_id' => $transferResult['transfer_id'] ?? null,
                    'allstate_transferred_at' => now(),
                    'allstate_response' => $transferResult['allstate_response'] ?? null,
                    'notes' => ($lead->notes ?? '') . "\n" . 'Auto-transferred to Allstate via Ringba decision at ' . now()->toDateTimeString()
                ]);
                
                Log::info('Allstate transfer successful via Ringba webhook', [
                    'lead_id' => $leadId,
                    'transfer_id' => $transferResult['transfer_id'] ?? null,
                    'allstate_response' => $transferResult['allstate_response'] ?? null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Lead successfully transferred to Allstate',
                    'lead_id' => $leadId,
                    'decision' => $decision,
                    'transfer_result' => [
                        'transfer_id' => $transferResult['transfer_id'] ?? null,
                        'status' => 'transferred_to_allstate',
                        'transferred_at' => now()->toISOString()
                    ]
                ]);
                
            } else {
                // Transfer failed
                $lead->update([
                    'status' => 'transfer_failed',
                    'notes' => ($lead->notes ?? '') . "\n" . 'Allstate transfer failed via Ringba webhook: ' . ($transferResult['error'] ?? 'Unknown error') . ' at ' . now()->toDateTimeString()
                ]);
                
                Log::error('Allstate transfer failed via Ringba webhook', [
                    'lead_id' => $leadId,
                    'error' => $transferResult['error'] ?? 'Unknown error',
                    'response_body' => $transferResult['response_body'] ?? null
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Allstate transfer failed',
                    'lead_id' => $leadId,
                    'decision' => $decision,
                    'error' => $transferResult['error'] ?? 'Transfer failed',
                    'transfer_result' => [
                        'status' => 'transfer_failed',
                        'error' => $transferResult['error'] ?? 'Unknown error'
                    ]
                ], 500);
            }
        } else {
            // Other decisions (not Allstate)
            Log::info('Ringba decision processed - not Allstate', [
                'lead_id' => $leadId,
                'decision' => $decision
            ]);
            
            // Update lead with decision but no transfer
            $lead->update([
                'notes' => ($lead->notes ?? '') . "\n" . "Ringba decision: $decision at " . now()->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Decision processed - no transfer needed',
                'lead_id' => $leadId,
                'decision' => $decision,
                'action' => 'logged_only'
            ]);
        }
        
    } catch (\Exception $e) {
        Log::error('Ringba webhook error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Webhook processing failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test route to verify routing is working
Route::get('/api/test-quick/{period}', function ($period) {
    return response()->json([
        'success' => true,
        'message' => 'Route working correctly',
        'period' => $period,
        'timestamp' => now()->toISOString()
    ]);
});

// Call Analytics API Routes - SPECIFIC ROUTES FIRST
Route::get('/api/analytics/quick/{period}', function (Request $request, $period) {
    try {
        Log::info('Analytics quick route called', ['period' => $period, 'url' => $request->fullUrl()]);
        
        $ranges = \App\Services\CallAnalyticsService::getDateRanges();
        
        if (!isset($ranges[$period])) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid period. Available: ' . implode(', ', array_keys($ranges))
            ], 400);
        }
        
        $range = $ranges[$period];
        $filters = $request->only(['agent_id', 'campaign_id', 'buyer_name']);
        
        $analytics = \App\Services\CallAnalyticsService::getAnalytics($range['start'], $range['end'], $filters);
        
        return response()->json([
            'success' => true,
            'period' => $period,
            'period_label' => $range['label'],
            'data' => $analytics,
            'generated_at' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Analytics generation failed: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/api/analytics/date-ranges', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Services\CallAnalyticsService::getDateRanges()
    ]);
});

// Analytics Dashboard View
Route::get('/analytics', function () {
    return view('analytics.dashboard');
});

// Simple Admin Dashboard (No Authentication Required)
Route::get('/admin', function () {
    return view('admin.simple-dashboard');
});

// Generic date range route - MUST COME AFTER SPECIFIC ROUTES
Route::get('/api/analytics/{startDate}/{endDate}', function (Request $request, $startDate, $endDate) {
    try {
        $filters = $request->only(['agent_id', 'campaign_id', 'buyer_name']);
        
        $analytics = \App\Services\CallAnalyticsService::getAnalytics($startDate, $endDate, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
            'generated_at' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Analytics generation failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test data normalization for buyers
Route::get('/test/normalization/{leadId?}', function ($leadId = 'BRAIN_TEST_RINGBA') {
    try {
        $lead = \App\Models\Lead::where('id', $leadId)->first();
        
        if (!$lead) {
            return response()->json([
                'error' => 'Lead not found',
                'lead_id' => $leadId
            ], 404);
        }
        
        // Get original lead data
        $originalData = [
            'drivers' => $lead->drivers ?? [],
            'vehicles' => $lead->vehicles ?? [],
            'coverage_type' => $lead->coverage_type ?? 'Full Coverage',
            'currently_insured' => $lead->currently_insured ?? 'Yes'
        ];
        
        // Apply Allstate normalization
        $normalizedData = \App\Services\DataNormalizationService::normalizeForBuyer($originalData, 'allstate');
        
        // Get validation report
        $validationReport = \App\Services\DataNormalizationService::getValidationReport($originalData, $normalizedData, 'allstate');
        
        return response()->json([
            'lead_id' => $leadId,
            'original_data' => $originalData,
            'normalized_data' => $normalizedData,
            'validation_report' => $validationReport,
            'buyer_profile' => 'allstate',
            'message' => 'Data normalization test completed'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Normalization test failed: ' . $e->getMessage(),
            'lead_id' => $leadId
        ], 500);
    }
});

// Ringba Conversion Tracking Webhook
Route::post('/webhook/ringba-conversion', function (Request $request) {
    try {
        Log::info('Ringba conversion webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        $data = $request->all();
        
        // Required fields
        $leadId = $data['lead_id'] ?? null;
        $converted = $data['converted'] ?? $data['sale_successful'] ?? false;
        $callId = $data['call_id'] ?? $data['ringba_call_id'] ?? null;
        
        if (!$leadId) {
            Log::error('Ringba conversion webhook missing lead_id', ['payload' => $data]);
            return response()->json([
                'success' => false,
                'error' => 'lead_id is required'
            ], 400);
        }
        
        // Find the lead
        $lead = \App\Models\Lead::where('id', $leadId)->first();
        if (!$lead) {
            Log::error('Lead not found for conversion tracking', [
                'lead_id' => $leadId,
                'payload' => $data
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Lead not found'
            ], 404);
        }
        
        // Find associated Vici call metrics
        $viciMetrics = \App\Models\ViciCallMetrics::where('lead_id', $leadId)->first();
        
        // Create or update conversion record
        $conversion = \App\Models\LeadConversion::updateOrCreate(
            [
                'lead_id' => $leadId,
                'ringba_call_id' => $callId
            ],
            [
                'vici_call_metrics_id' => $viciMetrics?->id,
                'ringba_campaign_id' => $data['campaign_id'] ?? null,
                'ringba_publisher_id' => $data['publisher_id'] ?? null,
                'converted' => $converted === 'yes' || $converted === true || $converted === 1,
                'conversion_time' => $converted ? now() : null,
                'buyer_name' => $data['buyer_name'] ?? $data['buyer'] ?? null,
                'buyer_id' => $data['buyer_id'] ?? null,
                'conversion_value' => $data['conversion_value'] ?? $data['revenue'] ?? $data['call_revenue'] ?? null,
                'conversion_type' => $data['conversion_type'] ?? 'sale',
                'ringba_payload' => $data,
                'notes' => $data['notes'] ?? null
            ]
        );
        
        // Calculate timing metrics if we have Vici data
        if ($viciMetrics && $conversion->converted) {
            $conversion->calculateTimingMetrics($viciMetrics);
        }
        
        Log::info('Ringba conversion tracked successfully', [
            'lead_id' => $leadId,
            'conversion_id' => $conversion->id,
            'converted' => $conversion->converted,
            'buyer' => $conversion->buyer_name,
            'value' => $conversion->conversion_value
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Conversion tracked successfully',
            'conversion_id' => $conversion->id,
            'lead_id' => $leadId,
            'converted' => $conversion->converted,
            'buyer' => $conversion->buyer_name,
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        Log::error('Ringba conversion webhook error', [
            'error' => $e->getMessage(),
            'payload' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Conversion tracking failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test Ringba Decision Webhook
Route::get('/test/ringba-decision/{leadId?}/{decision?}', function ($leadId = 'BRAIN_TEST_RINGBA', $decision = 'allstate') {
    try {
        // Find the lead first to make sure it exists
        $lead = \App\Models\Lead::where('id', $leadId)->first();
        if (!$lead) {
            return response()->json([
                'error' => 'Lead not found for testing',
                'lead_id' => $leadId,
                'available_test_lead' => 'BRAIN_TEST_RINGBA'
            ], 404);
        }
        
        // Simulate a Ringba webhook call
        $webhookData = [
            'lead_id' => $leadId,
            'decision' => $decision,
            'ringba_data' => [
                'campaign_id' => '2674154334576444838',
                'processed_at' => now()->toISOString(),
                'score' => 85,
                'qualification_results' => [
                    'currently_insured' => 'yes',
                    'license_valid' => 'yes',
                    'credit_score' => 'good'
                ]
            ]
        ];
        
        // Make a real HTTP call to the webhook endpoint
        $webhookUrl = url('/webhook/ringba-decision');
        $response = Http::post($webhookUrl, $webhookData);
        
        return response()->json([
            'test_type' => 'Ringba Decision Webhook Simulation',
            'lead_id' => $leadId,
            'lead_name' => $lead->name,
            'decision' => $decision,
            'webhook_data_sent' => $webhookData,
            'webhook_response' => [
                'status' => $response->status(),
                'body' => $response->json(),
                'successful' => $response->successful()
            ],
            'webhook_url' => $webhookUrl,
            'instructions' => [
                'This simulates Ringba calling your webhook',
                'Check the response above to see if the transfer worked',
                'Check logs for detailed processing information',
                'Test different decisions: /test/ringba-decision/' . $leadId . '/other_decision'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'test_type' => 'Ringba Decision Webhook Simulation',
            'error' => $e->getMessage(),
            'lead_id' => $leadId,
            'decision' => $decision,
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Vici lead update function
function updateViciLead($leadId, $leadData, $changedFields) {
    // Vici API configuration
    $viciConfig = [
        'server' => env('VICI_SERVER', 'your-vici-server.com'),
        'user' => env('VICI_API_USER', 'api_user'),
        'pass' => env('VICI_API_PASS', 'api_password'),
        'list_id' => env('VICI_LIST_ID', '101'),
        'phone_code' => '1',
        'source' => 'BRAIN_UPDATE'
    ];
    
    // Check if Vici integration is properly configured
    if (!$viciConfig['server'] || $viciConfig['server'] === 'your-vici-server.com') {
        Log::info('Vici integration not configured, skipping sync', ['lead_id' => $leadId]);
        return ['status' => 'skipped', 'reason' => 'not_configured'];
    }
    
    // Prepare Vici update data - only include changed fields
    $viciUpdateData = [
        'user' => $viciConfig['user'],
        'pass' => $viciConfig['pass'],
        'function' => 'update_lead',
        'vendor_lead_code' => $leadId,  // Use Brain lead ID as vendor code
        'source_id' => $viciConfig['source']
    ];
    
    // Map Brain fields to Vici fields and only include changed ones
    $fieldMapping = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'phone' => 'phone_number',
        'email' => 'email',
        'address' => 'address1',
        'city' => 'city',
        'state' => 'state',
        'zip_code' => 'postal_code'
    ];
    
    foreach ($changedFields as $brainField => $changeInfo) {
        if (isset($fieldMapping[$brainField])) {
            $viciField = $fieldMapping[$brainField];
            $newValue = $changeInfo['new'];
            
            // Special handling for phone number
            if ($brainField === 'phone') {
                $newValue = preg_replace('/[^0-9]/', '', $newValue);
            }
            
            $viciUpdateData[$viciField] = $newValue;
        }
    }
    
    // Add comments about the update
    $viciUpdateData['comments'] = "Updated from Brain agent interface - Fields: " . implode(', ', array_keys($changedFields));
    
    Log::info('Sending Vici update request', [
        'lead_id' => $leadId,
        'vici_data' => array_merge($viciUpdateData, ['pass' => '[HIDDEN]']) // Hide password in logs
    ]);
    
    // Send to Vici API
    try {
        $response = Http::timeout(30)->post("https://{$viciConfig['server']}/vicidial/non_agent_api.php", $viciUpdateData);
        
        if ($response->successful()) {
            $responseBody = $response->body();
            Log::info('Vici update response', [
                'lead_id' => $leadId,
                'response' => $responseBody
            ]);
            
            // Parse Vici response (usually contains SUCCESS or ERROR)
            if (strpos($responseBody, 'SUCCESS') !== false) {
                return [
                    'status' => 'success',
                    'response' => $responseBody,
                    'updated_fields' => array_keys($changedFields)
                ];
            } else {
                throw new Exception("Vici API returned: " . $responseBody);
            }
        } else {
            throw new Exception("Vici API HTTP error: " . $response->status() . " - " . $response->body());
        }
    } catch (Exception $e) {
        Log::error('Vici update failed', [
            'lead_id' => $leadId,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

// Test Vici lead update endpoint
Route::get('/test/vici-update/{leadId?}', function ($leadId = 'BRAIN_TEST_VICI') {
    try {
        // Find the lead first to make sure it exists
        $lead = \App\Models\Lead::where('id', $leadId)->first();
        if (!$lead) {
            return response()->json([
                'error' => 'Lead not found for testing',
                'lead_id' => $leadId,
                'available_test_lead' => 'BRAIN_TEST_VICI'
            ], 404);
        }
        
        // Simulate field changes for testing
        $testChanges = [
            'first_name' => [
                'old' => $lead->first_name,
                'new' => 'TestUpdated'
            ],
            'phone' => [
                'old' => $lead->phone,
                'new' => '5551234567'
            ]
        ];
        
        $testData = [
            'first_name' => 'TestUpdated',
            'last_name' => $lead->last_name,
            'phone' => '5551234567',
            'email' => $lead->email,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip_code' => $lead->zip_code
        ];
        
        Log::info('Testing Vici update function', [
            'lead_id' => $leadId,
            'test_changes' => $testChanges
        ]);
        
        // Test the Vici update function
        $result = updateViciLead($leadId, $testData, $testChanges);
        
        return response()->json([
            'test_type' => 'Vici Lead Update Test',
            'lead_id' => $leadId,
            'test_changes' => $testChanges,
            'vici_result' => $result,
            'success' => true
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'test_type' => 'Vici Lead Update Test',
            'error' => $e->getMessage(),
            'lead_id' => $leadId,
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
 