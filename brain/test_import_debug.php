<?php

// Debug the import issue
require_once 'vendor/autoload.php';

use App\Models\Lead;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force PostgreSQL connection
config(['database.default' => 'pgsql']);
config(['database.connections.pgsql' => [
    'driver' => 'pgsql',
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'port' => 5432,
    'database' => 'brain_production',
    'username' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ',
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'schema' => 'public',
    'sslmode' => 'prefer',
]]);

// Test file
$file = '/Users/giltorres/Downloads/Suraj Leads/11jun_auto_lead.csv';

echo "Testing import for: " . basename($file) . "\n";
echo "Current SURAJ_BULK leads: " . Lead::where('source', 'SURAJ_BULK')->count() . "\n\n";

// Open file
$handle = fopen($file, 'r');
$headers = fgetcsv($handle, 0, ',', '"', '\\');

// Auto-map columns
$columnMap = [];
$mappings = [
    'phone' => ['phonenumber', 'phone_number', 'phone'],
    'first_name' => ['firstname', 'first_name'],
    'last_name' => ['lastname', 'last_name'],
    'email' => ['emailaddress', 'email_address', 'email'],
    'campaign_id' => ['buyer_campaign_id'],
    'buyer_name' => ['buyer_name'],
    'vendor_name' => ['vendor_name'],
];

foreach ($headers as $index => $header) {
    $headerLower = strtolower(trim($header));
    foreach ($mappings as $field => $variations) {
        if (in_array($headerLower, $variations)) {
            $columnMap[$field] = $index;
            break;
        }
    }
}

echo "Column mapping:\n";
foreach ($columnMap as $field => $index) {
    echo "  $field => column $index ({$headers[$index]})\n";
}
echo "\n";

// Process first 5 rows
$rowCount = 0;
$inserted = 0;
$skipped = 0;

while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $rowCount < 5) {
    $rowCount++;
    
    // Get phone
    if (!isset($columnMap['phone'])) {
        echo "Row $rowCount: No phone column found\n";
        continue;
    }
    
    $phone = preg_replace('/[^0-9]/', '', $row[$columnMap['phone']] ?? '');
    if (strlen($phone) != 10) {
        echo "Row $rowCount: Invalid phone: $phone\n";
        $skipped++;
        continue;
    }
    
    // Check if exists
    $exists = Lead::where('phone', $phone)->exists();
    if ($exists) {
        echo "Row $rowCount: Phone $phone already exists - SKIPPING\n";
        $skipped++;
        continue;
    }
    
    // Build lead data
    $leadData = [
        'phone' => $phone,
        'source' => 'SURAJ_BULK',
        'type' => 'auto',
        'external_lead_id' => Lead::generateExternalLeadId(),
        'tcpa_compliant' => true,
        'tenant_id' => 1,
        'name' => trim(($row[$columnMap['first_name']] ?? '') . ' ' . ($row[$columnMap['last_name']] ?? '')),
        'first_name' => $row[$columnMap['first_name']] ?? null,
        'last_name' => $row[$columnMap['last_name']] ?? null,
        'email' => $row[$columnMap['email']] ?? null,
        'campaign_id' => $row[$columnMap['campaign_id']] ?? null,
        'buyer_name' => $row[$columnMap['buyer_name']] ?? null,
        'vendor_name' => $row[$columnMap['vendor_name']] ?? null,
        'payload' => json_encode($row),
        'meta' => json_encode(['test_import' => true])
    ];
    
    echo "Row $rowCount: Attempting to insert phone $phone...\n";
    echo "  Data: " . json_encode(array_filter($leadData, function($v) { return !is_null($v) && $v !== ''; })) . "\n";
    
    try {
        $lead = Lead::create($leadData);
        echo "  SUCCESS: Created lead ID {$lead->id}\n";
        $inserted++;
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

fclose($handle);

echo "\n========== RESULTS ==========\n";
echo "Processed: $rowCount rows\n";
echo "Inserted: $inserted\n";
echo "Skipped: $skipped\n";
echo "Final SURAJ_BULK leads: " . Lead::where('source', 'SURAJ_BULK')->count() . "\n";
