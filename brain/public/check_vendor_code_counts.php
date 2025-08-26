<?php
// Count 13-digit vendor_lead_code per list (runs from Render whitelisted IP)
header('Content-Type: application/json');

try {
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb   = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;

    $lists = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $listIds = array_filter(array_map('trim', explode(',', $lists)));
    $listCsv = implode(',', array_map('intval', $listIds));
    if ($listCsv === '') { $listCsv = '0'; }

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
            'sshpass -p %s ssh -T -p %d -o ServerAliveInterval=20 -o ServerAliveCountMax=9 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s %s 2>&1',
            escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql)
        );
        return (string)shell_exec($ssh);
    };

    $q = sprintf(
        "SELECT list_id, COUNT(*) AS cnt FROM vicidial_list WHERE list_id IN (%s) AND vendor_lead_code REGEXP '^[0-9]{13}$' GROUP BY list_id ORDER BY list_id",
        $listCsv
    );
    $out = $execMysql($q);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));

    $results = [ 'counts' => [], 'total' => 0, 'raw' => implode("\n", array_slice($lines, 0, 20)) ];
    for ($i=1; $i<count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) >= 2) {
            $lid = trim($cols[0]);
            $cnt = (int)trim($cols[1]);
            $results['counts'][$lid] = $cnt;
            $results['total'] += $cnt;
        }
    }

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}



