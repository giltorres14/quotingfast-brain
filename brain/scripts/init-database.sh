#!/bin/bash

# Initialize SQLite database for Render deployment
echo "🔧 Initializing SQLite database..."

# Create database directory if it doesn't exist
mkdir -p /var/www/html/database

# Create SQLite database file if it doesn't exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "📁 Creating SQLite database file..."
    touch /var/www/html/database/database.sqlite
    chmod 666 /var/www/html/database/database.sqlite
    echo "✅ SQLite database file created"
else
    echo "✅ SQLite database file already exists"
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

echo "🎉 Database initialization complete!"