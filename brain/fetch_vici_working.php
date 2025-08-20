<?php
/**
 * Working Vici Call Log Import - Last 7 Days First
 * Starting with a smaller test to ensure it works
 */

echo "=== VICI CALL LOG IMPORT (WORKING VERSION) ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Starting with last 7 days as a test...\n\n";

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

// Start with just 7 days to test
$startDate = '2025-08-08';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n\n";

// Test the connection first
echo "Testing Vici connection...\n";
$testSql = "SELECT COUNT(*) as count FROM vicidial_log WHERE call_date >= '2025-08-14 00:00:00' LIMIT 1";

try {
    $testResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($testSql) . " 2>&1"
    ]);
    
    if (!$testResponse->successful()) {
        echo "❌ Proxy connection failed. Status: " . $testResponse->status() . "\n";
        exit(1);
    }
    
    $output = $testResponse->json()['output'] ?? '';
    // Clean the SSH warnings
    $output = preg_replace('/Could not create directory.*\n/', '', $output);
    $output = preg_replace('/Failed to add the host.*\n/', '', $output);
    $output = trim($output);
    
    echo "✅ Connection successful! Found " . number_format(intval($output)) . " calls yesterday.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Connection test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Now process day by day
$currentDate = $startDate;
$totalImported = 0;
$totalOrphaned = 0;
$totalProcessed = 0;
$startTime = microtime(true);

