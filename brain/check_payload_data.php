<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

// Get a lead with payload
$lead = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('payload')
    ->first();

if ($lead) {
    echo "Lead: {$lead->name}\n";
    echo "Campaign ID: {$lead->campaign_id}\n\n";
    
    $payload = json_decode($lead->payload, true);
    
    // Show first 5 fields from payload
    echo "=== PAYLOAD SAMPLE (first 10 fields) ===\n";
    $count = 0;
    foreach ($payload as $key => $value) {
        echo "$key: $value\n";
        $count++;
        if ($count >= 10) break;
    }
    
    echo "\n=== KEY FIELDS ===\n";
    echo "vendor_id: " . ($payload['vendor_id'] ?? 'NOT FOUND') . "\n";
    echo "vendor_name: " . ($payload['vendor_name'] ?? 'NOT FOUND') . "\n";
    echo "buyer_id: " . ($payload['buyer_id'] ?? 'NOT FOUND') . "\n";
    echo "buyer_name: " . ($payload['buyer_name'] ?? 'NOT FOUND') . "\n";
    echo "buyer_campaign_id: " . ($payload['buyer_campaign_id'] ?? 'NOT FOUND') . "\n";
    echo "vendor_campaign_id: " . ($payload['vendor_campaign_id'] ?? 'NOT FOUND') . "\n";
} else {
    echo "No leads found with payload\n";
}
