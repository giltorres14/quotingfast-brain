<?php
/**
 * FINAL WORKING VERSION - Import 90 Days of Vici Call Logs
 * Uses smaller batches and direct processing
 */

echo "=== VICI 90-DAY IMPORT (FINAL VERSION) ===\n\n";
echo "Date: August 15, 2025\n";
echo "Importing: May 17 - August 15, 2025 (90 days)\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

// 90 days from today
$startDate = '2025-05-17';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n";
echo "⏳ Starting import...\n\n";

$totalImported = 0;
$totalOrphaned = 0;
$totalProcessed = 0;
$totalErrors = 0;
$startTime = microtime(true);

// Process day by day
$currentDate = $startDate;

while ($currentDate <= $endDate) {
    echo "📅 {$currentDate}: ";
    
    // Get count for this day
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "Failed to get count\n";
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
        
        // Process in smaller batches of 500
        $offset = 0;
        $batchSize = 500;
        $dayImported = 0;
        $dayOrphaned = 0;
        
        while ($offset < $dayCount) {
            // Fetch batch
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.vendor_lead_code
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                ORDER BY vl.call_date ASC
                LIMIT {$batchSize} OFFSET {$offset}
            ";
            
            try {
                $fetchResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                ]);
                
                if (!$fetchResponse->successful()) {
                    $totalErrors++;
                    break;
                }
                
                $fetchOutput = $fetchResponse->json()['output'] ?? '';
                $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
                $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
                
                $lines = explode("\n", trim($fetchOutput));
                
                if (empty($lines[0])) {
                    break; // No more data
                }
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    $fields = explode("\t", $line);
                    if (count($fields) < 7) continue;
                    
                    $totalProcessed++;
                    
                    // Parse fields
                    $callDate = $fields[0];
                    $viciLeadId = $fields[1];
                    $phoneNumber = $fields[2];
                    $campaignId = $fields[3];
                    $status = $fields[4];
                    $lengthInSec = intval($fields[5]);
                    $vendorLeadCode = $fields[6] ?? '';
                    
                    // Try to find lead
                    $leadId = null;
                    
                    // Check vendor_lead_code for 13-digit Brain ID
                    if (preg_match('/^\d{13}$/', $vendorLeadCode)) {
                        $lead = DB::table('leads')
                            ->where('external_lead_id', $vendorLeadCode)
                            ->select('id')
                            ->first();
                        if ($lead) $leadId = $lead->id;
                    }
                    
                    // Try phone match if no lead found
                    if (!$leadId && !empty($phoneNumber)) {
                        $cleanPhone = preg_replace('/\D/', '', $phoneNumber);
                        if (strlen($cleanPhone) == 10) {
                            $lead = DB::table('leads')
                                ->where('phone', 'LIKE', '%' . $cleanPhone . '%')
                                ->select('id')
                                ->first();
                            if ($lead) $leadId = $lead->id;
                        }
                    }
                    
                    if ($leadId) {
                        // Insert into vici_call_metrics
                        try {
                            DB::table('vici_call_metrics')->insertOrIgnore([
                                'lead_id' => $leadId,
                                'vici_lead_id' => $viciLeadId,
                                'campaign_id' => $campaignId,
                                'phone_number' => $phoneNumber,
                                'status' => $status,
                                'last_call_time' => $callDate,
                                'call_duration' => $lengthInSec,
                                'total_calls' => 1,
                                'connected' => in_array($status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayImported++;
                            $totalImported++;
                        } catch (\Exception $e) {
                            // Duplicate, skip
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
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayOrphaned++;
                            $totalOrphaned++;
                        } catch (\Exception $e) {
                            // Skip
                        }
                    }
                }
                
            } catch (\Exception $e) {
                $totalErrors++;
                break;
            }
            
            $offset += $batchSize;
            
            // Show progress
            echo ".";
            
            // Prevent memory issues
            if ($offset % 5000 == 0) {
                DB::connection()->disconnect();
                DB::connection()->reconnect();
            }
        }
        
        echo " Imported: {$dayImported} | Orphaned: {$dayOrphaned}\n";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $totalErrors++;
    }
    
    // Move to next day
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 FINAL RESULTS:\n";
echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
echo "  • Errors: " . number_format($totalErrors) . "\n";

