<?php
// Direct Laravel bootstrap test
require_once __DIR__.'/../vendor/autoload.php';

try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo json_encode([
        'success' => true,
        'message' => 'Laravel bootstrap successful',
        'laravel_version' => app()->version(),
        'environment' => app()->environment(),
        'routes_loaded' => count(app('router')->getRoutes()),
        'debug_mode' => config('app.debug'),
        'timestamp' => now()->toISOString()
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>