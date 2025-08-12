<?php
// Fix lead-flow page specifically

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔧 FIXING LEAD-FLOW PAGE\n";
echo "=====================================\n\n";

// 1. Clear specific cached view
echo "1. Clearing cached lead-flow view...\n";
$cachedView = storage_path('framework/views/f54e3fc16e2a238a1206c7601b4c07a9.php');
if (file_exists($cachedView)) {
    unlink($cachedView);
    echo "   ✅ Deleted cached view: f54e3fc16e2a238a1206c7601b4c07a9.php\n";
} else {
    echo "   ⚠️ Cached view not found\n";
}

// 2. Clear ALL cached views to be sure
echo "\n2. Clearing ALL cached views...\n";
$viewPath = storage_path('framework/views');
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        unlink($file);
        $count++;
    }
    echo "   ✅ Deleted $count cached views\n";
}

// 3. Clear all Laravel caches
echo "\n3. Clearing all Laravel caches...\n";
\Artisan::call('view:clear');
echo "   ✅ View cache cleared\n";
\Artisan::call('config:clear');
echo "   ✅ Config cache cleared\n";
\Artisan::call('cache:clear');
echo "   ✅ Application cache cleared\n";
\Artisan::call('route:clear');
echo "   ✅ Route cache cleared\n";

// 4. Test the lead-flow route directly
echo "\n4. Testing lead-flow route...\n";
try {
    $controller = new \App\Http\Controllers\LeadFlowController();
    $request = \Illuminate\Http\Request::create('/admin/lead-flow', 'GET');
    
    // Call the index method
    $response = $controller->index($request);
    
    if ($response) {
        echo "   ✅ Controller returns response\n";
        
        // Try to render the view
        try {
            $html = $response->render();
            echo "   ✅ View renders successfully!\n";
            
            // Check if it contains expected content
            if (strpos($html, 'Lead Flow Visualization') !== false) {
                echo "   ✅ View contains expected content\n";
            }
        } catch (\Exception $e) {
            echo "   ❌ View render error: " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

// 5. Test the actual URL
echo "\n5. Testing actual URL...\n";
$url = "https://quotingfast-brain-ohio.onrender.com/admin/lead-flow";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "   ✅ Page returns 200 OK\n";
} else {
    echo "   ❌ Page returns $httpCode\n";
}

// 6. Check if all required methods exist
echo "\n6. Checking controller methods...\n";
$controller = new \App\Http\Controllers\LeadFlowController();
$methods = ['index', 'getFlowData', 'getHourlyIntake', 'getConversionFunnel', 'getListBreakdown', 'getAgentPerformance'];
foreach ($methods as $method) {
    if (method_exists($controller, $method)) {
        echo "   ✅ Method exists: $method\n";
    } else {
        echo "   ❌ Method missing: $method\n";
    }
}

echo "\n=====================================\n";
echo "🏁 FIX COMPLETE\n";
echo "\nThe lead-flow page should now work. If it still shows 500:\n";
echo "1. Wait 30 seconds for cache to fully clear\n";
echo "2. Try accessing the page again\n";
echo "3. The view compilation will happen fresh\n";
