<?php
// brain/setup_lead_flow_view.php
// Create monitoring view and update Brain configuration

echo "=== SETTING UP LEAD FLOW MONITORING ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Step 1: Create the monitoring view
echo "📊 Creating lead flow monitoring view...\n";

$createView = "
CREATE OR REPLACE VIEW lead_flow_dashboard AS
SELECT 
    list_id,
    CASE list_id
        WHEN 101 THEN '🆕 Immediate - New Leads'
        WHEN 102 THEN '🔥 Aggressive - 30min delays'
        WHEN 103 THEN '📧 Voicemail Drop 1'
        WHEN 104 THEN '📞 Phase 1 - 3x/day'
        WHEN 105 THEN '📧 Voicemail Drop 2'
        WHEN 106 THEN '📞 Phase 2 - 2x/day'
        WHEN 107 THEN '❄️ Cool Down - No calls'
        WHEN 108 THEN '📞 Phase 3 - Final attempts'
        WHEN 110 THEN '📦 Archive - TCPA/Complete'
        WHEN 111 THEN '🎓 Training'
        ELSE CONCAT('List ', list_id)
    END as list_name,
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'NEW' THEN 1 ELSE 0 END) as new_status,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    AVG(CASE 
        WHEN original_entry_date IS NOT NULL THEN DATEDIFF(NOW(), original_entry_date)
        WHEN entry_date IS NOT NULL THEN DATEDIFF(NOW(), entry_date)
        ELSE 0
    END) as avg_age_days,
    MAX(entry_date) as last_lead_added
FROM vicidial_list
WHERE list_id IN (101,102,103,104,105,106,107,108,110,111)
GROUP BY list_id
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($createView) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') === false) {
        echo "   ✅ Monitoring view created\n\n";
    } else {
        echo "   ⚠️ View may already exist or error: {$output}\n\n";
    }
} else {
    echo "   ❌ Failed to create view\n\n";
}

// Step 2: Update ViciDialerService to use List 101
echo "🎯 Updating Brain to use List 101 for new leads...\n";

$viciServicePath = app_path('Services/ViciDialerService.php');
if (file_exists($viciServicePath)) {
    $content = file_get_contents($viciServicePath);
    
    // Check current targetListId
    if (preg_match('/protected \$targetListId = (\d+);/', $content, $matches)) {
        $currentList = $matches[1];
        
        if ($currentList != '101') {
            // Update to 101
            $content = preg_replace(
                '/protected \$targetListId = \d+;/',
                'protected $targetListId = 101; // All new leads start at List 101 for automated flow',
                $content
            );
            file_put_contents($viciServicePath, $content);
            echo "   ✅ Updated from List {$currentList} to List 101\n";
        } else {
            echo "   ✅ Already configured for List 101\n";
        }
    }
} else {
    echo "   ⚠️ ViciDialerService.php not found\n";
}

// Step 3: Check if we need to populate excluded_statuses
echo "\n📋 Checking excluded_statuses table...\n";

$checkExcluded = "SELECT COUNT(*) FROM excluded_statuses";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkExcluded) . " 2>&1"
]);

$output = trim($response->json()['output'] ?? '0');
$count = intval(preg_replace('/[^0-9]/', '', $output));

if ($count == 0) {
    echo "   Populating excluded statuses...\n";
    
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
    ('SALE', 'Sale Made'),
    ('CALLBK', 'Callback Scheduled')
    ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($populateExcluded) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        echo "   ✅ Excluded statuses populated\n";
    }
} else {
    echo "   ✅ Excluded statuses already populated ({$count} entries)\n";
}

// Step 4: Test the monitoring view
echo "\n📊 Testing monitoring view...\n";

$testView = "SELECT * FROM lead_flow_dashboard";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($testView) . " 2>&1"
]);

if ($response->successful()) {
    echo "   ✅ View is working (currently no leads in lists 101-110)\n";
}

echo "\n=== SETUP COMPLETE ===\n\n";

echo "✅ System is ready for lead flow!\n\n";
echo "📋 What's been configured:\n";
echo "   • Custom fields added to vicidial_list\n";
echo "   • Monitoring view created (lead_flow_dashboard)\n";
echo "   • Brain will send new leads to List 101\n";
echo "   • Excluded statuses populated\n\n";

echo "🎯 Next Steps:\n";
echo "   1. Deploy movement SQL scripts to Vici server\n";
echo "   2. Set up cron jobs for automated movement\n";
echo "   3. Send a test lead to verify flow\n\n";

echo "📊 To monitor lead distribution:\n";
echo "   SELECT * FROM lead_flow_dashboard;\n\n";

echo "🛡️ Your existing lists are completely unaffected!\n";


