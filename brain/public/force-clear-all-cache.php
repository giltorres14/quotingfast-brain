<?php
/**
 * AGGRESSIVE CACHE CLEARING FOR RENDER DEPLOYMENT
 * Based on cumulative learning from Laravel cache issues
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔥 AGGRESSIVE CACHE CLEARING STARTED\n";
echo "=====================================\n\n";

// 1. Clear ALL Laravel caches
$commands = [
    'config:clear' => 'Configuration cache',
    'cache:clear' => 'Application cache',
    'route:clear' => 'Route cache',
    'view:clear' => 'View cache',
    'optimize:clear' => 'All optimizations'
];

foreach ($commands as $command => $description) {
    echo "Clearing $description...\n";
    try {
        Artisan::call($command);
        echo "✅ $description cleared\n";
    } catch (\Exception $e) {
        echo "⚠️  Warning clearing $description: " . $e->getMessage() . "\n";
    }
}

// 2. Manually delete cache files
echo "\n📁 Manually deleting cache files...\n";
$cacheDirs = [
    __DIR__ . '/../bootstrap/cache/*.php',
    __DIR__ . '/../storage/framework/cache/data/*',
    __DIR__ . '/../storage/framework/sessions/*',
    __DIR__ . '/../storage/framework/views/*.php',
];

foreach ($cacheDirs as $pattern) {
    $files = glob($pattern);
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                unlink($file);
                echo "  Deleted: " . basename($file) . "\n";
            }
        }
    }
}

// 3. Force reload configuration
echo "\n🔄 Force reloading configuration...\n";
$app['config']->set('database.connections.pgsql.host', 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com');
$app['config']->set('database.connections.pgsql.port', '5432');
$app['config']->set('database.connections.pgsql.database', 'brain_production');
$app['config']->set('database.connections.pgsql.username', 'brain_user');
$app['config']->set('database.connections.pgsql.password', 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ');

// 4. Test the connection with forced config
echo "\n🔌 Testing database connection with forced config...\n";
echo "Host: " . config('database.connections.pgsql.host') . "\n";
echo "Database: " . config('database.connections.pgsql.database') . "\n";
echo "Username: " . config('database.connections.pgsql.username') . "\n";

try {
    // Force disconnect any existing connections
    DB::purge('pgsql');
    
    // Reconnect with new config
    DB::reconnect('pgsql');
    
    // Test the connection
    DB::connection('pgsql')->getPdo();
    echo "✅ Database connection successful!\n";
    
    // Get lead count
    $leadCount = DB::connection('pgsql')->table('leads')->count();
    echo "📊 Total leads in database: $leadCount\n";
    
    // Get recent leads
    $recentLeads = DB::connection('pgsql')->table('leads')
        ->select('id', 'name', 'created_at')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    
    if ($recentLeads->count() > 0) {
        echo "\n📋 Recent leads:\n";
        foreach ($recentLeads as $lead) {
            echo "  - {$lead->name} (ID: {$lead->id})\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\n🔍 Debugging info:\n";
    echo "Error code: " . $e->getCode() . "\n";
    
    // Try raw PDO connection
    echo "\n🔧 Trying raw PDO connection...\n";
    try {
        $dsn = "pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production";
        $pdo = new PDO($dsn, 'brain_user', 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Raw PDO connection successful!\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM leads");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Lead count via raw PDO: " . $result['count'] . "\n";
        
    } catch (\PDOException $pdoError) {
        echo "❌ Raw PDO also failed: " . $pdoError->getMessage() . "\n";
    }
}

echo "\n=====================================\n";
echo "🏁 CACHE CLEARING COMPLETE\n";
echo "\n⚠️  IMPORTANT: If database still fails, run this command on server:\n";
echo "php artisan config:cache\n";
