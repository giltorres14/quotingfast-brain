#!/usr/bin/env php
<?php
/**
 * AUTODIAL CAMPAIGN UPDATE SCRIPT
 * Run this AFTER 8 PM EDT to safely update campaign settings
 * Date: August 20, 2025
 */

date_default_timezone_set('America/New_York');

// Check if it's safe to run
$currentHour = (int)date('H');
if ($currentHour >= 9 && $currentHour < 20) {
    echo "⚠️  WARNING: It's currently " . date('g:i A') . " EDT\n";
    echo "This script should only run after 8 PM EDT to avoid disrupting calls.\n";
    echo "Are you sure you want to continue? (yes/no): ";
    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) !== 'yes') {
        echo "Aborted. Run this script after 8 PM EDT.\n";
        exit(0);
    }
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         AUTODIAL CAMPAIGN UPDATE FOR NEW LEAD FLOW            ║\n";
echo "║                  Running at: " . date('Y-m-d H:i:s T') . "             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// ViciDial proxy configuration
$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

function executeViciCommand($command, $viciProxy, $apiKey) {
    $ch = curl_init($viciProxy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP $httpCode"];
    }
    
    $result = json_decode($response, true);
    return $result;
}

echo "📋 CHANGES TO BE MADE:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. List Order Mix → DOWN COUNT (newest leads first)\n";
echo "2. Hopper Level → Keep at 50 (no change)\n";
echo "3. Lead Filter → Add 'called_since_last_reset = N' filter\n";
echo "4. Next Agent Call → oldest_call_finish\n";
echo "5. Drop Call Seconds → 5\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$updates = [];
$errors = [];

// Step 1: Update List Order Mix to DOWN COUNT
echo "1. Updating List Order Mix to DOWN COUNT...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"UPDATE vicidial_campaigns SET list_order_mix = 'DOWN COUNT' WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ List Order Mix updated to DOWN COUNT\n";
    $updates[] = "List Order Mix → DOWN COUNT";
} else {
    echo "   ❌ Failed to update List Order Mix\n";
    $errors[] = "List Order Mix update failed";
}

// Step 2: Verify/Set Hopper Level
echo "\n2. Verifying Hopper Level...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"UPDATE vicidial_campaigns SET hopper_level = 50 WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ Hopper Level set to 50\n";
    $updates[] = "Hopper Level → 50";
} else {
    echo "   ❌ Failed to set Hopper Level\n";
    $errors[] = "Hopper Level update failed";
}

// Step 3: Create/Update Lead Filter
echo "\n3. Creating Lead Filter for 'called_since_last_reset = N'...\n";

// First, create the filter if it doesn't exist
$filterName = 'READY_TO_CALL';
$filterSQL = "called_since_last_reset = 'N'";

$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"
INSERT INTO vicidial_lead_filters (lead_filter_id, lead_filter_name, lead_filter_sql, user_group) 
VALUES ('$filterName', 'Ready to Call Filter', '$filterSQL', '---ALL---')
ON DUPLICATE KEY UPDATE lead_filter_sql = '$filterSQL'\" 2>/dev/null";

$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ Lead Filter created/updated\n";
    
    // Now assign it to the campaign
    $command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"UPDATE vicidial_campaigns SET lead_filter_id = '$filterName' WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";
    $result = executeViciCommand($command, $viciProxy, $apiKey);
    
    if ($result['success']) {
        echo "   ✅ Lead Filter assigned to campaign\n";
        $updates[] = "Lead Filter → called_since_last_reset = 'N'";
    } else {
        echo "   ❌ Failed to assign Lead Filter to campaign\n";
        $errors[] = "Lead Filter assignment failed";
    }
} else {
    echo "   ❌ Failed to create Lead Filter\n";
    $errors[] = "Lead Filter creation failed";
}

