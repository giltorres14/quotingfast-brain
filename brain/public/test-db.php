<?php
// Comprehensive database connection test with debugging
header('Content-Type: application/json');

$configs = [
    'internal' => [
        'host' => 'dpg-d277kvk9c44c7388opg0-a',
        'port' => '5432',
        'dbname' => 'brain_production',
        'user' => 'brain_user',
        'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    ],
    'external' => [
        'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
        'port' => '5432',
        'dbname' => 'brain_production',
        'user' => 'brain_user',
        'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    ]
];

$results = [];

foreach ($configs as $type => $config) {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    
    $results[$type] = [
        'config' => [
            'host' => $config['host'],
            'port' => $config['port'],
            'dbname' => $config['dbname'],
            'user' => $config['user'],
            'password_length' => strlen($config['password'])
        ],
        'dsn' => $dsn
    ];
    
    try {
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        $stmt = $pdo->query('SELECT version()');
        $version = $stmt->fetchColumn();
        
        $results[$type]['success'] = true;
        $results[$type]['version'] = $version;
        $results[$type]['message'] = "Connection successful!";
        
    } catch (PDOException $e) {
        $results[$type]['success'] = false;
        $results[$type]['error'] = $e->getMessage();
        $results[$type]['code'] = $e->getCode();
    }
}

// Also check environment variables
$results['env'] = [
    'DB_HOST' => getenv('DB_HOST') ?: 'not set',
    'DB_PORT' => getenv('DB_PORT') ?: 'not set',
    'DB_DATABASE' => getenv('DB_DATABASE') ?: 'not set',
    'DB_USERNAME' => getenv('DB_USERNAME') ?: 'not set',
    'DB_PASSWORD_exists' => !empty(getenv('DB_PASSWORD'))
];

// Check if .env file exists
$results['env_file'] = [
    'exists' => file_exists('../.env'),
    'readable' => is_readable('../.env')
];

if ($results['env_file']['readable']) {
    $env_content = file_get_contents('../.env');
    $results['env_file']['lines'] = count(explode("\n", $env_content));
    $results['env_file']['has_db_host'] = strpos($env_content, 'DB_HOST=') !== false;
    $results['env_file']['has_db_password'] = strpos($env_content, 'DB_PASSWORD=') !== false;
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>


