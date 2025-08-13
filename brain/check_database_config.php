<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

echo "=== DATABASE CONFIGURATION ===\n\n";

// Check database config
echo "Default Connection: " . config('database.default') . "\n";

if (config('database.default') === 'sqlite') {
    $dbPath = config('database.connections.sqlite.database');
    echo "SQLite Database Path: $dbPath\n";
    
    if (file_exists($dbPath)) {
        echo "✅ Database file exists\n";
        echo "File size: " . number_format(filesize($dbPath) / 1024 / 1024, 2) . " MB\n";
    } else {
        echo "❌ Database file NOT FOUND at: $dbPath\n";
    }
} else {
    echo "Using: " . config('database.default') . "\n";
    echo "Host: " . config('database.connections.' . config('database.default') . '.host') . "\n";
    echo "Database: " . config('database.connections.' . config('database.default') . '.database') . "\n";
}

echo "\n=== CHECKING LEADS IN CURRENT DATABASE ===\n";
$totalLeads = Lead::count();
echo "Total leads: $totalLeads\n";

$surajLeads = Lead::where('source', 'SURAJ_BULK')->count();
echo "SURAJ_BULK leads: $surajLeads\n";

// Check for specific lead
$testLead = Lead::where('phone', '6828886054')->first();
if ($testLead) {
    echo "\n✅ Kenneth Takett lead FOUND in database\n";
    echo "  ID: {$testLead->id}\n";
    echo "  External ID: {$testLead->external_lead_id}\n";
} else {
    echo "\n❌ Kenneth Takett lead NOT FOUND in database\n";
}

// Check environment
echo "\n=== ENVIRONMENT ===\n";
echo "APP_ENV: " . env('APP_ENV', 'not set') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION', 'not set') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE', 'not set') . "\n";