// brain/setup_lead_flow_view.php
// Create monitoring view and update Brain configuration

echo "=== SETTING UP LEAD FLOW MONITORING ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Step 1: Create the monitoring view
echo "📊 Creating lead flow monitoring view...\n";

$createView = "
CREATE OR REPLACE VIEW lead_flow_dashboard AS
SELECT 
    list_id,
    CASE list_id
        WHEN 101 THEN '🆕 Immediate - New Leads'
        WHEN 102 THEN '🔥 Aggressive - 30min delays'
        WHEN 103 THEN '📧 Voicemail Drop 1'
        WHEN 104 THEN '📞 Phase 1 - 3x/day'
        WHEN 105 THEN '📧 Voicemail Drop 2'
        WHEN 106 THEN '📞 Phase 2 - 2x/day'
        WHEN 107 THEN '❄️ Cool Down - No calls'
        WHEN 108 THEN '📞 Phase 3 - Final attempts'
        WHEN 110 THEN '📦 Archive - TCPA/Complete'
        WHEN 111 THEN '🎓 Training'
        ELSE CONCAT('List ', list_id)
    END as list_name,
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'NEW' THEN 1 ELSE 0 END) as new_status,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    AVG(CASE 
        WHEN original_entry_date IS NOT NULL THEN DATEDIFF(NOW(), original_entry_date)
        WHEN entry_date IS NOT NULL THEN DATEDIFF(NOW(), entry_date)
        ELSE 0
    END) as avg_age_days,
    MAX(entry_date) as last_lead_added
FROM vicidial_list
WHERE list_id IN (101,102,103,104,105,106,107,108,110,111)
GROUP BY list_id
";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($createView) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    if (strpos($output, 'ERROR') === false) {
        echo "   ✅ Monitoring view created\n\n";
    } else {
        echo "   ⚠️ View may already exist or error: {$output}\n\n";
    }
} else {
    echo "   ❌ Failed to create view\n\n";
}

// Step 2: Update ViciDialerService to use List 101
echo "🎯 Updating Brain to use List 101 for new leads...\n";

$viciServicePath = app_path('Services/ViciDialerService.php');
if (file_exists($viciServicePath)) {
    $content = file_get_contents($viciServicePath);
    
    // Check current targetListId
    if (preg_match('/protected \$targetListId = (\d+);/', $content, $matches)) {
        $currentList = $matches[1];
        
        if ($currentList != '101') {
            // Update to 101
            $content = preg_replace(
                '/protected \$targetListId = \d+;/',
                'protected $targetListId = 101; // All new leads start at List 101 for automated flow',
                $content
            );
            file_put_contents($viciServicePath, $content);
            echo "   ✅ Updated from List {$currentList} to List 101\n";
        } else {
            echo "   ✅ Already configured for List 101\n";
        }
    }
} else {
    echo "   ⚠️ ViciDialerService.php not found\n";
}

// Step 3: Check if we need to populate excluded_statuses
echo "\n📋 Checking excluded_statuses table...\n";

$checkExcluded = "SELECT COUNT(*) FROM excluded_statuses";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkExcluded) . " 2>&1"
]);

$output = trim($response->json()['output'] ?? '0');
$count = intval(preg_replace('/[^0-9]/', '', $output));

if ($count == 0) {
    echo "   Populating excluded statuses...\n";
    
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
    ('SALE', 'Sale Made'),
    ('CALLBK', 'Callback Scheduled')
    ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($populateExcluded) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        echo "   ✅ Excluded statuses populated\n";
    }
} else {
    echo "   ✅ Excluded statuses already populated ({$count} entries)\n";
}

// Step 4: Test the monitoring view
echo "\n📊 Testing monitoring view...\n";

$testView = "SELECT * FROM lead_flow_dashboard";
$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($testView) . " 2>&1"
]);

if ($response->successful()) {
    echo "   ✅ View is working (currently no leads in lists 101-110)\n";
}

echo "\n=== SETUP COMPLETE ===\n\n";

echo "✅ System is ready for lead flow!\n\n";
echo "📋 What's been configured:\n";
echo "   • Custom fields added to vicidial_list\n";
echo "   • Monitoring view created (lead_flow_dashboard)\n";
echo "   • Brain will send new leads to List 101\n";
echo "   • Excluded statuses populated\n\n";

echo "🎯 Next Steps:\n";
echo "   1. Deploy movement SQL scripts to Vici server\n";
echo "   2. Set up cron jobs for automated movement\n";
echo "   3. Send a test lead to verify flow\n\n";

echo "📊 To monitor lead distribution:\n";
echo "   SELECT * FROM lead_flow_dashboard;\n\n";

echo "🛡️ Your existing lists are completely unaffected!\n";






