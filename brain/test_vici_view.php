<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Test rendering the Vici view
try {
    echo "Testing Vici view rendering...\n\n";
    
    // Get the view
    $view = view('vici.dashboard');
    
    // Try to render it
    $html = $view->render();
    
    echo "✅ View rendered successfully!\n";
    echo "HTML length: " . strlen($html) . " characters\n";
    
    // Check for any errors in the HTML
    if (strpos($html, 'Exception') !== false || strpos($html, 'Error') !== false) {
        echo "⚠️ Warning: The rendered HTML contains error text\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error rendering view: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Test rendering the Vici view
try {
    echo "Testing Vici view rendering...\n\n";
    
    // Get the view
    $view = view('vici.dashboard');
    
    // Try to render it
    $html = $view->render();
    
    echo "✅ View rendered successfully!\n";
    echo "HTML length: " . strlen($html) . " characters\n";
    
    // Check for any errors in the HTML
    if (strpos($html, 'Exception') !== false || strpos($html, 'Error') !== false) {
        echo "⚠️ Warning: The rendered HTML contains error text\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error rendering view: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
