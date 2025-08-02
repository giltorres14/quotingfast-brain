<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Exception;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LeadsQuotingFast webhook endpoint - FULL IMPLEMENTATION
Route::post('/webhook/leadsquotingfast', function (Request $request) {
    try {
        // Log the incoming request
        Log::info('LeadsQuotingFast API webhook received', [
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
        $lead = App\Models\Lead::create($leadData);
        
        Log::info('LeadsQuotingFast API lead stored successfully', ['lead_id' => $lead->id]);
        
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
        Log::error('LeadsQuotingFast API webhook error', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 400);
    }
});

// Test endpoint to verify API is working
Route::get('/webhook/leadsquotingfast', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'LeadsQuotingFast webhook endpoint is active',
        'methods_supported' => ['POST'],
        'timestamp' => now()->toISOString()
    ], 200);
});