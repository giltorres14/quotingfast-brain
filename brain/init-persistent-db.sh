#!/bin/bash
# Emergency SQLite persistence fix
# Create database in /tmp which has better persistence on Render
DB_PATH="/tmp/brain_persistent.sqlite"
if [ ! -f "$DB_PATH" ]; then
    echo "Creating persistent SQLite database..."
    touch "$DB_PATH"
    chmod 666 "$DB_PATH"
    php artisan migrate --force --database=sqlite_persistent
else
    echo "Using existing persistent database"
fi
ln -sf "$DB_PATH" database/database.sqlite
