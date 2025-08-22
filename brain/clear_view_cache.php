#!/usr/bin/env php
<?php

echo "Clearing Laravel view cache...\n";

// Clear compiled views
$viewPath = __DIR__ . '/storage/framework/views';
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Deleted: " . basename($file) . "\n";
        }
    }
    echo "✅ Cleared " . count($files) . " compiled view files\n";
} else {
    echo "❌ View cache directory not found\n";
}

// Clear blade cache
$cachePath = __DIR__ . '/bootstrap/cache';
if (is_dir($cachePath)) {
    $files = glob($cachePath . '/*.php');
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== 'packages.php' && basename($file) !== 'services.php') {
            unlink($file);
            echo "Deleted cache: " . basename($file) . "\n";
        }
    }
}

echo "\n✅ View cache cleared successfully!\n";
echo "The application will recompile views on next request.\n";

