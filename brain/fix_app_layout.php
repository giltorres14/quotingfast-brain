#!/usr/bin/env php
<?php
/**
 * Fix app.blade.php layout - remove CSS after closing HTML tag
 */

$file = __DIR__ . '/resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

// Find the position of </html>
$htmlEndPos = strpos($content, '</html>');

if ($htmlEndPos !== false) {
    // Keep only content up to and including </html>
    $cleanContent = substr($content, 0, $htmlEndPos + 7); // +7 for '</html>' length
    
    // Write back the clean content
    file_put_contents($file, $cleanContent);
    
    echo "✅ Fixed app.blade.php - removed orphaned CSS after </html>\n";
    echo "File now ends properly at the closing HTML tag.\n";
} else {
    echo "❌ Could not find </html> tag in file\n";
}



