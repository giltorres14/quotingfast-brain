<?php
// Writer: Assign Brain external_lead_id into Vici (vendor_lead_code) by matching phone/email.
// Usage: /vici_sync_assign_ids.php?admin_key=QF-ADMIN-KEY-2025&lists=6018,6019,...&limit=10000&dry=1

header('Content-Type: application/json');

try {
    // Security
    $providedKey = $_GET['admin_key'] ?? '';
    $ADMIN_KEY = getenv('ADMIN_ACTION_KEY') ?: 'QF-ADMIN-KEY-2025';
    if (!hash_equals($ADMIN_KEY, (string)$providedKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Params
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $limit = isset($_GET['limit']) ? max(100, (int)$_GET['limit']) : 10000;
    $isDryRun = isset($_GET['dry']) ? ((int)$_GET['dry'] === 1) : true;

    // Helpers
    $normalizePhone10 = function (?string $raw): string {
        $d = preg_replace('/\D+/', '', (string)$raw);
        if (strlen($d) > 10) { $d = substr($d, -10); }
        return $d ?? '';
    };
    $normalizeEmail = function (?string $e): string { return strtolower(trim((string)$e)); };

    // Brain (PG)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Build indexes
    $brainPhoneToId = [];
    $brainEmailToId = [];
    $stmt = $pg->query("SELECT external_lead_id, phone, email FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p10 = $normalizePhone10($row['phone'] ?? '');
        if ($p10 !== '') { $brainPhoneToId[$p10] = $row['external_lead_id']; }
        $em = $normalizeEmail($row['email'] ?? '');
        if ($em !== '') { $brainEmailToId[$em] = $row['external_lead_id']; }
    }

    // Vici (MySQL over SSH)
    $sshHost = isset($_GET['ssh_host']) ? trim($_GET['ssh_host']) : '37.27.138.222';
    $sshPort = isset($_GET['ssh_port']) ? (int)$_GET['ssh_port'] : 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'Superman';
    $mysqlPass = '8ZDWGAAQRD';
    $mysqlDb   = 'asterisk';

    $execMysql = function (string $query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb): string {
        $mysql = sprintf(
            'mysql -h localhost -u %s -p%s %s -e %s 2>&1',
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

    $listIds = array_filter(array_map('trim', explode(',', $listsParam)), fn($v)=>$v !== '');
    $listCsv = implode(',', array_map('intval', $listIds));
    if ($listCsv === '') { $listCsv = '0'; }

    $results = [
        'lists' => $listIds,
        'scanned' => 0,
        'matched_phone' => 0,
        'matched_email' => 0, // kept for report compatibility (unused in phone-only mode)
        'matched_both' => 0,  // kept for report compatibility (unused in phone-only mode)
        'updated' => 0,
        'skipped_already_set' => 0,
        'unmatched' => 0,
        'dry_run' => $isDryRun,
        'samples' => [ 'updated' => [], 'unmatched' => [] ],
    ];

    // Pull candidates in one page (limit param)
    $q = sprintf(
        "SELECT lead_id, list_id, phone_number, email, vendor_lead_code FROM vicidial_list WHERE vendor_lead_code IS NULL AND list_id IN (%s) LIMIT %d",
        $listCsv, $limit
    );
    $out = $execMysql($q);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
    $rows = [];
    for ($i=1; $i<count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) < 5) continue;
        $rows[] = [
            'lead_id' => (int)$cols[0],
            'list_id' => (int)$cols[1],
            'phone' => $cols[2],
            'email' => $cols[3],
        ];
    }
    $results['scanned'] = count($rows);

    foreach ($rows as $r) {
        $p10 = $normalizePhone10($r['phone']);
        $em  = $normalizeEmail($r['email']);
        $eid = ($p10 !== '' && isset($brainPhoneToId[$p10])) ? $brainPhoneToId[$p10] : null; // PHONE-ONLY

        if ($eid) {
            if ($eid === '0' || strlen($eid) !== 13) { continue; }
            $results['matched_phone']++;

            if ($isDryRun) {
                if (count($results['samples']['updated']) < 5) {
                    $results['samples']['updated'][] = [ 'lead_id'=>$r['lead_id'], 'list_id'=>$r['list_id'], 'external_lead_id'=>$eid ];
                }
                $results['updated']++;
            } else {
                $upd = sprintf(
                    "UPDATE vicidial_list SET vendor_lead_code='%s', source_id='BRAIN_%s', modify_date=NOW() WHERE lead_id=%d",
                    $eid, $eid, $r['lead_id']
                );
                $uout = $execMysql($upd);
                // best-effort: assume success unless ERROR
                if (stripos($uout, 'ERROR') === false) {
                    $results['updated']++;
                    if (count($results['samples']['updated']) < 5) {
                        $results['samples']['updated'][] = [ 'lead_id'=>$r['lead_id'], 'list_id'=>$r['list_id'], 'external_lead_id'=>$eid ];
                    }
                }
            }
        } else {
            $results['unmatched']++;
            if (count($results['samples']['unmatched']) < 5) {
                $results['samples']['unmatched'][] = [ 'lead_id'=>$r['lead_id'], 'list_id'=>$r['list_id'], 'phone'=>$r['phone'], 'email'=>$r['email'] ];
            }
        }
    }

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}


