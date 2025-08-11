<?php
// Script to fix header width issues

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
    
    // Fix header container to be more responsive and narrower
    // Set logo size to 100px for good visibility
    $content = preg_replace('/height:\s*150px/i', 'height: 100px', $content);
    $content = preg_replace('/height:\s*80px/i', 'height: 100px', $content);
    $content = preg_replace('/height:\s*60px/i', 'height: 100px', $content);
    
    // Fix header container height to 120px to accommodate 100px logo
    $content = preg_replace('/(\.header-container\s*{[^}]*height:\s*)170px/s', '${1}120px', $content);
    $content = preg_replace('/(\.header-container\s*{[^}]*height:\s*)100px/s', '${1}120px', $content);
    $content = preg_replace('/(\.header-container\s*{[^}]*height:\s*)70px/s', '${1}120px', $content);
    $content = preg_replace('/(\.nav-container\s*{[^}]*height:\s*)170px/s', '${1}120px', $content);
    $content = preg_replace('/(\.nav-container\s*{[^}]*height:\s*)100px/s', '${1}120px', $content);
    $content = preg_replace('/(\.nav-container\s*{[^}]*height:\s*)70px/s', '${1}120px', $content);
    
    // Fix navbar height
    $content = preg_replace('/(\.navbar\s*{[^}]*height:\s*)170px/s', '${1}120px', $content);
    $content = preg_replace('/(\.navbar\s*{[^}]*height:\s*)100px/s', '${1}120px', $content);
    $content = preg_replace('/(\.navbar\s*{[^}]*height:\s*)70px/s', '${1}120px', $content);
    
    // Ensure header doesn't overflow
    if (strpos($content, '.header {') !== false && strpos($content, 'overflow-x: hidden') === false) {
        $content = preg_replace('/(\.header\s*{)/s', '$1
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;', $content);
        $modified = true;
    }
    
    // Fix header-container to prevent overflow
    if (strpos($content, '.header-container {') !== false) {
        $content = preg_replace('/(\.header-container\s*{[^}]*)(max-width:\s*1400px;)/s', '$1max-width: 100%;
            width: 100%;
            box-sizing: border-box;', $content);
        $modified = true;
    }
    
    // Check if file was modified
    if (strpos($content, 'height: 100px') !== false || strpos($content, 'height: 120px') !== false || strpos($content, 'height:100px') !== false) {
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($fullPath, $content);
        $relativePath = str_replace($viewsPath . '/', '', $fullPath);
        echo "Fixed header width in: $relativePath\n";
    }
}

echo "\nHeader width fixes complete!\n";
