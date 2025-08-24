<?php
// Batch  API updater for Vici using UploadAPI credentials
// Pulls candidates from Vici (vendor_lead_code empty) for given lists,
// matches to Brain by phone (last 10 digits), then calls Vici update_lead
// Endpoint: /vici_api_batch_update.php?lists=6018,6019&limit=75

header('Content-Type: application/json');

function normalize10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 75;

    $listIds = array_values(array_filter(array_map('intval', explode(',', $listsParam)), fn($v)=>$v>0));
    if (empty($listIds)) { throw new Exception('No valid list ids'); }
    $listCsv = implode(',', $listIds);

    // Brain DB (Postgres)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $phoneToExternal = [];
    $stmt = $pg->query("SELECT external_lead_id, phone FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND phone IS NOT NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p10 = normalize10($row['phone'] ?? '');
        if ($p10 !== '') { $phoneToExternal[$p10] = $row['external_lead_id']; }
    }

    // Vici (MySQL over SSH) - read-only selects
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb   = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;
    $execMysql = function (string $query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort): string {
        $mysql = sprintf(
            'mysql -h localhost -P %d -u %s -p%s %s -e %s 2>&1',
            $mysqlPort,
            escapeshellarg($mysqlUser),
            escapeshellarg($mysqlPass),
            escapeshellarg($mysqlDb),
            escapeshellarg($query)
        );
        $ssh = sprintf(
            'sshpass -p %s ssh -T -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql)
        );
        return (string)shell_exec($ssh);
    };

    // Pull candidate phones from Vici with empty vendor_lead_code
    $q = sprintf("SELECT lead_id, list_id, phone_number FROM vicidial_list WHERE list_id IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='') LIMIT %d",
        $listCsv, $limit*5 /* fetch extra to account for non matches */);
    $out = $execMysql($q);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
    $candidates = [];
    for ($i=1; $i<count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) < 3) continue;
        $candidates[] = [ 'lead_id'=>(int)$cols[0], 'list_id'=>(int)$cols[1], 'phone'=>$cols[2] ];
    }

    // Prepare updates (matched in Brain)
    $queue = [];
    foreach ($candidates as $c) {
        if (count($queue) >= $limit) break;
        $p10 = normalize10($c['phone']);
        if ($p10 === '') continue;
        if (!isset($phoneToExternal[$p10])) continue;
        $queue[] = [ 'phone10'=>$p10, 'external_id'=>$phoneToExternal[$p10], 'list_id'=>$c['list_id'] ];
    }

    // Call Vici API for each queue item
    $apiUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
    $apiUser = 'UploadAPI';
    $apiPass = 'ZL8aY2MuQM';
    $updated = 0; $errors = 0; $responses = [];
    foreach ($queue as $item) {
        $payload = [
            'source' => 'brain-sync',
            'user' => $apiUser,
            'pass' => $apiPass,
            'function' => 'update_lead',
            'search_method' => 'PHONE_NUMBER',
            'phone_number' => $item['phone10'],
            'list_id' => $item['list_id'],
            'vendor_lead_code' => $item['external_id'],
        ];
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ok = ($code === 200) && is_string($resp) && stripos($resp, 'SUCCESS') !== false;
        if ($ok) { $updated++; } else { $errors++; }
        if (count($responses) < 5) {
            $responses[] = [ 'phone'=>$item['phone10'], 'list_id'=>$item['list_id'], 'external_id'=>$item['external_id'], 'http'=>$code, 'resp'=>substr((string)$resp,0,200), 'err'=>$err ];
        }
        if ($updated >= $limit) break;
    }

    echo json_encode([
        'lists' => $listIds,
        'requested' => $limit,
        'attempted' => count($queue),
        'updated' => $updated,
        'errors' => $errors,
        'samples' => $responses,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}


