<?php
/**
 * Fix malformed JSON in LQF imported leads
 */

ini_set('memory_limit', '1G');
set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n🔧 FIXING LQF LEAD JSON DATA\n";
echo "============================\n\n";

// Count affected leads
$totalCount = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->count();

echo "Total LQF leads: " . number_format($totalCount) . "\n\n";

// Process in batches
$batchSize = 1000;
$fixed = 0;
$failed = 0;

echo "Processing leads in batches of $batchSize...\n\n";

DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->orderBy('id')
    ->chunk($batchSize, function ($leads) use (&$fixed, &$failed) {
        foreach ($leads as $lead) {
            try {
                // Check if payload needs fixing
                if (strpos($lead->payload, '{"{\\"drivers\\":_"') !== false || 
                    strpos($lead->payload, '{"{') === 0) {
                    
                    // Extract the actual data from the malformed JSON
                    // The data is in format: {"{\"drivers\":_":{...actual data...}}
                    $payload = $lead->payload;
                    
                    // Try to extract the actual JSON
                    if (preg_match('/\{"\{\\\\"drivers\\\\":_":(.*)\}\}$/s', $payload, $matches)) {
                        $actualData = $matches[1] . '}';
                        
                        // Decode to validate
                        $decoded = json_decode($actualData, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Extract drivers and vehicles
                            $drivers = isset($decoded['drivers']) ? json_encode($decoded['drivers']) : '[]';
                            $vehicles = isset($decoded['vehicles']) ? json_encode($decoded['vehicles']) : '[]';
                            $policy = isset($decoded['requested_policy']) ? json_encode($decoded['requested_policy']) : '[]';
                            
                            // Update the lead
                            DB::table('leads')
                                ->where('id', $lead->id)
                                ->update([
                                    'payload' => $actualData,
                                    'drivers' => $drivers,
                                    'vehicles' => $vehicles,
                                    'current_policy' => $policy
                                ]);
                            
                            $fixed++;
                        } else {
                            // Try another pattern
                            // Sometimes it's just double-encoded
                            $decoded = json_decode($payload, true);
                            if (is_array($decoded) && count($decoded) == 1) {
                                $key = array_keys($decoded)[0];
                                $value = $decoded[$key];
                                
                                if (is_array($value)) {
                                    // This is the actual data
                                    $actualPayload = json_encode($value);
                                    $drivers = isset($value['drivers']) ? json_encode($value['drivers']) : '[]';
                                    $vehicles = isset($value['vehicles']) ? json_encode($value['vehicles']) : '[]';
                                    $policy = isset($value['requested_policy']) ? json_encode($value['requested_policy']) : '[]';
                                    
                                    DB::table('leads')
                                        ->where('id', $lead->id)
                                        ->update([
                                            'payload' => $actualPayload,
                                            'drivers' => $drivers,
                                            'vehicles' => $vehicles,
                                            'current_policy' => $policy
                                        ]);
                                    
                                    $fixed++;
                                } else {
                                    $failed++;
                                }
                            } else {
                                $failed++;
                            }
                        }
                    } else {
                        $failed++;
                    }
                } else {
                    // Check if it's valid JSON
                    $decoded = json_decode($lead->payload, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Update drivers/vehicles if they're empty
                        if ($lead->drivers == '[]' && isset($decoded['drivers'])) {
                            DB::table('leads')
                                ->where('id', $lead->id)
                                ->update([
                                    'drivers' => json_encode($decoded['drivers']),
                                    'vehicles' => json_encode($decoded['vehicles'] ?? []),
                                    'current_policy' => json_encode($decoded['requested_policy'] ?? [])
                                ]);
                            $fixed++;
                        }
                    }
                }
                
            } catch (\Exception $e) {
                $failed++;
            }
        }
        
        echo "  Processed batch: $fixed fixed, $failed failed\n";
    });

echo "\n============================\n";
echo "✅ FIX COMPLETE!\n";
echo "============================\n\n";
echo "Fixed: " . number_format($fixed) . " leads\n";
echo "Failed: " . number_format($failed) . " leads\n\n";

// Test a lead
$testLead = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->whereRaw("drivers != '[]'")
    ->first();

if ($testLead) {
    echo "Sample fixed lead:\n";
    echo "Name: " . $testLead->name . "\n";
    $drivers = json_decode($testLead->drivers, true);
    if (!empty($drivers)) {
        echo "First driver: " . ($drivers[0]['first_name'] ?? 'N/A') . " " . ($drivers[0]['last_name'] ?? 'N/A') . "\n";
    }
}
