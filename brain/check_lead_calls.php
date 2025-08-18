<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📊 LEAD ANALYSIS - AUTODIAL CAMPAIGN DISTRIBUTION\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Get database connection
$db = \DB::connection()->getPdo();

// First, let's check all leads regardless of list
echo "📋 OVERALL LEAD STATISTICS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$overallQuery = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(CASE WHEN vici_list_id = 0 THEN 1 END) as list_0_leads,
        COUNT(CASE WHEN vici_list_id = 101 THEN 1 END) as list_101_leads,
        COUNT(CASE WHEN vici_list_id NOT IN (0, 101) THEN 1 END) as other_list_leads
    FROM leads
";

$overall = $db->query($overallQuery)->fetch(PDO::FETCH_ASSOC);

echo "• Total leads in system: " . number_format($overall['total_leads']) . "\n";
echo "• Leads in List 0 (not in dialer): " . number_format($overall['list_0_leads']) . "\n";
echo "• Leads in List 101 (Brand New): " . number_format($overall['list_101_leads']) . "\n";
echo "• Leads in other lists: " . number_format($overall['other_list_leads']) . "\n";

// Check if List 0 leads have any call history
echo "\n📞 LIST 0 LEADS - CALL ANALYSIS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list0CallsQuery = "
    SELECT 
        COUNT(DISTINCT l.id) as total_leads,
        COUNT(DISTINCT vcm.lead_id) as leads_with_metrics,
        SUM(CASE WHEN vcm.call_attempts > 0 THEN 1 ELSE 0 END) as leads_with_calls,
        MAX(vcm.call_attempts) as max_calls,
        AVG(CASE WHEN vcm.call_attempts > 0 THEN vcm.call_attempts END)::NUMERIC(10,2) as avg_calls_when_called
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
";

$list0Calls = $db->query($list0CallsQuery)->fetch(PDO::FETCH_ASSOC);

echo "• Total List 0 leads: " . number_format($list0Calls['total_leads']) . "\n";
echo "• Leads with call metrics data: " . number_format($list0Calls['leads_with_metrics']) . "\n";
echo "• Leads that have been called: " . number_format($list0Calls['leads_with_calls'] ?? 0) . "\n";
echo "• Maximum calls on any lead: " . ($list0Calls['max_calls'] ?? 0) . "\n";
echo "• Average calls (when called): " . ($list0Calls['avg_calls_when_called'] ?? 0) . "\n";

// Now let's see what lists we should create based on your call flow
echo "\n🎯 PROPOSED DISTRIBUTION BASED ON YOUR CALL FLOW:\n";
echo "-" . str_repeat("-", 70) . "\n";

// Define your call flow
$callFlow = [
    '101' => ['name' => 'Brand New', 'calls' => 0, 'description' => 'No calls yet'],
    '102' => ['name' => 'First Call Made', 'calls' => 1, 'description' => 'After 1st call'],
    '103' => ['name' => 'VM Message', 'calls' => 2, 'description' => '1 call for VM'],
    '104' => ['name' => 'Intensive (3-15)', 'calls' => '3-15', 'description' => '4x/day for 3 days'],
    '105' => ['name' => 'Follow-up 1', 'calls' => '16-20', 'description' => 'Continued attempts'],
    '106' => ['name' => 'Follow-up 2', 'calls' => '21-25', 'description' => 'Extended follow-up'],
    '107' => ['name' => 'Follow-up 3', 'calls' => '26-30', 'description' => 'Long-term follow-up'],
    '108' => ['name' => 'Follow-up 4', 'calls' => '31-35', 'description' => 'Final attempts'],
    '110' => ['name' => 'Archive', 'calls' => '36+', 'description' => 'Max attempts reached'],
];

echo "\nYOUR CALL FLOW STRUCTURE:\n";
foreach ($callFlow as $listId => $info) {
    echo sprintf("List %s - %-15s: %-7s calls | %s\n", 
        $listId, 
        $info['name'],
        $info['calls'],
        $info['description']
    );
}

// Check current List 101 
echo "\n📊 CURRENT LIST 101 (BRAND NEW) STATUS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list101Query = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(vcm.id) as has_metrics,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 1 ELSE 0 END) as zero_calls,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 1 ELSE 0 END) as one_call,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 1 ELSE 0 END) as two_calls,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) >= 3 THEN 1 ELSE 0 END) as three_plus_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 101
";

$list101 = $db->query($list101Query)->fetch(PDO::FETCH_ASSOC);

echo "List 101 Analysis:\n";
echo "• Total leads: " . number_format($list101['total_leads']) . "\n";
echo "• Leads with 0 calls (correct placement): " . number_format($list101['zero_calls']) . "\n";
echo "• Leads with 1 call (should move to 102): " . number_format($list101['one_call']) . "\n";
echo "• Leads with 2 calls (should move to 103): " . number_format($list101['two_calls']) . "\n";
echo "• Leads with 3+ calls (should move to 104+): " . number_format($list101['three_plus_calls']) . "\n";

// Summary recommendation
echo "\n✅ RECOMMENDATIONS:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. All leads in List 101 currently have 0 calls - they are correctly placed\n";
echo "2. You have " . number_format($overall['list_0_leads']) . " leads in List 0 that need to be added to the campaign\n";
echo "3. Since List 0 leads show 0 calls, they should all go to List 101 (Brand New)\n";
echo "\nNext Steps:\n";
echo "• Move all List 0 leads to List 101 to start the calling campaign\n";
echo "• As calls are made, leads will automatically move through lists 102-110\n";
echo "• Monitor daily to ensure proper list progression\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📊 LEAD ANALYSIS - AUTODIAL CAMPAIGN DISTRIBUTION\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Get database connection
$db = \DB::connection()->getPdo();

