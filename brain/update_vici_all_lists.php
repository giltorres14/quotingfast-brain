<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== UPDATING VICI LEADS WITH BRAIN IDs ===\n";
echo "Processing Autodial and Auto2 campaign lists\n\n";

// All list IDs from both campaigns
$autodialLists = [
    6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    10006, 10007, 10008, 10009, 10010, 10011
];

$auto2Lists = [
    6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020
];

$allLists = array_merge($autodialLists, $auto2Lists);

echo "Total lists to check: " . count($allLists) . "\n";
echo "Autodial lists: " . count($autodialLists) . "\n";
echo "Auto2 lists: " . count($auto2Lists) . "\n\n";

// Get command line arguments
$testMode = in_array('--test', $argv);
$specificList = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--list=') === 0) {
        $specificList = intval(substr($arg, 7));
    }
}

if ($specificList) {
    echo "🎯 Processing only list: $specificList\n\n";
    $allLists = [$specificList];
} elseif ($testMode) {
    echo "🔧 TEST MODE - Will process first 3 lists only\n\n";
    $allLists = array_slice($allLists, 0, 3);
}

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

// Function to search for lead in specific list
function searchLeadInList($phone, $listId) {
    global $baseUrl, $apiUser, $apiPass;
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Parse response
    if (strpos($response, 'ERROR') !== false || strpos($response, 'not found') !== false) {
        return null;
    }
    
    // Response format: lead_id|vendor_lead_code|phone_number|status|...
    $parts = explode('|', $response);
    if (count($parts) > 3) {
        return [
            'lead_id' => $parts[0],
            'vendor_lead_code' => $parts[1],
            'phone_number' => $parts[2],
            'status' => $parts[3] ?? '',
            'raw' => $response
        ];
    }
    
    return null;
}

// Function to update lead via API
function updateLeadInVici($leadId, $listId, $vendorLeadCode) {
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return strpos($response, 'SUCCESS') !== false || strpos($response, 'NOTICE') !== false;
}

// Count Brain leads
echo "Counting Brain leads to match...\n";
$totalBrainLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->count();

echo "Found $totalBrainLeads Brain leads with valid IDs\n";
echo "Will process in chunks of 1000 to avoid memory issues\n\n";

// Track statistics
$stats = [
    'total_brain_leads' => $totalBrainLeads,
    'leads_found' => 0,
    'leads_updated' => 0,
    'already_had_id' => 0,
    'update_failed' => 0,
    'by_list' => []
];

// Process each list
foreach ($allLists as $listId) {
    $campaignName = in_array($listId, $autodialLists) ? 'Autodial' : 'Auto2';
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "Processing List $listId ($campaignName campaign)\n";
    echo str_repeat('=', 50) . "\n";
    
    $listStats = [
        'found' => 0,
        'updated' => 0,
        'already_had' => 0,
        'failed' => 0
    ];
    
    $processed = 0;
    
    // Process Brain leads in chunks
    $chunkSize = 1000;
    $offset = 0;
    
    while ($offset < $totalBrainLeads) {
        $brainLeads = Lead::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNotNull('external_lead_id')
            ->whereRaw("LENGTH(external_lead_id) = 13")
            ->whereRaw("external_lead_id ~ '^[0-9]+$'")
            ->offset($offset)
            ->limit($chunkSize)
            ->get(['id', 'phone', 'first_name', 'last_name', 'external_lead_id']);
        
        if ($brainLeads->isEmpty()) {
            break;
        }
        
        foreach ($brainLeads as $lead) {
        $phone = preg_replace('/[^0-9]/', '', $lead->phone);
        
        // Search for lead in this specific list
        $viciLead = searchLeadInList($phone, $listId);
        
        if ($viciLead) {
            $processed++;
            $listStats['found']++;
            $stats['leads_found']++;
            
            echo "[$processed] Found: {$lead->first_name} {$lead->last_name} ($phone)\n";
            echo "  Vici Lead ID: {$viciLead['lead_id']}\n";
            echo "  Current vendor_lead_code: " . ($viciLead['vendor_lead_code'] ?: 'empty') . "\n";
            
            // Check if already has Brain ID
            if ($viciLead['vendor_lead_code'] == $lead->external_lead_id) {
                echo "  ✅ Already has Brain ID: {$lead->external_lead_id}\n";
                $listStats['already_had']++;
                $stats['already_had_id']++;
            } else {
                // Update with Brain ID
                echo "  Updating to Brain ID: {$lead->external_lead_id}\n";
                
                if (updateLeadInVici($viciLead['lead_id'], $listId, $lead->external_lead_id)) {
                    echo "  ✅ Updated successfully!\n";
                    $listStats['updated']++;
                    $stats['leads_updated']++;
                    
                    // Log the update
                    Log::info('Updated Vici lead with Brain ID', [
                        'brain_lead_id' => $lead->id,
                        'external_lead_id' => $lead->external_lead_id,
                        'vici_lead_id' => $viciLead['lead_id'],
                        'list_id' => $listId,
                        'phone' => $phone
                    ]);
                } else {
                    echo "  ❌ Update failed\n";
                    $listStats['failed']++;
                    $stats['update_failed']++;
                }
            }
            
            // Show progress every 10 leads
            if ($processed % 10 == 0) {
                echo "\n  Progress: $processed leads processed in this list...\n\n";
            }
        }
        }
        
        // Move to next chunk
        $offset += $chunkSize;
        
        // Break early in test mode
        if ($testMode && $offset >= 100) {
            break;
        }
    }
    
    // List summary
    $stats['by_list'][$listId] = $listStats;
    
    echo "\nList $listId Summary:\n";
    echo "  Found: {$listStats['found']}\n";
    echo "  Updated: {$listStats['updated']}\n";
    echo "  Already had Brain ID: {$listStats['already_had']}\n";
    echo "  Failed: {$listStats['failed']}\n";
    
    // Add delay between lists to avoid overwhelming the API
    if ($listId != end($allLists)) {
        echo "\nPausing before next list...\n";
        sleep(2);
    }
}

