<?php
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current directory: " . getcwd() . "<br>";
echo "Files in current directory:<br>";
$files = scandir('.');
foreach($files as $file) {
    if($file != '.' && $file != '..') {
        echo "- $file<br>";
    }
}

// Check if Laravel files exist
if(file_exists('artisan')) {
    echo "<br>✅ Laravel artisan file found<br>";
} else {
    echo "<br>❌ Laravel artisan file NOT found<br>";
}

if(file_exists('public/index.php')) {
    echo "✅ Laravel public/index.php found<br>";
} else {
    echo "❌ Laravel public/index.php NOT found<br>";
}

if(file_exists('vendor/autoload.php')) {
    echo "✅ Composer vendor/autoload.php found<br>";
} else {
    echo "❌ Composer vendor/autoload.php NOT found<br>";
}

if(file_exists('bootstrap/app.php')) {
    echo "✅ Laravel bootstrap/app.php found<br>";
} else {
    echo "❌ Laravel bootstrap/app.php NOT found<br>";
}

// Try to show environment variables
echo "<br>Environment variables:<br>";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "<br>";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'not set') . "<br>";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'not set') . "<br>";
?>