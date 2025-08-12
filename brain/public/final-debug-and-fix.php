<?php
// Final debug and fix - get to the root of the 500 errors

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔍 FINAL DEBUG AND FIX\n";
echo "=====================================\n\n";

// 1. Check Laravel log for actual errors
echo "1. Checking Laravel error log...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $log = file_get_contents($logFile);
    $lines = explode("\n", $log);
    $recentErrors = [];
    
    // Get last 50 lines that contain errors
    for ($i = count($lines) - 1; $i >= max(0, count($lines) - 200); $i--) {
        if (strpos($lines[$i], 'ERROR') !== false || 
            strpos($lines[$i], 'CRITICAL') !== false ||
            strpos($lines[$i], 'production.ERROR') !== false) {
            $recentErrors[] = $lines[$i];
            if (count($recentErrors) >= 5) break;
        }
    }
    
    if (!empty($recentErrors)) {
        echo "   Recent errors found:\n";
        foreach ($recentErrors as $error) {
            // Extract just the error message
            if (preg_match('/production\.ERROR: (.+?) \{/', $error, $matches)) {
                echo "   ❌ " . $matches[1] . "\n";
            } else {
                echo "   ❌ " . substr($error, 0, 150) . "\n";
            }
        }
    } else {
        echo "   ✅ No recent errors in log\n";
    }
} else {
    echo "   ⚠️ Log file not found\n";
}

// 2. Test view compilation directly
echo "\n2. Testing view compilation...\n";

$viewsToTest = [
    'leads.index' => 'Leads Page View',
    'admin.control-center' => 'Control Center View',
    'admin.lead-flow' => 'Lead Flow View'
];

foreach ($viewsToTest as $viewName => $description) {
    echo "   Testing $description ($viewName):\n";
    try {
        // Check if view file exists
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (!file_exists($viewPath)) {
            echo "     ❌ View file not found: $viewPath\n";
            continue;
        }
        
        // Try to compile the view
        $view = view($viewName);
        
        // Check if view needs data
        if ($viewName === 'leads.index') {
            // Provide minimal data for leads view
            $view->with([
                'leads' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'statuses' => collect(),
                'sources' => collect(),
                'states' => collect(),
                'search' => '',
                'status' => '',
                'source' => '',
                'state_filter' => '',
                'vici_status' => '',
                'stats' => [
                    'total_leads' => 0,
                    'today_leads' => 0,
                    'vici_sent' => 0,
                    'allstate_sent' => 0
                ]
            ]);
        } elseif ($viewName === 'admin.lead-flow') {
            // Provide minimal data for lead-flow view
            $view->with([
                'funnel' => [],
                'hourlyIntake' => [],
                'sourcePerformance' => [],
                'agentPerformance' => [],
                'listBreakdown' => [],
                'startDate' => date('Y-m-d'),
                'endDate' => date('Y-m-d')
            ]);
        }
        
        // Try to render
        $html = $view->render();
        echo "     ✅ View compiles successfully\n";
        
    } catch (\Exception $e) {
        echo "     ❌ Compilation error: " . $e->getMessage() . "\n";
        echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        
        // Try to fix common issues
        if (strpos($e->getMessage(), 'Undefined variable') !== false) {
            echo "     🔧 Fix: View is expecting variables that aren't being passed\n";
        } elseif (strpos($e->getMessage(), 'Class') !== false && strpos($e->getMessage(), 'not found') !== false) {
            echo "     🔧 Fix: Missing class or incorrect namespace\n";
        }
    }
}

// 3. Test routes directly
echo "\n3. Testing routes with minimal setup...\n";

$routes = [
    '/leads' => 'Leads Route',
    '/admin/control-center' => 'Control Center Route',
    '/admin/lead-flow' => 'Lead Flow Route'
];

foreach ($routes as $route => $name) {
    echo "   Testing $name:\n";
    try {
        // Create a request
        $request = \Illuminate\Http\Request::create($route, 'GET');
        
        // Get the route
        $router = app('router');
        $matchedRoute = $router->getRoutes()->match($request);
        
        if ($matchedRoute) {
            echo "     ✅ Route exists\n";
            
            // Check if it's a controller or closure
            $action = $matchedRoute->getAction();
            if (isset($action['controller'])) {
                echo "     Controller: " . $action['controller'] . "\n";
                
                // Check if controller exists
                list($controllerClass, $method) = explode('@', $action['controller']);
                if (class_exists($controllerClass)) {
                    echo "     ✅ Controller class exists\n";
                } else {
                    echo "     ❌ Controller class not found: $controllerClass\n";
                }
            } elseif (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                echo "     Type: Closure (inline function)\n";
            }
        } else {
            echo "     ❌ Route not found\n";
        }
    } catch (\Exception $e) {
        echo "     ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 4. Check PHP configuration
echo "\n4. Checking PHP configuration...\n";
echo "   Memory limit: " . ini_get('memory_limit') . "\n";
echo "   Max execution time: " . ini_get('max_execution_time') . " seconds\n";
echo "   Error reporting: " . error_reporting() . "\n";
echo "   Display errors: " . ini_get('display_errors') . "\n";

// 5. Check file permissions
echo "\n5. Checking file permissions...\n";
$paths = [
    storage_path('framework/views'),
    storage_path('logs'),
    storage_path('framework/cache'),
    storage_path('framework/sessions')
];

foreach ($paths as $path) {
    if (is_dir($path)) {
        $writable = is_writable($path);
        echo "   " . basename(dirname($path)) . "/" . basename($path) . ": " . 
             ($writable ? "✅ Writable" : "❌ Not writable") . "\n";
    } else {
        echo "   " . basename(dirname($path)) . "/" . basename($path) . ": ❌ Directory doesn't exist\n";
        // Try to create it
        if (mkdir($path, 0777, true)) {
            echo "     ✅ Created directory\n";
        }
    }
}

// 6. Apply fixes
echo "\n6. Applying fixes...\n";

// Clear compiled views
$viewPath = storage_path('framework/views');
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*.php');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "   ✅ Cleared compiled views\n";
}

// Clear all caches
\Artisan::call('view:clear');
\Artisan::call('config:clear');
\Artisan::call('cache:clear');
echo "   ✅ Cleared all caches\n";

// Set proper permissions
$storagePath = storage_path();
if (is_dir($storagePath)) {
    exec("chmod -R 777 " . escapeshellarg($storagePath));
    echo "   ✅ Fixed storage permissions\n";
}

echo "\n=====================================\n";
echo "🏁 DEBUG COMPLETE\n";
echo "\nBased on the findings above, the issue is likely one of:\n";
echo "1. View compilation errors (check error messages above)\n";
echo "2. Missing controller classes\n";
echo "3. Permission issues with storage directories\n";
echo "4. Variables not being passed to views\n";
echo "\nThe specific errors found will guide the fix needed.\n";
