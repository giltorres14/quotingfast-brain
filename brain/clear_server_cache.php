<?php
/**
 * Clear all Laravel caches on the server
 * This will force the server to use the latest templates
 */

// Clear all caches
echo "Clearing server caches...\n";

// Clear compiled views
shell_exec('php artisan view:clear');
echo "✓ View cache cleared\n";

// Clear application cache
shell_exec('php artisan cache:clear');
echo "✓ Application cache cleared\n";

// Clear route cache
shell_exec('php artisan route:clear');
echo "✓ Route cache cleared\n";

// Clear config cache
shell_exec('php artisan config:clear');
echo "✓ Config cache cleared\n";

// Clear compiled classes
shell_exec('php artisan clear-compiled');
echo "✓ Compiled classes cleared\n";

// Optimize
shell_exec('php artisan optimize:clear');
echo "✓ All optimizations cleared\n";

echo "\n✅ All caches cleared successfully!\n";
echo "The server should now use the latest templates.\n";










