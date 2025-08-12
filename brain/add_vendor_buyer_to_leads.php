<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Add vendor_name column
    if (!Schema::hasColumn('leads', 'vendor_name')) {
        DB::statement('ALTER TABLE leads ADD COLUMN vendor_name VARCHAR(255) NULL');
        echo "✅ Added vendor_name column to leads table\n";
    } else {
        echo "ℹ️ vendor_name column already exists\n";
    }
    
    // Add buyer_name column
    if (!Schema::hasColumn('leads', 'buyer_name')) {
        DB::statement('ALTER TABLE leads ADD COLUMN buyer_name VARCHAR(255) NULL');
        echo "✅ Added buyer_name column to leads table\n";
    } else {
        echo "ℹ️ buyer_name column already exists\n";
    }
    
    echo "\n✅ Leads table ready for vendor and buyer tracking!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
