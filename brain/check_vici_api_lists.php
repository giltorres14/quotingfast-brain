<?php

echo "=== CHECKING VICI LISTS VIA API ===\n\n";

// Vici API credentials
$baseUrl = 'https://philli.callix.ai/vicidial/non_agent_api.php';
$apiUser = 'apiuser';
$apiPass = 'UZPATJ59GJAVKG8ES6';

// Function to make API call
function callViciApi($function, $params = []) {
    global $baseUrl, $apiUser, $apiPass;
    
    $params['source'] = 'test';
    $params['user'] = $apiUser;
    $params['pass'] = $apiPass;
    $params['function'] = $function;
    $params['agent_user'] = $apiUser;
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response
    ];
}

// 1. Check version/connection
echo "1. Testing API connection...\n";
$result = callViciApi('version');
echo "Response: " . substr($result['response'], 0, 200) . "\n\n";

// 2. Try to get campaign stats
echo "2. Checking campaign stats...\n";
$result = callViciApi('agent_campaigns', [
    'stage' => 'csv',
    'header' => 'YES'
]);
echo "Response: " . substr($result['response'], 0, 500) . "\n\n";

// 3. Try list_info function
echo "3. Checking list info for list 101...\n";
$result = callViciApi('list_info', [
    'list_id' => '101',
    'stage' => 'csv',
    'header' => 'YES'
]);
echo "Response: " . substr($result['response'], 0, 500) . "\n\n";

// 4. Try to search for a specific phone in list 101
echo "4. Searching for a phone in list 101...\n";
$result = callViciApi('search_phone_list', [
    'phone_number' => '8323122030',
    'list_id' => '101'
]);
echo "Response: " . $result['response'] . "\n\n";

// 5. Try to get lead info using list_export_calls_report
echo "5. Trying list_export_calls_report for Autodial campaign...\n";
$result = callViciApi('list_export_calls_report', [
    'campaign_id' => 'Autodial',
    'date' => date('Y-m-d'),
    'stage' => 'csv',
    'header' => 'YES'
]);
echo "Response (first 1000 chars): " . substr($result['response'], 0, 1000) . "\n\n";

// 6. Try for Auto2 campaign
echo "6. Trying list_export_calls_report for Auto2 campaign...\n";
$result = callViciApi('list_export_calls_report', [
    'campaign_id' => 'Auto2',
    'date' => date('Y-m-d'),
    'stage' => 'csv',
    'header' => 'YES'
]);
echo "Response (first 1000 chars): " . substr($result['response'], 0, 1000) . "\n\n";

// 7. Try to get leads from all lists (using wildcard or ALL)
echo "7. Trying to search across all lists...\n";
$result = callViciApi('search_phone_list', [
    'phone_number' => '8323122030',
    'list_id' => 'ALL'
]);
echo "Response: " . $result['response'] . "\n\n";

// 8. Check what lists are in specific campaigns
echo "8. Checking campaign list mappings...\n";
$campaigns = ['Autodial', 'Auto2', 'HEALTH', 'Blended'];
foreach ($campaigns as $campaign) {
    echo "\nCampaign: $campaign\n";
    $result = callViciApi('campaign_stats', [
        'campaign_id' => $campaign,
        'stage' => 'csv',
        'header' => 'YES'
    ]);
    if (strpos($result['response'], 'ERROR') === false) {
        echo "✅ Campaign exists\n";
        // Try to get more info
        $listResult = callViciApi('list_export_calls_report', [
            'campaign_id' => $campaign,
            'date' => '2025-01-14',
            'stage' => 'csv',
            'header' => 'YES'
        ]);
        $lines = explode("\n", $listResult['response']);
        if (count($lines) > 1) {
            echo "Has " . (count($lines) - 1) . " records today\n";
        }
    } else {
        echo "❌ " . substr($result['response'], 0, 100) . "\n";
    }
}

echo "\n=== API CHECK COMPLETE ===\n";


