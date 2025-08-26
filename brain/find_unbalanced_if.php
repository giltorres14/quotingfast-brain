#!/usr/bin/env php
<?php

$file = file_get_contents('resources/views/agent/lead-display.blade.php');
$lines = explode("\n", $file);

$stack = [];
$lineNum = 1;

foreach ($lines as $line) {
    // Count @if (including @elseif)
    if (preg_match('/@if\s*\(/', $line)) {
        $stack[] = ['type' => 'if', 'line' => $lineNum, 'content' => trim($line)];
    }
    
    // Count @endif
    if (preg_match('/@endif/', $line)) {
        if (empty($stack)) {
            echo "ERROR: Extra @endif at line $lineNum: " . trim($line) . "\n";
        } else {
            array_pop($stack);
        }
    }
    
    $lineNum++;
}

if (!empty($stack)) {
    echo "\nUnclosed @if statements:\n";
    foreach ($stack as $item) {
        echo "Line {$item['line']}: {$item['content']}\n";
    }
}

echo "\nTotal lines: " . count($lines) . "\n";








