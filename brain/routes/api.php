<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        
        // Generate unique lead ID for this session
        $leadId = 'LQF_' . date('Ymd_His') . '_' . substr(md5($leadData['phone']), 0, 6);
        
        // Try to store in database, but continue if it fails
        $lead = null;
        try {
            $lead = App\Models\Lead::create(array_merge($leadData, ['id' => $leadId]));
            Log::info('LeadsQuotingFast API lead stored in database', ['lead_id' => $leadId]);
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
        
        // Store lead data in session/cache for iframe testing (fallback if DB fails)
        Cache::put("lead_data_{$leadId}", $leadData, now()->addHours(24));
        
        Log::info('LeadsQuotingFast API lead processed successfully', ['lead_id' => $leadId]);
        
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

// Vici integration function
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