#!/bin/bash

# Clear all Laravel caches
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Clear opcache if available
if php -r "exit(function_exists('opcache_reset') ? 0 : 1);"; then
    php -r "opcache_reset();"
    echo "OPcache cleared"
fi

echo "All caches cleared successfully"

# Clear all Laravel caches
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Clear opcache if available
if php -r "exit(function_exists('opcache_reset') ? 0 : 1);"; then
    php -r "opcache_reset();"
    echo "OPcache cleared"
fi

echo "All caches cleared successfully"




