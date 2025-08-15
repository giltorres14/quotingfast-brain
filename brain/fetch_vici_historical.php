<?php
/**
 * Fetch Historical Vici Call Logs
 * This fetches older data since we're in August 2025
 */

echo "=== VICI HISTORICAL CALL LOG FETCH ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Looking for historical call data...\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Since today is August 15, 2025, let's look back further
// The Vici data is likely from late 2024 to early 2025
$periods = [
    ['start' => '2024-10-01', 'end' => '2024-10-31', 'name' => 'October 2024'],
    ['start' => '2024-11-01', 'end' => '2024-11-30', 'name' => 'November 2024'],
    ['start' => '2024-12-01', 'end' => '2024-12-31', 'name' => 'December 2024'],
    ['start' => '2025-01-01', 'end' => '2025-01-31', 'name' => 'January 2025'],
    ['start' => '2025-02-01', 'end' => '2025-02-28', 'name' => 'February 2025'],
    ['start' => '2025-03-01', 'end' => '2025-03-31', 'name' => 'March 2025'],
    ['start' => '2025-04-01', 'end' => '2025-04-30', 'name' => 'April 2025'],
    ['start' => '2025-05-01', 'end' => '2025-05-31', 'name' => 'May 2025'],
    ['start' => '2025-06-01', 'end' => '2025-06-30', 'name' => 'June 2025'],
    ['start' => '2025-07-01', 'end' => '2025-07-31', 'name' => 'July 2025'],
    ['start' => '2025-08-01', 'end' => '2025-08-15', 'name' => 'August 2025 (to date)'],
];

$totalCalls = 0;
$totalImported = 0;
$totalOrphaned = 0;

foreach ($periods as $period) {
    echo "Checking {$period['name']}...\n";
    
    // Build SQL query to check for data
    $sql = "
        SELECT COUNT(*) as count
        FROM vicidial_log
        WHERE call_date BETWEEN '{$period['start']} 00:00:00' 
            AND '{$period['end']} 23:59:59'
        AND campaign_id IS NOT NULL 
        AND campaign_id != ''
    ";
    
    try {
        // Check count first
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($sql) . " 2>&1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            $output = trim(str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $output));
            
            $count = intval($output);
            if ($count > 0) {
                echo "  ✅ Found {$count} calls in {$period['name']}\n";
                
                // Now fetch the actual data
                echo "  Fetching call details...\n";
                
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
                        vl.uniqueid
                    FROM vicidial_log vl
                    WHERE vl.call_date BETWEEN '{$period['start']} 00:00:00' 
                        AND '{$period['end']} 23:59:59'
                    AND vl.campaign_id IS NOT NULL 
                    AND vl.campaign_id != ''
                    ORDER BY vl.call_date ASC
                    LIMIT 10000
                ";
                
                $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                ]);
                
                if ($fetchResponse->successful()) {
                    $fetchOutput = $fetchResponse->json()['output'] ?? '';
                    $fetchOutput = str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $fetchOutput);
                    
                    $lines = explode("\n", trim($fetchOutput));
                    $periodCalls = 0;
                    $periodImported = 0;
                    $periodOrphaned = 0;
                    
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        
                        $fields = explode("\t", $line);
                        if (count($fields) < 10) continue;
                        
                        $periodCalls++;
                        $totalCalls++;
                        
                        // Process the call record
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
                            'uniqueid' => $fields[10] ?? ''
                        ];
                        
                        // Try to find the lead in Brain
                        $lead = null;
                        
                        // First try vendor_lead_code (Brain ID)
                        if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
                            $lead = \App\Models\Lead::where('external_lead_id', $record['vendor_lead_code'])->first();
                        }
                        
                        // If no lead, try phone number
                        if (!$lead && !empty($record['phone_number'])) {
                            $cleanPhone = preg_replace('/\D/', '', $record['phone_number']);
                            if (strlen($cleanPhone) >= 10) {
                                $lead = \App\Models\Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10) . '%')->first();
                            }
                        }
                        
                        if ($lead) {
                            // Update or create call metrics
                            $metrics = \App\Models\ViciCallMetrics::updateOrCreate(
                                [
                                    'lead_id' => $lead->id,
                                    'vici_lead_id' => $record['vici_lead_id']
                                ],
                                [
                                    'campaign_id' => $record['campaign_id'],
                                    'list_id' => $record['list_id'],
                                    'phone_number' => $record['phone_number'],
                                    'status' => $record['status'],
                                    'last_call_time' => $record['call_date'],
                                    'call_duration' => $record['length_in_sec'],
                                    'agent_id' => $record['agent'],
                                    'total_calls' => DB::raw('COALESCE(total_calls, 0) + 1'),
                                    'connected' => in_array($record['status'], ['SALE', 'SOLD', 'XFER']) ? 1 : 0
                                ]
                            );
                            $periodImported++;
                            $totalImported++;
                        } else {
                            // Store as orphan
                            \App\Models\OrphanCallLog::create([
                                'vici_lead_id' => $record['vici_lead_id'],
                                'phone_number' => $record['phone_number'],
                                'call_date' => $record['call_date'],
                                'campaign_id' => $record['campaign_id'],
                                'status' => $record['status'],
                                'vendor_lead_code' => $record['vendor_lead_code'],
                                'raw_data' => json_encode($record)
                            ]);
                            $periodOrphaned++;
                            $totalOrphaned++;
                        }
                    }
                    
                    echo "  📊 {$period['name']}: {$periodCalls} calls, {$periodImported} imported, {$periodOrphaned} orphaned\n";
                }
            } else {
                echo "  ⚠️  No calls found in {$period['name']}\n";
            }
        }
    } catch (\Exception $e) {
        echo "  ❌ Error checking {$period['name']}: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "\n=== FETCH COMPLETE ===\n\n";
echo "📊 TOTAL SUMMARY:\n";
echo "  • Total Calls Found: " . number_format($totalCalls) . "\n";
echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
echo "\n";
echo "✅ Historical data import complete!\n";
echo "📈 Check your reports at: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n\n";

