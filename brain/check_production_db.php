<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Override to use PostgreSQL
\Config::set('database.default', 'pgsql');
\Config::set('database.connections.pgsql', [
    'driver' => 'pgsql',
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'port' => 5432,
    'database' => 'brain_production',
    'username' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

// Purge and reconnect
\DB::purge('pgsql');
\DB::reconnect('pgsql');

echo "=== CONNECTING TO PRODUCTION POSTGRESQL ===\n\n";

try {
    // Test connection
    $count = \DB::table('leads')->count();
    echo "✅ Connected to PostgreSQL!\n";
    echo "Total leads in production: $count\n";
    
    // Check for Suraj leads
    $surajCount = \DB::table('leads')->where('source', 'SURAJ_BULK')->count();
    echo "SURAJ_BULK leads in production: $surajCount\n";
    
    // Check for specific lead
    $testLead = \DB::table('leads')->where('phone', '6828886054')->first();
    if ($testLead) {
        echo "\n✅ Kenneth Takett exists in production\n";
        echo "  Name: {$testLead->name}\n";
        echo "  External ID: {$testLead->external_lead_id}\n";
    } else {
        echo "\n❌ Kenneth Takett NOT in production database\n";
    }
    
    // Show recent leads
    echo "\n=== RECENT PRODUCTION LEADS ===\n";
    $recent = \DB::table('leads')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    foreach ($recent as $lead) {
        echo "  - {$lead->name} | Phone: {$lead->phone} | Source: {$lead->source}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Connection Error: " . $e->getMessage() . "\n";
}
