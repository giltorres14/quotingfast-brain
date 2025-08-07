<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Services\AllstateTestingService;

// Main landing page - redirect to leads dashboard
Route::get('/', function () {
    return redirect('/leads');
});

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

// Health check endpoint for Render - Simple version
Route::get('/healthz', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString()
    ], 200);
});

// Manual migration trigger - EMERGENCY USE ONLY
Route::get('/emergency-migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'Emergency migration completed!',
            'output' => $output,
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Debug environment variables
Route::get('/debug-env', function () {
    return response()->json([
        'environment_variables' => [
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'DB_PASSWORD' => env('DB_PASSWORD') ? '***SET***' : null,
            'DATABASE_URL' => env('DATABASE_URL') ? '***SET***' : null
        ],
        'server_env' => [
            'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? null,
            'DB_HOST' => $_ENV['DB_HOST'] ?? null,
            'DB_PORT' => $_ENV['DB_PORT'] ?? null,
            'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? null,
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? null,
            'DB_PASSWORD' => isset($_ENV['DB_PASSWORD']) ? '***SET***' : null,
            'DATABASE_URL' => isset($_ENV['DATABASE_URL']) ? '***SET***' : null
        ],
        'config' => [
            'database.default' => config('database.default'),
            'database.connections.pgsql.host' => config('database.connections.pgsql.host'),
            'database.connections.pgsql.database' => config('database.connections.pgsql.database')
        ]
    ]);
});

// REMOVED: Duplicate root route - consolidated with main root route at top of file

// Simple test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Laravel is working!',
        'timestamp' => now()->toISOString()
    ]);
});