// Final summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== FINAL SUMMARY ===\n";
echo str_repeat('=', 60) . "\n";
echo "Total Brain leads checked: {$stats['total_brain_leads']}\n";
echo "Total leads found in Vici: {$stats['leads_found']}\n";
echo "✅ Successfully updated: {$stats['leads_updated']}\n";
echo "✓ Already had Brain ID: {$stats['already_had_id']}\n";
echo "❌ Update failed: {$stats['update_failed']}\n";

// Show breakdown by list if any had updates
echo "\nBreakdown by list:\n";
foreach ($stats['by_list'] as $listId => $listStats) {
    if ($listStats['found'] > 0) {
        $campaign = in_array($listId, $autodialLists) ? 'Autodial' : 'Auto2';
        echo "  List $listId ($campaign): ";
        echo "Found {$listStats['found']}, ";
        echo "Updated {$listStats['updated']}, ";
        echo "Already had {$listStats['already_had']}\n";
    }
}

echo "\n=== COMPLETE ===\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== UPDATING VICI LEADS WITH BRAIN IDs ===\n";
echo "Processing Autodial and Auto2 campaign lists\n\n";

// All list IDs from both campaigns
$autodialLists = [
    6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    10006, 10007, 10008, 10009, 10010, 10011
];

$auto2Lists = [
    6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020
];

$allLists = array_merge($autodialLists, $auto2Lists);

echo "Total lists to check: " . count($allLists) . "\n";
echo "Autodial lists: " . count($autodialLists) . "\n";
echo "Auto2 lists: " . count($auto2Lists) . "\n\n";

// Get command line arguments
$testMode = in_array('--test', $argv);
$specificList = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--list=') === 0) {
        $specificList = intval(substr($arg, 7));
    }
}

if ($specificList) {
    echo "🎯 Processing only list: $specificList\n\n";
    $allLists = [$specificList];
} elseif ($testMode) {
    echo "🔧 TEST MODE - Will process first 3 lists only\n\n";
    $allLists = array_slice($allLists, 0, 3);
}

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

// Function to search for lead in specific list
function searchLeadInList($phone, $listId) {
    global $baseUrl, $apiUser, $apiPass;
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Parse response
    if (strpos($response, 'ERROR') !== false || strpos($response, 'not found') !== false) {
        return null;
    }
    
    // Response format: lead_id|vendor_lead_code|phone_number|status|...
    $parts = explode('|', $response);
    if (count($parts) > 3) {
        return [
            'lead_id' => $parts[0],
            'vendor_lead_code' => $parts[1],
            'phone_number' => $parts[2],
            'status' => $parts[3] ?? '',
            'raw' => $response
        ];
    }
    
    return null;
}

// Function to update lead via API
function updateLeadInVici($leadId, $listId, $vendorLeadCode) {
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return strpos($response, 'SUCCESS') !== false || strpos($response, 'NOTICE') !== false;
}

// Count Brain leads
echo "Counting Brain leads to match...\n";
$totalBrainLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->whereRaw("external_lead_id ~ '^[0-9]+$'")
    ->count();

echo "Found $totalBrainLeads Brain leads with valid IDs\n";
echo "Will process in chunks of 1000 to avoid memory issues\n\n";

// Track statistics
$stats = [
    'total_brain_leads' => $totalBrainLeads,
    'leads_found' => 0,
    'leads_updated' => 0,
    'already_had_id' => 0,
    'update_failed' => 0,
    'by_list' => []
];

