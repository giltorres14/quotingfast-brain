<?php
// Quick diagnostics for Vici lead counts vs dry-run fetch behavior
header('Content-Type: application/json');

try {
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $listIds = array_filter(array_map('trim', explode(',', $listsParam)), fn($v)=>$v!=='');
    $listCsv = implode(',', array_map('intval', $listIds));
    if ($listCsv === '') { $listCsv = '0'; }

    $pageSize = isset($_GET['page_size']) ? max(100, (int)$_GET['page_size']) : 2000;
    $pages = isset($_GET['pages']) ? max(1, (int)$_GET['pages']) : 5;

    // SSH + MySQL creds (match vici_dry_run_sync.php)
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb   = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;

    $execMysql = function(string $query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort): string {
        $mysql = sprintf(
            'mysql -h localhost -P %d -u %s -p%s -B %s -e %s 2>&1',
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

    $results = [
        'lists' => $listIds,
        'counts' => [],
        'totals' => [],
        'pages' => []
    ];

    // Baseline counts
    $qTotal = sprintf("SELECT COUNT(*) AS c FROM vicidial_list WHERE list_id IN (%s)", $listCsv);
    $rawTotal = $execMysql($qTotal);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $rawTotal))));
    $results['totals']['all_rows'] = (isset($lines[1]) ? (int)trim($lines[1]) : -1);

    $qPhone = sprintf("SELECT COUNT(*) AS c FROM vicidial_list WHERE list_id IN (%s) AND phone_number IS NOT NULL AND phone_number <> ''", $listCsv);
    $rawPhone = $execMysql($qPhone);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $rawPhone))));
    $results['totals']['with_phone'] = (isset($lines[1]) ? (int)trim($lines[1]) : -1);

    $qEmail = sprintf("SELECT COUNT(*) AS c FROM vicidial_list WHERE list_id IN (%s) AND email IS NOT NULL AND email <> ''", $listCsv);
    $rawEmail = $execMysql($qEmail);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $rawEmail))));
    $results['totals']['with_email'] = (isset($lines[1]) ? (int)trim($lines[1]) : -1);

    // Page through like dry-run (ORDER BY lead_id for stability)
    $scanned = 0;
    for ($p=0; $p<$pages; $p++) {
        $offset = $p * $pageSize;
        $q = sprintf(
            "SELECT lead_id, list_id, phone_number, email, vendor_lead_code FROM vicidial_list WHERE list_id IN (%s) ORDER BY lead_id LIMIT %d OFFSET %d",
            $listCsv,
            $pageSize,
            $offset
        );
        $out = $execMysql($q);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
        $rowCount = max(0, count($lines) - 1); // minus header
        $results['pages'][] = [ 'page' => $p+1, 'rows' => $rowCount, 'offset' => $offset ];
        $scanned += $rowCount;
        if ($rowCount < $pageSize) { break; }
    }
    $results['scanned_first_n_pages'] = $scanned;

    // How many have a 13-digit vendor_lead_code now
    $q13 = sprintf("SELECT COUNT(*) AS c FROM vicidial_list WHERE list_id IN (%s) AND vendor_lead_code REGEXP '^[0-9]{13}$'", $listCsv);
    $raw13 = $execMysql($q13);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $raw13))));
    $results['totals']['vendor_lead_code_13_digit'] = (isset($lines[1]) ? (int)trim($lines[1]) : -1);

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}



