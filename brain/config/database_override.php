<?php
/**
 * CRITICAL DATABASE OVERRIDE
 * 
 * This file FORCES the use of PostgreSQL and prevents SQLite mistakes.
 * DO NOT DELETE THIS FILE
 * DO NOT MODIFY THIS FILE
 * 
 * Created: 2025-01-12
 * Reason: Multiple mistakes using SQLite instead of PostgreSQL
 */

// FORCE PostgreSQL configuration
if (!defined('RUNNING_TESTS')) {
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
    
    // Log that we're using the correct database
    if (class_exists('\Log')) {
        \Log::info('✅ Database override active - Using PostgreSQL brain_production');
    }
}


