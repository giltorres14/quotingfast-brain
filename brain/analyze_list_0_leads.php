<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📊 ANALYZING LIST 0 LEADS (NOT IN DIALER)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules
$listRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0],
    '102' => ['name' => 'First Call', 'min_calls' => 1, 'max_calls' => 1],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999],
];

// Get database connection
$db = \DB::connection()->getPdo();

// Check List 0 leads
echo "📋 LIST 0 OVERVIEW:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list0Query = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(vcm.id) as leads_with_call_data,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,2) as avg_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls,
        MIN(l.created_at) as oldest_lead,
        MAX(l.created_at) as newest_lead
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
";

$list0Stats = $db->query($list0Query)->fetch(PDO::FETCH_ASSOC);

echo "• Total leads in List 0: " . number_format($list0Stats['total_leads']) . "\n";
echo "• Leads with call history: " . number_format($list0Stats['leads_with_call_data']) . "\n";
echo "• Average calls per lead: " . $list0Stats['avg_calls'] . "\n";
echo "• Maximum calls on any lead: " . $list0Stats['max_calls'] . "\n";
echo "• Date range: " . substr($list0Stats['oldest_lead'], 0, 10) . " to " . substr($list0Stats['newest_lead'], 0, 10) . "\n";

// Analyze call distribution for List 0
echo "\n📞 CALL DISTRIBUTION FOR LIST 0 LEADS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$callDistQuery = "
    SELECT 
        CASE 
            WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN '0 calls (New)'
            WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN '1 call'
            WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN '2 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 5 THEN '3-5 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 6 AND 10 THEN '6-10 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 11 AND 15 THEN '11-15 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN '16-20 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 30 THEN '21-30 calls'
            WHEN COALESCE(vcm.call_attempts, 0) > 30 THEN '30+ calls'
        END as call_range,
        COUNT(*) as lead_count,
        ROUND(COUNT(*) * 100.0 / $list0Stats[total_leads], 2) as percentage
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    GROUP BY call_range
    ORDER BY 
        CASE 
            WHEN call_range = '0 calls (New)' THEN 0
            WHEN call_range = '1 call' THEN 1
            WHEN call_range = '2 calls' THEN 2
            WHEN call_range = '3-5 calls' THEN 3
            WHEN call_range = '6-10 calls' THEN 4
            WHEN call_range = '11-15 calls' THEN 5
            WHEN call_range = '16-20 calls' THEN 6
            WHEN call_range = '21-30 calls' THEN 7
            WHEN call_range = '30+ calls' THEN 8
        END
";

$callDist = $db->query($callDistQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-20s | %-12s | %-10s\n", "Call Range", "Lead Count", "Percentage");
echo str_repeat("-", 50) . "\n";

foreach ($callDist as $dist) {
    echo sprintf("%-20s | %11s | %9.2f%%\n",
        $dist['call_range'],
        number_format($dist['lead_count']),
        $dist['percentage']
    );
}

// Show how List 0 leads should be distributed
echo "\n🎯 PROPOSED DISTRIBUTION OF LIST 0 LEADS TO CAMPAIGN LISTS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$proposedDistQuery = "
    SELECT 
        CASE 
            WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 101
            WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 102
            WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 103
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 15 THEN 104
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN 105
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 25 THEN 106
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 26 AND 30 THEN 107
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 31 AND 35 THEN 108
            ELSE 110
        END as proposed_list,
        COUNT(*) as lead_count,
        MIN(COALESCE(vcm.call_attempts, 0)) as min_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,1) as avg_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    GROUP BY proposed_list
    ORDER BY proposed_list
";

$proposedDist = $db->query($proposedDistQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-15s | %-12s | %-15s | %-20s\n", 
    "Target List", "Lead Count", "Avg Calls", "Call Range"
);
echo str_repeat("-", 70) . "\n";

$totalToMove = 0;
foreach ($proposedDist as $dist) {
    $listName = $listRules[$dist['proposed_list']]['name'] ?? 'Unknown';
    echo sprintf("List %-3d %-10s | %11s | %14.1f | %d - %d calls\n",
        $dist['proposed_list'],
        "(" . substr($listName, 0, 8) . ")",
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls']
    );
    $totalToMove += $dist['lead_count'];
}

