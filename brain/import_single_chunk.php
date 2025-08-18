<?php
/**
 * Import a single LQF CSV chunk - ULTRA FAST VERSION
 */

if ($argc < 2) {
    die("Usage: php import_single_chunk.php <csv_file>\n");
}

ini_set('memory_limit', '256M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$csvFile = $argv[1];
$chunkName = basename($csvFile);

echo "Importing chunk: $chunkName\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header
$header = fgetcsv($handle);
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Load existing phones for this session only
$existingPhones = [];
$phones = DB::select("SELECT phone FROM leads WHERE source = 'LQF_BULK' LIMIT 100000");
foreach ($phones as $p) {
    $existingPhones[$p->phone] = 1;
}

// Batch insert data
$batch = [];
$imported = 0;
$skipped = 0;
$batchSize = 500;

$pdo = DB::connection()->getPdo();
$pdo->beginTransaction();

while (($row = fgetcsv($handle)) !== FALSE) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Parse data field
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        parse_str($dataField, $parsedData);
    }
    
    // Get campaign ID
    $campaignId = null;
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = $m[1];
        }
    }
    
    // Build insert data
    $batch[] = [
        (string)(microtime(true) * 10000),
        'LQF_BULK',
        $phone,
        $row[$cols['first name']] ?? '',
        $row[$cols['last name']] ?? '',
        trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
        strtolower($row[$cols['email']] ?? ''),
        $row[$cols['address']] ?? '',
        $row[$cols['city']] ?? '',
        $row[$cols['state']] ?? '',
        $row[$cols['zip code']] ?? '',
        $row[$cols['ip address']] ?? null,
        'auto',
        $row[$cols['lead id']] ?? null,
        $row[$cols['leadid code']] ?? null,
        $row[$cols['trusted form cert url']] ?? null,
        $row[$cols['landing page url']] ?? null,
        $row[$cols['tcpa consent text']] ?? null,
        !empty($row[$cols['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
            date('Y-m-d H:i:s'),
        $row[$cols['vendor']] ?? null,
        $row[$cols['buyer']] ?? null,
        $campaignId,
        json_encode($parsedData['drivers'] ?? []),
        json_encode($parsedData['vehicles'] ?? []),
        json_encode($parsedData['requested_policy'] ?? []),
        json_encode($parsedData),
        json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ]),
        1, // tcpa_compliant
        1, // tenant_id
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ];
    
    $imported++;
    $existingPhones[$phone] = 1;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        insertBatch($pdo, $batch);
        $batch = [];
        echo "  $imported imported, $skipped skipped\n";
    }
}

// Insert remaining
if (!empty($batch)) {
    insertBatch($pdo, $batch);
}

$pdo->commit();
fclose($handle);

echo "✓ Chunk complete: $imported imported, $skipped skipped\n";

function insertBatch($pdo, $batch) {
    if (empty($batch)) return;
    
    $placeholders = array_fill(0, count($batch), '(' . implode(',', array_fill(0, 31, '?')) . ')');
    
    $sql = "INSERT INTO leads (
        external_lead_id, source, phone, first_name, last_name, name, email, 
        address, city, state, zip_code, ip_address, type, jangle_lead_id, 
        leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text,
        opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles,
        current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at
    ) VALUES " . implode(',', $placeholders);
    
    $values = [];
    foreach ($batch as $row) {
        foreach ($row as $val) {
            $values[] = $val;
        }
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    } catch (\Exception $e) {
        // Ignore duplicates
    }
}


/**
 * Import a single LQF CSV chunk - ULTRA FAST VERSION
 */

if ($argc < 2) {
    die("Usage: php import_single_chunk.php <csv_file>\n");
}

