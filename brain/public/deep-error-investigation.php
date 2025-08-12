<?php
// Deep dive error investigation
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔍 DEEP ERROR INVESTIGATION\n";
echo "=====================================\n\n";

// Test each route directly
$problematicRoutes = [
    '/leads' => 'Leads Page',
    '/admin/control-center' => 'Control Center',
    '/admin/lead-flow' => 'Lead Flow',
    '/diagnostics' => 'Diagnostics'
];

foreach ($problematicRoutes as $route => $name) {
    echo "\n📍 Testing: $name ($route)\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        // Create a request
        $request = \Illuminate\Http\Request::create($route, 'GET');
        
        // Get the route
        $router = app('router');
        $matchedRoute = $router->getRoutes()->match($request);
        
        if ($matchedRoute) {
            $action = $matchedRoute->getAction();
            
            // Check if it's a closure or controller
            if (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                echo "  Route Type: Closure\n";
                
                // Try to execute the closure
                try {
                    ob_start();
                    $result = $action['uses']($request);
                    $output = ob_get_clean();
                    
                    if ($result instanceof \Illuminate\Http\Response) {
                        echo "  Response Status: " . $result->getStatusCode() . "\n";
                        if ($result->getStatusCode() === 500) {
                            // Try to get the exception
                            $content = $result->getContent();
                            if (strpos($content, 'Server Error') !== false) {
                                echo "  ❌ Returns 500 Server Error\n";
                            }
                        }
                    }
                } catch (\Exception $e) {
                    ob_end_clean();
                    echo "  ❌ Exception in route closure:\n";
                    echo "     Error: " . $e->getMessage() . "\n";
                    echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                    
                    // Get the specific error details
                    if (strpos($e->getMessage(), 'Undefined variable') !== false) {
                        echo "     Type: Undefined Variable\n";
                    } elseif (strpos($e->getMessage(), 'Class') !== false && strpos($e->getMessage(), 'not found') !== false) {
                        echo "     Type: Missing Class\n";
                    } elseif (strpos($e->getMessage(), 'Call to undefined') !== false) {
                        echo "     Type: Undefined Method/Function\n";
                    } elseif (strpos($e->getMessage(), 'table') !== false || strpos($e->getMessage(), 'column') !== false) {
                        echo "     Type: Database Issue\n";
                    }
                    
                    // Show first few lines of trace
                    $trace = $e->getTraceAsString();
                    $traceLines = explode("\n", $trace);
                    echo "     Trace (first 3 lines):\n";
                    for ($i = 0; $i < min(3, count($traceLines)); $i++) {
                        echo "       " . $traceLines[$i] . "\n";
                    }
                }
            } elseif (isset($action['controller'])) {
                echo "  Route Type: Controller - " . $action['controller'] . "\n";
                
                // Try to call the controller
                try {
                    list($controller, $method) = explode('@', $action['controller']);
                    $controllerInstance = app()->make($controller);
                    
                    ob_start();
                    $result = $controllerInstance->$method($request);
                    ob_end_clean();
                    
                    if ($result instanceof \Illuminate\Http\Response) {
                        echo "  Response Status: " . $result->getStatusCode() . "\n";
                    }
                } catch (\Exception $e) {
                    ob_end_clean();
                    echo "  ❌ Exception in controller:\n";
                    echo "     Error: " . $e->getMessage() . "\n";
                    echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                }
            }
        } else {
            echo "  ⚠️ No route found\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Route testing failed:\n";
        echo "     " . $e->getMessage() . "\n";
    }
}

// Check for common issues
echo "\n\n🔎 CHECKING COMMON ISSUES\n";
echo "=====================================\n";

// Check all models referenced in views
$viewsToCheck = [
    'leads/index.blade.php',
    'admin/control-center.blade.php',
    'admin/lead-flow.blade.php',
    'diagnostics/index.blade.php'
];

foreach ($viewsToCheck as $viewPath) {
    $fullPath = resource_path('views/' . $viewPath);
    if (file_exists($fullPath)) {
        echo "\n📄 Checking view: $viewPath\n";
        $content = file_get_contents($fullPath);
        
        // Check for model references
        if (preg_match_all('/\\\\App\\\\Models\\\\(\w+)/', $content, $matches)) {
            foreach (array_unique($matches[1]) as $model) {
                $modelClass = "\\App\\Models\\$model";
                if (class_exists($modelClass)) {
                    echo "  ✅ Model exists: $model\n";
                    
                    // Check if table exists
                    $tableName = (new $modelClass)->getTable();
                    try {
                        $tableExists = \Schema::hasTable($tableName);
                        if ($tableExists) {
                            echo "     ✅ Table exists: $tableName\n";
                        } else {
                            echo "     ❌ Table missing: $tableName\n";
                        }
                    } catch (\Exception $e) {
                        echo "     ❌ Error checking table: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "  ❌ Model missing: $model\n";
                }
            }
        }
        
        // Check for undefined variables
        if (preg_match_all('/\$(\w+)(?!this)/', $content, $matches)) {
            $variables = array_unique($matches[1]);
            $commonLaravelVars = ['errors', 'loop', 'app', 'auth', 'request', 'session'];
            $suspiciousVars = array_diff($variables, $commonLaravelVars);
            
            if (count($suspiciousVars) > 0) {
                echo "  ⚠️ Variables used (check if passed from route): " . implode(', ', array_slice($suspiciousVars, 0, 10)) . "\n";
            }
        }
    }
}

echo "\n=====================================\n";
echo "🏁 INVESTIGATION COMPLETE\n";
