<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

echo "=== CHECKING LEAD ID ISSUES ===\n\n";

// Check if external_lead_ids are properly set
$surajLeads = Lead::where('source', 'SURAJ_BULK')->limit(10)->get();

echo "Sample Suraj Leads:\n";
foreach ($surajLeads as $lead) {
    $idLength = strlen($lead->external_lead_id);
    $status = $idLength == 13 ? "✅" : "❌";
    echo "{$status} ID: {$lead->id} | External: {$lead->external_lead_id} ({$idLength} digits) | {$lead->name}\n";
}

// Check for any issues
$wrongLength = Lead::where('source', 'SURAJ_BULK')
    ->whereRaw('LENGTH(external_lead_id) != 13')
    ->count();

$nullIds = Lead::where('source', 'SURAJ_BULK')
    ->whereNull('external_lead_id')
    ->count();

echo "\n=== STATISTICS ===\n";
echo "Total Suraj leads: " . Lead::where('source', 'SURAJ_BULK')->count() . "\n";
echo "Wrong length IDs: $wrongLength\n";
echo "NULL external IDs: $nullIds\n";

// Check if search would work
$testPhone = '6828886054';
$searchResult = Lead::where('phone', 'LIKE', "%$testPhone%")->first();
echo "\n=== SEARCH TEST ===\n";
echo "Searching for phone: $testPhone\n";
if ($searchResult) {
    echo "✅ Found: {$searchResult->name} (ID: {$searchResult->id}, External: {$searchResult->external_lead_id})\n";
} else {
    echo "❌ Not found\n";
}
