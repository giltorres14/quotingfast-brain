<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ENFORCE_POSTGRESQL.php'; // FORCE PostgreSQL
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATING SOURCES TABLE IN POSTGRESQL ===\n\n";

try {
    // Verify we're on PostgreSQL
    $driver = \DB::connection()->getDriverName();
    echo "Database: " . strtoupper($driver) . "\n\n";
    
    if ($driver !== 'pgsql') {
        throw new Exception("ERROR: Not connected to PostgreSQL!");
    }
    
    // Create sources table
    if (!\Schema::hasTable('sources')) {
        \Schema::create('sources', function ($table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['webhook', 'api', 'bulk', 'portal', 'manual']);
            $table->string('label');
            $table->string('color')->default('#666');
            $table->string('endpoint_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('total_leads')->default(0);
            $table->timestamp('last_lead_at')->nullable();
            $table->timestamps();
        });
        echo "✅ Created sources table\n";
        
        // Insert default sources
        $sources = [
            ['code' => 'LQF_WEBHOOK', 'name' => 'LeadsQuotingFast', 'type' => 'webhook', 
             'label' => 'LQF', 'color' => '#10b981', 'endpoint_url' => '/api-webhook'],
            ['code' => 'LQF_BULK', 'name' => 'LQF Bulk Import', 'type' => 'bulk', 
             'label' => 'LQF Bulk', 'color' => '#ec4899'],
            ['code' => 'SURAJ_BULK', 'name' => 'Suraj Bulk Import', 'type' => 'bulk', 
             'label' => 'Suraj Bulk', 'color' => '#8b5cf6'],
            ['code' => 'LQF_PORTAL', 'name' => 'LQF Portal Upload', 'type' => 'portal', 
             'label' => 'LQF', 'color' => '#06b6d4'],
            ['code' => 'SURAJ_PORTAL', 'name' => 'Suraj Portal Upload', 'type' => 'portal', 
             'label' => 'Suraj', 'color' => '#f59e0b'],
            ['code' => 'MANUAL', 'name' => 'Manual Entry', 'type' => 'manual', 
             'label' => 'Manual', 'color' => '#6b7280'],
            ['code' => 'AUTO_WEBHOOK', 'name' => 'Auto Webhook', 'type' => 'webhook',
             'label' => 'Auto', 'color' => '#3b82f6', 'endpoint_url' => '/webhook/auto'],
            ['code' => 'HOME_WEBHOOK', 'name' => 'Home Webhook', 'type' => 'webhook',
             'label' => 'Home', 'color' => '#22c55e', 'endpoint_url' => '/webhook/home']
        ];
        
        foreach ($sources as $source) {
            \DB::table('sources')->insert(array_merge($source, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        echo "✅ Inserted " . count($sources) . " default sources\n";
    } else {
        echo "ℹ️  Sources table already exists\n";
    }
    
    // Show sources
    $allSources = \DB::table('sources')->get();
    echo "\n📋 Sources in PostgreSQL:\n";
    foreach ($allSources as $src) {
        echo "  - {$src->name} ({$src->type}): {$src->label}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
