<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;
use Illuminate\Http\Request;

echo "=== TESTING SEARCH QUERY ===\n\n";

// Simulate the exact query from the web route
$search = '6828886054';
$query = Lead::query();

if ($search) {
    $search = trim($search);
    $tokens = preg_split('/\s+/', $search);
    $isPg = config('database.default') === 'pgsql';
    $like = $isPg ? 'ilike' : 'like';
    
    echo "Search term: $search\n";
    echo "Database type: " . config('database.default') . "\n";
    echo "Using operator: $like\n\n";

    $query->where(function ($outer) use ($tokens, $like, $isPg) {
        foreach ($tokens as $token) {
            $outer->where(function ($q) use ($token, $like, $isPg) {
                $q->where('first_name', $like, "%{$token}%")
                  ->orWhere('last_name', $like, "%{$token}%")
                  ->orWhere('name', $like, "%{$token}%")
                  ->orWhere('phone', $like, "%{$token}%")
                  ->orWhere('email', $like, "%{$token}%")
                  ->orWhere('city', $like, "%{$token}%")
                  ->orWhere('state', $like, "%{$token}%")
                  ->orWhere('zip_code', $like, "%{$token}%")
                  ->orWhere('external_lead_id', $like, "%{$token}%");

                // Full name concatenation
                if ($isPg) {
                    $q->orWhereRaw("(first_name || ' ' || last_name) ilike ?", ["%{$token}%"]);
                    $q->orWhereRaw("CAST(id AS TEXT) ilike ?", ["%{$token}%"]);
                } else {
                    $q->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$token}%"]);
                    $q->orWhereRaw("CAST(id AS CHAR) like ?", ["%{$token}%"]);
                }
            });
        }
    });
}

// Get SQL query
echo "Generated SQL:\n";
echo $query->toSql() . "\n\n";

// Execute query
$results = $query->limit(5)->get();

echo "Results found: " . $results->count() . "\n";
foreach ($results as $lead) {
    echo "  - {$lead->name} (Phone: {$lead->phone}, ID: {$lead->id})\n";
}

// Try simpler query
echo "\n=== SIMPLE PHONE SEARCH ===\n";
$simple = Lead::where('phone', '6828886054')->first();
if ($simple) {
    echo "✅ Found with simple query: {$simple->name}\n";
} else {
    echo "❌ Not found with simple query\n";
}
