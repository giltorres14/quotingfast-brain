#!/usr/bin/env php
<?php
/**
 * Get Current Time in EST/EDT
 * This script provides the current date and time in Florida timezone
 * Used by the AI assistant to maintain accurate time awareness
 */

// Set timezone to Florida (automatically handles EST/EDT)
date_default_timezone_set('America/New_York');

// Get current time
$now = new DateTime();

// Output in multiple formats for clarity
echo "=== CURRENT DATE AND TIME ===\n";
echo "Timezone: America/New_York (Florida)\n";
echo "Full DateTime: " . $now->format('Y-m-d H:i:s T') . "\n";
echo "Date: " . $now->format('F j, Y') . "\n";
echo "Time: " . $now->format('g:i A') . "\n";
echo "Day: " . $now->format('l') . "\n";
echo "Timestamp: " . $now->getTimestamp() . "\n";
echo "ISO 8601: " . $now->format('c') . "\n";

// ViciDial relevant time checks
$hour = (int)$now->format('H');
$dayOfWeek = $now->format('N'); // 1 (Monday) to 7 (Sunday)

echo "\n=== VICIDIAL TIME ANALYSIS ===\n";

// Check if within calling hours (9 AM - 6 PM EST, Mon-Fri)
if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
    if ($hour >= 9 && $hour < 18) {
        echo "Status: WITHIN calling hours (9 AM - 6 PM EST)\n";
        
        // Determine optimal calling period
        if (($hour >= 9 && $hour <= 11) || ($hour >= 15 && $hour <= 17)) {
            echo "Period: PEAK hours (High contact rate)\n";
            echo "Recommended Dial Ratio: 1.8 - 2.0\n";
        } else {
            echo "Period: OFF-PEAK hours\n";
            echo "Recommended Dial Ratio: 2.5 - 3.0\n";
        }
    } elseif ($hour < 9) {
        $minutesUntil = (9 - $hour) * 60 - (int)$now->format('i');
        echo "Status: BEFORE calling hours\n";
        echo "Calling starts in: " . $minutesUntil . " minutes\n";
    } else {
        echo "Status: AFTER calling hours\n";
        echo "Calling resumes: Tomorrow 9:00 AM EST\n";
    }
} else {
    echo "Status: WEEKEND - No regular calling\n";
    echo "Calling resumes: Monday 9:00 AM EST\n";
}

// TCPA compliance check
$leadAge = 89; // Will be passed as parameter in actual use
echo "\n=== TCPA COMPLIANCE ===\n";
echo "Current time for TCPA: " . $now->format('g:i A T') . "\n";
if ($hour >= 21 || $hour < 8) {
    echo "TCPA Status: ❌ Cannot call (outside 8 AM - 9 PM)\n";
} else {
    echo "TCPA Status: ✅ Can call (within 8 AM - 9 PM)\n";
}

// Output for memory update
echo "\n=== FOR MEMORY UPDATE ===\n";
echo "Today is " . $now->format('F j, Y') . ", " . $now->format('g:i A T') . ". ";
echo "The user is located in Florida, United States.\n";












