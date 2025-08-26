<?php
// SQL UPDATE via SSH from Render server (whitelisted IP)
// Updates vendor_lead_code for lists 6018-6026 using Brain phone mapping
header('Content-Type: application/json');

function norm10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $limit = isset($_GET['limit']) ? max(1, min(5000, (int)$_GET['limit'])) : 100;
    $commit = isset($_GET['commit']) ? (int)$_GET['commit'] === 1 : false;
    
    // Build Brain phone map
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $phoneMap = [];
    $stmt = $pg->query("SELECT external_lead_id, phone FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND phone IS NOT NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p10 = norm10($row['phone'] ?? '');
        if ($p10 !== '') { $phoneMap[$p10] = $row['external_lead_id']; }
    }
    
    // SSH to Vici (from Render's whitelisted IP)
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;
    
    $execSsh = function($cmd) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort) {
        $mysql = sprintf('mysql -h localhost -P %d -u %s -p%s %s -e %s 2>&1',
            $mysqlPort, escapeshellarg($mysqlUser), escapeshellarg($mysqlPass), escapeshellarg($mysqlDb), escapeshellarg($cmd));
        $ssh = sprintf('sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql));
        return shell_exec($ssh);
    };
    
    // Build UPDATE SQL in chunks
    $chunks = array_chunk($phoneMap, 100, true);
    $totalUpdated = 0;
    $samples = [];
    
    foreach ($chunks as $chunk) {
        if ($totalUpdated >= $limit) break;
        
        $cases = [];
        $phones = [];
        foreach ($chunk as $phone => $eid) {
            $cases[] = sprintf("WHEN '%s' THEN '%s'", addslashes($phone), addslashes($eid));
            $phones[] = "'" . addslashes($phone) . "'";
        }
        
        $sql = sprintf(
            "UPDATE vicidial_list SET vendor_lead_code = CASE RIGHT(phone_number,10) %s END WHERE list_id IN (6018,6019,6020,6021,6022,6023,6024,6025,6026) AND RIGHT(phone_number,10) IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='');",
            implode(' ', $cases),
            implode(',', $phones)
        );
        
        if ($commit) {
            $result = $execSsh($sql . " SELECT ROW_COUNT();");
            preg_match('/(\d+)/', $result, $matches);
            $updated = isset($matches[1]) ? (int)$matches[1] : 0;
            $totalUpdated += $updated;
            if (count($samples) < 3) {
                $samples[] = ['chunk_size' => count($chunk), 'updated' => $updated];
            }
        } else {
            // Dry run - just count matches
            $countSql = sprintf(
                "SELECT COUNT(*) FROM vicidial_list WHERE list_id IN (6018,6019,6020,6021,6022,6023,6024,6025,6026) AND RIGHT(phone_number,10) IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='');",
                implode(',', $phones)
            );
            $result = $execSsh($countSql);
            preg_match('/(\d+)/', $result, $matches);
            $matches = isset($matches[1]) ? (int)$matches[1] : 0;
            $totalUpdated += $matches;
            if (count($samples) < 3) {
                $samples[] = ['chunk_size' => count($chunk), 'would_update' => $matches];
            }
        }
    }
    
    echo json_encode([
        'brain_phone_map_size' => count($phoneMap),
        'limit' => $limit,
        'commit' => $commit,
        'total_updated' => $totalUpdated,
        'samples' => $samples
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}


