<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== UPDATING VICI LEADS WITH BRAIN IDs ===\n\n";
echo "This will update existing Vici leads with their corresponding Brain external_lead_id (13-digit)\n\n";

// Get command line arguments
$testMode = in_array('--test', $argv);
$limit = 10; // Start with 10 for testing
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = intval(substr($arg, 8));
    }
}

if ($testMode) {
    echo "🔧 TEST MODE - Will process up to $limit leads\n\n";
} else {
    echo "⚠️ PRODUCTION MODE - Will process ALL leads\n";
    echo "Press Ctrl+C to cancel, or wait 5 seconds to continue...\n";
    sleep(5);
}

try {
    // Step 1: Get leads from Brain that should be in Vici
    echo "Step 1: Finding Brain leads that need to be synced to Vici...\n";
    
    $query = Lead::whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'"); // PostgreSQL regex for numeric only
    
    if ($testMode) {
        $query->limit($limit);
    }
    
    $leads = $query->get();
    
    echo "Found " . $leads->count() . " leads to process\n\n";
    
    if ($leads->isEmpty()) {
        echo "No leads found to update.\n";
        exit(0);
    }
    
    // Step 2: Process each lead
    $updated = 0;
    $notFound = 0;
    $errors = 0;
    $alreadyUpdated = 0;
    
    foreach ($leads as $index => $lead) {
        $progress = $index + 1;
        echo "[$progress/" . $leads->count() . "] Processing Lead ID {$lead->id} ";
        echo "({$lead->first_name} {$lead->last_name}, Phone: {$lead->phone})\n";
        
        // Build the MySQL command to check if lead exists in Vici
        $checkCommand = sprintf(
            "mysql -u cron -p1234 asterisk -e \"SELECT lead_id, vendor_lead_code, first_name, last_name FROM vicidial_list WHERE phone_number = '%s' ORDER BY lead_id DESC LIMIT 1\" 2>/dev/null",
            $lead->phone
        );
        
        // Execute via proxy
        $response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => $checkCommand
        ]);
        
        if (!$response->successful()) {
            echo "  ❌ Error: Failed to connect to proxy\n";
            $errors++;
            continue;
        }
        
        $output = $response->json()['output'] ?? '';
        
        // Check if lead exists
        if (empty($output) || strpos($output, 'lead_id') === false || strpos($output, "\n") === false) {
            echo "  ⚠️ Not found in Vici\n";
            $notFound++;
            continue;
        }
        
        // Parse the result
        $lines = explode("\n", $output);
        if (count($lines) < 2) {
            echo "  ⚠️ Not found in Vici\n";
            $notFound++;
            continue;
        }
        
        // Parse the data line (skip header)
        $dataLine = $lines[1];
        $columns = preg_split('/\t/', $dataLine);
        
        if (count($columns) < 4) {
            echo "  ⚠️ Could not parse Vici data\n";
            $errors++;
            continue;
        }
        
        $viciLeadId = trim($columns[0]);
        $currentVendorCode = trim($columns[1]);
        $viciFirstName = trim($columns[2]);
        $viciLastName = trim($columns[3]);
        
        echo "  Found in Vici: Lead #{$viciLeadId} ({$viciFirstName} {$viciLastName})\n";
        
        // Check if already has Brain ID
        if ($currentVendorCode === $lead->external_lead_id) {
            echo "  ✅ Already has Brain ID: {$lead->external_lead_id}\n";
            $alreadyUpdated++;
            continue;
        }
        
        echo "  Current vendor_lead_code: " . ($currentVendorCode ?: 'empty') . "\n";
        echo "  Updating to Brain ID: {$lead->external_lead_id}\n";
        
        // Build update command
        $updateCommand = sprintf(
            "mysql -u cron -p1234 asterisk -e \"UPDATE vicidial_list SET vendor_lead_code = '%s', source_id = 'BRAIN_%s' WHERE lead_id = %d\" 2>/dev/null",
            $lead->external_lead_id,
            $lead->external_lead_id,
            $viciLeadId
        );
        
        // Execute update
        $updateResponse = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => $updateCommand
        ]);
        
        if ($updateResponse->successful()) {
            echo "  ✅ Updated successfully!\n";
            $updated++;
            
            // Log in Brain
            Log::info('Updated Vici lead with Brain ID', [
                'brain_lead_id' => $lead->id,
                'external_lead_id' => $lead->external_lead_id,
                'vici_lead_id' => $viciLeadId,
                'phone' => $lead->phone
            ]);
        } else {
            echo "  ❌ Update failed\n";
            $errors++;
        }
        
        echo "\n";
        
        // Add small delay to avoid overwhelming the server
        if ($progress % 10 == 0) {
            echo "Pausing briefly...\n";
            sleep(1);
        }
    }
    
    // Step 3: Summary
    echo "\n=== UPDATE COMPLETE ===\n";
    echo "✅ Updated: $updated leads\n";
    echo "✓ Already had Brain ID: $alreadyUpdated leads\n";
    echo "⚠️ Not found in Vici: $notFound leads\n";
    echo "❌ Errors: $errors leads\n";
    echo "Total processed: " . $leads->count() . " leads\n";
    
    // Step 4: Verify a sample
    if ($updated > 0) {
        echo "\n=== VERIFICATION ===\n";
        echo "Checking a sample of updated leads...\n";
        
        $sampleCommand = "mysql -u cron -p1234 asterisk -e \"SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}$' ORDER BY lead_id DESC LIMIT 5\" 2>/dev/null";
        
        $sampleResponse = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => $sampleCommand
        ]);
        
        if ($sampleResponse->successful()) {
            echo $sampleResponse->json()['output'] ?? 'No output';
        }
    }
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== DONE ===\n";
