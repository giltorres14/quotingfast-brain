<?php
// Runtime Autoloader Debugging - Accessible via web
echo "<h1>🔍 RUNTIME AUTOLOADER DEBUGGING</h1>";
echo "<pre>";

echo "=== RUNTIME ENVIRONMENT DIAGNOSTICS ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "PHP version: " . phpversion() . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "\n";

echo "=== CHECKING VENDOR AUTOLOADER ===\n";
$autoloader_path = __DIR__ . '/../vendor/autoload.php';
echo "Looking for autoloader at: $autoloader_path\n";
if (file_exists($autoloader_path)) {
    echo "✅ vendor/autoload.php EXISTS\n";
    echo "Autoloader file size: " . filesize($autoloader_path) . " bytes\n";
    echo "Autoloader permissions: " . substr(sprintf('%o', fileperms($autoloader_path)), -4) . "\n";
} else {
    echo "❌ vendor/autoload.php MISSING!\n";
}
echo "\n";

echo "=== CHECKING LARAVEL FRAMEWORK ===\n";
$laravel_path = __DIR__ . '/../vendor/laravel/framework';
if (is_dir($laravel_path)) {
    echo "✅ Laravel framework directory EXISTS\n";
    echo "Laravel framework path: $laravel_path\n";
    $foundation_path = $laravel_path . '/src/Illuminate/Foundation/Application.php';
    if (file_exists($foundation_path)) {
        echo "✅ Illuminate\\Foundation\\Application.php EXISTS\n";
        echo "Application.php size: " . filesize($foundation_path) . " bytes\n";
    } else {
        echo "❌ Illuminate\\Foundation\\Application.php MISSING!\n";
    }
} else {
    echo "❌ Laravel framework directory MISSING!\n";
}
echo "\n";

echo "=== TESTING AUTOLOADER AT RUNTIME ===\n";
try {
    require_once $autoloader_path;
    echo "✅ Autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== TESTING LARAVEL APPLICATION CLASS ===\n";
try {
    if (class_exists('Illuminate\\Foundation\\Application')) {
        echo "✅ Illuminate\\Foundation\\Application class found\n";
        $app = new Illuminate\Foundation\Application(realpath(__DIR__ . '/..'));
        echo "✅ Laravel Application instantiated successfully\n";
    } else {
        echo "❌ Illuminate\\Foundation\\Application class NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "❌ Laravel Application failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== COMPOSER AUTOLOADER DIAGNOSTICS ===\n";
if (function_exists('spl_autoload_functions')) {
    $autoloaders = spl_autoload_functions();
    echo "Number of registered autoloaders: " . count($autoloaders) . "\n";
    foreach ($autoloaders as $i => $loader) {
        if (is_array($loader)) {
            echo "Autoloader $i: " . get_class($loader[0]) . "::" . $loader[1] . "\n";
        } else {
            echo "Autoloader $i: " . (is_string($loader) ? $loader : gettype($loader)) . "\n";
        }
    }
} else {
    echo "❌ spl_autoload_functions not available\n";
}
echo "\n";

echo "=== SYMFONY/CLOCK CHECK (should be eliminated) ===\n";
$composer_json_path = __DIR__ . '/../composer.json';
if (file_exists($composer_json_path)) {
    $composer_data = json_decode(file_get_contents($composer_json_path), true);
    if (isset($composer_data['require']['symfony/clock'])) {
        echo "❌ symfony/clock still in composer.json!\n";
    } else {
        echo "✅ symfony/clock eliminated from composer.json\n";
    }
} else {
    echo "❌ composer.json not found\n";
}

echo "\n=== END DIAGNOSTICS ===\n";
echo "</pre>";
?>