<?php
// TEMPORARY DEBUG SCRIPT - Replace Laravel index.php to test basic functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== BASIC PHP TEST ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "\n";

// Check if critical files exist
$files_to_check = [
    '.env' => dirname(__DIR__) . '/.env',
    'bootstrap/app.php' => dirname(__DIR__) . '/bootstrap/app.php',
    'vendor/autoload.php' => dirname(__DIR__) . '/vendor/autoload.php',
    'config/app.php' => dirname(__DIR__) . '/config/app.php',
    'config/logging.php' => dirname(__DIR__) . '/config/logging.php'
];

echo "\n=== FILE EXISTENCE CHECK ===\n";
foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    echo "$name: " . ($exists ? "EXISTS ($size bytes)" : "MISSING") . "\n";
}

// Try to load Laravel
echo "\n=== LARAVEL BOOTSTRAP TEST ===\n";
try {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    echo "✅ Autoloader loaded successfully\n";
    
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    echo "✅ Laravel app bootstrapped successfully\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ HTTP Kernel created successfully\n";
    
    echo "🎉 LARAVEL IS WORKING!\n";
    
} catch (Exception $e) {
    echo "❌ Laravel Bootstrap Error: " . $e->getMessage() . "\n";
    echo "Error File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ENVIRONMENT VARIABLES ===\n";
$env_vars = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'LOG_CHANNEL', 'DB_CONNECTION'];
foreach ($env_vars as $var) {
    $value = $_ENV[$var] ?? getenv($var) ?? 'NOT SET';
    echo "$var: $value\n";
}

echo "\n=== END DEBUG ===\n";
?>