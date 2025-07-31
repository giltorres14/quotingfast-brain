#!/bin/bash
set -e

echo "Building Laravel Brain..."

# Create necessary directories
mkdir -p bootstrap/cache
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views

# Set permissions
chmod -R 777 storage bootstrap/cache

# Install composer dependencies without cache
composer install --no-dev --optimize-autoloader --no-cache

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    echo "APP_NAME=QuotingFast" > .env
    echo "APP_ENV=production" >> .env
    echo "APP_DEBUG=false" >> .env
    echo "APP_KEY=" >> .env
fi

# Generate app key
php artisan key:generate --force

echo "Laravel Brain build complete!"