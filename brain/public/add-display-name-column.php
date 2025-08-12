<?php
// Direct fix for missing display_name column

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔧 ADDING DISPLAY_NAME COLUMN\n";
echo "=====================================\n\n";

try {
    // Add the column
    \DB::statement("ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS display_name VARCHAR(255)");
    echo "✅ Added display_name column (or it already exists)\n";
    
    // Update existing campaigns to have display_name
    \DB::statement("UPDATE campaigns SET display_name = name WHERE display_name IS NULL");
    echo "✅ Updated existing campaigns with display_name\n";
    
    // Clear all caches
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    echo "✅ Cleared all caches\n";
    
    echo "\n=====================================\n";
    echo "🏁 FIX COMPLETE - Pages should work now!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
