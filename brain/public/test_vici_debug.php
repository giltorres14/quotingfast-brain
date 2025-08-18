<?php
// Temporary debug script - REMOVE AFTER TESTING
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "<h1>Vici Page Debug</h1>";

try {
    echo "<h2>Testing Database Connection...</h2>";
    $pdo = DB::connection()->getPdo();
    echo "<p style='color:green'>✅ Database connected</p>";
    
    echo "<h2>Testing OrphanCallLog Model...</h2>";
    
    // Check if table exists
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'orphan_call_logs'");
    if (count($tables) > 0) {
        echo "<p style='color:green'>✅ Table 'orphan_call_logs' exists</p>";
    } else {
        echo "<p style='color:red'>❌ Table 'orphan_call_logs' does not exist</p>";
    }
    
    // Check columns
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'orphan_call_logs'");
    echo "<p>Columns in orphan_call_logs: ";
    foreach ($columns as $col) {
        echo $col->column_name . ", ";
    }
    echo "</p>";
    
    // Test the unmatched scope
    echo "<h2>Testing OrphanCallLog::unmatched() scope...</h2>";
    $count = \App\Models\OrphanCallLog::unmatched()->count();
    echo "<p style='color:green'>✅ Unmatched orphan calls: $count</p>";
    
    // Test ViciCallMetrics
    echo "<h2>Testing ViciCallMetrics...</h2>";
    $totalCalls = \App\Models\ViciCallMetrics::count();
    echo "<p style='color:green'>✅ Total calls: $totalCalls</p>";
    
    $todayCalls = \App\Models\ViciCallMetrics::whereDate('created_at', today())->count();
    echo "<p style='color:green'>✅ Today's calls: $todayCalls</p>";
    
    echo "<h2>Testing View Rendering...</h2>";
    $view = view('vici.dashboard');
    $html = $view->render();
    echo "<p style='color:green'>✅ View rendered successfully (HTML length: " . strlen($html) . ")</p>";
    
    echo "<h2 style='color:green'>ALL TESTS PASSED!</h2>";
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>ERROR FOUND:</h2>";
    echo "<p style='color:red'>Message: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
// Temporary debug script - REMOVE AFTER TESTING
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "<h1>Vici Page Debug</h1>";

try {
    echo "<h2>Testing Database Connection...</h2>";
    $pdo = DB::connection()->getPdo();
    echo "<p style='color:green'>✅ Database connected</p>";
    
    echo "<h2>Testing OrphanCallLog Model...</h2>";
    
    // Check if table exists
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'orphan_call_logs'");
    if (count($tables) > 0) {
        echo "<p style='color:green'>✅ Table 'orphan_call_logs' exists</p>";
    } else {
        echo "<p style='color:red'>❌ Table 'orphan_call_logs' does not exist</p>";
    }
    
    // Check columns
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'orphan_call_logs'");
    echo "<p>Columns in orphan_call_logs: ";
    foreach ($columns as $col) {
        echo $col->column_name . ", ";
    }
    echo "</p>";
    
    // Test the unmatched scope
    echo "<h2>Testing OrphanCallLog::unmatched() scope...</h2>";
    $count = \App\Models\OrphanCallLog::unmatched()->count();
    echo "<p style='color:green'>✅ Unmatched orphan calls: $count</p>";
    
    // Test ViciCallMetrics
    echo "<h2>Testing ViciCallMetrics...</h2>";
    $totalCalls = \App\Models\ViciCallMetrics::count();
    echo "<p style='color:green'>✅ Total calls: $totalCalls</p>";
    
    $todayCalls = \App\Models\ViciCallMetrics::whereDate('created_at', today())->count();
    echo "<p style='color:green'>✅ Today's calls: $todayCalls</p>";
    
    echo "<h2>Testing View Rendering...</h2>";
    $view = view('vici.dashboard');
    $html = $view->render();
    echo "<p style='color:green'>✅ View rendered successfully (HTML length: " . strlen($html) . ")</p>";
    
    echo "<h2 style='color:green'>ALL TESTS PASSED!</h2>";
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>ERROR FOUND:</h2>";
    echo "<p style='color:red'>Message: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}




