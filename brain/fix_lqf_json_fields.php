<?php
/**
 * Fix LQF imported leads that have array fields instead of JSON
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lead;

echo "Fixing LQF leads with array fields...\n\n";

$fixed = 0;
$total = Lead::where('source', 'LQF_BULK')->count();

echo "Total LQF_BULK leads: " . number_format($total) . "\n";
echo "Processing...\n";

// Use raw query to avoid Laravel's automatic casting
$leads = \DB::select("
    SELECT id, drivers, vehicles, current_policy, payload, meta 
    FROM leads 
    WHERE source = 'LQF_BULK' 
    LIMIT 1000
");

foreach ($leads as $lead) {
    $updates = [];
    
    // Check each field and convert if needed
    if ($lead->drivers && $lead->drivers[0] === 'a') { // Serialized array starts with 'a:'
        $updates['drivers'] = '[]';
    }
    if ($lead->vehicles && $lead->vehicles[0] === 'a') {
        $updates['vehicles'] = '[]';
    }
    if ($lead->current_policy && $lead->current_policy[0] === 'a') {
        $updates['current_policy'] = '[]';
    }
    if ($lead->payload && $lead->payload[0] === 'a') {
        $updates['payload'] = '{}';
    }
    if ($lead->meta && $lead->meta[0] === 'a') {
        // Try to unserialize and re-encode as JSON
        try {
            $metaArray = @unserialize($lead->meta);
            if ($metaArray !== false) {
                $updates['meta'] = json_encode($metaArray);
            }
        } catch (\Exception $e) {
            $updates['meta'] = '{}';
        }
    }
    
    if (!empty($updates)) {
        \DB::table('leads')->where('id', $lead->id)->update($updates);
        $fixed++;
        
        if ($fixed % 100 === 0) {
            echo "Fixed: " . $fixed . " leads...\n";
        }
    }
}

echo "\n✅ Fixed " . $fixed . " leads\n";
echo "Now processing remaining leads in batches...\n";

// Process all LQF leads to ensure JSON encoding
\DB::statement("
    UPDATE leads 
    SET 
        drivers = CASE WHEN drivers IS NULL OR drivers = '' THEN '[]' ELSE drivers END,
        vehicles = CASE WHEN vehicles IS NULL OR vehicles = '' THEN '[]' ELSE vehicles END,
        current_policy = CASE WHEN current_policy IS NULL OR current_policy = '' THEN '[]' ELSE current_policy END,
        payload = CASE WHEN payload IS NULL OR payload = '' THEN '{}' ELSE payload END,
        meta = CASE WHEN meta IS NULL OR meta = '' THEN '{}' ELSE meta END
    WHERE source = 'LQF_BULK'
    AND (
        drivers IS NULL OR drivers = '' OR
        vehicles IS NULL OR vehicles = '' OR
        current_policy IS NULL OR current_policy = '' OR
        payload IS NULL OR payload = '' OR
        meta IS NULL OR meta = ''
    )
");

echo "✅ All LQF leads fixed!\n";


/**
 * Fix LQF imported leads that have array fields instead of JSON
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lead;

echo "Fixing LQF leads with array fields...\n\n";

$fixed = 0;
$total = Lead::where('source', 'LQF_BULK')->count();

echo "Total LQF_BULK leads: " . number_format($total) . "\n";
echo "Processing...\n";

// Use raw query to avoid Laravel's automatic casting
$leads = \DB::select("
    SELECT id, drivers, vehicles, current_policy, payload, meta 
    FROM leads 
    WHERE source = 'LQF_BULK' 
    LIMIT 1000
");

foreach ($leads as $lead) {
    $updates = [];
    
    // Check each field and convert if needed
    if ($lead->drivers && $lead->drivers[0] === 'a') { // Serialized array starts with 'a:'
        $updates['drivers'] = '[]';
    }
    if ($lead->vehicles && $lead->vehicles[0] === 'a') {
        $updates['vehicles'] = '[]';
    }
    if ($lead->current_policy && $lead->current_policy[0] === 'a') {
        $updates['current_policy'] = '[]';
    }
    if ($lead->payload && $lead->payload[0] === 'a') {
        $updates['payload'] = '{}';
    }
    if ($lead->meta && $lead->meta[0] === 'a') {
        // Try to unserialize and re-encode as JSON
        try {
            $metaArray = @unserialize($lead->meta);
            if ($metaArray !== false) {
                $updates['meta'] = json_encode($metaArray);
            }
        } catch (\Exception $e) {
            $updates['meta'] = '{}';
        }
    }
    
    if (!empty($updates)) {
        \DB::table('leads')->where('id', $lead->id)->update($updates);
        $fixed++;
        
        if ($fixed % 100 === 0) {
            echo "Fixed: " . $fixed . " leads...\n";
        }
    }
}

echo "\n✅ Fixed " . $fixed . " leads\n";
echo "Now processing remaining leads in batches...\n";

// Process all LQF leads to ensure JSON encoding
\DB::statement("
    UPDATE leads 
    SET 
        drivers = CASE WHEN drivers IS NULL OR drivers = '' THEN '[]' ELSE drivers END,
        vehicles = CASE WHEN vehicles IS NULL OR vehicles = '' THEN '[]' ELSE vehicles END,
        current_policy = CASE WHEN current_policy IS NULL OR current_policy = '' THEN '[]' ELSE current_policy END,
        payload = CASE WHEN payload IS NULL OR payload = '' THEN '{}' ELSE payload END,
        meta = CASE WHEN meta IS NULL OR meta = '' THEN '{}' ELSE meta END
    WHERE source = 'LQF_BULK'
    AND (
        drivers IS NULL OR drivers = '' OR
        vehicles IS NULL OR vehicles = '' OR
        current_policy IS NULL OR current_policy = '' OR
        payload IS NULL OR payload = '' OR
        meta IS NULL OR meta = ''
    )
");

echo "✅ All LQF leads fixed!\n";








