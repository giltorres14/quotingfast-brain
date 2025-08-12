<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Create vendors table
    if (!Schema::hasTable('vendors')) {
        DB::statement('
            CREATE TABLE vendors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE,
                campaigns TEXT NULL,
                contact_info TEXT NULL,
                total_leads INTEGER DEFAULT 0,
                total_cost DECIMAL(10,2) DEFAULT 0,
                active BOOLEAN DEFAULT 1,
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        echo "✅ Created vendors table\n";
    } else {
        echo "ℹ️ vendors table already exists\n";
    }
    
    // Create buyers table  
    if (!Schema::hasTable('buyers')) {
        DB::statement('
            CREATE TABLE buyers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE,
                campaigns TEXT NULL,
                contact_info TEXT NULL,
                api_credentials TEXT NULL,
                total_leads INTEGER DEFAULT 0,
                total_revenue DECIMAL(10,2) DEFAULT 0,
                active BOOLEAN DEFAULT 1,
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        echo "✅ Created buyers table\n";
    } else {
        echo "ℹ️ buyers table already exists\n";
    }
    
    echo "\n✅ Vendor and Buyer tables ready!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
