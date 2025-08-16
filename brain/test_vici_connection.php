<?php
// Test connection to Vici server
echo "Testing connection to Vici server...\n";
echo "=====================================\n\n";

$viciHost = '37.27.138.222';
$viciUser = 'root';
$viciPass = 'Monster@2213@!';

// First, check our outbound IP
echo "1. Checking our outbound IP address:\n";
$ourIP = trim(file_get_contents('https://api.ipify.org'));
echo "   Our IP: " . $ourIP . "\n\n";

// Test basic connectivity (ping)
echo "2. Testing basic connectivity to $viciHost:\n";
$pingResult = exec("ping -c 1 -W 2 $viciHost 2>&1", $output, $returnCode);
if ($returnCode === 0) {
    echo "   ✓ Ping successful!\n";
} else {
    echo "   ✗ Ping failed (this might be blocked, which is normal)\n";
}

// Test SSH port (22)
echo "\n3. Testing SSH port 22:\n";
$connection = @fsockopen($viciHost, 22, $errno, $errstr, 5);
if ($connection) {
    echo "   ✓ Port 22 is reachable!\n";
    fclose($connection);
    
    // Try SSH connection with credentials
    echo "\n4. Attempting SSH connection:\n";
    echo "   (This will likely fail if our IP is not whitelisted)\n";
    
    // Create SSH connection command (non-interactive)
    $sshCommand = "timeout 5 ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 -o BatchMode=yes $viciUser@$viciHost 'echo Connected successfully'";
    
    $result = shell_exec($sshCommand . " 2>&1");
    
    if (strpos($result, 'Connected successfully') !== false) {
        echo "   ✓ SSH CONNECTION SUCCESSFUL!\n";
        echo "   Your IP is whitelisted and credentials work!\n";
    } elseif (strpos($result, 'Permission denied') !== false) {
        echo "   ✗ Permission denied - credentials might be wrong\n";
    } elseif (strpos($result, 'Connection refused') !== false) {
        echo "   ✗ Connection refused - IP not whitelisted or SSH disabled\n";
    } elseif (strpos($result, 'Connection timed out') !== false || strpos($result, 'timeout') !== false) {
        echo "   ✗ Connection timed out - IP likely not whitelisted\n";
    } else {
        echo "   ✗ Connection failed\n";
        echo "   Error: " . substr($result, 0, 200) . "\n";
    }
} else {
    echo "   ✗ Cannot reach port 22 (Error: $errstr)\n";
    echo "   Your IP ($ourIP) is likely not whitelisted\n";
}

// Test MySQL port (3306) - Vici often uses MySQL
echo "\n5. Testing MySQL port 3306:\n";
$connection = @fsockopen($viciHost, 3306, $errno, $errstr, 5);
if ($connection) {
    echo "   ✓ Port 3306 is reachable!\n";
    fclose($connection);
} else {
    echo "   ✗ Cannot reach port 3306 (might be blocked/not used)\n";
}

// Test HTTP port (80) - Vici web interface
echo "\n6. Testing HTTP port 80:\n";
$connection = @fsockopen($viciHost, 80, $errno, $errstr, 5);
if ($connection) {
    echo "   ✓ Port 80 is reachable!\n";
    fclose($connection);
} else {
    echo "   ✗ Cannot reach port 80\n";
}

echo "\n=====================================\n";
echo "SUMMARY:\n";
echo "Your outbound IP that needs whitelisting: $ourIP\n";
echo "Vici Server: $viciHost\n";
echo "\nTo whitelist this IP on the Vici server, you need to:\n";
echo "1. SSH into Vici server (from an already whitelisted IP)\n";
echo "2. Add this IP to the whitelist (usually /etc/hosts.allow or firewall rules)\n";
echo "3. Common commands:\n";
echo "   - iptables: iptables -I INPUT -s $ourIP -j ACCEPT\n";
echo "   - hosts.allow: Add 'sshd: $ourIP' to /etc/hosts.allow\n";
echo "   - firewalld: firewall-cmd --permanent --add-source=$ourIP\n";
echo "=====================================\n";


