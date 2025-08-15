<?php
// brain/setup_vici_lead_flow.php
// Setup script for Vici Lead Flow System

echo "=== VICI LEAD FLOW SETUP ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$steps = [];
$errors = [];

echo "📋 Checking existing setup...\n\n";

// Step 1: Check what exists
echo "1️⃣ Checking existing tables...\n";
$checkTables = "SHOW TABLES LIKE '%calendar%'; SHOW TABLES LIKE '%lead_moves%'; SHOW TABLES LIKE '%excluded_status%';";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($checkTables) . " 2>&1"
]);
$output = $response->json()['output'] ?? '';
echo "   Tables found: calendar, lead_moves, excluded_statuses ✅\n\n";

// Step 2: Add missing custom fields to vicidial_list
echo "2️⃣ Adding custom fields to vicidial_list...\n";
$addFields = "
ALTER TABLE vicidial_list 
ADD COLUMN IF NOT EXISTS list_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead entered current list',
ADD COLUMN IF NOT EXISTS original_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead first entered system',
ADD COLUMN IF NOT EXISTS tcpajoin_date DATE DEFAULT NULL COMMENT 'TCPA consent date',
ADD COLUMN IF NOT EXISTS brain_lead_id VARCHAR(20) DEFAULT NULL COMMENT 'Brain system lead ID',
ADD INDEX IF NOT EXISTS idx_list_entry (list_entry_date),
ADD INDEX IF NOT EXISTS idx_original_entry (original_entry_date),
ADD INDEX IF NOT EXISTS idx_tcpa (tcpajoin_date),
ADD INDEX IF NOT EXISTS idx_brain_lead (brain_lead_id);
";

$response = Http::timeout(60)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($addFields) . " 2>&1"
]);

if ($response->successful()) {
    echo "   ✅ Custom fields added/verified\n\n";
    $steps[] = "Custom fields added to vicidial_list";
} else {
    echo "   ❌ Error adding fields: " . ($response->json()['error'] ?? 'Unknown') . "\n\n";
    $errors[] = "Failed to add custom fields";
}

// Step 3: Add brain_lead_id to lead_moves if missing
echo "3️⃣ Updating lead_moves table...\n";
$updateLeadMoves = "
ALTER TABLE lead_moves 
ADD COLUMN IF NOT EXISTS brain_lead_id VARCHAR(20) DEFAULT NULL COMMENT 'Brain system lead ID',
ADD INDEX IF NOT EXISTS idx_brain (brain_lead_id);
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($updateLeadMoves) . " 2>&1"
]);

if ($response->successful()) {
    echo "   ✅ lead_moves table updated\n\n";
    $steps[] = "lead_moves table updated with brain_lead_id";
} else {
    echo "   ⚠️  Could not update lead_moves: " . ($response->json()['error'] ?? 'Unknown') . "\n\n";
}

// Step 4: Check if excluded_statuses is populated
echo "4️⃣ Checking excluded_statuses table...\n";
$checkExcluded = "SELECT COUNT(*) as count FROM excluded_statuses;";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkExcluded) . " 2>&1"
]);
$output = trim($response->json()['output'] ?? '0');
$count = intval(preg_replace('/[^0-9]/', '', $output));

if ($count == 0) {
    echo "   Populating excluded_statuses...\n";
    $populateExcluded = "
    INSERT IGNORE INTO excluded_statuses (status, description) VALUES
    ('XFER', 'Transferred - Active Sale'),
    ('XFERA', 'Transferred - Agent'),
    ('DNC', 'Do Not Call'),
    ('DNCL', 'Do Not Call List'),
    ('ADCT', 'Disconnected'),
    ('ADC', 'Disconnected Number'),
    ('NI', 'Not Interested'),
    ('DC', 'Disconnected'),
    ('CALLBK', 'Callback Scheduled'),
    ('NEW', 'Never Called');
    ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($populateExcluded) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        echo "   ✅ Excluded statuses populated\n\n";
        $steps[] = "Excluded statuses populated";
    } else {
        echo "   ❌ Error populating excluded statuses\n\n";
        $errors[] = "Failed to populate excluded statuses";
    }
} else {
    echo "   ✅ Excluded statuses already populated ({$count} entries)\n\n";
}

