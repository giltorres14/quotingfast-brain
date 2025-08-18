<?php
// deploy_vici_simple.php
// Simple deployment using base64 encoding to avoid shell escaping issues

echo "=== DEPLOYING VICI LEAD FLOW SCRIPTS (SIMPLE METHOD) ===\n\n";

$scripts = [
    'move_101_102.sql',
    'move_101_103_callbk.sql',
    'move_102_103_workdays.sql',
    'move_103_104_lvm.sql',
    'move_104_105_phase1.sql',
    'move_105_106_lvm.sql',
    'move_106_107_phase2.sql',
    'move_107_108_cooldown.sql',
    'move_108_110_archive.sql',
    'tcpa_compliance_check.sql'
];

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$deployed = 0;

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/vici_scripts/' . $script;
    
    if (!file_exists($scriptPath)) {
        echo "❌ Not found: $script\n";
        continue;
    }
    
    echo "📤 $script... ";
    
    // Read and base64 encode the content
    $content = file_get_contents($scriptPath);
    $encoded = base64_encode($content);
    
    // Use base64 to decode and write the file
    $command = "echo '$encoded' | base64 -d > /opt/vici_scripts/$script && echo 'OK'";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅\n";
        $deployed++;
    } else {
        echo "❌ (HTTP $httpCode)\n";
    }
}

echo "\n✅ Deployed: $deployed/" . count($scripts) . " scripts\n";

// Verify deployment
echo "\n🔍 Verifying...\n";
$verifyCmd = "ls -1 /opt/vici_scripts/*.sql 2>/dev/null | xargs -n1 basename";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo "Files on server:\n";
        echo $result['output'];
    }
}

echo "\n📋 CRON SETUP REQUIRED:\n";
echo "1. SSH to Vici: ssh Superman@66.175.219.105\n";
echo "2. Edit crontab: crontab -e\n";
echo "3. Add entries from vici_scripts/crontab_entries.txt\n";
echo "\nTest command:\n";
echo "mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql\n";


// deploy_vici_simple.php
// Simple deployment using base64 encoding to avoid shell escaping issues

echo "=== DEPLOYING VICI LEAD FLOW SCRIPTS (SIMPLE METHOD) ===\n\n";

$scripts = [
    'move_101_102.sql',
    'move_101_103_callbk.sql',
    'move_102_103_workdays.sql',
    'move_103_104_lvm.sql',
    'move_104_105_phase1.sql',
    'move_105_106_lvm.sql',
    'move_106_107_phase2.sql',
    'move_107_108_cooldown.sql',
    'move_108_110_archive.sql',
    'tcpa_compliance_check.sql'
];

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$deployed = 0;

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/vici_scripts/' . $script;
    
    if (!file_exists($scriptPath)) {
        echo "❌ Not found: $script\n";
        continue;
    }
    
    echo "📤 $script... ";
    
    // Read and base64 encode the content
    $content = file_get_contents($scriptPath);
    $encoded = base64_encode($content);
    
    // Use base64 to decode and write the file
    $command = "echo '$encoded' | base64 -d > /opt/vici_scripts/$script && echo 'OK'";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅\n";
        $deployed++;
    } else {
        echo "❌ (HTTP $httpCode)\n";
    }
}

echo "\n✅ Deployed: $deployed/" . count($scripts) . " scripts\n";

// Verify deployment
echo "\n🔍 Verifying...\n";
$verifyCmd = "ls -1 /opt/vici_scripts/*.sql 2>/dev/null | xargs -n1 basename";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo "Files on server:\n";
        echo $result['output'];
    }
}

echo "\n📋 CRON SETUP REQUIRED:\n";
echo "1. SSH to Vici: ssh Superman@66.175.219.105\n";
echo "2. Edit crontab: crontab -e\n";
echo "3. Add entries from vici_scripts/crontab_entries.txt\n";
echo "\nTest command:\n";
echo "mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql\n";


