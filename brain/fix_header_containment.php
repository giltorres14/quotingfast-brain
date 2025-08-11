<?php

$files = [
    'resources/views/admin/simple-dashboard.blade.php',
    'resources/views/admin/allstate-testing.blade.php',
    'resources/views/admin/lead-queue.blade.php',
    'resources/views/admin/buyer-management.blade.php',
    'resources/views/leads/index.blade.php',
    'resources/views/api/directory.blade.php',
    'resources/views/campaign/directory.blade.php',
    'resources/views/analytics/dashboard.blade.php',
    'resources/views/lead-upload/index.blade.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Fix the navigation header to ensure it's properly contained within the blue gradient
    // Update the navbar styles to be contained within the gradient background
    $content = preg_replace(
        '/\.navbar\s*\{[^}]*\}/s',
        '.navbar {
            background: linear-gradient(135deg, #4f46e5 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            width: 100%;
        }',
        $content
    );
    
    // Update nav-container to ensure proper containment
    $content = preg_replace(
        '/\.nav-container\s*\{[^}]*\}/s',
        '.nav-container {
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 120px;
            box-sizing: border-box;
            position: relative;
        }',
        $content
    );
    
    // Ensure nav-menu doesn't overflow
    $content = preg_replace(
        '/\.nav-menu\s*\{[^}]*\}/s',
        '.nav-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: nowrap;
        }',
        $content
    );
    
    // Make sure dropdown menus appear below the header, not extending it
    $content = preg_replace(
        '/\.dropdown-menu\s*\{([^}]*)\}/s',
        '.dropdown-menu {$1
            position: absolute;
            top: calc(100% + 10px);
            margin-top: 0;
        }',
        $content
    );
    
    // Update header container height to be consistent
    $content = preg_replace(
        '/\.header-container\s*\{([^}]*height:\s*)\d+px/s',
        '.header-container {$1120px',
        $content
    );
    
    // Add overflow hidden to prevent content from extending beyond header
    if (!preg_match('/\.navbar[^}]*overflow:\s*hidden/s', $content)) {
        $content = str_replace(
            '.navbar {',
            '.navbar {
            overflow: visible; /* Allow dropdowns to show */
            contain: layout; /* Contain layout within navbar */',
            $content
        );
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}

echo "\nHeader containment fix completed!\n";
