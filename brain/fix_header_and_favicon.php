<?php
// Script to fix header alignment and create a better favicon

$viewsPath = __DIR__ . '/resources/views';

// Get all blade files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$bladeFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $bladeFiles[] = $file->getPathname();
    }
}

foreach ($bladeFiles as $fullPath) {
    $content = file_get_contents($fullPath);
    $modified = false;
    
    // Fix header container height to accommodate 150px logo
    // Need at least 170px for the logo plus padding
    $content = preg_replace('/(\.header-container\s*{[^}]*height:\s*)100px/s', '${1}170px', $content);
    $content = preg_replace('/(\.nav-container\s*{[^}]*height:\s*)100px/s', '${1}170px', $content);
    
    // Also fix any inline height styles for nav containers
    $content = preg_replace('/height:\s*100px;\s*\/\*[^*]*\*\//i', 'height: 170px;', $content);
    
    // Fix navbar height if needed
    $content = preg_replace('/(\.navbar\s*{[^}]*height:\s*)70px/s', '${1}170px', $content);
    
    // Check if file was modified
    if (strpos($content, 'height: 170px') !== false || strpos($content, 'height:170px') !== false) {
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($fullPath, $content);
        $relativePath = str_replace($viewsPath . '/', '', $fullPath);
        echo "Fixed header height in: $relativePath\n";
    }
}

echo "\nHeader alignment fixes complete!\n";
