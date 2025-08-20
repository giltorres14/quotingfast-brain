<?php

echo "=== FETCHING VICI CALL LOGS (LAST 90 DAYS) ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Configuration - Fix year to 2024
$startDate = Carbon::parse('2024-10-16')->format('Y-m-d 00:00:00'); // 90 days ago from Jan 14, 2025
$endDate = Carbon::parse('2025-01-14')->format('Y-m-d 23:59:59'); // Today
$dbName = 'Q6hdjl67GRigMofv';

echo "📅 Date Range: $startDate to $endDate\n";
echo "📊 Database: $dbName\n\n";

// Create the export script
$exportScript = <<<'BASH'
#!/bin/bash

# Database parameters
DB_NAME="Q6hdjl67GRigMofv"
START_DATE="__START_DATE__"
END_DATE="__END_DATE__"

# Generate filename
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vici_call_logs_${now}.csv"

# File paths
REPORT_PATH="/tmp/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Create directory if needed
mkdir -p "${REPORT_PATH}"

echo "Exporting Vici call logs..."
echo "Date range: ${START_DATE} to ${END_DATE}"

# Export the data with header
mysql -u root ${DB_NAME} -N -B <<EOF
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 'length_in_sec', 
         'server_ip', 'extension', 'channel', 'outbound_cid', 'sip_hangup_cause', 'sip_hangup_reason', 
         'vendor_lead_code', 'user', 'term_reason', 'uniqueid'
)
UNION ALL
(
  SELECT 
    vl.call_date,
    vl.lead_id,
    vl.list_id,
    vl.phone_number,
    vl.campaign_id,
    vl.status,
    vl.length_in_sec,
    IFNULL(vdl.server_ip, ''),
    IFNULL(vdl.extension, ''),
    IFNULL(vdl.channel, ''),
    IFNULL(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)), ''),
    IFNULL(vdl.sip_hangup_cause, ''),
    IFNULL(vdl.sip_hangup_reason, ''),
    IFNULL(vl.vendor_lead_code, ''),
    IFNULL(vl.user, ''),
    IFNULL(vl.term_reason, ''),
    IFNULL(vl.uniqueid, '')
  FROM vicidial_log vl
  LEFT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id 
    AND vl.call_date = vdl.call_date
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
  ORDER BY vl.call_date DESC
  LIMIT 100000
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Move file to accessible location
if [ -f "${MYSQL_OUTPUT}" ]; then
    mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"
    echo "Success! CSV exported to: ${FINAL_OUTPUT}"
    
    # Show file info
    wc -l "${FINAL_OUTPUT}"
    ls -lh "${FINAL_OUTPUT}"
    
    # Show first few lines
    echo ""
    echo "First 5 lines of data:"
    head -5 "${FINAL_OUTPUT}"
else
    echo "Error: Export failed"
    exit 1
fi
BASH;

// Replace placeholders
$exportScript = str_replace('__START_DATE__', $startDate, $exportScript);
$exportScript = str_replace('__END_DATE__', $endDate, $exportScript);

// Save script locally
file_put_contents('vici_export_90days.sh', $exportScript);

echo "Step 1: Executing export on Vici server...\n\n";

