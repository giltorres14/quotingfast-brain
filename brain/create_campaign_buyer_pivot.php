<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Create pivot table for many-to-many relationship
    if (!Schema::hasTable('campaign_buyer')) {
        DB::statement('
            CREATE TABLE campaign_buyer (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                campaign_id INTEGER NOT NULL,
                buyer_id INTEGER NOT NULL,
                buyer_campaign_id VARCHAR(255) NULL,
                is_primary BOOLEAN DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        echo "✅ Created campaign_buyer pivot table\n";
        
        // Add indexes
        DB::statement('CREATE INDEX idx_campaign_buyer_campaign ON campaign_buyer(campaign_id)');
        DB::statement('CREATE INDEX idx_campaign_buyer_buyer ON campaign_buyer(buyer_id)');
        DB::statement('CREATE UNIQUE INDEX idx_campaign_buyer_unique ON campaign_buyer(campaign_id, buyer_id)');
        echo "✅ Added indexes to pivot table\n";
    } else {
        echo "ℹ️ campaign_buyer table already exists\n";
    }
    
    // Remove single buyer columns from campaigns if they exist
    if (Schema::hasColumn('campaigns', 'buyer_id')) {
        echo "ℹ️ Note: buyer_id column exists on campaigns table - keeping for backward compatibility\n";
    }
    
    echo "\n✅ Campaign-Buyer many-to-many relationship ready!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
