<?php
// Check actual lead counts in ViciDial lists
header('Content-Type: application/json');

try {
    // SSH and MySQL credentials
    $sshHost = '37.27.138.222';
    $sshPort = 11845;
    $sshUser = 'root';
    $sshPass = 'Monster@2213@!';
    $mysqlUser = 'wS3Vtb7rJgAGePi5';
    $mysqlPass = 'hkj7uAlV9wp9zOMr';
    $mysqlDb = 'Q6hdjl67GRigMofv';
    $mysqlPort = 20540;
    
    $lists = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $listIds = array_filter(array_map('trim', explode(',', $lists)));
    $listCsv = implode(',', array_map('intval', $listIds));
    
    // Function to execute MySQL via SSH
    $execMysql = function($query) use ($sshHost,$sshPort,$sshUser,$sshPass,$mysqlUser,$mysqlPass,$mysqlDb,$mysqlPort) {
        $mysql = sprintf(
            'mysql -h localhost -P %d -u %s -p%s %s -e %s 2>&1',
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
    
    // Query to count leads per list
    $q = sprintf(
        "SELECT list_id, COUNT(*) as count FROM vicidial_list WHERE list_id IN (%s) GROUP BY list_id ORDER BY list_id",
        $listCsv
    );
    
    $out = $execMysql($q);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $out))));
    
    $results = [
        'lists_requested' => $listIds,
        'counts' => [],
        'total' => 0,
        'raw_output' => implode("\n", array_slice($lines, 0, 20))
    ];
    
    // Parse results (skip header)
    for ($i = 1; $i < count($lines); $i++) {
        $cols = preg_split('/\t/', $lines[$i]);
        if (count($cols) >= 2) {
            $listId = trim($cols[0]);
            $count = (int)trim($cols[1]);
            $results['counts'][$listId] = $count;
            $results['total'] += $count;
        }
    }
    
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}