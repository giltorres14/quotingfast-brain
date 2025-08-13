<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Create sources table
    if (!Schema::hasTable('sources')) {
        DB::statement('
            CREATE TABLE sources (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(50) NOT NULL,
                endpoint_url TEXT NULL,
                api_key TEXT NULL,
                color VARCHAR(7) DEFAULT "#6b7280",
                label VARCHAR(50) NOT NULL,
                active BOOLEAN DEFAULT 1,
                total_leads INTEGER DEFAULT 0,
                last_lead_at TIMESTAMP NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        echo "✅ Created sources table\n";
        
        // Insert default sources
        $sources = [
            ['code' => 'SURAJ_BULK', 'name' => 'Suraj Bulk Import', 'type' => 'bulk', 'color' => '#8b5cf6', 'label' => 'Suraj Bulk'],
            ['code' => 'LQF_BULK', 'name' => 'LQF Bulk Import', 'type' => 'bulk', 'color' => '#ec4899', 'label' => 'LQF Bulk'],
            ['code' => 'LQF', 'name' => 'LeadsQuotingFast Webhook', 'type' => 'webhook', 'color' => '#06b6d4', 'label' => 'LQF', 'endpoint_url' => '/api-webhook'],
            ['code' => 'SURAJ', 'name' => 'Suraj Upload Portal', 'type' => 'portal', 'color' => '#10b981', 'label' => 'Suraj'],
            ['code' => 'API', 'name' => 'Direct API', 'type' => 'api', 'color' => '#f59e0b', 'label' => 'API'],
            ['code' => 'MANUAL', 'name' => 'Manual Entry', 'type' => 'manual', 'color' => '#6b7280', 'label' => 'Manual'],
        ];
        
        foreach ($sources as $source) {
            DB::table('sources')->insert(array_merge($source, [
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        echo "✅ Added default sources\n";
        
        // Update lead counts for existing sources
        DB::statement("
            UPDATE sources 
            SET total_leads = (
                SELECT COUNT(*) FROM leads WHERE leads.source = sources.code
            )
        ");
        
        echo "✅ Updated lead counts\n";
        
    } else {
        echo "ℹ️ sources table already exists\n";
    }
    
    echo "\n✅ Sources table ready!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
