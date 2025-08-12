<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Buyer;
use App\Models\Vendor;
use App\Services\ViciDialerService;
use App\Services\TwilioService;
use App\Services\AllstateService;
use App\Services\RingBAService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes - Cleaned and Optimized
|--------------------------------------------------------------------------
| Essential routes only, organized by functionality
*/

// ==================================================
// PUBLIC ROUTES
// ==================================================

Route::get('/', function () {
    return redirect('/leads');
});

Route::get('/healthz', function () {
    return response()->json(['status' => 'healthy', 'timestamp' => now()->toIso8601String()]);
});

// ==================================================
// AUTHENTICATION (if needed later)
// ==================================================

// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================================================
// MAIN WEBHOOKS - CRITICAL FOR LEAD INTAKE
// ==================================================

// Main LQF webhook endpoint
Route::post('/webhook.php', function (Request $request) {
    try {
        Log::info('LQF Webhook received', ['headers' => $request->headers->all()]);
        
        $data = $request->all();
        
        // Handle nested contact structure if present
        if (isset($data['contact'])) {
            $contact = $data['contact'];
            $data = array_merge($data, $contact);
        }
        
        // Extract and clean phone number
        $phone = preg_replace('/\D/', '', $data['phone'] ?? '');
        if (strlen($phone) == 11 && $phone[0] == '1') {
            $phone = substr($phone, 1);
        }
        
        if (strlen($phone) != 10) {
            Log::warning('Invalid phone number received', ['phone' => $data['phone'] ?? 'missing']);
            return response()->json(['error' => 'Invalid phone number'], 400);
        }
        
        // Build lead data with new vendor/buyer fields
        $leadData = [
            'source' => $data['source'] ?? 'LQF',
            'type' => $data['type'] ?? 'auto',
            'status' => 'new',
            
            // Contact information
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $phone,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? $data['address1'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? $data['zip'] ?? null,
            
            // Vendor Information (from LQF payload)
            'vendor_name' => $data['vendor'] ?? $data['vendor_name'] ?? null,
            'vendor_campaign' => $data['vendor_campaign'] ?? $data['vendor_campaign_id'] ?? null,
            'cost' => $data['cost'] ?? $data['lead_cost'] ?? null,
            
            // Buyer Information (from LQF payload)
            'buyer_name' => $data['buyer'] ?? $data['buyer_name'] ?? null,
            'buyer_campaign' => $data['buyer_campaign'] ?? $data['buyer_campaign_id'] ?? null,
            'sell_price' => $data['sell_price'] ?? $data['revenue'] ?? null,
            
            // TCPA Compliance (from LQF payload)
            'tcpa_lead_id' => $data['tcpa_lead_id'] ?? $data['lead_id'] ?? null,
            'trusted_form_cert' => $data['trusted_form_cert'] ?? $data['trusted_form_cert_url'] ?? null,
            'tcpa_compliant' => $data['tcpa_compliant'] ?? $data['tcpa'] ?? $data['meta']['tcpa_compliant'] ?? false,
            
            // Tracking and analytics
            'landing_page_url' => $data['landing_page_url'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            
            // Store full payload
            'payload' => json_encode($data),
            
            // Insurance data
            'drivers' => isset($data['drivers']) ? json_encode($data['drivers']) : null,
            'vehicles' => isset($data['vehicles']) ? json_encode($data['vehicles']) : null,
            'current_policy' => isset($data['current_policy']) ? json_encode($data['current_policy']) : null,
        ];
        
        // Auto-create vendor if doesn't exist
        if (!empty($leadData['vendor_name'])) {
            $vendor = Vendor::firstOrCreate(
                ['name' => $leadData['vendor_name']],
                ['campaigns' => [], 'active' => true]
            );
            
            if (!empty($leadData['vendor_campaign'])) {
                $vendor->addCampaign($leadData['vendor_campaign']);
            }
        }
        
        // Auto-create buyer if doesn't exist
        if (!empty($leadData['buyer_name'])) {
            $buyer = Buyer::firstOrCreate(
                ['name' => $leadData['buyer_name']],
                ['campaigns' => [], 'active' => true]
            );
            
            if (!empty($leadData['buyer_campaign'])) {
                $buyer->addCampaign($leadData['buyer_campaign']);
            }
        }
        
        // Create lead
        $lead = Lead::create($leadData);
        
        // Push to Vici
        $viciService = app(ViciDialerService::class);
        $viciResult = $viciService->pushLead($lead);
        
        Log::info('Lead created and pushed to Vici', [
            'lead_id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id,
            'vici_result' => $viciResult
        ]);
        
        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id,
            'vici_result' => $viciResult
        ]);
        
    } catch (\Exception $e) {
        Log::error('Webhook processing failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Processing failed'], 500);
    }
});

// Home insurance webhook
Route::post('/webhook/home', function (Request $request) {
    $request->merge(['type' => 'home']);
    return app()->call('App\Http\Controllers\WebhookController@handleLQF', [$request]);
});

// Auto insurance webhook
Route::post('/webhook/auto', function (Request $request) {
    $request->merge(['type' => 'auto']);
    return app()->call('App\Http\Controllers\WebhookController@handleLQF', [$request]);
});

// ==================================================
// VICI WEBHOOKS
// ==================================================

