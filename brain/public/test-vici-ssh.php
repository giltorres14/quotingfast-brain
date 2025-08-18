<?php
/**
 * Direct test of Vici SSH connection from Render
 */

header('Content-Type: application/json');

$viciHost = '37.27.138.222';
$viciUser = 'root';
$viciPass = 'Monster@2213@!';

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'render_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'vici_host' => $viciHost
];

// Get our actual outbound IP
$result['outbound_ip'] = trim(file_get_contents('https://api.ipify.org'));

// Test SSH port
$fp = @fsockopen($viciHost, 22, $errno, $errstr, 5);
if ($fp) {
    $result['ssh_port_22'] = 'open';
    fclose($fp);
    
    // Try actual SSH command
    $testCmd = "ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 -o BatchMode=yes {$viciUser}@{$viciHost} exit 2>&1";
    $output = shell_exec($testCmd);
    
    if (strpos($output, 'Permission denied') !== false) {
        $result['ssh_status'] = 'reachable_needs_auth';
        $result['ready'] = true;
        
        // Try with sshpass if available
        $sshpassTest = shell_exec("which sshpass");
        if ($sshpassTest) {
            $result['sshpass_available'] = true;
            
            // Try actual connection
            $connectCmd = sprintf(
                'sshpass -p %s ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 %s@%s "echo CONNECTED; hostname; date" 2>&1',
                escapeshellarg($viciPass),
                $viciUser,
                $viciHost
            );
            $connectOutput = shell_exec($connectCmd);
            
            if (strpos($connectOutput, 'CONNECTED') !== false) {
                $result['ssh_auth'] = 'success';
                $result['connection_test'] = $connectOutput;
                $result['message'] = '✅ READY! SSH connection successful from IP: ' . $result['outbound_ip'];
            } else {
                $result['ssh_auth'] = 'failed';
                $result['auth_error'] = substr($connectOutput, 0, 200);
            }
        } else {
            $result['sshpass_available'] = false;
            $result['message'] = 'SSH port open but sshpass not installed on Render';
        }
    } else {
        $result['ssh_status'] = 'connection_failed';
        $result['ssh_error'] = substr($output, 0, 200);
        $result['ready'] = false;
    }
} else {
    $result['ssh_port_22'] = 'blocked';
    $result['error'] = "{$errstr} ({$errno})";
    $result['ready'] = false;
    $result['message'] = '❌ SSH port 22 blocked. IP ' . $result['outbound_ip'] . ' needs whitelisting';
}

// Test HTTP port
$fp = @fsockopen($viciHost, 80, $errno, $errstr, 5);
$result['http_port_80'] = $fp ? 'open' : 'blocked';
if ($fp) fclose($fp);

echo json_encode($result, JSON_PRETTY_PRINT);


/**
 * Direct test of Vici SSH connection from Render
 */

header('Content-Type: application/json');

$viciHost = '37.27.138.222';
$viciUser = 'root';
$viciPass = 'Monster@2213@!';

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'render_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'vici_host' => $viciHost
];

// Get our actual outbound IP
$result['outbound_ip'] = trim(file_get_contents('https://api.ipify.org'));

// Test SSH port
$fp = @fsockopen($viciHost, 22, $errno, $errstr, 5);
if ($fp) {
    $result['ssh_port_22'] = 'open';
    fclose($fp);
    
    // Try actual SSH command
    $testCmd = "ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 -o BatchMode=yes {$viciUser}@{$viciHost} exit 2>&1";
    $output = shell_exec($testCmd);
    
    if (strpos($output, 'Permission denied') !== false) {
        $result['ssh_status'] = 'reachable_needs_auth';
        $result['ready'] = true;
        
        // Try with sshpass if available
        $sshpassTest = shell_exec("which sshpass");
        if ($sshpassTest) {
            $result['sshpass_available'] = true;
            
            // Try actual connection
            $connectCmd = sprintf(
                'sshpass -p %s ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 %s@%s "echo CONNECTED; hostname; date" 2>&1',
                escapeshellarg($viciPass),
                $viciUser,
                $viciHost
            );
            $connectOutput = shell_exec($connectCmd);
            
            if (strpos($connectOutput, 'CONNECTED') !== false) {
                $result['ssh_auth'] = 'success';
                $result['connection_test'] = $connectOutput;
                $result['message'] = '✅ READY! SSH connection successful from IP: ' . $result['outbound_ip'];
            } else {
                $result['ssh_auth'] = 'failed';
                $result['auth_error'] = substr($connectOutput, 0, 200);
            }
        } else {
            $result['sshpass_available'] = false;
            $result['message'] = 'SSH port open but sshpass not installed on Render';
        }
    } else {
        $result['ssh_status'] = 'connection_failed';
        $result['ssh_error'] = substr($output, 0, 200);
        $result['ready'] = false;
    }
} else {
    $result['ssh_port_22'] = 'blocked';
    $result['error'] = "{$errstr} ({$errno})";
    $result['ready'] = false;
    $result['message'] = '❌ SSH port 22 blocked. IP ' . $result['outbound_ip'] . ' needs whitelisting';
}

// Test HTTP port
$fp = @fsockopen($viciHost, 80, $errno, $errstr, 5);
$result['http_port_80'] = $fp ? 'open' : 'blocked';
if ($fp) fclose($fp);

echo json_encode($result, JSON_PRETTY_PRINT);


