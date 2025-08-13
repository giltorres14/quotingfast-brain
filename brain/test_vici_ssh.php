<?php
/**
 * Test SSH connection to Vici server
 */

$viciHost = '37.27.138.222';
$viciUser = 'root';
$viciPass = 'Monster@2213@!';

echo "Testing SSH connection to Vici server...\n";
echo "Host: {$viciHost}\n";
echo "User: {$viciUser}\n\n";

// Test basic connectivity
echo "1. Testing network connectivity (ping)...\n";
$ping = shell_exec("ping -c 1 -W 2 {$viciHost} 2>&1");
if (strpos($ping, '1 packets transmitted, 1 received') !== false || strpos($ping, '1 received') !== false) {
    echo "✅ Ping successful\n\n";
} else {
    echo "❌ Ping failed or timed out\n\n";
}

// Test SSH port
echo "2. Testing SSH port 22...\n";
$connection = @fsockopen($viciHost, 22, $errno, $errstr, 5);
if ($connection) {
    echo "✅ Port 22 is open\n\n";
    fclose($connection);
} else {
    echo "❌ Port 22 is blocked: {$errstr} ({$errno})\n\n";
}

// Try SSH connection with sshpass
echo "3. Testing SSH authentication...\n";
$testCommand = sprintf(
    'sshpass -p %s ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 %s@%s "echo SUCCESS; hostname; date" 2>&1',
    escapeshellarg($viciPass),
    escapeshellarg($viciUser),
    escapeshellarg($viciHost)
);

$output = shell_exec($testCommand);
echo "SSH Output:\n{$output}\n";

if (strpos($output, 'SUCCESS') !== false) {
    echo "✅ SSH connection successful!\n\n";
    
    // Try to check if our export script exists
    echo "4. Checking for export script on Vici...\n";
    $checkScript = sprintf(
        'sshpass -p %s ssh -o StrictHostKeyChecking=no %s@%s "ls -la /home/vici_export_script.sh 2>&1" 2>&1',
        escapeshellarg($viciPass),
        escapeshellarg($viciUser),
        escapeshellarg($viciHost)
    );
    $scriptCheck = shell_exec($checkScript);
    echo "Script check:\n{$scriptCheck}\n";
    
    if (strpos($scriptCheck, 'No such file') !== false) {
        echo "Script not found. Uploading...\n";
        
        // Upload the script
        $uploadCommand = sprintf(
            'sshpass -p %s scp -o StrictHostKeyChecking=no vici_export_script.sh %s@%s:/home/vici_export_script.sh 2>&1',
            escapeshellarg($viciPass),
            escapeshellarg($viciUser),
            escapeshellarg($viciHost)
        );
        $uploadResult = shell_exec($uploadCommand);
        echo "Upload result: {$uploadResult}\n";
        
        // Make it executable
        $chmodCommand = sprintf(
            'sshpass -p %s ssh -o StrictHostKeyChecking=no %s@%s "chmod +x /home/vici_export_script.sh" 2>&1',
            escapeshellarg($viciPass),
            escapeshellarg($viciUser),
            escapeshellarg($viciHost)
        );
        shell_exec($chmodCommand);
        echo "✅ Script uploaded and made executable\n";
    }
    
} else {
    echo "❌ SSH connection failed\n";
    echo "This could mean:\n";
    echo "- The IPs are not fully whitelisted yet\n";
    echo "- Only certain IPs from the list are whitelisted\n";
    echo "- Firewall rules haven't propagated yet\n";
}
