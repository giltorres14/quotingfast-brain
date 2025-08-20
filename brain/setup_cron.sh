#!/bin/bash
# This script sets up the Laravel scheduler to run automatically

echo "Setting up Laravel scheduler cron job..."

# The ONLY cron entry needed for Laravel
# This runs every minute and Laravel decides what to execute
CRON_ENTRY="* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1"

# Add to crontab (this would be done on the server)
echo "Add this line to your server's crontab:"
echo "$CRON_ENTRY"
echo ""
echo "To add it, run: crontab -e"
echo "Then paste the line above and save"
echo ""
echo "Once added, Laravel will handle ALL scheduled tasks automatically:"
echo "- Call log syncs every 15 minutes"
echo "- Lead flow movements every 30 minutes"  
echo "- Optimal timing control every hour"
echo "- Health checks every 5 minutes"
echo ""
echo "NO HUMAN INTERACTION REQUIRED! 🤖"