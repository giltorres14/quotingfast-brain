<?php
// setup_vici_cron.php
// Add cron jobs to Vici server for lead flow automation

echo "=== SETTING UP VICI LEAD FLOW CRON JOBS ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// First, let's backup existing crontab
echo "📋 Backing up existing crontab...\n";
$backupCmd = "crontab -l > /tmp/crontab_backup_" . date('Ymd_His') . ".txt 2>/dev/null || echo 'No existing crontab'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $backupCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo "   ✅ Backup created\n\n";

// Create the complete crontab content
$cronContent = '# Vici Lead Flow Automation - Added ' . date('Y-m-d H:i:s') . '

# ============================================
# EVERY 15 MINUTES - Fast movements
# ============================================

# Move from 101 to 102 after first call
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_flow

# Move CALLBK leads from 101 to 103
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_callbk.sql 2>&1 | logger -t vici_flow

# Move from 103 to 104 after voicemail left
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104_lvm.sql 2>&1 | logger -t vici_flow

# Move from 105 to 106 after second voicemail
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106_lvm.sql 2>&1 | logger -t vici_flow

# ============================================
# DAILY AT 12:01 AM - Workday-based movements
# ============================================

# Move from 102 to 103 after 3 workdays
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103_workdays.sql 2>&1 | logger -t vici_flow

# Move from 104 to 105 after 5 days (Phase 1 complete)
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105_phase1.sql 2>&1 | logger -t vici_flow

# Move from 106 to 107 after 10 days (to Cool Down)
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107_phase2.sql 2>&1 | logger -t vici_flow

# Move from 107 to 108 after 7 days rest
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108_cooldown.sql 2>&1 | logger -t vici_flow

# Move from 108 to 110 after 30 days or TCPA expiry
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_110_archive.sql 2>&1 | logger -t vici_flow

# ============================================
# HOURLY - TCPA Compliance Check
# ============================================

# Emergency TCPA compliance - archive any expired leads
0 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_compliance_check.sql 2>&1 | logger -t vici_tcpa

# End of Vici Lead Flow Automation
';

// Save the new cron content to a temp file
echo "📝 Creating new crontab file...\n";
$tempFile = "/tmp/new_crontab_" . time() . ".txt";

// First get existing crontab (if any) and append our new jobs
$getExistingCmd = "crontab -l 2>/dev/null | grep -v 'vici_flow\\|vici_tcpa\\|vici_scripts' > $tempFile || touch $tempFile";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $getExistingCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

// Append our new cron jobs
$appendCmd = "cat >> $tempFile << 'EOF'
$cronContent
EOF";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $appendCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

echo "   ✅ Crontab file prepared\n\n";

// Install the new crontab
echo "🚀 Installing crontab...\n";
$installCmd = "crontab $tempFile && echo 'Crontab installed successfully' || echo 'Failed to install crontab'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $installCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    echo "   " . ($result['output'] ?? 'Installed') . "\n\n";
} else {
    echo "   ❌ Failed to install (HTTP $httpCode)\n\n";
}

// Verify installation
echo "🔍 Verifying cron jobs...\n";
$verifyCmd = "crontab -l 2>/dev/null | grep -c 'vici_scripts' || echo '0'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    $count = trim($result['output'] ?? '0');
    echo "   ✅ Found $count Vici lead flow cron entries\n\n";
}

// Clean up temp file
$cleanupCmd = "rm -f $tempFile";
$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $cleanupCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

echo "=== CRON SETUP COMPLETE ===\n\n";
echo "✅ Lead flow automation is now active!\n";
echo "📊 Cron jobs will run on schedule:\n";
echo "   - Every 15 minutes: Fast movements\n";
echo "   - Daily at 12:01 AM: Phase transitions\n";
echo "   - Every hour: TCPA compliance\n\n";
echo "🔍 To monitor:\n";
echo "   - View logs: grep vici_flow /var/log/syslog\n";
echo "   - Check dashboard: /admin/vici-lead-flow\n";
echo "   - Test manually: mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql\n";


