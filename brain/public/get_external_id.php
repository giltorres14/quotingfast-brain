<?php
header('Content-Type: application/json');

function norm10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
    if ($phone === '') { throw new Exception('phone required'); }
    $p10 = norm10($phone);
    if ($p10 === '') { echo json_encode(['phone'=>$phone,'external_lead_id'=>null]); exit; }

    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pg->prepare('SELECT external_lead_id FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND REGEXP_REPLACE(phone,\'[^0-9]\',\'\',\'g\') LIKE :p10 ORDER BY id DESC LIMIT 1');
    $stmt->execute([':p10' => '%' . $p10]);
    $eid = $stmt->fetchColumn();
    echo json_encode(['phone'=>$p10,'external_lead_id'=>$eid?:null]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}