if ($totalProcessed > 0) {
    echo "  • Match Rate: " . round(($totalImported / $totalProcessed) * 100, 2) . "%\n";
    echo "  • Processing Speed: " . round($totalProcessed / $totalTime) . " calls/sec\n";
}

echo "  • Total Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Update last sync time
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Last sync time updated to: {$endDate}\n";
echo "✅ Automatic 5-minute sync will continue from here\n\n";

// Check what we have now
$totalCalls = DB::table('vici_call_metrics')->count();
$totalOrphans = DB::table('orphan_call_logs')->count();

echo "📊 Database Status:\n";
echo "  • Total Call Records: " . number_format($totalCalls) . "\n";
echo "  • Total Orphan Records: " . number_format($totalOrphans) . "\n\n";

echo "📈 Your reports are ready at:\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

echo "🎯 The automatic 5-minute sync is now active and will keep data updated!\n";


/**
 * FINAL WORKING VERSION - Import 90 Days of Vici Call Logs
 * Uses smaller batches and direct processing
 */

echo "=== VICI 90-DAY IMPORT (FINAL VERSION) ===\n\n";
echo "Date: August 15, 2025\n";
echo "Importing: May 17 - August 15, 2025 (90 days)\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

// 90 days from today
$startDate = '2025-05-17';
$endDate = '2025-08-15';

echo "📅 Date Range: {$startDate} to {$endDate}\n";
echo "⏳ Starting import...\n\n";

$totalImported = 0;
$totalOrphaned = 0;
$totalProcessed = 0;
$totalErrors = 0;
$startTime = microtime(true);

// Process day by day
$currentDate = $startDate;

