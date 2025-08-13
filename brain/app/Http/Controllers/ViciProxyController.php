<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ViciProxyController extends Controller
{
    private $viciHost = '37.27.138.222';
    private $viciUser = 'root';
    private $viciPass = 'Monster@2213@!';
    
    /**
     * Execute commands on Vici server through Render (acts as proxy)
     * This ensures all connections come from Render's IP
     */
    public function executeCommand(Request $request)
    {
        // Check for authorization (add your own security here)
        $apiKey = $request->header('X-API-Key');
        if ($apiKey !== env('VICI_PROXY_KEY', 'your-secret-key-here')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $command = $request->input('command', 'echo "Test connection"');
        
        try {
            // Method 1: Using SSH2 extension (if available)
            if (function_exists('ssh2_connect')) {
                $connection = ssh2_connect($this->viciHost, 22);
                
                if (ssh2_auth_password($connection, $this->viciUser, $this->viciPass)) {
                    $stream = ssh2_exec($connection, $command);
                    stream_set_blocking($stream, true);
                    $output = stream_get_contents($stream);
                    fclose($stream);
                    
                    return response()->json([
                        'success' => true,
                        'output' => $output,
                        'render_ip' => trim(file_get_contents('https://api.ipify.org')),
                        'timestamp' => now()
                    ]);
                }
            }
            
            // Method 2: Using SSH command with sshpass
            $sshCommand = sprintf(
                'sshpass -p %s ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 %s@%s %s 2>&1',
                escapeshellarg($this->viciPass),
                escapeshellarg($this->viciUser),
                escapeshellarg($this->viciHost),
                escapeshellarg($command)
            );
            
            $output = shell_exec($sshCommand);
            
            if ($output === null) {
                // Method 3: Using expect script
                $expectScript = $this->createExpectScript($command);
                $output = shell_exec("expect -c '$expectScript' 2>&1");
            }
            
            return response()->json([
                'success' => true,
                'output' => $output,
                'render_ip' => trim(file_get_contents('https://api.ipify.org')),
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Vici proxy error', [
                'error' => $e->getMessage(),
                'command' => $command
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'render_ip' => trim(file_get_contents('https://api.ipify.org'))
            ], 500);
        }
    }
    
    /**
     * Test connection to Vici through Render
     */
    public function testConnection()
    {
        $renderIP = trim(file_get_contents('https://api.ipify.org'));
        
        // Test if we can reach the Vici server
        $connection = @fsockopen($this->viciHost, 22, $errno, $errstr, 5);
        $sshReachable = $connection ? true : false;
        if ($connection) fclose($connection);
        
        $connection = @fsockopen($this->viciHost, 80, $errno, $errstr, 5);
        $httpReachable = $connection ? true : false;
        if ($connection) fclose($connection);
        
        return response()->json([
            'render_ip' => $renderIP,
            'vici_host' => $this->viciHost,
            'ssh_port_22' => $sshReachable ? 'reachable' : 'blocked',
            'http_port_80' => $httpReachable ? 'reachable' : 'blocked',
            'message' => !$sshReachable ? 
                "Whitelist this IP on Vici server: $renderIP" : 
                "Connection test successful",
            'whitelist_commands' => [
                'iptables' => "iptables -I INPUT -s $renderIP -j ACCEPT",
                'hosts.allow' => "echo 'sshd: $renderIP' >> /etc/hosts.allow",
                'firewalld' => "firewall-cmd --permanent --add-source=$renderIP"
            ]
        ]);
    }
    
    /**
     * Create expect script for SSH automation
     */
    private function createExpectScript($command)
    {
        return sprintf('
            spawn ssh -o StrictHostKeyChecking=no %s@%s
            expect "password:"
            send "%s\r"
            expect "$ "
            send "%s\r"
            expect "$ "
            send "exit\r"
            expect eof
        ', $this->viciUser, $this->viciHost, $this->viciPass, $command);
    }
    
    /**
     * Fetch Vici call logs through Render proxy
     */
    public function fetchCallLogs(Request $request)
    {
        $dateFrom = $request->input('from', date('Y-m-d'));
        $dateTo = $request->input('to', date('Y-m-d'));
        
        // Your custom Vici query command here
        $command = "mysql -u cron -p1234 asterisk -e \"
            SELECT * FROM vicidial_log 
            WHERE call_date >= '$dateFrom 00:00:00' 
            AND call_date <= '$dateTo 23:59:59'
            LIMIT 100
        \"";
        
        return $this->executeCommand($request->merge(['command' => $command]));
    }
}
