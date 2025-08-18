<?php
/**
 * TURBO LQF Import - Raw SQL, no ORM
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

echo "\n⚡ TURBO LQF IMPORT - RAW SQL VERSION\n";
echo "=====================================\n\n";

// Kill any existing imports
shell_exec("pkill -f 'lqf' 2>/dev/null");
shell_exec("pkill -f 'artisan' 2>/dev/null");

// Get existing count
$before = DB::select("SELECT COUNT(*) as cnt FROM leads WHERE source = 'LQF_BULK'")[0]->cnt;
echo "Starting count: " . number_format($before) . "\n\n";

// Load existing phones into memory (just the numbers, no IDs)
echo "Loading duplicate check data...\n";
$existingPhones = [];
$result = DB::select("SELECT DISTINCT phone FROM leads WHERE phone IS NOT NULL AND phone != ''");
foreach ($result as $row) {
    $existingPhones[$row->phone] = 1;
}
echo "✓ Loaded " . number_format(count($existingPhones)) . " phones\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);

// Map columns by index
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Prepare for batch insert
$values = [];
$imported = 0;
$skipped = 0;
$batchSize = 1000; // Big batches
$startTime = time();

echo "Processing CSV with batch size $batchSize...\n\n";

// Process CSV
while (($row = fgetcsv($handle)) !== FALSE) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Skip invalid or duplicate
    if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Mark as seen
    $existingPhones[$phone] = 1;
    
    // Extract basic fields
    $firstName = $row[$cols['first name']] ?? '';
    $lastName = $row[$cols['last name']] ?? '';
    
    // Get campaign ID from buyer campaign
    $campaignId = 'NULL';
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = "'" . $m[1] . "'";
        }
    }
    
    // Parse data field for JSON
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        parse_str($dataField, $parsedData);
    }
    
    // Create SQL values
    $values[] = sprintf(
        "(%s, 'LQF_BULK', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'auto', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 1, 1, NOW(), NOW())",
        "'" . (microtime(true) * 10000) . "'",
        "'" . pg_escape_string($phone) . "'",
        "'" . pg_escape_string($firstName) . "'",
        "'" . pg_escape_string($lastName) . "'",
        "'" . pg_escape_string($firstName . ' ' . $lastName) . "'",
        !empty($row[$cols['email']]) ? "'" . pg_escape_string($row[$cols['email']]) . "'" : 'NULL',
        !empty($row[$cols['address']]) ? "'" . pg_escape_string($row[$cols['address']]) . "'" : 'NULL',
        !empty($row[$cols['city']]) ? "'" . pg_escape_string($row[$cols['city']]) . "'" : 'NULL',
        !empty($row[$cols['state']]) ? "'" . pg_escape_string($row[$cols['state']]) . "'" : 'NULL',
        !empty($row[$cols['zip code']]) ? "'" . pg_escape_string($row[$cols['zip code']]) . "'" : 'NULL',
        !empty($row[$cols['ip address']]) ? "'" . pg_escape_string($row[$cols['ip address']]) . "'" : 'NULL',
        !empty($row[$cols['lead id']]) ? "'" . pg_escape_string($row[$cols['lead id']]) . "'" : 'NULL',
        !empty($row[$cols['leadid code']]) ? "'" . pg_escape_string($row[$cols['leadid code']]) . "'" : 'NULL',
        !empty($row[$cols['trusted form cert url']]) ? "'" . pg_escape_string($row[$cols['trusted form cert url']]) . "'" : 'NULL',
        !empty($row[$cols['landing page url']]) ? "'" . pg_escape_string($row[$cols['landing page url']]) . "'" : 'NULL',
        !empty($row[$cols['tcpa consent text']]) ? "'" . pg_escape_string($row[$cols['tcpa consent text']]) . "'" : 'NULL',
        !empty($row[$cols['originally created']]) ? "'" . date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) . "'" : 'NOW()',
        !empty($row[$cols['vendor']]) ? "'" . pg_escape_string($row[$cols['vendor']]) . "'" : 'NULL',
        !empty($row[$cols['buyer']]) ? "'" . pg_escape_string($row[$cols['buyer']]) . "'" : 'NULL',
        $campaignId,
        "'" . pg_escape_string(json_encode($parsedData['drivers'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData['vehicles'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData['requested_policy'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData)) . "'",
        "'" . pg_escape_string(json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ])) . "'"
    );
    
    $imported++;
    
    // Insert batch when full
    if (count($values) >= $batchSize) {
        $sql = "INSERT INTO leads (external_lead_id, source, phone, first_name, last_name, name, email, address, city, state, zip_code, ip_address, type, jangle_lead_id, leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text, opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles, current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at) VALUES " . implode(',', $values);
        
        try {
            DB::statement($sql);
            echo "✓ Batch inserted: " . number_format($imported) . " total, " . number_format($skipped) . " skipped\n";
        } catch (\Exception $e) {
            echo "⚠️  Batch failed, continuing...\n";
        }
        
        $values = [];
        
        // Check speed
        $elapsed = time() - $startTime;
        if ($elapsed > 0) {
            $rate = ($imported / $elapsed) * 60;
            echo "   Speed: " . round($rate) . " leads/minute\n";
        }
    }
}

// Insert final batch
if (!empty($values)) {
    $sql = "INSERT INTO leads (external_lead_id, source, phone, first_name, last_name, name, email, address, city, state, zip_code, ip_address, type, jangle_lead_id, leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text, opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles, current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at) VALUES " . implode(',', $values);
    try {
        DB::statement($sql);
    } catch (\Exception $e) {
        echo "⚠️  Final batch failed\n";
    }
}

fclose($handle);

// Final stats
$after = DB::select("SELECT COUNT(*) as cnt FROM leads WHERE source = 'LQF_BULK'")[0]->cnt;
$newLeads = $after - $before;
$elapsed = time() - $startTime;

echo "\n=====================================\n";
echo "✅ TURBO IMPORT COMPLETE!\n";
echo "=====================================\n\n";
echo "New leads imported: " . number_format($newLeads) . "\n";
echo "Total LQF leads: " . number_format($after) . "\n";
echo "Skipped: " . number_format($skipped) . "\n";
echo "Time: " . round($elapsed/60, 1) . " minutes\n";
if ($elapsed > 0) {
    echo "Speed: " . round(($newLeads / $elapsed) * 60) . " leads/minute\n";
}
echo "\n";


/**
 * TURBO LQF Import - Raw SQL, no ORM
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

echo "\n⚡ TURBO LQF IMPORT - RAW SQL VERSION\n";
echo "=====================================\n\n";

// Kill any existing imports
shell_exec("pkill -f 'lqf' 2>/dev/null");
shell_exec("pkill -f 'artisan' 2>/dev/null");

// Get existing count
$before = DB::select("SELECT COUNT(*) as cnt FROM leads WHERE source = 'LQF_BULK'")[0]->cnt;
echo "Starting count: " . number_format($before) . "\n\n";

// Load existing phones into memory (just the numbers, no IDs)
echo "Loading duplicate check data...\n";
$existingPhones = [];
$result = DB::select("SELECT DISTINCT phone FROM leads WHERE phone IS NOT NULL AND phone != ''");
foreach ($result as $row) {
    $existingPhones[$row->phone] = 1;
}
echo "✓ Loaded " . number_format(count($existingPhones)) . " phones\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);

// Map columns by index
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Prepare for batch insert
$values = [];
$imported = 0;
$skipped = 0;
$batchSize = 1000; // Big batches
$startTime = time();

echo "Processing CSV with batch size $batchSize...\n\n";

// Process CSV
while (($row = fgetcsv($handle)) !== FALSE) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Skip invalid or duplicate
    if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Mark as seen
    $existingPhones[$phone] = 1;
    
    // Extract basic fields
    $firstName = $row[$cols['first name']] ?? '';
    $lastName = $row[$cols['last name']] ?? '';
    
    // Get campaign ID from buyer campaign
    $campaignId = 'NULL';
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = "'" . $m[1] . "'";
        }
    }
    
    // Parse data field for JSON
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        parse_str($dataField, $parsedData);
    }
    
    // Create SQL values
    $values[] = sprintf(
        "(%s, 'LQF_BULK', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'auto', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 1, 1, NOW(), NOW())",
        "'" . (microtime(true) * 10000) . "'",
        "'" . pg_escape_string($phone) . "'",
        "'" . pg_escape_string($firstName) . "'",
        "'" . pg_escape_string($lastName) . "'",
        "'" . pg_escape_string($firstName . ' ' . $lastName) . "'",
        !empty($row[$cols['email']]) ? "'" . pg_escape_string($row[$cols['email']]) . "'" : 'NULL',
        !empty($row[$cols['address']]) ? "'" . pg_escape_string($row[$cols['address']]) . "'" : 'NULL',
        !empty($row[$cols['city']]) ? "'" . pg_escape_string($row[$cols['city']]) . "'" : 'NULL',
        !empty($row[$cols['state']]) ? "'" . pg_escape_string($row[$cols['state']]) . "'" : 'NULL',
        !empty($row[$cols['zip code']]) ? "'" . pg_escape_string($row[$cols['zip code']]) . "'" : 'NULL',
        !empty($row[$cols['ip address']]) ? "'" . pg_escape_string($row[$cols['ip address']]) . "'" : 'NULL',
        !empty($row[$cols['lead id']]) ? "'" . pg_escape_string($row[$cols['lead id']]) . "'" : 'NULL',
        !empty($row[$cols['leadid code']]) ? "'" . pg_escape_string($row[$cols['leadid code']]) . "'" : 'NULL',
        !empty($row[$cols['trusted form cert url']]) ? "'" . pg_escape_string($row[$cols['trusted form cert url']]) . "'" : 'NULL',
        !empty($row[$cols['landing page url']]) ? "'" . pg_escape_string($row[$cols['landing page url']]) . "'" : 'NULL',
        !empty($row[$cols['tcpa consent text']]) ? "'" . pg_escape_string($row[$cols['tcpa consent text']]) . "'" : 'NULL',
        !empty($row[$cols['originally created']]) ? "'" . date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) . "'" : 'NOW()',
        !empty($row[$cols['vendor']]) ? "'" . pg_escape_string($row[$cols['vendor']]) . "'" : 'NULL',
        !empty($row[$cols['buyer']]) ? "'" . pg_escape_string($row[$cols['buyer']]) . "'" : 'NULL',
        $campaignId,
        "'" . pg_escape_string(json_encode($parsedData['drivers'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData['vehicles'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData['requested_policy'] ?? [])) . "'",
        "'" . pg_escape_string(json_encode($parsedData)) . "'",
        "'" . pg_escape_string(json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ])) . "'"
    );
    
    $imported++;
    
    // Insert batch when full
    if (count($values) >= $batchSize) {
        $sql = "INSERT INTO leads (external_lead_id, source, phone, first_name, last_name, name, email, address, city, state, zip_code, ip_address, type, jangle_lead_id, leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text, opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles, current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at) VALUES " . implode(',', $values);
        
        try {
            DB::statement($sql);
            echo "✓ Batch inserted: " . number_format($imported) . " total, " . number_format($skipped) . " skipped\n";
        } catch (\Exception $e) {
            echo "⚠️  Batch failed, continuing...\n";
        }
        
        $values = [];
        
        // Check speed
        $elapsed = time() - $startTime;
        if ($elapsed > 0) {
            $rate = ($imported / $elapsed) * 60;
            echo "   Speed: " . round($rate) . " leads/minute\n";
        }
    }
}

// Insert final batch
if (!empty($values)) {
    $sql = "INSERT INTO leads (external_lead_id, source, phone, first_name, last_name, name, email, address, city, state, zip_code, ip_address, type, jangle_lead_id, leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text, opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles, current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at) VALUES " . implode(',', $values);
    try {
        DB::statement($sql);
    } catch (\Exception $e) {
        echo "⚠️  Final batch failed\n";
    }
}

fclose($handle);

// Final stats
$after = DB::select("SELECT COUNT(*) as cnt FROM leads WHERE source = 'LQF_BULK'")[0]->cnt;
$newLeads = $after - $before;
$elapsed = time() - $startTime;

echo "\n=====================================\n";
echo "✅ TURBO IMPORT COMPLETE!\n";
echo "=====================================\n\n";
echo "New leads imported: " . number_format($newLeads) . "\n";
echo "Total LQF leads: " . number_format($after) . "\n";
echo "Skipped: " . number_format($skipped) . "\n";
echo "Time: " . round($elapsed/60, 1) . " minutes\n";
if ($elapsed > 0) {
    echo "Speed: " . round(($newLeads / $elapsed) * 60) . " leads/minute\n";
}
echo "\n";






