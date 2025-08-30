<?php
header('Content-Type: application/json');
try {
    $q = isset($_GET['q']) ? $_GET['q'] : '';
    if ($q === '') { throw new Exception('Provide q=... SQL'); }

    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb   = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;

    $mysql = sprintf(
        'mysql -h localhost -P %d -u %s -p%s -B %s -e %s 2>&1',
        $mysqlPort,
        escapeshellarg($mysqlUser),
        escapeshellarg($mysqlPass),
        escapeshellarg($mysqlDb),
        escapeshellarg($q)
    );
    $ssh = sprintf(
        'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=ERROR %s@%s %s 2>&1',
        escapeshellarg($sshPass), $sshPort, escapeshellarg($sshUser), escapeshellarg($sshHost), escapeshellarg($mysql)
    );
    $raw = (string)shell_exec($ssh);
    $lines = explode("\n", $raw);
    echo json_encode([
        'query' => $q,
        'bytes' => strlen($raw),
        'lines' => count($lines),
        'first_3' => array_slice($lines, 0, 3),
        'last_3' => array_slice($lines, max(0, count($lines)-3), 3)
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}





