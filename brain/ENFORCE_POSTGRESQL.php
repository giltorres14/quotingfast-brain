<?php
/**
 * CRITICAL: DATABASE ENFORCEMENT SCRIPT
 * 
 * This script MUST be included in EVERY database operation
 * to prevent using SQLite by mistake.
 * 
 * MISTAKE COUNTER: 5+ times used SQLite instead of PostgreSQL
 * TARGET: 0 mistakes going forward
 */

// Check if we're in a Laravel context
if (function_exists('config')) {
    // FORCE PostgreSQL - NO EXCEPTIONS
    config(['database.default' => 'pgsql']);
    config(['database.connections.pgsql' => [
        'driver' => 'pgsql',
        'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
        'port' => 5432,
        'database' => 'brain_production',
        'username' => 'brain_user',
        'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
    ]]);
    
    // Purge any existing connections and reconnect
    if (class_exists('\DB')) {
        \DB::purge('pgsql');
        \DB::reconnect('pgsql');
    }
}

// Set environment variables for CLI commands
putenv('DB_CONNECTION=pgsql');
putenv('DB_HOST=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com');
putenv('DB_PORT=5432');
putenv('DB_DATABASE=brain_production');
putenv('DB_USERNAME=brain_user');
putenv('DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ');

// Log enforcement
if (class_exists('\Log')) {
    \Log::warning('🔒 PostgreSQL ENFORCED - SQLite BLOCKED');
}

// Return true to indicate enforcement is active
return true;

