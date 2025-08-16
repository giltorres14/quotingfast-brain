<?php
// deploy_vici_scripts.php
// Deploy Vici lead flow SQL scripts via proxy

echo "=== DEPLOYING VICI LEAD FLOW SCRIPTS ===\n\n";

// List of scripts to deploy
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
$failed = 0;

// First, create the scripts directory
echo "📁 Creating /opt/vici_scripts directory...\n";
$createDirCmd = "mkdir -p /opt/vici_scripts && echo 'Directory created' || echo 'Failed to create directory'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $createDirCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    echo "   " . ($result['output'] ?? 'Created') . "\n";
} else {
    echo "   ⚠️ Could not create directory (may already exist)\n";
}

echo "\n📤 Deploying SQL scripts...\n";

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/vici_scripts/' . $script;
    
    if (!file_exists($scriptPath)) {
        echo "   ❌ Script not found: $script\n";
        $failed++;
        continue;
    }
    
    echo "   Deploying $script...\n";
    
    // Read the script content
    $content = file_get_contents($scriptPath);
    
    // Escape single quotes for shell command
    $escapedContent = str_replace("'", "'\\''", $content);
    
    // Create the file on Vici server
    $command = "cat > /opt/vici_scripts/$script << 'EOF'\n$content\nEOF";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "   ✅ $script deployed\n";
        $deployed++;
    } else {
        echo "   ❌ Failed to deploy $script: " . ($error ?: "HTTP $httpCode") . "\n";
        $failed++;
    }
}

echo "\n📋 Setting permissions...\n";
$chmodCmd = "chmod 644 /opt/vici_scripts/*.sql 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $chmodCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "   ✅ Permissions set\n";
} else {
    echo "   ⚠️ Could not set permissions\n";
}

echo "\n🔍 Verifying deployment...\n";
$verifyCmd = "ls -la /opt/vici_scripts/*.sql 2>/dev/null | wc -l";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    $fileCount = trim($result['output'] ?? '0');
    echo "   📊 $fileCount SQL files found on server\n";
}

echo "\n=== DEPLOYMENT SUMMARY ===\n";
echo "✅ Deployed: $deployed scripts\n";
echo "❌ Failed: $failed scripts\n";

echo "\n📝 Next steps:\n";
echo "1. SSH to Vici server\n";
echo "2. Review crontab_entries.txt\n";
echo "3. Add cron jobs: crontab -e\n";
echo "4. Test with a single lead in List 101\n";
echo "\nTo test a script manually:\n";
echo "   mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql\n";
echo "\nTo monitor lead flow:\n";
echo "   mysql -u root Q6hdjl67GRigMofv -e 'SELECT * FROM lead_flow_dashboard'\n";


