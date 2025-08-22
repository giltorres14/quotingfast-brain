#!/usr/bin/env php
<?php
/**
 * EMERGENCY FIX APPLICATION
 * Removes @if/@endif from JavaScript blocks which is causing the error
 */

echo "\n🔧 APPLYING EMERGENCY FIX\n";
echo "=========================\n\n";

$bladePath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
$backupPath = __DIR__ . '/resources/views/agent/lead-display.blade.backup.' . date('Y-m-d-H-i-s') . '.php';

// Step 1: Backup current file
echo "📦 Creating backup...\n";
copy($bladePath, $backupPath);
echo "  Backup saved to: " . basename($backupPath) . "\n\n";

// Step 2: Read the file
echo "📖 Reading current file...\n";
$content = file_get_contents($bladePath);
$lines = explode("\n", $content);

// Step 3: Fix the specific problems
echo "🔨 Fixing problems:\n";

// Problem 1: @if/@endif at lines 879-881 inside script
// Find and fix the first script block issue
$fixed = false;
for ($i = 878; $i <= 881 && $i < count($lines); $i++) {
    if (strpos($lines[$i], '@if') !== false || strpos($lines[$i], '@endif') !== false) {
        echo "  - Removing Blade directive from line " . ($i + 1) . "\n";
        // Replace @if with PHP
        if (strpos($lines[$i], '@if') !== false) {
            $lines[$i] = str_replace('@if', '<?php if', $lines[$i]) . ': ?>';
        }
        if (strpos($lines[$i], '@endif') !== false) {
            $lines[$i] = '<?php endif; ?>';
        }
        $fixed = true;
    }
}

// Problem 2: @if/@endif at line 4358 inside script
for ($i = 4357; $i <= 4358 && $i < count($lines); $i++) {
    if (strpos($lines[$i], '@if') !== false) {
        echo "  - Fixing Blade directive at line " . ($i + 1) . "\n";
        // This appears to be: leadPayload: @if(isset($leadPayload)) @json($leadPayload) @else null @endif
        // Replace with PHP ternary
        $lines[$i] = preg_replace(
            '/@if\(isset\(\$leadPayload\)\)\s*@json\(\$leadPayload\)\s*@else\s*null\s*@endif/',
            '<?php echo isset($leadPayload) ? json_encode($leadPayload) : "null"; ?>',
            $lines[$i]
        );
        $fixed = true;
    }
}

if (!$fixed) {
    echo "  No Blade-in-JavaScript issues found at expected lines.\n";
    echo "  Searching entire file for the pattern...\n";
    
    // Search for the specific problematic pattern
    $inScript = false;
    $scriptStart = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Track script blocks
        if (strpos($line, '<script>') !== false || strpos($line, '<script ') !== false) {
            $inScript = true;
            $scriptStart = $i + 1;
        }
        if ($inScript && strpos($line, '</script>') !== false) {
            $inScript = false;
        }
        
        // Fix @if/@endif inside scripts
        if ($inScript && (strpos($line, '@if') !== false || strpos($line, '@endif') !== false)) {
            echo "  - Found and fixing Blade in JavaScript at line " . ($i + 1) . "\n";
            
            // Special handling for leadPayload pattern
            if (strpos($line, 'leadPayload') !== false && strpos($line, '@if') !== false) {
                $lines[$i] = preg_replace(
                    '/@if\s*\(isset\(\$leadPayload\)\)\s*@json\(\$leadPayload\)\s*@else\s*null\s*@endif/',
                    '<?php echo isset($leadPayload) ? json_encode($leadPayload) : "null"; ?>',
                    $line
                );
            } else {
                // Generic fix - comment out the Blade directives
                $lines[$i] = str_replace(['@if', '@endif', '@else'], ['//if', '//endif', '//else'], $line);
            }
            $fixed = true;
        }
    }
}

// Step 4: Write the fixed content
if ($fixed) {
    echo "\n💾 Writing fixed content...\n";
    $newContent = implode("\n", $lines);
    file_put_contents($bladePath, $newContent);
    echo "  File updated successfully!\n";
    
    // Verify the fix
    $verifyContent = file_get_contents($bladePath);
    $inScript = false;
    $hasProblems = false;
    $verifyLines = explode("\n", $verifyContent);
    
    foreach ($verifyLines as $i => $line) {
        if (strpos($line, '<script>') !== false || strpos($line, '<script ') !== false) {
            $inScript = true;
        }
        if ($inScript && strpos($line, '</script>') !== false) {
            $inScript = false;
        }
        if ($inScript && (strpos($line, '@if') !== false || strpos($line, '@endif') !== false)) {
            $hasProblems = true;
            echo "  ⚠️ Still has Blade in JavaScript at line " . ($i + 1) . "\n";
        }
    }
    
    if (!$hasProblems) {
        echo "\n✅ All Blade-in-JavaScript issues fixed!\n";
    }
} else {
    echo "\n⚠️ No changes needed - file appears clean.\n";
}

echo "\n📋 Next steps:\n";
echo "1. Commit and push: git add -A && git commit -m 'Fix Blade in JavaScript' && git push\n";
echo "2. Wait for deployment (60-90 seconds)\n";
echo "3. Test the page\n";
echo "\nIf issues persist, restore from backup:\n";
echo "  cp $backupPath $bladePath\n";

