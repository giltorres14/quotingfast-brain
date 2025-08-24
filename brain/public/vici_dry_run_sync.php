<?php
// Dry-run matcher: scans Vici rows (no vendor_lead_code) and matches to Brain by phone/email.
// Usage: /vici_dry_run_sync.php?lists=6018,6019,6020&limit=5000
// Output: JSON summary with counts and small samples.

header('Content-Type: application/json');

try {
    // Params
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '';
    $campaignParam = isset($_GET['campaign']) ? trim($_GET['campaign']) : '';
    // SAFETY: Max limit 10000 to prevent overloading 11M row table
    $limit = isset($_GET['limit']) ? min(10000, max(100, (int)$_GET['limit'])) : 5000;
    // Page size for batch retrieval to avoid SSH/CLI output truncation
    $pageSize = isset($_GET['page_size']) ? min(5000, max(250, (int)$_GET['page_size'])) : 2000;
    // Optional progress id to log live progress to a public JSON file
    $progressId = isset($_GET['progress_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['progress_id']) : 'default';
    $progressFile = __DIR__ . '/vici_scan_progress_' . $progressId . '.json';
    $writeProgress = function(array $payload) use ($progressFile) {
        @file_put_contents($progressFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    };

    // Normalize helpers
    $normalizePhone10 = function (?string $raw): string {
        $d = preg_replace('/\D+/', '', (string)$raw);
        if (strlen($d) > 10) { $d = substr($d, -10); }
        return $d ?? '';
    };
    $normalizeEmail = function (?string $e): string { return strtolower(trim((string)$e)); };

    // Brain DB (Postgres)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Build Brain index (phones and emails)
    $brainPhoneToId = [];
    $brainEmailToId = [];
    $stmt = $pg->query("SELECT id, external_lead_id, phone, email FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p10 = $normalizePhone10($row['phone'] ?? '');
        if ($p10 !== '') { $brainPhoneToId[$p10] = $row['external_lead_id']; }
        $em = $normalizeEmail($row['email'] ?? '');
        if ($em !== '') { $brainEmailToId[$em] = $row['external_lead_id']; }
    }

    // Vici (MySQL over SSH) - PRODUCTION CREDENTIALS
    // WARNING: vicidial_list has 11 MILLION rows - always use LIMIT!
    $sshHost = isset($_GET['ssh_host']) ? trim($_GET['ssh_host']) : '37.27.138.222';
    $sshPort = isset($_GET['ssh_port']) ? (int)$_GET['ssh_port'] : 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';  // Cron user
    $mysqlPass = 'hkj7uAlV9wp9zOMr';  // Cron password
    $mysqlDb   = 'Q6hdjl67GRigMofv';  // Asterisk DB (11M rows!)
    $mysqlPort = 20540;  // Custom port

    $execMysql = function (string $query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort): string {
        // Build mysql command with localhost and custom port and stream via remote temp file to avoid stdout caps
        $tmp = '/tmp/vici_page_' . bin2hex(random_bytes(6)) . '.tsv';
        $base = sprintf(
            'PAGER=cat MYSQL_PAGER=cat mysql --skip-pager --skip-column-names --batch --raw --unbuffered -h localhost -P %d -u %s -p%s %s -e %s',
            $mysqlPort,
            escapeshellarg($mysqlUser),
            escapeshellarg($mysqlPass),
            escapeshellarg($mysqlDb),
            escapeshellarg($query)
        );
        $remote = sprintf('bash -lc %s', escapeshellarg("set -eo pipefail; ($base) > $tmp; wc -l $tmp 1>&2; cat $tmp; rm -f $tmp"));
        $ssh = sprintf(
            'sshpass -p %s ssh -T -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=ERROR %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), $remote
        );
        return (string)shell_exec($ssh);
    };

    // Prepare list filter
    $listIds = [];
    if ($campaignParam !== '') {
        // Fetch lists for campaign(s)
        $camps = array_filter(array_map('trim', explode(',', $campaignParam)), fn($v)=>$v!=='');
        $campCsv = "'" . implode("','", array_map(fn($c)=>str_replace("'","''", $c), $camps)) . "'";
        $listQuery = sprintf("SELECT list_id FROM vicidial_lists WHERE campaign_id IN (%s)", $campCsv);
        $outLists = $execMysql($listQuery);
        $linesL = array_values(array_filter(array_map('trim', explode("\n", $outLists))));
        for ($i=1; $i<count($linesL); $i++) {
            $lid = trim($linesL[$i]);
            if ($lid !== '') { $listIds[] = $lid; }
        }
    } else {
        $listIds = array_filter(array_map('trim', explode(',', $listsParam)), fn($v)=>$v !== '');
    }
    $listCsv = implode(',', array_map('intval', $listIds));
    if ($listCsv === '') { $listCsv = '0'; }

    // Fetch Vici candidates per-list, keyset pagination, to avoid stdout truncation and OFFSET costs
    $data = [];
    $fetched = 0;
    $debugPages = [];
    // Initialize progress log
    $progress = [
        'started_at' => date('c'),
        'lists' => [],
        'scanned' => 0,
        'limit' => $limit,
        'page_size' => $pageSize,
        'per_list' => [],
    ];
    foreach ($listIds as $lidInit) { $progress['per_list'][(string)$lidInit] = ['last_id'=>0,'pages'=>0,'rows'=>0]; }
    $writeProgress($progress);
    foreach ($listIds as $listId) {
        if ($fetched >= $limit) { break; }
        $lastId = 0;
        $listPages = [];
        while ($fetched < $limit) {
            $take = min($pageSize, $limit - $fetched);
            $q = sprintf(
                "SELECT lead_id, list_id, phone_number, email, vendor_lead_code FROM vicidial_list WHERE list_id = %d AND lead_id > %d ORDER BY lead_id ASC LIMIT %d",
                (int)$listId,
                $lastId,
                $take
            );
            $out = $execMysql($q);
            $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
            // Remove any non-data warnings or empty lines
            $lines = array_values(array_filter($lines, function($ln){
                if ($ln === '') return false;
                if (stripos($ln, 'Warning:') === 0) return false;
                return true;
            }));
            if (count($lines) === 0) { $listPages[] = ['rows'=>0,'lastId'=>$lastId]; break; }
            $pageRows = 0;
            for ($i=0; $i<count($lines); $i++) {
                $cols = preg_split('/\t/', $lines[$i]);
                // Skip header lines if present
                if (isset($cols[0]) && !ctype_digit(strval($cols[0])) && stripos($cols[0], 'lead_id') !== false) {
                    continue;
                }
                // Pad missing trailing fields (MySQL omits trailing empty columns)
                while (count($cols) < 5) { $cols[] = ''; }
                $data[] = [
                    'lead_id' => $cols[0],
                    'list_id' => $cols[1],
                    'phone_number' => $cols[2],
                    'email' => $cols[3],
                    'vendor_lead_code' => $cols[4],
                ];
                $fetched++;
                $pageRows++;
                $lastId = max($lastId, (int)$cols[0]);
                if ($fetched >= $limit) { break; }
            }
            $listPages[] = ['rows'=>$pageRows,'lastId'=>$lastId];
            // Update live progress after each page
            $progress['scanned'] = $fetched;
            $progress['per_list'][(string)$listId]['last_id'] = $lastId;
            $progress['per_list'][(string)$listId]['pages'] = ($progress['per_list'][(string)$listId]['pages'] ?? 0) + 1;
            $progress['per_list'][(string)$listId]['rows'] = ($progress['per_list'][(string)$listId]['rows'] ?? 0) + $pageRows;
            $writeProgress($progress);
            if ($pageRows === 0) { break; }
        }
        $debugPages[(string)$listId] = $listPages;
    }

    $results = [
        'lists' => $listIds,
        'scanned' => count($data),
        'matched_phone' => 0,
        'matched_email' => 0,
        'matched_both' => 0,
        'unmatched' => 0,
        'samples' => [ 'phone' => [], 'email' => [], 'both' => [], 'unmatched' => [] ],
        'pages' => $debugPages,
    ];

    foreach ($data as $row) {
        $p10 = $normalizePhone10($row['phone_number']);
        $em  = $normalizeEmail($row['email']);
        $byPhone = ($p10 !== '' && isset($brainPhoneToId[$p10]));
        $byEmail = ($em !== '' && isset($brainEmailToId[$em]));
        if ($byPhone && $byEmail) {
            $results['matched_both']++;
            if (count($results['samples']['both']) < 5) {
                $results['samples']['both'][] = [ 'lead_id'=>$row['lead_id'], 'list_id'=>$row['list_id'], 'phone'=>$row['phone_number'], 'email'=>$row['email'], 'external_lead_id_phone'=>$brainPhoneToId[$p10], 'external_lead_id_email'=>$brainEmailToId[$em] ];
            }
        } elseif ($byPhone) {
            $results['matched_phone']++;
            if (count($results['samples']['phone']) < 5) {
                $results['samples']['phone'][] = [ 'lead_id'=>$row['lead_id'], 'list_id'=>$row['list_id'], 'phone'=>$row['phone_number'], 'external_lead_id'=>$brainPhoneToId[$p10] ];
            }
        } elseif ($byEmail) {
            $results['matched_email']++;
            if (count($results['samples']['email']) < 5) {
                $results['samples']['email'][] = [ 'lead_id'=>$row['lead_id'], 'list_id'=>$row['list_id'], 'email'=>$row['email'], 'external_lead_id'=>$brainEmailToId[$em] ];
            }
        } else {
            $results['unmatched']++;
            if (count($results['samples']['unmatched']) < 5) {
                $results['samples']['unmatched'][] = [ 'lead_id'=>$row['lead_id'], 'list_id'=>$row['list_id'], 'phone'=>$row['phone_number'], 'email'=>$row['email'] ];
            }
        }
    }

    // Finalize progress
    $progress['finished_at'] = date('c');
    $progress['result_summary'] = [
        'scanned' => $results['scanned'],
        'matched_phone' => $results['matched_phone'],
        'matched_email' => $results['matched_email'],
        'matched_both' => $results['matched_both'],
        'unmatched' => $results['unmatched'],
    ];
    $writeProgress($progress);

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}


