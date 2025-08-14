<?php

// This script runs on the deployed server with proper SSH access

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get parameters
$testMode = isset($_GET['test']) || isset($_GET['mode']) && $_GET['mode'] === 'test';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

header('Content-Type: application/json');

// SSH connection details
$sshHost = '37.27.138.222';
$sshPort = 11845;
$sshUser = 'root';
$sshPass = 'Monster@2213@!';
$mysqlUser = 'Superman';
$mysqlPass = '8ZDWGAAQRD';
$mysqlDb = 'asterisk';

// Function to execute MySQL query through SSH
function executeMysqlQuery($query) {
    global $sshHost, $sshPort, $sshUser, $sshPass, $mysqlUser, $mysqlPass, $mysqlDb;
    
    $mysqlCommand = sprintf(
        'mysql -h localhost -u %s -p%s %s -e %s 2>&1',
        escapeshellarg($mysqlUser),
        escapeshellarg($mysqlPass),
        escapeshellarg($mysqlDb),
        escapeshellarg($query)
    );
    
    $sshCommand = sprintf(
        'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
        escapeshellarg($sshPass),
        $sshPort,
        escapeshellarg($sshUser),
        escapeshellarg($sshHost),
        escapeshellarg($mysqlCommand)
    );
    
    $output = shell_exec($sshCommand);
    return $output;
}

try {
    // Get leads from Brain
    $query = Lead::whereNotNull('phone')
        ->where('phone', '!=', '')
        ->whereNotNull('external_lead_id')
        ->whereRaw("LENGTH(external_lead_id) = 13")
        ->whereRaw("external_lead_id ~ '^[0-9]+$'");
    
    if ($testMode) {
        $query->limit($limit);
    }
    
    $leads = $query->get();
    
    $results = [
        'mode' => $testMode ? 'test' : 'production',
        'total_leads' => $leads->count(),
        'limit' => $testMode ? $limit : 'unlimited',
        'updated' => 0,
        'already_updated' => 0,
        'not_found' => 0,
        'errors' => 0,
        'details' => []
    ];
    
    foreach ($leads as $lead) {
        $leadResult = [
            'brain_id' => $lead->id,
            'external_id' => $lead->external_lead_id,
            'phone' => $lead->phone,
            'name' => $lead->first_name . ' ' . $lead->last_name
        ];
        
        // Check if lead exists in Vici
        $checkQuery = sprintf(
            "SELECT lead_id, vendor_lead_code, first_name, last_name FROM vicidial_list WHERE phone_number = '%s' ORDER BY lead_id DESC LIMIT 1",
            $lead->phone
        );
        
        $output = executeMysqlQuery($checkQuery);
        
        if (empty($output) || strpos($output, 'lead_id') === false) {
            $leadResult['status'] = 'not_found';
            $leadResult['message'] = 'Lead not found in Vici';
            $results['not_found']++;
        } else {
            // Parse the result
            $lines = explode("\n", $output);
            if (count($lines) >= 2) {
                $dataLine = $lines[1];
                $columns = preg_split('/\t/', $dataLine);
                
                if (count($columns) >= 4) {
                    $viciLeadId = trim($columns[0]);
                    $currentVendorCode = trim($columns[1]);
                    
                    $leadResult['vici_lead_id'] = $viciLeadId;
                    $leadResult['current_vendor_code'] = $currentVendorCode;
                    
                    if ($currentVendorCode === $lead->external_lead_id) {
                        $leadResult['status'] = 'already_updated';
                        $leadResult['message'] = 'Already has Brain ID';
                        $results['already_updated']++;
                    } else {
                        // Update the lead
                        $updateQuery = sprintf(
                            "UPDATE vicidial_list SET vendor_lead_code = '%s', source_id = 'BRAIN_%s', modify_date = NOW() WHERE lead_id = %d",
                            $lead->external_lead_id,
                            $lead->external_lead_id,
                            $viciLeadId
                        );
                        
                        $updateOutput = executeMysqlQuery($updateQuery);
                        
                        if (strpos($updateOutput, 'ERROR') === false) {
                            $leadResult['status'] = 'updated';
                            $leadResult['message'] = 'Successfully updated with Brain ID';
                            $results['updated']++;
                            
                            // Log the update
                            Log::info('Updated Vici lead with Brain ID', [
                                'brain_lead_id' => $lead->id,
                                'external_lead_id' => $lead->external_lead_id,
                                'vici_lead_id' => $viciLeadId,
                                'phone' => $lead->phone
                            ]);
                        } else {
                            $leadResult['status'] = 'error';
                            $leadResult['message'] = 'Failed to update: ' . $updateOutput;
                            $results['errors']++;
                        }
                    }
                } else {
                    $leadResult['status'] = 'error';
                    $leadResult['message'] = 'Could not parse Vici response';
                    $results['errors']++;
                }
            } else {
                $leadResult['status'] = 'not_found';
                $leadResult['message'] = 'No data returned from Vici';
                $results['not_found']++;
            }
        }
        
        $results['details'][] = $leadResult;
    }
    
    // Add verification sample if any were updated
    if ($results['updated'] > 0) {
        $verifyQuery = "SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code FROM vicidial_list WHERE vendor_lead_code LIKE '17%' AND LENGTH(vendor_lead_code) = 13 ORDER BY lead_id DESC LIMIT 5";
        $verifyOutput = executeMysqlQuery($verifyQuery);
        $results['verification_sample'] = $verifyOutput;
    }
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
