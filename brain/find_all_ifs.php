#!/usr/bin/env php
<?php

$file = file_get_contents('resources/views/agent/lead-display.blade.php');
$lines = explode("\n", $file);

$stack = [];
$lineNum = 1;
$allIfs = [];

foreach ($lines as $line) {
    // Count @if (including @elseif but not counting elseif as new if)
    if (preg_match('/@if\s*\(/', $line)) {
        $stack[] = ['type' => 'if', 'line' => $lineNum, 'content' => trim($line)];
        $allIfs[] = ['line' => $lineNum, 'content' => trim($line), 'status' => 'open'];
    }
    
    // Count @endif
    if (preg_match('/@endif/', $line)) {
        if (empty($stack)) {
            echo "ERROR: Extra @endif at line $lineNum: " . trim($line) . "\n";
        } else {
            $closed = array_pop($stack);
            // Mark the if as closed
            foreach ($allIfs as &$if) {
                if ($if['line'] == $closed['line']) {
                    $if['status'] = 'closed';
                    $if['closed_at'] = $lineNum;
                    break;
                }
            }
        }
    }
    
    $lineNum++;
}

echo "\nAll @if statements and their status:\n";
echo "=====================================\n";
foreach ($allIfs as $if) {
    if ($if['status'] == 'open') {
        echo "❌ Line {$if['line']}: {$if['content']} - UNCLOSED\n";
    }
}

if (!empty($stack)) {
    echo "\n\nUnclosed @if statements in stack:\n";
    foreach ($stack as $item) {
        echo "Line {$item['line']}: {$item['content']}\n";
    }
} else {
    echo "\n✅ All @if statements appear to be closed based on stack analysis.\n";
}

$ifCount = substr_count($file, '@if');
$endifCount = substr_count($file, '@endif');
echo "\nDirect count: @if: $ifCount, @endif: $endifCount, Diff: " . ($ifCount - $endifCount) . "\n";