// Execute via proxy
$response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "bash -c " . escapeshellarg($exportScript) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Export Result:\n$output\n\n";
    
    // Check if file was created
    if (strpos($output, 'Success!') !== false) {
        // Extract filename
        if (preg_match('/vici_call_logs_\d+\.csv/', $output, $matches)) {
            $csvFile = '/tmp/' . $matches[0];
            
            echo "Step 2: Downloading CSV file...\n";
            
            // Get the CSV content
            $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "cat $csvFile | head -10000 2>&1"
            ]);
            
            if ($response->successful()) {
                $csvContent = $response->json()['output'] ?? '';
                
                // Save locally
                $localFile = 'vici_call_logs_90days.csv';
                file_put_contents($localFile, $csvContent);
                
                echo "✅ CSV saved locally as: $localFile\n";
                
                // Parse and import
                echo "\nStep 3: Parsing CSV data...\n";
                
                $lines = explode("\n", $csvContent);
                $header = str_getcsv(array_shift($lines));
                
                $stats = [
                    'total' => 0,
                    'with_brain_id' => 0,
                    'imported' => 0,
                    'orphaned' => 0,
                    'by_campaign' => [],
                    'by_status' => [],
                    'by_date' => []
                ];
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    $data = str_getcsv($line);
                    if (count($data) < 17) continue;
                    
                    $row = array_combine($header, $data);
                    $stats['total']++;
                    
                    // Check for Brain ID
                    if (!empty($row['vendor_lead_code']) && preg_match('/^\d{13}$/', $row['vendor_lead_code'])) {
                        $stats['with_brain_id']++;
                        
                        // Find the lead
                        $lead = \App\Models\Lead::where('external_lead_id', $row['vendor_lead_code'])->first();
                        
                        if ($lead) {
                            // Update or create call metrics
                            $metrics = \App\Models\ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
                            
                            $metrics->phone_number = $row['phone_number'];
                            $metrics->campaign_id = $row['campaign_id'];
                            $metrics->agent_id = $row['user'] ?? null;
                            $metrics->status = $row['status'];
                            $metrics->last_call_date = $row['call_date'];
                            $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
                            
                            $talkTime = intval($row['length_in_sec'] ?? 0);
                            if ($talkTime > 0) {
                                $metrics->talk_time = ($metrics->talk_time ?? 0) + $talkTime;
                                $metrics->connected = true;
                            }
                            
                            $metrics->save();
                            $stats['imported']++;
                        } else {
                            $stats['orphaned']++;
                        }
                    }
                    
                    // Track by campaign
                    $campaign = $row['campaign_id'] ?? 'Unknown';
                    $stats['by_campaign'][$campaign] = ($stats['by_campaign'][$campaign] ?? 0) + 1;
                    
                    // Track by status
                    $status = $row['status'] ?? 'Unknown';
                    $stats['by_status'][$status] = ($stats['by_status'][$status] ?? 0) + 1;
                    
                    // Track by date
                    $date = substr($row['call_date'], 0, 10);
                    $stats['by_date'][$date] = ($stats['by_date'][$date] ?? 0) + 1;
                    
                    // Show progress
                    if ($stats['total'] % 1000 == 0) {
                        echo "  Processed " . number_format($stats['total']) . " calls...\r";
                    }
                }
                
                echo "\n\n=== IMPORT STATISTICS ===\n";
                echo "Total Call Records: " . number_format($stats['total']) . "\n";
                echo "With Brain IDs: " . number_format($stats['with_brain_id']) . "\n";
                echo "Imported to Brain: " . number_format($stats['imported']) . "\n";
                echo "Orphaned (no matching lead): " . number_format($stats['orphaned']) . "\n";
                
                echo "\n=== BY CAMPAIGN ===\n";
                arsort($stats['by_campaign']);
                foreach (array_slice($stats['by_campaign'], 0, 10) as $campaign => $count) {
                    echo "  $campaign: " . number_format($count) . "\n";
                }
                
                echo "\n=== BY STATUS (Top 20) ===\n";
                arsort($stats['by_status']);
                foreach (array_slice($stats['by_status'], 0, 20) as $status => $count) {
                    echo "  $status: " . number_format($count) . "\n";
                }
                
                echo "\n=== CALLS BY DATE (Last 10 Days) ===\n";
                krsort($stats['by_date']);
                foreach (array_slice($stats['by_date'], 0, 10) as $date => $count) {
                    echo "  $date: " . number_format($count) . "\n";
                }
                
                // Clean up remote file
                Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "rm -f $csvFile 2>&1"
                ]);
            }
        }
    } else {
        echo "❌ Export failed. Check the error message above.\n";
    }
} else {
    echo "❌ Failed to execute export script\n";
}

echo "\n=== COMPLETE ===\n";

