<?php
// Simple debug that bypasses Laravel bootstrap entirely
?>
<!DOCTYPE html>
<html>
<head><title>Simple Runtime Debug</title></head>
<body>
<h1>🔍 SIMPLE RUNTIME DEBUG (No Laravel Bootstrap)</h1>
<pre>
<?php
echo "=== BASIC ENVIRONMENT ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "PHP version: " . phpversion() . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "\n";

echo "=== CHECKING VENDOR AUTOLOADER ===\n";
$autoloader_path = __DIR__ . '/../vendor/autoload.php';
echo "Looking for autoloader at: $autoloader_path\n";
if (file_exists($autoloader_path)) {
    echo "✅ vendor/autoload.php EXISTS\n";
    echo "File size: " . filesize($autoloader_path) . " bytes\n";
} else {
    echo "❌ vendor/autoload.php MISSING!\n";
}
echo "\n";

echo "=== CHECKING LARAVEL FRAMEWORK ===\n";
$laravel_path = __DIR__ . '/../vendor/laravel/framework';
if (is_dir($laravel_path)) {
    echo "✅ Laravel framework directory EXISTS at: $laravel_path\n";
    $app_path = $laravel_path . '/src/Illuminate/Foundation/Application.php';
    if (file_exists($app_path)) {
        echo "✅ Application.php EXISTS at: $app_path\n";
        echo "File size: " . filesize($app_path) . " bytes\n";
    } else {
        echo "❌ Application.php MISSING!\n";
    }
} else {
    echo "❌ Laravel framework directory MISSING!\n";
}
echo "\n";

echo "=== TESTING AUTOLOADER (SAFE) ===\n";
try {
    if (file_exists($autoloader_path)) {
        require_once $autoloader_path;
        echo "✅ Autoloader loaded without error\n";
        
        if (class_exists('Illuminate\\Foundation\\Application', false)) {
            echo "✅ Illuminate\\Foundation\\Application class exists\n";
        } else {
            echo "❌ Illuminate\\Foundation\\Application class NOT FOUND\n";
        }
    } else {
        echo "❌ Cannot test autoloader - file missing\n";
    }
} catch (Exception $e) {
    echo "❌ Autoloader error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Autoloader fatal error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== SYMFONY/CLOCK CHECK ===\n";
$composer_path = __DIR__ . '/../composer.json';
if (file_exists($composer_path)) {
    $composer_data = json_decode(file_get_contents($composer_path), true);
    if (isset($composer_data['require']['symfony/clock'])) {
        echo "❌ symfony/clock still in composer.json\n";
    } else {
        echo "✅ symfony/clock eliminated from composer.json\n";
    }
} else {
    echo "❌ composer.json missing\n";
}

echo "\n=== END SIMPLE DEBUG ===\n";
?>
</pre>
</body>
</html>