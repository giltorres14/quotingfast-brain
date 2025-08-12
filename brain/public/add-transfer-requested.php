<?php
// Add transfer_requested column to vici_call_metrics table

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 ADDING TRANSFER_REQUESTED COLUMN\n";
echo "=====================================\n\n";

try {
    if (Schema::hasTable('vici_call_metrics')) {
        if (!Schema::hasColumn('vici_call_metrics', 'transfer_requested')) {
            echo "Adding transfer_requested column...\n";
            DB::statement("ALTER TABLE vici_call_metrics ADD COLUMN transfer_requested BOOLEAN DEFAULT false");
            echo "✅ Added transfer_requested column\n";
        } else {
            echo "✅ transfer_requested column already exists\n";
        }
    } else {
        echo "❌ vici_call_metrics table does not exist\n";
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
