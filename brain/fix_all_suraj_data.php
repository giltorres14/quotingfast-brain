<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔧 FIXING ALL SURAJ DATA WITH CORRECT MAPPINGS 🔧\n";
echo "==================================================\n\n";

echo "CORRECT COLUMN MAPPING:\n";
echo "  Column B (index 1): Opt-in Date\n";
echo "  Column H (index 7): Lead Type (Home/Auto)\n";
echo "  Column K (index 10): Vendor Name\n";
echo "  Column AC (index 28): Email (can be null)\n";
echo "  Column AD (index 29): Phone\n";
echo "  Column AE (index 30): Address\n";
echo "  Column AF (index 31): Zip Code\n";
echo "  Column AG (index 32): State\n";
echo "  Column AH (index 33): Birth Date (can be null)\n";
echo "==================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$phoneToData = [];
$totalProcessed = 0;

// Step 1: Collect all correct data from CSV files
echo "Step 1: Reading correct data from CSV files...\n";
foreach ($files as $index => $file) {
    echo "Reading " . basename($file) . "...";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    if ($header[0] == 'First Name') {
        echo " [SKIP - wrong format]\n";
        fclose($handle);
        continue;
    }
    
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        // CORRECT COLUMN MAPPINGS (0-indexed)
        $phone = trim($row[29] ?? '');      // Column AD (30th column)
        if (!$phone || strlen($phone) < 10) continue;
        
        $optInDate = trim($row[1] ?? '');   // Column B (2nd column)
        $leadType = trim($row[7] ?? '');    // Column H (8th column)
        $vendorName = trim($row[10] ?? ''); // Column K (11th column)
        $email = trim($row[28] ?? '');      // Column AC (29th column) - can be null
        $address = trim($row[30] ?? '');    // Column AE (31st column)
        $zipCode = trim($row[31] ?? '');    // Column AF (32nd column)
        $state = trim($row[32] ?? '');      // Column AG (33rd column)
        $birthDate = trim($row[33] ?? '');  // Column AH (34th column) - can be null
        
        // Parse opt-in date
        $optInDateTime = null;
        if ($optInDate) {
            try {
                $optInDateTime = Carbon::parse($optInDate)->toDateTimeString();
            } catch (\Exception $e) {
                $optInDateTime = null;
            }
        }
        
        // Parse birth date (format: MM/DD/YYYY)
        $dob = null;
        if ($birthDate && $birthDate != '') {
            try {
                $dob = Carbon::createFromFormat('m/d/Y', $birthDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null;
            }
        }
        
        // Get or create vendor
        $vendorId = null;
        if ($vendorName) {
            $vendor = DB::table('vendors')->where('name', $vendorName)->first();
            if (!$vendor) {
                $vendorId = DB::table('vendors')->insertGetId([
                    'name' => $vendorName,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $vendorId = $vendor->id;
            }
        }
        
        // Store all data for this phone
        $phoneToData[$phone] = [
            'email' => $email ?: null,
            'address' => $address,
            'state' => $state,
            'zip_code' => $zipCode,
            'type' => strpos(strtolower($leadType), 'home') !== false ? 'home' : 'auto',
            'opt_in_date' => $optInDateTime,
            'dob' => $dob,
            'vendor_id' => $vendorId
        ];
        
        $count++;
    }
    fclose($handle);
    echo " [$count records]\n";
    $totalProcessed += $count;
}

echo "\nProcessed " . number_format($totalProcessed) . " records from CSV files\n";
echo "Have data for " . count($phoneToData) . " unique phone numbers\n\n";

// Show samples
echo "Sample data to be updated:\n";
$samples = array_slice($phoneToData, 0, 3, true);
foreach ($samples as $phone => $data) {
    echo "  Phone: $phone\n";
    echo "    Email: " . ($data['email'] ?: 'NULL') . "\n";
    echo "    Address: {$data['address']}\n";
    echo "    State: {$data['state']}\n";
    echo "    Zip: {$data['zip_code']}\n";
    echo "    Type: {$data['type']}\n";
    echo "    DOB: " . ($data['dob'] ?: 'NULL') . "\n";
    echo "    Vendor ID: " . ($data['vendor_id'] ?: 'NULL') . "\n";
    echo "    Opt-in: {$data['opt_in_date']}\n\n";
}

// Step 2: Update database
echo "Step 2: Updating database records...\n";
$totalUpdated = 0;
$batchSize = 100;
$batch = [];

foreach ($phoneToData as $phone => $data) {
    $batch[$phone] = $data;
    
    if (count($batch) >= $batchSize) {
        // Update this batch
        $phones = array_keys($batch);
        $leads = DB::table('leads')
            ->where('source', 'SURAJ_BULK')
            ->whereIn('phone', $phones)
            ->get(['id', 'phone']);
        
        foreach ($leads as $lead) {
            if (isset($batch[$lead->phone])) {
                $updateData = $batch[$lead->phone];
                $updateData['updated_at'] = now();
                
                DB::table('leads')
                    ->where('id', $lead->id)
                    ->update($updateData);
                $totalUpdated++;
            }
        }
        
        echo "  Updated $totalUpdated records...\r";
        $batch = [];
    }
}

// Update remaining batch
if (!empty($batch)) {
    $phones = array_keys($batch);
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->whereIn('phone', $phones)
        ->get(['id', 'phone']);
    
    foreach ($leads as $lead) {
        if (isset($batch[$lead->phone])) {
            $updateData = $batch[$lead->phone];
            $updateData['updated_at'] = now();
            
            DB::table('leads')
                ->where('id', $lead->id)
                ->update($updateData);
            $totalUpdated++;
        }
    }
}

echo "\n\n==================================================\n";
echo "✅ COMPLETE!\n";
echo "Updated " . number_format($totalUpdated) . " records with correct data\n\n";

// Verify the update
echo "Verification - Sample of updated records:\n";
$sample = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->whereNotNull('state')
    ->whereNotNull('zip_code')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get(['phone', 'name', 'email', 'state', 'zip_code', 'type', 'opt_in_date']);

foreach ($sample as $lead) {
    echo "  Phone: {$lead->phone}\n";
    echo "    Email: {$lead->email}\n";
    echo "    State: {$lead->state}\n";
    echo "    Zip: {$lead->zip_code}\n";
    echo "    Type: {$lead->type}\n";
    echo "    Opt-in: {$lead->opt_in_date}\n\n";
}

echo "==================================================\n";

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔧 FIXING ALL SURAJ DATA WITH CORRECT MAPPINGS 🔧\n";
echo "==================================================\n\n";

echo "CORRECT COLUMN MAPPING:\n";
echo "  Column B (index 1): Opt-in Date\n";
echo "  Column H (index 7): Lead Type (Home/Auto)\n";
echo "  Column K (index 10): Vendor Name\n";
echo "  Column AC (index 28): Email (can be null)\n";
echo "  Column AD (index 29): Phone\n";
echo "  Column AE (index 30): Address\n";
echo "  Column AF (index 31): Zip Code\n";
echo "  Column AG (index 32): State\n";
echo "  Column AH (index 33): Birth Date (can be null)\n";
echo "==================================================\n\n";

$folder = $_SERVER['HOME'] . '/Downloads/Suraj Leads';
$files = glob($folder . '/*.csv');
$files = array_filter($files, function($f) { return !str_contains($f, 'skip'); });

echo "Found " . count($files) . " CSV files\n\n";

$phoneToData = [];
$totalProcessed = 0;

// Step 1: Collect all correct data from CSV files
echo "Step 1: Reading correct data from CSV files...\n";
foreach ($files as $index => $file) {
    echo "Reading " . basename($file) . "...";
    
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    
    if ($header[0] == 'First Name') {
        echo " [SKIP - wrong format]\n";
        fclose($handle);
        continue;
    }
    
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 33) continue;
        
        // CORRECT COLUMN MAPPINGS (0-indexed)
        $phone = trim($row[29] ?? '');      // Column AD (30th column)
        if (!$phone || strlen($phone) < 10) continue;
        
        $optInDate = trim($row[1] ?? '');   // Column B (2nd column)
        $leadType = trim($row[7] ?? '');    // Column H (8th column)
        $vendorName = trim($row[10] ?? ''); // Column K (11th column)
        $email = trim($row[28] ?? '');      // Column AC (29th column) - can be null
        $address = trim($row[30] ?? '');    // Column AE (31st column)
        $zipCode = trim($row[31] ?? '');    // Column AF (32nd column)
        $state = trim($row[32] ?? '');      // Column AG (33rd column)
        $birthDate = trim($row[33] ?? '');  // Column AH (34th column) - can be null
        
        // Parse opt-in date
        $optInDateTime = null;
        if ($optInDate) {
            try {
                $optInDateTime = Carbon::parse($optInDate)->toDateTimeString();
            } catch (\Exception $e) {
                $optInDateTime = null;
            }
        }
        
        // Parse birth date (format: MM/DD/YYYY)
        $dob = null;
        if ($birthDate && $birthDate != '') {
            try {
                $dob = Carbon::createFromFormat('m/d/Y', $birthDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null;
            }
        }
        
        // Get or create vendor
        $vendorId = null;
        if ($vendorName) {
            $vendor = DB::table('vendors')->where('name', $vendorName)->first();
            if (!$vendor) {
                $vendorId = DB::table('vendors')->insertGetId([
                    'name' => $vendorName,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $vendorId = $vendor->id;
            }
        }
        
        // Store all data for this phone
        $phoneToData[$phone] = [
            'email' => $email ?: null,
            'address' => $address,
            'state' => $state,
            'zip_code' => $zipCode,
            'type' => strpos(strtolower($leadType), 'home') !== false ? 'home' : 'auto',
            'opt_in_date' => $optInDateTime,
            'dob' => $dob,
            'vendor_id' => $vendorId
        ];
        
        $count++;
    }
    fclose($handle);
    echo " [$count records]\n";
    $totalProcessed += $count;
}

echo "\nProcessed " . number_format($totalProcessed) . " records from CSV files\n";
echo "Have data for " . count($phoneToData) . " unique phone numbers\n\n";

// Show samples
echo "Sample data to be updated:\n";
$samples = array_slice($phoneToData, 0, 3, true);
foreach ($samples as $phone => $data) {
    echo "  Phone: $phone\n";
    echo "    Email: " . ($data['email'] ?: 'NULL') . "\n";
    echo "    Address: {$data['address']}\n";
    echo "    State: {$data['state']}\n";
    echo "    Zip: {$data['zip_code']}\n";
    echo "    Type: {$data['type']}\n";
    echo "    DOB: " . ($data['dob'] ?: 'NULL') . "\n";
    echo "    Vendor ID: " . ($data['vendor_id'] ?: 'NULL') . "\n";
    echo "    Opt-in: {$data['opt_in_date']}\n\n";
}

// Step 2: Update database
echo "Step 2: Updating database records...\n";
$totalUpdated = 0;
$batchSize = 100;
$batch = [];

foreach ($phoneToData as $phone => $data) {
    $batch[$phone] = $data;
    
    if (count($batch) >= $batchSize) {
        // Update this batch
        $phones = array_keys($batch);
        $leads = DB::table('leads')
            ->where('source', 'SURAJ_BULK')
            ->whereIn('phone', $phones)
            ->get(['id', 'phone']);
        
        foreach ($leads as $lead) {
            if (isset($batch[$lead->phone])) {
                $updateData = $batch[$lead->phone];
                $updateData['updated_at'] = now();
                
                DB::table('leads')
                    ->where('id', $lead->id)
                    ->update($updateData);
                $totalUpdated++;
            }
        }
        
        echo "  Updated $totalUpdated records...\r";
        $batch = [];
    }
}

// Update remaining batch
if (!empty($batch)) {
    $phones = array_keys($batch);
    $leads = DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->whereIn('phone', $phones)
        ->get(['id', 'phone']);
    
    foreach ($leads as $lead) {
        if (isset($batch[$lead->phone])) {
            $updateData = $batch[$lead->phone];
            $updateData['updated_at'] = now();
            
            DB::table('leads')
                ->where('id', $lead->id)
                ->update($updateData);
            $totalUpdated++;
        }
    }
}

echo "\n\n==================================================\n";
echo "✅ COMPLETE!\n";
echo "Updated " . number_format($totalUpdated) . " records with correct data\n\n";

// Verify the update
echo "Verification - Sample of updated records:\n";
$sample = DB::table('leads')
    ->where('source', 'SURAJ_BULK')
    ->whereNotNull('state')
    ->whereNotNull('zip_code')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get(['phone', 'name', 'email', 'state', 'zip_code', 'type', 'opt_in_date']);

foreach ($sample as $lead) {
    echo "  Phone: {$lead->phone}\n";
    echo "    Email: {$lead->email}\n";
    echo "    State: {$lead->state}\n";
    echo "    Zip: {$lead->zip_code}\n";
    echo "    Type: {$lead->type}\n";
    echo "    Opt-in: {$lead->opt_in_date}\n\n";
}

echo "==================================================\n";


