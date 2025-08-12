<?php
/**
 * Vici Whitelist Check - Run this periodically to ensure connectivity
 * 
 * IMPORTANT: Render's egress IP is 3.129.111.220
 * This IP must be whitelisted in Vici for leads to flow
 */

header('Content-Type: application/json');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'render_ip' => '3.129.111.220',
    'checks' => []
];

// Check 1: Get current server IP
$server_ip = file_get_contents('https://api.ipify.org?format=text');
$result['checks']['server_ip'] = [
    'status' => $server_ip ? 'success' : 'failed',
    'value' => trim($server_ip),
    'match' => trim($server_ip) === '3.129.111.220' ? 'matches_expected' : 'different'
];

// Check 2: Test Vici connectivity
$vici_url = 'http://162.241.97.210/vicidial/non_agent_api.php';
$test_params = [
    'source' => 'brain',
    'user' => 'UploadAPI',
    'pass' => '8ZDWGAAQRD',
    'function' => 'version'
];

$ch = curl_init($vici_url . '?' . http_build_query($test_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$vici_response = curl_exec($ch);
$vici_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$vici_error = curl_error($ch);
curl_close($ch);

$result['checks']['vici_connectivity'] = [
    'status' => $vici_http_code === 200 ? 'success' : 'failed',
    'http_code' => $vici_http_code,
    'error' => $vici_error,
    'response' => substr($vici_response, 0, 100)
];

// Check 3: Test add_lead capability (dry run)
$test_lead_params = [
    'source' => 'brain',
    'user' => 'UploadAPI', 
    'pass' => '8ZDWGAAQRD',
    'function' => 'add_lead',
    'list_id' => 101,
    'phone_number' => '6145550000',
    'first_name' => 'WhitelistTest',
    'last_name' => 'DoNotDial',
    'duplicate_check' => 'DUPLIST'  // Check for duplicates only
];

$ch = curl_init($vici_url . '?' . http_build_query($test_lead_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$lead_response = curl_exec($ch);
$lead_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result['checks']['vici_add_lead'] = [
    'status' => strpos($lead_response, 'SUCCESS') !== false || strpos($lead_response, 'DUPLICATE') !== false ? 'success' : 'failed',
    'http_code' => $lead_http_code,
    'response' => substr($lead_response, 0, 100)
];

// Overall status
$all_success = true;
foreach ($result['checks'] as $check) {
    if ($check['status'] !== 'success') {
        $all_success = false;
        break;
    }
}

$result['overall_status'] = $all_success ? 'READY' : 'NOT_READY';

if (!$all_success) {
    $result['action_required'] = [
        'message' => 'Vici integration not working',
        'steps' => [
            '1. Log into Vici admin panel',
            '2. Go to Admin > System Settings > Security',
            '3. Add IP 3.129.111.220 to whitelist',
            '4. Ensure UploadAPI user exists with correct password',
            '5. Verify List 101 exists in AUTODIAL campaign'
        ]
    ];
} else {
    $result['message'] = 'Vici integration is working! Leads will flow to List 101';
}

echo json_encode($result, JSON_PRETTY_PRINT);


