<?php

echo "=== COMPLETE VICI CALL LOG FETCH ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use App\Models\OrphanCallLog;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Configuration
$daysBack = isset($argv[1]) ? intval($argv[1]) : 90;
$batchDays = 7; // Process 7 days at a time to avoid timeouts

$endDate = Carbon::now();
$startDate = Carbon::now()->subDays($daysBack);

echo "📅 Fetching call logs from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
echo "📊 Processing in {$batchDays}-day batches\n\n";

$totalStats = [
    'total_calls' => 0,
    'imported' => 0,
    'orphaned' => 0,
    'updated' => 0,
    'errors' => 0,
    'by_campaign' => [],
    'by_status' => [],
    'by_date' => []
];

// Process in weekly batches
$currentDate = clone $startDate;
$batchNum = 1;

while ($currentDate->lt($endDate)) {
    $batchStart = clone $currentDate;
    $batchEnd = clone $currentDate;
    $batchEnd->addDays($batchDays);
    
    if ($batchEnd->gt($endDate)) {
        $batchEnd = clone $endDate;
    }
    
    echo "Batch $batchNum: {$batchStart->format('Y-m-d')} to {$batchEnd->format('Y-m-d')}...\n";
    
    // Build SQL query
    $sql = "
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
            vl.closecallid,
            vl.comments
        FROM vicidial_log vl
        WHERE vl.call_date BETWEEN '{$batchStart->format('Y-m-d 00:00:00')}' 
            AND '{$batchEnd->format('Y-m-d 23:59:59')}'
        AND vl.campaign_id IS NOT NULL 
        AND vl.campaign_id != ''
        ORDER BY vl.call_date ASC
    ";
    
    try {
        // Execute via proxy
        echo "  Fetching from Vici...\n";
        $response = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($sql) . " 2>&1"
        ]);
        
        if (!$response->successful()) {
            echo "  ❌ Failed to fetch batch $batchNum\n";
            $currentDate->addDays($batchDays);
            $batchNum++;
            continue;
        }
        
        $output = $response->json()['output'] ?? '';
        
        // Clean output
        $output = str_replace("Could not create directory '/var/www/.ssh' (Permission denied).", "", $output);
        $output = str_replace("Failed to add the host to the list of known hosts (/var/www/.ssh/known_hosts).", "", $output);
        
        // Parse tab-separated output
        $lines = explode("\n", trim($output));
        $batchCalls = 0;
        $batchImported = 0;
        $batchOrphaned = 0;
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $fields = explode("\t", $line);
            if (count($fields) < 11) continue;
            
            $record = [
                'call_date' => $fields[0],
                'vici_lead_id' => $fields[1],
                'list_id' => $fields[2],
                'phone_number' => $fields[3],
                'campaign_id' => $fields[4],
                'status' => $fields[5],
                'length_in_sec' => intval($fields[6]),
                'agent' => $fields[7],
                'term_reason' => $fields[8],
                'vendor_lead_code' => $fields[9],
                'uniqueid' => $fields[10],
                'closecallid' => $fields[11] ?? null,
                'comments' => $fields[12] ?? null
            ];
            
            $batchCalls++;
            $totalStats['total_calls']++;
            
            // Track by campaign
            $campaign = $record['campaign_id'];
            $totalStats['by_campaign'][$campaign] = ($totalStats['by_campaign'][$campaign] ?? 0) + 1;
            
            // Track by status
            $status = $record['status'];
            $totalStats['by_status'][$status] = ($totalStats['by_status'][$status] ?? 0) + 1;
            
            // Track by date
            $date = substr($record['call_date'], 0, 10);
            $totalStats['by_date'][$date] = ($totalStats['by_date'][$date] ?? 0) + 1;
            
            // Try to find the lead
            $lead = null;
            
            // First try vendor_lead_code (Brain ID)
            if (!empty($record['vendor_lead_code']) && preg_match('/^\d{13}$/', $record['vendor_lead_code'])) {
                $lead = Lead::where('external_lead_id', $record['vendor_lead_code'])->first();
            }
            
            // Fallback to phone number
            if (!$lead && !empty($record['phone_number'])) {
                $cleanPhone = preg_replace('/\D/', '', $record['phone_number']);
                if (strlen($cleanPhone) >= 10) {
                    // Try exact match first
                    $lead = Lead::where('phone', $cleanPhone)->first();
                    
                    // Try last 10 digits
                    if (!$lead) {
                        $lead = Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10))->first();
                    }
                }
            }
            
            if ($lead) {
                // Update or create call metrics
                $metrics = ViciCallMetrics::firstOrNew(['lead_id' => $lead->id]);
                
                $isNew = !$metrics->exists;
                
                // Update basic info
                $metrics->phone_number = $record['phone_number'];
                $metrics->campaign_id = $record['campaign_id'];
                $metrics->agent_id = $record['agent'];
                $metrics->status = $record['status'];
                $metrics->last_call_date = $record['call_date'];
                
                // Update counters
                $metrics->total_calls = ($metrics->total_calls ?? 0) + 1;
                
                if ($record['length_in_sec'] > 0) {
                    $metrics->talk_time = ($metrics->talk_time ?? 0) + $record['length_in_sec'];
                    $metrics->connected = true;
                }
                
                // Update dispositions (keep last 100)
                $dispositions = json_decode($metrics->dispositions ?? '[]', true);
                $dispositions[] = [
                    'status' => $record['status'],
                    'date' => $record['call_date'],
                    'agent' => $record['agent'],
                    'duration' => $record['length_in_sec']
                ];
                
                if (count($dispositions) > 100) {
                    $dispositions = array_slice($dispositions, -100);
                }
                
                $metrics->dispositions = json_encode($dispositions);
                
                // Store additional data
                $additionalData = json_decode($metrics->additional_data ?? '{}', true);
                $additionalData['last_vici_id'] = $record['vici_lead_id'];
                $additionalData['last_list_id'] = $record['list_id'];
                $additionalData['last_uniqueid'] = $record['uniqueid'];
                $metrics->additional_data = json_encode($additionalData);
                
                $metrics->save();
                
                $batchImported++;
                $totalStats[$isNew ? 'imported' : 'updated']++;
                
            } else {
                // Store as orphan for later matching
                OrphanCallLog::updateOrCreate(
                    [
                        'vici_lead_id' => $record['vici_lead_id'],
                        'call_date' => $record['call_date']
                    ],
                    [
                        'phone_number' => $record['phone_number'],
                        'vendor_lead_code' => $record['vendor_lead_code'],
                        'campaign_id' => $record['campaign_id'],
                        'agent_id' => $record['agent'],
                        'status' => $record['status'],
                        'talk_time' => $record['length_in_sec'],
                        'call_data' => json_encode($record)
                    ]
                );
                
                $batchOrphaned++;
                $totalStats['orphaned']++;
            }
            
            // Progress indicator
            if ($batchCalls % 1000 == 0) {
                echo "    Processed " . number_format($batchCalls) . " calls...\r";
            }
        }
        
        echo "  ✅ Batch $batchNum complete: $batchCalls calls, $batchImported imported, $batchOrphaned orphaned\n";
        
    } catch (\Exception $e) {
        echo "  ❌ Error in batch $batchNum: " . $e->getMessage() . "\n";
        $totalStats['errors']++;
    }
    
    // Move to next batch
    $currentDate->addDays($batchDays);
    $batchNum++;
    
    // Small delay between batches
    sleep(1);
}