while ($currentDate <= $endDate) {
    echo "📅 Processing {$currentDate}...\n";
    
    // First get count for this day
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59' AND campaign_id IS NOT NULL AND campaign_id != ''";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "  ⚠️ Failed to get count for {$currentDate}\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        $countOutput = $countResponse->json()['output'] ?? '0';
        $countOutput = preg_replace('/Could not create directory.*\n/', '', $countOutput);
        $countOutput = preg_replace('/Failed to add the host.*\n/', '', $countOutput);
        $dayCount = intval(trim($countOutput));
        
        echo "  Found {$dayCount} calls\n";
        
        if ($dayCount > 0) {
            // Fetch the actual data
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.list_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.user,
                    vl.vendor_lead_code,
                    vl.source_id
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                AND vl.campaign_id IS NOT NULL AND vl.campaign_id != ''
                ORDER BY vl.call_date ASC
                LIMIT 5000
            ";
            
            $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
            ]);
            
            if (!$fetchResponse->successful()) {
                echo "  ⚠️ Failed to fetch data for {$currentDate}\n";
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                continue;
            }
            
            $fetchOutput = $fetchResponse->json()['output'] ?? '';
            $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
            $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
            
            $lines = explode("\n", trim($fetchOutput));
            $dayImported = 0;
            $dayOrphaned = 0;
            $dayProcessed = 0;
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $fields = explode("\t", $line);
                if (count($fields) < 9) continue;
                
                $dayProcessed++;
                $totalProcessed++;
                
                // Parse the record
                $callDate = $fields[0];
                $viciLeadId = $fields[1];
                $listId = $fields[2];
                $phoneNumber = $fields[3];
                $campaignId = $fields[4];
                $status = $fields[5];
                $lengthInSec = intval($fields[6]);
                $agent = $fields[7] ?? '';
                $vendorLeadCode = $fields[8] ?? '';
                $sourceId = $fields[9] ?? '';
                
                // Try to find the lead in Brain
                $leadId = null;
                
                // Try vendor_lead_code first (13-digit Brain ID)
                if (!empty($vendorLeadCode) && preg_match('/^\d{13}$/', $vendorLeadCode)) {
                    $lead = DB::table('leads')
                        ->where('external_lead_id', $vendorLeadCode)
                        ->select('id')
                        ->first();
                    if ($lead) $leadId = $lead->id;
                }
                
                // Try source_id as backup
                if (!$leadId && !empty($sourceId) && preg_match('/^\d{13}$/', $sourceId)) {
                    $lead = DB::table('leads')
                        ->where('external_lead_id', $sourceId)
                        ->select('id')
                        ->first();
                    if ($lead) $leadId = $lead->id;
                }
                
                if ($leadId) {
                    // Check if already exists
                    $exists = DB::table('vici_call_metrics')
                        ->where('lead_id', $leadId)
                        ->where('vici_lead_id', $viciLeadId)
                        ->exists();
                    
                    if (!$exists) {
                        try {
                            DB::table('vici_call_metrics')->insert([
                                'lead_id' => $leadId,
                                'vici_lead_id' => $viciLeadId,
                                'campaign_id' => $campaignId,
                                'list_id' => $listId,
                                'phone_number' => $phoneNumber,
                                'status' => $status,
                                'last_call_time' => $callDate,
                                'call_duration' => $lengthInSec,
                                'agent_id' => $agent,
                                'total_calls' => 1,
                                'connected' => in_array($status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayImported++;
                            $totalImported++;
                        } catch (\Exception $e) {
                            // Skip on error
                        }
                    }
                } else {
                    // Store as orphan
                    try {
                        DB::table('orphan_call_logs')->insertOrIgnore([
                            'vici_lead_id' => $viciLeadId,
                            'phone_number' => $phoneNumber,
                            'call_date' => $callDate,
                            'campaign_id' => $campaignId,
                            'status' => $status,
                            'vendor_lead_code' => $vendorLeadCode,
                            'raw_data' => json_encode([
                                'call_date' => $callDate,
                                'vici_lead_id' => $viciLeadId,
                                'list_id' => $listId,
                                'phone_number' => $phoneNumber,
                                'campaign_id' => $campaignId,
                                'status' => $status,
                                'length_in_sec' => $lengthInSec,
                                'agent' => $agent,
                                'vendor_lead_code' => $vendorLeadCode,
                                'source_id' => $sourceId
                            ]),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $dayOrphaned++;
                        $totalOrphaned++;
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
                
                // Show progress every 100 records
                if ($totalProcessed % 100 == 0) {
                    echo "  Progress: " . number_format($totalProcessed) . " processed | " . 
                         number_format($totalImported) . " imported | " . 
                         number_format($totalOrphaned) . " orphaned\r";
                }
            }
            
            echo "\n  ✅ {$currentDate}: Processed {$dayProcessed} | Imported {$dayImported} | Orphaned {$dayOrphaned}\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error processing {$currentDate}: " . $e->getMessage() . "\n";
    }
    
    // Move to next day
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    echo "\n";
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 FINAL SUMMARY:\n";
echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";

if ($totalProcessed > 0) {
    echo "  • Match Rate: " . round(($totalImported / $totalProcessed) * 100, 2) . "%\n";
    echo "  • Processing Speed: " . round($totalProcessed / $totalTime) . " calls/sec\n";
}

echo "  • Total Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Update cache for incremental sync
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Set last sync time to: {$endDate}\n";
echo "✅ Incremental sync will continue from this point\n\n";

echo "📈 Check your reports:\n";
echo "  • Basic: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • Advanced: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

if ($totalProcessed > 0) {
    echo "🎯 Next step: Run the full 90-day import with:\n";
    echo "   php fetch_vici_90days_fixed.php\n\n";
}


/**
 * Working Vici Call Log Import - Last 7 Days First
 * Starting with a smaller test to ensure it works
 */

echo "=== VICI CALL LOG IMPORT (WORKING VERSION) ===\n\n";
echo "Current Date: August 15, 2025\n";
echo "Starting with last 7 days as a test...\n\n";

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

// Start with just 7 days to test
$startDate = '2025-08-08';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n\n";

// Test the connection first
echo "Testing Vici connection...\n";
$testSql = "SELECT COUNT(*) as count FROM vicidial_log WHERE call_date >= '2025-08-14 00:00:00' LIMIT 1";

try {
    $testResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($testSql) . " 2>&1"
    ]);
    
    if (!$testResponse->successful()) {
        echo "❌ Proxy connection failed. Status: " . $testResponse->status() . "\n";
        exit(1);
    }
    
    $output = $testResponse->json()['output'] ?? '';
    // Clean the SSH warnings
    $output = preg_replace('/Could not create directory.*\n/', '', $output);
    $output = preg_replace('/Failed to add the host.*\n/', '', $output);
    $output = trim($output);
    
    echo "✅ Connection successful! Found " . number_format(intval($output)) . " calls yesterday.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Connection test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Now process day by day
$currentDate = $startDate;
$totalImported = 0;
$totalOrphaned = 0;
$totalProcessed = 0;
$startTime = microtime(true);

while ($currentDate <= $endDate) {
    echo "📅 Processing {$currentDate}...\n";
    
    // First get count for this day
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59' AND campaign_id IS NOT NULL AND campaign_id != ''";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "  ⚠️ Failed to get count for {$currentDate}\n";
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            continue;
        }
        
        $countOutput = $countResponse->json()['output'] ?? '0';
        $countOutput = preg_replace('/Could not create directory.*\n/', '', $countOutput);
        $countOutput = preg_replace('/Failed to add the host.*\n/', '', $countOutput);
        $dayCount = intval(trim($countOutput));
        
        echo "  Found {$dayCount} calls\n";
        
        if ($dayCount > 0) {
            // Fetch the actual data
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.list_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.user,
                    vl.vendor_lead_code,
                    vl.source_id
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                AND vl.campaign_id IS NOT NULL AND vl.campaign_id != ''
                ORDER BY vl.call_date ASC
                LIMIT 5000
            ";
            
            $fetchResponse = Http::timeout(120)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
            ]);
            
            if (!$fetchResponse->successful()) {
                echo "  ⚠️ Failed to fetch data for {$currentDate}\n";
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                continue;
            }
            
            $fetchOutput = $fetchResponse->json()['output'] ?? '';
            $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
            $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
            
            $lines = explode("\n", trim($fetchOutput));
            $dayImported = 0;
            $dayOrphaned = 0;
            $dayProcessed = 0;
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $fields = explode("\t", $line);
                if (count($fields) < 9) continue;
                
                $dayProcessed++;
                $totalProcessed++;
                
                // Parse the record
                $callDate = $fields[0];
                $viciLeadId = $fields[1];
                $listId = $fields[2];
                $phoneNumber = $fields[3];
                $campaignId = $fields[4];
                $status = $fields[5];
                $lengthInSec = intval($fields[6]);
                $agent = $fields[7] ?? '';
                $vendorLeadCode = $fields[8] ?? '';
                $sourceId = $fields[9] ?? '';
                
                // Try to find the lead in Brain
                $leadId = null;
                
                // Try vendor_lead_code first (13-digit Brain ID)
                if (!empty($vendorLeadCode) && preg_match('/^\d{13}$/', $vendorLeadCode)) {
                    $lead = DB::table('leads')
                        ->where('external_lead_id', $vendorLeadCode)
                        ->select('id')
                        ->first();
                    if ($lead) $leadId = $lead->id;
                }
                
                // Try source_id as backup
                if (!$leadId && !empty($sourceId) && preg_match('/^\d{13}$/', $sourceId)) {
                    $lead = DB::table('leads')
                        ->where('external_lead_id', $sourceId)
                        ->select('id')
                        ->first();
                    if ($lead) $leadId = $lead->id;
                }
                
                if ($leadId) {
                    // Check if already exists
                    $exists = DB::table('vici_call_metrics')
                        ->where('lead_id', $leadId)
                        ->where('vici_lead_id', $viciLeadId)
                        ->exists();
                    
                    if (!$exists) {
                        try {
                            DB::table('vici_call_metrics')->insert([
                                'lead_id' => $leadId,
                                'vici_lead_id' => $viciLeadId,
                                'campaign_id' => $campaignId,
                                'list_id' => $listId,
                                'phone_number' => $phoneNumber,
                                'status' => $status,
                                'last_call_time' => $callDate,
                                'call_duration' => $lengthInSec,
                                'agent_id' => $agent,
                                'total_calls' => 1,
                                'connected' => in_array($status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayImported++;
                            $totalImported++;
                        } catch (\Exception $e) {
                            // Skip on error
                        }
                    }
                } else {
                    // Store as orphan
                    try {
                        DB::table('orphan_call_logs')->insertOrIgnore([
                            'vici_lead_id' => $viciLeadId,
                            'phone_number' => $phoneNumber,
                            'call_date' => $callDate,
                            'campaign_id' => $campaignId,
                            'status' => $status,
                            'vendor_lead_code' => $vendorLeadCode,
                            'raw_data' => json_encode([
                                'call_date' => $callDate,
                                'vici_lead_id' => $viciLeadId,
                                'list_id' => $listId,
                                'phone_number' => $phoneNumber,
                                'campaign_id' => $campaignId,
                                'status' => $status,
                                'length_in_sec' => $lengthInSec,
                                'agent' => $agent,
                                'vendor_lead_code' => $vendorLeadCode,
                                'source_id' => $sourceId
                            ]),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $dayOrphaned++;
                        $totalOrphaned++;
                    } catch (\Exception $e) {
                        // Skip on error
                    }
                }
                
                // Show progress every 100 records
                if ($totalProcessed % 100 == 0) {
                    echo "  Progress: " . number_format($totalProcessed) . " processed | " . 
                         number_format($totalImported) . " imported | " . 
                         number_format($totalOrphaned) . " orphaned\r";
                }
            }
            
            echo "\n  ✅ {$currentDate}: Processed {$dayProcessed} | Imported {$dayImported} | Orphaned {$dayOrphaned}\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error processing {$currentDate}: " . $e->getMessage() . "\n";
    }
    
    // Move to next day
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    echo "\n";
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 FINAL SUMMARY:\n";
echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";

if ($totalProcessed > 0) {
    echo "  • Match Rate: " . round(($totalImported / $totalProcessed) * 100, 2) . "%\n";
    echo "  • Processing Speed: " . round($totalProcessed / $totalTime) . " calls/sec\n";
}

echo "  • Total Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Update cache for incremental sync
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Set last sync time to: {$endDate}\n";
echo "✅ Incremental sync will continue from this point\n\n";

echo "📈 Check your reports:\n";
echo "  • Basic: https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • Advanced: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

if ($totalProcessed > 0) {
    echo "🎯 Next step: Run the full 90-day import with:\n";
    echo "   php fetch_vici_90days_fixed.php\n\n";
}








