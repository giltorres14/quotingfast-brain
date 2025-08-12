<?php
/**
 * Import Historical Vici Leads to Brain Database
 * This script imports leads from Vici that don't have Brain IDs yet
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

echo "========================================\n";
echo "VICI HISTORICAL LEADS IMPORT TO BRAIN\n";
echo "========================================\n\n";

// Configuration
$viciConfig = [
    'server' => 'philli.callix.ai',
    'api_endpoint' => '/vicidial/non_agent_api.php',
    'user' => 'apiuser',
    'pass' => 'UZPATJ59GJAVKG8ES6',
    'list_id' => '101'
];

// Function to call Vici API
function callViciApi($params, $config) {
    $url = "https://{$config['server']}{$config['api_endpoint']}";
    
    try {
        $response = Http::timeout(30)
            ->asForm()
            ->post($url, $params);
        
        if ($response->successful()) {
            return $response->body();
        }
    } catch (\Exception $e) {
        // Try HTTP if HTTPS fails
        try {
            $url = "http://{$config['server']}{$config['api_endpoint']}";
            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);
            
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e2) {
            echo "API Error: " . $e2->getMessage() . "\n";
        }
    }
    
    return null;
}

// Step 1: Get count of leads in Vici
echo "📊 Checking Vici for leads in List 101...\n";

$params = [
    'source' => 'brain',
    'user' => $viciConfig['user'],
    'pass' => $viciConfig['pass'],
    'function' => 'lead_search',
    'list_id' => $viciConfig['list_id'],
    'records' => '10' // Get first 10 as a test
];

$response = callViciApi($params, $viciConfig);

if (!$response) {
    echo "❌ Could not connect to Vici API\n";
    echo "Please check connection and try again.\n";
    exit(1);
}

echo "✅ Connected to Vici API\n\n";

// Parse response (Vici returns pipe-delimited data)
$lines = explode("\n", $response);
$leadCount = 0;
$viciLeads = [];

foreach ($lines as $line) {
    if (strpos($line, 'ERROR') !== false) {
        echo "❌ Vici Error: $line\n";
        exit(1);
    }
    
    // Parse lead data (format varies, but typically includes lead_id|phone|name|etc)
    $parts = explode('|', $line);
    if (count($parts) > 5) { // Assuming valid lead data has multiple fields
        $viciLeads[] = $parts;
        $leadCount++;
    }
}

echo "📋 Found $leadCount leads in initial batch\n\n";

// Step 2: Process each lead
echo "🔄 Processing leads...\n\n";

$imported = 0;
$skipped = 0;
$failed = 0;

// For this example, we'll process the test batch
// In production, you'd want to paginate through all leads
foreach ($viciLeads as $leadData) {
    // Vici lead data structure (adjust based on actual response)
    // Typical format: lead_id|list_id|phone_number|first_name|last_name|address|city|state|postal_code|...
    
    if (count($leadData) < 9) {
        echo "⚠️  Skipping invalid lead data\n";
        $skipped++;
        continue;
    }
    
    $viciLeadId = $leadData[0];
    $phone = $leadData[2];
    $firstName = $leadData[3] ?? '';
    $lastName = $leadData[4] ?? '';
    $address = $leadData[5] ?? '';
    $city = $leadData[6] ?? '';
    $state = $leadData[7] ?? '';
    $zip = $leadData[8] ?? '';
    
    // Check if lead already exists in Brain
    $existingLead = Lead::where('external_lead_id', $viciLeadId)->first();
    
    if ($existingLead) {
        echo "⏭️  Lead $viciLeadId already in Brain (ID: {$existingLead->id})\n";
        $skipped++;
        continue;
    }
    
    // Check if phone number already exists
    $phoneExists = Lead::where('phone', $phone)->first();
    if ($phoneExists) {
        echo "⚠️  Phone $phone already exists in Brain (ID: {$phoneExists->id})\n";
        // Update external_lead_id if not set
        if (!$phoneExists->external_lead_id) {
            $phoneExists->external_lead_id = $viciLeadId;
            $phoneExists->save();
            echo "   Updated with Vici ID: $viciLeadId\n";
        }
        $skipped++;
        continue;
    }
    
    // Create new Brain lead
    try {
        $lead = Lead::create([
            'external_lead_id' => $viciLeadId,
            'name' => trim("$firstName $lastName"),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email' => '', // Vici might not have email
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip,
            'source' => 'vici_historical',
            'type' => 'auto', // Default type
            'campaign_id' => 'VICI_IMPORT',
            'payload' => json_encode($leadData) // Store original data
        ]);
        
        echo "✅ Created Brain Lead {$lead->id} for Vici {$viciLeadId}\n";
        echo "   Name: {$lead->name}, Phone: {$lead->phone}\n";
        
        // Now update Vici with Brain ID
        $updateParams = [
            'source' => 'brain',
            'user' => $viciConfig['user'],
            'pass' => $viciConfig['pass'],
            'function' => 'update_lead',
            'lead_id' => $viciLeadId,
            'vendor_lead_code' => "BRAIN_{$lead->id}"
        ];
        
        $updateResponse = callViciApi($updateParams, $viciConfig);
        
        if ($updateResponse && strpos($updateResponse, 'SUCCESS') !== false) {
            echo "   ✅ Updated Vici vendor_lead_code to BRAIN_{$lead->id}\n";
        } else {
            echo "   ⚠️  Could not update Vici vendor_lead_code\n";
        }
        
        $imported++;
        
    } catch (\Exception $e) {
        echo "❌ Failed to create lead for Vici $viciLeadId: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

// Step 3: Summary
echo "========================================\n";
echo "IMPORT COMPLETE\n";
echo "========================================\n\n";

echo "📊 Results:\n";
echo "   ✅ Imported: $imported leads\n";
echo "   ⏭️  Skipped: $skipped leads (already exist)\n";
echo "   ❌ Failed: $failed leads\n\n";

// Step 4: Verify counts
$totalBrainLeads = Lead::count();
$historicalLeads = Lead::where('source', 'vici_historical')->count();
$leadsWithViciId = Lead::whereNotNull('external_lead_id')->count();

echo "📈 Database Status:\n";
echo "   Total Brain Leads: $totalBrainLeads\n";
echo "   Historical Imports: $historicalLeads\n";
echo "   Leads with Vici ID: $leadsWithViciId\n\n";

echo "🎯 Next Steps:\n";
echo "1. Review imported leads in Brain dashboard\n";
echo "2. Run full import for all Vici leads (modify 'records' parameter)\n";
echo "3. Verify vendor_lead_code updates in Vici\n";
echo "4. Update LQF webhook to point to Brain\n\n";

echo "✅ Script complete!\n";


