<?php
/**
 * FAST LQF Import - Clean and optimized
 * Uses direct SQL inserts with proper JSON encoding
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    die("File not found: $csvFile\n");
}

echo "\n🚀 FAST LQF IMPORT - OPTIMIZED VERSION\n";
echo "========================================\n\n";

// First, kill the slow import
echo "Checking for slow import process...\n";
$pid = trim(shell_exec("ps aux | grep 'artisan lqf:bulk-import' | grep -v grep | awk '{print $2}'"));
if ($pid) {
    echo "Killing slow import (PID: $pid)...\n";
    shell_exec("kill $pid");
    sleep(2);
}

// Pre-load existing phones for duplicate checking
echo "Loading existing phone numbers...\n";
$existingPhones = DB::table('leads')
    ->pluck('id', 'phone')
    ->toArray();
echo "✓ Loaded " . number_format(count($existingPhones)) . " existing phones\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header and map columns
$header = fgetcsv($handle);
$columnMap = [];
foreach ($header as $index => $col) {
    $normalized = strtolower(trim($col));
    $columnMap[$normalized] = $index;
}

// Prepare for batch inserts
$batchSize = 500;
$batch = [];
$imported = 0;
$skipped = 0;
$replaced = 0;
$startTime = microtime(true);
$rowNum = 1;

echo "Processing CSV file...\n";
echo "Batch size: " . $batchSize . " leads\n\n";

// Use prepared statement for faster inserts
$pdo = DB::connection()->getPdo();
$pdo->beginTransaction();

while (($data = fgetcsv($handle)) !== FALSE) {
    $rowNum++;
    
    // Extract and clean phone
    $phone = preg_replace('/\D/', '', $data[$columnMap['phone']] ?? '');
    if (strlen($phone) < 10) {
        $skipped++;
        continue;
    }
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Check for duplicates
    if (isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Extract names
    $firstName = trim($data[$columnMap['first name']] ?? '');
    $lastName = trim($data[$columnMap['last name']] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
    
    // Extract campaign_id from buyer campaign
    $campaignId = null;
    $buyerCampaign = $data[$columnMap['buyer campaign']] ?? '';
    if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
        $campaignId = $matches[1];
    }
    
    // Parse the data field (contains JSON with drivers/vehicles)
    $dataField = $data[$columnMap['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        // Parse URL-encoded data
        parse_str($dataField, $parsedData);
    }
    
    // Extract drivers, vehicles, policy from parsed data
    $drivers = [];
    $vehicles = [];
    $policy = [];
    
    if (!empty($parsedData)) {
        if (isset($parsedData['drivers']) && is_array($parsedData['drivers'])) {
            $drivers = $parsedData['drivers'];
        }
        if (isset($parsedData['vehicles']) && is_array($parsedData['vehicles'])) {
            $vehicles = $parsedData['vehicles'];
        }
        if (isset($parsedData['requested_policy']) && is_array($parsedData['requested_policy'])) {
            $policy = $parsedData['requested_policy'];
        }
    }
    
    // Build lead record
    $lead = [
        'external_lead_id' => (string)(microtime(true) * 10000),
        'source' => 'LQF_BULK',
        'phone' => $phone,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'name' => $fullName,
        'email' => strtolower(trim($data[$columnMap['email']] ?? '')),
        'address' => trim($data[$columnMap['address']] ?? ''),
        'city' => trim($data[$columnMap['city']] ?? ''),
        'state' => trim($data[$columnMap['state']] ?? ''),
        'zip_code' => trim($data[$columnMap['zip code']] ?? ''),
        'ip_address' => $data[$columnMap['ip address']] ?? null,
        'type' => 'auto', // Default to auto
        'jangle_lead_id' => $data[$columnMap['lead id']] ?? null,
        'leadid_code' => $data[$columnMap['leadid code']] ?? null,
        'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
        'landing_page_url' => $data[$columnMap['landing page url']] ?? null,
        'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($data[$columnMap['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($data[$columnMap['originally created']])) : 
            date('Y-m-d H:i:s'),
        'vendor_name' => $data[$columnMap['vendor']] ?? null,
        'buyer_name' => $data[$columnMap['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'drivers' => json_encode($drivers),
        'vehicles' => json_encode($vehicles),
        'current_policy' => json_encode($policy),
        'payload' => json_encode($parsedData),
        'meta' => json_encode([
            'vendor_campaign' => $data[$columnMap['vendor campaign']] ?? null,
            'buyer_campaign' => $data[$columnMap['buyer campaign']] ?? null,
            'buy_price' => $data[$columnMap['buy price']] ?? null,
            'sell_price' => $data[$columnMap['sell price']] ?? null,
            'source_id' => $data[$columnMap['source id']] ?? null,
            'offer_id' => $data[$columnMap['offer id']] ?? null,
        ]),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to batch
    $batch[] = $lead;
    $imported++;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        insertBatch($pdo, $batch);
        $batch = [];
        
        // Report progress
        $elapsed = microtime(true) - $startTime;
        $rate = $imported / ($elapsed / 60);
        echo sprintf(
            "✓ Imported: %s | Rate: %d/min | Skipped: %s | Row: %s\n",
            number_format($imported),
            $rate,
            number_format($skipped),
            number_format($rowNum)
        );
    }
}

// Insert remaining batch
if (!empty($batch)) {
    insertBatch($pdo, $batch);
}

$pdo->commit();
fclose($handle);

// Final stats
$elapsed = microtime(true) - $startTime;
$rate = $imported / ($elapsed / 60);

echo "\n========================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "========================================\n\n";
echo "Imported: " . number_format($imported) . " new leads\n";
echo "Skipped: " . number_format($skipped) . " (duplicates)\n";
echo "Time: " . round($elapsed/60, 1) . " minutes\n";
echo "Speed: " . round($rate) . " leads/minute\n\n";

function insertBatch($pdo, $batch) {
    $sql = "INSERT INTO leads (
        external_lead_id, source, phone, first_name, last_name, name, email, 
        address, city, state, zip_code, ip_address, type, jangle_lead_id, 
        leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text,
        opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles,
        current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at
    ) VALUES ";
    
    $placeholders = [];
    $values = [];
    
    foreach ($batch as $lead) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $values[] = $lead['external_lead_id'];
        $values[] = $lead['source'];
        $values[] = $lead['phone'];
        $values[] = $lead['first_name'];
        $values[] = $lead['last_name'];
        $values[] = $lead['name'];
        $values[] = $lead['email'];
        $values[] = $lead['address'];
        $values[] = $lead['city'];
        $values[] = $lead['state'];
        $values[] = $lead['zip_code'];
        $values[] = $lead['ip_address'];
        $values[] = $lead['type'];
        $values[] = $lead['jangle_lead_id'];
        $values[] = $lead['leadid_code'];
        $values[] = $lead['trusted_form_cert'];
        $values[] = $lead['landing_page_url'];
        $values[] = $lead['tcpa_consent_text'];
        $values[] = $lead['opt_in_date'];
        $values[] = $lead['vendor_name'];
        $values[] = $lead['buyer_name'];
        $values[] = $lead['campaign_id'];
        $values[] = $lead['drivers'];
        $values[] = $lead['vehicles'];
        $values[] = $lead['current_policy'];
        $values[] = $lead['payload'];
        $values[] = $lead['meta'];
        $values[] = $lead['tcpa_compliant'];
        $values[] = $lead['tenant_id'];
        $values[] = $lead['created_at'];
        $values[] = $lead['updated_at'];
    }
    
    $sql .= implode(', ', $placeholders);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}

 * FAST LQF Import - Clean and optimized
 * Uses direct SQL inserts with proper JSON encoding
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    die("File not found: $csvFile\n");
}

echo "\n🚀 FAST LQF IMPORT - OPTIMIZED VERSION\n";
echo "========================================\n\n";

// First, kill the slow import
echo "Checking for slow import process...\n";
$pid = trim(shell_exec("ps aux | grep 'artisan lqf:bulk-import' | grep -v grep | awk '{print $2}'"));
if ($pid) {
    echo "Killing slow import (PID: $pid)...\n";
    shell_exec("kill $pid");
    sleep(2);
}

// Pre-load existing phones for duplicate checking
echo "Loading existing phone numbers...\n";
$existingPhones = DB::table('leads')
    ->pluck('id', 'phone')
    ->toArray();
echo "✓ Loaded " . number_format(count($existingPhones)) . " existing phones\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header and map columns
$header = fgetcsv($handle);
$columnMap = [];
foreach ($header as $index => $col) {
    $normalized = strtolower(trim($col));
    $columnMap[$normalized] = $index;
}

// Prepare for batch inserts
$batchSize = 500;
$batch = [];
$imported = 0;
$skipped = 0;
$replaced = 0;
$startTime = microtime(true);
$rowNum = 1;

echo "Processing CSV file...\n";
echo "Batch size: " . $batchSize . " leads\n\n";

// Use prepared statement for faster inserts
$pdo = DB::connection()->getPdo();
$pdo->beginTransaction();

while (($data = fgetcsv($handle)) !== FALSE) {
    $rowNum++;
    
    // Extract and clean phone
    $phone = preg_replace('/\D/', '', $data[$columnMap['phone']] ?? '');
    if (strlen($phone) < 10) {
        $skipped++;
        continue;
    }
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Check for duplicates
    if (isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Extract names
    $firstName = trim($data[$columnMap['first name']] ?? '');
    $lastName = trim($data[$columnMap['last name']] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
    
    // Extract campaign_id from buyer campaign
    $campaignId = null;
    $buyerCampaign = $data[$columnMap['buyer campaign']] ?? '';
    if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
        $campaignId = $matches[1];
    }
    
    // Parse the data field (contains JSON with drivers/vehicles)
    $dataField = $data[$columnMap['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        // Parse URL-encoded data
        parse_str($dataField, $parsedData);
    }
    
    // Extract drivers, vehicles, policy from parsed data
    $drivers = [];
    $vehicles = [];
    $policy = [];
    
    if (!empty($parsedData)) {
        if (isset($parsedData['drivers']) && is_array($parsedData['drivers'])) {
            $drivers = $parsedData['drivers'];
        }
        if (isset($parsedData['vehicles']) && is_array($parsedData['vehicles'])) {
            $vehicles = $parsedData['vehicles'];
        }
        if (isset($parsedData['requested_policy']) && is_array($parsedData['requested_policy'])) {
            $policy = $parsedData['requested_policy'];
        }
    }
    
    // Build lead record
    $lead = [
        'external_lead_id' => (string)(microtime(true) * 10000),
        'source' => 'LQF_BULK',
        'phone' => $phone,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'name' => $fullName,
        'email' => strtolower(trim($data[$columnMap['email']] ?? '')),
        'address' => trim($data[$columnMap['address']] ?? ''),
        'city' => trim($data[$columnMap['city']] ?? ''),
        'state' => trim($data[$columnMap['state']] ?? ''),
        'zip_code' => trim($data[$columnMap['zip code']] ?? ''),
        'ip_address' => $data[$columnMap['ip address']] ?? null,
        'type' => 'auto', // Default to auto
        'jangle_lead_id' => $data[$columnMap['lead id']] ?? null,
        'leadid_code' => $data[$columnMap['leadid code']] ?? null,
        'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
        'landing_page_url' => $data[$columnMap['landing page url']] ?? null,
        'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($data[$columnMap['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($data[$columnMap['originally created']])) : 
            date('Y-m-d H:i:s'),
        'vendor_name' => $data[$columnMap['vendor']] ?? null,
        'buyer_name' => $data[$columnMap['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'drivers' => json_encode($drivers),
        'vehicles' => json_encode($vehicles),
        'current_policy' => json_encode($policy),
        'payload' => json_encode($parsedData),
        'meta' => json_encode([
            'vendor_campaign' => $data[$columnMap['vendor campaign']] ?? null,
            'buyer_campaign' => $data[$columnMap['buyer campaign']] ?? null,
            'buy_price' => $data[$columnMap['buy price']] ?? null,
            'sell_price' => $data[$columnMap['sell price']] ?? null,
            'source_id' => $data[$columnMap['source id']] ?? null,
            'offer_id' => $data[$columnMap['offer id']] ?? null,
        ]),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to batch
    $batch[] = $lead;
    $imported++;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        insertBatch($pdo, $batch);
        $batch = [];
        
        // Report progress
        $elapsed = microtime(true) - $startTime;
        $rate = $imported / ($elapsed / 60);
        echo sprintf(
            "✓ Imported: %s | Rate: %d/min | Skipped: %s | Row: %s\n",
            number_format($imported),
            $rate,
            number_format($skipped),
            number_format($rowNum)
        );
    }
}

// Insert remaining batch
if (!empty($batch)) {
    insertBatch($pdo, $batch);
}

$pdo->commit();
fclose($handle);

// Final stats
$elapsed = microtime(true) - $startTime;
$rate = $imported / ($elapsed / 60);

echo "\n========================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "========================================\n\n";
echo "Imported: " . number_format($imported) . " new leads\n";
echo "Skipped: " . number_format($skipped) . " (duplicates)\n";
echo "Time: " . round($elapsed/60, 1) . " minutes\n";
echo "Speed: " . round($rate) . " leads/minute\n\n";

function insertBatch($pdo, $batch) {
    $sql = "INSERT INTO leads (
        external_lead_id, source, phone, first_name, last_name, name, email, 
        address, city, state, zip_code, ip_address, type, jangle_lead_id, 
        leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text,
        opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles,
        current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at
    ) VALUES ";
    
    $placeholders = [];
    $values = [];
    
    foreach ($batch as $lead) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $values[] = $lead['external_lead_id'];
        $values[] = $lead['source'];
        $values[] = $lead['phone'];
        $values[] = $lead['first_name'];
        $values[] = $lead['last_name'];
        $values[] = $lead['name'];
        $values[] = $lead['email'];
        $values[] = $lead['address'];
        $values[] = $lead['city'];
        $values[] = $lead['state'];
        $values[] = $lead['zip_code'];
        $values[] = $lead['ip_address'];
        $values[] = $lead['type'];
        $values[] = $lead['jangle_lead_id'];
        $values[] = $lead['leadid_code'];
        $values[] = $lead['trusted_form_cert'];
        $values[] = $lead['landing_page_url'];
        $values[] = $lead['tcpa_consent_text'];
        $values[] = $lead['opt_in_date'];
        $values[] = $lead['vendor_name'];
        $values[] = $lead['buyer_name'];
        $values[] = $lead['campaign_id'];
        $values[] = $lead['drivers'];
        $values[] = $lead['vehicles'];
        $values[] = $lead['current_policy'];
        $values[] = $lead['payload'];
        $values[] = $lead['meta'];
        $values[] = $lead['tcpa_compliant'];
        $values[] = $lead['tenant_id'];
        $values[] = $lead['created_at'];
        $values[] = $lead['updated_at'];
    }
    
    $sql .= implode(', ', $placeholders);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}