// Process each list
foreach ($allLists as $listId) {
    $campaignName = in_array($listId, $autodialLists) ? 'Autodial' : 'Auto2';
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "Processing List $listId ($campaignName campaign)\n";
    echo str_repeat('=', 50) . "\n";
    
    $listStats = [
        'found' => 0,
        'updated' => 0,
        'already_had' => 0,
        'failed' => 0
    ];
    
    $processed = 0;
    
    // Process Brain leads in chunks
    $chunkSize = 1000;
    $offset = 0;
    
    while ($offset < $totalBrainLeads) {
        $brainLeads = Lead::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNotNull('external_lead_id')
            ->whereRaw("LENGTH(external_lead_id) = 13")
            ->whereRaw("external_lead_id ~ '^[0-9]+$'")
            ->offset($offset)
            ->limit($chunkSize)
            ->get(['id', 'phone', 'first_name', 'last_name', 'external_lead_id']);
        
        if ($brainLeads->isEmpty()) {
            break;
        }
        
        foreach ($brainLeads as $lead) {
        $phone = preg_replace('/[^0-9]/', '', $lead->phone);
        
        // Search for lead in this specific list
        $viciLead = searchLeadInList($phone, $listId);
        
        if ($viciLead) {
            $processed++;
            $listStats['found']++;
            $stats['leads_found']++;
            
            echo "[$processed] Found: {$lead->first_name} {$lead->last_name} ($phone)\n";
            echo "  Vici Lead ID: {$viciLead['lead_id']}\n";
            echo "  Current vendor_lead_code: " . ($viciLead['vendor_lead_code'] ?: 'empty') . "\n";
            
            // Check if already has Brain ID
            if ($viciLead['vendor_lead_code'] == $lead->external_lead_id) {
                echo "  ✅ Already has Brain ID: {$lead->external_lead_id}\n";
                $listStats['already_had']++;
                $stats['already_had_id']++;
            } else {
                // Update with Brain ID
                echo "  Updating to Brain ID: {$lead->external_lead_id}\n";
                
                if (updateLeadInVici($viciLead['lead_id'], $listId, $lead->external_lead_id)) {
                    echo "  ✅ Updated successfully!\n";
                    $listStats['updated']++;
                    $stats['leads_updated']++;
                    
                    // Log the update
                    Log::info('Updated Vici lead with Brain ID', [
                        'brain_lead_id' => $lead->id,
                        'external_lead_id' => $lead->external_lead_id,
                        'vici_lead_id' => $viciLead['lead_id'],
                        'list_id' => $listId,
                        'phone' => $phone
                    ]);
                } else {
                    echo "  ❌ Update failed\n";
                    $listStats['failed']++;
                    $stats['update_failed']++;
                }
            }
            
            // Show progress every 10 leads
            if ($processed % 10 == 0) {
                echo "\n  Progress: $processed leads processed in this list...\n\n";
            }
        }
        }
        
        // Move to next chunk
        $offset += $chunkSize;
        
        // Break early in test mode
        if ($testMode && $offset >= 100) {
            break;
        }
    }
    
    // List summary
    $stats['by_list'][$listId] = $listStats;
    
    echo "\nList $listId Summary:\n";
    echo "  Found: {$listStats['found']}\n";
    echo "  Updated: {$listStats['updated']}\n";
    echo "  Already had Brain ID: {$listStats['already_had']}\n";
    echo "  Failed: {$listStats['failed']}\n";
    
    // Add delay between lists to avoid overwhelming the API
    if ($listId != end($allLists)) {
        echo "\nPausing before next list...\n";
        sleep(2);
    }
}

// Final summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== FINAL SUMMARY ===\n";
echo str_repeat('=', 60) . "\n";
echo "Total Brain leads checked: {$stats['total_brain_leads']}\n";
echo "Total leads found in Vici: {$stats['leads_found']}\n";
echo "✅ Successfully updated: {$stats['leads_updated']}\n";
echo "✓ Already had Brain ID: {$stats['already_had_id']}\n";
echo "❌ Update failed: {$stats['update_failed']}\n";

// Show breakdown by list if any had updates
echo "\nBreakdown by list:\n";
foreach ($stats['by_list'] as $listId => $listStats) {
    if ($listStats['found'] > 0) {
        $campaign = in_array($listId, $autodialLists) ? 'Autodial' : 'Auto2';
        echo "  List $listId ($campaign): ";
        echo "Found {$listStats['found']}, ";
        echo "Updated {$listStats['updated']}, ";
        echo "Already had {$listStats['already_had']}\n";
    }
}

echo "\n=== COMPLETE ===\n";




