<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
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
        
        // Create lead using Eloquent model
        $lead = Lead::create($leadData);
        
        Log::info('LeadsQuotingFast lead stored in admin panel successfully', ['lead_id' => $lead->id]);
        
        // TODO: Trigger SMS via Parcelvoy when lead is received
        // $this->triggerSMS($lead);
        
        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Lead received and stored successfully',
            'lead_id' => $lead->id,
            'name' => $leadData['name'],
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

 