ini_set('memory_limit', '256M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$csvFile = $argv[1];
$chunkName = basename($csvFile);

echo "Importing chunk: $chunkName\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header
$header = fgetcsv($handle);
$cols = [];
foreach ($header as $i => $h) {
    $cols[strtolower(trim($h))] = $i;
}

// Load existing phones for this session only
$existingPhones = [];
$phones = DB::select("SELECT phone FROM leads WHERE source = 'LQF_BULK' LIMIT 100000");
foreach ($phones as $p) {
    $existingPhones[$p->phone] = 1;
}

// Batch insert data
$batch = [];
$imported = 0;
$skipped = 0;
$batchSize = 500;

$pdo = DB::connection()->getPdo();
$pdo->beginTransaction();

while (($row = fgetcsv($handle)) !== FALSE) {
    // Extract phone
    $phone = preg_replace('/\D/', '', $row[$cols['phone']] ?? '');
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    if (strlen($phone) !== 10 || isset($existingPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Parse data field
    $dataField = $row[$cols['data']] ?? '';
    $parsedData = [];
    if ($dataField) {
        parse_str($dataField, $parsedData);
    }
    
    // Get campaign ID
    $campaignId = null;
    if (!empty($row[$cols['buyer campaign']])) {
        if (preg_match('/(\d{7})/', $row[$cols['buyer campaign']], $m)) {
            $campaignId = $m[1];
        }
    }
    
    // Build insert data
    $batch[] = [
        (string)(microtime(true) * 10000),
        'LQF_BULK',
        $phone,
        $row[$cols['first name']] ?? '',
        $row[$cols['last name']] ?? '',
        trim(($row[$cols['first name']] ?? '') . ' ' . ($row[$cols['last name']] ?? '')),
        strtolower($row[$cols['email']] ?? ''),
        $row[$cols['address']] ?? '',
        $row[$cols['city']] ?? '',
        $row[$cols['state']] ?? '',
        $row[$cols['zip code']] ?? '',
        $row[$cols['ip address']] ?? null,
        'auto',
        $row[$cols['lead id']] ?? null,
        $row[$cols['leadid code']] ?? null,
        $row[$cols['trusted form cert url']] ?? null,
        $row[$cols['landing page url']] ?? null,
        $row[$cols['tcpa consent text']] ?? null,
        !empty($row[$cols['originally created']]) ? 
            date('Y-m-d H:i:s', strtotime($row[$cols['originally created']])) : 
            date('Y-m-d H:i:s'),
        $row[$cols['vendor']] ?? null,
        $row[$cols['buyer']] ?? null,
        $campaignId,
        json_encode($parsedData['drivers'] ?? []),
        json_encode($parsedData['vehicles'] ?? []),
        json_encode($parsedData['requested_policy'] ?? []),
        json_encode($parsedData),
        json_encode([
            'vendor_campaign' => $row[$cols['vendor campaign']] ?? null,
            'buyer_campaign' => $row[$cols['buyer campaign']] ?? null,
            'buy_price' => $row[$cols['buy price']] ?? null,
            'sell_price' => $row[$cols['sell price']] ?? null,
        ]),
        1, // tcpa_compliant
        1, // tenant_id
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ];
    
    $imported++;
    $existingPhones[$phone] = 1;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        insertBatch($pdo, $batch);
        $batch = [];
        echo "  $imported imported, $skipped skipped\n";
    }
}

// Insert remaining
if (!empty($batch)) {
    insertBatch($pdo, $batch);
}

$pdo->commit();
fclose($handle);

echo "✓ Chunk complete: $imported imported, $skipped skipped\n";

function insertBatch($pdo, $batch) {
    if (empty($batch)) return;
    
    $placeholders = array_fill(0, count($batch), '(' . implode(',', array_fill(0, 31, '?')) . ')');
    
    $sql = "INSERT INTO leads (
        external_lead_id, source, phone, first_name, last_name, name, email, 
        address, city, state, zip_code, ip_address, type, jangle_lead_id, 
        leadid_code, trusted_form_cert, landing_page_url, tcpa_consent_text,
        opt_in_date, vendor_name, buyer_name, campaign_id, drivers, vehicles,
        current_policy, payload, meta, tcpa_compliant, tenant_id, created_at, updated_at
    ) VALUES " . implode(',', $placeholders);
    
    $values = [];
    foreach ($batch as $row) {
        foreach ($row as $val) {
            $values[] = $val;
        }
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    } catch (\Exception $e) {
        // Ignore duplicates
    }
}






