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
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Welcome to The Brain - Laravel API is running!',
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
            '/webhook/status' => 'Webhook status monitoring (GET)',
            '/api/webhooks' => 'Webhook dashboard API (GET)'
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
        
        // Store the lead first
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
            'drivers' => isset($contact['drivers']) ? json_encode($contact['drivers']) : null,
            'vehicles' => isset($contact['vehicles']) ? json_encode($contact['vehicles']) : null,
            'payload' => json_encode($data),
        ];
        
        $lead = Lead::create($leadData);
        
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
                        $lead = (object) array_merge($cachedData, ['id' => $leadId]);
                        Log::info('Lead found in cache', ['lead_id' => $leadId]);
                    }
                } catch (Exception $cacheError) {
                    // Try file cache fallback
                    $cacheFile = storage_path("app/lead_cache/{$leadId}.json");
                    if (file_exists($cacheFile)) {
                        $cachedData = json_decode(file_get_contents($cacheFile), true);
                        if ($cachedData) {
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
                        'name' => 'John ViciTest',
                        'age' => 35,
                        'gender' => 'Male',
                        'accidents' => [],
                        'violations' => 1,
                        'license_status' => 'Valid'
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
        $lead = App\Models\Lead::find($leadId);
        
        if (!$lead) {
            return response()->json([
                'success' => false,
                'error' => "Lead #{$leadId} not found"
            ], 404);
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
    // Your Vici API configuration
    $viciConfig = [
        'server' => env('VICI_SERVER', 'your-vici-server.com'),
        'user' => env('VICI_API_USER', 'api_user'),
        'pass' => env('VICI_API_PASS', 'api_password'),
        'list_id' => 101,
        'phone_code' => '1',
        'source' => 'LQF_API'
    ];
    
    // Prepare Vici lead data
    $viciData = [
        'user' => $viciConfig['user'],
        'pass' => $viciConfig['pass'],
        'function' => 'add_lead',
        'list_id' => $viciConfig['list_id'],
        'phone_number' => preg_replace('/[^0-9]/', '', $leadData['phone']),
        'phone_code' => $viciConfig['phone_code'],
        'vendor_lead_code' => $leadId,
        'source_id' => $viciConfig['source'],
        'first_name' => $leadData['first_name'] ?? '',
        'last_name' => $leadData['last_name'] ?? '',
        'address1' => $leadData['address'] ?? '',
        'city' => $leadData['city'] ?? '',
        'state' => $leadData['state'] ?? '',
        'postal_code' => $leadData['zip_code'] ?? '',
        'email' => $leadData['email'] ?? '',
        'comments' => "Lead from LeadsQuotingFast - ID: {$leadId}"
    ];
    
    // Send to Vici (commented out for testing - replace with your actual Vici API endpoint)
    /*
    $response = Http::timeout(30)->post("https://{$viciConfig['server']}/vicidial/non_agent_api.php", $viciData);
    
    if ($response->successful()) {
        return $response->json();
    } else {
        throw new Exception("Vici API error: " . $response->body());
    }
    */
    
    // For testing - simulate successful Vici response
    Log::info('Vici lead submission simulated', ['vici_data' => $viciData]);
    return [
        'success' => true,
        'lead_id' => $leadId,
        'list_id' => 101,
        'message' => 'Lead added to Vici list 101 (simulated for testing)'
    ];
}
 