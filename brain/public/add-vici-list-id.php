<?php
// Add vici_list_id column to leads table

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 ADDING VICI_LIST_ID TO LEADS TABLE\n";
echo "=====================================\n\n";

try {
    if (Schema::hasTable('leads')) {
        if (!Schema::hasColumn('leads', 'vici_list_id')) {
            echo "Adding vici_list_id column...\n";
            DB::statement("ALTER TABLE leads ADD COLUMN vici_list_id INTEGER DEFAULT NULL");
            echo "✅ Added vici_list_id column\n";
        } else {
            echo "✅ vici_list_id column already exists\n";
        }
    } else {
        echo "❌ leads table does not exist\n";
    }
    
    // Clear caches
    echo "\nClearing caches...\n";
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    echo "✅ Caches cleared\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "🏁 COLUMN ADDED - Lead Flow should work now!\n";
