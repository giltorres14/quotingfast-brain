<?php
// Simple script to check the Brain server's actual IP when deployed

header('Content-Type: application/json');

$data = [
    'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
    'forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A',
    'real_ip' => $_SERVER['HTTP_X_REAL_IP'] ?? 'N/A',
    'outbound_ip' => trim(file_get_contents('https://api.ipify.org')),
    'timestamp' => date('Y-m-d H:i:s'),
    'hostname' => gethostname()
];

echo json_encode($data, JSON_PRETTY_PRINT);
