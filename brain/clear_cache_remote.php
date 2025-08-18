<?php
// This script will clear all Laravel caches

echo "🧹 Clearing all Laravel caches...\n\n";

$commands = [
    'php artisan config:clear' => 'Configuration cache',
    'php artisan cache:clear' => 'Application cache', 
    'php artisan route:clear' => 'Route cache',
    'php artisan view:clear' => 'View cache',
    'php artisan optimize:clear' => 'All optimizations'
];

foreach ($commands as $command => $description) {
    echo "Clearing $description...\n";
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ $description cleared successfully\n";
    } else {
        echo "❌ Failed to clear $description\n";
        echo implode("\n", $output) . "\n";
    }
    
    echo "\n";
}

echo "✅ All caches cleared!\n";
// This script will clear all Laravel caches

echo "🧹 Clearing all Laravel caches...\n\n";

$commands = [
    'php artisan config:clear' => 'Configuration cache',
    'php artisan cache:clear' => 'Application cache', 
    'php artisan route:clear' => 'Route cache',
    'php artisan view:clear' => 'View cache',
    'php artisan optimize:clear' => 'All optimizations'
];

foreach ($commands as $command => $description) {
    echo "Clearing $description...\n";
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ $description cleared successfully\n";
    } else {
        echo "❌ Failed to clear $description\n";
        echo implode("\n", $output) . "\n";
    }
    
    echo "\n";
}

echo "✅ All caches cleared!\n";
