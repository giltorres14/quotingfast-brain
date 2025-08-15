<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Check if vendors table exists
    if (Schema::hasTable('vendors')) {
        echo "✅ Vendors table EXISTS\n";
        
        // Get table columns
        $columns = Schema::getColumnListing('vendors');
        echo "Columns: " . implode(', ', $columns) . "\n";
        
        // Count records
        $count = DB::table('vendors')->count();
        echo "Total vendors: $count\n";
    } else {
        echo "❌ Vendors table DOES NOT EXIST\n";
        echo "Running migration...\n";
        
        // Run the migration
        Artisan::call('migrate', ['--force' => true]);
        echo "Migration completed.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