echo "=== FETCHING VICI CALL LOGS (LAST 90 DAYS) ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Configuration - Fix year to 2024
$startDate = Carbon::parse('2024-10-16')->format('Y-m-d 00:00:00'); // 90 days ago from Jan 14, 2025
$endDate = Carbon::parse('2025-01-14')->format('Y-m-d 23:59:59'); // Today
$dbName = 'Q6hdjl67GRigMofv';

echo "📅 Date Range: $startDate to $endDate\n";
echo "📊 Database: $dbName\n\n";

// Create the export script
$exportScript = <<<'BASH'
#!/bin/bash

# Database parameters
DB_NAME="Q6hdjl67GRigMofv"
START_DATE="__START_DATE__"
END_DATE="__END_DATE__"

# Generate filename
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vici_call_logs_${now}.csv"

# File paths
REPORT_PATH="/tmp/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Create directory if needed
mkdir -p "${REPORT_PATH}"

echo "Exporting Vici call logs..."
echo "Date range: ${START_DATE} to ${END_DATE}"

# Export the data with header
mysql -u root ${DB_NAME} -N -B <<EOF
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 'length_in_sec', 
         'server_ip', 'extension', 'channel', 'outbound_cid', 'sip_hangup_cause', 'sip_hangup_reason', 
         'vendor_lead_code', 'user', 'term_reason', 'uniqueid'
)
UNION ALL
(
  SELECT 
    vl.call_date,
    vl.lead_id,
    vl.list_id,
    vl.phone_number,
    vl.campaign_id,
    vl.status,
    vl.length_in_sec,
    IFNULL(vdl.server_ip, ''),
    IFNULL(vdl.extension, ''),
    IFNULL(vdl.channel, ''),
    IFNULL(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)), ''),
    IFNULL(vdl.sip_hangup_cause, ''),
    IFNULL(vdl.sip_hangup_reason, ''),
    IFNULL(vl.vendor_lead_code, ''),
    IFNULL(vl.user, ''),
    IFNULL(vl.term_reason, ''),
    IFNULL(vl.uniqueid, '')
  FROM vicidial_log vl
  LEFT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id 
    AND vl.call_date = vdl.call_date
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
  ORDER BY vl.call_date DESC
  LIMIT 100000
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Move file to accessible location
if [ -f "${MYSQL_OUTPUT}" ]; then
    mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"
    echo "Success! CSV exported to: ${FINAL_OUTPUT}"
    
    # Show file info
    wc -l "${FINAL_OUTPUT}"
    ls -lh "${FINAL_OUTPUT}"
    
    # Show first few lines
    echo ""
    echo "First 5 lines of data:"
    head -5 "${FINAL_OUTPUT}"
else
    echo "Error: Export failed"
    exit 1
fi
BASH;

// Replace placeholders
$exportScript = str_replace('__START_DATE__', $startDate, $exportScript);
$exportScript = str_replace('__END_DATE__', $endDate, $exportScript);

// Save script locally
file_put_contents('vici_export_90days.sh', $exportScript);

echo "Step 1: Executing export on Vici server...\n\n";

