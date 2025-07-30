<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

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
            '/webhook.php' => 'LeadsQuotingFast webhook (POST)'
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

// LeadsQuotingFast webhook endpoint
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
}); 

 