<?php

echo "=== VICI BULK UPDATE - EXPORT/IMPORT METHOD ===\n\n";
echo "This approach:\n";
echo "1. Exports all leads from Vici lists to a CSV\n";
echo "2. Matches them with Brain leads by phone\n";
echo "3. Creates an update file\n";
echo "4. Bulk updates via Vici import\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Step 1: Get all Brain leads with their phone numbers and external IDs
echo "Step 1: Loading Brain leads...\n";

$brainLeads = [];
$chunkSize = 5000;
$offset = 0;

while (true) {
    $chunk = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($chunk->isEmpty()) {
        break;
    }
    
    foreach ($chunk as $lead) {
        // Clean phone and use as key for fast lookup
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            // Store by last 10 digits for consistent matching
            $phoneKey = substr($cleanPhone, -10);
            $brainLeads[$phoneKey] = [
                'external_id' => $lead->external_lead_id,
                'name' => $lead->first_name . ' ' . $lead->last_name
            ];
        }
    }
    
    $offset += $chunkSize;
    echo "  Loaded " . count($brainLeads) . " unique phone numbers...\r";
}

echo "\n✅ Loaded " . count($brainLeads) . " Brain leads\n\n";

// Step 2: Export Vici leads and match
echo "Step 2: Exporting and matching Vici leads...\n\n";

// Lists to process
$allLists = [
    // Autodial lists
    6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    10006, 10007, 10008, 10009, 10010, 10011,
    // Auto2 lists
    6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020
];

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

$updateFile = fopen('vici_updates.csv', 'w');
fputcsv($updateFile, ['lead_id', 'list_id', 'vendor_lead_code', 'phone_number', 'action']);

$stats = [
    'total_vici_leads' => 0,
    'matched' => 0,
    'needs_update' => 0,
    'already_has_id' => 0
];

foreach ($allLists as $listId) {
    echo "Processing List $listId...\n";
    
    // Export list via API
    $params = [
        'source' => 'test',
        'user' => $apiUser,
        'pass' => $apiPass,
        'function' => 'list_export',
        'list_id' => $listId,
        'stage' => 'pipe',
        'header' => 'YES'
    ];
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || strpos($response, 'ERROR') !== false) {
        echo "  ⚠️ Could not export list $listId\n";
        continue;
    }
    
    // Parse CSV response
    $lines = explode("\n", $response);
    $header = null;
    $listMatched = 0;
    $listNeedsUpdate = 0;
    
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        $data = str_getcsv($line, '|');
        
        if (!$header) {
            $header = $data;
            continue;
        }
        
        // Map data to header
        $lead = array_combine($header, $data);
        if (!$lead || !isset($lead['lead_id']) || !isset($lead['phone_number'])) {
            continue;
        }
        
        $stats['total_vici_leads']++;
        
        // Clean phone for matching
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead['phone_number']);
        if (strlen($cleanPhone) >= 10) {
            $phoneKey = substr($cleanPhone, -10);
            
            if (isset($brainLeads[$phoneKey])) {
                $stats['matched']++;
                $listMatched++;
                
                $brainId = $brainLeads[$phoneKey]['external_id'];
                $currentVendorCode = $lead['vendor_lead_code'] ?? '';
                
                if ($currentVendorCode === $brainId) {
                    $stats['already_has_id']++;
                } else {
                    $stats['needs_update']++;
                    $listNeedsUpdate++;
                    
                    // Write to update file
                    fputcsv($updateFile, [
                        $lead['lead_id'],
                        $listId,
                        $brainId,
                        $lead['phone_number'],
                        'UPDATE'
                    ]);
                }
            }
        }
    }
    
    echo "  Found $listMatched matches, $listNeedsUpdate need updates\n";
}

fclose($updateFile);

// Step 3: Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== SUMMARY ===\n";
echo str_repeat('=', 60) . "\n";
echo "Brain leads loaded: " . count($brainLeads) . "\n";
echo "Vici leads processed: {$stats['total_vici_leads']}\n";
echo "Matched by phone: {$stats['matched']}\n";
echo "Need update: {$stats['needs_update']}\n";
echo "Already have Brain ID: {$stats['already_has_id']}\n\n";

if ($stats['needs_update'] > 0) {
    echo "✅ Update file created: vici_updates.csv\n";
    echo "   Contains {$stats['needs_update']} leads to update\n\n";
    
    echo "Next steps:\n";
    echo "1. Review the vici_updates.csv file\n";
    echo "2. Use Vici's bulk update feature or API to apply updates\n";
    echo "3. Or run: php apply_vici_updates.php\n";
} else {
    echo "✅ All matched leads already have Brain IDs!\n";
}

// Estimate time saved
$traditionalTime = $stats['total_vici_leads'] * 0.5 / 60; // 0.5 sec per API call
$actualTime = (time() - $_SERVER['REQUEST_TIME']) / 60;

echo "\nTime saved: " . round($traditionalTime - $actualTime, 1) . " minutes\n";
echo "This method is " . round($traditionalTime / max($actualTime, 1), 0) . "x faster!\n";



