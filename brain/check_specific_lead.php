<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

$phone = '6828886054';

echo "=== SEARCHING FOR PHONE: $phone ===\n\n";

// Try exact match
$lead = Lead::where('phone', $phone)->first();

if ($lead) {
    echo "✅ FOUND Lead:\n";
    echo "  ID: {$lead->id}\n";
    echo "  Name: {$lead->name}\n";
    echo "  Phone: {$lead->phone}\n";
    echo "  Source: {$lead->source}\n";
    echo "  Created: {$lead->created_at}\n";
} else {
    echo "❌ NOT FOUND with exact match\n\n";
    
    // Try with LIKE
    $similar = Lead::where('phone', 'LIKE', '%' . substr($phone, -4) . '%')->get();
    
    if ($similar->count() > 0) {
        echo "Found similar phones ending in " . substr($phone, -4) . ":\n";
        foreach ($similar as $l) {
            echo "  - {$l->phone} ({$l->name})\n";
        }
    }
    
    // Check total Suraj leads
    echo "\n=== SURAJ_BULK STATS ===\n";
    $surajCount = Lead::where('source', 'SURAJ_BULK')->count();
    echo "Total SURAJ_BULK leads: $surajCount\n";
    
    // Show some actual Suraj leads
    echo "\n=== SAMPLE SURAJ_BULK LEADS ===\n";
    $samples = Lead::where('source', 'SURAJ_BULK')->limit(5)->get(['id', 'name', 'phone', 'created_at']);
    foreach ($samples as $sample) {
        echo "ID: {$sample->id} | Phone: {$sample->phone} | Name: {$sample->name}\n";
    }
}
