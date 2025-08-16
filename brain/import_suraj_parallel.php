#!/usr/bin/env php
<?php
/**
 * SUPER FAST PARALLEL SURAJ IMPORT
 * Processes multiple CSV files simultaneously for maximum speed
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

// Configure database
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'database'  => 'brain_production',
    'username'  => 'brain_user',
    'password'  => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "========================================\n";
echo "SUPER FAST PARALLEL SURAJ IMPORT\n";
echo "========================================\n\n";

// Get all CSV files
$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$totalFiles = count($files);

echo "📂 Found $totalFiles CSV files\n";
echo "⚡ Using parallel processing for maximum speed\n\n";

// Load existing phones for duplicate checking
echo "📱 Loading existing phone numbers...\n";
$existingPhones = [];
$phoneCount = 0;
Capsule::table('leads')
    ->select('phone')
    ->chunk(10000, function($leads) use (&$existingPhones, &$phoneCount) {
        foreach ($leads as $lead) {
            $existingPhones[$lead->phone] = true;
            $phoneCount++;
        }
    });
echo "   Found " . number_format($phoneCount) . " existing phones\n\n";

// Pre-load vendors, buyers, campaigns
echo "📦 Loading caches...\n";
$vendors = Capsule::table('vendors')->pluck('id', 'name')->toArray();
$buyers = Capsule::table('buyers')->pluck('id', 'name')->toArray();
$campaigns = Capsule::table('campaigns')->pluck('id', 'campaign_id')->toArray();
echo "   Ready!\n\n";

$startTime = microtime(true);
$totalImported = 0;
$totalDuplicates = 0;
$totalErrors = 0;

// Process files in chunks for parallel processing
$fileChunks = array_chunk($files, 5); // Process 5 files at a time
$chunkNumber = 0;

foreach ($fileChunks as $chunk) {
    $chunkNumber++;
    echo "📦 Processing chunk $chunkNumber/" . ceil($totalFiles/5) . "\n";
    
    $processes = [];
    foreach ($chunk as $file) {
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            die("Could not fork process\n");
        } elseif ($pid == 0) {
            // Child process - process single file
            processFile($file, $existingPhones, $vendors, $buyers, $campaigns);
            exit(0);
        } else {
            // Parent process
            $processes[] = $pid;
        }
    }
    
    // Wait for all child processes to complete
    foreach ($processes as $pid) {
        pcntl_waitpid($pid, $status);
    }
}

$duration = round(microtime(true) - $startTime, 2);

// Get final count
$finalCount = Capsule::table('leads')
    ->whereIn('source', ['SURAJ_BULK', 'SURAJ'])
    ->count();

$imported = $finalCount - $phoneCount;

echo "\n========================================\n";
echo "IMPORT COMPLETE\n";
echo "========================================\n";
echo "✅ Imported: " . number_format($imported) . " new leads\n";
echo "📊 Total Suraj leads: " . number_format($finalCount) . "\n";
echo "⏱️  Duration: {$duration} seconds\n";
echo "⚡ Speed: " . round($imported / max($duration, 1), 2) . " leads/second\n";

function processFile($filepath, $existingPhones, $vendors, $buyers, $campaigns) {
    $filename = basename($filepath);
    $fileDate = extractDateFromFilename($filename);
    
    echo "   📄 Processing: $filename\n";
    
    $handle = fopen($filepath, 'r');
    if (!$handle) {
        echo "   ❌ Failed to open file\n";
        return;
    }
    
    // Read headers
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return;
    }
    
    $columnMap = autoMapColumns($headers);
    
    $batch = [];
    $batchSize = 1000;
    $imported = 0;
    $duplicates = 0;
    $now = Carbon::now();
    
    while (($row = fgetcsv($handle)) !== false) {
        // Extract phone
        $phone = extractPhone($row, $columnMap);
        if (!$phone) {
            continue;
        }
        
        // Check duplicate
        if (isset($existingPhones[$phone])) {
            $duplicates++;
            continue;
        }
        
        // Build lead data
        $leadData = buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers, $now);
        
        // Handle vendor/buyer
        handleVendorBuyer($leadData, $vendors, $buyers, $campaigns);
        
        // Add to batch
        $batch[] = $leadData;
        $existingPhones[$phone] = true;
        
        // Insert batch
        if (count($batch) >= $batchSize) {
            insertBatch($batch);
            $imported += count($batch);
            $batch = [];
        }
    }
    
    // Insert remaining
    if (!empty($batch)) {
        insertBatch($batch);
        $imported += count($batch);
    }
    
    fclose($handle);
    echo "   ✅ File complete: $imported imported, $duplicates duplicates\n";
}

function insertBatch($batch) {
    if (empty($batch)) return;
    
    try {
        // Use insertOrIgnore for duplicate safety
        $inserted = Capsule::table('leads')->insertOrIgnore($batch);
    } catch (Exception $e) {
        // Try individual inserts on failure
        foreach ($batch as $data) {
            try {
                Capsule::table('leads')->insertOrIgnore($data);
            } catch (Exception $e2) {
                // Skip
            }
        }
    }
}

function extractPhone($row, $columnMap) {
    $phoneIndex = $columnMap['phone'] ?? null;
    if ($phoneIndex === null || !isset($row[$phoneIndex])) {
        return null;
    }
    
    $phone = preg_replace('/[^0-9]/', '', $row[$phoneIndex]);
    if (strlen($phone) == 10) {
        return $phone;
    }
    if (strlen($phone) == 11 && $phone[0] == '1') {
        return substr($phone, 1);
    }
    
    return null;
}

function autoMapColumns($headers) {
    $map = [];
    foreach ($headers as $index => $header) {
        $normalized = strtolower(trim($header));
        $normalized = str_replace(['_', '-', ' '], '', $normalized);
        
        if (in_array($normalized, ['phone', 'phonenumber', 'phonenum'])) {
            $map['phone'] = $index;
        } elseif ($normalized == 'firstname') {
            $map['first_name'] = $index;
        } elseif ($normalized == 'lastname') {
            $map['last_name'] = $index;
        } elseif ($normalized == 'email') {
            $map['email'] = $index;
        } elseif ($normalized == 'timestamp') {
            $map['timestamp'] = $index;
        } elseif ($normalized == 'buyercampaignid') {
            $map['campaign_id'] = $index;
        } elseif ($normalized == 'buyerid') {
            $map['buyer_id'] = $index;
        } elseif ($normalized == 'buyername') {
            $map['buyer_name'] = $index;
        } elseif ($normalized == 'vendorid') {
            $map['vendor_id'] = $index;
        } elseif ($normalized == 'vendorname') {
            $map['vendor_name'] = $index;
        } elseif ($normalized == 'vendorcampaignid') {
            $map['vendor_campaign_id'] = $index;
        }
    }
    
    return $map;
}

function buildLeadData($row, $columnMap, $phone, $filename, $fileDate, $headers, $now) {
    // Generate 13-digit ID
    $timestamp = round(microtime(true) * 1000);
    $externalLeadId = substr($timestamp . '000', 0, 13);
    
    $leadData = [
        'external_lead_id' => $externalLeadId,
        'phone' => $phone,
        'source' => 'SURAJ_BULK',
        'type' => 'auto',
        'received_at' => $fileDate ? Carbon::parse($fileDate)->format('Y-m-d H:i:s') : $now,
        'joined_at' => $now,
        'tenant_id' => 1,
        'tcpa_compliant' => true,
        'created_at' => $now,
        'updated_at' => $now,
    ];
    
    // Map fields
    $fieldMapping = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'campaign_id' => 'campaign_id',
        'buyer_name' => 'buyer_name',
        'vendor_name' => 'vendor_name',
    ];
    
    foreach ($fieldMapping as $csvField => $dbField) {
        if (isset($columnMap[$csvField])) {
            $value = trim($row[$columnMap[$csvField]] ?? '');
            if ($value !== '') {
                $leadData[$dbField] = $value;
            }
        }
    }
    
    // Build name
    $firstName = $leadData['first_name'] ?? '';
    $lastName = $leadData['last_name'] ?? '';
    $leadData['name'] = trim("$firstName $lastName") ?: 'Unknown';
    
    // Handle opt_in_date from timestamp
    if (isset($columnMap['timestamp'])) {
        $timestamp = trim($row[$columnMap['timestamp']] ?? '');
        if ($timestamp) {
            try {
                $leadData['opt_in_date'] = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Skip
            }
        }
    }
    
    // Store metadata
    $meta = [
        'source_file' => $filename,
        'file_date' => $fileDate,
        'vendor_id' => isset($columnMap['vendor_id']) ? ($row[$columnMap['vendor_id']] ?? null) : null,
        'buyer_id' => isset($columnMap['buyer_id']) ? ($row[$columnMap['buyer_id']] ?? null) : null,
        'vendor_campaign_id' => isset($columnMap['vendor_campaign_id']) ? ($row[$columnMap['vendor_campaign_id']] ?? null) : null,
    ];
    $leadData['meta'] = json_encode($meta);
    
    // Store full row as payload
    $payload = [];
    foreach ($headers as $index => $header) {
        if (isset($row[$index])) {
            $payload[$header] = $row[$index];
        }
    }
    $leadData['payload'] = json_encode($payload);
    
    return $leadData;
}

function handleVendorBuyer(&$leadData, &$vendors, &$buyers, &$campaigns) {
    // Handle vendor
    if (!empty($leadData['vendor_name']) && !isset($vendors[$leadData['vendor_name']])) {
        try {
            $id = Capsule::table('vendors')->insertGetId([
                'name' => $leadData['vendor_name'],
                'active' => true,
                'notes' => 'Auto-created from Suraj import',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $vendors[$leadData['vendor_name']] = $id;
        } catch (Exception $e) {
            // Vendor might exist, try to get it
            $vendor = Capsule::table('vendors')->where('name', $leadData['vendor_name'])->first();
            if ($vendor) {
                $vendors[$leadData['vendor_name']] = $vendor->id;
            }
        }
    }
    
    // Handle buyer
    if (!empty($leadData['buyer_name']) && !isset($buyers[$leadData['buyer_name']])) {
        try {
            $id = Capsule::table('buyers')->insertGetId([
                'name' => $leadData['buyer_name'],
                'active' => true,
                'notes' => 'Auto-created from Suraj import',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $buyers[$leadData['buyer_name']] = $id;
        } catch (Exception $e) {
            // Buyer might exist, try to get it
            $buyer = Capsule::table('buyers')->where('name', $leadData['buyer_name'])->first();
            if ($buyer) {
                $buyers[$leadData['buyer_name']] = $buyer->id;
            }
        }
    }
    
    // Handle campaign
    if (!empty($leadData['campaign_id']) && !isset($campaigns[$leadData['campaign_id']])) {
        try {
            $id = Capsule::table('campaigns')->insertGetId([
                'campaign_id' => $leadData['campaign_id'],
                'name' => 'Campaign ' . $leadData['campaign_id'],
                'display_name' => 'Campaign ' . $leadData['campaign_id'],
                'is_auto_created' => true,
                'status' => 'active',
                'tenant_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $campaigns[$leadData['campaign_id']] = $id;
        } catch (Exception $e) {
            // Campaign might exist
            $campaign = Capsule::table('campaigns')->where('campaign_id', $leadData['campaign_id'])->first();
            if ($campaign) {
                $campaigns[$leadData['campaign_id']] = $campaign->id;
            }
        }
    }
}

function extractDateFromFilename($filename) {
    if (preg_match('/(\d{4})[-_](\d{2})[-_](\d{2})/', $filename, $matches)) {
        return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
    }
    return null;
}


