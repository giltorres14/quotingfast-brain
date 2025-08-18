<?php
/**
 * Focused Vici Call Log Fetch - Last 30 days
 * Since today is August 15, 2025, this fetches July 16 - August 15, 2025
 */

echo "=== FOCUSED VICI CALL LOG FETCH ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Fetching last 30 days of call data (July 16 - August 15, 2025)...\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Last 30 days from August 15, 2025
$startDate = '2025-07-16';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n";

// First, get a count
$countSql = "
    SELECT COUNT(*) as count
    FROM vicidial_log
    WHERE call_date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
    AND campaign_id IS NOT NULL AND campaign_id != ''
";

try {
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        $output = trim(str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $output));
        $totalCount = intval($output);
        
        echo "📊 Total calls found: " . number_format($totalCount) . "\n\n";
        
        if ($totalCount > 0) {
            // Process in daily batches
            $currentDate = $startDate;
            $totalImported = 0;
            $totalOrphaned = 0;
            $totalProcessed = 0;
            
            while ($currentDate <= $endDate) {
                $dayEnd = $currentDate;
                
                echo "Processing {$currentDate}...\n";
                
                // Fetch calls for this day
                $fetchSql = "
                    SELECT 
                        vl.call_date,
                        vl.lead_id as vici_lead_id,
                        vl.list_id,
                        vl.phone_number,
                        vl.campaign_id,
                        vl.status,
                        vl.length_in_sec,
                        vl.user as agent,
                        vl.term_reason,
                        vl.vendor_lead_code,
                        vl.uniqueid,
                        vl.source_id
                    FROM vicidial_log vl
                    WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$dayEnd} 23:59:59'
                    AND vl.campaign_id IS NOT NULL AND vl.campaign_id != ''
                    ORDER BY vl.call_date ASC
                ";
                
                $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                ]);
                
                if ($fetchResponse->successful()) {
                    $fetchOutput = $fetchResponse->json()['output'] ?? '';
                    $fetchOutput = str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $fetchOutput);
                    
                    $lines = explode("\n", trim($fetchOutput));
                    $dayProcessed = 0;
                    $dayImported = 0;
                    $dayOrphaned = 0;
                    
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        
                        $fields = explode("\t", $line);
                        if (count($fields) < 11) continue;
                        
                        $dayProcessed++;
                        $totalProcessed++;
                        
                        $record = [
                            'call_date' => $fields[0],
                            'vici_lead_id' => $fields[1],
                            'list_id' => $fields[2],
                            'phone_number' => $fields[3],
                            'campaign_id' => $fields[4],
                            'status' => $fields[5],
                            'length_in_sec' => intval($fields[6]),
                            'agent' => $fields[7] ?? '',
                            'term_reason' => $fields[8] ?? '',
                            'vendor_lead_code' => $fields[9] ?? '',
                            'uniqueid' => $fields[10] ?? '',
                            'source_id' => $fields[11] ?? ''
                        ];
                        
                        // Try to find the lead in Brain
                        $lead = null;
                        
                        // Try vendor_lead_code first (should be Brain's external_lead_id)
                        if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
                            $lead = \App\Models\Lead::where('external_lead_id', $record['vendor_lead_code'])->first();
                        }
                        
                        // Try source_id as well
                        if (!$lead && !empty($record['source_id']) && preg_match('/^\d{13}$/', $record['source_id'])) {
                            $lead = \App\Models\Lead::where('external_lead_id', $record['source_id'])->first();
                        }
                        
                        // Try phone number as last resort
                        if (!$lead && !empty($record['phone_number'])) {
                            $cleanPhone = preg_replace('/\D/', '', $record['phone_number']);
                            if (strlen($cleanPhone) >= 10) {
                                $lead = \App\Models\Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10) . '%')->first();
                            }
                        }
                        
                        if ($lead) {
                            // Create or update call metrics
                            try {
                                $existing = \App\Models\ViciCallMetrics::where('lead_id', $lead->id)
                                    ->where('vici_lead_id', $record['vici_lead_id'])
                                    ->first();
                                
                                if (!$existing) {
                                    \App\Models\ViciCallMetrics::create([
                                        'lead_id' => $lead->id,
                                        'vici_lead_id' => $record['vici_lead_id'],
                                        'campaign_id' => $record['campaign_id'],
                                        'list_id' => $record['list_id'],
                                        'phone_number' => $record['phone_number'],
                                        'status' => $record['status'],
                                        'last_call_time' => $record['call_date'],
                                        'call_duration' => $record['length_in_sec'],
                                        'agent_id' => $record['agent'],
                                        'total_calls' => 1,
                                        'connected' => in_array($record['status'], ['SALE', 'SOLD', 'XFER']) ? 1 : 0
                                    ]);
                                    $dayImported++;
                                    $totalImported++;
                                }
                            } catch (\Exception $e) {
                                // Skip on error
                            }
                        } else {
                            // Store as orphan
                            try {
                                \App\Models\OrphanCallLog::create([
                                    'vici_lead_id' => $record['vici_lead_id'],
                                    'phone_number' => $record['phone_number'],
                                    'call_date' => $record['call_date'],
                                    'campaign_id' => $record['campaign_id'],
                                    'status' => $record['status'],
                                    'vendor_lead_code' => $record['vendor_lead_code'],
                                    'raw_data' => json_encode($record),
                                    'matched_at' => null
                                ]);
                                $dayOrphaned++;
                                $totalOrphaned++;
                            } catch (\Exception $e) {
                                // Skip on error
                            }
                        }
                        
                        // Show progress
                        if ($totalProcessed % 1000 == 0) {
                            echo "  Processed: " . number_format($totalProcessed) . " | Imported: " . number_format($totalImported) . " | Orphaned: " . number_format($totalOrphaned) . "\r";
                        }
                    }
                    
                    echo "  ✅ {$currentDate}: Processed {$dayProcessed} | Imported {$dayImported} | Orphaned {$dayOrphaned}\n";
                }
                
                // Move to next day
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
            
            echo "\n=== IMPORT COMPLETE ===\n\n";
            echo "📊 FINAL SUMMARY:\n";
            echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
            echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
            echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
            echo "  • Success Rate: " . ($totalProcessed > 0 ? round(($totalImported / $totalProcessed) * 100, 2) : 0) . "%\n";
            echo "\n";
            echo "✅ Call log import complete!\n";
            echo "📈 Check your reports at:\n";
            echo "  • Basic: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
            echo "  • Advanced: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}


