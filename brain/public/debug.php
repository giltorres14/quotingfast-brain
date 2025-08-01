<?php
// Simple debug script to test Laravel bootstrap
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Laravel Debug Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// Check if .env file exists
$envFile = dirname(__DIR__) . '/.env';
echo ".env file exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

if (file_exists($envFile)) {
    echo ".env file size: " . filesize($envFile) . " bytes\n";
}

// Check if bootstrap/app.php exists
$bootstrapFile = dirname(__DIR__) . '/bootstrap/app.php';
echo "bootstrap/app.php exists: " . (file_exists($bootstrapFile) ? 'YES' : 'NO') . "\n";

// Try to load Laravel
try {
    echo "\n=== Attempting to load Laravel ===\n";
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    echo "Autoloader loaded successfully\n";
    
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    echo "Bootstrap loaded successfully\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel created successfully\n";
    
    echo "Laravel loaded successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Environment Variables ===\n";
echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'NOT SET') . "\n";
echo "APP_KEY: " . (isset($_ENV['APP_KEY']) ? 'SET (' . strlen($_ENV['APP_KEY']) . ' chars)' : 'NOT SET') . "\n";
echo "LOG_CHANNEL: " . ($_ENV['LOG_CHANNEL'] ?? 'NOT SET') . "\n";
?>