Route::post('/webhook/vici/call-status', 'App\Http\Controllers\ViciCallWebhookController@handleCallStatus');
Route::post('/webhook/vici/disposition', 'App\Http\Controllers\ViciCallWebhookController@handleDisposition');
Route::post('/webhook/vici/realtime', 'App\Http\Controllers\ViciCallWebhookController@handleRealTimeEvent');

// ==================================================
// LEAD MANAGEMENT
// ==================================================

// Lead listing page
Route::get('/leads', function (Request $request) {
    $query = Lead::query();
    
    // Search functionality
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('phone', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('first_name', 'like', "%$search%")
              ->orWhere('last_name', 'like', "%$search%")
              ->orWhere('external_lead_id', 'like', "%$search%");
        });
    }
    
    // Filters
    if ($request->has('source') && $request->source != '') {
        $query->where('source', $request->source);
    }
    if ($request->has('type') && $request->type != '') {
        $query->where('type', $request->type);
    }
    if ($request->has('status') && $request->status != '') {
        $query->where('status', $request->status);
    }
    
    // Date range
    if ($request->has('date_from')) {
        $query->where('created_at', '>=', $request->date_from);
    }
    if ($request->has('date_to')) {
        $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
    }
    
    // Order and paginate
    $leads = $query->orderBy('created_at', 'desc')
                   ->with(['viciCallMetrics'])
                   ->paginate($request->per_page ?? 50);
    
    // Get stats
    $stats = [
        'total' => Lead::where('source', '!=', 'test')->count(),
        'today' => Lead::whereDate('created_at', today())->where('source', '!=', 'test')->count(),
        'week' => Lead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('source', '!=', 'test')->count(),
        'month' => Lead::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('source', '!=', 'test')->count(),
    ];
    
    return view('leads.index', compact('leads', 'stats'));
});

// Single lead display
Route::get('/leads/{id}', function ($id) {
    $lead = Lead::with(['viciCallMetrics', 'callHistory'])->findOrFail($id);
    return view('leads.show', compact('lead'));
});

// Agent lead display (iframe)
Route::get('/agent/lead/{external_lead_id}', function ($external_lead_id) {
    $lead = Lead::where('external_lead_id', $external_lead_id)->firstOrFail();
    return view('agent.lead-display', compact('lead'));
});

// ==================================================
// ADMIN ROUTES
// ==================================================

// API Directory
Route::get('/api-directory', function () {
    $endpoints = DB::table('api_endpoints')->get();
    return view('admin.api-directory', compact('endpoints'));
});

// Diagnostics Dashboard
Route::get('/diagnostics', function () {
    return view('diagnostics.index');
});

// Buyer Management
Route::get('/admin/buyers', function () {
    $buyers = Buyer::withCount('leads')->get();
    return view('admin.buyer-management', compact('buyers'));
});

// Vendor Management
Route::get('/admin/vendors', function () {
    $vendors = Vendor::withCount('leads')->get();
    return view('admin.vendor-management', compact('vendors'));
});

// ==================================================
// SURAJ CSV UPLOAD
// ==================================================

Route::get('/suraj/upload', function () {
    return view('suraj.upload-portal');
});

Route::post('/suraj/upload', function (Request $request) {
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt|max:10240'
    ]);
    
    $file = $request->file('csv_file');
    $tempPath = storage_path('app/suraj_uploads');
    
    if (!file_exists($tempPath)) {
        mkdir($tempPath, 0755, true);
    }
    
    $filename = date('Ymd_His') . '_' . $file->getClientOriginalName();
    $file->move($tempPath, $filename);
    
    // Process the file
    $command = "cd " . base_path() . " && php artisan suraj:import-daily " . 
               escapeshellarg($tempPath . '/' . $filename) . " --push-to-vici 2>&1";
    
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        return back()->with('success', 'File uploaded and processed successfully!');
    } else {
        return back()->with('error', 'File upload failed: ' . implode("\n", $output));
    }
});

// ==================================================
// API ENDPOINTS
// ==================================================

// Get lead by ID (API)
Route::get('/api/leads/{id}', function ($id) {
    $lead = Lead::find($id);
    if (!$lead) {
        return response()->json(['error' => 'Lead not found'], 404);
    }
    return response()->json($lead);
});

// Update lead (API)
Route::post('/api/leads/{id}', function (Request $request, $id) {
    $lead = Lead::find($id);
    if (!$lead) {
        return response()->json(['error' => 'Lead not found'], 404);
    }
    
    $lead->update($request->all());
    
    // Also update in Vici if needed
    $viciService = app(ViciDialerService::class);
    $viciService->updateLead($lead);
    
    return response()->json(['success' => true, 'lead' => $lead]);
});

// ==================================================
// UTILITY ROUTES (KEEP FOR MAINTENANCE)
// ==================================================

// Database connection test (useful for debugging)
Route::get('/db-test', function() {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'connected', 'database' => DB::connection()->getDatabaseName()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// Clear cache (needed for Render deployments)
Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return response()->json(['message' => 'Cache cleared successfully']);
});

// ==================================================
// FALLBACK ROUTE
// ==================================================

Route::fallback(function () {
    return response()->json(['error' => 'Route not found'], 404);
});
