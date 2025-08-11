<?php
// Test Vici connection from Brain server
// This will be accessed via web route

use App\Models\Lead;

Route::get('/test-vici-connection', function() {
    $result = [
        'timestamp' => now()->toISOString(),
        'tests' => []
    ];
    
    try {
        // First ensure we're whitelisted
        $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
        $ch = curl_init($whitelistUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $whitelistResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result['tests']['whitelist'] = [
            'success' => $httpCode == 200,
            'http_code' => $httpCode
        ];
        
        // Now test database connection
        $viciDb = new PDO(
            'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
            env('VICI_DB_USER', 'cron'),
            env('VICI_DB_PASS', '1234'),
            [PDO::ATTR_TIMEOUT => 5]
        );
        
        $result['tests']['db_connection'] = [
            'success' => true,
            'message' => 'Connected to Vici database'
        ];
        
        // Check campaigns
        $stmt = $viciDb->query("
            SELECT DISTINCT campaign_id, COUNT(*) as lead_count 
            FROM vicidial_list 
            WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL', 'auto2', 'autodial')
            GROUP BY campaign_id
        ");
        
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result['tests']['campaigns'] = [
            'success' => !empty($campaigns),
            'data' => $campaigns ?: 'No leads in Auto2/Autodial campaigns'
        ];
        
        // Test update capability
        $testStmt = $viciDb->prepare("
            SELECT lead_id, phone_number, vendor_lead_code
            FROM vicidial_list 
            WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL')
            AND (vendor_lead_code IS NULL OR vendor_lead_code = '')
            LIMIT 1
        ");
        
        $testStmt->execute();
        $testLead = $testStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testLead) {
            $testCode = 'TEST_' . time();
            
            $updateStmt = $viciDb->prepare("
                UPDATE vicidial_list 
                SET vendor_lead_code = :code
                WHERE lead_id = :id
            ");
            
            $updated = $updateStmt->execute([
                'code' => $testCode,
                'id' => $testLead['lead_id']
            ]);
            
            if ($updated) {
                // Revert
                $updateStmt->execute([
                    'code' => $testLead['vendor_lead_code'],
                    'id' => $testLead['lead_id']
                ]);
                
                $result['tests']['update_capability'] = [
                    'success' => true,
                    'message' => 'Successfully tested update on lead ' . $testLead['lead_id']
                ];
            }
        } else {
            $result['tests']['update_capability'] = [
                'success' => false,
                'message' => 'No test lead found'
            ];
        }
        
        // Count total leads needing update
        $countStmt = $viciDb->query("
            SELECT COUNT(*) as total
            FROM vicidial_list 
            WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL')
            AND (vendor_lead_code IS NULL OR vendor_lead_code = '')
        ");
        
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        $result['tests']['leads_needing_update'] = $count['total'];
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }
    
    return response()->json($result, 200, [], JSON_PRETTY_PRINT);
});