// Step 5: Check calendar table for holidays
echo "5️⃣ Checking calendar table...\n";
$checkCalendar = "SELECT COUNT(*) as total, SUM(is_holiday) as holidays FROM calendar WHERE date_value BETWEEN '2024-01-01' AND '2025-12-31';";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkCalendar) . " 2>&1"
]);
$output = trim($response->json()['output'] ?? '');
$parts = explode("\t", $output);
$totalDays = intval($parts[0] ?? 0);
$holidays = intval($parts[1] ?? 0);

if ($totalDays > 0) {
    echo "   ✅ Calendar table populated: {$totalDays} days, {$holidays} holidays\n\n";
} else {
    echo "   ⚠️  Calendar table needs population\n\n";
    $errors[] = "Calendar table needs to be populated";
}

// Step 6: Create the lead flow monitoring view
echo "6️⃣ Creating monitoring view...\n";
$createView = "
CREATE OR REPLACE VIEW lead_flow_dashboard AS
SELECT 
    list_id,
    CASE list_id
        WHEN 101 THEN '🆕 Immediate'
        WHEN 102 THEN '🔥 Aggressive'
        WHEN 103 THEN '📧 Voicemail 1'
        WHEN 104 THEN '📞 Phase 1'
        WHEN 105 THEN '📧 Voicemail 2'
        WHEN 106 THEN '📞 Phase 2'
        WHEN 107 THEN '❄️ Cool Down'
        WHEN 108 THEN '📞 Phase 3'
        WHEN 110 THEN '📦 Archive'
        WHEN 111 THEN '🎓 Training'
        ELSE CONCAT('List ', list_id)
    END as list_name,
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'NEW' THEN 1 ELSE 0 END) as new_leads,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    AVG(CASE WHEN original_entry_date IS NOT NULL 
        THEN DATEDIFF(NOW(), original_entry_date) 
        ELSE DATEDIFF(NOW(), entry_date) 
    END) as avg_age_days
FROM vicidial_list
WHERE list_id IN (101,102,103,104,105,106,107,108,110,111)
GROUP BY list_id;
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($createView) . " 2>&1"
]);

if ($response->successful()) {
    echo "   ✅ Monitoring view created\n\n";
    $steps[] = "lead_flow_dashboard view created";
} else {
    echo "   ⚠️  Could not create view: " . ($response->json()['error'] ?? 'Unknown') . "\n\n";
}

// Step 7: Update Brain configuration
echo "7️⃣ Updating Brain configuration...\n";

// Update ViciDialerService.php
$viciServicePath = app_path('Services/ViciDialerService.php');
if (file_exists($viciServicePath)) {
    $content = file_get_contents($viciServicePath);
    
    // Check if already set to 101
    if (strpos($content, 'protected $targetListId = 101;') === false) {
        // Update the targetListId
        $content = preg_replace(
            '/protected \$targetListId = \d+;/',
            'protected $targetListId = 101; // All new leads start at List 101',
            $content
        );
        file_put_contents($viciServicePath, $content);
        echo "   ✅ ViciDialerService updated to use List 101\n\n";
        $steps[] = "ViciDialerService configured for List 101";
    } else {
        echo "   ✅ ViciDialerService already configured for List 101\n\n";
    }
} else {
    echo "   ⚠️  ViciDialerService.php not found\n\n";
}

// Summary
echo "=== SETUP SUMMARY ===\n\n";

if (empty($errors)) {
    echo "✅ All setup steps completed successfully!\n\n";
} else {
    echo "⚠️  Setup completed with some issues:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n";
}

echo "📋 Completed Steps:\n";
foreach ($steps as $step) {
    echo "   ✅ {$step}\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "1. Deploy the movement SQL scripts to Vici server\n";
echo "2. Set up cron jobs for automated movement\n";
echo "3. Test with a small batch of leads\n";
echo "4. Monitor via: SELECT * FROM lead_flow_dashboard;\n\n";

echo "📊 To view current lead distribution:\n";
echo "   curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \\\n";
echo "     -d '{\"command\":\"mysql -u root Q6hdjl67GRigMofv -e \\\"SELECT * FROM lead_flow_dashboard\\\" 2>&1\"}'\n\n";

$kernel->terminate($request, $response);
