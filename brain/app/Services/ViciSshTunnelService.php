<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;

class ViciSshTunnelService
{
    private string $sshHost = '37.27.138.222';
    private int $sshPort = 11845;
    private string $sshUser = 'root';
    private string $sshPass = 'Monster@2213@!';
    
    private string $mysqlHost = 'localhost'; // Connect to localhost through tunnel
    private string $mysqlDb = 'asterisk';
    private string $mysqlUser = 'cron';
    private string $mysqlPass = '1234';
    private int $mysqlPort = 3306;
    
    private ?PDO $viciConnection = null;
    private int $targetListId = 101;
    
    /**
     * Execute MySQL query through SSH tunnel
     */
    public function executeMysqlQuery(string $query, array $params = []): array
    {
        try {
            // Build SSH command to execute MySQL query remotely
            $mysqlCommand = sprintf(
                'mysql -u %s -p%s %s -e %s',
                escapeshellarg($this->mysqlUser),
                escapeshellarg($this->mysqlPass),
                escapeshellarg($this->mysqlDb),
                escapeshellarg($query)
            );
            
            $sshCommand = sprintf(
                'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s %s 2>&1',
                escapeshellarg($this->sshPass),
                $this->sshPort,
                escapeshellarg($this->sshUser),
                escapeshellarg($this->sshHost),
                escapeshellarg($mysqlCommand)
            );
            
            $output = shell_exec($sshCommand);
            
            if ($output === null) {
                throw new \Exception('Failed to execute SSH command');
            }
            
            return [
                'success' => true,
                'output' => $output
            ];
            
        } catch (\Exception $e) {
            Log::error('SSH MySQL execution failed', [
                'error' => $e->getMessage(),
                'query' => substr($query, 0, 200)
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update existing Vici lead with Brain Lead ID through SSH
     */
    public function updateViciLeadWithBrainId(Lead $lead): array
    {
        try {
            // Ensure we have the 13-digit external_lead_id
            $brainLeadId = $lead->external_lead_id;
            if (empty($brainLeadId) || strlen($brainLeadId) !== 13) {
                Log::warning('Lead missing 13-digit external_lead_id, generating one', [
                    'lead_id' => $lead->id,
                    'current_external_id' => $brainLeadId
                ]);
                $brainLeadId = Lead::generateExternalLeadId();
                $lead->external_lead_id = $brainLeadId;
                $lead->save();
            }
            
            // First, find the lead in Vici by phone number
            $findQuery = sprintf(
                "SELECT lead_id, vendor_lead_code, source_id FROM vicidial_list WHERE phone_number = '%s' ORDER BY lead_id DESC LIMIT 1",
                $lead->phone
            );
            
            $findResult = $this->executeMysqlQuery($findQuery);
            
            if (!$findResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to search for lead: ' . ($findResult['error'] ?? 'Unknown error'),
                    'brain_lead_id' => $brainLeadId
                ];
            }
            
            // Parse the output to check if lead exists
            $output = $findResult['output'];
            if (strpos($output, 'Empty set') !== false || empty(trim($output))) {
                Log::warning('Lead not found in Vici for update', [
                    'brain_lead_id' => $brainLeadId,
                    'phone' => $lead->phone
                ]);
                return [
                    'success' => false,
                    'message' => 'Lead not found in Vici',
                    'brain_lead_id' => $brainLeadId
                ];
            }
            
            // Parse the lead_id from output (first column of result)
            $lines = explode("\n", trim($output));
            $viciLeadId = null;
            $oldVendorCode = null;
            
            // Skip header line and get data
            if (count($lines) > 1) {
                $dataLine = $lines[1]; // First data row
                $columns = preg_split('/\s+/', $dataLine);
                $viciLeadId = $columns[0] ?? null;
                $oldVendorCode = $columns[1] ?? null;
            }
            
            if (!$viciLeadId || !is_numeric($viciLeadId)) {
                return [
                    'success' => false,
                    'message' => 'Could not parse Vici lead ID from result',
                    'brain_lead_id' => $brainLeadId,
                    'output' => substr($output, 0, 500)
                ];
            }
            
            // Update the Vici lead with Brain Lead ID
            $updateQuery = sprintf(
                "UPDATE vicidial_list SET vendor_lead_code = '%s', source_id = 'BRAIN_%s', comments = CONCAT('Brain Lead ID: %s | Updated: %s | ', COALESCE(comments, '')), modify_date = NOW() WHERE lead_id = %d",
                $brainLeadId,
                $brainLeadId,
                $brainLeadId,
                now()->format('Y-m-d H:i:s'),
                $viciLeadId
            );
            
            $updateResult = $this->executeMysqlQuery($updateQuery);
            
            if ($updateResult['success']) {
                // Update our ViciCallMetrics if it exists
                $callMetrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
                if ($callMetrics) {
                    $callMetrics->vici_lead_id = $viciLeadId;
                    $callMetrics->save();
                }
                
                Log::info('✅ Vici Lead Updated with Brain ID via SSH', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'phone' => $lead->phone,
                    'old_vendor_code' => $oldVendorCode
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Vici lead updated with Brain Lead ID via SSH',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'old_vendor_code' => $oldVendorCode
                ];
            } else {
                Log::error('Failed to update Vici lead via SSH', [
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'error' => $updateResult['error'] ?? 'Unknown error'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to update Vici lead',
                    'brain_lead_id' => $brainLeadId,
                    'vici_lead_id' => $viciLeadId,
                    'error' => $updateResult['error'] ?? null
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Vici SSH Update Failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'SSH error: ' . $e->getMessage(),
                'brain_lead_id' => $lead->external_lead_id ?? null
            ];
        }
    }
}