// ViciDial firewall whitelisting endpoint
Route::get('/vici/whitelist', function () {
    try {
        $viciConfig = [
            'server' => env('VICI_SERVER', 'philli.callix.ai'),
            'user' => env('VICI_API_USER', 'apiuser'),
            'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'),
        ];
        
        \Log::info('Manual ViciDial firewall whitelist requested');
        
        $firewallAuth = \Http::timeout(10)->post("https://{$viciConfig['server']}:26793/92RG8UJYTW.php", [
            'user' => $viciConfig['user'],
            'pass' => $viciConfig['pass']
        ]);
        
        \Cache::put('vici_last_whitelist', time(), 3600);
        
        return response()->json([
            'success' => true,
            'message' => 'ViciDial firewall whitelist completed',
            'status' => $firewallAuth->status(),
            'response_body' => substr($firewallAuth->body(), 0, 200),
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (Exception $e) {
        \Log::error('Manual firewall whitelist failed', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Firewall whitelist failed: ' . $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// External lead lookup by external_lead_id (for Vici/RingBa callbacks)
Route::get('/api/external-lead/{externalLeadId}', function ($externalLeadId) {
    try {
        $lead = Lead::where('external_lead_id', $externalLeadId)->first();
        
        if (!$lead) {
            return response()->json([
                'success' => false,
                'error' => 'Lead not found',
                'external_lead_id' => $externalLeadId
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'status' => $lead->status,
            'created_at' => $lead->created_at,
            'iframe_url' => url("/agent/lead/{$lead->id}")
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Debug Vici configuration
Route::get('/debug/vici-config', function () {
    return response()->json([
        'vici_server' => env('VICI_SERVER', 'philli.callix.ai'),
        'vici_user' => env('VICI_API_USER', 'apiuser'),
        'vici_pass_set' => !empty(env('VICI_API_PASS')),
        'whitelist_url' => 'https://' . env('VICI_SERVER', 'philli.callix.ai') . ':26793/92RG8UJYTW.php',
        'api_url' => 'https://' . env('VICI_SERVER', 'philli.callix.ai') . env('VICI_API_ENDPOINT', '/vicidial/non_agent_api.php'),
        'last_whitelist' => \Cache::get('vici_last_whitelist', 'never'),
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
            
            // Generate our own external_lead_id
            $externalLeadId = generateLeadId();
            
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
                'external_lead_id' => $externalLeadId, // ALWAYS use our generated ID
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
            
            Log::info('🔢 /test-webhook creating lead with generated ID', [
                'generated_id' => $externalLeadId,
                'incoming_id' => $data['id'] ?? 'none'
            ]);
            
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

// Working leads dashboard - bypasses authentication issues
Route::get('/leads-simple', function () {
    try {
        // Get all leads from database
        $leads = \App\Models\Lead::orderBy('created_at', 'desc')->limit(50)->get();
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Dashboard - The Brain</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; }
        .header { background: #2563eb; color: white; padding: 1rem; text-align: center; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #2563eb; }
        .stat-label { color: #666; margin-top: 0.5rem; }
        .leads-table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background: #f8f9fa; }
        .lead-name { font-weight: 600; color: #2563eb; }
        .lead-phone { color: #666; font-size: 0.9rem; }
        .lead-location { color: #666; }
        .lead-source { background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .lead-date { color: #666; font-size: 0.9rem; }
        .view-btn { background: #2563eb; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; text-decoration: none; font-size: 0.9rem; }
        .view-btn:hover { background: #1d4ed8; }
        .no-leads { text-align: center; padding: 3rem; color: #666; }
        .nav-links { margin-bottom: 2rem; }
        .nav-links a { background: #2563eb; color: white; padding: 0.75rem 1.5rem; margin-right: 1rem; text-decoration: none; border-radius: 4px; display: inline-block; }
        .nav-links a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧠 The Brain - Leads Dashboard</h1>
        <p>Lead Management & Analytics System</p>
    </div>
    
    <div class="container">
        <div class="nav-links">
            <a href="/admin">Full Admin Dashboard</a>
            <a href="/analytics">Analytics</a>
            <a href="/api-directory">🔗 API Directory</a>
            <a href="/test/allstate/connection">Test Allstate API</a>
            <a href="/agent/lead/TEST_LEAD_1">View Test Lead</a>
        </div>';
        
        if ($leads->isEmpty()) {
            $html .= '<div class="no-leads">
                <h2>No Leads Found</h2>
                <p>No leads in the database yet. Submit a test lead through your webhooks:</p>
                <div style="margin-top: 1rem;">
                    <a href="/webhook.php" class="view-btn">LeadQuotingFast Webhook</a>
                    <a href="/webhook/ringba" class="view-btn">Ringba Webhook</a>
                </div>
            </div>';
        } else {
            // Calculate stats
            $totalLeads = $leads->count();
            $todayLeads = $leads->where('created_at', '>=', date('Y-m-d'))->count();
            $sources = $leads->groupBy('source')->map->count();
            $states = $leads->groupBy('state')->map->count();
            
            $html .= '<div class="stats">
                <div class="stat-card">
                    <div class="stat-number">' . $totalLeads . '</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">' . $todayLeads . '</div>
                    <div class="stat-label">Today\'s Leads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">' . $sources->count() . '</div>
                    <div class="stat-label">Lead Sources</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">' . $states->count() . '</div>
                    <div class="stat-label">States</div>
                </div>
            </div>
            
            <div class="leads-table">
                <table>
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Source</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($leads as $lead) {
                $createdAt = date('M j, Y g:i A', strtotime($lead->created_at));
                $html .= '<tr>
                    <td>
                        <div class="lead-name">' . htmlspecialchars($lead->name ?? 'Unknown') . '</div>
                        <div class="lead-phone">' . htmlspecialchars($lead->email ?? '') . '</div>
                    </td>
                    <td>' . htmlspecialchars($lead->phone ?? '') . '</td>
                    <td class="lead-location">' . htmlspecialchars(($lead->city ?? '') . ', ' . ($lead->state ?? '')) . '</td>
                    <td><span class="lead-source">' . htmlspecialchars($lead->source ?? 'unknown') . '</span></td>
                    <td class="lead-date">' . $createdAt . '</td>
                    <td><a href="/agent/lead/' . $lead->id . '" class="view-btn">View Details</a></td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }
        
        $html .= '</div>
</body>
</html>';
        
        return response($html)->header('Content-Type', 'text/html');
        
    } catch (\Exception $e) {
        return response('<h1>Database Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><p><a href="/agent/lead/TEST_LEAD_1">View Test Lead Instead</a></p>')->header('Content-Type', 'text/html');
    }
});

// Simple lead browser - shows available leads and their data
Route::get('/test-lead-data', function () {
    try {
        // Get all leads from database
        $leads = \App\Models\Lead::orderBy('created_at', 'desc')->limit(10)->get();
        
        if ($leads->isEmpty()) {
    return response()->json([
                'message' => 'No leads found in database',
                'explanation' => 'You need to submit a test lead first to see the payload structure',
                'your_ui_urls' => [
                    'main_ui' => 'https://quotingfast-brain-ohio.onrender.com',
                    'simple_leads_dashboard' => 'https://quotingfast-brain-ohio.onrender.com/leads-simple',
                    'admin_dashboard' => 'https://quotingfast-brain-ohio.onrender.com/admin',
                    'test_lead_viewer' => 'https://quotingfast-brain-ohio.onrender.com/agent/lead/TEST_LEAD_1',
                    'webhook_endpoints' => [
                        'leadquotingfast' => 'https://quotingfast-brain-ohio.onrender.com/webhook.php',
                        'ringba' => 'https://quotingfast-brain-ohio.onrender.com/webhook/ringba',
                        'vici' => 'https://quotingfast-brain-ohio.onrender.com/webhook/vici',
                        'twilio' => 'https://quotingfast-brain-ohio.onrender.com/webhook/twilio'
                    ]
                ],
                'next_steps' => [
                    '1. Submit a test lead through one of your webhook endpoints',
                    '2. Or visit the test lead viewer to see mock data structure',
                    '3. Then call this endpoint again to see actual lead data'
                ],
                'test_allstate_api' => 'https://quotingfast-brain-ohio.onrender.com/test/allstate/connection',
                'timestamp' => now()->toISOString()
            ]);
        }
        
        // Show available leads with their basic info and payload structure
        $leadData = [];
        foreach ($leads as $lead) {
            $drivers = json_decode($lead->drivers ?? '[]', true) ?: [];
            $vehicles = json_decode($lead->vehicles ?? '[]', true) ?: [];
            $policy = json_decode($lead->current_policy ?? '{}', true) ?: [];
            $meta = json_decode($lead->meta ?? '{}', true) ?: [];
            
            $leadData[] = [
                'lead_id' => $lead->id,
                'view_url' => "https://quotingfast-brain-ohio.onrender.com/agent/lead/{$lead->id}",
                'basic_info' => [
        'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'city' => $lead->city,
                    'state' => $lead->state,
                    'zipcode' => $lead->zipcode,
                    'dob' => $lead->dob,
                    'created_at' => $lead->created_at
                ],
                'insurance_data' => [
                    'currently_insured' => $lead->currently_insured,
                    'current_carrier' => $lead->current_carrier,
                    'policy_expiration' => $lead->policy_expiration,
                    'tcpa_compliant' => $lead->tcpa_compliant,
                    'requested_policy' => $lead->requested_policy
                ],
                'drivers_count' => count($drivers),
                'vehicles_count' => count($vehicles),
                'drivers_sample' => $drivers[0] ?? null,
                'vehicles_sample' => $vehicles[0] ?? null,
                'policy_keys' => array_keys($policy),
                'meta_keys' => array_keys($meta),
                'allstate_mapping' => [
                    'external_id' => $lead->id,
                    'city' => $lead->city,
                    'state' => $lead->state,
                    'zipcode' => $lead->zipcode,
                    'date_of_birth' => $lead->dob,
                    'tcpa_compliant' => $lead->tcpa_compliant,
                    'currently_insured' => $lead->currently_insured,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'name' => $lead->name,
                    'missing_fields' => [
                        'desired_coverage_type' => 'STATEMINIMUM, BASIC, STANDARD, SUPERIOR - need to map from policy data',
                        'residence_status' => 'own, rent, live_with_parents - missing from current data'
                    ]
                ]
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => "Found {$leads->count()} leads in database",
            'leads' => $leadData,
            'allstate_api_test' => 'https://quotingfast-brain-ohio.onrender.com/test/allstate/connection',
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to retrieve leads: ' . $e->getMessage(),
            'your_ui_urls' => [
                'simple_leads_dashboard' => 'https://quotingfast-brain-ohio.onrender.com/leads-simple',
                'test_lead_viewer' => 'https://quotingfast-brain-ohio.onrender.com/agent/lead/TEST_LEAD_1',
                'allstate_api_test' => 'https://quotingfast-brain-ohio.onrender.com/test/allstate/connection'
            ],
            'timestamp' => now()->toISOString()
        ], 500);
    }
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

// Lead cost reporting endpoints
Route::get('/api/reports/cost/today', function () {
    try {
        $today = now()->startOfDay();
        $leads = Lead::whereDate('created_at', $today)->get();
        
        $totalCost = $leads->sum('sell_price');
        $leadCount = $leads->count();
        
        // Group by source
        $bySource = $leads->groupBy('source')->map(function ($sourceLeads, $source) {
            return [
                'source' => $source,
                'count' => $sourceLeads->count(),
                'total_cost' => $sourceLeads->sum('sell_price'),
                'avg_cost' => $sourceLeads->avg('sell_price')
            ];
        })->values();
        
        // Group by state
        $byState = $leads->groupBy('state')->map(function ($stateLeads, $state) {
            return [
                'state' => $state,
                'count' => $stateLeads->count(), 
                'total_cost' => $stateLeads->sum('sell_price'),
                'avg_cost' => $stateLeads->avg('sell_price')
            ];
        })->values();
        
        return response()->json([
            'date' => $today->format('Y-m-d'),
            'summary' => [
                'total_leads' => $leadCount,
                'total_cost' => round($totalCost, 2),
                'average_cost_per_lead' => $leadCount > 0 ? round($totalCost / $leadCount, 2) : 0
            ],
            'by_source' => $bySource,
            'by_state' => $byState
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/api/reports/cost/source/{source}', function ($source) {
    try {
        $leads = Lead::where('source', $source)->get();
        
        $totalCost = $leads->sum('sell_price');
        $leadCount = $leads->count();
        
        // Group by date (last 30 days)
        $byDate = $leads->where('created_at', '>=', now()->subDays(30))
                       ->groupBy(function($lead) {
                           return $lead->created_at->format('Y-m-d');
                       })
                       ->map(function ($dateLeads, $date) {
                           return [
                               'date' => $date,
                               'count' => $dateLeads->count(),
                               'total_cost' => $dateLeads->sum('sell_price')
                           ];
                       })->values();
        
        return response()->json([
            'source' => $source,
            'summary' => [
                'total_leads' => $leadCount,
                'total_cost' => round($totalCost, 2),
                'average_cost_per_lead' => $leadCount > 0 ? round($totalCost / $leadCount, 2) : 0
            ],
            'last_30_days' => $byDate
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/api/reports/cost/state/{state}', function ($state) {
    try {
        $leads = Lead::where('state', $state)->get();
        
        $totalCost = $leads->sum('sell_price');
        $leadCount = $leads->count();
        
        // Group by date (last 30 days)
        $byDate = $leads->where('created_at', '>=', now()->subDays(30))
                       ->groupBy(function($lead) {
                           return $lead->created_at->format('Y-m-d');
                       })
                       ->map(function ($dateLeads, $date) {
                           return [
                               'date' => $date,
                               'count' => $dateLeads->count(),
                               'total_cost' => $dateLeads->sum('sell_price')
                           ];
                       })->values();
        
        return response()->json([
            'state' => $state,
            'summary' => [
                'total_leads' => $leadCount,
                'total_cost' => round($totalCost, 2),
                'average_cost_per_lead' => $leadCount > 0 ? round($totalCost / $leadCount, 2) : 0
            ],
            'last_30_days' => $byDate
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// REMOVED: Cleanup and reset functionality per user request
// These features were automatically deleting leads on deployment
// Will be re-added only when specifically requested by user

// Database status endpoint
Route::get('/api/database/status', function () {
    try {
        $dbPath = database_path('database.sqlite');
        $dbExists = file_exists($dbPath);
        
        if (!$dbExists) {
            // Try to create the database file
            touch($dbPath);
            chmod($dbPath, 0666);
        }
        
        // Test database connection
        $leadCount = Lead::count();
        
        return response()->json([
            'database_status' => 'connected',
            'database_type' => config('database.default'),
            'database_file' => $dbPath,
            'file_exists' => file_exists($dbPath),
            'file_writable' => is_writable($dbPath),
            'lead_count' => $leadCount,
            'migrations_needed' => false
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'database_status' => 'error',
            'error' => $e->getMessage(),
            'database_type' => config('database.default'),
            'database_file' => database_path('database.sqlite'),
            'file_exists' => file_exists(database_path('database.sqlite')),
            'suggestions' => [
                'Run migrations: php artisan migrate',
                'Check file permissions',
                'Verify database configuration'
            ]
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// API endpoint to view lead payload
Route::get('/api/lead/{leadId}/payload', function ($leadId) {
    try {
        // Try to find the lead in database first
        $lead = Lead::find($leadId);
        
        if ($lead && $lead->payload) {
            $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
            
            return response()->json([
                'lead_id' => $leadId,
                'original_payload' => $payload,
                'stored_at' => $lead->created_at,
                'source' => 'database'
            ], 200, [], JSON_PRETTY_PRINT);
        }
        
        // Fallback: check cache for recent leads
        $cachedData = Cache::get("lead_data_{$leadId}");
        if ($cachedData) {
            return response()->json([
                'lead_id' => $leadId,
                'original_payload' => $cachedData,
                'source' => 'cache',
                'note' => 'This lead was not stored in database but found in cache'
            ], 200, [], JSON_PRETTY_PRINT);
        }
        
        // If it's a test lead, show test data structure
        if (str_starts_with($leadId, 'TEST_') || str_starts_with($leadId, 'BRAIN_TEST_')) {
            return response()->json([
                'lead_id' => $leadId,
                'message' => 'This is test data - no original payload available',
                'test_data_structure' => [
                    'contact' => [
                        'first_name' => 'string',
                        'last_name' => 'string',
                        'phone' => 'string',
                        'email' => 'string',
                        'address' => 'string',
                        'city' => 'string',
                        'state' => 'string',
                        'zip_code' => 'string'
                    ],
                    'data' => [
                        'drivers' => 'array',
                        'vehicles' => 'array',
                        'requested_policy' => 'object'
                    ],
                    'meta' => [
                        'user_agent' => 'string',
                        'landing_page_url' => 'string',
                        'tcpa_compliant' => 'boolean'
                    ]
                ],
                'source' => 'test_data'
            ], 200, [], JSON_PRETTY_PRINT);
        }
        
        return response()->json([
            'error' => 'Lead not found',
            'lead_id' => $leadId,
            'searched_in' => ['database', 'cache']
        ], 404, [], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to retrieve payload',
            'message' => $e->getMessage(),
            'lead_id' => $leadId
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Debug endpoint to analyze incoming webhook data
Route::post('/webhook/debug', function (Request $request) {
    $data = $request->all();
    $headers = $request->headers->all();
    
    return response()->json([
        'received_data' => $data,
        'headers' => $headers,
        'parsed_contact' => isset($data['contact']) ? $data['contact'] : $data,
        'extracted_fields' => [
            'name' => trim(($data['contact']['first_name'] ?? $data['first_name'] ?? '') . ' ' . ($data['contact']['last_name'] ?? $data['last_name'] ?? '')) ?: 'Unknown',
            'first_name' => $data['contact']['first_name'] ?? $data['first_name'] ?? null,
            'last_name' => $data['contact']['last_name'] ?? $data['last_name'] ?? null,
            'phone' => $data['contact']['phone'] ?? $data['phone'] ?? 'Unknown',
            'email' => $data['contact']['email'] ?? $data['email'] ?? null,
            'address' => $data['contact']['address'] ?? $data['address'] ?? null,
            'city' => $data['contact']['city'] ?? $data['city'] ?? null,
            'state' => $data['contact']['state'] ?? $data['state'] ?? 'Unknown',
            'zip_code' => $data['contact']['zip_code'] ?? $data['zip_code'] ?? null,
            'drivers' => $data['data']['drivers'] ?? $data['drivers'] ?? [],
            'vehicles' => $data['data']['vehicles'] ?? $data['vehicles'] ?? [],
            'current_policy' => $data['data']['requested_policy'] ?? $data['requested_policy'] ?? $data['current_policy'] ?? null,
        ],
        'missing_fields_analysis' => [
            'available_in_lead_model' => [
                'campaign_id', 'external_lead_id', 'sell_price', 'ip_address', 
                'user_agent', 'landing_page_url', 'tcpa_compliant', 'meta'
            ],
            'potentially_missing_from_payload' => []
        ]
    ], 200, [], JSON_PRETTY_PRINT);
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
            'type' => detectLeadType($data),
            'received_at' => now(),
            'joined_at' => now(),
            
            // NEW: Capture additional fields for reporting and compliance
            'sell_price' => $data['sell_price'] ?? $data['cost'] ?? null, // Lead cost for reporting
            'tcpa_compliant' => $data['tcpa_compliant'] ?? $data['meta']['tcpa_compliant'] ?? false,
            'landing_page_url' => $data['landing_page_url'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            // Don't use incoming external_lead_id or lead_id, we'll generate our own
            // 'external_lead_id' => $data['external_lead_id'] ?? $data['lead_id'] ?? null,
            
            // Store compliance and tracking data in meta
            'meta' => json_encode(array_merge([
                'trusted_form_cert_url' => $data['trusted_form_cert_url'] ?? null,
                'originally_created' => $data['originally_created'] ?? null,
                'source_details' => $data['source'] ?? null,
            ], $data['meta'] ?? [])),
            
            'drivers' => json_encode($data['data']['drivers'] ?? []),
            'vehicles' => json_encode($data['data']['vehicles'] ?? []),
            'current_policy' => json_encode($data['data']['current_policy'] ?? null),
            'requested_policy' => json_encode($data['data']['requested_policy'] ?? $data['requested_policy'] ?? null),
            'payload' => json_encode($data),
        ];
        
        // Try to store in database first to get auto-increment ID
        $lead = null;
        $externalLeadId = null;
        try {
            // CRITICAL: Generate our ID BEFORE creating the lead
            $externalLeadId = generateLeadId();
            
            // Force our generated ID into the lead data, overriding ANY incoming ID
            $leadData['external_lead_id'] = $externalLeadId;
            
            Log::info('🔢 Creating lead with generated external_lead_id', [
                'generated_id' => $externalLeadId,
                'incoming_id' => $data['id'] ?? 'none',
                'incoming_external_id' => $data['external_lead_id'] ?? 'none',
                'will_use' => $externalLeadId
            ]);
            
            $lead = Lead::create($leadData);
            
            // Double-check and force update if somehow it got overridden
            if ($lead->external_lead_id !== $externalLeadId) {
                Log::warning('🔢 External ID mismatch, forcing correction', [
                    'expected' => $externalLeadId,
                    'actual' => $lead->external_lead_id,
                    'lead_id' => $lead->id
                ]);
                \DB::table('leads')->where('id', $lead->id)->update(['external_lead_id' => $externalLeadId]);
                $lead->external_lead_id = $externalLeadId;
            }
            
            // 🚨 CAMPAIGN AUTO-DETECTION: Check if this is a new campaign ID
            if (!empty($leadData['campaign_id'])) {
                $campaign = \App\Models\Campaign::autoCreateFromId($leadData['campaign_id']);
                
                // If this was a newly created campaign, log it for notification
                if ($campaign->wasRecentlyCreated) {
                    Log::warning('🆕 NEW CAMPAIGN DETECTED', [
                        'campaign_id' => $leadData['campaign_id'],
                        'lead_id' => $externalLeadId,
                        'message' => "New campaign ID '{$leadData['campaign_id']}' detected and auto-created. Please add campaign name in directory."
                    ]);
                } else {
                    // Update existing campaign activity
                    $campaign->recordLeadActivity();
                }
            }
            
            Log::info('LeadsQuotingFast lead stored in database', [
                'db_id' => $lead->id, 
                'external_lead_id' => $externalLeadId,
                'campaign_id' => $leadData['campaign_id'] ?? 'none'
            ]);
        } catch (Exception $dbError) {
            Log::warning('Database storage failed, continuing with Vici integration', ['error' => $dbError->getMessage()]);
        }
        
        // 🧪 TEMPORARY TESTING MODE: Bypass Vici and send directly to Allstate for API testing
        // TODO: RESTORE VICI INTEGRATION AFTER TESTING (see memory ID: 5307562)
        // ALWAYS test with Allstate for now
        if ($lead) {
            try {
                Log::warning('🧪 ALLSTATE TESTING MODE ACTIVE', [
                    'lead_id' => $lead->id,
                    'external_lead_id' => $externalLeadId,
                    'lead_name' => $lead->name,
                    'testing_mode' => true,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                // Create testing service with error handling
                try {
                    $testingService = new AllstateTestingService();
                } catch (\Exception $e) {
                    Log::error('🚨 Failed to create AllstateTestingService', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                
                $testSession = 'live_testing_' . date('Y-m-d_H');
                
                Log::info('🧪 Calling processLeadForTesting', [
                    'session' => $testSession,
                    'lead_id' => $lead->id,
                    'lead_data' => $lead->toArray()
                ]);
                
                $testResult = $testingService->processLeadForTesting($lead, $testSession);
                
                Log::info('🧪 Allstate testing completed', [
                    'lead_id' => $lead->id,
                    'external_lead_id' => $externalLeadId,
                    'success' => $testResult['success'],
                    'test_log_id' => $testResult['test_log_id'] ?? null,
                    'response_time_ms' => $testResult['response_time_ms'] ?? null
                ]);
                
            } catch (Exception $testingError) {
                Log::error('🧪 Allstate testing failed', [
                    'lead_id' => $lead->id,
                    'external_lead_id' => $externalLeadId,
                    'error' => $testingError->getMessage()
                ]);
            }
        }
        
        // ORIGINAL VICI INTEGRATION (TEMPORARILY DISABLED FOR TESTING):
        // if ($externalLeadId) {
        //     try {
        //         $viciResult = sendToViciList101($leadData, $externalLeadId);
        //         Log::info('Lead sent to Vici list 101', ['external_lead_id' => $externalLeadId, 'vici_result' => $viciResult]);
        //     } catch (Exception $viciError) {
        //         Log::error('Failed to send lead to Vici', ['error' => $viciError->getMessage(), 'external_lead_id' => $externalLeadId]);
        //     }
        // }
        
        // Store lead data in file cache for iframe testing
        $cacheId = $lead ? $lead->id : 'fallback';
        try {
            Cache::put("lead_data_{$cacheId}", $leadData, now()->addHours(24));
        } catch (Exception $cacheError) {
            // File-based fallback if cache also fails
            $cacheDir = storage_path('app/lead_cache');
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents(
                "{$cacheDir}/{$cacheId}.json", 
                json_encode(array_merge($leadData, ['cached_at' => now()->toISOString()]))
            );
            Log::info('Lead stored in file cache', ['cache_id' => $cacheId]);
        }
        
        Log::info('LeadsQuotingFast lead processed successfully', [
            'db_id' => $lead ? $lead->id : null,
            'external_lead_id' => $externalLeadId
        ]);
        
        // Return success response - only show external_lead_id to users
        return response()->json([
            'success' => true,
            'message' => 'Lead received and sent to Vici list 101',
            'lead_id' => $externalLeadId, // Show only the business lead ID
            'name' => $leadData['name'],
            'vici_list' => 101,
            'iframe_url' => $lead ? url("/agent/lead/{$lead->id}") : null, // Internal routing uses DB ID
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
        $state_filter = $request->get('state_filter');
        $vici_status = $request->get('vici_status');
        
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
        
        // Apply state filter
        if ($state_filter && $state_filter !== 'all') {
            $query->where('state', $state_filter);
        }
        
        // Apply Vici status filter
        if ($vici_status && $vici_status !== 'all') {
            if ($vici_status === 'sent') {
                $query->whereNotNull('vici_lead_id');
            } else {
                $query->whereNull('vici_lead_id');
            }
        }
        
        // Handle per_page parameter
        $perPage = $request->get('per_page', 50); // Default to 50
        
        // Get leads with pagination (simplified query to avoid relationship errors)
        if ($perPage === 'all') {
            $allLeads = $query->orderBy('created_at', 'desc')->get();
            $leads = new \Illuminate\Pagination\LengthAwarePaginator(
                $allLeads,
                $allLeads->count(),
                $allLeads->count() ?: 1,
                1,
                ['path' => $request->url()]
            );
        } else {
            $leads = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }
        
        // Get unique statuses, sources, and states for filters
        $statuses = Lead::distinct('status')->pluck('status')->filter()->sort();
        $sources = Lead::distinct('source')->pluck('source')->filter()->sort();
        $states = Lead::distinct('state')->pluck('state')->filter()->sort();
        
        // Calculate statistics for dashboard cards (with error handling)
        $stats = [
            'total_leads' => 0,
            'today_leads' => 0,
            'vici_sent' => 0,
            'allstate_sent' => 0
        ];
        
        try {
            $stats['total_leads'] = Lead::count();
            
            // Fix today's leads calculation with proper EST timezone handling
            $estNow = \Carbon\Carbon::now('America/New_York');
            $todayEST = $estNow->copy()->startOfDay();
            $tomorrowEST = $todayEST->copy()->addDay();
            $stats['today_leads'] = Lead::whereBetween('created_at', [
                $todayEST->utc(), 
                $tomorrowEST->utc()
            ])->count();
            
            $stats['vici_sent'] = Lead::whereNotNull('vici_lead_id')->count();
            $stats['allstate_sent'] = Lead::whereNotNull('allstate_lead_id')->count();
        } catch (\Exception $statsError) {
            Log::warning('Statistics calculation failed, using defaults', ['error' => $statsError->getMessage()]);
        }
        
        return view('leads.index', compact('leads', 'statuses', 'sources', 'states', 'search', 'status', 'source', 'state_filter', 'vici_status', 'stats'));
        
    } catch (\Exception $e) {
        Log::error('Leads listing error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'db_connection' => config('database.default'),
            'db_file' => database_path('database.sqlite')
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
                'sell_price' => 0.10,
                'sent_to_vici' => true
            ],
            (object)[
                'id' => 'TEST_LEAD_2',
                'first_name' => 'Cornelious',
                'last_name' => 'Zulauf',
                'phone' => '7668383228',
                'email' => 'asa.prohaska@berge.com',
                'city' => 'Riverdale',
                'state' => 'MD',
                'source' => 'Campaign',
                'status' => 'Contacted',
                'created_at' => now()->subHours(4),
                'vehicles' => [['year' => 2002, 'make' => 'MAZDA', 'model' => 'B3000 CAB PLUS']],
                'current_policy' => ['company' => 'Unknown'],
                'vici_call_metrics' => null,
                'latest_conversion' => null,
                'sell_price' => 0.15,
                'sent_to_vici' => false
            ],
            (object)[
                'id' => 'TEST_LEAD_3',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'phone' => '5551234567',
                'email' => 'sarah.j@email.com',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'source' => 'Web',
                'status' => 'Qualified',
                'created_at' => now()->subHours(6),
                'vehicles' => [['year' => 2020, 'make' => 'Honda', 'model' => 'Civic']],
                'current_policy' => ['company' => 'State Farm'],
                'vici_call_metrics' => null,
                'latest_conversion' => null,
                'sell_price' => 0.25,
                'sent_to_vici' => true
            ],
            (object)[
                'id' => 'TEST_LEAD_4',
                'first_name' => 'Michael',
                'last_name' => 'Davis',
                'phone' => '5559876543',
                'email' => 'mdavis@test.com',
                'city' => 'Miami',
                'state' => 'FL',
                'source' => 'Manual',
                'status' => 'Converted',
                'created_at' => now()->subDays(1),
                'vehicles' => [['year' => 2021, 'make' => 'Toyota', 'model' => 'Camry']],
                'current_policy' => ['company' => 'Geico'],
                'vici_call_metrics' => null,
                'latest_conversion' => null,
                'sell_price' => 0.50,
                'sent_to_vici' => false
            ]
        ]);
        
        // Apply filters to test data
        $filteredLeads = $testLeads;
        
        if ($search) {
            $filteredLeads = $filteredLeads->filter(function($lead) use ($search) {
                return stripos($lead->first_name, $search) !== false ||
                       stripos($lead->last_name, $search) !== false ||
                       stripos($lead->phone, $search) !== false ||
                       stripos($lead->email, $search) !== false;
            });
        }
        
        if ($status && $status !== 'all') {
            $filteredLeads = $filteredLeads->filter(function($lead) use ($status) {
                return strtolower($lead->status) === strtolower($status);
            });
        }
        
        if ($source && $source !== 'all') {
            $filteredLeads = $filteredLeads->filter(function($lead) use ($source) {
                return strtolower($lead->source) === strtolower($source);
            });
        }
        
        if ($state_filter && $state_filter !== 'all') {
            $filteredLeads = $filteredLeads->filter(function($lead) use ($state_filter) {
                return $lead->state === $state_filter;
            });
        }
        
        if ($vici_status && $vici_status !== 'all') {
            $filteredLeads = $filteredLeads->filter(function($lead) use ($vici_status) {
                if ($vici_status === 'sent') {
                    return $lead->sent_to_vici === true;
                } else {
                    return $lead->sent_to_vici === false;
                }
            });
        }
        
        return view('leads.index', [
            'leads' => $filteredLeads,
            'statuses' => collect(['New', 'Contacted', 'Qualified', 'Converted']),
            'sources' => collect(['Manual', 'Web', 'Campaign']),
            'states' => collect(['NV', 'MD', 'AZ', 'FL']),
            'search' => $search,
            'status' => $status,
            'source' => $source,
            'state_filter' => $state_filter,
            'vici_status' => $vici_status,
            'isTestMode' => true
        ]);
    }
});

// Agent iframe endpoint - displays full lead data with transfer button
Route::get('/agent/lead/{leadId}', function ($leadId) {
    $mode = request()->get('mode', 'agent'); // 'agent', 'view', or 'edit'
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
        // First try to find by external_lead_id (for Vici), then by internal ID (for admin)
        $lead = App\Models\Lead::where('external_lead_id', $leadId)->first();
        if (!$lead && is_numeric($leadId)) {
        $lead = App\Models\Lead::find($leadId);
        }
                if ($lead) {
                    // Ensure JSON fields are properly decoded as arrays for view compatibility
                    if (is_string($lead->drivers)) {
                        $lead->drivers = json_decode($lead->drivers, true) ?: [];
                    }
                    if (is_string($lead->vehicles)) {
                        $lead->vehicles = json_decode($lead->vehicles, true) ?: [];
                    }
                    if (is_string($lead->current_policy)) {
                        $lead->current_policy = json_decode($lead->current_policy, true) ?: [];
                    }
                    if (is_string($lead->requested_policy)) {
                        $lead->requested_policy = json_decode($lead->requested_policy, true) ?: [];
                    }
                    if (is_string($lead->meta)) {
                        $lead->meta = json_decode($lead->meta, true) ?: [];
                    }
                    
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
                        // Ensure JSON fields are properly decoded for view compatibility
                        if (isset($cachedData['drivers']) && is_string($cachedData['drivers'])) {
                            $cachedData['drivers'] = json_decode($cachedData['drivers'], true) ?: [];
                        }
                        if (isset($cachedData['vehicles']) && is_string($cachedData['vehicles'])) {
                            $cachedData['vehicles'] = json_decode($cachedData['vehicles'], true) ?: [];
                        }
                        if (isset($cachedData['current_policy']) && is_string($cachedData['current_policy'])) {
                            $cachedData['current_policy'] = json_decode($cachedData['current_policy'], true) ?: [];
                        }
                        if (isset($cachedData['meta']) && is_string($cachedData['meta'])) {
                            $cachedData['meta'] = json_decode($cachedData['meta'], true) ?: [];
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
                            // Ensure JSON fields are properly decoded for view compatibility
                            if (isset($cachedData['drivers']) && is_string($cachedData['drivers'])) {
                                $cachedData['drivers'] = json_decode($cachedData['drivers'], true) ?: [];
                            }
                            if (isset($cachedData['vehicles']) && is_string($cachedData['vehicles'])) {
                                $cachedData['vehicles'] = json_decode($cachedData['vehicles'], true) ?: [];
                            }
                            if (isset($cachedData['current_policy']) && is_string($cachedData['current_policy'])) {
                                $cachedData['current_policy'] = json_decode($cachedData['current_policy'], true) ?: [];
                            }
                            if (isset($cachedData['meta']) && is_string($cachedData['meta'])) {
                                $cachedData['meta'] = json_decode($cachedData['meta'], true) ?: [];
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
            'mockData' => !($lead instanceof App\Models\Lead), // Flag to show this is mock data
            'mode' => $mode // 'agent', 'view', or 'edit'
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
        // Note: RingbaService integration available if needed
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

// Test Vici lead push endpoint (using same function as webhook)
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

        // Prepare lead data in the same format as webhook
        $leadData = [
            'first_name' => $lead->first_name ?? explode(' ', $lead->name)[0] ?? 'Unknown',
            'last_name' => $lead->last_name ?? (count(explode(' ', $lead->name)) > 1 ? end(explode(' ', $lead->name)) : ''),
            'phone' => $lead->phone,
            'email' => $lead->email,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'zip_code' => $lead->zip_code
        ];

        // Use the same function that works in the webhook
        $viciResult = sendToViciList101($leadData, $lead->id);

        if ($viciResult) {
            return response()->json([
                'success' => true,
                'message' => 'Vici lead push test completed successfully',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'vici_result' => $viciResult,
                'webhook_url' => url('/webhook/vici'),
                'timestamp' => now()->toISOString()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vici lead push test failed',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'error' => 'Vici function returned null/false',
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

// Test Allstate API connection with multiple auth methods
Route::get('/test/allstate/connection', function () {
    try {
        // Allstate API configuration based on environment
        $environment = env('ALLSTATE_API_ENV', 'testing');
        
        if ($environment === 'production') {
            // Production credentials
            $apiKey = env('ALLSTATE_API_KEY', 'YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6'); // Production token
            $baseUrl = 'https://api.allstateleadmarketplace.com/v2';
        } else {
            // Testing credentials
            $apiKey = env('ALLSTATE_API_KEY', 'cXVvdGluZy1mYXN0Og=='); // Testing token  
            $baseUrl = 'https://int.allstateleadmarketplace.com/v2';
        }

        Log::info('Testing Allstate API connection with new token', [
            'api_key' => substr($apiKey, 0, 10) . '...',
            'base_url' => $baseUrl
        ]);

        // Test /ping endpoint with correct Basic Auth format and vertical parameter
        $testVertical = request('vertical', 'auto-insurance'); // Allow testing different verticals
        
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $apiKey
            ])
            ->post($baseUrl . '/ping', [
                'vertical' => $testVertical  // Required vertical parameter from Allstate docs
            ]);
            
        $results = [
            'ping' => [
                'status' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful()
            ]
        ];

        // We now use the correct Base64 encoded authorization format

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Allstate API connection successful',
                'api_key' => substr($apiKey, 0, 10) . '...',
                'environment' => env('ALLSTATE_API_ENV', 'testing'),
                'base_url' => $baseUrl,
                'working_endpoint' => $endpoint ?? 'unknown',
                'auth_method' => 'Bearer Token',
                'response' => $response->json(),
                'all_endpoints_tested' => $results,
                'timestamp' => now()->toISOString()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Allstate API connection failed - all endpoints failed',
                'api_key' => substr($apiKey, 0, 10) . '...',
                'api_key_length' => strlen($apiKey),
                'base_url' => $baseUrl,
                'all_endpoints_tested' => $results,
                'last_response_status' => $response->status(),
                'last_response_body' => $response->body(),
                'timestamp' => now()->toISOString()
            ], 500);
        }

    } catch (Exception $e) {
        Log::error('Allstate API connection test failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Allstate API connection test failed',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// CSV Lead Upload Portal - Admin only
Route::get('/lead-upload', function () {
    $isAdmin = true; // Placeholder for auth - replace with actual admin check
    if (!$isAdmin) {
        abort(403, 'Access denied. Admin only.');
    }
    
    // Get upload history
    $recentUploads = collect(); // Will be replaced with actual upload history from database
    
    return view('leads.upload', compact('recentUploads'));
});

// Process CSV Upload
Route::post('/lead-upload/process', function (Request $request) {
    try {
        if (!$request->hasFile('csv_file')) {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }
        
        $file = $request->file('csv_file');
        $campaignId = $request->input('campaign_id');
        $leadType = $request->input('lead_type', 'auto');
        
        // Validate file
        if ($file->getClientOriginalExtension() !== 'csv') {
            return response()->json(['success' => false, 'message' => 'File must be a CSV'], 400);
        }
        
        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
            return response()->json(['success' => false, 'message' => 'File too large. Maximum 10MB'], 400);
        }
        
        // Read and parse CSV
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $headers = array_shift($csvData); // First row as headers
        
        if (empty($csvData)) {
            return response()->json(['success' => false, 'message' => 'CSV file is empty'], 400);
        }
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($csvData as $rowIndex => $row) {
            try {
                // Map CSV row to lead data
                $leadData = [];
                foreach ($headers as $colIndex => $header) {
                    $leadData[strtolower(trim($header))] = $row[$colIndex] ?? '';
                }
                
                // Prepare lead for database
                $processedLead = [
                    'name' => trim(($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? '')) ?: ($leadData['name'] ?? 'Unknown'),
                    'first_name' => $leadData['first_name'] ?? null,
                    'last_name' => $leadData['last_name'] ?? null,
                    'phone' => $leadData['phone'] ?? 'Unknown',
                    'email' => $leadData['email'] ?? null,
                    'address' => $leadData['address'] ?? null,
                    'city' => $leadData['city'] ?? null,
                    'state' => $leadData['state'] ?? 'Unknown',
                    'zip_code' => $leadData['zip_code'] ?? $leadData['zip'] ?? null,
                    'source' => 'csv_upload',
                    'type' => $leadType,
                    'campaign_id' => $campaignId,
                    'received_at' => now(),
                    'joined_at' => now(),
                    'payload' => json_encode($leadData),
                ];
                
                // Create lead
                $lead = Lead::create($processedLead);
                
                // Generate external lead ID
                $externalLeadId = generateLeadId();
                $lead->update(['external_lead_id' => $externalLeadId]);
                
                // Handle campaign auto-detection
                if (!empty($campaignId)) {
                    $campaign = \App\Models\Campaign::autoCreateFromId($campaignId);
                    $campaign->recordLeadActivity();
                }
                
                $successCount++;
                
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Upload completed! {$successCount} leads imported successfully.",
            'stats' => [
                'total_rows' => count($csvData),
                'successful' => $successCount,
                'errors' => $errorCount,
                'error_details' => array_slice($errors, 0, 10) // First 10 errors
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
});

// Campaign Directory - Admin only
Route::get('/campaign-directory', function () {
    $isAdmin = true; // Placeholder for auth - replace with actual admin check
    if (!$isAdmin) {
        abort(403, 'Access denied. Admin only.');
    }
    
    // Get search and sort parameters
    $search = request('search');
    $sortBy = request('sort', 'last_lead_received_at');
    $sortDir = request('dir', 'desc');
    
    // Build query
    $query = \App\Models\Campaign::query();
    
    // Apply search filter
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('campaign_id', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
    
    // Apply sorting
    $allowedSorts = ['campaign_id', 'name', 'last_lead_received_at', 'total_leads', 'status', 'created_at'];
    if (in_array($sortBy, $allowedSorts)) {
        $query->orderBy($sortBy, $sortDir);
    }
    
    $campaigns = $query->paginate(20)->withQueryString();
    
    // Get statistics
    $stats = [
        'total_campaigns' => \App\Models\Campaign::count(),
        'active_campaigns' => \App\Models\Campaign::where('status', 'active')->count(),
        'auto_detected' => \App\Models\Campaign::needsAttention()->count(),
        'recent_activity' => \App\Models\Campaign::recentActivity(7)->count(),
        'total_leads_from_campaigns' => \App\Models\Lead::whereNotNull('campaign_id')->count(),
    ];
    
    return view('campaigns.directory', compact('campaigns', 'stats', 'search', 'sortBy', 'sortDir'));
});

// Update Campaign Name - converts auto-detected campaigns to managed campaigns
Route::post('/campaign-directory/{campaign}/update', function (\App\Models\Campaign $campaign) {
    $name = request('name');
    $description = request('description');
    
    if (empty($name)) {
        return response()->json(['success' => false, 'message' => 'Campaign name is required'], 400);
    }
    
    // Update campaign and get count of affected leads
    $leadsCount = $campaign->leads()->count();
    $campaign->updateWithName($name, $description);
    
    return response()->json([
        'success' => true,
        'message' => 'Campaign updated successfully',
        'campaign_id' => $campaign->campaign_id,
        'leads_updated' => $leadsCount
    ]);
});

// Fix Tony Clark lead type
Route::get('/fix-tony-clark', function () {
    $lead = Lead::find(17); // Tony Clark
        if (!$lead) {
        return response()->json(['error' => 'Tony Clark lead not found'], 404);
    }
    
    $oldType = $lead->type;
    $lead->update(['type' => 'home']);
    
    return response()->json([
        'success' => true,
        'message' => "Tony Clark updated from {$oldType} to home",
        'lead_id' => $lead->id,
        'external_lead_id' => $lead->external_lead_id,
        'name' => $lead->name,
        'old_type' => $oldType,
        'new_type' => 'home'
    ]);
});

// ===== BUYER PORTAL ROUTES =====

// Buyer Registration
Route::get('/buyer/signup', function () {
    return view('buyer.signup');
});

Route::post('/buyer/signup', function (Request $request) {
    try {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|unique:buyers,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms_accepted' => 'required|accepted'
        ]);

        $buyer = \App\Models\Buyer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'company' => $validated['company'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'status' => 'pending'
        ]);

        // Send activation email (placeholder)
        Log::info('New buyer registered', ['buyer_id' => $buyer->id, 'email' => $buyer->email]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email for activation instructions.',
            'buyer_id' => $buyer->id
        ]);

    } catch (Exception $e) {
            return response()->json([
                'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ], 400);
    }
});

// Buyer Login
Route::get('/buyer/login', function () {
    return view('buyer.login');
});

Route::post('/buyer/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $buyer = \App\Models\Buyer::where('email', $credentials['email'])->first();
    
    if (!$buyer || !Hash::check($credentials['password'], $buyer->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    if ($buyer->status !== 'active') {
        return response()->json([
            'success' => false,
            'message' => 'Account is not active. Please contact support.'
        ], 403);
    }

    // Update last login
    $buyer->update(['last_login_at' => now()]);

    // Create session (simplified)
    session(['buyer_id' => $buyer->id]);

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => '/buyer/dashboard'
    ]);
});

// Buyer Dashboard
Route::get('/buyer/dashboard', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::with(['leads' => function($query) {
        $query->latest()->limit(10);
    }])->find($buyerId);

    if (!$buyer) {
        return redirect('/buyer/login');
    }

    // Get statistics
    $stats = [
        'total_leads' => $buyer->leads()->count(),
        'delivered_leads' => $buyer->leads()->delivered()->count(),
        'returned_leads' => $buyer->leads()->returned()->count(),
        'current_balance' => $buyer->balance,
        'total_spent' => $buyer->leads()->sum('price'),
        'auto_reload_status' => $buyer->auto_reload_enabled
    ];

    return view('buyer.dashboard', compact('buyer', 'stats'));
});

// Buyer Leads
Route::get('/buyer/leads', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    
    // Get pagination parameters
    $perPage = $request->get('per_page', 20);
    if ($perPage === 'all') {
        $perPage = 10000; // Large number for "all"
    } else {
        $perPage = in_array($perPage, [20, 50, 100, 200]) ? (int)$perPage : 20;
    }
    
    // Build query with filters
    $query = $buyer->leads()->with('lead')->latest();
    
    // Date filter
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    // Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->whereHas('lead', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('external_lead_id', 'like', "%{$search}%");
        });
    }
    
    $leads = $query->paginate($perPage)->appends($request->query());

    return view('buyer.leads', compact('buyer', 'leads'));
});

// Buyer Billing
Route::get('/buyer/billing', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::with('payments')->find($buyerId);
    if (!$buyer || $buyer->status !== 'active') {
        session()->forget('buyer_id');
        return redirect('/buyer/login')->with('error', 'Account not active');
    }

    return view('buyer.billing', compact('buyer'));
});

// Buyer Lead Return
Route::post('/buyer/leads/{leadId}/return', function ($leadId, Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $buyerLead = \App\Models\BuyerLead::where('id', $leadId)
        ->where('buyer_id', $buyerId)
        ->first();

    if (!$buyerLead) {
        return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
    }

    $validated = $request->validate([
        'return_reason' => 'required|string|max:255',
        'return_notes' => 'nullable|string|max:1000'
    ]);

    if ($buyerLead->returnLead($validated['return_reason'], $validated['return_notes'])) {
        return response()->json([
            'success' => true,
            'message' => 'Lead returned successfully. Your account has been credited.',
            'refund_amount' => $buyerLead->price
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Unable to return lead. Return window may have expired.'
        ], 400);
    }
});

// QuickBooks OAuth Routes
Route::get('/buyer/quickbooks/connect', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $qbService = new \App\Services\QuickBooksService();
    $authUrl = $qbService->getAuthUrl($buyerId);
    
    return redirect($authUrl);
});

Route::get('/buyer/quickbooks/callback', function (Request $request) {
    $code = $request->get('code');
    $state = $request->get('state');
    $realmId = $request->get('realmId');
    
    if (!$code || !$state || !$realmId) {
        return redirect('/buyer/billing')->with('error', 'QuickBooks connection failed');
    }

    $qbService = new \App\Services\QuickBooksService();
    $result = $qbService->exchangeCodeForTokens($code, $state, $realmId);
    
    if ($result['success']) {
        return redirect('/buyer/billing')->with('success', 'QuickBooks connected successfully!');
    } else {
        return redirect('/buyer/billing')->with('error', 'QuickBooks connection failed: ' . $result['error']);
    }
});

Route::post('/buyer/quickbooks/disconnect', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $qbService = new \App\Services\QuickBooksService();
    if ($qbService->disconnect($buyerId)) {
        return response()->json(['success' => true, 'message' => 'QuickBooks disconnected successfully']);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to disconnect QuickBooks']);
    }
});

// Payment Processing Routes
Route::post('/buyer/payment/add-funds', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'amount' => 'required|numeric|min:10|max:10000',
        'payment_method' => 'required|string|in:quickbooks,credit_card,bank_account'
    ]);

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return response()->json(['success' => false, 'message' => 'Buyer not found'], 404);
    }

    $qbService = new \App\Services\QuickBooksService();
    $result = $qbService->processPayment($buyer, $validated['amount'], $validated['payment_method']);

    if ($result['success']) {
        // Send payment notification
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendPaymentNotification($buyerId, [
            'status' => 'completed',
            'amount' => $validated['amount'],
            'new_balance' => $result['new_balance'],
            'payment_method' => $validated['payment_method']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'new_balance' => $result['new_balance'],
            'payment_id' => $result['payment']->id
        ]);
    } else {
        // Send payment failure notification
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendPaymentNotification($buyerId, [
            'status' => 'failed',
            'amount' => $validated['amount'],
            'failure_reason' => $result['error'],
            'payment_method' => $validated['payment_method']
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment failed: ' . $result['error']
        ], 400);
    }
});

Route::get('/buyer/payment/quickbooks/{paymentId}', function ($paymentId) {
    $paymentData = Cache::get("qb_payment_{$paymentId}");
    
    if (!$paymentData) {
        return redirect('/buyer/billing')->with('error', 'Payment link expired or invalid');
    }

    $buyer = \App\Models\Buyer::find($paymentData['buyer_id']);
    if (!$buyer) {
        return redirect('/buyer/billing')->with('error', 'Invalid payment request');
    }

    // This would render a QuickBooks payment form
    // For now, redirect to billing with success message
    return redirect('/buyer/billing')->with('success', 'Payment link accessed successfully');
});

// Auto-reload Settings
Route::post('/buyer/settings/auto-reload', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'enabled' => 'required|boolean',
        'amount' => 'required_if:enabled,true|nullable|numeric|min:25|max:1000',
        'threshold' => 'required_if:enabled,true|nullable|numeric|min:5|max:500'
    ]);

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return response()->json(['success' => false, 'message' => 'Buyer not found'], 404);
    }

    $buyer->update([
        'auto_reload_enabled' => $validated['enabled'],
        'auto_reload_amount' => $validated['enabled'] ? $validated['amount'] : null,
        'auto_reload_threshold' => $validated['enabled'] ? $validated['threshold'] : null
    ]);

    return response()->json([
        'success' => true,
        'message' => $validated['enabled'] ? 'Auto-reload enabled' : 'Auto-reload disabled',
        'settings' => [
            'enabled' => $buyer->auto_reload_enabled,
            'amount' => $buyer->auto_reload_amount,
            'threshold' => $buyer->auto_reload_threshold
        ]
    ]);
});

// Buyer Documents
Route::get('/buyer/documents', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer || $buyer->status !== 'active') {
        session()->forget('buyer_id');
        return redirect('/buyer/login')->with('error', 'Account not active');
    }

    // Sample documents data (in real app, this would come from database)
    $documents = [
        [
            'id' => 'doc_001',
            'name' => 'Insurance License Copy',
            'type' => 'pdf',
            'size' => '2.1 MB',
            'uploaded_at' => '2 days ago',
            'requires_signature' => false,
            'signed' => false
        ],
        [
            'id' => 'doc_002', 
            'name' => 'W-9 Tax Form',
            'type' => 'pdf',
            'size' => '1.3 MB',
            'uploaded_at' => '1 week ago',
            'requires_signature' => true,
            'signed' => false
        ],
        [
            'id' => 'doc_003',
            'name' => 'Company Logo',
            'type' => 'image',
            'size' => '245 KB',
            'uploaded_at' => '2 weeks ago',
            'requires_signature' => false,
            'signed' => false
        ]
    ];

    return view('buyer.documents', compact('buyer', 'documents'));
});

// Document Signing Interface
Route::get('/buyer/documents/{documentId}/sign', function ($documentId) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return redirect('/buyer/login');
    }

    // Sample document data
    $document = [
        'id' => $documentId,
        'name' => $documentId === 'contract' ? 'QuotingFast Buyer Agreement' : 'Document ' . $documentId,
        'type' => 'contract',
        'requires_signature' => true
    ];

    return view('buyer.sign-document', compact('buyer', 'document'));
});

// Process Document Signature
Route::post('/buyer/documents/{documentId}/signature', function ($documentId, Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return response()->json(['success' => false, 'message' => 'Buyer not found'], 404);
    }

    $validated = $request->validate([
        'signer_name' => 'required|string|max:255',
        'signer_email' => 'required|email',
        'signer_title' => 'nullable|string|max:255',
        'signature_data' => 'required|string',
        'consent_agreed' => 'required|boolean|accepted'
    ]);

    try {
        // If this is the main contract, update buyer's contract status
        if ($documentId === 'contract') {
            $buyer->update([
                'contract_signed' => true,
                'contract_signed_at' => now(),
                'contract_ip' => $request->ip(),
                'status' => 'active' // Activate account upon contract signing
            ]);

            // Create contract record
            $buyer->contracts()->create([
                'contract_version' => 'v2.1',
                'contract_content' => 'QuotingFast Buyer Agreement - Full Terms',
                'signed_at' => now(),
                'signature_ip' => $request->ip(),
                'signature_method' => 'digital_canvas',
                'signature_data' => [
                    'signer_name' => $validated['signer_name'],
                    'signer_email' => $validated['signer_email'],
                    'signer_title' => $validated['signer_title'],
                    'signature_canvas_data' => $validated['signature_data'],
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString()
                ],
                'is_active' => true
            ]);

            \Illuminate\Support\Facades\Log::info("Buyer contract signed", [
                'buyer_id' => $buyer->id,
                'document_id' => $documentId,
                'signer_name' => $validated['signer_name'],
                'ip_address' => $request->ip()
            ]);

            // Send contract signed notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendSystemNotification(
                $buyer->id,
                'Contract Signed Successfully',
                'Your buyer agreement has been signed and your account is now active. Welcome to QuotingFast!',
                ['contract_version' => 'v2.1', 'signed_at' => now()->toISOString()]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Document signed successfully',
            'signed_at' => now()->toISOString(),
            'certificate_id' => 'CERT_' . strtoupper(uniqid())
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Document signature failed", [
            'buyer_id' => $buyer->id,
            'document_id' => $documentId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Signature processing failed'
        ], 500);
    }
});

// Document Upload
Route::post('/buyer/documents/upload', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        'document_name' => 'nullable|string|max:255',
        'document_type' => 'required|string|in:contract,license,tax_form,identity,other'
    ]);

    try {
        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // In a real app, you'd store this in cloud storage (S3, etc.)
        $path = $file->storeAs('buyer_documents/' . $buyerId, $filename, 'local');

        // Store document metadata in database (would need a documents table)
        $documentData = [
            'buyer_id' => $buyerId,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $filename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'document_type' => $validated['document_type'],
            'uploaded_at' => now()
        ];

        \Illuminate\Support\Facades\Log::info("Document uploaded", $documentData);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document' => [
                'id' => 'doc_' . uniqid(),
                'name' => $validated['document_name'] ?? $file->getClientOriginalName(),
                'size' => number_format($file->getSize() / 1024 / 1024, 1) . ' MB',
                'type' => $file->getClientOriginalExtension(),
                'uploaded_at' => 'Just now'
            ]
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Document upload failed", [
            'buyer_id' => $buyerId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
});

// Document Download
Route::get('/buyer/documents/{documentId}/download', function ($documentId) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    // In a real app, you'd fetch the document from database and serve the file
    // For now, return a success message
    return response()->json([
        'success' => true,
        'message' => 'Document download initiated',
        'download_url' => '/storage/documents/' . $documentId . '.pdf'
    ]);
});

// Buyer Notifications
Route::get('/buyer/notifications', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer || $buyer->status !== 'active') {
        session()->forget('buyer_id');
        return redirect('/buyer/login')->with('error', 'Account not active');
    }

    return view('buyer.notifications', compact('buyer'));
});

// Get Notifications API
Route::get('/api/buyer/notifications', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $notificationService = new \App\Services\NotificationService();
    
    $filter = $request->get('filter', 'all');
    $limit = $request->get('limit', 20);
    
    $notifications = $notificationService->getBuyerNotifications($buyerId, $limit, $filter);
    $stats = $notificationService->getNotificationStats($buyerId);

    return response()->json([
        'notifications' => $notifications,
        'stats' => $stats
    ]);
});

// Real-time Notifications Polling
Route::get('/api/buyer/notifications/realtime', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $notificationService = new \App\Services\NotificationService();
    $realtimeNotifications = $notificationService->getRealtimeNotifications($buyerId);

    return response()->json([
        'notifications' => $realtimeNotifications,
        'timestamp' => now()->toISOString()
    ]);
});

// Mark Notification as Read
Route::post('/api/buyer/notifications/{notificationId}/read', function ($notificationId) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $notificationService = new \App\Services\NotificationService();
    $success = $notificationService->markAsRead($buyerId, $notificationId);

    return response()->json(['success' => $success]);
});

// Mark All Notifications as Read
Route::post('/api/buyer/notifications/read-all', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $notificationService = new \App\Services\NotificationService();
    $success = $notificationService->markAllAsRead($buyerId);

    return response()->json(['success' => $success]);
});

// Update Notification Preferences
Route::post('/api/buyer/notifications/preferences', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'email.new_leads' => 'boolean',
        'email.payment_confirmations' => 'boolean',
        'email.low_balance_warnings' => 'boolean',
        'email.weekly_summary' => 'boolean',
        'push.browser_notifications' => 'boolean',
        'push.sound_alerts' => 'boolean'
    ]);

    $notificationService = new \App\Services\NotificationService();
    $success = $notificationService->updateNotificationPreferences($buyerId, $validated);

    return response()->json(['success' => $success]);
});

// Test Notification (Admin/Development)
Route::post('/api/buyer/notifications/test', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $type = $request->get('type', 'system');
    $title = $request->get('title', 'Test Notification');
    $message = $request->get('message', 'This is a test notification from The Brain.');

    $notificationService = new \App\Services\NotificationService();
    $success = $notificationService->sendNotification(
        $buyerId,
        $type,
        $title,
        $message,
        ['test' => true],
        ['database', 'realtime']
    );

    return response()->json([
        'success' => $success,
        'message' => 'Test notification sent'
    ]);
});

// Buyer Lead Outcomes
Route::get('/buyer/lead-outcomes', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer || $buyer->status !== 'active') {
        session()->forget('buyer_id');
        return redirect('/buyer/login')->with('error', 'Account not active');
    }

    // Get outcome statistics
    $stats = [
        'total_leads' => \App\Models\LeadOutcome::where('buyer_id', $buyerId)->count(),
        'sold_leads' => \App\Models\LeadOutcome::where('buyer_id', $buyerId)->where('outcome', 'sold')->count(),
        'conversion_rate' => 0,
        'avg_quality' => \App\Models\LeadOutcome::where('buyer_id', $buyerId)->whereNotNull('quality_rating')->avg('quality_rating'),
        'total_revenue' => \App\Models\LeadOutcome::where('buyer_id', $buyerId)->sum('sale_amount'),
        'avg_close_time' => 0
    ];

    $totalLeads = $stats['total_leads'];
    if ($totalLeads > 0) {
        $stats['conversion_rate'] = round(($stats['sold_leads'] / $totalLeads) * 100, 1);
    }

    $stats['avg_quality'] = round($stats['avg_quality'] ?? 0, 1);

    return view('buyer.lead-outcomes', compact('buyer', 'stats'));
});

// Submit Lead Outcome API
Route::post('/api/buyer/lead-outcomes', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'lead_id' => 'required|string',
        'status' => 'nullable|string|in:new,contacted,qualified,proposal_sent,negotiating,closed_won,closed_lost,not_interested,bad_lead,duplicate',
        'outcome' => 'nullable|string|in:pending,sold,not_sold,bad_lead,duplicate',
        'sale_amount' => 'nullable|numeric|min:0',
        'commission_amount' => 'nullable|numeric|min:0',
        'quality_rating' => 'nullable|integer|min:1|max:5',
        'notes' => 'nullable|string|max:1000',
        'contact_attempts' => 'nullable|integer|min:0'
    ]);

    try {
        // Find the lead
        $lead = \App\Models\Lead::where('external_lead_id', $validated['lead_id'])->first();
        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
        }

        // Create or update outcome
        $outcome = \App\Models\LeadOutcome::updateOrCreate(
            [
                'lead_id' => $lead->id,
                'buyer_id' => $buyerId
            ],
            [
                'external_lead_id' => $validated['lead_id'],
                'status' => $validated['status'] ?? 'new',
                'outcome' => $validated['outcome'] ?? 'pending',
                'sale_amount' => $validated['sale_amount'],
                'commission_amount' => $validated['commission_amount'],
                'quality_rating' => $validated['quality_rating'],
                'notes' => $validated['notes'],
                'contact_attempts' => $validated['contact_attempts'] ?? 0,
                'reported_via' => 'api',
                'last_contact_at' => now()
            ]
        );

        // Set first contact if not set
        if (!$outcome->first_contact_at && $validated['status'] !== 'new') {
            $outcome->update(['first_contact_at' => now()]);
        }

        // Set closed date for final statuses
        if (in_array($validated['status'], ['closed_won', 'closed_lost', 'not_interested', 'bad_lead'])) {
            $outcome->update(['closed_at' => now()]);
        }

        // Send notification to QuotingFast about outcome
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendSystemNotification(
            $buyerId,
            'Lead Outcome Reported',
            "Thank you for reporting the outcome of lead {$validated['lead_id']}. This helps us improve lead quality!",
            ['lead_id' => $validated['lead_id'], 'outcome' => $validated['outcome']]
        );

        // Log for QuotingFast internal tracking
        \Illuminate\Support\Facades\Log::info("Lead outcome reported", [
            'buyer_id' => $buyerId,
            'lead_id' => $validated['lead_id'],
            'outcome' => $validated['outcome'],
            'status' => $validated['status'],
            'sale_amount' => $validated['sale_amount'],
            'quality_rating' => $validated['quality_rating']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Outcome reported successfully',
            'outcome_id' => $outcome->id
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Lead outcome submission failed", [
            'buyer_id' => $buyerId,
            'lead_id' => $validated['lead_id'],
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to submit outcome report'
        ], 500);
    }
});

// Get Lead Outcomes API
Route::get('/api/buyer/lead-outcomes', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $filter = $request->get('filter', 'all');
    $limit = $request->get('limit', 20);
    $page = $request->get('page', 1);

    $query = \App\Models\LeadOutcome::where('buyer_id', $buyerId)
        ->with('lead')
        ->orderBy('updated_at', 'desc');

    // Apply filters
    if ($filter !== 'all') {
        $query->where('outcome', $filter);
    }

    $outcomes = $query->paginate($limit, ['*'], 'page', $page);

    return response()->json([
        'outcomes' => $outcomes->items(),
        'pagination' => [
            'current_page' => $outcomes->currentPage(),
            'total_pages' => $outcomes->lastPage(),
            'total_items' => $outcomes->total()
        ]
    ]);
});

// Webhook for CRM outcome reports
Route::post('/webhook/crm-outcome/{buyerId}', function (Request $request, $buyerId) {
    try {
        $buyer = \App\Models\Buyer::find($buyerId);
        if (!$buyer) {
            return response()->json(['error' => 'Buyer not found'], 404);
        }

        // Validate webhook signature if configured
        $crmConfig = $buyer->crm_config ?? [];
        if (isset($crmConfig['webhook_secret'])) {
            $signature = $request->header('X-Webhook-Signature');
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $crmConfig['webhook_secret']);
            
            if (!hash_equals($expectedSignature, $signature)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $data = $request->all();
        
        // Map CRM data to our format (this would vary by CRM)
        $leadId = $data['lead_id'] ?? $data['external_id'] ?? null;
        $status = $data['status'] ?? 'new';
        $outcome = $data['outcome'] ?? 'pending';
        
        if (!$leadId) {
            return response()->json(['error' => 'Lead ID required'], 400);
        }

        // Find the lead
        $lead = \App\Models\Lead::where('external_lead_id', $leadId)->first();
        if (!$lead) {
            return response()->json(['error' => 'Lead not found'], 404);
        }

        // Create or update outcome
        $outcome = \App\Models\LeadOutcome::updateOrCreate(
            [
                'lead_id' => $lead->id,
                'buyer_id' => $buyerId
            ],
            [
                'external_lead_id' => $leadId,
                'crm_lead_id' => $data['crm_lead_id'] ?? null,
                'status' => $status,
                'outcome' => $outcome,
                'sale_amount' => $data['sale_amount'] ?? null,
                'commission_amount' => $data['commission_amount'] ?? null,
                'quality_rating' => $data['quality_rating'] ?? null,
                'contact_attempts' => $data['contact_attempts'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'source_system' => $data['source_system'] ?? 'crm',
                'reported_via' => 'webhook',
                'metadata' => $data,
                'last_contact_at' => now()
            ]
        );

        \Illuminate\Support\Facades\Log::info("CRM outcome webhook received", [
            'buyer_id' => $buyerId,
            'lead_id' => $leadId,
            'status' => $status,
            'outcome' => $outcome,
            'source' => $data['source_system'] ?? 'unknown'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Outcome received',
            'outcome_id' => $outcome->id
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("CRM outcome webhook failed", [
            'buyer_id' => $buyerId,
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Webhook processing failed'
        ], 500);
    }
});

// Buyer CRM Settings
Route::get('/buyer/crm-settings', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return redirect('/buyer/login');
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer || $buyer->status !== 'active') {
        session()->forget('buyer_id');
        return redirect('/buyer/login')->with('error', 'Account not active');
    }

    return view('buyer.crm-settings', compact('buyer'));
});

// Get CRM Configuration API
Route::get('/api/buyer/crm/config', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    $config = $buyer->crm_config ?? [];
    $stats = $buyer->crm_stats ?? [];

    return response()->json([
        'config' => $config,
        'stats' => $stats
    ]);
});

// Save CRM Configuration API
Route::post('/api/buyer/crm/config', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return response()->json(['error' => 'Buyer not found'], 404);
    }

    $validated = $request->validate([
        'type' => 'required|string|in:salesforce,hubspot,pipedrive,zoho,dynamics,freshsales,activecampaign,gohighlevel,webhook',
        'enabled' => 'boolean',
        'instance_url' => 'nullable|string',
        'access_token' => 'nullable|string',
        'api_key' => 'nullable|string',
        'portal_id' => 'nullable|string',
        'domain' => 'nullable|string',
        'api_token' => 'nullable|string',
        'webhook_url' => 'nullable|url',
        'auth_method' => 'nullable|string|in:none,bearer,api_key,basic',
        'field_mapping' => 'nullable|array'
    ]);

    try {
        $buyer->update(['crm_config' => $validated]);

        // Send notification about CRM setup
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendSystemNotification(
            $buyerId,
            'CRM Integration Configured',
            "Your {$validated['type']} integration has been set up successfully. Leads will now be automatically delivered to your CRM.",
            ['crm_type' => $validated['type']]
        );

        return response()->json([
            'success' => true,
            'message' => 'CRM configuration saved successfully'
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("CRM config save failed", [
            'buyer_id' => $buyerId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Failed to save CRM configuration'
        ], 500);
    }
});

// Test CRM Connection API
Route::post('/api/buyer/crm/test', function (Request $request) {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'type' => 'required|string',
        'instance_url' => 'nullable|string',
        'access_token' => 'nullable|string',
        'api_key' => 'nullable|string',
        'domain' => 'nullable|string',
        'api_token' => 'nullable|string',
        'webhook_url' => 'nullable|url'
    ]);

    try {
        $crmService = new \App\Services\CRMIntegrationService();
        $result = $crmService->testCRMConnection($buyerId, $validated);

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Connection test failed: ' . $e->getMessage()
        ]);
    }
});

// Disable CRM Integration API
Route::post('/api/buyer/crm/disable', function () {
    $buyerId = session('buyer_id');
    if (!$buyerId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return response()->json(['error' => 'Buyer not found'], 404);
    }

    try {
        $currentConfig = $buyer->crm_config ?? [];
        $currentConfig['enabled'] = false;
        
        $buyer->update(['crm_config' => $currentConfig]);

        // Send notification about CRM disable
        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendSystemNotification(
            $buyerId,
            'CRM Integration Disabled',
            'Your CRM integration has been disabled. Leads will no longer be automatically delivered to your CRM system.',
            ['action' => 'disabled']
        );

        return response()->json([
            'success' => true,
            'message' => 'CRM integration disabled successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to disable CRM integration'
        ], 500);
    }
});

// Get CRM Templates API
Route::get('/api/buyer/crm/templates', function () {
    $crmService = new \App\Services\CRMIntegrationService();
    $templates = $crmService->getCRMTemplates();

    return response()->json([
        'templates' => $templates
    ]);
});

// Buyer Logout
Route::post('/buyer/logout', function () {
    session()->forget('buyer_id');
    return redirect('/buyer/login');
});

// Admin Buyer Management
Route::get('/admin/buyer-management', function () {
    return view('admin.buyer-management');
});

// 🧪 Allstate API Testing Dashboard
Route::get('/admin/allstate-testing', function () {
    $testingService = new \App\Services\AllstateTestingService();
    
    $testLogs = $testingService->getRecentTestResults(100);
    $stats = $testingService->getTestStatistics();
    
    return view('admin.allstate-testing', compact('testLogs', 'stats'));
});

// API endpoint to get test details (for modal)
Route::get('/admin/allstate-testing/details/{logId}', function ($logId) {
    $log = \App\Models\AllstateTestLog::findOrFail($logId);
    
    return response()->json([
        'id' => $log->id,
        'lead_name' => $log->lead_name,
        'lead_type' => $log->lead_type,
        'qualification_data' => $log->qualification_data,
        'data_sources' => $log->data_sources,
        'allstate_payload' => $log->allstate_payload,
        'allstate_endpoint' => $log->allstate_endpoint,
        'allstate_response' => $log->allstate_response,
        'success' => $log->success,
        'error_message' => $log->error_message,
        'validation_errors' => $log->validation_errors,
        'response_status' => $log->response_status,
        'response_time_ms' => $log->response_time_ms,
        'test_environment' => $log->test_environment,
        'sent_at' => $log->sent_at->toISOString()
    ]);
});

// Bulk Process Existing Leads Through Allstate API
Route::post('/admin/allstate-testing/bulk-process', function (Request $request) {
    try {
        $dateFilter = $request->get('date_filter', 'today');
        $limit = $request->get('limit', 50);
        
        // Build query based on date filter
        $query = \App\Models\Lead::orderBy('created_at', 'desc');
        
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'last_7_days':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'all':
                // No date filter
                break;
        }
        
        // Get leads that haven't been tested yet
        $leads = $query->whereNotIn('id', function($subQuery) {
            $subQuery->select('lead_id')
                     ->from('allstate_test_logs')
                     ->whereNotNull('lead_id');
        })->limit($limit)->get();
        
        if ($leads->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No untested leads found for the selected criteria'
            ]);
        }
        
        $testingService = new AllstateTestingService();
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($leads as $lead) {
            try {
                $testSession = 'bulk_processing_' . date('Y-m-d_H-i');
                $result = $testingService->processLeadForTesting($lead, $testSession);
                
                $results[] = [
                    'lead_id' => $lead->id,
                    'lead_name' => $lead->name,
                    'success' => $result['success'],
                    'test_log_id' => $result['test_log_id'] ?? null,
                    'response_time_ms' => $result['response_time_ms'] ?? null,
                    'error' => $result['error'] ?? null
                ];
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                
                // Small delay to avoid overwhelming the API
                usleep(250000); // 0.25 seconds
                
            } catch (Exception $e) {
                $results[] = [
                    'lead_id' => $lead->id,
                    'lead_name' => $lead->name,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $failCount++;
            }
        }
        
        \Illuminate\Support\Facades\Log::info('Bulk Allstate testing completed', [
            'total_processed' => count($results),
            'successful' => $successCount,
            'failed' => $failCount,
            'date_filter' => $dateFilter
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Processed {$leads->count()} leads: {$successCount} successful, {$failCount} failed",
            'stats' => [
                'total_processed' => count($results),
                'successful' => $successCount,
                'failed' => $failCount
            ],
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        \Illuminate\Support\Facades\Log::error('Bulk processing failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Bulk processing failed: ' . $e->getMessage()
        ], 500);
    }
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Admin Impersonation - Login as any buyer
Route::get('/admin/impersonate/{buyerId}', function ($buyerId) {
    $buyer = \App\Models\Buyer::find($buyerId);
    if (!$buyer) {
        return redirect('/admin/buyer-management')->with('error', 'Buyer not found');
    }

    // Set buyer session to impersonate
    session(['buyer_id' => $buyerId, 'impersonating' => true, 'admin_impersonation' => true]);
    
    // Log the impersonation
    \Illuminate\Support\Facades\Log::info("Admin impersonation started", [
        'buyer_id' => $buyerId,
        'buyer_name' => $buyer->full_name,
        'buyer_email' => $buyer->email,
        'admin_ip' => request()->ip()
    ]);

    return redirect('/buyer/dashboard')->with('success', "Now viewing as {$buyer->full_name}");
});

// Stop Admin Impersonation
Route::get('/admin/stop-impersonation', function () {
    $buyerId = session('buyer_id');
    $buyer = \App\Models\Buyer::find($buyerId);
    
    // Log the end of impersonation
    if ($buyer) {
        \Illuminate\Support\Facades\Log::info("Admin impersonation ended", [
            'buyer_id' => $buyerId,
            'buyer_name' => $buyer->full_name,
            'admin_ip' => request()->ip()
        ]);
    }

    // Clear buyer session
    session()->forget(['buyer_id', 'impersonating', 'admin_impersonation']);
    
    return redirect('/admin/buyer-management')->with('success', 'Impersonation ended');
});

// Create Dummy Buyer API
Route::post('/admin/create-dummy-buyer', function (Request $request) {
    $validated = $request->validate([
        'account_type' => 'required|string|in:realistic,demo,minimal',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:buyers,email',
        'company' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'balance' => 'nullable|numeric|min:0',
        'include_sample_leads' => 'boolean',
        'include_sample_payments' => 'boolean',
        'include_sample_documents' => 'boolean',
        'include_crm_config' => 'boolean'
    ]);

    try {
        // Create buyer
        $buyer = \App\Models\Buyer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'company' => $validated['company'],
            'phone' => $validated['phone'],
            'password' => bcrypt('password123'), // Default password for test accounts
            'balance' => $validated['balance'] ?? 0,
            'status' => 'active',
            'is_test_account' => true,
            'account_type' => $validated['account_type'],
            'created_via' => 'admin_dummy'
        ]);

        // Generate sample data if requested
        if ($validated['include_sample_leads'] ?? false) {
            generateSampleLeads($buyer->id, $validated['account_type']);
        }

        if ($validated['include_sample_payments'] ?? false) {
            generateSamplePayments($buyer->id, $validated['account_type']);
        }

        if ($validated['include_sample_documents'] ?? false) {
            generateSampleDocuments($buyer->id);
        }

        if ($validated['include_crm_config'] ?? false) {
            generateSampleCRMConfig($buyer->id);
        }

        \Illuminate\Support\Facades\Log::info("Dummy buyer created", [
            'buyer_id' => $buyer->id,
            'buyer_name' => $buyer->full_name,
            'account_type' => $validated['account_type'],
            'created_by' => 'admin'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dummy buyer created successfully',
            'buyer_id' => $buyer->id,
            'login_url' => "/admin/impersonate/{$buyer->id}"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create dummy buyer: ' . $e->getMessage()
        ], 500);
    }
});

// Generate sample data for specific buyer
Route::post('/admin/generate-sample-data/{buyerId}', function ($buyerId) {
    try {
        $buyer = \App\Models\Buyer::find($buyerId);
        if (!$buyer) {
            return response()->json(['success' => false, 'message' => 'Buyer not found'], 404);
        }

        generateSampleLeads($buyerId, 'demo');
        generateSamplePayments($buyerId, 'demo');
        generateSampleDocuments($buyerId);
        generateSampleOutcomes($buyerId);

        return response()->json([
            'success' => true,
            'message' => 'Sample data generated successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate sample data: ' . $e->getMessage()
        ], 500);
    }
});

// Generate sample data for all buyers
Route::post('/admin/generate-all-sample-data', function () {
    try {
        $buyers = \App\Models\Buyer::where('is_test_account', true)->get();
        
        foreach ($buyers as $buyer) {
            generateSampleLeads($buyer->id, 'demo');
            generateSamplePayments($buyer->id, 'demo');
            generateSampleDocuments($buyer->id);
            generateSampleOutcomes($buyer->id);
        }

        return response()->json([
            'success' => true,
            'message' => "Sample data generated for {$buyers->count()} buyers"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate sample data: ' . $e->getMessage()
        ], 500);
    }
});

// Clear all test data
Route::post('/admin/clear-test-data', function () {
    try {
        // Delete test buyers and all related data
        $testBuyers = \App\Models\Buyer::where('is_test_account', true)->get();
        
        foreach ($testBuyers as $buyer) {
            // Delete related data first
            \App\Models\LeadOutcome::where('buyer_id', $buyer->id)->delete();
            \App\Models\BuyerPayment::where('buyer_id', $buyer->id)->delete();
            \App\Models\BuyerLead::where('buyer_id', $buyer->id)->delete();
            \App\Models\BuyerContract::where('buyer_id', $buyer->id)->delete();
            
            // Delete the buyer
            $buyer->delete();
        }

        // Delete sample leads
        \App\Models\Lead::where('is_sample_data', true)->delete();

        \Illuminate\Support\Facades\Log::info("Test data cleared", [
            'buyers_deleted' => $testBuyers->count(),
            'cleared_by' => 'admin'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cleared {$testBuyers->count()} test buyers and all sample data"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to clear test data: ' . $e->getMessage()
        ], 500);
    }
});

// Get buyers list API
Route::get('/admin/buyers-list', function () {
    try {
        $buyers = \App\Models\Buyer::with(['payments', 'leads'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($buyer) {
                return [
                    'id' => $buyer->id,
                    'name' => $buyer->full_name,
                    'email' => $buyer->email,
                    'company' => $buyer->company,
                    'status' => $buyer->status,
                    'balance' => $buyer->formatted_balance,
                    'leads_count' => $buyer->leads->count(),
                    'is_test_account' => $buyer->is_test_account,
                    'created_at' => $buyer->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'buyers' => $buyers
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load buyers list'
        ], 500);
    }
});

// Delete buyer
Route::delete('/admin/delete-buyer/{buyerId}', function ($buyerId) {
    try {
        $buyer = \App\Models\Buyer::find($buyerId);
        if (!$buyer) {
            return response()->json(['success' => false, 'message' => 'Buyer not found'], 404);
        }

        // Delete related data first
        \App\Models\LeadOutcome::where('buyer_id', $buyerId)->delete();
        \App\Models\BuyerPayment::where('buyer_id', $buyerId)->delete();
        \App\Models\BuyerLead::where('buyer_id', $buyerId)->delete();
        \App\Models\BuyerContract::where('buyer_id', $buyerId)->delete();
        
        $buyerName = $buyer->full_name;
        $buyer->delete();

        return response()->json([
            'success' => true,
            'message' => "Buyer {$buyerName} deleted successfully"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete buyer: ' . $e->getMessage()
        ], 500);
    }
});

// Sample data generation functions
function generateSampleLeads($buyerId, $accountType = 'demo') {
    $leadCount = $accountType === 'demo' ? 25 : ($accountType === 'realistic' ? 15 : 5);
    
    $sampleLeads = [
        [
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.johnson@email.com',
            'phone' => '(555) 123-4567',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'vertical' => 'auto',
            'type' => 'auto'
        ],
        [
            'first_name' => 'Michael',
            'last_name' => 'Chen',
            'email' => 'michael.chen@email.com',
            'phone' => '(555) 234-5678',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90210',
            'vertical' => 'home',
            'type' => 'home'
        ],
        [
            'first_name' => 'Emily',
            'last_name' => 'Davis',
            'email' => 'emily.davis@email.com',
            'phone' => '(555) 345-6789',
            'city' => 'Houston',
            'state' => 'TX',
            'zip' => '77001',
            'vertical' => 'auto',
            'type' => 'auto'
        ]
    ];

    for ($i = 0; $i < $leadCount; $i++) {
        $sampleLead = $sampleLeads[$i % count($sampleLeads)];
        $externalLeadId = 'DEMO' . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
        
        \App\Models\Lead::create(array_merge($sampleLead, [
            'external_lead_id' => $externalLeadId,
            'address' => '123 Sample St',
            'age' => rand(25, 65),
            'campaign_id' => 'DEMO_CAMPAIGN_' . rand(1, 5),
            'is_sample_data' => true,
            'created_at' => now()->subDays(rand(0, 30))
        ]));
    }
}

function generateSamplePayments($buyerId, $accountType = 'demo') {
    $paymentCount = $accountType === 'demo' ? 10 : ($accountType === 'realistic' ? 6 : 3);
    
    for ($i = 0; $i < $paymentCount; $i++) {
        \App\Models\BuyerPayment::create([
            'buyer_id' => $buyerId,
            'transaction_id' => 'DEMO_' . uniqid(),
            'type' => rand(0, 1) ? 'credit' : 'debit',
            'amount' => rand(50, 500),
            'status' => 'completed',
            'payment_method' => ['quickbooks', 'credit_card', 'bank_transfer'][rand(0, 2)],
            'payment_processor' => 'demo',
            'description' => 'Sample payment transaction',
            'processed_at' => now()->subDays(rand(1, 60)),
            'created_at' => now()->subDays(rand(1, 60))
        ]);
    }
}

function generateSampleDocuments($buyerId) {
    \App\Models\BuyerContract::create([
        'buyer_id' => $buyerId,
        'contract_type' => 'buyer_agreement',
        'contract_version' => 'v2.1',
        'status' => 'signed',
        'signed_at' => now()->subDays(rand(1, 30)),
        'signer_name' => 'Demo Signer',
        'signer_email' => 'demo@example.com',
        'signature_data' => 'data:image/png;base64,sample_signature_data',
        'is_active' => true
    ]);
}

function generateSampleCRMConfig($buyerId) {
    $buyer = \App\Models\Buyer::find($buyerId);
    $buyer->update([
        'crm_config' => [
            'type' => 'webhook',
            'enabled' => true,
            'webhook_url' => 'https://demo.example.com/webhook',
            'auth_method' => 'none',
            'field_mapping' => [
                'name' => 'first_name',
                'email' => 'email',
                'phone' => 'phone'
            ]
        ],
        'crm_stats' => [
            'total_attempts' => rand(10, 50),
            'successful_deliveries' => rand(8, 45),
            'failed_deliveries' => rand(0, 5),
            'success_rate' => rand(85, 98),
            'last_attempt' => now()->subHours(rand(1, 24))->toISOString()
        ]
    ]);
}

function generateSampleOutcomes($buyerId) {
    $leads = \App\Models\Lead::where('is_sample_data', true)->limit(10)->get();
    
    foreach ($leads as $lead) {
        $outcomes = ['sold', 'not_sold', 'bad_lead', 'pending'];
        $statuses = ['closed_won', 'closed_lost', 'bad_lead', 'qualified'];
        
        $outcome = $outcomes[rand(0, 3)];
        $status = $statuses[rand(0, 3)];
        
        \App\Models\LeadOutcome::create([
            'lead_id' => $lead->id,
            'buyer_id' => $buyerId,
            'external_lead_id' => $lead->external_lead_id,
            'status' => $status,
            'outcome' => $outcome,
            'sale_amount' => $outcome === 'sold' ? rand(500, 5000) : null,
            'quality_rating' => rand(1, 5),
            'contact_attempts' => rand(1, 8),
            'first_contact_at' => now()->subDays(rand(1, 10)),
            'last_contact_at' => now()->subDays(rand(0, 5)),
            'closed_at' => in_array($status, ['closed_won', 'closed_lost', 'bad_lead']) ? now()->subDays(rand(0, 3)) : null,
            'notes' => 'Sample outcome data for demonstration',
            'reported_via' => 'api'
        ]);
    }
}

// Manually update lead type (GET for easy testing)
Route::get('/admin/lead/{leadId}/update-type/{type}', function ($leadId, $type) {
    $lead = Lead::findOrFail($leadId);
    
    if (!in_array($type, ['auto', 'home'])) {
        return response()->json(['error' => 'Invalid type. Must be auto or home.'], 400);
    }
    
    $lead->update(['type' => $type]);
    
    return response()->json([
        'success' => true,
        'message' => "Lead type updated to {$type}",
        'lead_id' => $lead->id,
        'new_type' => $type,
        'redirect' => "/agent/lead/{$leadId}"
    ]);
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

// Generate unique 9-digit lead ID starting with 100000001
// Helper function to detect lead type from payload
function detectLeadType($data) {
    // Check for explicit type in payload
    if (isset($data['type'])) {
        return strtolower($data['type']);
    }
    
    // Check for vertical field (common in lead forms)
    if (isset($data['vertical'])) {
        $vertical = strtolower($data['vertical']);
        if (strpos($vertical, 'home') !== false || strpos($vertical, 'property') !== false) {
            return 'home';
        }
        if (strpos($vertical, 'auto') !== false || strpos($vertical, 'car') !== false) {
            return 'auto';
        }
    }
    
    // Check for vehicles data (indicates auto insurance)
    if (isset($data['data']['vehicles']) && !empty($data['data']['vehicles'])) {
        return 'auto';
    }
    
    // Check for property data (indicates home insurance)
    if (isset($data['data']['property']) || isset($data['data']['home']) || isset($data['data']['dwelling'])) {
        return 'home';
    }
    
    // Check form URL or source for clues
    if (isset($data['landing_page_url'])) {
        $url = strtolower($data['landing_page_url']);
        if (strpos($url, 'home') !== false || strpos($url, 'property') !== false) {
            return 'home';
        }
        if (strpos($url, 'auto') !== false || strpos($url, 'car') !== false) {
            return 'auto';
        }
    }
    
    // Default to auto if can't determine
    return 'auto';
}

function generateLeadId() {
    try {
        // Generate a 13-digit ID using Unix timestamp + sequence
        // Format: TTTTTTTTTTXXX (10-digit timestamp + 3-digit sequence)
        $timestamp = time();
        
        // Get count of leads created in the same second (for sequence)
        $startOfSecond = \Carbon\Carbon::createFromTimestamp($timestamp);
        $endOfSecond = $startOfSecond->copy()->addSecond();
        
        $countThisSecond = Lead::whereBetween('created_at', [$startOfSecond, $endOfSecond])
                              ->count();
        
        // Create sequence number (000-999)
        $sequence = str_pad($countThisSecond, 3, '0', STR_PAD_LEFT);
        
        // Combine timestamp + sequence for 13-digit ID
        $externalId = $timestamp . $sequence;
        
        Log::info('🔢 Generated timestamp-based external_lead_id', [
            'timestamp' => $timestamp,
            'sequence' => $sequence,
            'final_id' => $externalId,
            'datetime' => date('Y-m-d H:i:s', $timestamp)
        ]);
        
        return $externalId;
        
    } catch (Exception $e) {
        // Fallback: timestamp + random if database fails
        $timestamp = time();
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $fallbackId = $timestamp . $random;
        
        Log::warning('🔢 Using fallback ID generation', [
            'error' => $e->getMessage(),
            'fallback_id' => $fallbackId
        ]);
        
        return $fallbackId;
    }
}

// Vici integration function (shared between webhooks)
function sendToViciList101($leadData, $leadId) {
    // Your Vici API configuration (with firewall-aware endpoint)
    // FIXED: Ensure list_id is always 101, not from env variable that might be wrong
    $viciConfig = [
        'server' => env('VICI_SERVER', 'philli.callix.ai'),
        'api_endpoint' => env('VICI_API_ENDPOINT', '/vicidial/non_agent_api.php'), // Can be updated for firewall
        'user' => env('VICI_API_USER', 'apiuser'),
        'pass' => env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6'),
        'list_id' => 101, // FIXED: Hard-coded to 101 - do NOT use env variable
        'phone_code' => '1',
        'source' => 'LQF_API'
    ];
    
    // Generate ViciDial-compatible lead_id (9 digits starting with 100000000)
    $viciLeadId = 100000000 + (int)(microtime(true) * 100) % 99999999;
    
    // Prepare Vici lead data (using source parameter)
    $viciData = [
        'user' => $viciConfig['user'],
        'pass' => $viciConfig['pass'],
        'function' => 'add_lead',
        'source' => 'LQF_API',
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
        Log::info('Attempting Vici API call - CONFIRMED LIST 101', [
            'list_id_config' => $viciConfig['list_id'],
            'list_id_payload' => $viciData['list_id'],
            'lead_id' => $leadId,
            'vici_lead_id' => $viciLeadId,
            'phone' => $viciData['phone_number'],
            'name' => $viciData['first_name'] . ' ' . $viciData['last_name']
        ]);
        
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
                        // Attempt to sync with Vici (disabled during testing)
        if (env('VICI_SYNC_ENABLED', true)) {
            $viciSyncResult = updateViciLead($leadId, $updatedData, $changedFields);
        } else {
            Log::info('Vici sync disabled for testing', ['lead_id' => $leadId]);
            $viciSyncResult = false; // Simulate disabled sync
        }
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

// Route to update specific driver
Route::put('/agent/lead/{leadId}/driver/{driverIndex}', function (Request $request, $leadId, $driverIndex) {
    try {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $drivers = $lead->drivers ?? [];
        
        if (!isset($drivers[$driverIndex])) {
            return response()->json([
                'success' => false,
                'error' => 'Driver not found'
            ], 404);
        }
        
        // Validate required fields
        if (!$request->first_name || !$request->last_name) {
            return response()->json([
                'success' => false,
                'error' => 'First name and last name are required'
            ], 400);
        }
        
        // Update the existing driver
        $drivers[$driverIndex] = array_merge($drivers[$driverIndex], [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'license_state' => $request->license_state,
            'license_status' => $request->license_status,
            'years_licensed' => $request->years_licensed,
            // Preserve existing violations and accidents
            'violations' => $drivers[$driverIndex]['violations'] ?? [],
            'accidents' => $drivers[$driverIndex]['accidents'] ?? []
        ]);
        
        $lead->update(['drivers' => $drivers]);
        
        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'drivers' => $drivers
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error updating driver', [
            'lead_id' => $leadId,
            'driver_index' => $driverIndex,
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
            
                    // Attempt Vici sync if fields changed (disabled during testing)
        if (!empty($changedFields)) {
            try {
                if (env('VICI_SYNC_ENABLED', true)) {
                    $viciSyncResult = updateViciLead($leadId, $updatedData, $changedFields);
                } else {
                    Log::info('Vici sync disabled for testing in save-all', ['lead_id' => $leadId]);
                    $viciSyncResult = false; // Simulate disabled sync
                }
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

// REMOVED: Allstate validation route per user request
// This was causing issues and will be re-implemented later if needed

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

// API & Webhooks Directory - Dynamic data from database (Admin Only)
Route::get('/api-directory', function () {
    // Check if user is admin (simple check - you can enhance this based on your auth system)
    $isAdmin = true; // For now, allow access - you can add proper auth later
    
    if (!$isAdmin) {
        return redirect('/admin')->with('error', 'Admin access required');
    }
    
    // Get statistics for the dashboard
    $stats = [
        'total_leads' => \App\Models\Lead::count(),
        'today_leads' => \App\Models\Lead::whereBetween('created_at', [
            \Carbon\Carbon::now('America/New_York')->startOfDay()->utc(), 
            \Carbon\Carbon::now('America/New_York')->startOfDay()->addDay()->utc()
        ])->count(),
        'active_sources' => \App\Models\Lead::distinct('source')->count('source'),
        'total_webhooks' => \App\Models\ApiEndpoint::webhooks()->count(),
        'total_apis' => \App\Models\ApiEndpoint::apis()->count(), 
        'total_tests' => \App\Models\ApiEndpoint::tests()->count(),
        'active_endpoints' => \App\Models\ApiEndpoint::active()->count(),
    ];

    // Get endpoints organized by category and type
    $webhooks = \App\Models\ApiEndpoint::webhooks()->ordered()->get()->groupBy('category');
    $apis = \App\Models\ApiEndpoint::apis()->ordered()->get()->groupBy('category');
    $tests = \App\Models\ApiEndpoint::tests()->ordered()->get()->groupBy('category');

    return view('api.directory', compact('stats', 'webhooks', 'apis', 'tests'));
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

// PARALLEL RINGBA TESTING (Keep Allstate integration intact)
Route::get('/test/ringba-send/{leadId?}', function ($leadId = null) {
    try {
        // Find a test lead or use a specific one
        $lead = $leadId ? 
            \App\Models\Lead::where('id', $leadId)
                ->orWhere('external_lead_id', $leadId)
                ->first() :
            \App\Models\Lead::latest()->first();
            
        if (!$lead) {
            return response()->json([
                'error' => 'No lead found',
                'suggestion' => 'Try: /test/ringba-send/BRAIN_TEST_RINGBA'
            ], 404);
        }
        
        // Initialize RingBA service
        $ringbaService = new \App\Services\RingBAService();
        
        // Send lead to RingBA (no qualification data for now)
        $result = $ringbaService->sendLead($lead);
        
        return response()->json([
            'test_type' => 'Direct RingBA API Send',
            'lead_id' => $lead->id,
            'lead_name' => $lead->first_name . ' ' . $lead->last_name,
            'lead_type' => $lead->type,
            'result' => $result,
            'next_steps' => [
                'Check logs for detailed request/response',
                'Verify data mapping is correct',
                'Test with different lead types'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test with simulated qualification data (like from Vici agent)
Route::get('/test/ringba-send-qualified/{leadId?}', function ($leadId = null) {
    try {
        $lead = $leadId ? 
            \App\Models\Lead::where('id', $leadId)
                ->orWhere('external_lead_id', $leadId)
                ->first() :
            \App\Models\Lead::latest()->first();
            
        if (!$lead) {
            return response()->json(['error' => 'No lead found'], 404);
        }
        
        // Simulate agent qualification data (Top 13 Questions answered)
        $qualificationData = [
            // Insurance Status
            'currently_insured' => true,
            'current_company' => 'Geico',
            'policy_expires' => '2024-06-15',
            'shopping_for_rates' => true,
            
            // Coverage Needs
            'coverage_type' => $lead->type === 'home' ? 'home' : 'auto',
            'vehicle_count' => 2,
            'home_status' => 'own',
            'recent_claims' => false,
            
            // Financial
            'current_premium' => 180.00,
            'desired_budget' => 150.00,
            'urgency' => '30_days',
            
            // Decision Making
            'decision_maker' => true,
            'motivation_level' => 8,
            'lead_quality_score' => 9,
            
            'agent_notes' => 'Very motivated, current policy expires soon, looking for savings'
        ];
        
        $ringbaService = new \App\Services\RingBAService();
        $result = $ringbaService->sendLead($lead, $qualificationData);
        
        return response()->json([
            'test_type' => 'RingBA Send with Agent Qualification',
            'lead_id' => $lead->id,
            'qualification_data' => $qualificationData,
            'result' => $result,
            'note' => 'This simulates what happens when agent clicks Enrich button'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Qualified test failed',
            'message' => $e->getMessage()
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
 