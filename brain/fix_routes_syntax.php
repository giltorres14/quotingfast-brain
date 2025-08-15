<?php
// fix_routes_syntax.php
// Fix all commented routes that have uncommented code blocks

$file = 'routes/web.php';
$content = file_get_contents($file);

// List of routes that need to be properly commented
$routesToFix = [
    ['line' => 3556, 'pattern' => "// Route::get('/test/allstate/connection', function () {"],
    ['line' => 6217, 'pattern' => "// Route::get('/test/allstate/{leadId?}', function (\$leadId = 1) {"],
    ['line' => 6488, 'pattern' => "// Route::post('/webhook/home', function (Request \$request) {"],
    ['line' => 6692, 'pattern' => "// Route::post('/webhook/auto', function (Request \$request) {"],
    ['line' => 8494, 'pattern' => "// Route::get('/test/vici-update/{leadId?}', function (\$leadId = 'BRAIN_TEST_VICI') {"],
];

echo "Fixing commented routes in routes/web.php...\n\n";

foreach ($routesToFix as $route) {
    echo "Fixing route at line {$route['line']}...\n";
    
    // Replace the pattern to add /* before it
    $searchPattern = $route['pattern'];
    $replacePattern = "/*\n" . substr($route['pattern'], 3); // Remove // and add /*
    
    if (strpos($content, $searchPattern) !== false) {
        $content = str_replace($searchPattern, $replacePattern, $content);
        echo "  ✅ Added opening comment block\n";
    } else {
        echo "  ⚠️ Pattern not found (may already be fixed)\n";
    }
}

// Now we need to find and close these comment blocks
// We'll look for the closing }); after each route and add */

echo "\nAdding closing comment blocks...\n";

// For each route, we need to find its corresponding closing
$patterns = [
    "Route::get('/test/allstate/connection'",
    "Route::get('/test/allstate/{leadId?}'",
    "Route::post('/webhook/home'",
    "Route::post('/webhook/auto'",
    "Route::get('/test/vici-update/{leadId?}'"
];

foreach ($patterns as $pattern) {
    // Find the route in the content
    $pos = strpos($content, $pattern);
    if ($pos !== false) {
        // Find the closing }); for this route
        $startSearch = $pos;
        $braceCount = 0;
        $inRoute = false;
        
        for ($i = $startSearch; $i < strlen($content); $i++) {
            if (substr($content, $i, 8) === 'function') {
                $inRoute = true;
            }
            
            if ($inRoute) {
                if ($content[$i] === '{') {
                    $braceCount++;
                } elseif ($content[$i] === '}') {
                    $braceCount--;
                    
                    // Check if this is the final closing
                    if ($braceCount === 0 && substr($content, $i, 3) === '});') {
                        // Found the closing, add */ after it if not already there
                        $afterClosing = $i + 3;
                        $nextChars = substr($content, $afterClosing, 10);
                        
                        if (strpos($nextChars, '*/') === false) {
                            // Insert */ after });
                            $content = substr($content, 0, $afterClosing) . "\n*/" . substr($content, $afterClosing);
                            echo "  ✅ Added closing comment for $pattern\n";
                        }
                        break;
                    }
                }
            }
        }
    }
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Routes file fixed!\n";
echo "Now checking syntax...\n\n";

// Check syntax
$output = [];
$returnCode = 0;
exec('php -l routes/web.php 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ No syntax errors found!\n";
} else {
    echo "❌ Syntax errors remain:\n";
    echo implode("\n", $output) . "\n";
}
