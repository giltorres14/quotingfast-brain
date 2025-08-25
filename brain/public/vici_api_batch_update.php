<?php
// Batch  API updater for Vici using UploadAPI credentials
// Pulls candidates from Vici (vendor_lead_code empty) for given lists,
// matches to Brain by phone (last 10 digits), then calls Vici update_lead
// Endpoint: /vici_api_batch_update.php?lists=6018,6019&limit=75

header('Content-Type: application/json');

function normalize10(?string $raw): string {
    $d = preg_replace('/\D+/', '', (string)$raw);
    if (strlen($d) > 10) { $d = substr($d, -10); }
    return $d ?? '';
}

try {
    $listsParam = isset($_GET['lists']) ? trim($_GET['lists']) : '6018,6019,6020,6021,6022,6023,6024,6025,6026';
    $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 75; // API-only: keep batches reasonable

    $listIds = array_values(array_filter(array_map('intval', explode(',', $listsParam)), fn($v)=>$v>0));
    if (empty($listIds)) { throw new Exception('No valid list ids'); }

    // Brain DB (Postgres)
    $pg = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $phoneToExternal = [];
    $stmt = $pg->query("SELECT external_lead_id, phone FROM leads WHERE external_lead_id IS NOT NULL AND LENGTH(external_lead_id)=13 AND phone IS NOT NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $p10 = normalize10($row['phone'] ?? '');
        if ($p10 !== '') { $phoneToExternal[$p10] = $row['external_lead_id']; }
    }

    // API-only mode: choose phones from Brain and push updates via Non-Agent API across target lists
    $phones = array_keys($phoneToExternal);
    // Simple deterministic slice for repeatability
    $phonesSlice = array_slice($phones, 0, $limit);

    $apiUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
    $apiUser = 'UploadAPI';
    $apiPass = 'ZL8aY2MuQM';

    // Pre-flight: whitelist Render IP via portal (same credentials)
    // Safe best-effort; proceed even if it times out
    $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
    $wl = curl_init($whitelistUrl);
    if ($wl) {
        curl_setopt_array($wl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'user' => $apiUser,
                'pass' => $apiPass,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        curl_exec($wl);
        curl_close($wl);
    }

    // Build curl_multi requests: for each phone, fire one request per list until any succeeds
    $mh = curl_multi_init();
    $handles = [];
    foreach ($phonesSlice as $p10) {
        $eid = $phoneToExternal[$p10] ?? null;
        if (!$eid) continue;
        foreach ($listIds as $lid) {
            $payload = [
                'source' => 'brain-sync',
                'user' => $apiUser,
                'pass' => $apiPass,
                'function' => 'update_lead',
                'search_method' => 'PHONE_NUMBER',
                'phone_number' => $p10,
                'list_id' => $lid,
                'vendor_lead_code' => $eid,
            ];
            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
            ]);
            $key = $p10 . '|' . $lid;
            $handles[$key] = [ 'ch' => $ch, 'phone' => $p10, 'list_id' => $lid, 'eid' => $eid ];
            curl_multi_add_handle($mh, $ch);
        }
    }

    $running = null;
    do {
        $mrc = curl_multi_exec($mh, $running);
        curl_multi_select($mh, 1.0);
    } while ($running > 0 && $mrc == CURLM_OK);

    $updatedPhones = [];
    $errors = 0; $responses = [];
    foreach ($handles as $key => $ctx) {
        $ch = $ctx['ch'];
        $resp = curl_multi_getcontent($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ok = ($code === 200) && is_string($resp) && stripos($resp, 'SUCCESS') !== false;
        if ($ok) {
            $updatedPhones[$ctx['phone']] = true;
            if (count($responses) < 5) {
                $responses[] = [ 'phone'=>$ctx['phone'], 'list_id'=>$ctx['list_id'], 'external_id'=>$ctx['eid'], 'http'=>$code, 'resp'=>substr((string)$resp,0,200) ];
            }
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    echo json_encode([
        'lists' => $listIds,
        'requested' => $limit,
        'attempted' => count($phonesSlice) * count($listIds),
        'updated' => count($updatedPhones),
        'errors' => $errors,
        'samples' => $responses,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}