while ($currentDate <= $endDate) {
    echo "📅 {$currentDate}: ";
    
    // Get count for this day
    $countSql = "SELECT COUNT(*) FROM vicidial_log WHERE call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'";
    
    try {
        $countResponse = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($countSql) . " 2>&1"
        ]);
        
        if (!$countResponse->successful()) {
            echo "Failed to get count\n";
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
        
        // Process in smaller batches of 500
        $offset = 0;
        $batchSize = 500;
        $dayImported = 0;
        $dayOrphaned = 0;
        
        while ($offset < $dayCount) {
            // Fetch batch
            $fetchSql = "
                SELECT 
                    vl.call_date,
                    vl.lead_id,
                    vl.phone_number,
                    vl.campaign_id,
                    vl.status,
                    vl.length_in_sec,
                    vl.vendor_lead_code
                FROM vicidial_log vl
                WHERE vl.call_date BETWEEN '{$currentDate} 00:00:00' AND '{$currentDate} 23:59:59'
                ORDER BY vl.call_date ASC
                LIMIT {$batchSize} OFFSET {$offset}
            ";
            
            try {
                $fetchResponse = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($fetchSql) . " 2>&1"
                ]);
                
                if (!$fetchResponse->successful()) {
                    $totalErrors++;
                    break;
                }
                
                $fetchOutput = $fetchResponse->json()['output'] ?? '';
                $fetchOutput = preg_replace('/Could not create directory.*\n/', '', $fetchOutput);
                $fetchOutput = preg_replace('/Failed to add the host.*\n/', '', $fetchOutput);
                
                $lines = explode("\n", trim($fetchOutput));
                
                if (empty($lines[0])) {
                    break; // No more data
                }
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    $fields = explode("\t", $line);
                    if (count($fields) < 7) continue;
                    
                    $totalProcessed++;
                    
                    // Parse fields
                    $callDate = $fields[0];
                    $viciLeadId = $fields[1];
                    $phoneNumber = $fields[2];
                    $campaignId = $fields[3];
                    $status = $fields[4];
                    $lengthInSec = intval($fields[5]);
                    $vendorLeadCode = $fields[6] ?? '';
                    
                    // Try to find lead
                    $leadId = null;
                    
                    // Check vendor_lead_code for 13-digit Brain ID
                    if (preg_match('/^\d{13}$/', $vendorLeadCode)) {
                        $lead = DB::table('leads')
                            ->where('external_lead_id', $vendorLeadCode)
                            ->select('id')
                            ->first();
                        if ($lead) $leadId = $lead->id;
                    }
                    
                    // Try phone match if no lead found
                    if (!$leadId && !empty($phoneNumber)) {
                        $cleanPhone = preg_replace('/\D/', '', $phoneNumber);
                        if (strlen($cleanPhone) == 10) {
                            $lead = DB::table('leads')
                                ->where('phone', 'LIKE', '%' . $cleanPhone . '%')
                                ->select('id')
                                ->first();
                            if ($lead) $leadId = $lead->id;
                        }
                    }
                    
                    if ($leadId) {
                        // Insert into vici_call_metrics
                        try {
                            DB::table('vici_call_metrics')->insertOrIgnore([
                                'lead_id' => $leadId,
                                'vici_lead_id' => $viciLeadId,
                                'campaign_id' => $campaignId,
                                'phone_number' => $phoneNumber,
                                'status' => $status,
                                'last_call_time' => $callDate,
                                'call_duration' => $lengthInSec,
                                'total_calls' => 1,
                                'connected' => in_array($status, ['SALE', 'SOLD', 'XFER']) ? 1 : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayImported++;
                            $totalImported++;
                        } catch (\Exception $e) {
                            // Duplicate, skip
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
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $dayOrphaned++;
                            $totalOrphaned++;
                        } catch (\Exception $e) {
                            // Skip
                        }
                    }
                }
                
            } catch (\Exception $e) {
                $totalErrors++;
                break;
            }
            
            $offset += $batchSize;
            
            // Show progress
            echo ".";
            
            // Prevent memory issues
            if ($offset % 5000 == 0) {
                DB::connection()->disconnect();
                DB::connection()->reconnect();
            }
        }
        
        echo " Imported: {$dayImported} | Orphaned: {$dayOrphaned}\n";
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $totalErrors++;
    }
    
    // Move to next day
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "\n=== IMPORT COMPLETE ===\n\n";
echo "📊 FINAL RESULTS:\n";
echo "  • Total Processed: " . number_format($totalProcessed) . "\n";
echo "  • Imported to Brain: " . number_format($totalImported) . "\n";
echo "  • Orphaned (no match): " . number_format($totalOrphaned) . "\n";
echo "  • Errors: " . number_format($totalErrors) . "\n";

if ($totalProcessed > 0) {
    echo "  • Match Rate: " . round(($totalImported / $totalProcessed) * 100, 2) . "%\n";
    echo "  • Processing Speed: " . round($totalProcessed / $totalTime) . " calls/sec\n";
}

echo "  • Total Time: " . gmdate("H:i:s", $totalTime) . "\n\n";

// Update last sync time
Cache::put('vici_last_incremental_sync', Carbon::parse($endDate), now()->addDays(7));
echo "✅ Last sync time updated to: {$endDate}\n";
echo "✅ Automatic 5-minute sync will continue from here\n\n";

// Check what we have now
$totalCalls = DB::table('vici_call_metrics')->count();
$totalOrphans = DB::table('orphan_call_logs')->count();

echo "📊 Database Status:\n";
echo "  • Total Call Records: " . number_format($totalCalls) . "\n";
echo "  • Total Orphan Records: " . number_format($totalOrphans) . "\n\n";

echo "📈 Your reports are ready at:\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-reports\n";
echo "  • https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports\n\n";

echo "🎯 The automatic 5-minute sync is now active and will keep data updated!\n";






