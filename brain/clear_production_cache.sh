#!/bin/bash

# Clear all Laravel caches on production
echo "Clearing all Laravel caches..."

php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

echo "All caches cleared successfully!"





