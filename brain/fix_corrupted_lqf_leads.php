<?php
/**
 * Fix corrupted LQF leads from the turbo import
 * These have malformed JSON in payload and meta fields
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "\n🔧 FIXING CORRUPTED LQF LEADS\n";
echo "================================\n\n";

// Get count of potentially corrupted leads
$total = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->count();

echo "Total LQF_BULK leads: " . number_format($total) . "\n";

// Check for corrupted payload (contains double-encoded JSON)
$corrupted = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->count();

echo "Corrupted payload fields: " . number_format($corrupted) . "\n\n";

if ($corrupted === 0) {
    echo "✅ No corrupted leads found!\n";
    exit(0);
}

echo "Starting fix process...\n\n";

$fixed = 0;
$failed = 0;
$batch = 0;

// Process in chunks to avoid memory issues
DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->orderBy('id')
    ->chunk(100, function($leads) use (&$fixed, &$failed, &$batch) {
        $batch++;
        echo "Processing batch $batch...\n";
        
        foreach ($leads as $lead) {
            try {
                $updates = [];
                
                // Fix payload - it's double-encoded
                if ($lead->payload && strpos($lead->payload, '{"{') !== false) {
                    // Try to extract the actual JSON from the corrupted string
                    // The pattern is usually: {"{"key": "value"}":""}
                    $payload = $lead->payload;
                    
                    // Remove the outer wrapping
                    $payload = str_replace('{"{', '{"', $payload);
                    $payload = str_replace('}":""}', '}', $payload);
                    $payload = str_replace('\\"', '"', $payload);
                    $payload = str_replace('\\/', '/', $payload);
                    
                    // Try to parse to validate
                    $test = @json_decode($payload, true);
                    if ($test !== null) {
                        $updates['payload'] = $payload;
                    } else {
                        // If still can't parse, try a different approach
                        // Extract the JSON between the quotes
                        if (preg_match('/"({.+})"/', $lead->payload, $matches)) {
                            $inner = str_replace('\\"', '"', $matches[1]);
                            $inner = str_replace('\\/', '/', $inner);
                            $test = @json_decode($inner, true);
                            if ($test !== null) {
                                $updates['payload'] = $inner;
                            } else {
                                $updates['payload'] = '{}';
                            }
                        } else {
                            $updates['payload'] = '{}';
                        }
                    }
                }
                
                // Fix meta if needed
                if ($lead->meta && json_decode($lead->meta, true) === null) {
                    // Meta might be okay, but let's validate
                    $updates['meta'] = '{}';
                }
                
                // Fix empty JSON fields
                if ($lead->drivers === '' || $lead->drivers === null) {
                    $updates['drivers'] = '[]';
                }
                if ($lead->vehicles === '' || $lead->vehicles === null) {
                    $updates['vehicles'] = '[]';
                }
                if ($lead->current_policy === '' || $lead->current_policy === null) {
                    $updates['current_policy'] = '[]';
                }
                
                if (!empty($updates)) {
                    DB::table('leads')
                        ->where('id', $lead->id)
                        ->update($updates);
                    $fixed++;
                    
                    if ($fixed % 100 === 0) {
                        echo "  ✓ Fixed $fixed leads...\n";
                    }
                }
                
            } catch (\Exception $e) {
                $failed++;
                echo "  ❌ Failed to fix lead {$lead->id}: " . $e->getMessage() . "\n";
            }
        }
    });

echo "\n================================\n";
echo "✅ FIX COMPLETE!\n";
echo "================================\n\n";
echo "Fixed: " . number_format($fixed) . " leads\n";
echo "Failed: " . number_format($failed) . " leads\n\n";

// Now let's also extract the drivers/vehicles data from payload if they exist
echo "Extracting drivers/vehicles data from payload...\n";

$extracted = 0;

DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('drivers', '[]')
    ->whereNotNull('payload')
    ->where('payload', '!=', '{}')
    ->orderBy('id')
    ->chunk(100, function($leads) use (&$extracted) {
        foreach ($leads as $lead) {
            try {
                $payload = json_decode($lead->payload, true);
                if ($payload && !empty($payload)) {
                    $updates = [];
                    
                    // Check for drivers in payload
                    if (isset($payload['drivers']) && is_array($payload['drivers'])) {
                        $updates['drivers'] = json_encode($payload['drivers']);
                    }
                    
                    // Check for vehicles in payload
                    if (isset($payload['vehicles']) && is_array($payload['vehicles'])) {
                        $updates['vehicles'] = json_encode($payload['vehicles']);
                    }
                    
                    // Check for current_policy/requested_policy
                    if (isset($payload['requested_policy']) && is_array($payload['requested_policy'])) {
                        $updates['current_policy'] = json_encode($payload['requested_policy']);
                    } elseif (isset($payload['current_policy']) && is_array($payload['current_policy'])) {
                        $updates['current_policy'] = json_encode($payload['current_policy']);
                    }
                    
                    if (!empty($updates)) {
                        DB::table('leads')
                            ->where('id', $lead->id)
                            ->update($updates);
                        $extracted++;
                        
                        if ($extracted % 100 === 0) {
                            echo "  ✓ Extracted data for $extracted leads...\n";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Skip
            }
        }
    });

echo "\n✅ Extracted data for " . number_format($extracted) . " leads\n\n";

// Final check
$stillCorrupted = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->count();

if ($stillCorrupted > 0) {
    echo "⚠️  Warning: " . number_format($stillCorrupted) . " leads still have corrupted payload\n";
    echo "These may need to be re-imported from the CSV file.\n";
} else {
    echo "✅ All corrupted payloads have been fixed!\n";
}

echo "\nYou can now view the leads properly in the UI.\n";


/**
 * Fix corrupted LQF leads from the turbo import
 * These have malformed JSON in payload and meta fields
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "\n🔧 FIXING CORRUPTED LQF LEADS\n";
echo "================================\n\n";

// Get count of potentially corrupted leads
$total = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->count();

echo "Total LQF_BULK leads: " . number_format($total) . "\n";

// Check for corrupted payload (contains double-encoded JSON)
$corrupted = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->count();

echo "Corrupted payload fields: " . number_format($corrupted) . "\n\n";

if ($corrupted === 0) {
    echo "✅ No corrupted leads found!\n";
    exit(0);
}

echo "Starting fix process...\n\n";

$fixed = 0;
$failed = 0;
$batch = 0;

// Process in chunks to avoid memory issues
DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->orderBy('id')
    ->chunk(100, function($leads) use (&$fixed, &$failed, &$batch) {
        $batch++;
        echo "Processing batch $batch...\n";
        
        foreach ($leads as $lead) {
            try {
                $updates = [];
                
                // Fix payload - it's double-encoded
                if ($lead->payload && strpos($lead->payload, '{"{') !== false) {
                    // Try to extract the actual JSON from the corrupted string
                    // The pattern is usually: {"{"key": "value"}":""}
                    $payload = $lead->payload;
                    
                    // Remove the outer wrapping
                    $payload = str_replace('{"{', '{"', $payload);
                    $payload = str_replace('}":""}', '}', $payload);
                    $payload = str_replace('\\"', '"', $payload);
                    $payload = str_replace('\\/', '/', $payload);
                    
                    // Try to parse to validate
                    $test = @json_decode($payload, true);
                    if ($test !== null) {
                        $updates['payload'] = $payload;
                    } else {
                        // If still can't parse, try a different approach
                        // Extract the JSON between the quotes
                        if (preg_match('/"({.+})"/', $lead->payload, $matches)) {
                            $inner = str_replace('\\"', '"', $matches[1]);
                            $inner = str_replace('\\/', '/', $inner);
                            $test = @json_decode($inner, true);
                            if ($test !== null) {
                                $updates['payload'] = $inner;
                            } else {
                                $updates['payload'] = '{}';
                            }
                        } else {
                            $updates['payload'] = '{}';
                        }
                    }
                }
                
                // Fix meta if needed
                if ($lead->meta && json_decode($lead->meta, true) === null) {
                    // Meta might be okay, but let's validate
                    $updates['meta'] = '{}';
                }
                
                // Fix empty JSON fields
                if ($lead->drivers === '' || $lead->drivers === null) {
                    $updates['drivers'] = '[]';
                }
                if ($lead->vehicles === '' || $lead->vehicles === null) {
                    $updates['vehicles'] = '[]';
                }
                if ($lead->current_policy === '' || $lead->current_policy === null) {
                    $updates['current_policy'] = '[]';
                }
                
                if (!empty($updates)) {
                    DB::table('leads')
                        ->where('id', $lead->id)
                        ->update($updates);
                    $fixed++;
                    
                    if ($fixed % 100 === 0) {
                        echo "  ✓ Fixed $fixed leads...\n";
                    }
                }
                
            } catch (\Exception $e) {
                $failed++;
                echo "  ❌ Failed to fix lead {$lead->id}: " . $e->getMessage() . "\n";
            }
        }
    });

echo "\n================================\n";
echo "✅ FIX COMPLETE!\n";
echo "================================\n\n";
echo "Fixed: " . number_format($fixed) . " leads\n";
echo "Failed: " . number_format($failed) . " leads\n\n";

// Now let's also extract the drivers/vehicles data from payload if they exist
echo "Extracting drivers/vehicles data from payload...\n";

$extracted = 0;

DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('drivers', '[]')
    ->whereNotNull('payload')
    ->where('payload', '!=', '{}')
    ->orderBy('id')
    ->chunk(100, function($leads) use (&$extracted) {
        foreach ($leads as $lead) {
            try {
                $payload = json_decode($lead->payload, true);
                if ($payload && !empty($payload)) {
                    $updates = [];
                    
                    // Check for drivers in payload
                    if (isset($payload['drivers']) && is_array($payload['drivers'])) {
                        $updates['drivers'] = json_encode($payload['drivers']);
                    }
                    
                    // Check for vehicles in payload
                    if (isset($payload['vehicles']) && is_array($payload['vehicles'])) {
                        $updates['vehicles'] = json_encode($payload['vehicles']);
                    }
                    
                    // Check for current_policy/requested_policy
                    if (isset($payload['requested_policy']) && is_array($payload['requested_policy'])) {
                        $updates['current_policy'] = json_encode($payload['requested_policy']);
                    } elseif (isset($payload['current_policy']) && is_array($payload['current_policy'])) {
                        $updates['current_policy'] = json_encode($payload['current_policy']);
                    }
                    
                    if (!empty($updates)) {
                        DB::table('leads')
                            ->where('id', $lead->id)
                            ->update($updates);
                        $extracted++;
                        
                        if ($extracted % 100 === 0) {
                            echo "  ✓ Extracted data for $extracted leads...\n";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Skip
            }
        }
    });

echo "\n✅ Extracted data for " . number_format($extracted) . " leads\n\n";

// Final check
$stillCorrupted = DB::table('leads')
    ->where('source', 'LQF_BULK')
    ->where('payload', 'LIKE', '%{"{%')
    ->count();

if ($stillCorrupted > 0) {
    echo "⚠️  Warning: " . number_format($stillCorrupted) . " leads still have corrupted payload\n";
    echo "These may need to be re-imported from the CSV file.\n";
} else {
    echo "✅ All corrupted payloads have been fixed!\n";
}

echo "\nYou can now view the leads properly in the UI.\n";






