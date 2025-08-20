#!/usr/bin/env php
<?php
/**
 * CHECK CURRENT AUTODIAL CAMPAIGN SETTINGS
 * Shows current settings to compare with proposed changes
 */

$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

function executeViciQuery($query, $viciProxy, $apiKey) {
    $ch = curl_init($viciProxy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         CURRENT AUTODIAL CAMPAIGN SETTINGS CHECK              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get current campaign settings - simplified query
$query = "SELECT * FROM vicidial_campaigns WHERE campaign_id = 'AUTODIAL'";

$result = executeViciQuery($query, $viciProxy, $apiKey);

if (!$result['success'] || empty($result['data'])) {
    echo "❌ Could not retrieve campaign settings\n";
    exit(1);
}

$settings = $result['data'][0];

echo "📊 CURRENT SETTINGS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "DIALING METHOD:\n";
echo "  • Dial Method: " . ($settings['dial_method'] ?? 'NOT SET') . "\n";
echo "  • Auto Dial Level: " . ($settings['auto_dial_level'] ?? 'NOT SET') . "\n";
echo "  • Adaptive Maximum: " . ($settings['adaptive_maximum_level'] ?? 'NOT SET') . "\n";
echo "  • Adaptive Drop %: " . ($settings['adaptive_dropped_percentage'] ?? 'NOT SET') . "\n\n";

echo "HOPPER SETTINGS:\n";
echo "  • Hopper Level: " . ($settings['hopper_level'] ?? 'NOT SET') . " ✅ (You want to keep at 50)\n";
echo "  • Auto Hopper Level: " . ($settings['auto_hopper_level'] ?? 'NOT SET') . "\n\n";

echo "LIST ORDER:\n";
echo "  • List Order Mix: " . ($settings['list_order_mix'] ?? 'NOT SET') . "\n";
echo "  • List Order Secondary: " . ($settings['list_order_secondary'] ?? 'NOT SET') . "\n\n";

echo "LEAD FILTERING:\n";
echo "  • Lead Filter ID: " . ($settings['lead_filter_id'] ?? 'NONE') . "\n";
echo "  • Dial Statuses: " . ($settings['dial_statuses'] ?? 'NOT SET') . "\n\n";

echo "CALL HANDLING:\n";
echo "  • Next Agent Call: " . ($settings['next_agent_call'] ?? 'NOT SET') . "\n";
echo "  • Drop Call Seconds: " . ($settings['drop_call_seconds'] ?? 'NOT SET') . "\n";
echo "  • Drop Action: " . ($settings['drop_action'] ?? 'NOT SET') . "\n";
echo "  • Drop Inbound Group: " . ($settings['drop_inbound_group'] ?? 'NOT SET') . "\n\n";

echo "OTHER:\n";
echo "  • Active: " . ($settings['active'] ?? 'NOT SET') . "\n";
echo "  • Dial Timeout: " . ($settings['dial_timeout'] ?? 'NOT SET') . "\n";
echo "  • Campaign CID: " . ($settings['campaign_cid'] ?? 'NOT SET') . "\n\n";

// Check lead filter details if one exists
if (!empty($settings['lead_filter_id']) && $settings['lead_filter_id'] != 'NONE') {
    $filterQuery = "SELECT lead_filter_id, lead_filter_sql FROM vicidial_lead_filters 
                    WHERE lead_filter_id = '{$settings['lead_filter_id']}'";
    $filterResult = executeViciQuery($filterQuery, $viciProxy, $apiKey);
    
    if ($filterResult['success'] && !empty($filterResult['data'])) {
        echo "CURRENT LEAD FILTER SQL:\n";
        echo "  " . $filterResult['data'][0]['lead_filter_sql'] . "\n\n";
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🔄 PROPOSED CHANGES FOR NEW LEAD FLOW:\n\n";

echo "1. LIST ORDER MIX:\n";
echo "   Current: " . ($settings['list_order_mix'] ?? 'NOT SET') . "\n";
echo "   Proposed: DOWN COUNT\n";
echo "   Impact: " . (($settings['list_order_mix'] == 'DOWN COUNT') ? "✅ NO CHANGE" : "⚠️ WILL CHANGE ORDER") . "\n\n";

echo "2. HOPPER LEVEL:\n";
echo "   Current: " . ($settings['hopper_level'] ?? 'NOT SET') . "\n";
echo "   Proposed: 50 (KEEP AS IS)\n";
echo "   Impact: ✅ NO CHANGE\n\n";

echo "3. LEAD FILTER:\n";
echo "   Current: " . ($settings['lead_filter_id'] ?? 'NONE') . "\n";
echo "   Proposed: Add filter for 'called_since_last_reset = N'\n";
echo "   Impact: " . (empty($settings['lead_filter_id']) || $settings['lead_filter_id'] == 'NONE' ? 
                    "⚠️ NEW FILTER - Will only dial 'ready' leads" : 
                    "⚠️ CHECK CURRENT FILTER") . "\n\n";

echo "4. NEXT AGENT CALL:\n";
echo "   Current: " . ($settings['next_agent_call'] ?? 'NOT SET') . "\n";
echo "   Proposed: oldest_call_finish\n";
echo "   Impact: " . (($settings['next_agent_call'] == 'oldest_call_finish') ? "✅ NO CHANGE" : "⚠️ MINOR CHANGE") . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "⚠️  IMPACT ON CURRENT CALLING:\n\n";

if ($settings['list_order_mix'] != 'DOWN COUNT') {
    echo "• LIST ORDER CHANGE: Will affect which leads are called first\n";
    echo "  - New leads will get priority over older leads\n";
    echo "  - Lists with lower 'list_order' numbers will get priority\n\n";
}

if (empty($settings['lead_filter_id']) || $settings['lead_filter_id'] == 'NONE') {
    echo "• LEAD FILTER: Adding filter will reduce available leads\n";
    echo "  - Only leads with called_since_last_reset='N' will dial\n";
    echo "  - This is controlled by our cron scripts\n\n";
}

echo "💡 RECOMMENDATION:\n";
echo "These changes should be made AFTER current calling hours or\n";
echo "during a scheduled maintenance window to avoid disruption.\n";
