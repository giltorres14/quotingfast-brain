<?php
// Return sample Brain phones for a given Vici list id by searching JSON meta for vici_list_id.
// Usage: /brain_list_sample.php?list=6026&limit=20

header('Content-Type: application/json');

try {
    $list = isset($_GET['list']) ? (int)$_GET['list'] : 6026;
    $limit = isset($_GET['limit']) ? max(1,(int)$_GET['limit']) : 20;

    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Try vici_list_id column first
    $phones = [];
    try {
        $stmt = $pdo->prepare("SELECT id, phone FROM leads WHERE vici_list_id = :lid AND COALESCE(phone,'')<>'' LIMIT :lim");
        $stmt->bindValue(':lid', $list, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $phones[] = $row;
        }
    } catch (Throwable $e) {
        // Column may not exist; ignore
    }

    if (count($phones) < 1) {
        // Fallback: search meta JSON for vici_list_id
        $like = '%"vici_list_id":'.$list.'%';
        $stmt = $pdo->prepare("SELECT id, phone FROM leads WHERE COALESCE(meta,'') LIKE :like AND COALESCE(phone,'')<>'' LIMIT :lim");
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $phones[] = $row;
        }
    }

    // Normalize phone10
    $samples = [];
    foreach ($phones as $row) {
        $raw = (string)($row['phone'] ?? '');
        $digits = preg_replace('/\D+/', '', $raw);
        if (strlen($digits) > 10) { $digits = substr($digits, -10); }
        $samples[] = [ 'id' => (int)$row['id'], 'raw_phone' => $raw, 'phone10' => $digits ];
    }

    echo json_encode([
        'list' => $list,
        'limit' => $limit,
        'found' => count($samples),
        'samples' => $samples,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}







