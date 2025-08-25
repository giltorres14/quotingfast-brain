<?php
// Bulk ViciDial sync via server-side JOIN using a temporary phone->external_id map
// Usage (dry-run default):
//   /vici_bulk_join_sync.php?lists=6018,6019&only_null=0&limit_updates=0
// Commit changes:
//   /vici_bulk_join_sync.php?lists=6018,6019&commit=1&only_null=1&limit_updates=5000
// Notes:
// - Matches strictly by last-10 digits of phone
// - Builds map from Brain (Postgres) and inserts in chunks into MySQL temp table
// - Never scans vicidial_list; uses JOIN for counts/updates

header('Content-Type: application/json');

function normalize_phone_10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $startedAt = microtime(true);
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '';
    if ($listsParam === '') { throw new Exception('lists parameter required, e.g., 6018,6019'); }
    $listIds = array_values(array_filter(array_map('intval', explode(',', $listsParam)), fn($v)=>$v>0));
    if (empty($listIds)) { throw new Exception('No valid list ids'); }
    $listCsv = implode(',', $listIds);

    $commit = isset($_GET['commit']) ? (int)$_GET['commit'] === 1 : false;
    $onlyNull = isset($_GET['only_null']) ? (int)$_GET['only_null'] === 1 : false; // true → only update empty vendor_lead_code
    $limitUpdates = isset($_GET['limit_updates']) ? max(0, (int)$_GET['limit_updates']) : 0; // 0 → no limit
    $chunkSize = isset($_GET['chunk']) ? max(200, min(5000, (int)$_GET['chunk'])) : 1000;

    // Brain DB (Postgres)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vici (MySQL over SSH)
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
            'sshpass -p %s ssh -T -p %d -o ServerAliveInterval=20 -o ServerAliveCountMax=9 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql)
        );
        return (string)shell_exec($ssh);
    };

    // 1) Build phone->external_id map from Brain (deduped)
    $inserted = 0; $rowsSeen = 0; $distinctPhones = [];
    $stmt = $pg->query("SELECT external_lead_id, phone FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND phone IS NOT NULL");
    $batch = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rowsSeen++;
        $p10 = normalize_phone_10($r['phone'] ?? '');
        if ($p10 === '') { continue; }
        $eid = trim($r['external_lead_id']);
        $distinctPhones[$p10] = $eid; // dedupe latest wins
    }
    $inserted = count($distinctPhones);

    // 2) Dry-run count and optional update using UNION ALL subquery chunks (no CREATE privilege needed)
    $phonePairs = [];
    foreach ($distinctPhones as $ph=>$ex) { $phonePairs[] = [$ph, $ex]; }
    $totalMatches = 0;
    $totalUpdated = 0;
    $whereNull = $onlyNull ? " AND (v.vendor_lead_code IS NULL OR v.vendor_lead_code='')" : '';
    $remainingLimit = $limitUpdates > 0 ? $limitUpdates : 0;
    for ($i = 0; $i < count($phonePairs); $i += $chunkSize) {
        $slice = array_slice($phonePairs, $i, $chunkSize);
        // Build derived table as UNION ALL
        $parts = [];
        foreach ($slice as $pair) {
            $ph = addslashes($pair[0]);
            $ex = addslashes($pair[1]);
            $parts[] = "SELECT '$ph' AS phone10, '$ex' AS external_id";
        }
        $derived = '(' . implode(' UNION ALL ', $parts) . ') AS b';
        // Count matches for this chunk
        $countSql = sprintf(
            "SELECT COUNT(*) AS c FROM vicidial_list v JOIN %s ON RIGHT(v.phone_number,10)=b.phone10 WHERE v.list_id IN (%s)%s",
            $derived,
            $listCsv,
            $whereNull
        );
        $outCnt = $execMysql($countSql);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $outCnt))));
        if (isset($lines[1]) && is_numeric($lines[1])) { $totalMatches += (int)$lines[1]; }
        if ($commit) {
            $updSql = sprintf(
                "UPDATE vicidial_list v JOIN %s ON RIGHT(v.phone_number,10)=b.phone10 SET v.vendor_lead_code=b.external_id WHERE v.list_id IN (%s)%s AND (v.vendor_lead_code IS NULL OR v.vendor_lead_code <> b.external_id)",
                $derived,
                $listCsv,
                $whereNull
            );
            if ($remainingLimit > 0) { $updSql .= ' LIMIT ' . (int)$remainingLimit; }
            $outUpd = $execMysql($updSql . '; SELECT ROW_COUNT() AS updated;');
            $partsOut = array_values(array_filter(array_map('trim', explode("\n", $outUpd))));
            for ($k=count($partsOut)-1; $k>=0; $k--) {
                if (is_numeric($partsOut[$k])) { $totalUpdated += (int)$partsOut[$k]; break; }
            }
            if ($remainingLimit > 0) {
                $remainingLimit -= $totalUpdated;
                if ($remainingLimit <= 0) { break; }
            }
        }
    }

    $matchCount = $totalMatches;
    $updated = $totalUpdated;

    $elapsed = round(microtime(true) - $startedAt, 3);
    echo json_encode([
        'lists' => $listIds,
        'rows_seen_in_brain' => $rowsSeen,
        'inserted_phone_map' => $inserted,
        'only_null' => $onlyNull,
        'limit_updates' => $limitUpdates,
        'dry_run_match_count' => $matchCount,
        'updated' => $updated,
        'commit' => $commit,
        'elapsed_sec' => $elapsed,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}


