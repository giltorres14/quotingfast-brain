<?php

// Fix timezone issues for leads

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing timezone for lead timestamps...\n\n";

// Replace all instances of now() with EST timezone
$patterns = [
    "'received_at' => now()," => "'received_at' => now()->setTimezone('America/New_York'),",
    "'joined_at' => now()," => "'joined_at' => now()->setTimezone('America/New_York'),",
    "'created_at' => now()," => "'created_at' => now()->setTimezone('America/New_York'),",
    "'updated_at' => now()" => "'updated_at' => now()->setTimezone('America/New_York')",
    "= now();" => "= now()->setTimezone('America/New_York');",
    "=> now()" => "=> now()->setTimezone('America/New_York')"
];

$replacements = 0;
foreach ($patterns as $search => $replace) {
    $count = 0;
    $content = str_replace($search, $replace, $content, $count);
    $replacements += $count;
    if ($count > 0) {
        echo "✓ Replaced $count instances of: $search\n";
    }
}

// Write back
file_put_contents($routesFile, $content);

echo "\n✓ Fixed $replacements timezone instances in routes\n";

// Also update the config/app.php timezone
$configFile = 'config/app.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    
    // Update timezone setting
    $configContent = preg_replace(
        "/'timezone' => '[^']*'/",
        "'timezone' => 'America/New_York'",
        $configContent
    );
    
    file_put_contents($configFile, $configContent);
    echo "✓ Updated config/app.php timezone to America/New_York (EST/EDT)\n";
}

// Create a helper function file for consistent timezone handling
$helperFile = 'app/Helpers/timezone.php';
$helperContent = '<?php

/**
 * Get current time in EST/EDT timezone
 */
function estNow() {
    return \Carbon\Carbon::now(\'America/New_York\');
}

/**
 * Convert UTC to EST/EDT
 */
function toEst($datetime) {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone(\'America/New_York\');
}

/**
 * Format date in EST/EDT
 */
function formatEst($datetime, $format = \'m/d/Y g:i A\') {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone(\'America/New_York\')->format($format);
}';

// Create helpers directory if it doesn't exist
if (!file_exists('app/Helpers')) {
    mkdir('app/Helpers', 0755, true);
}

file_put_contents($helperFile, $helperContent);
echo "✓ Created timezone helper functions\n";

// Update composer.json to autoload helpers
$composerFile = 'composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    
    if (!isset($composer['autoload']['files'])) {
        $composer['autoload']['files'] = [];
    }
    
    if (!in_array('app/Helpers/timezone.php', $composer['autoload']['files'])) {
        $composer['autoload']['files'][] = 'app/Helpers/timezone.php';
        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "✓ Updated composer.json to autoload timezone helpers\n";
        echo "\nRun 'composer dump-autoload' to load the helper functions\n";
    }
}

echo "\n=== TIMEZONE FIXES APPLIED ===\n\n";
echo "1. All lead timestamps now use America/New_York (EST/EDT)\n";
echo "2. Config timezone set to America/New_York\n";
echo "3. Created helper functions:\n";
echo "   - estNow() - Get current EST time\n";
echo "   - toEst(\$datetime) - Convert to EST\n";
echo "   - formatEst(\$datetime) - Format in EST\n";
echo "\nLeads will now show correct EST/EDT times!\n";


// Fix timezone issues for leads

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing timezone for lead timestamps...\n\n";

// Replace all instances of now() with EST timezone
$patterns = [
    "'received_at' => now()," => "'received_at' => now()->setTimezone('America/New_York'),",
    "'joined_at' => now()," => "'joined_at' => now()->setTimezone('America/New_York'),",
    "'created_at' => now()," => "'created_at' => now()->setTimezone('America/New_York'),",
    "'updated_at' => now()" => "'updated_at' => now()->setTimezone('America/New_York')",
    "= now();" => "= now()->setTimezone('America/New_York');",
    "=> now()" => "=> now()->setTimezone('America/New_York')"
];

$replacements = 0;
foreach ($patterns as $search => $replace) {
    $count = 0;
    $content = str_replace($search, $replace, $content, $count);
    $replacements += $count;
    if ($count > 0) {
        echo "✓ Replaced $count instances of: $search\n";
    }
}

// Write back
file_put_contents($routesFile, $content);

echo "\n✓ Fixed $replacements timezone instances in routes\n";

// Also update the config/app.php timezone
$configFile = 'config/app.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    
    // Update timezone setting
    $configContent = preg_replace(
        "/'timezone' => '[^']*'/",
        "'timezone' => 'America/New_York'",
        $configContent
    );
    
    file_put_contents($configFile, $configContent);
    echo "✓ Updated config/app.php timezone to America/New_York (EST/EDT)\n";
}

// Create a helper function file for consistent timezone handling
$helperFile = 'app/Helpers/timezone.php';
$helperContent = '<?php

/**
 * Get current time in EST/EDT timezone
 */
function estNow() {
    return \Carbon\Carbon::now(\'America/New_York\');
}

/**
 * Convert UTC to EST/EDT
 */
function toEst($datetime) {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone(\'America/New_York\');
}

/**
 * Format date in EST/EDT
 */
function formatEst($datetime, $format = \'m/d/Y g:i A\') {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone(\'America/New_York\')->format($format);
}';

// Create helpers directory if it doesn't exist
if (!file_exists('app/Helpers')) {
    mkdir('app/Helpers', 0755, true);
}

file_put_contents($helperFile, $helperContent);
echo "✓ Created timezone helper functions\n";

// Update composer.json to autoload helpers
$composerFile = 'composer.json';
if (file_exists($composerFile)) {
    $composer = json_decode(file_get_contents($composerFile), true);
    
    if (!isset($composer['autoload']['files'])) {
        $composer['autoload']['files'] = [];
    }
    
    if (!in_array('app/Helpers/timezone.php', $composer['autoload']['files'])) {
        $composer['autoload']['files'][] = 'app/Helpers/timezone.php';
        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "✓ Updated composer.json to autoload timezone helpers\n";
        echo "\nRun 'composer dump-autoload' to load the helper functions\n";
    }
}

echo "\n=== TIMEZONE FIXES APPLIED ===\n\n";
echo "1. All lead timestamps now use America/New_York (EST/EDT)\n";
echo "2. Config timezone set to America/New_York\n";
echo "3. Created helper functions:\n";
echo "   - estNow() - Get current EST time\n";
echo "   - toEst(\$datetime) - Convert to EST\n";
echo "   - formatEst(\$datetime) - Format in EST\n";
echo "\nLeads will now show correct EST/EDT times!\n";







