<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

// Count leads by source
$sources = Lead::select('source', \DB::raw('count(*) as count'))
    ->groupBy('source')
    ->get();

echo "=== LEAD SOURCES IN DATABASE ===\n";
foreach ($sources as $source) {
    $label = $source->source ?? 'NULL';
    echo "{$label}: {$source->count} leads\n";
}

echo "\n=== SURAJ_BULK LEADS ===\n";
$surajCount = Lead::where('source', 'SURAJ_BULK')->count();
echo "Total Suraj Bulk leads: $surajCount\n";

// Show a sample
$sample = Lead::where('source', 'SURAJ_BULK')->first();
if ($sample) {
    echo "\nSample Lead:\n";
    echo "  Name: {$sample->name}\n";
    echo "  Phone: {$sample->phone}\n";
    echo "  Source: {$sample->source}\n";
    echo "  Vendor: {$sample->vendor_name}\n";
}
