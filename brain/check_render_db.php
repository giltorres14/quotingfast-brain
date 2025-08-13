<?php
// Check .env for database configuration
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    echo "=== .env DATABASE CONFIG ===\n";
    
    // Extract DB settings
    preg_match('/DB_CONNECTION=(.*)/', $env, $connection);
    preg_match('/DB_HOST=(.*)/', $env, $host);
    preg_match('/DB_DATABASE=(.*)/', $env, $database);
    preg_match('/DB_USERNAME=(.*)/', $env, $username);
    
    if (!empty($connection[1])) {
        echo "DB_CONNECTION: " . trim($connection[1]) . "\n";
    }
    if (!empty($host[1])) {
        echo "DB_HOST: " . trim($host[1]) . "\n";
    }
    if (!empty($database[1])) {
        echo "DB_DATABASE: " . trim($database[1]) . "\n";
    }
    if (!empty($username[1])) {
        echo "DB_USERNAME: " . trim($username[1]) . "\n";
    }
} else {
    echo "No .env file found\n";
}

// Check if there's a .env.production
if (file_exists('.env.production')) {
    echo "\n=== .env.production EXISTS ===\n";
    $prod = file_get_contents('.env.production');
    preg_match('/DB_CONNECTION=(.*)/', $prod, $connection);
    preg_match('/DB_HOST=(.*)/', $prod, $host);
    if (!empty($connection[1])) {
        echo "DB_CONNECTION: " . trim($connection[1]) . "\n";
    }
    if (!empty($host[1])) {
        echo "DB_HOST: " . trim($host[1]) . "\n";
    }
}