/**
 * Focused Vici Call Log Fetch - Last 30 days
 * Since today is August 15, 2025, this fetches July 16 - August 15, 2025
 */

echo "=== FOCUSED VICI CALL LOG FETCH ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Fetching last 30 days of call data (July 16 - August 15, 2025)...\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Last 30 days from August 15, 2025
$startDate = '2025-07-16';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n";

// First, get a count
$countSql = "
    SELECT COUNT(*) as count
    FROM vicidial_log
    WHERE call_date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
    AND campaign_id IS NOT NULL AND campaign_id != ''
";

try {
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        $output = trim(str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $output));
        $totalCount = intval($output);
        
        echo "📊 Total calls found: " . number_format($totalCount) . "\n\n";
        
        if ($totalCount > 0) {
            // Process in daily batches
            $currentDate = $startDate;
            $totalImported = 0;
            $totalOrphaned = 0;
            $totalProcessed = 0;
            
            while ($currentDate <= $endDate) {
                $dayEnd = $currentDate;
                
                echo "Processing {$currentDate}...\n";
                
                // Fetch calls for this day
                $fetchSql = "
                    SELECT 
                        vl.call_date,
                        vl.lead_id as vici_lead_id,
                        vl.list_id,
                        vl.phone_number,
                        vl.campaign_id,
                        vl.status,
                        vl.length_in_sec,
                        vl.user as agent,
                        vl.term_reason,
                        vl.vendor_lead_code,
                        vl.uniqueid,
                        vl.source_id
                    FROM vicidial_log vl
                    WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$dayEnd} 23:59:59'
                    AND vl.campaign_id IS NOT NULL AND vl.campaign_id != ''
                    ORDER BY vl.call_date ASC
                ";
                
                $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                ]);
                
                if ($fetchResponse->successful()) {
                    $fetchOutput = $fetchResponse->json()['output'] ?? '';
                    $fetchOutput = str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $fetchOutput);
                    
                    $lines = explode("\n", trim($fetchOutput));
                    $dayProcessed = 0;
                    $dayImported = 0;
                    $dayOrphaned = 0;
                    
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        
                        $fields = explode("\t", $line);
                        if (count($fields) < 11) continue;
                        
                        $dayProcessed++;
                        $totalProcessed++;
                        
                        $record = [
                            'call_date' => $fields[0],
                            'vici_lead_id' => $fields[1],
                            'list_id' => $fields[2],
                            'phone_number' => $fields[3],
                            'campaign_id' => $fields[4],
                            'status' => $fields[5],
                            'length_in_sec' => intval($fields[6]),
                            'agent' => $fields[7] ?? '',
                            'term_reason' => $fields[8] ?? '',
                            'vendor_lead_code' => $fields[9] ?? '',
                            'uniqueid' => $fields[10] ?? '',
                            'source_id' => $fields[11] ?? ''
                        ];
                        
                        // Try to find the lead in Brain
                        $lead = null;
                        
                        // Try vendor_lead_code first (should be Brain's external_lead_id)
                        if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
                            $lead = \App\Models\Lead::where('external_lead_id', $record['vendor_lead_code'])->first();
                        }
                        
                        // Try source_id as well
                        if (!$lead && !empty($record['source_id']) && preg_match('/^\d{13}$/', $record['source_id'])) {
                            $lead = \App\Models\Lead::where('external_lead_id', $record['source_id'])->first();
                        }
                        
                        // Try phone number as last resort
                        if (!$lead && !empty($record['phone_number'])) {
                            $cleanPhone = preg_replace('/\D/', '', $record['phone_number']);
                            if (strlen($cleanPhone) >= 10) {
                                $lead = \App\Models\Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10) . '%')->first();
                            }
                        }
                        
                        if ($lead) {
                            // Create or update call metrics
                            try {
                                $existing = \App\Models\ViciCallMetrics::where('lead_id', $lead->id)
                                    ->where('vici_lead_id', $record['vici_lead_id'])
                                    ->first();
                                
                                if (!$existing) {
                                    \App\Models\ViciCallMetrics::create([
                                        'lead_id' => $lead->id,
                                        'vici_lead_id' => $record['vici_lead_id'],
                                        'campaign_id' => $record['campaign_id'],
                                        'list_id' => $record['list_id'],
                                        'phone_number' => $record['phone_number'],
                                        'status' => $record['status'],
                                        'last_call_time' => $record['call_date'],
                                        'call_duration' => $record['length_in_sec'],
                                        'agent_id' => $record['agent'],
                                        'total_calls' => 1,
                                        'connected' => in_array($record['status'], ['SALE', 'SOLD', 'XFER']) ? 1 : 0
                                    ]);
                                    $dayImported++;
                                    $totalImported++;
                                }
                            } catch (\Exception $e) {
                                // Skip on error
                            }
                        } else {
                            // Store as orphan
                            try {
                                \App\Models\OrphanCallLog::create([
                                    'vici_lead_id' => $record['vici_lead_id'],
                                    'phone_number' => $record['phone_number'],
                                    'call_date' => $record['call_date'],
                                    'campaign_id' => $record['campaign_id'],
                                    'status' => $record['status'],
                                    'vendor_lead_code' => $record['vendor_lead_code'],
                                    'raw_data' => json_encode($record),
                                    'matched_at' => null
                                ]);
                                $dayOrphaned++;
                                $totalOrphaned++;
                            } catch (\Exception $e) {
                                // Skip on error
                            }
                        }
                        
                        // Show progress
                        if ($totalProcessed % 1000 == 0) {
                            echo "  Processed: " . number_format($totalProcessed) . " | Imported: " . number_format($totalImported) . " | Orphaned: " . number_format($totalOrphaned) . "\r";
                        }
                    }
                    
                    echo "  ✅ {$currentDate}: Processed {$dayProcessed} | Imported {$dayImported} | Orphaned {$dayOrphaned}\n";
                }
                
                // Move to next day
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
            
            echo "\n=== IMPORT COMPLETE ===\n\n";
            echo "📊 FINAL SUMMARY:\n";
            echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
            echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
            echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
            echo "  • Success Rate: " . ($totalProcessed > 0 ? round(($totalImported / $totalProcessed) * 100, 2) : 0) . "%\n";
            echo "\n";
            echo "✅ Call log import complete!\n";
            echo "📈 Check your reports at:\n";
            echo "  • Basic: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
            echo "  • Advanced: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}


