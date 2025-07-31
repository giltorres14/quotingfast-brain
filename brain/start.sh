#!/bin/bash
set -e

echo "Starting Laravel application..."

# Generate app key if needed
php artisan key:generate --force --no-interaction

# Run database migrations
php artisan migrate --force --no-interaction

# Install and setup Filament
php artisan filament:install --panels --no-interaction

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache

echo "Laravel setup complete, starting server..."

# Start Laravel development server (like your local setup)
php artisan serve --host=0.0.0.0 --port=8080