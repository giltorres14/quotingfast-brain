<?php

echo "=== SMART VICI UPDATE - PHONE-BASED SEARCH ===\n\n";

// This approach:
// 1. Takes each Brain lead's phone number
// 2. Searches for it ONCE across ALL lists
// 3. Updates it wherever found
// Much faster than checking every lead against every list!

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get parameters
$limit = isset($argv[1]) ? intval($argv[1]) : 100;
$offset = isset($argv[2]) ? intval($argv[2]) : 0;

echo "Processing $limit leads starting from offset $offset\n\n";

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

// Lists to search
$allLists = [
    // Autodial lists
    6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    10006, 10007, 10008, 10009, 10010, 10011,
    // Auto2 lists
    6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020
];

// Function to search all lists for a phone number
function findPhoneInAllLists($phone) {
    global $baseUrl, $apiUser, $apiPass, $allLists;
    
    $found = [];
    
    // Try each list
    foreach ($allLists as $listId) {
        $params = [
            'source' => 'test',
            'user' => $apiUser,
            'pass' => $apiPass,
            'function' => 'search_phone_list',
            'phone_number' => $phone,
            'list_id' => $listId
        ];
        
        $url = $baseUrl . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Check if found
        if ($response && strpos($response, 'ERROR') === false && strpos($response, 'not found') === false) {
            $parts = explode('|', $response);
            if (count($parts) > 3) {
                $found[] = [
                    'list_id' => $listId,
                    'lead_id' => $parts[0],
                    'vendor_lead_code' => $parts[1],
                    'status' => $parts[3] ?? ''
                ];
            }
        }
    }
    
    return $found;
}

// Function to update lead
function updateViciLeadById($leadId, $listId, $vendorLeadCode) {
    global $baseUrl, $apiUser, $apiPass;
    
    $params = [
        'source' => 'test',
        'user' => $apiUser,
        'pass' => $apiPass,
        'function' => 'update_lead',
        'lead_id' => $leadId,
        'list_id' => $listId,
        'vendor_lead_code' => $vendorLeadCode,
        'search_method' => 'LEAD_ID'
    ];
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return strpos($response, 'SUCCESS') !== false || strpos($response, 'NOTICE') !== false;
}

// Get Brain leads
$brainLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->orderBy('id')
    ->offset($offset)
    ->limit($limit)
    ->get(['id', 'phone', 'first_name', 'last_name', 'external_lead_id']);

echo "Processing " . $brainLeads->count() . " Brain leads...\n\n";

$stats = [
    'processed' => 0,
    'found' => 0,
    'updated' => 0,
    'already_had' => 0,
    'in_multiple_lists' => 0
];

$startTime = time();

foreach ($brainLeads as $lead) {
    $stats['processed']++;
    $phone = preg_replace('/[^0-9]/', '', $lead->phone);
    
    echo "[{$stats['processed']}/$limit] {$lead->first_name} {$lead->last_name} ($phone)\n";
    
    // Search all lists for this phone
    $foundIn = findPhoneInAllLists($phone);
    
    if (empty($foundIn)) {
        echo "  ❌ Not found in any Vici list\n";
    } else {
        $stats['found']++;
        
        if (count($foundIn) > 1) {
            $stats['in_multiple_lists']++;
            echo "  📋 Found in " . count($foundIn) . " lists: " . implode(', ', array_column($foundIn, 'list_id')) . "\n";
        }
        
        foreach ($foundIn as $viciLead) {
            echo "  List {$viciLead['list_id']}: ";
            
            if ($viciLead['vendor_lead_code'] == $lead->external_lead_id) {
                echo "✅ Already has Brain ID\n";
                $stats['already_had']++;
            } else {
                if (updateViciLeadById($viciLead['lead_id'], $viciLead['list_id'], $lead->external_lead_id)) {
                    echo "✅ Updated with Brain ID: {$lead->external_lead_id}\n";
                    $stats['updated']++;
                    
                    Log::info('Updated Vici lead', [
                        'brain_id' => $lead->id,
                        'external_id' => $lead->external_lead_id,
                        'vici_lead_id' => $viciLead['lead_id'],
                        'list_id' => $viciLead['list_id']
                    ]);
                } else {
                    echo "❌ Update failed\n";
                }
            }
        }
    }
    
    // Show time estimate every 10 leads
    if ($stats['processed'] % 10 == 0) {
        $elapsed = time() - $startTime;
        $rate = $stats['processed'] / $elapsed;
        $remaining = ($limit - $stats['processed']) / $rate;
        echo "\n  ⏱️ Rate: " . round($rate * 60, 1) . " leads/min | ";
        echo "Est. remaining: " . round($remaining / 60, 1) . " minutes\n\n";
    }
}

$totalTime = time() - $startTime;

echo "\n=== SUMMARY ===\n";
echo "Processed: {$stats['processed']} leads in " . round($totalTime / 60, 1) . " minutes\n";
echo "Found in Vici: {$stats['found']}\n";
echo "Updated: {$stats['updated']}\n";
echo "Already had Brain ID: {$stats['already_had']}\n";
echo "In multiple lists: {$stats['in_multiple_lists']}\n";
echo "Processing rate: " . round($stats['processed'] / $totalTime * 60, 1) . " leads/minute\n";

// Estimate for all leads
$totalLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->count();

$estimatedTotalTime = ($totalLeads / $stats['processed']) * $totalTime;
echo "\nEstimated time for all $totalLeads leads: " . round($estimatedTotalTime / 3600, 1) . " hours\n";

echo "\nTo continue, run: php update_vici_smart.php $limit " . ($offset + $limit) . "\n";
