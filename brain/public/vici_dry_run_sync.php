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
    $mysqlUser = 'qUSDV7hoj5cM6OFh';  // Production user
    $mysqlPass = 'dsHVMx9QqHtx5zNt';  // Production password
    $mysqlDb   = 'YLtZX713f1r6uauf';  // Production DB (11M rows!)
    $mysqlPort = 23964;  // Custom port

    $execMysql = function (string $query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort): string {
        // Build mysql command with host IP and port
        $mysql = sprintf(
            'mysql -h 37.27.138.222 -P %d -u %s -p%s %s -e %s 2>&1',
            $mysqlPort,
            escapeshellarg($mysqlUser),
            escapeshellarg($mysqlPass),
            escapeshellarg($mysqlDb),
            escapeshellarg($query)
        );
        $ssh = sprintf(
            'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql)
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

    // Fetch Vici candidates
    $q = sprintf(
        "SELECT lead_id, list_id, phone_number, email, vendor_lead_code FROM vicidial_list WHERE list_id IN (%s) LIMIT %d",
        $listCsv,
        $limit
    );
    $out = $execMysql($q);

    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
    // Expect header line present
    $data = [];
    for ($i=1; $i<count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) < 5) continue;
        $data[] = [
            'lead_id' => $cols[0],
            'list_id' => $cols[1],
            'phone_number' => $cols[2],
            'email' => $cols[3],
            'vendor_lead_code' => $cols[4],
        ];
    }

    $results = [
        'lists' => $listIds,
        'scanned' => count($data),
        'matched_phone' => 0,
        'matched_email' => 0,
        'matched_both' => 0,
        'unmatched' => 0,
        'samples' => [ 'phone' => [], 'email' => [], 'both' => [], 'unmatched' => [] ],
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

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}


