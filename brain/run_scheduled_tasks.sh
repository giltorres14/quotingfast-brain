#!/bin/bash
# Master script for running scheduled ViciDial tasks
# This ensures tasks run even if Laravel scheduler fails

export TZ=America/New_York
BRAIN_DIR="/var/www/html/brain"
cd $BRAIN_DIR

# Get current time
HOUR=$(date +%H)
MINUTE=$(date +%M)

# Log function
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> storage/logs/automation.log
}

# Only run during business hours (9 AM - 6 PM)
if [ $HOUR -ge 9 ] && [ $HOUR -lt 18 ]; then
    
    # Every 30 minutes - Test A Lead Flow
    if [ $((MINUTE % 30)) -eq 0 ]; then
        log_message "Running Test A Lead Flow"
        php artisan vici:test-a-flow >> storage/logs/test_a_flow.log 2>&1 &
    fi
    
    # Every hour - Optimal Timing Control
    if [ $MINUTE -eq 0 ]; then
        log_message "Running Optimal Timing Control"
        php artisan vici:optimal-timing >> storage/logs/optimal_timing.log 2>&1 &
    fi
fi

# Every 15 minutes - Sync (runs 24/7)
if [ $((MINUTE % 15)) -eq 0 ]; then
    log_message "Running Incremental Sync"
    php artisan vici:sync-logs --incremental >> storage/logs/sync.log 2>&1 &
fi

# Every 5 minutes - Health Check
if [ $((MINUTE % 5)) -eq 0 ]; then
    log_message "Running Health Check"
    php artisan system:health-check >> storage/logs/health.log 2>&1 &
fi

# Daily at 2 AM - Cleanup
if [ $HOUR -eq 2 ] && [ $MINUTE -eq 0 ]; then
    log_message "Running Daily Cleanup"
    find storage/logs -name "*.log" -mtime +30 -delete
    log_message "Cleaned old logs"
fi

log_message "Scheduled tasks check complete"
