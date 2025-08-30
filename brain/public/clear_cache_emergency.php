<?php
/**
 * EMERGENCY CACHE CLEAR
 * Access this via browser to force clear all caches
 */

// Security check - only allow for 1 hour
$allowedUntil = strtotime('2025-08-22 04:30:00');
if (time() > $allowedUntil) {
    die('This emergency script has expired for security.');
}

echo "<h1>Emergency Cache Clear</h1>";
echo "<pre>";

// Move to Laravel root
chdir('..');

// Clear all possible caches
$commands = [
    'php artisan view:clear',
    'php artisan cache:clear', 
    'php artisan config:clear',
    'php artisan route:clear',
    'rm -rf storage/framework/views/*',
    'rm -rf storage/framework/cache/*',
    'rm -rf bootstrap/cache/*.php',
];

foreach ($commands as $cmd) {
    echo "Running: $cmd\n";
    $output = shell_exec($cmd . ' 2>&1');
    echo $output . "\n";
}

// Also try to clear opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset\n";
}

echo "</pre>";
echo "<h2>✅ All caches cleared!</h2>";
echo "<p><a href='/agent/lead/491801?mode=view'>Test Lead Page</a></p>";








