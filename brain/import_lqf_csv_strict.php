<?php
/**
 * STRICT CSV IMPORT - NO DUPLICATES ALLOWED
 * This script imports LQF CSV data with STRICT duplicate prevention
 * Any phone number that already exists in the database is SKIPPED
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "========================================\n";
echo "LQF CSV IMPORT - STRICT NO DUPLICATES\n";
echo "========================================\n\n";

// Get CSV file path from command line
if ($argc < 2) {
    echo "Usage: php import_lqf_csv_strict.php <csv_file_path> [--dry-run]\n";
    echo "Example: php import_lqf_csv_strict.php /path/to/leads.csv\n";
    echo "Example: php import_lqf_csv_strict.php /path/to/leads.csv --dry-run\n";
    exit(1);
}

$csvFile = $argv[1];
$dryRun = isset($argv[2]) && $argv[2] === '--dry-run';

if (!file_exists($csvFile)) {
    echo "❌ Error: File not found: $csvFile\n";
    exit(1);
}

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No data will be saved\n\n";
} else {
    echo "💾 LIVE MODE - Data will be imported\n\n";
}

// Statistics
$stats = [
    'total_rows' => 0,
    'imported' => 0,
    'duplicates' => 0,
    'invalid' => 0,
    'errors' => 0
];

// Track duplicate phones for reporting
$duplicatePhones = [];

// Open CSV file
$handle = fopen($csvFile, 'r');
if (!$handle) {
    echo "❌ Error: Cannot open file: $csvFile\n";
    exit(1);
}

// Get header row
$headers = fgetcsv($handle);
if (!$headers) {
    echo "❌ Error: CSV file appears to be empty\n";
    exit(1);
}

echo "📋 CSV Headers Found:\n";
foreach ($headers as $index => $header) {
    echo "   [$index] $header\n";
}
echo "\n";

// Map headers to expected fields
$columnMap = [];
foreach ($headers as $index => $header) {
    $headerLower = strtolower(trim($header));
    
    // Map common variations
    if (strpos($headerLower, 'first') !== false && strpos($headerLower, 'name') !== false) {
        $columnMap['first_name'] = $index;
    } elseif (strpos($headerLower, 'last') !== false && strpos($headerLower, 'name') !== false) {
        $columnMap['last_name'] = $index;
    } elseif (strpos($headerLower, 'phone') !== false) {
        $columnMap['phone'] = $index;
    } elseif (strpos($headerLower, 'email') !== false) {
        $columnMap['email'] = $index;
    } elseif (strpos($headerLower, 'address') !== false && strpos($headerLower, 'email') === false) {
        $columnMap['address'] = $index;
    } elseif (strpos($headerLower, 'city') !== false) {
        $columnMap['city'] = $index;
    } elseif (strpos($headerLower, 'state') !== false) {
        $columnMap['state'] = $index;
    } elseif (strpos($headerLower, 'zip') !== false) {
        $columnMap['zip_code'] = $index;
    } elseif ($headerLower === 'data' || strpos($headerLower, 'json') !== false) {
        $columnMap['data'] = $index;
    } elseif (strpos($headerLower, 'timestamp') !== false || strpos($headerLower, 'created') !== false) {
        $columnMap['created_at'] = $index;
    } elseif (strpos($headerLower, 'lead') !== false && strpos($headerLower, 'id') !== false) {
        $columnMap['lqf_lead_id'] = $index;
    }
}

// Verify we have minimum required fields
if (!isset($columnMap['phone'])) {
    echo "❌ Error: Could not find phone column in CSV\n";
    echo "Please ensure CSV has a column with 'phone' in the name\n";
    exit(1);
}

echo "✅ Column Mapping:\n";
foreach ($columnMap as $field => $index) {
    echo "   $field => {$headers[$index]}\n";
}
echo "\n";

// Get all existing phone numbers for faster duplicate checking
echo "📱 Loading existing phone numbers...\n";
$existingPhones = Lead::pluck('phone')->map(function($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
})->toArray();
$existingPhonesSet = array_flip($existingPhones); // Use as hashmap for O(1) lookup
echo "   Found " . count($existingPhones) . " existing phone numbers in database\n\n";

// Process CSV rows
echo "🔄 Processing CSV rows...\n\n";

$rowNumber = 1;
while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;
    $stats['total_rows']++;
    
    try {
        // Extract phone number
        $phone = isset($columnMap['phone']) && isset($row[$columnMap['phone']]) 
            ? $row[$columnMap['phone']] 
            : '';
        
        // Clean phone number - strip to digits only
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validate phone number
        if (strlen($phone) < 10) {
            $stats['invalid']++;
            echo "⚠️  Row $rowNumber: Invalid phone number: $phone\n";
            continue;
        }
        
        // Ensure 10 digits (remove country code if present)
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
            $phone = substr($phone, 1);
        }
        if (strlen($phone) !== 10) {
            $stats['invalid']++;
            echo "⚠️  Row $rowNumber: Invalid phone length: $phone\n";
            continue;
        }
        
        // CHECK FOR DUPLICATE - STRICT MODE
        if (isset($existingPhonesSet[$phone])) {
            $stats['duplicates']++;
            $duplicatePhones[] = $phone;
            
            // Get existing lead info for reporting
            if (!$dryRun) {
                $existingLead = Lead::where('phone', $phone)->first();
                echo "🚫 Row $rowNumber: DUPLICATE SKIPPED - Phone $phone already exists";
                if ($existingLead) {
                    echo " (Lead ID: {$existingLead->id}, Created: {$existingLead->created_at->format('Y-m-d')})";
                }
                echo "\n";
            } else {
                echo "🚫 Row $rowNumber: DUPLICATE - Phone $phone already exists\n";
            }
            continue;
        }
        
        // Build lead data
        $leadData = [
            'phone' => $phone,
            'source' => 'LQF_CSV_IMPORT',
            'type' => 'auto',
            'campaign_id' => 'CSV_IMPORT_' . date('Y-m-d')
        ];
        
        // Map other fields
        foreach ($columnMap as $field => $index) {
            if ($field !== 'phone' && isset($row[$index]) && !empty(trim($row[$index]))) {
                $value = trim($row[$index]);
                
                if ($field === 'data') {
                    // Parse JSON data if present
                    try {
                        $jsonData = json_decode($value, true);
                        if ($jsonData) {
                            $leadData['drivers'] = json_encode($jsonData['drivers'] ?? []);
                            $leadData['vehicles'] = json_encode($jsonData['vehicles'] ?? []);
                            $leadData['current_policy'] = json_encode($jsonData['current_policy'] ?? []);
                            $leadData['payload'] = $value;
                        }
                    } catch (\Exception $e) {
                        // Store as-is if not valid JSON
                        $leadData['payload'] = $value;
                    }
                } elseif ($field === 'created_at') {
                    // Parse date
                    try {
                        $leadData['created_at'] = \Carbon\Carbon::parse($value);
                    } catch (\Exception $e) {
                        // Use current time if parse fails
                        $leadData['created_at'] = now();
                    }
                } else {
                    $leadData[$field] = $value;
                }
            }
        }
        
        // Set name if we have first/last
        if (isset($leadData['first_name']) || isset($leadData['last_name'])) {
            $leadData['name'] = trim(
                ($leadData['first_name'] ?? '') . ' ' . 
                ($leadData['last_name'] ?? '')
            );
        }
        
        // Generate external_lead_id (13-digit timestamp)
        $leadData['external_lead_id'] = (string) round(microtime(true) * 1000);
        
        // Import the lead (if not dry run)
        if (!$dryRun) {
            $lead = Lead::create($leadData);
            echo "✅ Row $rowNumber: IMPORTED - ID: {$lead->id}, Phone: $phone, Name: " . ($leadData['name'] ?? 'Unknown') . "\n";
            
            // Add to existing phones set for subsequent duplicate checking
            $existingPhonesSet[$phone] = true;
        } else {
            echo "✅ Row $rowNumber: WOULD IMPORT - Phone: $phone, Name: " . ($leadData['name'] ?? 'Unknown') . "\n";
        }
        
        $stats['imported']++;
        
        // Show progress every 100 rows
        if ($stats['total_rows'] % 100 == 0) {
            echo "\n📊 Progress: {$stats['total_rows']} rows processed...\n\n";
        }
        
    } catch (\Exception $e) {
        $stats['errors']++;
        echo "❌ Row $rowNumber: ERROR - " . $e->getMessage() . "\n";
    }
}

fclose($handle);

// Final Statistics
echo "\n========================================\n";
echo "IMPORT COMPLETE\n";
echo "========================================\n\n";

echo "📊 Final Statistics:\n";
echo "   Total Rows Processed: {$stats['total_rows']}\n";
echo "   ✅ Imported: {$stats['imported']}\n";
echo "   🚫 Duplicates Skipped: {$stats['duplicates']}\n";
echo "   ⚠️  Invalid: {$stats['invalid']}\n";
echo "   ❌ Errors: {$stats['errors']}\n\n";

if ($stats['duplicates'] > 0) {
    echo "📱 Duplicate Phone Numbers Found:\n";
    $uniqueDuplicates = array_unique($duplicatePhones);
    $sampleSize = min(10, count($uniqueDuplicates));
    $sample = array_slice($uniqueDuplicates, 0, $sampleSize);
    
    foreach ($sample as $phone) {
        $lead = Lead::where('phone', $phone)->first();
        if ($lead) {
            echo "   $phone - Existing Lead ID: {$lead->id} (Created: {$lead->created_at->format('Y-m-d')})\n";
        } else {
            echo "   $phone\n";
        }
    }
    
    if (count($uniqueDuplicates) > 10) {
        echo "   ... and " . (count($uniqueDuplicates) - 10) . " more\n";
    }
    echo "\n";
}

// Database summary
$totalLeads = Lead::count();
$csvImportLeads = Lead::where('source', 'LQF_CSV_IMPORT')->count();

echo "📈 Database Status:\n";
echo "   Total Leads in Database: $totalLeads\n";
echo "   Leads from CSV Import: $csvImportLeads\n\n";

if ($dryRun) {
    echo "🔍 This was a DRY RUN - no data was actually imported\n";
    echo "To perform actual import, run without --dry-run flag\n\n";
} else {
    echo "✅ Import completed successfully!\n\n";
    
    if ($stats['imported'] > 0) {
        echo "🎯 Next Steps:\n";
        echo "1. Review imported leads at: https://quotingfast-brain-ohio.onrender.com/leads\n";
        echo "2. Push to Vici using: php artisan vici:push-new-leads\n";
        echo "3. Update vendor codes: php artisan vici:update-vendor-codes\n\n";
    }
}

echo "📝 Log file: storage/logs/csv_import_" . date('Y-m-d_H-i-s') . ".log\n";

