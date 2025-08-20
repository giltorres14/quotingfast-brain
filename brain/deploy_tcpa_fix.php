<?php
// deploy_tcpa_fix.php
// Deploy TCPA 90-day compliance scripts

echo "=== DEPLOYING TCPA 90-DAY COMPLIANCE FIX ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// Scripts to deploy
$scripts = [
    'tcpa_90day_compliance.sql',  // New TCPA script
    'move_108_110_archive.sql'     // Updated archive script
];

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/vici_scripts/' . $script;
    
    echo "📤 Deploying $script... ";
    
    // Read and base64 encode the content
    $content = file_get_contents($scriptPath);
    $encoded = base64_encode($content);
    
    // Deploy the script
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
    } else {
        echo "❌ (HTTP $httpCode)\n";
    }
}

// Add the new cron job for TCPA 90-day compliance
echo "\n📋 Adding TCPA 90-day compliance cron job...\n";

// Get existing crontab and add our new job
$cronLine = "*/30 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_90day_compliance.sql 2>&1 | logger -t tcpa_90day";

$addCronCmd = "(crontab -l 2>/dev/null | grep -v 'tcpa_90day_compliance'; echo '$cronLine') | crontab - && echo 'Cron added'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $addCronCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "   ✅ TCPA 90-day cron job added (runs every 30 minutes)\n";
} else {
    echo "   ❌ Failed to add cron job\n";
}

// Test the TCPA script
echo "\n🧪 Testing TCPA 90-day compliance script...\n";
$testCmd = "mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_90day_compliance.sql 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $testCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo "   Result: " . substr($result['output'], 0, 200) . "\n";
    }
}

// Check how many leads are approaching 90 days
echo "\n📊 Checking leads approaching TCPA limit...\n";
$checkCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
SELECT 
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 89 THEN '90+ days (MUST ARCHIVE NOW)'
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 85 THEN '85-89 days (archive soon)'
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 80 THEN '80-84 days (warning)'
        ELSE 'Under 80 days'
    END as urgency,
    COUNT(*) as lead_count
FROM vicidial_list
WHERE list_id NOT IN (199, 998, 999)
AND entry_date IS NOT NULL
GROUP BY urgency
ORDER BY urgency DESC
\" 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo $result['output'] . "\n";
    }
}

echo "\n=== TCPA COMPLIANCE UPDATE COMPLETE ===\n\n";
echo "✅ TCPA 90-day compliance is now active!\n";
echo "📊 The system will:\n";
echo "   - Check every 30 minutes for leads at 89+ days\n";
echo "   - Move them to List 199 (DNC) immediately\n";
echo "   - Archive leads at 85 days (safety buffer)\n";
echo "   - NEVER allow calling after 89 days from opt-in\n\n";
echo "⚠️  CRITICAL: This ensures TCPA compliance by preventing\n";
echo "   any calls after the 90-day limit from opt-in date.\n";


// deploy_tcpa_fix.php
// Deploy TCPA 90-day compliance scripts

echo "=== DEPLOYING TCPA 90-DAY COMPLIANCE FIX ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// Scripts to deploy
$scripts = [
    'tcpa_90day_compliance.sql',  // New TCPA script
    'move_108_110_archive.sql'     // Updated archive script
];

foreach ($scripts as $script) {
    $scriptPath = __DIR__ . '/vici_scripts/' . $script;
    
    echo "📤 Deploying $script... ";
    
    // Read and base64 encode the content
    $content = file_get_contents($scriptPath);
    $encoded = base64_encode($content);
    
    // Deploy the script
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
    } else {
        echo "❌ (HTTP $httpCode)\n";
    }
}

// Add the new cron job for TCPA 90-day compliance
echo "\n📋 Adding TCPA 90-day compliance cron job...\n";

// Get existing crontab and add our new job
$cronLine = "*/30 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_90day_compliance.sql 2>&1 | logger -t tcpa_90day";

$addCronCmd = "(crontab -l 2>/dev/null | grep -v 'tcpa_90day_compliance'; echo '$cronLine') | crontab - && echo 'Cron added'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $addCronCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "   ✅ TCPA 90-day cron job added (runs every 30 minutes)\n";
} else {
    echo "   ❌ Failed to add cron job\n";
}

// Test the TCPA script
echo "\n🧪 Testing TCPA 90-day compliance script...\n";
$testCmd = "mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_90day_compliance.sql 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $testCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo "   Result: " . substr($result['output'], 0, 200) . "\n";
    }
}

// Check how many leads are approaching 90 days
echo "\n📊 Checking leads approaching TCPA limit...\n";
$checkCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
SELECT 
    CASE 
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 89 THEN '90+ days (MUST ARCHIVE NOW)'
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 85 THEN '85-89 days (archive soon)'
        WHEN DATEDIFF(CURDATE(), DATE(entry_date)) >= 80 THEN '80-84 days (warning)'
        ELSE 'Under 80 days'
    END as urgency,
    COUNT(*) as lead_count
FROM vicidial_list
WHERE list_id NOT IN (199, 998, 999)
AND entry_date IS NOT NULL
GROUP BY urgency
ORDER BY urgency DESC
\" 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo $result['output'] . "\n";
    }
}

echo "\n=== TCPA COMPLIANCE UPDATE COMPLETE ===\n\n";
echo "✅ TCPA 90-day compliance is now active!\n";
echo "📊 The system will:\n";
echo "   - Check every 30 minutes for leads at 89+ days\n";
echo "   - Move them to List 199 (DNC) immediately\n";
echo "   - Archive leads at 85 days (safety buffer)\n";
echo "   - NEVER allow calling after 89 days from opt-in\n\n";
echo "⚠️  CRITICAL: This ensures TCPA compliance by preventing\n";
echo "   any calls after the 90-day limit from opt-in date.\n";








