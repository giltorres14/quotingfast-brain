<?php
// Simple route test to find the real issue

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔍 SIMPLE ROUTE TEST\n";
echo "=====================================\n\n";

// Test each problematic URL directly
$urls = [
    '/leads',
    '/admin/control-center', 
    '/admin/lead-flow',
    '/diagnostics'
];

foreach ($urls as $url) {
    echo "Testing $url:\n";
    
    // Use curl to test the actual response
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost" . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  HTTP Status: $httpCode\n";
    
    if ($httpCode === 500) {
        // Try to extract error from response
        if (preg_match('/<title>(.*?)<\/title>/i', $response, $matches)) {
            echo "  Page Title: " . $matches[1] . "\n";
        }
        
        // Check Laravel log for this request
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $lines = explode("\n", $logContent);
            $recentLines = array_slice($lines, -20);
            
            foreach ($recentLines as $line) {
                if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
                    if (strpos($line, $url) !== false || strpos($line, date('Y-m-d')) !== false) {
                        echo "  Recent Error: " . substr($line, 0, 200) . "\n";
                        break;
                    }
                }
            }
        }
    }
    
    echo "\n";
}

// Check if routes are registered
echo "\n📋 REGISTERED ROUTES CHECK:\n";
echo "=====================================\n";

$router = app('router');
$routes = $router->getRoutes();

foreach ($urls as $url) {
    $found = false;
    foreach ($routes as $route) {
        if ($route->uri() === ltrim($url, '/')) {
            $found = true;
            echo "✅ Route registered: $url\n";
            
            // Check the action
            $action = $route->getAction();
            if (isset($action['uses'])) {
                if ($action['uses'] instanceof \Closure) {
                    echo "   Type: Closure\n";
                } else {
                    echo "   Type: " . $action['uses'] . "\n";
                }
            }
            break;
        }
    }
    
    if (!$found) {
        echo "❌ Route NOT registered: $url\n";
    }
}

echo "\n=====================================\n";
echo "🏁 TEST COMPLETE\n";