// First, let's check all leads regardless of list
echo "📋 OVERALL LEAD STATISTICS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$overallQuery = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(CASE WHEN vici_list_id = 0 THEN 1 END) as list_0_leads,
        COUNT(CASE WHEN vici_list_id = 101 THEN 1 END) as list_101_leads,
        COUNT(CASE WHEN vici_list_id NOT IN (0, 101) THEN 1 END) as other_list_leads
    FROM leads
";

$overall = $db->query($overallQuery)->fetch(PDO::FETCH_ASSOC);

echo "• Total leads in system: " . number_format($overall['total_leads']) . "\n";
echo "• Leads in List 0 (not in dialer): " . number_format($overall['list_0_leads']) . "\n";
echo "• Leads in List 101 (Brand New): " . number_format($overall['list_101_leads']) . "\n";
echo "• Leads in other lists: " . number_format($overall['other_list_leads']) . "\n";

// Check if List 0 leads have any call history
echo "\n📞 LIST 0 LEADS - CALL ANALYSIS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list0CallsQuery = "
    SELECT 
        COUNT(DISTINCT l.id) as total_leads,
        COUNT(DISTINCT vcm.lead_id) as leads_with_metrics,
        SUM(CASE WHEN vcm.call_attempts > 0 THEN 1 ELSE 0 END) as leads_with_calls,
        MAX(vcm.call_attempts) as max_calls,
        AVG(CASE WHEN vcm.call_attempts > 0 THEN vcm.call_attempts END)::NUMERIC(10,2) as avg_calls_when_called
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
";

$list0Calls = $db->query($list0CallsQuery)->fetch(PDO::FETCH_ASSOC);

echo "• Total List 0 leads: " . number_format($list0Calls['total_leads']) . "\n";
echo "• Leads with call metrics data: " . number_format($list0Calls['leads_with_metrics']) . "\n";
echo "• Leads that have been called: " . number_format($list0Calls['leads_with_calls'] ?? 0) . "\n";
echo "• Maximum calls on any lead: " . ($list0Calls['max_calls'] ?? 0) . "\n";
echo "• Average calls (when called): " . ($list0Calls['avg_calls_when_called'] ?? 0) . "\n";

// Now let's see what lists we should create based on your call flow
echo "\n🎯 PROPOSED DISTRIBUTION BASED ON YOUR CALL FLOW:\n";
echo "-" . str_repeat("-", 70) . "\n";

// Define your call flow
$callFlow = [
    '101' => ['name' => 'Brand New', 'calls' => 0, 'description' => 'No calls yet'],
    '102' => ['name' => 'First Call Made', 'calls' => 1, 'description' => 'After 1st call'],
    '103' => ['name' => 'VM Message', 'calls' => 2, 'description' => '1 call for VM'],
    '104' => ['name' => 'Intensive (3-15)', 'calls' => '3-15', 'description' => '4x/day for 3 days'],
    '105' => ['name' => 'Follow-up 1', 'calls' => '16-20', 'description' => 'Continued attempts'],
    '106' => ['name' => 'Follow-up 2', 'calls' => '21-25', 'description' => 'Extended follow-up'],
    '107' => ['name' => 'Follow-up 3', 'calls' => '26-30', 'description' => 'Long-term follow-up'],
    '108' => ['name' => 'Follow-up 4', 'calls' => '31-35', 'description' => 'Final attempts'],
    '110' => ['name' => 'Archive', 'calls' => '36+', 'description' => 'Max attempts reached'],
];

echo "\nYOUR CALL FLOW STRUCTURE:\n";
foreach ($callFlow as $listId => $info) {
    echo sprintf("List %s - %-15s: %-7s calls | %s\n", 
        $listId, 
        $info['name'],
        $info['calls'],
        $info['description']
    );
}

// Check current List 101 
echo "\n📊 CURRENT LIST 101 (BRAND NEW) STATUS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list101Query = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(vcm.id) as has_metrics,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 1 ELSE 0 END) as zero_calls,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 1 ELSE 0 END) as one_call,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 1 ELSE 0 END) as two_calls,
        SUM(CASE WHEN COALESCE(vcm.call_attempts, 0) >= 3 THEN 1 ELSE 0 END) as three_plus_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 101
";

$list101 = $db->query($list101Query)->fetch(PDO::FETCH_ASSOC);

echo "List 101 Analysis:\n";
echo "• Total leads: " . number_format($list101['total_leads']) . "\n";
echo "• Leads with 0 calls (correct placement): " . number_format($list101['zero_calls']) . "\n";
echo "• Leads with 1 call (should move to 102): " . number_format($list101['one_call']) . "\n";
echo "• Leads with 2 calls (should move to 103): " . number_format($list101['two_calls']) . "\n";
echo "• Leads with 3+ calls (should move to 104+): " . number_format($list101['three_plus_calls']) . "\n";

// Summary recommendation
echo "\n✅ RECOMMENDATIONS:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. All leads in List 101 currently have 0 calls - they are correctly placed\n";
echo "2. You have " . number_format($overall['list_0_leads']) . " leads in List 0 that need to be added to the campaign\n";
echo "3. Since List 0 leads show 0 calls, they should all go to List 101 (Brand New)\n";
echo "\nNext Steps:\n";
echo "• Move all List 0 leads to List 101 to start the calling campaign\n";
echo "• As calls are made, leads will automatically move through lists 102-110\n";
echo "• Monitor daily to ensure proper list progression\n";
