<?php
// Incremental updater: reads Vici phones in pages and updates vendor_lead_code per row
// Avoids temp tables and large JOIN payloads; relies only on UPDATE privilege
// Usage: /vici_update_incremental.php?lists=6018,6019&limit_updates=2000&page_size=500

header('Content-Type: application/json');

function norm10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $started = microtime(true);
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '';
    if ($listsParam === '') { throw new Exception('lists parameter required'); }
    $listIds = array_values(array_filter(array_map('intval', explode(',', $listsParam)), fn($v)=>$v>0));
    if (empty($listIds)) { throw new Exception('no valid list ids'); }
    $listCsv = implode(',', $listIds);

    $limitUpdates = isset($_GET['limit_updates']) ? max(1, (int)$_GET['limit_updates']) : 2000;
    $pageSize = isset($_GET['page_size']) ? max(50, min(1000, (int)$_GET['page_size'])) : 500;

    // Brain (PG)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $map = [];
    $stmt = $pg->query("SELECT external_lead_id, phone FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND phone IS NOT NULL");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p = norm10($r['phone'] ?? '');
        if ($p !== '') { $map[$p] = $r['external_lead_id']; }
    }

    // Vici over SSH MySQL
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb   = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;
    $exec = function(string $q) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort): string {
        $mysql = sprintf('mysql -h localhost -P %d -u %s -p%s %s -e %s 2>&1',
            $mysqlPort, escapeshellarg($mysqlUser), escapeshellarg($mysqlPass), escapeshellarg($mysqlDb), escapeshellarg($q));
        $ssh = sprintf('sshpass -p %s ssh -T -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql));
        return (string)shell_exec($ssh);
    };

    $updated = 0; $scanned = 0; $pages = 0; $lastLeadId = 0;
    $samples = [];
    while ($updated < $limitUpdates) {
        // Pull next page of candidates with empty vendor_lead_code, ordered by lead_id
        $q = sprintf("SELECT lead_id, phone_number FROM vicidial_list WHERE list_id IN (%s) AND (vendor_lead_code IS NULL OR vendor_lead_code='') AND lead_id > %d ORDER BY lead_id ASC LIMIT %d",
            $listCsv, $lastLeadId, $pageSize);
        $out = $exec($q);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
        if (count($lines) <= 1) { break; }
        for ($i=1; $i<count($lines); $i++) {
            $cols = preg_split('/\t/', $lines[$i]);
            if (count($cols) < 2) { continue; }
            $leadId = (int)$cols[0];
            $phone  = $cols[1];
            $lastLeadId = max($lastLeadId, $leadId);
            $scanned++;
            $p10 = norm10($phone);
            if ($p10 === '' || !isset($map[$p10])) { continue; }
            $eid = $map[$p10];
            if (strlen($eid) !== 13) { continue; }
            // Update this single row
            $upd = sprintf("UPDATE vicidial_list SET vendor_lead_code='%s', source_id='BRAIN_%s', modify_date=NOW() WHERE lead_id=%d AND (vendor_lead_code IS NULL OR vendor_lead_code='')",
                addslashes($eid), addslashes($eid), $leadId);
            $u = $exec($upd . '; SELECT ROW_COUNT() AS updated;');
            // Parse last numeric
            $parts = array_values(array_filter(array_map('trim', explode("\n", $u))));
            for ($k=count($parts)-1; $k>=0; $k--) { if (is_numeric($parts[$k])) { $updated += (int)$parts[$k]; break; } }
            if (count($samples) < 5 && $updated > 0) { $samples[] = ['lead_id'=>$leadId, 'external_lead_id'=>$eid]; }
            if ($updated >= $limitUpdates) { break 2; }
        }
        $pages++;
    }

    $elapsed = round(microtime(true) - $started, 2);
    echo json_encode([
        'lists' => $listIds,
        'scanned_candidates' => $scanned,
        'updated' => $updated,
        'limit_updates' => $limitUpdates,
        'pages' => $pages,
        'last_lead_id' => $lastLeadId,
        'samples' => $samples,
        'elapsed_sec' => $elapsed
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}