echo str_repeat("-", 70) . "\n";
echo sprintf("%-15s | %11s\n", "TOTAL", number_format($totalToMove));

// Check for any leads with significant call history
echo "\n📌 LEADS WITH SIGNIFICANT CALL HISTORY IN LIST 0:\n";
echo "-" . str_repeat("-", 70) . "\n";

$highCallQuery = "
    SELECT 
        l.external_lead_id,
        l.name,
        COALESCE(vcm.call_attempts, 0) as calls,
        l.created_at::date as created_date,
        vcm.last_call_time::date as last_call_date
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    AND COALESCE(vcm.call_attempts, 0) > 5
    ORDER BY vcm.call_attempts DESC
    LIMIT 20
";

$highCallLeads = $db->query($highCallQuery)->fetchAll(PDO::FETCH_ASSOC);

if (count($highCallLeads) > 0) {
    echo sprintf("%-15s | %-25s | %-8s | %-12s | %-12s\n",
        "Lead ID", "Name", "Calls", "Created", "Last Call"
    );
    echo str_repeat("-", 80) . "\n";
    
    foreach ($highCallLeads as $lead) {
        echo sprintf("%-15s | %-25s | %7d | %-12s | %-12s\n",
            substr($lead['external_lead_id'], 0, 15),
            substr($lead['name'], 0, 25),
            $lead['calls'],
            $lead['created_date'],
            $lead['last_call_date'] ?? 'N/A'
        );
    }
} else {
    echo "No leads with more than 5 calls found in List 0.\n";
}

echo "\n✅ RECOMMENDATION:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "• Move all " . number_format($totalToMove) . " leads from List 0 to appropriate campaign lists\n";
echo "• Leads with 0 calls → List 101 (Brand New)\n";
echo "• Leads with existing calls → Distribute to Lists 102-110 based on call count\n";
echo "\nWould you like me to create a migration script to move these leads?\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📊 ANALYZING LIST 0 LEADS (NOT IN DIALER)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules
$listRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0],
    '102' => ['name' => 'First Call', 'min_calls' => 1, 'max_calls' => 1],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999],
];

// Get database connection
$db = \DB::connection()->getPdo();

// Check List 0 leads
echo "📋 LIST 0 OVERVIEW:\n";
echo "-" . str_repeat("-", 70) . "\n";

$list0Query = "
    SELECT 
        COUNT(*) as total_leads,
        COUNT(vcm.id) as leads_with_call_data,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,2) as avg_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls,
        MIN(l.created_at) as oldest_lead,
        MAX(l.created_at) as newest_lead
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
";

$list0Stats = $db->query($list0Query)->fetch(PDO::FETCH_ASSOC);

echo "• Total leads in List 0: " . number_format($list0Stats['total_leads']) . "\n";
echo "• Leads with call history: " . number_format($list0Stats['leads_with_call_data']) . "\n";
echo "• Average calls per lead: " . $list0Stats['avg_calls'] . "\n";
echo "• Maximum calls on any lead: " . $list0Stats['max_calls'] . "\n";
echo "• Date range: " . substr($list0Stats['oldest_lead'], 0, 10) . " to " . substr($list0Stats['newest_lead'], 0, 10) . "\n";

// Analyze call distribution for List 0
echo "\n📞 CALL DISTRIBUTION FOR LIST 0 LEADS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$callDistQuery = "
    SELECT 
        CASE 
            WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN '0 calls (New)'
            WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN '1 call'
            WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN '2 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 5 THEN '3-5 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 6 AND 10 THEN '6-10 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 11 AND 15 THEN '11-15 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN '16-20 calls'
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 30 THEN '21-30 calls'
            WHEN COALESCE(vcm.call_attempts, 0) > 30 THEN '30+ calls'
        END as call_range,
        COUNT(*) as lead_count,
        ROUND(COUNT(*) * 100.0 / $list0Stats[total_leads], 2) as percentage
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    GROUP BY call_range
    ORDER BY 
        CASE 
            WHEN call_range = '0 calls (New)' THEN 0
            WHEN call_range = '1 call' THEN 1
            WHEN call_range = '2 calls' THEN 2
            WHEN call_range = '3-5 calls' THEN 3
            WHEN call_range = '6-10 calls' THEN 4
            WHEN call_range = '11-15 calls' THEN 5
            WHEN call_range = '16-20 calls' THEN 6
            WHEN call_range = '21-30 calls' THEN 7
            WHEN call_range = '30+ calls' THEN 8
        END
