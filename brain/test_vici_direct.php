<?php

echo "=== TESTING DIRECT VICI CONNECTION ===\n\n";
echo "Note: This requires sshpass to be installed locally.\n";
echo "If it fails, we'll use the deployed proxy instead.\n\n";

// SSH details
$host = '37.27.138.222';
$port = 11845;
$user = 'root';
$pass = 'Monster@2213@!';

// Test SSH connection
echo "1. Testing SSH connection on port $port...\n";
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection) {
    echo "✅ Port $port is open!\n\n";
    fclose($connection);
} else {
    echo "❌ Cannot connect to port $port: $errstr\n\n";
    exit(1);
}

// Try to execute a command via SSH
echo "2. Attempting to execute command via SSH...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT COUNT(*) as total_lists FROM vicidial_lists\"";
$sshCommand = sprintf(
    'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o ConnectTimeout=10 %s@%s %s 2>&1',
    escapeshellarg($pass),
    $port,
    escapeshellarg($user),
    escapeshellarg($host),
    escapeshellarg($command)
);

$output = shell_exec($sshCommand);

if ($output === null || strpos($output, 'sshpass: command not found') !== false) {
    echo "❌ sshpass not installed locally. Use the deployed proxy instead:\n";
    echo "   https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute\n\n";
} elseif (strpos($output, 'Permission denied') !== false) {
    echo "❌ SSH authentication failed\n";
    echo "Output: $output\n\n";
} elseif (strpos($output, 'total_lists') !== false) {
    echo "✅ SSH connection successful! Query result:\n";
    echo $output . "\n\n";
    
    // Now check Autodial campaign lists
    echo "3. Checking Autodial campaign lists...\n";
    $command = "mysql -u cron -p1234 asterisk -e \"SELECT l.list_id, l.list_name, COUNT(vl.lead_id) as lead_count FROM vicidial_lists l LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id WHERE l.campaign_id = 'Autodial' OR l.list_id IN (SELECT list_id FROM vicidial_campaign_lists WHERE campaign_id = 'Autodial') GROUP BY l.list_id, l.list_name\"";
    $sshCommand = sprintf(
        'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s %s 2>&1',
        escapeshellarg($pass),
        $port,
        escapeshellarg($user),
        escapeshellarg($host),
        escapeshellarg($command)
    );
    $output = shell_exec($sshCommand);
    echo $output . "\n";
} else {
    echo "⚠️ Unexpected output:\n";
    echo $output . "\n\n";
}

echo "=== TEST COMPLETE ===\n";


