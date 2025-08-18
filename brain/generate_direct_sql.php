<?php

echo "=== GENERATING DIRECT SQL UPDATE SCRIPT ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Step 1: Generating optimized SQL statements...\n";

// Target lists
$autodialLists = [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
                  8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
                  10006, 10007, 10008, 10009, 10010, 10011];
$auto2Lists = [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020];
$allLists = array_merge($autodialLists, $auto2Lists);

// Create multiple smaller SQL files for easier execution
$fileNum = 0;
$statementsPerFile = 500;
$currentStatements = [];
$totalUpdates = 0;

// Process Brain leads in chunks
$chunkSize = 1000;
$offset = 0;

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            // Create UPDATE statement for this phone number
            $stmt = sprintf(
                "UPDATE vicidial_list SET vendor_lead_code='%s', source_id='BRAIN_%s' WHERE phone_number='%s' AND list_id IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='' OR vendor_lead_code!='%s');",
                addslashes($lead->external_lead_id),
                addslashes($lead->external_lead_id),
                addslashes($cleanPhone),
                implode(',', $allLists),
                addslashes($lead->external_lead_id)
            );
            
            $currentStatements[] = $stmt;
            $totalUpdates++;
            
            // Write to file when we have enough statements
            if (count($currentStatements) >= $statementsPerFile) {
                $filename = sprintf("vici_direct_%03d.sql", $fileNum);
                $fp = fopen($filename, 'w');
                fwrite($fp, "-- Direct Update File $fileNum\n");
                fwrite($fp, "USE Q6hdjl67GRigMofv;\n");
                fwrite($fp, "SET autocommit=1;\n\n");
                fwrite($fp, implode("\n", $currentStatements) . "\n");
                fclose($fp);
                
                echo "  Created $filename with " . count($currentStatements) . " updates\n";
                $fileNum++;
                $currentStatements = [];
            }
        }
    }
    
    $offset += $chunkSize;
    echo "  Processed $totalUpdates leads...\r";
}

// Write remaining statements
if (!empty($currentStatements)) {
    $filename = sprintf("vici_direct_%03d.sql", $fileNum);
    $fp = fopen($filename, 'w');
    fwrite($fp, "-- Direct Update File $fileNum\n");
    fwrite($fp, "USE Q6hdjl67GRigMofv;\n");
    fwrite($fp, "SET autocommit=1;\n\n");
    fwrite($fp, implode("\n", $currentStatements) . "\n");
    fclose($fp);
    echo "  Created $filename with " . count($currentStatements) . " updates\n";
    $fileNum++;
}

echo "\n✅ Generated $fileNum SQL files with $totalUpdates update statements\n\n";

// Create execution script
$execScript = "#!/bin/bash\n\n";
$execScript .= "echo '=== EXECUTING VICI UPDATES ==='\n";
$execScript .= "echo 'Found $fileNum SQL files to process'\n\n";
$execScript .= "TOTAL=0\n";

for ($i = 0; $i < $fileNum; $i++) {
    $filename = sprintf("vici_direct_%03d.sql", $i);
    $execScript .= "echo 'Processing $filename...'\n";
    $execScript .= "curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \\\n";
    $execScript .= "  -H \"Content-Type: application/json\" \\\n";
    $execScript .= "  -d '{\"command\": \"mysql -u root Q6hdjl67GRigMofv < /tmp/$filename 2>&1 | tail -5\"}' \\\n";
    $execScript .= "  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'\n";
    $execScript .= "sleep 0.5\n\n";
}

$execScript .= "echo ''\n";
$execScript .= "echo '=== COMPLETE ==='\n";
$execScript .= "echo 'All updates have been processed!'\n";

file_put_contents('execute_all_updates.sh', $execScript);
chmod('execute_all_updates.sh', 0755);

echo "Step 2: Created execution script: execute_all_updates.sh\n";
echo "\n=== READY TO EXECUTE ===\n";
echo "Generated $fileNum SQL files with direct UPDATE statements\n";
echo "Total update statements: $totalUpdates\n";
echo "\nThese files update Vici leads directly without temp tables.\n";
echo "Each file can be executed independently.\n";



echo "=== GENERATING DIRECT SQL UPDATE SCRIPT ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Step 1: Generating optimized SQL statements...\n";

