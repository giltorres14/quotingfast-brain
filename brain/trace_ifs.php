#!/usr/bin/env php
<?php

$file = file_get_contents('resources/views/agent/lead-display.blade.php');
$lines = explode("\n", $file);

$stack = [];
$lineNum = 1;

echo "Tracing @if/@endif pairs:\n";
echo "=========================\n\n";

foreach ($lines as $line) {
    // Check for @if
    if (preg_match('/@if\s*\(/', $line)) {
        $indent = str_repeat("  ", count($stack));
        echo "{$indent}Line $lineNum: @if OPEN (depth " . (count($stack) + 1) . ")\n";
        $stack[] = ['type' => 'if', 'line' => $lineNum];
    }
    
    // Check for @endif
    if (preg_match('/@endif/', $line)) {
        if (empty($stack)) {
            echo "ERROR: Extra @endif at line $lineNum (no matching @if)\n";
        } else {
            $opened = array_pop($stack);
            $indent = str_repeat("  ", count($stack));
            echo "{$indent}Line $lineNum: @endif CLOSE (closes @if from line {$opened['line']})\n";
        }
    }
    
    $lineNum++;
}

echo "\n";
if (!empty($stack)) {
    echo "UNCLOSED @if statements:\n";
    foreach ($stack as $item) {
        echo "  Line {$item['line']}: Still open\n";
    }
} else {
    echo "All @if statements are properly closed.\n";
}
