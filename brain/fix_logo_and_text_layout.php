<?php
// Script to fix logo size to 3x (150px) and layout "The" over "Brain"

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
    
    // Fix logo height from 50px to 150px (3x from original 50px)
    $content = preg_replace('/height:\s*50px/i', 'height: 150px', $content);
    
    // Fix logo-image class
    $content = preg_replace('/\.logo-image\s*{\s*height:\s*50px;/s', '.logo-image {
            height: 150px;', $content);
    
    // Fix .logo class
    $content = preg_replace('/\.logo\s*{\s*height:\s*50px;/s', '.logo {
            height: 150px;', $content);
    
    // Fix .brand-logo class
    $content = preg_replace('/\.brand-logo\s*{\s*height:\s*50px;/s', '.brand-logo {
            height: 150px;', $content);
    
    // Fix inline styles
    $content = preg_replace('/style="height:\s*50px;/i', 'style="height:150px;', $content);
    
    // Update The Brain layout - replace single line with stacked layout
    $patterns = [
        '/<div class="logo-text"><div class="brand-text">The Brain<\/div><\/div>/',
        '/<div class="brand-text">The Brain<\/div>/',
    ];
    
    $replacement = '<div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: \'Orbitron\', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: \'Orbitron\', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>';
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            $modified = true;
        }
    }
    
    // Also update header container height to accommodate larger logo
    $content = preg_replace('/(\.header-container\s*{[^}]*height:\s*)70px/s', '${1}100px', $content);
    $content = preg_replace('/(\.nav-container\s*{[^}]*height:\s*)70px/s', '${1}100px', $content);
    
    // Check if file was modified
    if (strpos($content, 'height: 150px') !== false || strpos($content, 'height:150px') !== false) {
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($fullPath, $content);
        $relativePath = str_replace($viewsPath . '/', '', $fullPath);
        echo "Updated logo size and text layout in: $relativePath\n";
    }
}

echo "\nLogo size and text layout fixes complete!\n";