// setup_vici_cron.php
// Add cron jobs to Vici server for lead flow automation

echo "=== SETTING UP VICI LEAD FLOW CRON JOBS ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// First, let's backup existing crontab
echo "📋 Backing up existing crontab...\n";
$backupCmd = "crontab -l > /tmp/crontab_backup_" . date('Ymd_His') . ".txt 2>/dev/null || echo 'No existing crontab'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $backupCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo "   ✅ Backup created\n\n";

// Create the complete crontab content
$cronContent = '# Vici Lead Flow Automation - Added ' . date('Y-m-d H:i:s') . '

# ============================================
# EVERY 15 MINUTES - Fast movements
# ============================================

# Move from 101 to 102 after first call
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_flow

# Move CALLBK leads from 101 to 103
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_callbk.sql 2>&1 | logger -t vici_flow

# Move from 103 to 104 after voicemail left
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104_lvm.sql 2>&1 | logger -t vici_flow

# Move from 105 to 106 after second voicemail
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106_lvm.sql 2>&1 | logger -t vici_flow

# ============================================
# DAILY AT 12:01 AM - Workday-based movements
# ============================================

# Move from 102 to 103 after 3 workdays
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103_workdays.sql 2>&1 | logger -t vici_flow

# Move from 104 to 105 after 5 days (Phase 1 complete)
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105_phase1.sql 2>&1 | logger -t vici_flow

# Move from 106 to 107 after 10 days (to Cool Down)
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107_phase2.sql 2>&1 | logger -t vici_flow

# Move from 107 to 108 after 7 days rest
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108_cooldown.sql 2>&1 | logger -t vici_flow

# Move from 108 to 110 after 30 days or TCPA expiry
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_110_archive.sql 2>&1 | logger -t vici_flow

# ============================================
# HOURLY - TCPA Compliance Check
# ============================================

# Emergency TCPA compliance - archive any expired leads
0 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_compliance_check.sql 2>&1 | logger -t vici_tcpa

# End of Vici Lead Flow Automation
';

// Save the new cron content to a temp file
echo "📝 Creating new crontab file...\n";
$tempFile = "/tmp/new_crontab_" . time() . ".txt";

// First get existing crontab (if any) and append our new jobs
$getExistingCmd = "crontab -l 2>/dev/null | grep -v 'vici_flow\\|vici_tcpa\\|vici_scripts' > $tempFile || touch $tempFile";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $getExistingCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

// Append our new cron jobs
$appendCmd = "cat >> $tempFile << 'EOF'
$cronContent
EOF";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $appendCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

echo "   ✅ Crontab file prepared\n\n";

// Install the new crontab
echo "🚀 Installing crontab...\n";
$installCmd = "crontab $tempFile && echo 'Crontab installed successfully' || echo 'Failed to install crontab'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $installCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    echo "   " . ($result['output'] ?? 'Installed') . "\n\n";
} else {
    echo "   ❌ Failed to install (HTTP $httpCode)\n\n";
}

// Verify installation
echo "🔍 Verifying cron jobs...\n";
$verifyCmd = "crontab -l 2>/dev/null | grep -c 'vici_scripts' || echo '0'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    $count = trim($result['output'] ?? '0');
    echo "   ✅ Found $count Vici lead flow cron entries\n\n";
}

// Clean up temp file
$cleanupCmd = "rm -f $tempFile";
$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $cleanupCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

echo "=== CRON SETUP COMPLETE ===\n\n";
echo "✅ Lead flow automation is now active!\n";
echo "📊 Cron jobs will run on schedule:\n";
echo "   - Every 15 minutes: Fast movements\n";
echo "   - Daily at 12:01 AM: Phase transitions\n";
echo "   - Every hour: TCPA compliance\n\n";
echo "🔍 To monitor:\n";
echo "   - View logs: grep vici_flow /var/log/syslog\n";
echo "   - Check dashboard: /admin/vici-lead-flow\n";
echo "   - Test manually: mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql\n";






