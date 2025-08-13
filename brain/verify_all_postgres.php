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

echo "=== VERIFYING ALL DATA IS IN POSTGRESQL ===\n\n";

try {
    // Check what database we're actually connected to
    $connection = \DB::connection()->getPdo();
    $driver = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    echo "✅ Connected via: " . strtoupper($driver) . "\n\n";
    
    // Check all the work we did today
    echo "📊 DATA VERIFICATION:\n";
    echo "─────────────────────\n";
    
    // 1. Vendors
    $vendors = \DB::table('vendors')->count();
    echo "Vendors: $vendors\n";
    if ($vendors > 0) {
        $vendorSample = \DB::table('vendors')->limit(3)->pluck('name');
        foreach ($vendorSample as $v) {
            echo "  - $v\n";
        }
    }
    
    // 2. Buyers
    echo "\nBuyers: " . \DB::table('buyers')->count() . "\n";
    $buyers = \DB::table('buyers')->count();
    if ($buyers > 0) {
        $buyerSample = \DB::table('buyers')->limit(3)->pluck('name');
        foreach ($buyerSample as $b) {
            echo "  - $b\n";
        }
    }
    
    // 3. Campaigns with buyers
    echo "\nCampaigns: " . \DB::table('campaigns')->count() . "\n";
    $campaignBuyers = \DB::table('campaign_buyer')->count();
    echo "Campaign-Buyer links: $campaignBuyers\n";
    
    // 4. Sources
    echo "\nSources: " . \DB::table('sources')->count() . "\n";
    $sources = \DB::table('sources')->get();
    foreach ($sources as $s) {
        echo "  - {$s->name} ({$s->type})\n";
    }
    
    // 5. Leads by source
    echo "\n📈 LEADS BY SOURCE:\n";
    echo "─────────────────────\n";
    $leadsBySource = \DB::table('leads')
        ->select('source', \DB::raw('count(*) as total'))
        ->groupBy('source')
        ->orderBy('total', 'desc')
        ->get();
    
    foreach ($leadsBySource as $ls) {
        $source = $ls->source ?: '(no source)';
        echo sprintf("%-25s: %d\n", $source, $ls->total);
    }
    
    // 6. Check for Suraj leads specifically
    echo "\n🔍 SURAJ DATA CHECK:\n";
    echo "─────────────────────\n";
    $surajCount = \DB::table('leads')->where('source', 'SURAJ_BULK')->count();
    echo "SURAJ_BULK leads: $surajCount\n";
    
    // Check for the test lead
    $testLead = \DB::table('leads')->where('phone', '6828886054')->first();
    if ($testLead) {
        echo "✅ Kenneth Takett IS in PostgreSQL\n";
        echo "  - Name: {$testLead->name}\n";
        echo "  - Source: {$testLead->source}\n";
        echo "  - Vendor: {$testLead->vendor_name}\n";
        echo "  - Buyer: {$testLead->buyer_name}\n";
    } else {
        echo "❌ Kenneth Takett NOT in PostgreSQL - Need to import!\n";
    }
    
    echo "\n✅ ALL CHECKS COMPLETE - Using PostgreSQL brain_production\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
