<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "🔍 Searching for leads in ALL Vici lists\n";
echo "=========================================\n\n";

$apiUser = 'apiuser';
$apiPass = env('VICI_API_PASS', 'UZPATJ59GJAVKG8ES6');
$viciServer = 'philli.callix.ai';
$baseUrl = "https://{$viciServer}/vicidial/non_agent_api.php";

// Phone numbers to search for
$phoneNumbers = [
    '8064378907',  // Juan Sustaita
    '2155551234',  // Test number
];

echo "Searching for phone numbers:\n";
foreach ($phoneNumbers as $phone) {
    echo "- {$phone}\n";
}
echo "\n";

// Method 1: Try searching without specifying a list
echo "Method 1: Using search_phone_list (searches all lists):\n";
echo "---------------------------------------------------------\n";

foreach ($phoneNumbers as $phone) {
    $searchParams = [
        'source' => 'brain_search',
        'user' => $apiUser,
        'pass' => $apiPass,
        'function' => 'search_phone_list',
        'phone_number' => $phone,
        'records_to_return' => 10  // Get up to 10 matches
    ];
    
    $url = $baseUrl . '?' . http_build_query($searchParams);
    
    echo "Searching for {$phone}...\n";
    
    try {
        $response = Http::timeout(30)->get($url);
        $responseBody = $response->body();
        
        if (strpos($responseBody, 'RESULTS FOUND') !== false) {
            echo "✅ FOUND! Response:\n";
            // Parse the response to show details
            $lines = explode("\n", $responseBody);
            foreach ($lines as $line) {
                if (strpos($line, 'lead_id:') !== false || strpos($line, 'list_id:') !== false) {
                    echo "  " . trim($line) . "\n";
                }
            }
        } else {
            echo "❌ Not found. Response: " . substr($responseBody, 0, 100) . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Method 2: Check specific lists
echo "\nMethod 2: Checking specific lists:\n";
echo "-----------------------------------\n";

$listsToCheck = ['101', '100', '999', '998', '1001', '1002']; // Common list IDs

foreach ($listsToCheck as $listId) {
    echo "\nChecking List {$listId}:\n";
    
    $exportParams = [
        'source' => 'brain_export',
        'user' => $apiUser,
        'pass' => $apiPass,
        'function' => 'list_export_calls_report',
        'list_id' => $listId,
        'header' => 'NO',
        'rec_fields' => 'lead_id,phone_number,vendor_lead_code,first_name,last_name'
    ];
    
    $url = $baseUrl . '?' . http_build_query($exportParams);
    
    try {
        $response = Http::timeout(60)->get($url);
        $responseBody = $response->body();
        
        if (strlen($responseBody) > 100) {
            // Count total leads in this list
            $lines = explode("\n", $responseBody);
            $leadCount = count(array_filter($lines));
            echo "  List has {$leadCount} leads\n";
            
            // Search for our phone numbers
            foreach ($phoneNumbers as $phone) {
                $found = false;
                foreach ($lines as $line) {
                    if (strpos($line, $phone) !== false) {
                        $fields = str_getcsv($line);
                        echo "  ✅ Found {$phone}: Lead ID: {$fields[0]}, Vendor Code: {$fields[2]}\n";
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    echo "  ❌ {$phone} not in this list\n";
                }
            }
        } else {
            echo "  List appears to be empty or inaccessible\n";
        }
    } catch (\Exception $e) {
        echo "  Error accessing list: " . $e->getMessage() . "\n";
    }
}

// Method 3: Try to get all lists
echo "\n\nMethod 3: Trying to get all available lists:\n";
echo "---------------------------------------------\n";

$listParams = [
    'source' => 'brain',
    'user' => $apiUser,
    'pass' => $apiPass,
    'function' => 'list_info',
    'stage' => 'csv',
    'header' => 'YES'
];

$url = $baseUrl . '?' . http_build_query($listParams);

try {
    $response = Http::timeout(30)->get($url);
    $responseBody = $response->body();
    
    if (strpos($responseBody, 'ERROR') === false && strlen($responseBody) > 50) {
        echo "Available lists:\n";
        $lines = explode("\n", $responseBody);
        foreach (array_slice($lines, 0, 10) as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "Could not retrieve list information\n";
        echo "Response: " . substr($responseBody, 0, 200) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Search complete!\n";
echo "\nNOTE: If leads are not found, they may:\n";
echo "1. Not exist in Vici yet (never pushed)\n";
echo "2. Be in a different campaign\n";
echo "3. Have a different phone format (with/without country code)\n";
echo "4. Have been deleted or archived\n";
