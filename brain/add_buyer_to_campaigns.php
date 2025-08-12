<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Check if buyer_id column exists
    if (!Schema::hasColumn('campaigns', 'buyer_id')) {
        DB::statement('ALTER TABLE campaigns ADD COLUMN buyer_id INTEGER NULL');
        echo "✅ Added buyer_id column to campaigns table\n";
    } else {
        echo "ℹ️ buyer_id column already exists\n";
    }
    
    // Check if buyer_name column exists
    if (!Schema::hasColumn('campaigns', 'buyer_name')) {
        DB::statement('ALTER TABLE campaigns ADD COLUMN buyer_name VARCHAR(255) NULL');
        echo "✅ Added buyer_name column to campaigns table\n";
    } else {
        echo "ℹ️ buyer_name column already exists\n";
    }
    
    // Add indexes for performance
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'campaigns'");
    $indexNames = array_column($indexes, 'indexname');
    
    if (!in_array('campaigns_buyer_id_index', $indexNames)) {
        DB::statement('CREATE INDEX campaigns_buyer_id_index ON campaigns(buyer_id)');
        echo "✅ Added index on buyer_id\n";
    }
    
    echo "\n✅ Campaign table ready for buyer relationships!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
