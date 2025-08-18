<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ViciUpdateController extends Controller
{
    public function executeUpdate(Request $request)
    {
        set_time_limit(600); // 10 minutes
        
        $updates = $request->input('updates', []);
        $results = [
            'success' => 0,
            'failed' => 0,
            'rows_updated' => 0,
            'errors' => []
        ];
        
        foreach ($updates as $idx => $sql) {
            // Execute via local proxy (which connects to Vici via SSH)
            $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($sql) . " 2>&1"
            ]);
            
            if ($response->successful()) {
                $output = $response->json()['output'] ?? '';
                if (strpos($output, 'ERROR') === false) {
                    $results['success']++;
                    // Try to extract rows affected
                    if (preg_match('/(\d+) row/', $output, $matches)) {
                        $results['rows_updated'] += intval($matches[1]);
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Update $idx: " . substr($output, 0, 100);
                }
            } else {
                $results['failed']++;
                $results['errors'][] = "Update $idx: HTTP request failed";
            }
        }
        
        // Get final count
        $checkSQL = "SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015,6016,6017,6018,6019,6020,6021,6022,6023,6024,6025,8001,8002,8003,8004,8005,8006,8007,8008,10006,10007,10008,10009,10010,10011,7010,7011,7012,60010,60020);";
        
        $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
            'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkSQL) . " 2>&1"
        ]);
        
        if ($response->successful()) {
            $output = $response->json()['output'] ?? '';
            $results['final_count'] = $output;
        }
        
        return response()->json($results);
    }
}