// Target lists
$autodialLists = [6010, 6015, 6016, 6017, 6018, 6019, 6020, 6021, 6022, 6023, 6024, 6025,
                  8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
                  10006, 10007, 10008, 10009, 10010, 10011];
$auto2Lists = [6011, 6012, 6013, 6014, 7010, 7011, 7012, 60010, 60020];
$allLists = array_merge($autodialLists, $auto2Lists);

// Create multiple smaller SQL files for easier execution
$fileNum = 0;
$statementsPerFile = 500;
$currentStatements = [];
$totalUpdates = 0;

// Process Brain leads in chunks
$chunkSize = 1000;
$offset = 0;

while (true) {
    $leads = DB::table('leads')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'")
        ->offset($offset)
        ->limit($chunkSize)
        ->get(['phone', 'external_lead_id']);
    
    if ($leads->isEmpty()) {
        break;
    }
    
    foreach ($leads as $lead) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
        if (strlen($cleanPhone) >= 10) {
            // Create UPDATE statement for this phone number
            $stmt = sprintf(
                "UPDATE vicidial_list SET vendor_lead_code='%s', source_id='BRAIN_%s' WHERE phone_number='%s' AND list_id IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='' OR vendor_lead_code!='%s');",
                addslashes($lead->external_lead_id),
                addslashes($lead->external_lead_id),
                addslashes($cleanPhone),
                implode(',', $allLists),
                addslashes($lead->external_lead_id)
            );
            
            $currentStatements[] = $stmt;
            $totalUpdates++;
            
            // Write to file when we have enough statements
            if (count($currentStatements) >= $statementsPerFile) {
                $filename = sprintf("vici_direct_%03d.sql", $fileNum);
                $fp = fopen($filename, 'w');
                fwrite($fp, "-- Direct Update File $fileNum\n");
                fwrite($fp, "USE Q6hdjl67GRigMofv;\n");
                fwrite($fp, "SET autocommit=1;\n\n");
                fwrite($fp, implode("\n", $currentStatements) . "\n");
                fclose($fp);
                
                echo "  Created $filename with " . count($currentStatements) . " updates\n";
                $fileNum++;
                $currentStatements = [];
            }
        }
    }
    
    $offset += $chunkSize;
    echo "  Processed $totalUpdates leads...\r";
}

// Write remaining statements
if (!empty($currentStatements)) {
    $filename = sprintf("vici_direct_%03d.sql", $fileNum);
    $fp = fopen($filename, 'w');
    fwrite($fp, "-- Direct Update File $fileNum\n");
    fwrite($fp, "USE Q6hdjl67GRigMofv;\n");
    fwrite($fp, "SET autocommit=1;\n\n");
    fwrite($fp, implode("\n", $currentStatements) . "\n");
    fclose($fp);
    echo "  Created $filename with " . count($currentStatements) . " updates\n";
    $fileNum++;
}

echo "\n✅ Generated $fileNum SQL files with $totalUpdates update statements\n\n";

// Create execution script
$execScript = "#!/bin/bash\n\n";
$execScript .= "echo '=== EXECUTING VICI UPDATES ==='\n";
$execScript .= "echo 'Found $fileNum SQL files to process'\n\n";
$execScript .= "TOTAL=0\n";

for ($i = 0; $i < $fileNum; $i++) {
    $filename = sprintf("vici_direct_%03d.sql", $i);
    $execScript .= "echo 'Processing $filename...'\n";
    $execScript .= "curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \\\n";
    $execScript .= "  -H \"Content-Type: application/json\" \\\n";
    $execScript .= "  -d '{\"command\": \"mysql -u root Q6hdjl67GRigMofv < /tmp/$filename 2>&1 | tail -5\"}' \\\n";
    $execScript .= "  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'\n";
    $execScript .= "sleep 0.5\n\n";
}

$execScript .= "echo ''\n";
$execScript .= "echo '=== COMPLETE ==='\n";
$execScript .= "echo 'All updates have been processed!'\n";

file_put_contents('execute_all_updates.sh', $execScript);
chmod('execute_all_updates.sh', 0755);

echo "Step 2: Created execution script: execute_all_updates.sh\n";
echo "\n=== READY TO EXECUTE ===\n";
echo "Generated $fileNum SQL files with direct UPDATE statements\n";
echo "Total update statements: $totalUpdates\n";
echo "\nThese files update Vici leads directly without temp tables.\n";
echo "Each file can be executed independently.\n";


