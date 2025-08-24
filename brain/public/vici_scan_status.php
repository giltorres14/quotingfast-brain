<?php
header('Content-Type: application/json');
$id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']) : 'default';
$file = __DIR__ . '/vici_scan_progress_' . $id . '.json';
if (!file_exists($file)) {
    echo json_encode(['status'=>'not_found','id'=>$id]);
    exit;
}
echo file_get_contents($file);


