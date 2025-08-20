<?php
// Script to check the Brain server's public IP address

echo "Checking Brain server IP addresses...\n";
echo "=====================================\n\n";

// Method 1: Using external service
$ip1 = file_get_contents('https://api.ipify.org');
echo "Public IP (via ipify.org): " . $ip1 . "\n";

// Method 2: Using alternative service
$ip2 = file_get_contents('https://ifconfig.me/ip');
echo "Public IP (via ifconfig.me): " . trim($ip2) . "\n";

// Method 3: Using another service for verification
$ch = curl_init('https://checkip.amazonaws.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ip3 = curl_exec($ch);
curl_close($ch);
echo "Public IP (via AWS): " . trim($ip3) . "\n";

echo "\n=====================================\n";
echo "The Brain server on Render.com is using IP: " . $ip1 . "\n";
echo "This IP needs to be whitelisted on the Vici server.\n";

// Also show server info
echo "\nServer Information:\n";
echo "Hostname: " . gethostname() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "PHP Version: " . phpversion() . "\n";


// Script to check the Brain server's public IP address

echo "Checking Brain server IP addresses...\n";
echo "=====================================\n\n";

// Method 1: Using external service
$ip1 = file_get_contents('https://api.ipify.org');
echo "Public IP (via ipify.org): " . $ip1 . "\n";

// Method 2: Using alternative service
$ip2 = file_get_contents('https://ifconfig.me/ip');
echo "Public IP (via ifconfig.me): " . trim($ip2) . "\n";

// Method 3: Using another service for verification
$ch = curl_init('https://checkip.amazonaws.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ip3 = curl_exec($ch);
curl_close($ch);
echo "Public IP (via AWS): " . trim($ip3) . "\n";

echo "\n=====================================\n";
echo "The Brain server on Render.com is using IP: " . $ip1 . "\n";
echo "This IP needs to be whitelisted on the Vici server.\n";

// Also show server info
echo "\nServer Information:\n";
echo "Hostname: " . gethostname() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "PHP Version: " . phpversion() . "\n";








