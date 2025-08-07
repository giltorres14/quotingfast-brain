#!/bin/bash

# Setup script for lead queue processing cron job
# Run this on your production server

echo "Setting up Lead Queue Processing Cron Job..."

# Add cron job to process queue every minute
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "✅ Cron job added successfully!"
echo ""
echo "The system will now:"
echo "1. Accept all leads via /webhook-failsafe.php"
echo "2. Queue them immediately (never lose a lead)"
echo "3. Process the queue every minute automatically"
echo ""
echo "To check if it's working:"
echo "  tail -f storage/logs/lead-queue.log"
echo ""
echo "To manually process queue:"
echo "  php artisan leads:process-queue"
