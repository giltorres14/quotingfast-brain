#!/bin/bash
# Simple script to get current time for the assistant
# This will be used to maintain accurate timestamps throughout our work

# Get current time in EST/EDT (Florida timezone)
echo "Current Time: $(date '+%Y-%m-%d %H:%M:%S %Z')"
echo "Unix Timestamp: $(date +%s)"
echo "Day of Week: $(date '+%A')"
echo "Time in 12hr format: $(date '+%I:%M %p')"

# Check if it's within ViciDial calling hours (9 AM - 6 PM)
current_hour=$(date +%H)
if [ $current_hour -ge 9 ] && [ $current_hour -lt 18 ]; then
    echo "ViciDial Status: Within calling hours (9 AM - 6 PM)"
else
    echo "ViciDial Status: Outside calling hours"
fi





