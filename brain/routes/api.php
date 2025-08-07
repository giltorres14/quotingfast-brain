<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - NO CSRF PROTECTION
|--------------------------------------------------------------------------
|
| These routes are completely free from CSRF protection.
| Perfect for webhooks and external API calls.
|
*/

// WORKING WEBHOOK - NO CSRF ON API ROUTES
Route::post('/webhook', function (Request $request) {
    try {
        $data = $request->all();
        
        \Log::info('✅ API WEBHOOK RECEIVED', [
            'data' => $data,
            'ip' => $request->ip()
        ]);
        
        // Generate external lead ID
        $data['external_lead_id'] = \App\Models\Lead::generateExternalLeadId();
        $data['source'] = $data['source'] ?? 'api-webhook';
        
        // Create the lead
        $lead = \App\Models\Lead::create($data);
        
        \Log::info('✅ Lead created via API webhook', [
            'id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Lead received and saved',
            'lead_id' => $lead->external_lead_id,
            'timestamp' => now()->toIso8601String()
        ], 200);
        
    } catch (\Exception $e) {
        \Log::error('API webhook error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Still return 200 to prevent retries
        return response()->json([
            'success' => false,
            'message' => 'Error acknowledged',
            'error' => $e->getMessage()
        ], 200);
    }
});

// Test endpoint
Route::get('/webhook', function () {
    return response()->json([
        'status' => 'ready',
        'message' => 'API webhook is ready to receive leads',
        'url' => 'POST https://quotingfast-brain-ohio.onrender.com/api/webhook',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String()
    ]);
});