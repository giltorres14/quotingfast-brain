<?php
// Runtime debug outside public directory - bypasses Laravel routing completely
?>
<!DOCTYPE html>
<html>
<head><title>🔍 RUNTIME DEBUG (Outside Public)</title></head>
<body>
<h1>🔍 RUNTIME DEBUG - BYPASSING LARAVEL ROUTING</h1>
<pre>
<?php
echo "=== RUNTIME ENVIRONMENT (ROOT LEVEL) ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "PHP version: " . phpversion() . "\n";
echo "Script location: " . __FILE__ . "\n";
echo "\n";

echo "=== CHECKING VENDOR AUTOLOADER ===\n";
$autoloader_path = __DIR__ . '/vendor/autoload.php';
echo "Looking for autoloader at: $autoloader_path\n";
if (file_exists($autoloader_path)) {
    echo "✅ vendor/autoload.php EXISTS\n";
    echo "File size: " . filesize($autoloader_path) . " bytes\n";
    echo "File permissions: " . substr(sprintf('%o', fileperms($autoloader_path)), -4) . "\n";
    echo "File owner: " . posix_getpwuid(fileowner($autoloader_path))['name'] . "\n";
} else {
    echo "❌ vendor/autoload.php MISSING at: $autoloader_path\n";
    // Check if vendor directory exists at all
    $vendor_dir = __DIR__ . '/vendor';
    if (is_dir($vendor_dir)) {
        echo "✅ vendor directory exists\n";
        echo "Vendor directory contents:\n";
        $contents = scandir($vendor_dir);
        foreach (array_slice($contents, 0, 10) as $item) {
            if ($item !== '.' && $item !== '..') {
                echo "  - $item\n";
            }
        }
    } else {
        echo "❌ vendor directory completely missing!\n";
    }
}
echo "\n";

echo "=== CHECKING LARAVEL FRAMEWORK ===\n";
$laravel_path = __DIR__ . '/vendor/laravel/framework';
if (is_dir($laravel_path)) {
    echo "✅ Laravel framework directory EXISTS\n";
    $app_file = $laravel_path . '/src/Illuminate/Foundation/Application.php';
    if (file_exists($app_file)) {
        echo "✅ Illuminate\\Foundation\\Application.php EXISTS\n";
        echo "File size: " . filesize($app_file) . " bytes\n";
        echo "File permissions: " . substr(sprintf('%o', fileperms($app_file)), -4) . "\n";
    } else {
        echo "❌ Illuminate\\Foundation\\Application.php MISSING!\n";
        echo "Laravel framework structure:\n";
        if (is_dir($laravel_path . '/src')) {
            echo "  ✅ src/ directory exists\n";
            if (is_dir($laravel_path . '/src/Illuminate')) {
                echo "  ✅ src/Illuminate/ directory exists\n";
                if (is_dir($laravel_path . '/src/Illuminate/Foundation')) {
                    echo "  ✅ src/Illuminate/Foundation/ directory exists\n";
                    $foundation_files = scandir($laravel_path . '/src/Illuminate/Foundation');
                    echo "  Foundation directory contents:\n";
                    foreach (array_slice($foundation_files, 0, 10) as $file) {
                        if ($file !== '.' && $file !== '..') {
                            echo "    - $file\n";
                        }
                    }
                } else {
                    echo "  ❌ src/Illuminate/Foundation/ directory missing!\n";
                }
            } else {
                echo "  ❌ src/Illuminate/ directory missing!\n";
            }
        } else {
            echo "  ❌ src/ directory missing!\n";
        }
    }
} else {
    echo "❌ Laravel framework directory MISSING!\n";
    // Check if vendor/laravel exists
    $vendor_laravel = __DIR__ . '/vendor/laravel';
    if (is_dir($vendor_laravel)) {
        echo "✅ vendor/laravel directory exists\n";
        $laravel_contents = scandir($vendor_laravel);
        echo "vendor/laravel contents:\n";
        foreach ($laravel_contents as $item) {
            if ($item !== '.' && $item !== '..') {
                echo "  - $item\n";
            }
        }
    } else {
        echo "❌ vendor/laravel directory completely missing!\n";
    }
}
echo "\n";

echo "=== TESTING AUTOLOADER (SAFE) ===\n";
try {
    if (file_exists($autoloader_path)) {
        require_once $autoloader_path;
        echo "✅ Autoloader loaded successfully\n";
        
        // Test if class exists without instantiating
        if (class_exists('Illuminate\\Foundation\\Application', false)) {
            echo "✅ Illuminate\\Foundation\\Application class is registered\n";
            
            // Try to get class info
            $reflection = new ReflectionClass('Illuminate\\Foundation\\Application');
            echo "✅ Class file location: " . $reflection->getFileName() . "\n";
            
            // Try to instantiate
            try {
                $app = new Illuminate\Foundation\Application(realpath(__DIR__));
                echo "✅ Laravel Application instantiated successfully!\n";
            } catch (Exception $e) {
                echo "❌ Laravel Application instantiation failed: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "❌ Illuminate\\Foundation\\Application class NOT FOUND in autoloader\n";
            
            // Check what classes are available
            $declared_classes = get_declared_classes();
            $illuminate_classes = array_filter($declared_classes, function($class) {
                return strpos($class, 'Illuminate\\') === 0;
            });
            echo "Available Illuminate classes: " . count($illuminate_classes) . "\n";
            foreach (array_slice($illuminate_classes, 0, 5) as $class) {
                echo "  - $class\n";
            }
        }
    } else {
        echo "❌ Cannot test autoloader - file missing\n";
    }
} catch (Exception $e) {
    echo "❌ Autoloader exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Autoloader fatal error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== COMPOSER VERIFICATION ===\n";
$composer_json = __DIR__ . '/composer.json';
if (file_exists($composer_json)) {
    echo "✅ composer.json exists\n";
    $composer_data = json_decode(file_get_contents($composer_json), true);
    if (isset($composer_data['require']['symfony/clock'])) {
        echo "❌ symfony/clock still in composer.json!\n";
    } else {
        echo "✅ symfony/clock eliminated from composer.json (VICTORY MAINTAINED)\n";
    }
    
    if (isset($composer_data['require']['laravel/framework'])) {
        echo "✅ laravel/framework in composer.json: " . $composer_data['require']['laravel/framework'] . "\n";
    } else {
        echo "❌ laravel/framework missing from composer.json!\n";
    }
} else {
    echo "❌ composer.json missing\n";
}

$composer_lock = __DIR__ . '/composer.lock';
if (file_exists($composer_lock)) {
    echo "✅ composer.lock exists (size: " . filesize($composer_lock) . " bytes)\n";
} else {
    echo "❌ composer.lock missing\n";
}

echo "\n=== END RUNTIME DEBUG ===\n";
?>
</pre>
</body>
</html>