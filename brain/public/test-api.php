<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Lead received successfully!',
        'data' => $input,
        'timestamp' => date('c'),
        'endpoint' => 'test-api.php'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Test API endpoint is working!',
        'methods' => ['GET', 'POST'],
        'timestamp' => date('c')
    ]);
}
