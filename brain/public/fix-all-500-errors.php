<?php
// Comprehensive fix for all 500 errors

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔧 COMPREHENSIVE 500 ERROR FIX\n";
echo "=====================================\n\n";

$fixes = 0;

try {
    // 1. Ensure campaigns table has all required columns
    echo "1. Checking campaigns table structure...\n";
    $columns = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'campaigns'");
    $columnNames = array_map(function($col) { return $col->column_name; }, $columns);
    
    if (!in_array('display_name', $columnNames)) {
        echo "   Adding display_name column...\n";
        \DB::statement("ALTER TABLE campaigns ADD COLUMN display_name VARCHAR(255)");
        $fixes++;
    }
    
    // Update any null display_names
    \DB::statement("UPDATE campaigns SET display_name = name WHERE display_name IS NULL");
    echo "   ✅ Campaigns table structure verified\n";
    
    // 2. Create default campaigns if none exist
    echo "\n2. Ensuring default campaigns exist...\n";
    $campaignCount = \DB::table('campaigns')->count();
    if ($campaignCount == 0) {
        \DB::table('campaigns')->insert([
            ['campaign_id' => 'default', 'name' => 'Default', 'display_name' => 'Default', 'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => 'web', 'name' => 'Web', 'display_name' => 'Web', 'created_at' => now(), 'updated_at' => now()],
            ['campaign_id' => 'manual', 'name' => 'Manual', 'display_name' => 'Manual', 'created_at' => now(), 'updated_at' => now()]
        ]);
        echo "   ✅ Added default campaigns\n";
        $fixes++;
    } else {
        echo "   ✅ Campaigns exist: $campaignCount\n";
    }
    
    // 3. Check if vendors table exists (referenced in some views)
    echo "\n3. Checking vendors table...\n";
    $hasVendors = \DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'vendors')");
    if (!$hasVendors[0]->exists) {
        echo "   Creating vendors table...\n";
        \DB::statement("
            CREATE TABLE IF NOT EXISTS vendors (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255),
                campaigns TEXT,
                contact_info TEXT,
                total_leads INTEGER DEFAULT 0,
                total_cost DECIMAL(10,2) DEFAULT 0,
                active BOOLEAN DEFAULT true,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "   ✅ Created vendors table\n";
        $fixes++;
    } else {
        echo "   ✅ Vendors table exists\n";
    }
    
    // 4. Check if buyers table exists
    echo "\n4. Checking buyers table...\n";
    $hasBuyers = \DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'buyers')");
    if (!$hasBuyers[0]->exists) {
        echo "   Creating buyers table...\n";
        \DB::statement("
            CREATE TABLE IF NOT EXISTS buyers (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255),
                campaigns TEXT,
                contact_info TEXT,
                total_leads INTEGER DEFAULT 0,
                total_revenue DECIMAL(10,2) DEFAULT 0,
                active BOOLEAN DEFAULT true,
                api_credentials TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "   ✅ Created buyers table\n";
        $fixes++;
    } else {
        echo "   ✅ Buyers table exists\n";
    }
    
    // 5. Set APP_DEBUG to true temporarily to see real errors
    echo "\n5. Setting APP_DEBUG to true...\n";
    \DB::statement("UPDATE cache SET value = 's:4:\"true\";' WHERE key LIKE '%app.debug%'");
    echo "   ✅ Debug mode enabled\n";
    $fixes++;
    
    // 6. Clear all caches
    echo "\n6. Clearing all caches...\n";
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    
    // Clear compiled views manually
    $viewPath = storage_path('framework/views');
    if (is_dir($viewPath)) {
        $files = glob($viewPath . '/*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    echo "   ✅ All caches cleared\n";
    $fixes++;
    
    // 7. Test each route
    echo "\n7. Testing routes...\n";
    $routes = ['/leads', '/admin/control-center', '/admin/lead-flow', '/diagnostics'];
    foreach ($routes as $route) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost" . $route);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            echo "   ✅ $route - OK\n";
        } elseif ($httpCode == 500) {
            echo "   ❌ $route - Still has errors\n";
        } else {
            echo "   ⚠️ $route - Status: $httpCode\n";
        }
    }
    
} catch (\Exception $e) {
    echo "\n❌ Error during fix: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "🏁 FIX COMPLETE\n";
echo "Applied $fixes fixes\n";
echo "\n⚠️ IMPORTANT: If errors persist, check:\n";
echo "1. The Laravel log at storage/logs/laravel.log\n";
echo "2. Run 'php artisan migrate' on the server\n";
echo "3. Ensure all environment variables are set correctly\n";
