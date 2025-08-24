<?php

// Script to create a Filament admin user
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Check if user already exists
    $email = 'admin@quotingfast.com';
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        $user = User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make('QuotingFast2025!'),
        ]);
        echo "✅ Admin user created successfully!\n";
    } else {
        // Update password for existing user
        $user->password = Hash::make('QuotingFast2025!');
        $user->save();
        echo "✅ Admin user password updated!\n";
    }
    
    echo "\n📧 Email: admin@quotingfast.com\n";
    echo "🔑 Password: QuotingFast2025!\n";
    echo "🌐 URL: https://quotingfast-brain-ohio.onrender.com/admin-panel\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nYou can create a user manually by running:\n";
    echo "php artisan make:filament-user\n";
}








