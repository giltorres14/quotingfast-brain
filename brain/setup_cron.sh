#!/bin/bash

# Setup Laravel scheduler cron job for Vici sync
# This script adds the Laravel scheduler to crontab if it doesn't exist

CURRENT_DIR=$(pwd)
PHP_PATH=$(which php)

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "✅ Laravel scheduler cron job already exists"
    crontab -l | grep "schedule:run"
else
    echo "📝 Adding Laravel scheduler cron job..."
    
    # Add the cron job
    (crontab -l 2>/dev/null; echo "* * * * * cd $CURRENT_DIR && $PHP_PATH artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    echo "✅ Laravel scheduler cron job added successfully!"
    echo ""
    echo "The following cron job has been added:"
    echo "* * * * * cd $CURRENT_DIR && $PHP_PATH artisan schedule:run >> /dev/null 2>&1"
    echo ""
    echo "This will run every minute and execute:"
    echo "  - vici:sync-incremental (every 5 minutes)"
    echo "  - vici:run-export (every 5 minutes)"
    echo "  - vici:match-orphans (every 10 minutes)"
    echo "  - vici:archive-old-leads (daily at 2 AM)"
fi

echo ""
echo "Current crontab:"
crontab -l

