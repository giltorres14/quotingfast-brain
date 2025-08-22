#!/usr/bin/env php
<?php
/**
 * GET CURRENT AUTODIAL CAMPAIGN SETTINGS
 * Uses the correct parameter name for the proxy
 */

$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         CURRENT AUTODIAL CAMPAIGN SETTINGS                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// The correct database is Q6hdjl67GRigMofv (not asterisk)
// The proxy expects 'command' not 'query'
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"SELECT campaign_id, dial_method, hopper_level, list_order_mix, next_agent_call, lead_filter_id, dial_statuses, drop_call_seconds, auto_dial_level FROM vicidial_campaigns WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";

$ch = curl_init($viciProxy);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command])); // Changed from 'query' to 'command'
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ HTTP Error: $httpCode\n";
    exit(1);
}

$result = json_decode($response, true);

if (!$result || !$result['success']) {
    echo "❌ Failed to get campaign settings\n";
    echo "Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    exit(1);
}

if (empty($result['output'])) {
    echo "❌ No output returned\n";
    exit(1);
}

// Parse the tab-separated output
$lines = explode("\n", trim($result['output']));

// Remove SSH warning lines if present
$cleanLines = [];
foreach ($lines as $line) {
    if (strpos($line, 'Could not create directory') === false && 
        strpos($line, 'Failed to add the host') === false &&
        strpos($line, 'Test connection') === false &&
        !empty(trim($line))) {
        $cleanLines[] = $line;
    }
}

if (count($cleanLines) < 2) {
    echo "⚠️ Campaign AUTODIAL might not exist or no data returned\n";
    echo "Raw output: " . $result['output'] . "\n";
    exit(1);
}

// Parse headers and data
$headers = preg_split('/\t/', $cleanLines[0]);
$data = preg_split('/\t/', $cleanLines[1]);

echo "📊 CURRENT AUTODIAL CAMPAIGN SETTINGS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$settings = [];
for ($i = 0; $i < count($headers) && $i < count($data); $i++) {
    $settings[$headers[$i]] = $data[$i];
    echo str_pad($headers[$i] . ":", 25) . $data[$i] . "\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Analyze what would change
echo "🔄 PROPOSED CHANGES FOR NEW LEAD FLOW:\n\n";

// 1. List Order
echo "1. LIST ORDER MIX:\n";
echo "   Current: " . ($settings['list_order_mix'] ?? 'NOT SET') . "\n";
echo "   Proposed: DOWN COUNT\n";
if (isset($settings['list_order_mix']) && $settings['list_order_mix'] == 'DOWN COUNT') {
    echo "   ✅ NO CHANGE NEEDED\n\n";
} else {
    echo "   ⚠️ WILL CHANGE - Affects which leads are called first\n\n";
}

// 2. Hopper Level
echo "2. HOPPER LEVEL:\n";
echo "   Current: " . ($settings['hopper_level'] ?? 'NOT SET') . "\n";
echo "   Proposed: 50 (keep as is)\n";
if (isset($settings['hopper_level']) && $settings['hopper_level'] == '50') {
    echo "   ✅ NO CHANGE NEEDED\n\n";
} else {
    echo "   ⚠️ Currently different from your preferred 50\n\n";
}

// 3. Lead Filter
echo "3. LEAD FILTER:\n";
echo "   Current: " . ($settings['lead_filter_id'] ?? 'NONE') . "\n";
echo "   Proposed: Add filter for 'called_since_last_reset = N'\n";
if (empty($settings['lead_filter_id']) || $settings['lead_filter_id'] == 'NONE') {
    echo "   ⚠️ NEW FILTER NEEDED - Will only dial 'ready' leads\n\n";
} else {
    echo "   ⚠️ Has filter - need to check what it does\n\n";
}

// 4. Dial Method
echo "4. DIAL METHOD:\n";
echo "   Current: " . ($settings['dial_method'] ?? 'NOT SET') . "\n";
echo "   Proposed: Keep current (RATIO or ADAPT_AVERAGE)\n";
echo "   ✅ NO CHANGE NEEDED\n\n";

// 5. Drop Call Seconds
echo "5. DROP CALL SECONDS:\n";
echo "   Current: " . ($settings['drop_call_seconds'] ?? 'NOT SET') . "\n";
echo "   Proposed: 5\n";
if (isset($settings['drop_call_seconds']) && $settings['drop_call_seconds'] == '5') {
    echo "   ✅ NO CHANGE NEEDED\n\n";
} else {
    echo "   ℹ️ Minor adjustment\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Impact assessment
echo "⚠️ IMPACT ON CURRENT CALLING:\n\n";

$hasImpact = false;

if (!isset($settings['list_order_mix']) || $settings['list_order_mix'] != 'DOWN COUNT') {
    echo "• LIST ORDER CHANGE: Will affect lead priority\n";
    echo "  - Newer leads will be called before older ones\n";
    echo "  - Combined with list priorities for optimal flow\n\n";
    $hasImpact = true;
}

if (empty($settings['lead_filter_id']) || $settings['lead_filter_id'] == 'NONE') {
    echo "• LEAD FILTER: Adding filter will restrict available leads\n";
    echo "  - Only leads marked as ready by our scripts will dial\n";
    echo "  - May temporarily reduce hopper if scripts haven't run\n\n";
    $hasImpact = true;
}

if (!$hasImpact) {
    echo "✅ Most settings are already configured correctly!\n";
    echo "   Minimal changes needed for new lead flow.\n\n";
} else {
    echo "💡 RECOMMENDATION:\n";
    echo "   Make these changes after hours or during maintenance\n";
    echo "   to avoid disrupting current calling.\n\n";
}

echo "📝 To implement changes, run the campaign update script\n";
echo "   after current calling hours.\n";




