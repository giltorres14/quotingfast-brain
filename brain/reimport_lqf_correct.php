<?php
/**
 * Re-import LQF with CORRECT JSON handling
 * The Data field contains JSON, not URL-encoded data!
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n🔄 RE-IMPORTING LQF WITH CORRECT JSON\n";
echo "======================================\n\n";

// First, delete the incorrectly imported LQF data
echo "Deleting incorrectly imported LQF leads...\n";
$deleted = DB::table('leads')->where('source', 'LQF_BULK')->delete();
echo "✓ Deleted " . number_format($deleted) . " leads\n\n";

// Now re-import from one chunk as a test
$testFile = '/Users/giltorres/Downloads/lqf_chunks_final/lqf_part_ab.csv';

echo "Testing with: " . basename($testFile) . "\n\n";

$handle = fopen($testFile, 'r');
if (!$handle) {
    die("Cannot open file: $testFile\n");
}

// Get header
$header = fgetcsv($handle);
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Process first 10 records as test
$imported = 0;
$maxTest = 10;

while (($row = fgetcsv($handle)) !== FALSE && $imported < $maxTest) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    if (strlen($phone) !== 10) {
        continue;
    }
    
    // Parse the JSON data field (NOT URL-encoded!)
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        // It's JSON, not URL-encoded!
        $parsedData = json_decode($dataField, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON decode error for phone $phone: " . json_last_error_msg() . "\n";
            continue;
        }
    }
    
    // Get campaign ID
    $campaignId = null;
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = $m[1];
        }
    }
    
    // Extract drivers, vehicles, policy from the parsed JSON
    $drivers = $parsedData['drivers'] ?? [];
    $vehicles = $parsedData['vehicles'] ?? [];
    $policy = $parsedData['requested_policy'] ?? [];
    
    // Insert the lead with CORRECT data
    DB::table('leads')->insert([
        'external_lead_id' => (string)(microtime(true) * 10000),
        'source' => 'LQF_BULK',
        'phone' => $phone,
        'first_name' => $row[$cols['first name']] ?? '',
        'last_name' => $row[$cols['last name']] ?? '',
        'name' => trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
        'email' => strtolower($row[$cols['email']] ?? ''),
        'address' => $row[$cols['address']] ?? '',
        'city' => $row[$cols['city']] ?? '',
        'state' => $row[$cols['state']] ?? '',
        'zip_code' => $row[$cols['zip code']] ?? '',
        'ip_address' => $row[$cols['ip address']] ?? null,
        'type' => 'auto',
        'jangle_lead_id' => $row[$cols['lead id']] ?? null,
        'leadid_code' => $row[$cols['leadid code']] ?? null,
        'trusted_form_cert' => $row[$cols['trusted form cert url']] ?? null,
        'landing_page_url' => $row[$cols['landing page url']] ?? null,
        'tcpa_consent_text' => $row[$cols['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($row[$cols['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
            date('Y-m-d H:i:s'),
        'vendor_name' => $row[$cols['vendor']] ?? null,
        'buyer_name' => $row[$cols['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'drivers' => json_encode($drivers), // Already an array from json_decode
        'vehicles' => json_encode($vehicles), // Already an array from json_decode
        'current_policy' => json_encode($policy), // Already an array from json_decode
        'payload' => json_encode($parsedData), // The full parsed data
        'meta' => json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ]),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $imported++;
    echo "✓ Imported: " . ($row[$cols['first name']] ?? '') . " " . ($row[$cols['last name']] ?? '') . " ($phone)\n";
}

fclose($handle);

echo "\n======================================\n";
echo "✅ TEST IMPORT COMPLETE!\n";
echo "======================================\n\n";
echo "Imported $imported test leads\n\n";

// Verify the data
$testLead = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->first();

if ($testLead) {
    echo "Verifying first lead:\n";
    echo "Name: " . $testLead->name . "\n";
    echo "Phone: " . $testLead->phone . "\n";
    
    $drivers = json_decode($testLead->drivers, true);
    if (!empty($drivers)) {
        echo "Driver data: ✅ Present\n";
        echo "  First driver: " . $drivers[0]['first_name'] . " " . $drivers[0]['last_name'] . "\n";
        echo "  Birth date: " . $drivers[0]['birth_date'] . "\n";
    } else {
        echo "Driver data: ❌ Missing\n";
    }
    
    $vehicles = json_decode($testLead->vehicles, true);
    if (!empty($vehicles)) {
        echo "Vehicle data: ✅ Present\n";
        echo "  First vehicle: " . $vehicles[0]['year'] . " " . $vehicles[0]['make'] . " " . $vehicles[0]['model'] . "\n";
    } else {
        echo "Vehicle data: ❌ Missing\n";
    }
    
    echo "\nIf this looks correct, we can re-import all chunks with the fixed logic!\n";
}


/**
 * Re-import LQF with CORRECT JSON handling
 * The Data field contains JSON, not URL-encoded data!
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n🔄 RE-IMPORTING LQF WITH CORRECT JSON\n";
echo "======================================\n\n";

// First, delete the incorrectly imported LQF data
echo "Deleting incorrectly imported LQF leads...\n";
$deleted = DB::table('leads')->where('source', 'LQF_BULK')->delete();
echo "✓ Deleted " . number_format($deleted) . " leads\n\n";

// Now re-import from one chunk as a test
$testFile = '/Users/giltorres/Downloads/lqf_chunks_final/lqf_part_ab.csv';

echo "Testing with: " . basename($testFile) . "\n\n";

$handle = fopen($testFile, 'r');
if (!$handle) {
    die("Cannot open file: $testFile\n");
}

// Get header
$header = fgetcsv($handle);
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Process first 10 records as test
$imported = 0;
$maxTest = 10;

while (($row = fgetcsv($handle)) !== FALSE && $imported < $maxTest) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    if (strlen($phone) !== 10) {
        continue;
    }
    
    // Parse the JSON data field (NOT URL-encoded!)
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        // It's JSON, not URL-encoded!
        $parsedData = json_decode($dataField, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON decode error for phone $phone: " . json_last_error_msg() . "\n";
            continue;
        }
    }
    
    // Get campaign ID
    $campaignId = null;
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = $m[1];
        }
    }
    
    // Extract drivers, vehicles, policy from the parsed JSON
    $drivers = $parsedData['drivers'] ?? [];
    $vehicles = $parsedData['vehicles'] ?? [];
    $policy = $parsedData['requested_policy'] ?? [];
    
    // Insert the lead with CORRECT data
    DB::table('leads')->insert([
        'external_lead_id' => (string)(microtime(true) * 10000),
        'source' => 'LQF_BULK',
        'phone' => $phone,
        'first_name' => $row[$cols['first name']] ?? '',
        'last_name' => $row[$cols['last name']] ?? '',
        'name' => trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
        'email' => strtolower($row[$cols['email']] ?? ''),
        'address' => $row[$cols['address']] ?? '',
        'city' => $row[$cols['city']] ?? '',
        'state' => $row[$cols['state']] ?? '',
        'zip_code' => $row[$cols['zip code']] ?? '',
        'ip_address' => $row[$cols['ip address']] ?? null,
        'type' => 'auto',
        'jangle_lead_id' => $row[$cols['lead id']] ?? null,
        'leadid_code' => $row[$cols['leadid code']] ?? null,
        'trusted_form_cert' => $row[$cols['trusted form cert url']] ?? null,
        'landing_page_url' => $row[$cols['landing page url']] ?? null,
        'tcpa_consent_text' => $row[$cols['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($row[$cols['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
            date('Y-m-d H:i:s'),
        'vendor_name' => $row[$cols['vendor']] ?? null,
        'buyer_name' => $row[$cols['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'drivers' => json_encode($drivers), // Already an array from json_decode
        'vehicles' => json_encode($vehicles), // Already an array from json_decode
        'current_policy' => json_encode($policy), // Already an array from json_decode
        'payload' => json_encode($parsedData), // The full parsed data
        'meta' => json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ]),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $imported++;
    echo "✓ Imported: " . ($row[$cols['first name']] ?? '') . " " . ($row[$cols['last name']] ?? '') . " ($phone)\n";
}

fclose($handle);

echo "\n======================================\n";
echo "✅ TEST IMPORT COMPLETE!\n";
echo "======================================\n\n";
echo "Imported $imported test leads\n\n";

// Verify the data
$testLead = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->first();

if ($testLead) {
    echo "Verifying first lead:\n";
    echo "Name: " . $testLead->name . "\n";
    echo "Phone: " . $testLead->phone . "\n";
    
    $drivers = json_decode($testLead->drivers, true);
    if (!empty($drivers)) {
        echo "Driver data: ✅ Present\n";
        echo "  First driver: " . $drivers[0]['first_name'] . " " . $drivers[0]['last_name'] . "\n";
        echo "  Birth date: " . $drivers[0]['birth_date'] . "\n";
    } else {
        echo "Driver data: ❌ Missing\n";
    }
    
    $vehicles = json_decode($testLead->vehicles, true);
    if (!empty($vehicles)) {
        echo "Vehicle data: ✅ Present\n";
        echo "  First vehicle: " . $vehicles[0]['year'] . " " . $vehicles[0]['make'] . " " . $vehicles[0]['model'] . "\n";
    } else {
        echo "Vehicle data: ❌ Missing\n";
    }
    
    echo "\nIf this looks correct, we can re-import all chunks with the fixed logic!\n";
}






