<?php
// Read the file
$content = file_get_contents('resources/views/agent/lead-display.blade.php');

// Remove the duplicate vendor section (lines 1811-1868 approximately)
// This is the section that starts with "<!-- Combined Vendor, Buyer & Cost Information Section - Hidden in Edit Mode -->"
// and ends just before "<!-- Drivers Section (Auto Insurance Only) -->"

$pattern = '/<!-- Combined Vendor, Buyer & Cost Information Section - Hidden in Edit Mode -->.*?(?=<!-- Drivers Section \(Auto Insurance Only\) -->)/s';
$content = preg_replace($pattern, '', $content);

// Write back
file_put_contents('resources/views/agent/lead-display.blade.php', $content);

echo "Removed duplicate vendor section\n";

// Read the file
$content = file_get_contents('resources/views/agent/lead-display.blade.php');

// Remove the duplicate vendor section (lines 1811-1868 approximately)
// This is the section that starts with "<!-- Combined Vendor, Buyer & Cost Information Section - Hidden in Edit Mode -->"
// and ends just before "<!-- Drivers Section (Auto Insurance Only) -->"

$pattern = '/<!-- Combined Vendor, Buyer & Cost Information Section - Hidden in Edit Mode -->.*?(?=<!-- Drivers Section \(Auto Insurance Only\) -->)/s';
$content = preg_replace($pattern, '', $content);

// Write back
file_put_contents('resources/views/agent/lead-display.blade.php', $content);

echo "Removed duplicate vendor section\n";







