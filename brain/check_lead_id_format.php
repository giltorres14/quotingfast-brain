<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

echo "=== CHECKING LEAD ID FORMATS ===\n\n";

// Get the Kenneth Takett lead
$lead = Lead::where('phone', '6828886054')->first();

if ($lead) {
    echo "Kenneth Takett Lead:\n";
    echo "  Internal ID: {$lead->id}\n";
    echo "  External Lead ID: {$lead->external_lead_id}\n";
    echo "  Phone: {$lead->phone}\n";
    echo "  Source: {$lead->source}\n\n";
}

// Check what external_lead_id format we're using
echo "=== SAMPLE EXTERNAL LEAD IDs ===\n";
$samples = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('external_lead_id')
    ->limit(5)
    ->get(['name', 'external_lead_id', 'phone']);

foreach ($samples as $sample) {
    echo "  {$sample->external_lead_id} - {$sample->name} ({$sample->phone})\n";
}

// Check the format from memories
echo "\n=== CHECKING LEAD ID GENERATION ===\n";
$newId = Lead::generateExternalLeadId();
echo "New generated ID would be: $newId\n";
echo "Format: " . (strlen($newId) == 9 ? "9-digit starting with 10000001" : "13-digit timestamp") . "\n";
