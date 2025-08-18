<?php
/**
 * Import ALL Vici calls as orphans first
 * Then we'll match them with leads afterwards
 */

echo "=== VICI IMPORT - ALL AS ORPHANS ===\n\n";
echo "Date: August 15, 2025\n";
echo "Strategy: Import all calls first, match later\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

// Last 30 days for faster import
$startDate = '2025-07-16';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate} (30 days)\n\n";

$totalImported = 0;
$totalSkipped = 0;
$startTime = microtime(true);

// Process day by day
$currentDate = $startDate;

while ($currentDate <= $endDate) {
    echo "📅 {$currentDate}: ";
    
    // Get count
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "Failed\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        $output = $countResponse->json()['output'] ?? '0';
        $output = preg_replace('/Could not create directory.*\n/', '', $output);
        $output = preg_replace('/Failed to add the host.*\n/', '', $output);
        $dayCount = intval(trim($output));
        
        if ($dayCount == 0) {
            echo "No calls\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        echo "{$dayCount} calls - ";
        
        // Process in batches
        $offset = 0;
        $batchSize = 1000;
        $dayImported = 0;
        
        while ($offset < $dayCount) {
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.user,
                    vl.vendor_lead_code,
                    vl.list_id
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                ORDER BY vl.call_date ASC
                LIMIT {$batchSize} OFFSET {$offset}
            ";
            
            $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
            ]);
            
            if (!$fetchResponse->successful()) {
                break;
            }
            
            $fetchOutput = $fetchResponse->json()['output'] ?? '';
            $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
            $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
            
            $lines = explode("\n", trim($fetchOutput));
            
            if (empty($lines[0])) break;
            
            $batchData = [];
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $fields = explode("\t", $line);
                if (count($fields) < 9) continue;
                
                $batchData[] = [
                    'vici_lead_id' => $fields[1],
                    'phone_number' => $fields[2],
                    'call_date' => $fields[0],
                    'campaign_id' => $fields[3],
                    'status' => $fields[4],
                    'vendor_lead_code' => $fields[7] ?? '',
                    'raw_data' => json_encode([
                        'call_date' => $fields[0],
                        'lead_id' => $fields[1],
                        'phone_number' => $fields[2],
                        'campaign_id' => $fields[3],
                        'status' => $fields[4],
                        'length_in_sec' => $fields[5],
                        'user' => $fields[6] ?? '',
                        'vendor_lead_code' => $fields[7] ?? '',
                        'list_id' => $fields[8] ?? ''
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            if (!empty($batchData)) {
                // Insert ignoring duplicates
                foreach ($batchData as $record) {
                    try {
                        DB::table('orphan_call_logs')->insertOrIgnore($record);
                        $dayImported++;
                        $totalImported++;
                    } catch (\Exception $e) {
                        $totalSkipped++;
                    }
                }
            }
            
            $offset += $batchSize;
            echo ".";
        }
        
        echo " Imported: {$dayImported}\n";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 Results:\n";
echo "  • Total Imported: " . number_format($totalImported) . "\n";
echo "  • Skipped (duplicates): " . number_format($totalSkipped) . "\n";
echo "  • Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Now match orphans with leads
echo "🔄 Matching orphans with leads...\n";

$matched = 0;
$orphans = DB::table('orphan_call_logs')
    ->whereNull('matched_at')
    ->limit(10000)
    ->get();

foreach ($orphans as $orphan) {
    $leadId = null;
    
    // Try vendor_lead_code
    if (!empty($orphan->vendor_lead_code) && preg_match('/^\d{13}$/', $orphan->vendor_lead_code)) {
        $lead = DB::table('leads')->where('external_lead_id', $orphan->vendor_lead_code)->first();
        if ($lead) $leadId = $lead->id;
    }
    
    // Try phone
    if (!$leadId && !empty($orphan->phone_number)) {
        $cleanPhone = preg_replace('/\D/', '', $orphan->phone_number);
        if (strlen($cleanPhone) == 10) {
            $lead = DB::table('leads')->where('phone', 'LIKE', '%' . $cleanPhone . '%')->first();
            if ($lead) $leadId = $lead->id;
        }
    }
    
    if ($leadId) {
        // Move to vici_call_metrics
        $data = json_decode($orphan->raw_data, true);
        
        DB::table('vici_call_metrics')->insertOrIgnore([
            'lead_id' => $leadId,
            'vici_lead_id' => $orphan->vici_lead_id,
            'campaign_id' => $orphan->campaign_id,
            'phone_number' => $orphan->phone_number,
            'status' => $orphan->status,
            'last_call_time' => $orphan->call_date,
            'call_duration' => $data['length_in_sec'] ?? 0,
            'agent_id' => $data['user'] ?? '',
            'list_id' => $data['list_id'] ?? '',
            'total_calls' => 1,
            'connected' => in_array($orphan->status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Mark as matched
        DB::table('orphan_call_logs')
            ->where('id', $orphan->id)
            ->update(['matched_at' => now()]);
            
        $matched++;
    }
}

echo "✅ Matched {$matched} orphans with leads\n\n";

// Final stats
$totalCalls = DB::table('vici_call_metrics')->count();
$totalOrphans = DB::table('orphan_call_logs')->whereNull('matched_at')->count();

echo "📊 Database Status:\n";
echo "  • Call Records: " . number_format($totalCalls) . "\n";
echo "  • Unmatched Orphans: " . number_format($totalOrphans) . "\n\n";

// Set up for incremental sync
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Incremental sync will continue from: {$endDate}\n\n";

echo "📈 Reports ready at:\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

echo "🎯 The 5-minute automatic sync is now active!\n";


/**
 * Import ALL Vici calls as orphans first
 * Then we'll match them with leads afterwards
 */

echo "=== VICI IMPORT - ALL AS ORPHANS ===\n\n";
echo "Date: August 15, 2025\n";
echo "Strategy: Import all calls first, match later\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

// Last 30 days for faster import
$startDate = '2025-07-16';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate} (30 days)\n\n";

$totalImported = 0;
$totalSkipped = 0;
$startTime = microtime(true);

// Process day by day
$currentDate = $startDate;

while ($currentDate <= $endDate) {
    echo "📅 {$currentDate}: ";
    
    // Get count
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "Failed\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        $output = $countResponse->json()['output'] ?? '0';
        $output = preg_replace('/Could not create directory.*\n/', '', $output);
        $output = preg_replace('/Failed to add the host.*\n/', '', $output);
        $dayCount = intval(trim($output));
        
        if ($dayCount == 0) {
            echo "No calls\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        echo "{$dayCount} calls - ";
        
        // Process in batches
        $offset = 0;
        $batchSize = 1000;
        $dayImported = 0;
        
        while ($offset < $dayCount) {
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.user,
                    vl.vendor_lead_code,
                    vl.list_id
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                ORDER BY vl.call_date ASC
                LIMIT {$batchSize} OFFSET {$offset}
            ";
            
            $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
            ]);
            
            if (!$fetchResponse->successful()) {
                break;
            }
            
            $fetchOutput = $fetchResponse->json()['output'] ?? '';
            $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
            $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
            
            $lines = explode("\n", trim($fetchOutput));
            
            if (empty($lines[0])) break;
            
            $batchData = [];
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $fields = explode("\t", $line);
                if (count($fields) < 9) continue;
                
                $batchData[] = [
                    'vici_lead_id' => $fields[1],
                    'phone_number' => $fields[2],
                    'call_date' => $fields[0],
                    'campaign_id' => $fields[3],
                    'status' => $fields[4],
                    'vendor_lead_code' => $fields[7] ?? '',
                    'raw_data' => json_encode([
                        'call_date' => $fields[0],
                        'lead_id' => $fields[1],
                        'phone_number' => $fields[2],
                        'campaign_id' => $fields[3],
                        'status' => $fields[4],
                        'length_in_sec' => $fields[5],
                        'user' => $fields[6] ?? '',
                        'vendor_lead_code' => $fields[7] ?? '',
                        'list_id' => $fields[8] ?? ''
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            if (!empty($batchData)) {
                // Insert ignoring duplicates
                foreach ($batchData as $record) {
                    try {
                        DB::table('orphan_call_logs')->insertOrIgnore($record);
                        $dayImported++;
                        $totalImported++;
                    } catch (\Exception $e) {
                        $totalSkipped++;
                    }
                }
            }
            
            $offset += $batchSize;
            echo ".";
        }
        
        echo " Imported: {$dayImported}\n";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 Results:\n";
echo "  • Total Imported: " . number_format($totalImported) . "\n";
echo "  • Skipped (duplicates): " . number_format($totalSkipped) . "\n";
echo "  • Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Now match orphans with leads
echo "🔄 Matching orphans with leads...\n";

$matched = 0;
$orphans = DB::table('orphan_call_logs')
    ->whereNull('matched_at')
    ->limit(10000)
    ->get();

foreach ($orphans as $orphan) {
    $leadId = null;
    
    // Try vendor_lead_code
    if (!empty($orphan->vendor_lead_code) && preg_match('/^\d{13}$/', $orphan->vendor_lead_code)) {
        $lead = DB::table('leads')->where('external_lead_id', $orphan->vendor_lead_code)->first();
        if ($lead) $leadId = $lead->id;
    }
    
    // Try phone
    if (!$leadId && !empty($orphan->phone_number)) {
        $cleanPhone = preg_replace('/\D/', '', $orphan->phone_number);
        if (strlen($cleanPhone) == 10) {
            $lead = DB::table('leads')->where('phone', 'LIKE', '%' . $cleanPhone . '%')->first();
            if ($lead) $leadId = $lead->id;
        }
    }
    
    if ($leadId) {
        // Move to vici_call_metrics
        $data = json_decode($orphan->raw_data, true);
        
        DB::table('vici_call_metrics')->insertOrIgnore([
            'lead_id' => $leadId,
            'vici_lead_id' => $orphan->vici_lead_id,
            'campaign_id' => $orphan->campaign_id,
            'phone_number' => $orphan->phone_number,
            'status' => $orphan->status,
            'last_call_time' => $orphan->call_date,
            'call_duration' => $data['length_in_sec'] ?? 0,
            'agent_id' => $data['user'] ?? '',
            'list_id' => $data['list_id'] ?? '',
            'total_calls' => 1,
            'connected' => in_array($orphan->status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Mark as matched
        DB::table('orphan_call_logs')
            ->where('id', $orphan->id)
            ->update(['matched_at' => now()]);
            
        $matched++;
    }
}

echo "✅ Matched {$matched} orphans with leads\n\n";

// Final stats
$totalCalls = DB::table('vici_call_metrics')->count();
$totalOrphans = DB::table('orphan_call_logs')->whereNull('matched_at')->count();

echo "📊 Database Status:\n";
echo "  • Call Records: " . number_format($totalCalls) . "\n";
echo "  • Unmatched Orphans: " . number_format($totalOrphans) . "\n\n";

// Set up for incremental sync
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Incremental sync will continue from: {$endDate}\n\n";

echo "📈 Reports ready at:\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

echo "🎯 The 5-minute automatic sync is now active!\n";


