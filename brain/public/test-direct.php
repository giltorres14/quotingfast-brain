<?php
// Direct PHP test without Laravel routing
echo json_encode([
    'success' => true,
    'message' => 'Direct PHP file works! Laravel deployment is successful.',
    'timestamp' => date('c'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown'
    ]
]);
?>