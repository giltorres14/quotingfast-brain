<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ViciProxyController extends Controller
{
    private $viciHost = '37.27.138.222';
    private $viciUser = 'root';
    private $viciPass = 'Monster@2213@!';
    private $viciSshPort = 11845; // Custom SSH port - NOT DEFAULT 22!
    
    /**
     * Execute commands on Vici server through Render (acts as proxy)
     * This ensures all connections come from Render's IP
     */
    public function executeCommand(Request $request)
    {
        // Authorization temporarily disabled for testing
        // TODO: Re-enable for production
        // $apiKey = $request->header('X-API-Key');
        // if ($apiKey !== env('VICI_PROXY_KEY', 'your-secret-key-here')) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
        
        $command = $request->input('command', 'echo "Test connection"');
        
        try {
            // Method 1: Using SSH2 extension (if available)
            if (function_exists('ssh2_connect')) {
                $connection = ssh2_connect($this->viciHost, $this->viciSshPort);
                
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
                'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o ConnectTimeout=10 %s@%s %s 2>&1',
                escapeshellarg($this->viciPass),
                $this->viciSshPort,
                escapeshellarg($this->viciUser),
                escapeshellarg($this->viciHost),
                escapeshellarg($command)
            );
            
            $output = shell_exec($sshCommand);
            
            if ($output === null) {
                // Method 3: Using expect script
                $expectScript = $this->createExpectScript($command);
                $output = shell_exec($expectScript);
            }
            
            return response()->json([
                'success' => true,
                'output' => $output,
                'render_ip' => trim(file_get_contents('https://api.ipify.org')),
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Vici proxy execution failed', [
                'error' => $e->getMessage(),
                'command' => $command
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test connection to Vici server
     */
    public function testConnection(Request $request)
    {
        $renderIp = trim(file_get_contents('https://api.ipify.org'));
        
        // Test SSH port connectivity on port 11845
        $sshPort = @fsockopen($this->viciHost, $this->viciSshPort, $errno, $errstr, 5);
        $sshStatus = $sshPort ? 'open' : 'blocked';
        if ($sshPort) fclose($sshPort);
        
        // Test HTTP port (80)
        $httpPort = @fsockopen($this->viciHost, 80, $errno, $errstr, 5);
        $httpStatus = $httpPort ? 'open' : 'blocked';
        if ($httpPort) fclose($httpPort);
        
        // Try to test SSH connection if port is open
        $sshTestDetail = 'Not tested';
        if ($sshStatus === 'open' && function_exists('shell_exec')) {
            $testCmd = sprintf(
                'timeout 5 ssh -p %d -o StrictHostKeyChecking=no -o ConnectTimeout=5 -o PasswordAuthentication=no %s@%s exit 2>&1',
                $this->viciSshPort,
                $this->viciUser,
                $this->viciHost
            );
            $sshTest = shell_exec($testCmd);
            if (strpos($sshTest, 'Permission denied') !== false) {
                $sshTestDetail = 'SSH reachable (auth required)';
            } elseif (strpos($sshTest, 'Connection refused') !== false) {
                $sshTestDetail = 'Port blocked or service down';
            } else {
                $sshTestDetail = substr($sshTest, 0, 100);
            }
        }
        
        return response()->json([
            'render_ip' => $renderIp,
            'vici_host' => $this->viciHost,
            'ssh_port_' . $this->viciSshPort => $sshStatus,
            'ssh_test_detail' => $sshTestDetail,
            'http_port_80' => $httpStatus,
            'message' => $sshStatus === 'open' 
                ? 'SSH port ' . $this->viciSshPort . ' is open - ready to connect!' 
                : 'Whitelist this IP on Vici server: ' . $renderIp,
            'whitelist_commands' => [
                'iptables' => 'iptables -I INPUT -s ' . $renderIp . ' -j ACCEPT',
                'hosts.allow' => 'echo \'sshd: ' . $renderIp . '\' >> /etc/hosts.allow',
                'firewalld' => 'firewall-cmd --permanent --add-source=' . $renderIp
            ],
            'timestamp' => now()
        ]);
    }
    
    /**
     * Create expect script for SSH connection
     */
    private function createExpectScript($command)
    {
        return sprintf('
            spawn ssh -p %d -o StrictHostKeyChecking=no %s@%s
            expect "password:"
            send "%s\r"
            expect "$ "
            send "%s\r"
            expect "$ "
            send "exit\r"
            expect eof
        ',
            $this->viciSshPort,
            $this->viciUser,
            $this->viciHost,
            $this->viciPass,
            $command
        );
    }
    
    /**
     * Fetch call logs from Vici
     */
    public function fetchCallLogs(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-7 days')));
        $endDate = $request->input('end_date', date('Y-m-d'));
        
        $command = sprintf(
            'mysql -u %s -p%s asterisk -e "SELECT * FROM vicidial_log WHERE call_date BETWEEN \'%s 00:00:00\' AND \'%s 23:59:59\' LIMIT 100" --batch',
            'cron',
            '1234',
            $startDate,
            $endDate
        );
        
        return $this->executeCommand(new Request(['command' => $command]));
    }
    
    /**
     * Run the Vici export script
     */
    public function runExportScript(Request $request)
    {
        $dbName = $request->input('db_name', 'Colh42mUsWs40znH');
        
        try {
            // First, upload the export script to Vici server
            $scriptContent = file_get_contents(base_path('vici_export_script.sh'));
            $uploadCommand = sprintf(
                'echo %s | sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s "cat > /tmp/vici_export.sh && chmod +x /tmp/vici_export.sh"',
                escapeshellarg($scriptContent),
                escapeshellarg($this->viciPass),
                $this->viciSshPort,
                escapeshellarg($this->viciUser),
                escapeshellarg($this->viciHost)
            );
            
            shell_exec($uploadCommand);
            
            // Run the export script
            $runCommand = sprintf(
                'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s "bash /tmp/vici_export.sh %s"',
                escapeshellarg($this->viciPass),
                $this->viciSshPort,
                escapeshellarg($this->viciUser),
                escapeshellarg($this->viciHost),
                escapeshellarg($dbName)
            );
            
            $output = shell_exec($runCommand);
            
            // Parse output to get CSV filename
            if (preg_match('/CSV file with header generated at: (.+)/', $output, $matches)) {
                $remoteFile = trim($matches[1]);
                $filename = basename($remoteFile);
                $localPath = storage_path("app/vici_logs/{$filename}");
                
                // Create directory if it doesn't exist
                if (!file_exists(dirname($localPath))) {
                    mkdir(dirname($localPath), 0755, true);
                }
                
                // Download the CSV file (note: scp uses -P for port, not -p)
                $downloadCommand = sprintf(
                    'sshpass -p %s scp -P %d -o StrictHostKeyChecking=no %s@%s:%s %s',
                    escapeshellarg($this->viciPass),
                    $this->viciSshPort,
                    escapeshellarg($this->viciUser),
                    escapeshellarg($this->viciHost),
                    escapeshellarg($remoteFile),
                    escapeshellarg($localPath)
                );
                
                shell_exec($downloadCommand);
                
                if (file_exists($localPath)) {
                    // Process the CSV using our artisan command
                    \Artisan::call('vici:process-csv', ['file' => $localPath]);
                    $artisanOutput = \Artisan::output();
                    
                    // Clean up remote file
                    $cleanupCommand = sprintf(
                        'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s "rm -f %s"',
                        escapeshellarg($this->viciPass),
                        $this->viciSshPort,
                        escapeshellarg($this->viciUser),
                        escapeshellarg($this->viciHost),
                        escapeshellarg($remoteFile)
                    );
                    
                    shell_exec($cleanupCommand);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Export script executed and CSV processed',
                        'filename' => $filename,
                        'script_output' => $output,
                        'process_output' => $artisanOutput,
                        'render_ip' => trim(file_get_contents('https://api.ipify.org'))
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Script executed but CSV not found or downloaded',
                'output' => $output,
                'render_ip' => trim(file_get_contents('https://api.ipify.org'))
            ]);
            
        } catch (\Exception $e) {
            Log::error('Vici export script failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