";

$callDist = $db->query($callDistQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-20s | %-12s | %-10s\n", "Call Range", "Lead Count", "Percentage");
echo str_repeat("-", 50) . "\n";

foreach ($callDist as $dist) {
    echo sprintf("%-20s | %11s | %9.2f%%\n",
        $dist['call_range'],
        number_format($dist['lead_count']),
        $dist['percentage']
    );
}

// Show how List 0 leads should be distributed
echo "\n🎯 PROPOSED DISTRIBUTION OF LIST 0 LEADS TO CAMPAIGN LISTS:\n";
echo "-" . str_repeat("-", 70) . "\n";

$proposedDistQuery = "
    SELECT 
        CASE 
            WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 101
            WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 102
            WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 103
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 15 THEN 104
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN 105
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 25 THEN 106
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 26 AND 30 THEN 107
            WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 31 AND 35 THEN 108
            ELSE 110
        END as proposed_list,
        COUNT(*) as lead_count,
        MIN(COALESCE(vcm.call_attempts, 0)) as min_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,1) as avg_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    GROUP BY proposed_list
    ORDER BY proposed_list
";

$proposedDist = $db->query($proposedDistQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-15s | %-12s | %-15s | %-20s\n", 
    "Target List", "Lead Count", "Avg Calls", "Call Range"
);
echo str_repeat("-", 70) . "\n";

$totalToMove = 0;
foreach ($proposedDist as $dist) {
    $listName = $listRules[$dist['proposed_list']]['name'] ?? 'Unknown';
    echo sprintf("List %-3d %-10s | %11s | %14.1f | %d - %d calls\n",
        $dist['proposed_list'],
        "(" . substr($listName, 0, 8) . ")",
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls']
    );
    $totalToMove += $dist['lead_count'];
}

echo str_repeat("-", 70) . "\n";
echo sprintf("%-15s | %11s\n", "TOTAL", number_format($totalToMove));

// Check for any leads with significant call history
echo "\n📌 LEADS WITH SIGNIFICANT CALL HISTORY IN LIST 0:\n";
echo "-" . str_repeat("-", 70) . "\n";

$highCallQuery = "
    SELECT 
        l.external_lead_id,
        l.name,
        COALESCE(vcm.call_attempts, 0) as calls,
        l.created_at::date as created_date,
        vcm.last_call_time::date as last_call_date
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    AND COALESCE(vcm.call_attempts, 0) > 5
    ORDER BY vcm.call_attempts DESC
    LIMIT 20
";

$highCallLeads = $db->query($highCallQuery)->fetchAll(PDO::FETCH_ASSOC);

if (count($highCallLeads) > 0) {
    echo sprintf("%-15s | %-25s | %-8s | %-12s | %-12s\n",
        "Lead ID", "Name", "Calls", "Created", "Last Call"
    );
    echo str_repeat("-", 80) . "\n";
    
    foreach ($highCallLeads as $lead) {
        echo sprintf("%-15s | %-25s | %7d | %-12s | %-12s\n",
            substr($lead['external_lead_id'], 0, 15),
            substr($lead['name'], 0, 25),
            $lead['calls'],
            $lead['created_date'],
            $lead['last_call_date'] ?? 'N/A'
        );
    }
} else {
    echo "No leads with more than 5 calls found in List 0.\n";
}

echo "\n✅ RECOMMENDATION:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "• Move all " . number_format($totalToMove) . " leads from List 0 to appropriate campaign lists\n";
echo "• Leads with 0 calls → List 101 (Brand New)\n";
echo "• Leads with existing calls → Distribute to Lists 102-110 based on call count\n";
echo "\nWould you like me to create a migration script to move these leads?\n";






