<?php
// Script to update all pages to use the white QuotingFast logo

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
    
    // Replace the colored logo with the white logo
    // The white logo is typically at whitelogo or a similar URL
    $replacements = [
        'https://quotingfast.com/logoqf0704.png' => 'https://quotingfast.com/whitelogo',
        'https://quotingfast.com/qfqflogo.png' => 'https://quotingfast.com/whitelogo',
    ];
    
    foreach ($replacements as $old => $new) {
        if (strpos($content, $old) !== false) {
            $content = str_replace($old, $new, $content);
            $modified = true;
        }
    }
    
    // Also remove the QuotingFast text span since the logo already has it
    // Remove the QuotingFast text that we added
    $patterns = [
        '/<div class="logo-text">\s*<div[^>]*>QuotingFast<\/div>\s*<div[^>]*>The Brain<\/div>\s*<\/div>/s',
        '/<span[^>]*>QuotingFast<\/span>\s*<span[^>]*>The Brain<\/span>/s',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            // Just keep "The Brain" text
            $content = preg_replace($pattern, '<div class="logo-text"><div class="brand-text">The Brain</div></div>', $content);
            $modified = true;
        }
    }
    
    if ($modified) {
        file_put_contents($fullPath, $content);
        $relativePath = str_replace($viewsPath . '/', '', $fullPath);
        echo "Updated to white logo in: $relativePath\n";
    }
}

echo "\nWhite logo update complete!\n";
