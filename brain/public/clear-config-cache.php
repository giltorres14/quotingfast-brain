<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Clearing Laravel configuration cache...\n";

try {
    // Clear config cache
    Artisan::call('config:clear');
    echo "✅ Config cache cleared\n";
    
    // Clear application cache
    Artisan::call('cache:clear');
    echo "✅ Application cache cleared\n";
    
    // Clear route cache
    Artisan::call('route:clear');
    echo "✅ Route cache cleared\n";
    
    // Clear view cache
    Artisan::call('view:clear');
    echo "✅ View cache cleared\n";
    
    // Now check the database config
    $config = config('database.connections.pgsql');
    echo "\n📊 Current PostgreSQL Configuration:\n";
    echo "Host: " . $config['host'] . "\n";
    echo "Port: " . $config['port'] . "\n";
    echo "Database: " . $config['database'] . "\n";
    echo "Username: " . $config['username'] . "\n";
    echo "Password exists: " . (!empty($config['password']) ? 'Yes' : 'No') . "\n";
    
    // Test connection
    echo "\n🔌 Testing database connection...\n";
    try {
        DB::connection()->getPdo();
        echo "✅ Database connection successful!\n";
        
        $leadCount = DB::table('leads')->count();
        echo "📈 Total leads in database: $leadCount\n";
    } catch (\Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