echo "=== VICI BULK UPDATE - EXPORT/IMPORT METHOD ===\n\n";
echo "This approach:\n";
echo "1. Exports all leads from Vici lists to a CSV\n";
echo "2. Matches them with Brain leads by phone\n";
echo "3. Creates an update file\n";
echo "4. Bulk updates via Vici import\n\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Step 1: Get all Brain leads with their phone numbers and external IDs
echo "Step 1: Loading Brain leads...\n";

$brainLeads = [];
$chunkSize = 5000;
$offset = 0;

while (true) {
    $chunk = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id', 'first_name', 'last_name']);
    
    if ($chunk->isEmpty()) {
        break;
    }
    
    foreach ($chunk as $lead) {
        // Clean phone and use as key for fast lookup
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            // Store by last 10 digits for consistent matching
            $phoneKey = substr($cleanPhone, -10);
            $brainLeads[$phoneKey] = [
                'external_id' => $lead->external_lead_id,
                'name' => $lead->first_name . ' ' . $lead->last_name
            ];
        }
    }
    
    $offset += $chunkSize;
    echo "  Loaded " . count($brainLeads) . " unique phone numbers...\r";
}

echo "\n✅ Loaded " . count($brainLeads) . " Brain leads\n\n";

// Step 2: Export Vici leads and match
echo "Step 2: Exporting and matching Vici leads...\n\n";

// Lists to process
$allLists = [
    // Autodial lists
    6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    10006, 10007, 10008, 10009, 10010, 10011,
    // Auto2 lists
    6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020
];

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

$updateFile = fopen('vici_updates.csv', 'w');
fputcsv($updateFile, ['lead_id', 'list_id', 'vendor_lead_code', 'phone_number', 'action']);

$stats = [
    'total_vici_leads' => 0,
    'matched' => 0,
    'needs_update' => 0,
    'already_has_id' => 0
];

foreach ($allLists as $listId) {
    echo "Processing List $listId...\n";
    
    // Export list via API
    $params = [
        'source' => 'test',
        'user' => $apiUser,
        'pass' => $apiPass,
        'function' => 'list_export',
        'list_id' => $listId,
        'stage' => 'pipe',
        'header' => 'YES'
    ];
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || strpos($response, 'ERROR') !== false) {
        echo "  ⚠️ Could not export list $listId\n";
        continue;
    }
    
    // Parse CSV response
    $lines = explode("\n", $response);
    $header = null;
    $listMatched = 0;
    $listNeedsUpdate = 0;
    
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        $data = str_getcsv($line, '|');
        
        if (!$header) {
            $header = $data;
            continue;
        }
        
        // Map data to header
        $lead = array_combine($header, $data);
        if (!$lead || !isset($lead['lead_id']) || !isset($lead['phone_number'])) {
            continue;
        }
        
        $stats['total_vici_leads']++;
        
        // Clean phone for matching
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead['phone_number']);
        if (strlen($cleanPhone) >= 10) {
            $phoneKey = substr($cleanPhone, -10);
            
            if (isset($brainLeads[$phoneKey])) {
                $stats['matched']++;
                $listMatched++;
                
                $brainId = $brainLeads[$phoneKey]['external_id'];
                $currentVendorCode = $lead['vendor_lead_code'] ?? '';
                
                if ($currentVendorCode === $brainId) {
                    $stats['already_has_id']++;
                } else {
                    $stats['needs_update']++;
                    $listNeedsUpdate++;
                    
                    // Write to update file
                    fputcsv($updateFile, [
                        $lead['lead_id'],
                        $listId,
                        $brainId,
                        $lead['phone_number'],
                        'UPDATE'
                    ]);
                }
            }
        }
    }
    
    echo "  Found $listMatched matches, $listNeedsUpdate need updates\n";
}

fclose($updateFile);

// Step 3: Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== SUMMARY ===\n";
echo str_repeat('=', 60) . "\n";
echo "Brain leads loaded: " . count($brainLeads) . "\n";
echo "Vici leads processed: {$stats['total_vici_leads']}\n";
echo "Matched by phone: {$stats['matched']}\n";
echo "Need update: {$stats['needs_update']}\n";
echo "Already have Brain ID: {$stats['already_has_id']}\n\n";

if ($stats['needs_update'] > 0) {
    echo "✅ Update file created: vici_updates.csv\n";
    echo "   Contains {$stats['needs_update']} leads to update\n\n";
    
    echo "Next steps:\n";
    echo "1. Review the vici_updates.csv file\n";
    echo "2. Use Vici's bulk update feature or API to apply updates\n";
    echo "3. Or run: php apply_vici_updates.php\n";
} else {
    echo "✅ All matched leads already have Brain IDs!\n";
}

// Estimate time saved
$traditionalTime = $stats['total_vici_leads'] * 0.5 / 60; // 0.5 sec per API call
$actualTime = (time() - $_SERVER['REQUEST_TIME']) / 60;

echo "\nTime saved: " . round($traditionalTime - $actualTime, 1) . " minutes\n";
echo "This method is " . round($traditionalTime / max($actualTime, 1), 0) . "x faster!\n";








