<?php
// Debug endpoint: show sample phone numbers from a Vici list and their normalized 10-digit forms.
// Usage: /test_vici_debug.php?list=6026&limit=50

header('Content-Type: application/json');

try {
    $list = isset($_GET['list']) ? (int)$_GET['list'] : 6026;
    $limit = isset($_GET['limit']) ? max(1,(int)$_GET['limit']) : 50;

    $sshHost = isset($_GET['ssh_host']) ? trim($_GET['ssh_host']) : '37.27.138.222';
    $sshPort = isset($_GET['ssh_port']) ? (int)$_GET['ssh_port'] : 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'cron';
    $mysqlPass = '1234';
    $mysqlDb   = 'asterisk';  // Try asterisk DB instead

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

    $q = sprintf("SELECT lead_id, phone_number FROM vicidial_list WHERE list_id=%d LIMIT %d", $list, $limit);
    $out = $execMysql($q);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
    $samples = [];
    for ($i=1; $i<count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) < 2) continue;
        $leadId = $cols[0];
        $raw    = $cols[1];
        $digits = preg_replace('/\D+/', '', (string)$raw);
        if (strlen($digits) > 10) { $digits = substr($digits, -10); }
        $samples[] = [
            'lead_id' => $leadId,
            'raw_phone' => $raw,
            'phone10' => $digits,
        ];
    }

    echo json_encode([
        'list' => $list,
        'limit' => $limit,
        'count_returned' => count($samples),
        'samples' => $samples,
        'raw_head' => implode("\n", array_slice($lines,0,5)),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()], JSON_PRETTY_PRINT);
}


