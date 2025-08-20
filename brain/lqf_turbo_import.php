<?php
/**
 * TURBO LQF Import - Optimized for SPEED
 * Target: 1000+ leads per minute
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$csvFile = $_SERVER['argv'][1] ?? '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    die("File not found: $csvFile\n");
}

echo "\n🚀 TURBO LQF IMPORT - MAXIMUM SPEED\n";
echo "=====================================\n\n";

// Pre-load existing phone numbers (but only non-LQF to save memory)
echo "Loading existing phone numbers into memory...\n";
$existingPhones = DB::table('leads')
    ->where('source', '!=', 'LQF_BULK')
    ->pluck('source', 'phone')
    ->toArray();
    
// Also get a count of LQF phones separately for skipping
$existingLqfPhones = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
    
echo "✓ Loaded " . number_format(count($existingPhones)) . " non-LQF phones\n";
echo "✓ Found " . number_format(count($existingLqfPhones)) . " existing LQF phones to skip\n\n";

// Count existing LQF leads
$existingLqfCount = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->count();
echo "Starting count: " . number_format($existingLqfCount) . " LQF_BULK leads\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header
$header = fgetcsv($handle);
if (!$header) {
    die("Cannot read CSV header\n");
}

// Map columns (normalize to lowercase like the original import)
$columnMap = [];
foreach ($header as $index => $col) {
    $normalized = strtolower(trim($col));
    $columnMap[$normalized] = $index;
}

// Process in batches (smaller to avoid memory issues)
$batchSize = 500;
$batch = [];
$imported = 0;
$skipped = 0;
$replaced = 0;
$startTime = microtime(true);
$lastReport = time();

echo "Processing CSV file...\n";
echo "Batch size: " . number_format($batchSize) . " leads\n\n";

while (($data = fgetcsv($handle)) !== FALSE) {
    // Extract phone (use lowercase column name)
    $phone = preg_replace('/\D/', '', $data[$columnMap['phone']] ?? '');
    if (strlen($phone) < 10) {
        $skipped++;
        continue;
    }
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Check if already imported as LQF
    if (isset($existingLqfPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Check if exists as other source (for potential replacement)
    if (isset($existingPhones[$phone])) {
        // Skip non-Suraj duplicates
        if (strpos($existingPhones[$phone], 'SURAJ') === false) {
            $skipped++;
            continue;
        }
        // For Suraj, we'll replace - but skip for now to focus on new imports
        $skipped++;
        continue;
    }
    
    // Parse payload (column is "data" in lowercase)
    $payload = [];
    $payloadStr = $data[$columnMap['data']] ?? '';
    if ($payloadStr) {
        parse_str($payloadStr, $payload);
    }
    
    // Extract first and last name
    $firstName = trim($data[$columnMap['first name']] ?? '');
    $lastName = trim($data[$columnMap['last name']] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
    
    // Extract campaign_id from buyer campaign string (like original)
    $campaignId = null;
    $buyerCampaign = $data[$columnMap['buyer campaign']] ?? '';
    if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
        $campaignId = $matches[1];
    }
    
    // Extract lead type from vertical
    $vertical = strtolower($data[$columnMap['vertical']] ?? 'auto');
    $leadType = 'auto';
    if (strpos($vertical, 'home') !== false) $leadType = 'home';
    elseif (strpos($vertical, 'health') !== false) $leadType = 'health';
    elseif (strpos($vertical, 'life') !== false) $leadType = 'life';
    
    // Build lead record (matching original import exactly)
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
        'type' => $leadType,
        'leadid_code' => $data[$columnMap['leadid code']] ?? null,
        'jangle_lead_id' => $data[$columnMap['lead id']] ?? null,
        'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
        'landing_page_url' => $data[$columnMap['landing page url']] ?? null,
        'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($data[$columnMap['originally created']]) ? 
            Carbon::parse($data[$columnMap['originally created']])->format('Y-m-d H:i:s') : 
            now()->format('Y-m-d H:i:s'),
        'vendor_name' => $data[$columnMap['vendor']] ?? null,
        'buyer_name' => $data[$columnMap['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'meta' => json_encode([
            'lead_id' => $data[$columnMap['lead id']] ?? null,
            'vendor_campaign' => $data[$columnMap['vendor campaign']] ?? null,
            'vendor_status' => $data[$columnMap['vendor status']] ?? null,
            'buyer_campaign' => $data[$columnMap['buyer campaign']] ?? null,
            'buyer_status' => $data[$columnMap['buyer status']] ?? null,
            'buy_price' => $data[$columnMap['buy price']] ?? null,
            'sell_price' => $data[$columnMap['sell price']] ?? null,
            'source_id' => $data[$columnMap['source id']] ?? null,
            'offer_id' => $data[$columnMap['offer id']] ?? null,
            'leadid_code' => $data[$columnMap['leadid code']] ?? null,
            'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
            'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
            'landing_page' => $data[$columnMap['landing page url']] ?? null,
            'ip_address' => $data[$columnMap['ip address']] ?? null,
            'user_agent' => $data[$columnMap['user agent']] ?? null,
            'import_source' => 'LQF Bulk Import',
            'import_date' => now()->toISOString()
        ]),
        'payload' => json_encode($payload),
        'drivers' => json_encode($payload['drivers'] ?? []),
        'vehicles' => json_encode($payload['vehicles'] ?? []),
        'current_policy' => json_encode($payload['current_policy'] ?? []),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => now()->format('Y-m-d H:i:s'),
        'updated_at' => now()->format('Y-m-d H:i:s')
    ];
    
    // Add to batch
    $batch[] = $lead;
    $imported++;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        DB::table('leads')->insert($batch);
        $batch = [];
        
        // Report progress every 5 seconds
        if (time() - $lastReport >= 5) {
            $elapsed = microtime(true) - $startTime;
            $rate = $imported / ($elapsed / 60);
            echo sprintf(
                "✓ Imported: %s | Rate: %d/min | Skipped: %s | Time: %ds\n",
                number_format($imported),
                $rate,
                number_format($skipped),
                (int)$elapsed
            );
            $lastReport = time();
        }
    }
}

// Insert remaining batch
if (!empty($batch)) {
    DB::table('leads')->insert($batch);
}

fclose($handle);

// Final stats
$elapsed = microtime(true) - $startTime;
$rate = $imported / ($elapsed / 60);
$finalCount = DB::table('leads')->where('source', 'LQF_BULK')->count();
$actualImported = $finalCount - $existingLqfCount;

echo "\n=====================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "=====================================\n\n";
echo "Imported: " . number_format($actualImported) . " new leads\n";
echo "Skipped: " . number_format($skipped) . " (duplicates/invalid)\n";
echo "Total LQF_BULK: " . number_format($finalCount) . " leads\n";
echo "Time: " . round($elapsed, 1) . " seconds\n";
echo "Speed: " . round($rate) . " leads/minute\n\n";

if ($replaced > 0) {
    echo "Note: Found $replaced Suraj leads to replace.\n";
    echo "Run separate update script to replace them.\n";
}

 * TURBO LQF Import - Optimized for SPEED
 * Target: 1000+ leads per minute
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$csvFile = $_SERVER['argv'][1] ?? '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    die("File not found: $csvFile\n");
}

echo "\n🚀 TURBO LQF IMPORT - MAXIMUM SPEED\n";
echo "=====================================\n\n";

// Pre-load existing phone numbers (but only non-LQF to save memory)
echo "Loading existing phone numbers into memory...\n";
$existingPhones = DB::table('leads')
    ->where('source', '!=', 'LQF_BULK')
    ->pluck('source', 'phone')
    ->toArray();
    
// Also get a count of LQF phones separately for skipping
$existingLqfPhones = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->pluck('phone')
    ->flip()
    ->toArray();
    
echo "✓ Loaded " . number_format(count($existingPhones)) . " non-LQF phones\n";
echo "✓ Found " . number_format(count($existingLqfPhones)) . " existing LQF phones to skip\n\n";

// Count existing LQF leads
$existingLqfCount = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->count();
echo "Starting count: " . number_format($existingLqfCount) . " LQF_BULK leads\n\n";

// Open CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Get header
$header = fgetcsv($handle);
if (!$header) {
    die("Cannot read CSV header\n");
}

// Map columns (normalize to lowercase like the original import)
$columnMap = [];
foreach ($header as $index => $col) {
    $normalized = strtolower(trim($col));
    $columnMap[$normalized] = $index;
}

// Process in batches (smaller to avoid memory issues)
$batchSize = 500;
$batch = [];
$imported = 0;
$skipped = 0;
$replaced = 0;
$startTime = microtime(true);
$lastReport = time();

echo "Processing CSV file...\n";
echo "Batch size: " . number_format($batchSize) . " leads\n\n";

while (($data = fgetcsv($handle)) !== FALSE) {
    // Extract phone (use lowercase column name)
    $phone = preg_replace('/\D/', '', $data[$columnMap['phone']] ?? '');
    if (strlen($phone) < 10) {
        $skipped++;
        continue;
    }
    if (strlen($phone) === 11 && $phone[0] === '1') {
        $phone = substr($phone, 1);
    }
    
    // Check if already imported as LQF
    if (isset($existingLqfPhones[$phone])) {
        $skipped++;
        continue;
    }
    
    // Check if exists as other source (for potential replacement)
    if (isset($existingPhones[$phone])) {
        // Skip non-Suraj duplicates
        if (strpos($existingPhones[$phone], 'SURAJ') === false) {
            $skipped++;
            continue;
        }
        // For Suraj, we'll replace - but skip for now to focus on new imports
        $skipped++;
        continue;
    }
    
    // Parse payload (column is "data" in lowercase)
    $payload = [];
    $payloadStr = $data[$columnMap['data']] ?? '';
    if ($payloadStr) {
        parse_str($payloadStr, $payload);
    }
    
    // Extract first and last name
    $firstName = trim($data[$columnMap['first name']] ?? '');
    $lastName = trim($data[$columnMap['last name']] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
    
    // Extract campaign_id from buyer campaign string (like original)
    $campaignId = null;
    $buyerCampaign = $data[$columnMap['buyer campaign']] ?? '';
    if ($buyerCampaign && preg_match('/(\d{7})/', $buyerCampaign, $matches)) {
        $campaignId = $matches[1];
    }
    
    // Extract lead type from vertical
    $vertical = strtolower($data[$columnMap['vertical']] ?? 'auto');
    $leadType = 'auto';
    if (strpos($vertical, 'home') !== false) $leadType = 'home';
    elseif (strpos($vertical, 'health') !== false) $leadType = 'health';
    elseif (strpos($vertical, 'life') !== false) $leadType = 'life';
    
    // Build lead record (matching original import exactly)
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
        'type' => $leadType,
        'leadid_code' => $data[$columnMap['leadid code']] ?? null,
        'jangle_lead_id' => $data[$columnMap['lead id']] ?? null,
        'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
        'landing_page_url' => $data[$columnMap['landing page url']] ?? null,
        'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
        'opt_in_date' => !empty($data[$columnMap['originally created']]) ? 
            Carbon::parse($data[$columnMap['originally created']])->format('Y-m-d H:i:s') : 
            now()->format('Y-m-d H:i:s'),
        'vendor_name' => $data[$columnMap['vendor']] ?? null,
        'buyer_name' => $data[$columnMap['buyer']] ?? null,
        'campaign_id' => $campaignId,
        'meta' => json_encode([
            'lead_id' => $data[$columnMap['lead id']] ?? null,
            'vendor_campaign' => $data[$columnMap['vendor campaign']] ?? null,
            'vendor_status' => $data[$columnMap['vendor status']] ?? null,
            'buyer_campaign' => $data[$columnMap['buyer campaign']] ?? null,
            'buyer_status' => $data[$columnMap['buyer status']] ?? null,
            'buy_price' => $data[$columnMap['buy price']] ?? null,
            'sell_price' => $data[$columnMap['sell price']] ?? null,
            'source_id' => $data[$columnMap['source id']] ?? null,
            'offer_id' => $data[$columnMap['offer id']] ?? null,
            'leadid_code' => $data[$columnMap['leadid code']] ?? null,
            'trusted_form_cert' => $data[$columnMap['trusted form cert url']] ?? null,
            'tcpa_consent_text' => $data[$columnMap['tcpa consent text']] ?? null,
            'landing_page' => $data[$columnMap['landing page url']] ?? null,
            'ip_address' => $data[$columnMap['ip address']] ?? null,
            'user_agent' => $data[$columnMap['user agent']] ?? null,
            'import_source' => 'LQF Bulk Import',
            'import_date' => now()->toISOString()
        ]),
        'payload' => json_encode($payload),
        'drivers' => json_encode($payload['drivers'] ?? []),
        'vehicles' => json_encode($payload['vehicles'] ?? []),
        'current_policy' => json_encode($payload['current_policy'] ?? []),
        'tcpa_compliant' => 1,
        'tenant_id' => 1,
        'created_at' => now()->format('Y-m-d H:i:s'),
        'updated_at' => now()->format('Y-m-d H:i:s')
    ];
    
    // Add to batch
    $batch[] = $lead;
    $imported++;
    
    // Insert batch when full
    if (count($batch) >= $batchSize) {
        DB::table('leads')->insert($batch);
        $batch = [];
        
        // Report progress every 5 seconds
        if (time() - $lastReport >= 5) {
            $elapsed = microtime(true) - $startTime;
            $rate = $imported / ($elapsed / 60);
            echo sprintf(
                "✓ Imported: %s | Rate: %d/min | Skipped: %s | Time: %ds\n",
                number_format($imported),
                $rate,
                number_format($skipped),
                (int)$elapsed
            );
            $lastReport = time();
        }
    }
}

// Insert remaining batch
if (!empty($batch)) {
    DB::table('leads')->insert($batch);
}

fclose($handle);

// Final stats
$elapsed = microtime(true) - $startTime;
$rate = $imported / ($elapsed / 60);
$finalCount = DB::table('leads')->where('source', 'LQF_BULK')->count();
$actualImported = $finalCount - $existingLqfCount;

echo "\n=====================================\n";
echo "✅ IMPORT COMPLETE!\n";
echo "=====================================\n\n";
echo "Imported: " . number_format($actualImported) . " new leads\n";
echo "Skipped: " . number_format($skipped) . " (duplicates/invalid)\n";
echo "Total LQF_BULK: " . number_format($finalCount) . " leads\n";
echo "Time: " . round($elapsed, 1) . " seconds\n";
echo "Speed: " . round($rate) . " leads/minute\n\n";

if ($replaced > 0) {
    echo "Note: Found $replaced Suraj leads to replace.\n";
    echo "Run separate update script to replace them.\n";
}


