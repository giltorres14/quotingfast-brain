<?php
require_once __DIR__ . '/vendor/autoload.php';

// Override database config to use PostgreSQL
config(['database.default' => 'pgsql']);
config(['database.connections.pgsql' => [
    'driver' => 'pgsql',
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'port' => 5432,
    'database' => 'brain_production',
    'username' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]]);

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

echo "=== CONNECTING TO PRODUCTION POSTGRESQL ===\n\n";

try {
    // Test connection
    $count = Lead::count();
    echo "✅ Connected successfully!\n";
    echo "Total leads in production: $count\n";
    
    // Check for Suraj leads
    $surajCount = Lead::where('source', 'SURAJ_BULK')->count();
    echo "SURAJ_BULK leads in production: $surajCount\n";
    
    // Check for test lead
    $testLead = Lead::where('phone', '6828886054')->first();
    if ($testLead) {
        echo "\n✅ Kenneth Takett already exists in production\n";
    } else {
        echo "\n❌ Kenneth Takett NOT in production database\n";
        echo "Need to import Suraj leads to production PostgreSQL\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