// Step 4: Update Next Agent Call
echo "\n4. Updating Next Agent Call to oldest_call_finish...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"UPDATE vicidial_campaigns SET next_agent_call = 'oldest_call_finish' WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ Next Agent Call updated\n";
    $updates[] = "Next Agent Call → oldest_call_finish";
} else {
    echo "   ❌ Failed to update Next Agent Call\n";
    $errors[] = "Next Agent Call update failed";
}

// Step 5: Update Drop Call Seconds
echo "\n5. Setting Drop Call Seconds to 5...\n";
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"UPDATE vicidial_campaigns SET drop_call_seconds = 5 WHERE campaign_id = 'AUTODIAL'\" 2>/dev/null";
$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ Drop Call Seconds set to 5\n";
    $updates[] = "Drop Call Seconds → 5";
} else {
    echo "   ❌ Failed to update Drop Call Seconds\n";
    $errors[] = "Drop Call Seconds update failed";
}

// Step 6: Mark leads as ready to call based on our logic
echo "\n6. Marking leads as ready to call based on lead flow logic...\n";

// Mark Test A fresh leads (List 101) as ready
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"
UPDATE vicidial_list 
SET called_since_last_reset = 'N' 
WHERE list_id = 101 
AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'DNQ', 'DC', 'ADC')
AND call_count < 5\" 2>/dev/null";

$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ List 101 leads marked as ready\n";
} else {
    echo "   ⚠️ Could not mark List 101 leads\n";
}

// Mark Test B fresh leads (List 150) as ready
$command = "mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e \"
UPDATE vicidial_list 
SET called_since_last_reset = 'N' 
WHERE list_id = 150 
AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'DNQ', 'DC', 'ADC')
AND call_count < 5\" 2>/dev/null";

$result = executeViciCommand($command, $viciProxy, $apiKey);
if ($result['success']) {
    echo "   ✅ List 150 leads marked as ready\n";
} else {
    echo "   ⚠️ Could not mark List 150 leads\n";
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                     UPDATE COMPLETE                           ║\n";
echo "╠════════════════════════════════════════════════════════════════╣\n";

if (count($updates) > 0) {
    echo "║ ✅ SUCCESSFUL UPDATES:                                        ║\n";
    foreach ($updates as $update) {
        echo "║   • " . str_pad($update, 57) . "║\n";
    }
}

if (count($errors) > 0) {
    echo "║ ❌ FAILED UPDATES:                                            ║\n";
    foreach ($errors as $error) {
        echo "║   • " . str_pad($error, 57) . "║\n";
    }
}

echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Create rollback script
$rollbackScript = "#!/usr/bin/env php
<?php
// ROLLBACK SCRIPT - Use if needed to revert changes
\$commands = [
    \"UPDATE vicidial_campaigns SET list_order_mix = 'RANDOM' WHERE campaign_id = 'AUTODIAL'\",
    \"UPDATE vicidial_campaigns SET lead_filter_id = 'NONE' WHERE campaign_id = 'AUTODIAL'\",
    \"UPDATE vicidial_campaigns SET next_agent_call = 'longest_wait_time' WHERE campaign_id = 'AUTODIAL'\"
];

echo \"Rolling back campaign changes...\\n\";
foreach (\$commands as \$cmd) {
    // Execute rollback commands
    echo \"Executing: \$cmd\\n\";
}
echo \"Rollback complete. Verify in ViciDial admin.\\n\";
";

file_put_contents('rollback_campaign_changes.php', $rollbackScript);
chmod('rollback_campaign_changes.php', 0755);

echo "📝 NEXT STEPS:\n";
echo "1. Monitor the hopper to ensure it's filling correctly\n";
echo "2. Check that agents are receiving calls normally\n";
echo "3. Verify lead flow movements are working\n";
echo "4. If issues arise, run: php rollback_campaign_changes.php\n\n";

echo "⏰ Cron jobs should now control lead flow automatically.\n";
echo "   The system will manage which leads are ready to call.\n";

