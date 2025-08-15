#!/bin/bash

# Start Laravel Scheduler for Vici Sync
# This runs the scheduler every minute to check for due tasks

echo "=== Starting Laravel Scheduler for Vici Sync ==="
echo "This will run continuously. Press Ctrl+C to stop."
echo ""
echo "📅 Scheduled tasks:"
echo "  • vici:sync-incremental - Every 5 minutes"
echo "  • vici:match-orphans - Every 10 minutes"
echo ""
echo "📊 Logs will be written to:"
echo "  • storage/logs/scheduler.log (scheduler activity)"
echo "  • storage/logs/vici_sync.log (sync results)"
echo ""
echo "Starting scheduler..."

# Create log file if it doesn't exist
touch storage/logs/scheduler.log

# Run scheduler in a loop
while true; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Running scheduled tasks..." >> storage/logs/scheduler.log
    php artisan schedule:run --no-interaction >> storage/logs/scheduler.log 2>&1
    
    # Show a dot to indicate it's still running
    echo -n "."
    
    # Wait 60 seconds before next check
    sleep 60
done

