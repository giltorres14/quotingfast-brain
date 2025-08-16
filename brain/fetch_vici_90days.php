<?php
/**
 * Fetch Last 90 Days of Vici Call Logs
 * From May 17, 2025 to August 15, 2025
 */

echo "=== VICI 90-DAY CALL LOG IMPORT ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Fetching last 90 days (May 17 - August 15, 2025)...\n\n";

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

// 90 days from August 15, 2025
$startDate = '2025-05-17';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n";
echo "⏳ This will take several minutes. Please be patient...\n\n";

// First, get a count
$countSql = "
    SELECT COUNT(*) as count
    FROM vicidial_log
    WHERE call_date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
    AND campaign_id IS NOT NULL AND campaign_id != ''
";

try {
    $response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        $output = trim(str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $output));
        $totalCount = intval($output);
        
        echo "📊 Total calls to import: " . number_format($totalCount) . "\n";
        echo "💾 Estimated storage: ~" . round($totalCount * 500 / 1024 / 1024) . " MB\n\n";
        
        if ($totalCount > 0) {
            // Process in daily batches
            $currentDate = $startDate;
            $totalImported = 0;
            $totalOrphaned = 0;
            $totalProcessed = 0;
            $totalSkipped = 0;
            $startTime = microtime(true);
            
            while ($currentDate <= $endDate) {
                echo "📅 Processing {$currentDate}...\n";
                
                // Fetch calls for this day with LIMIT to prevent memory issues
                $offset = 0;
                $limit = 5000;
                $dayImported = 0;
                $dayOrphaned = 0;
                $dayProcessed = 0;
                
                while (true) {
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
                            vl.vendor_lead_code,
                            vl.source_id
                        FROM vicidial_log vl
                        WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                        AND vl.campaign_id IS NOT NULL AND vl.campaign_id != ''
                        ORDER BY vl.call_date ASC
                        LIMIT {$limit} OFFSET {$offset}
                    ";
                    
                    $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                    ]);
                    
                    if (!$fetchResponse->successful()) {
                        echo "  ⚠️ Failed to fetch batch at offset {$offset}\n";
                        break;
                    }
                    
                    $fetchOutput = $fetchResponse->json()['output'] ?? '';
                    $fetchOutput = str_replace(["Could not create directory '/var/www/.ssh' (Permission denied).", "Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts)."], "", $fetchOutput);
                    
                    $lines = explode("\n", trim($fetchOutput));
                    
                    if (count($lines) == 0 || (count($lines) == 1 && empty($lines[0]))) {
                        // No more data for this day
                        break;
                    }
                    
                    $batchToInsert = [];
                    $orphansToInsert = [];
                    
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        
                        $fields = explode("\t", $line);
                        if (count($fields) < 9) continue;
                        
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
                            'vendor_lead_code' => $fields[8] ?? '',
                            'source_id' => $fields[9] ?? ''
                        ];
                        
                        // Try to find the lead in Brain
                        $lead = null;
                        $leadId = null;
                        
                        // Try vendor_lead_code first (should be Brain's external_lead_id)
                        if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
                            $lead = DB::table('leads')
                                ->where('external_lead_id', $record['vendor_lead_code'])
                                ->select('id')
                                ->first();
                            if ($lead) $leadId = $lead->id;
                        }
                        
                        // Try source_id as well
                        if (!$leadId && !empty($record['source_id']) && preg_match('/^\d{13}$/', $record['source_id'])) {
                            $lead = DB::table('leads')
                                ->where('external_lead_id', $record['source_id'])
                                ->select('id')
                                ->first();
                            if ($lead) $leadId = $lead->id;
                        }
                        
                        if ($leadId) {
                            // Check if already exists
                            $exists = DB::table('vici_call_metrics')
                                ->where('lead_id', $leadId)
                                ->where('vici_lead_id', $record['vici_lead_id'])
                                ->exists();
                            
                            if (!$exists) {
                                $batchToInsert[] = [
                                    'lead_id' => $leadId,
                                    'vici_lead_id' => $record['vici_lead_id'],
                                    'campaign_id' => $record['campaign_id'],
                                    'list_id' => $record['list_id'],
                                    'phone_number' => $record['phone_number'],
                                    'status' => $record['status'],
                                    'last_call_time' => $record['call_date'],
                                    'call_duration' => $record['length_in_sec'],
                                    'agent_id' => $record['agent'],
                                    'total_calls' => 1,
                                    'connected' => in_array($record['status'], ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                                $dayImported++;
                            } else {
                                $totalSkipped++;
                            }
                        } else {
                            // Store as orphan
                            $orphansToInsert[] = [
                                'vici_lead_id' => $record['vici_lead_id'],
                                'phone_number' => $record['phone_number'],
                                'call_date' => $record['call_date'],
                                'campaign_id' => $record['campaign_id'],
                                'status' => $record['status'],
                                'vendor_lead_code' => $record['vendor_lead_code'],
                                'raw_data' => json_encode($record),
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $dayOrphaned++;
                        }
                    }
                    
                    // Bulk insert for better performance
                    if (!empty($batchToInsert)) {
                        DB::table('vici_call_metrics')->insert($batchToInsert);
                        $totalImported += count($batchToInsert);
                    }
                    
                    if (!empty($orphansToInsert)) {
                        DB::table('orphan_call_logs')->insert($orphansToInsert);
                        $totalOrphaned += count($orphansToInsert);
                    }
                    
                    // Show progress
                    $elapsed = round(microtime(true) - $startTime, 2);
                    $rate = $totalProcessed > 0 ? round($totalProcessed / $elapsed) : 0;
                    echo "  Progress: " . number_format($totalProcessed) . " processed | " . 
                         number_format($totalImported) . " imported | " . 
                         number_format($totalOrphaned) . " orphaned | " .
                         number_format($totalSkipped) . " skipped | " .
                         "{$rate} calls/sec\r";
                    
                    $offset += $limit;
                    
                    if (count($lines) < $limit) {
                        // Last batch for this day
                        break;
                    }
                }
                
                echo "\n  ✅ {$currentDate}: Processed {$dayProcessed} | Imported {$dayImported} | Orphaned {$dayOrphaned}\n\n";
                
                // Move to next day
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
            
            $totalTime = round(microtime(true) - $startTime, 2);
            
            echo "\n=== IMPORT COMPLETE ===\n\n";
            echo "📊 FINAL SUMMARY:\n";
            echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
            echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
            echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
            echo "  • Skipped (duplicates): " . number_format($totalSkipped) . "\n";
            echo "  • Match Rate: " . ($totalProcessed > 0 ? round(($totalImported / $totalProcessed) * 100, 2) : 0) . "%\n";
            echo "  • Total Time: " . gmdate("H:i:s", $totalTime) . "\n";
            echo "  • Processing Speed: " . round($totalProcessed / $totalTime) . " calls/sec\n";
            echo "\n";
            
            // Update cache for incremental sync
            \Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
            echo "✅ Set last sync time to: {$endDate}\n";
            echo "✅ Incremental sync will continue from this point\n\n";
            
            echo "📈 Your reports are ready!\n";
            echo "  • Basic: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
            echo "  • Advanced: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";
        } else {
            echo "⚠️ No calls found in the specified date range.\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}


