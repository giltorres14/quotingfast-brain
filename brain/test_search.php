<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

$phone = '6828886054';

echo "=== TESTING SEARCH QUERIES ===\n\n";

// Test exact match
$exact = Lead::where('phone', $phone)->first();
echo "Exact match for $phone: " . ($exact ? "FOUND (ID: {$exact->id})" : "NOT FOUND") . "\n";

// Test LIKE match
$like = Lead::where('phone', 'LIKE', "%$phone%")->first();
echo "LIKE match for $phone: " . ($like ? "FOUND (ID: {$like->id})" : "NOT FOUND") . "\n";

// Test partial match (last 4 digits)
$partial = Lead::where('phone', 'LIKE', '%6054')->first();
echo "Partial match for 6054: " . ($partial ? "FOUND (ID: {$partial->id}, Phone: {$partial->phone})" : "NOT FOUND") . "\n";

// Check database type
echo "\n=== DATABASE INFO ===\n";
echo "Default connection: " . config('database.default') . "\n";
echo "Database path: " . database_path('database.sqlite') . "\n";

// Check if it's the local database
if (file_exists(database_path('database.sqlite'))) {
    $size = filesize(database_path('database.sqlite'));
    echo "SQLite file size: " . number_format($size / 1024 / 1024, 2) . " MB\n";
    
    // Count total leads
    $total = Lead::count();
    echo "Total leads in database: $total\n";
    
    // Count by source
    $sources = Lead::select('source', \DB::raw('count(*) as count'))
        ->groupBy('source')
        ->get();
    
    echo "\nLeads by source:\n";
    foreach ($sources as $s) {
        echo "  - {$s->source}: {$s->count}\n";
    }
}
