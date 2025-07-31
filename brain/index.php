<?php

// Bootstrap Laravel application for DigitalOcean
echo "Starting Laravel bootstrap...\n";

// Set up Laravel
require_once __DIR__.'/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';

// Run necessary setup commands
echo "Running Laravel setup...\n";

// Generate app key
exec('php artisan key:generate --force --no-interaction 2>&1', $output1);
echo "Key generated: " . implode("\n", $output1) . "\n";

// Run migrations
exec('php artisan migrate --force --no-interaction 2>&1', $output2);
echo "Migrations: " . implode("\n", $output2) . "\n";

// Install Filament
exec('php artisan filament:install --panels --no-interaction 2>&1', $output3);
echo "Filament: " . implode("\n", $output3) . "\n";

// Cache routes and config
exec('php artisan config:cache 2>&1', $output4);
exec('php artisan route:cache 2>&1', $output5);

echo "Laravel setup complete!\n";

// Handle the HTTP request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);