// Execute via proxy
$response = Http::timeout(300)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "bash -c " . escapeshellarg($exportScript) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    echo "Export Result:\n$output\n\n";
    
    // Check if file was created
    if (strpos($output, 'Success!') !== false) {
        // Extract filename
        if (preg_match('/vici_call_logs_\d+\.csv/', $output, $matches)) {
            $csvFile = '/tmp/' . $matches[0];
            
            echo "Step 2: Downloading CSV file...\n";
            
            // Get the CSV content
            $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "cat $csvFile | head -10000 2>&1"
            ]);
            
            if ($response->successful()) {
                $csvContent = $response->json()['output'] ?? '';
                
                // Save locally
                $localFile = 'vici_call_logs_90days.csv';
                file_put_contents($localFile, $csvContent);
                
                echo "✅ CSV saved locally as: $localFile\n";
                
                // Parse and import
                echo "\nStep 3: Parsing CSV data...\n";
                
                $lines = explode("\n", $csvContent);
                $header = str_getcsv(array_shift($lines));
                
                $stats = [
                    'total' => 0,
                    'with_brain_id' => 0,
                    'imported' => 0,
                    'orphaned' => 0,
                    'by_campaign' => [],
                    'by_status' => [],
                    'by_date' => []
                ];
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    $data = str_getcsv($line);
                    if (count($data) < 17) continue;
                    
                    $row = array_combine($header, $data);
                    $stats['total']++;
                    
                    // Check for Brain ID
                    if (!empty($row['vendor_lead_code']) && preg_match('/^\d{13}$/', $row['vendor_lead_code'])) {
                        $stats['with_brain_id']++;
                        
                        // Find the lead
                        $lead = \App\Models\Lead::where('external_lead_id', $row['vendor_lead_code'])->first();
                        
                        if ($lead) {
                            // Update or create call metrics
                            $metrics = \App\Models\ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
                            
                            $metrics->phone_number = $row['phone_number'];
                            $metrics->campaign_id = $row['campaign_id'];
                            $metrics->agent_id = $row['user'] ?? null;
                            $metrics->status = $row['status'];
                            $metrics->last_call_date = $row['call_date'];
                            $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
                            
                            $talkTime = intval($row['length_in_sec'] ?? 0);
                            if ($talkTime > 0) {
                                $metrics->talk_time = ($metrics->talk_time ?? 0) + $talkTime;
                                $metrics->connected = true;
                            }
                            
                            $metrics->save();
                            $stats['imported']++;
                        } else {
                            $stats['orphaned']++;
                        }
                    }
                    
                    // Track by campaign
                    $campaign = $row['campaign_id'] ?? 'Unknown';
                    $stats['by_campaign'][$campaign] = ($stats['by_campaign'][$campaign] ?? 0) + 1;
                    
                    // Track by status
                    $status = $row['status'] ?? 'Unknown';
                    $stats['by_status'][$status] = ($stats['by_status'][$status] ?? 0) + 1;
                    
                    // Track by date
                    $date = substr($row['call_date'], 0, 10);
                    $stats['by_date'][$date] = ($stats['by_date'][$date] ?? 0) + 1;
                    
                    // Show progress
                    if ($stats['total'] % 1000 == 0) {
                        echo "  Processed " . number_format($stats['total']) . " calls...\r";
                    }
                }
                
                echo "\n\n=== IMPORT STATISTICS ===\n";
                echo "Total Call Records: " . number_format($stats['total']) . "\n";
                echo "With Brain IDs: " . number_format($stats['with_brain_id']) . "\n";
                echo "Imported to Brain: " . number_format($stats['imported']) . "\n";
                echo "Orphaned (no matching lead): " . number_format($stats['orphaned']) . "\n";
                
                echo "\n=== BY CAMPAIGN ===\n";
                arsort($stats['by_campaign']);
                foreach (array_slice($stats['by_campaign'], 0, 10) as $campaign => $count) {
                    echo "  $campaign: " . number_format($count) . "\n";
                }
                
                echo "\n=== BY STATUS (Top 20) ===\n";
                arsort($stats['by_status']);
                foreach (array_slice($stats['by_status'], 0, 20) as $status => $count) {
                    echo "  $status: " . number_format($count) . "\n";
                }
                
                echo "\n=== CALLS BY DATE (Last 10 Days) ===\n";
                krsort($stats['by_date']);
                foreach (array_slice($stats['by_date'], 0, 10) as $date => $count) {
                    echo "  $date: " . number_format($count) . "\n";
                }
                
                // Clean up remote file
                Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "rm -f $csvFile 2>&1"
                ]);
            }
        }
    } else {
        echo "❌ Export failed. Check the error message above.\n";
    }
} else {
    echo "❌ Failed to execute export script\n";
}

echo "\n=== COMPLETE ===\n";


