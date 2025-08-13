<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Force PostgreSQL
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

\DB::purge('pgsql');
\DB::reconnect('pgsql');

echo "=== POSTGRESQL DATA STATUS ===\n\n";

// 1. Check leads
$totalLeads = \DB::table('leads')->count();
echo "Total Leads: $totalLeads\n";

// 2. Check sources
$leadsBySource = \DB::table('leads')
    ->select('source', \DB::raw('count(*) as total'))
    ->groupBy('source')
    ->orderBy('total', 'desc')
    ->get();

echo "\n📊 LEADS BY SOURCE:\n";
foreach ($leadsBySource as $ls) {
    $source = $ls->source ?: '(no source)';
    echo sprintf("  %-25s: %d\n", $source, $ls->total);
}

// 3. Check for SURAJ_BULK specifically
echo "\n🔍 SURAJ_BULK STATUS:\n";
$surajCount = \DB::table('leads')->where('source', 'SURAJ_BULK')->count();
if ($surajCount > 0) {
    echo "✅ SURAJ_BULK leads found: $surajCount\n";
    
    // Show sample
    $sample = \DB::table('leads')
        ->where('source', 'SURAJ_BULK')
        ->limit(3)
        ->get(['name', 'phone', 'vendor_name', 'buyer_name']);
    
    echo "Sample SURAJ_BULK leads:\n";
    foreach ($sample as $lead) {
        echo "  - {$lead->name} | {$lead->phone}\n";
        if ($lead->vendor_name) echo "    Vendor: {$lead->vendor_name}\n";
        if ($lead->buyer_name) echo "    Buyer: {$lead->buyer_name}\n";
    }
} else {
    echo "❌ NO SURAJ_BULK leads in PostgreSQL!\n";
    echo "   Need to run import with PostgreSQL env vars\n";
}

// 4. Check sources table
echo "\n📋 SOURCES TABLE:\n";
if (\Schema::hasTable('sources')) {
    $sources = \DB::table('sources')->count();
    echo "Sources configured: $sources\n";
    if ($sources > 0) {
        $sourceList = \DB::table('sources')->get(['name', 'type', 'label']);
        foreach ($sourceList as $src) {
            echo "  - {$src->name} ({$src->type}): {$src->label}\n";
        }
    }
} else {
    echo "❌ Sources table doesn't exist in PostgreSQL\n";
}

// 5. Summary
echo "\n" . str_repeat("=", 50) . "\n";
if ($surajCount == 0) {
    echo "⚠️  ACTION NEEDED: Import Suraj leads to PostgreSQL!\n";
    echo "Use this command:\n";
    echo "DB_CONNECTION=pgsql \\\n";
    echo "DB_HOST=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com \\\n";
    echo "DB_PORT=5432 \\\n";
    echo "DB_DATABASE=brain_production \\\n";
    echo "DB_USERNAME=brain_user \\\n";
    echo "DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ \\\n";
    echo "php artisan suraj:bulk-import ~/Downloads/Suraj\\ Leads --pattern=\"*.csv\"\n";
} else {
    echo "✅ PostgreSQL has all data including SURAJ_BULK leads\n";
}
