<?php
// Check actual error details

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔍 ERROR INVESTIGATION\n";
echo "=====================================\n\n";

// Check last Laravel error
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    $errorFound = false;
    
    echo "📋 Last Laravel Log Entries:\n";
    echo "----------------------------\n";
    foreach ($lastLines as $line) {
        if (stripos($line, 'ERROR') !== false || stripos($line, 'EXCEPTION') !== false) {
            echo $line;
            $errorFound = true;
        }
    }
    
    if (!$errorFound) {
        echo "No recent errors in Laravel log\n";
    }
} else {
    echo "❌ Laravel log file not found\n";
}

echo "\n📊 Database Tables Check:\n";
echo "----------------------------\n";
try {
    $tables = \DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    foreach ($tables as $table) {
        echo "  ✅ " . $table->table_name . "\n";
        if ($table->table_name === 'campaigns') {
            // Check campaigns table columns
            $columns = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'campaigns'");
            echo "     Columns: ";
            foreach ($columns as $col) {
                echo $col->column_name . ", ";
            }
            echo "\n";
        }
    }
} catch (\Exception $e) {
    echo "  ❌ Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n🔧 Testing Problem Routes:\n";
echo "----------------------------\n";

// Test each problematic route
$routes = ['/leads', '/admin/control-center', '/admin/lead-flow', '/diagnostics'];
foreach ($routes as $route) {
    echo "\nTesting $route:\n";
    try {
        // Simulate the route
        $request = \Illuminate\Http\Request::create($route, 'GET');
        $app->instance('request', $request);
        
        // Try to handle the request
        $response = $kernel->handle($request);
        
        if ($response->getStatusCode() === 500) {
            echo "  ❌ Returns 500 error\n";
            
            // Try to get the actual error
            if ($response->exception) {
                echo "  Error: " . $response->exception->getMessage() . "\n";
                echo "  File: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
            }
        } else {
            echo "  ✅ Returns " . $response->getStatusCode() . "\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Exception: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "  Trace:\n" . substr($e->getTraceAsString(), 0, 500) . "\n";
    }
}

echo "\n=====================================\n";
echo "🏁 ERROR CHECK COMPLETE\n";
