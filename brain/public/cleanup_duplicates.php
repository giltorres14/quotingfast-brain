<?php
// Public maintenance endpoint to cleanup duplicate leads by keeping the highest-score per group.
// Usage: /cleanup_duplicates.php?admin_key=QF-ADMIN-KEY-2025&batch=300&dry=0

header('Content-Type: application/json');

try {
    $providedKey = $_GET['admin_key'] ?? '';
    $ADMIN_KEY = getenv('ADMIN_ACTION_KEY') ?: 'QF-ADMIN-KEY-2025';
    if (!hash_equals($ADMIN_KEY, (string)$providedKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $batchSize = isset($_GET['batch']) ? (int)$_GET['batch'] : 200;
    $batchSize = max(50, min($batchSize, 1000));
    $isDryRun = isset($_GET['dry']) ? (int)$_GET['dry'] === 1 : false;

    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $scoreLead = function(array $l): int {
        $score = 0;
        foreach (['name','phone','email','address','city','state','zip','zip_code','type'] as $f) {
            if (!empty($l[$f])) { $score++; }
        }
        $toArray = function($v) {
            if (is_array($v)) { return $v; }
            if (is_string($v)) { $d = json_decode($v, true); return is_array($d) ? $d : []; }
            return [];
        };
        $drivers = $toArray($l['drivers'] ?? []);
        $vehicles = $toArray($l['vehicles'] ?? []);
        $current = $toArray($l['current_policy'] ?? []);
        $score += (is_countable($drivers) ? count($drivers) : 0) * 2;
        $score += (is_countable($vehicles) ? count($vehicles) : 0) * 2;
        if (is_array($current)) { $score += count(array_filter($current, fn($v)=>$v!==null && $v!=='')); }
        return $score;
    };

    $keepIds = [];
    $deleteCandidates = [];
    $processedPhoneKeys = 0;
    $processedEmailKeys = 0;

    $processKeys = function(array $keys, string $by) use ($pdo, &$keepIds, &$deleteCandidates, $scoreLead) {
        if (empty($keys)) { return; }
        $in = str_repeat('?,', count($keys) - 1) . '?';
        if ($by === 'phone') {
            $sql = "SELECT *, REGEXP_REPLACE(COALESCE(phone,''),'[^0-9]','','g') AS nkey, LOWER(TRIM(email)) AS nemail FROM leads WHERE REGEXP_REPLACE(COALESCE(phone,''),'[^0-9]','','g') IN ($in)";
        } else {
            $sql = "SELECT *, LOWER(TRIM(email)) AS nkey, REGEXP_REPLACE(COALESCE(phone,''),'[^0-9]','','g') AS nphone FROM leads WHERE LOWER(TRIM(email)) IN ($in)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($keys);
        $groups = [];
        while ($lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $by . ':' . ($lead['nkey'] ?? '');
            $groups[$key]['by'] = $by;
            $groups[$key]['key'] = $lead['nkey'] ?? '';
            $groups[$key]['leads'][] = $lead;
        }
        foreach ($groups as $g) {
            $leads = $g['leads'] ?? [];
            if (count($leads) < 2) { continue; }
            foreach ($leads as &$l) { $l['_score'] = $scoreLead($l); }
            usort($leads, fn($a,$b)=>($b['_score'] <=> $a['_score']));
            $best = $leads[0]['id'] ?? null;
            if ($best) { $keepIds[(int)$best] = true; }
            foreach (array_slice($leads,1) as $l) { $deleteCandidates[(int)$l['id']] = true; }
        }
    };

    // Duplicate phone keys
    $offset = 0;
    while (true) {
        $stmt = $pdo->prepare("SELECT REGEXP_REPLACE(COALESCE(phone,''),'[^0-9]','','g') AS nphone, COUNT(*) FROM leads WHERE COALESCE(phone,'') <> '' GROUP BY nphone HAVING COUNT(*) > 1 ORDER BY COUNT(*) DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', $batchSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $keys = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { if (!empty($row['nphone'])) { $keys[] = $row['nphone']; } }
        if (empty($keys)) { break; }
        $processedPhoneKeys += count($keys);
        $processKeys($keys, 'phone');
        $offset += $batchSize;
    }

    // Duplicate email keys
    $offset = 0;
    while (true) {
        $stmt = $pdo->prepare("SELECT LOWER(TRIM(email)) AS nemail, COUNT(*) FROM leads WHERE COALESCE(email,'') <> '' GROUP BY nemail HAVING COUNT(*) > 1 ORDER BY COUNT(*) DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', $batchSize, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $keys = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { if (!empty($row['nemail'])) { $keys[] = $row['nemail']; } }
        if (empty($keys)) { break; }
        $processedEmailKeys += count($keys);
        $processKeys($keys, 'email');
        $offset += $batchSize;
    }

    $finalDelete = array_values(array_diff(array_keys($deleteCandidates), array_keys($keepIds)));
    $deleted = 0;
    if (!$isDryRun && !empty($finalDelete)) {
        $chunks = array_chunk($finalDelete, 1000);
        foreach ($chunks as $chunk) {
            $in = str_repeat('?,', count($chunk)-1) . '?';
            $sql = "DELETE FROM leads WHERE id IN ($in)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($chunk);
            $deleted += $stmt->rowCount();
        }
    }

    echo json_encode([
        'status' => 'ok',
        'processed_phone_groups' => $processedPhoneKeys,
        'processed_email_groups' => $processedEmailKeys,
        'keepers' => count($keepIds),
        'to_delete' => count($finalDelete),
        'deleted' => $deleted,
        'dry_run' => $isDryRun,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}