// Set last sync timestamp
Cache::put('vici_last_complete_sync', Carbon::now(), now()->addDays(30));
Cache::put('vici_last_incremental_sync', Carbon::now(), now()->addDays(7));

echo "\n=== FETCH COMPLETE ===\n\n";

echo "📊 SUMMARY:\n";
echo "  • Total Calls: " . number_format($totalStats['total_calls']) . "\n";
echo "  • Imported to Brain: " . number_format($totalStats['imported']) . "\n";
echo "  • Updated Existing: " . number_format($totalStats['updated']) . "\n";
echo "  • Orphaned (no match): " . number_format($totalStats['orphaned']) . "\n";
echo "  • Errors: " . $totalStats['errors'] . "\n\n";

echo "📈 BY CAMPAIGN (Top 10):\n";
arsort($totalStats['by_campaign']);
foreach (array_slice($totalStats['by_campaign'], 0, 10, true) as $campaign => $count) {
    echo "  • $campaign: " . number_format($count) . "\n";
}

echo "\n📊 BY STATUS (Top 20):\n";
arsort($totalStats['by_status']);
foreach (array_slice($totalStats['by_status'], 0, 20, true) as $status => $count) {
    $pct = round($count / $totalStats['total_calls'] * 100, 1);
    echo "  • $status: " . number_format($count) . " ($pct%)\n";
}

echo "\n📅 CALLS BY DATE (Last 10 days with data):\n";
krsort($totalStats['by_date']);
foreach (array_slice($totalStats['by_date'], 0, 10, true) as $date => $count) {
    echo "  • $date: " . number_format($count) . "\n";
}

// Now try to match orphaned calls
echo "\n🔄 ATTEMPTING TO MATCH ORPHANED CALLS...\n";
$orphanCount = OrphanCallLog::unmatched()->count();

if ($orphanCount > 0) {
    echo "Found $orphanCount unmatched orphan calls. Running match process...\n";
    \Artisan::call('vici:match-orphan-calls');
    echo "Match process complete.\n";
}

echo "\n✅ ALL DONE!\n";
echo "The incremental sync will now run every 5 minutes to keep data current.\n";
echo "You can view the comprehensive reports at:\n";
echo "https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n";


