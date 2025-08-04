#!/bin/bash
set -e

echo "🚀 DEPLOYMENT STABILITY SCRIPT - Ensuring Database Persistence"

# Check if PostgreSQL is available
if [ -n "$DATABASE_URL" ] && [[ "$DATABASE_URL" == postgres* ]]; then
    echo "✅ PostgreSQL detected - Using persistent database"
    export DB_CONNECTION=pgsql
    echo "DB_CONNECTION=pgsql" >> /var/www/html/.env
    
    # Test PostgreSQL connection
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'PostgreSQL connection successful\n';"
    
    # Run migrations on PostgreSQL
    php artisan migrate --force
    
    echo "✅ PostgreSQL setup complete"
    
elif [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ]; then
    echo "✅ PostgreSQL environment variables detected"
    export DB_CONNECTION=pgsql
    echo "DB_CONNECTION=pgsql" >> /var/www/html/.env
    
    # Run migrations on PostgreSQL
    php artisan migrate --force
    
    echo "✅ PostgreSQL setup complete"
    
else
    echo "⚠️  PostgreSQL not available - Using persistent SQLite"
    
    # Create persistent SQLite database in /tmp (better persistence on Render)
    PERSISTENT_DB="/tmp/brain_persistent.sqlite"
    
    if [ ! -f "$PERSISTENT_DB" ]; then
        echo "📁 Creating persistent SQLite database..."
        touch "$PERSISTENT_DB"
        chmod 666 "$PERSISTENT_DB"
        
        # Set database path in environment
        export DB_DATABASE="$PERSISTENT_DB"
        echo "DB_DATABASE=$PERSISTENT_DB" >> /var/www/html/.env
        
        # Run migrations on new database
        php artisan migrate --force
        
        echo "✅ New persistent SQLite database created"
    else
        echo "✅ Using existing persistent SQLite database"
        export DB_DATABASE="$PERSISTENT_DB"
        echo "DB_DATABASE=$PERSISTENT_DB" >> /var/www/html/.env
    fi
    
    # Create symlink for application access
    ln -sf "$PERSISTENT_DB" /var/www/html/database/database.sqlite
fi

echo "🎯 Database stability ensured - Deployment ready"