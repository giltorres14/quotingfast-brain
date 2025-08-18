<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📞 LEAD DISTRIBUTION ANALYSIS BY CALL COUNT\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules based on your specification
$listRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0, 'description' => 'No calls yet'],
    '102' => ['name' => 'First Call', 'min_calls' => 1, 'max_calls' => 1, 'description' => 'After 1st call'],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2, 'description' => '1 call for VM'],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15, 'description' => '4x/day for 3 days'],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20, 'description' => 'Next phase'],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25, 'description' => 'Continued follow-up'],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30, 'description' => 'Extended follow-up'],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35, 'description' => 'Long-term follow-up'],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999, 'description' => 'Many attempts'],
];

echo "📋 CALL FLOW RULES:\n";
echo "-" . str_repeat("-", 70) . "\n";
foreach ($listRules as $listId => $rule) {
    echo sprintf("List %s - %-15s: %2d-%-3d calls | %s\n", 
        $listId, 
        $rule['name'],
        $rule['min_calls'],
        $rule['max_calls'] == 999 ? '∞' : $rule['max_calls'],
        $rule['description']
    );
}

// Get database connection
$db = \DB::connection()->getPdo();

// First, let's see current distribution in Autodial campaign
echo "\n\n📊 CURRENT DISTRIBUTION IN AUTODIAL CAMPAIGN:\n";
echo "-" . str_repeat("-", 70) . "\n";

$currentQuery = "
    SELECT 
        l.vici_list_id,
        COUNT(*) as lead_count,
        COUNT(vcm.id) as leads_with_calls,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,1) as avg_calls,
        MIN(COALESCE(vcm.call_attempts, 0)) as min_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id IS NOT NULL
    AND l.vici_list_id > 0  -- Exclude list 0 (not in dialer)
    GROUP BY l.vici_list_id
    ORDER BY l.vici_list_id
";

$currentDist = $db->query($currentQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-10s | %-12s | %-15s | %-20s\n", 
    "List ID", "Lead Count", "Avg Calls", "Call Range"
);
echo str_repeat("-", 70) . "\n";

foreach ($currentDist as $dist) {
    echo sprintf("List %-5d | %11s | %14.1f | %d - %d calls\n",
        $dist['vici_list_id'],
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls']
    );
}

// Now analyze how leads SHOULD be distributed based on call count
echo "\n\n🎯 PROPOSED REDISTRIBUTION BASED ON CALL COUNT:\n";
echo "-" . str_repeat("-", 70) . "\n";

$redistributionQuery = "
    WITH lead_call_data AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            l.created_at,
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
            END as proposed_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
        AND l.vici_list_id > 0
    )
    SELECT 
        proposed_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls,
        AVG(call_count)::NUMERIC(10,1) as avg_calls,
        COUNT(CASE WHEN current_list != proposed_list THEN 1 END) as needs_moving
    FROM lead_call_data
    GROUP BY proposed_list
    ORDER BY proposed_list
";

$redistribution = $db->query($redistributionQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-10s | %-12s | %-15s | %-20s | %-12s\n", 
    "New List", "Lead Count", "Avg Calls", "Call Range", "Need Moving"
);
echo str_repeat("-", 90) . "\n";

$totalLeads = 0;
$totalNeedMoving = 0;

foreach ($redistribution as $dist) {
    $listName = $listRules[$dist['proposed_list']]['name'] ?? 'Unknown';
    echo sprintf("%-10s | %11s | %14.1f | %2d - %-14d | %11s\n",
        $dist['proposed_list'] . " " . substr($listName, 0, 7),
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls'],
        number_format($dist['needs_moving'])
    );
    $totalLeads += $dist['lead_count'];
    $totalNeedMoving += $dist['needs_moving'];
}

echo str_repeat("-", 90) . "\n";
echo sprintf("%-10s | %11s | %-15s | %-20s | %11s\n",
    "TOTAL",
    number_format($totalLeads),
    "",
    "",
    number_format($totalNeedMoving)
);

// Show specific examples of leads that need to be moved
echo "\n\n📝 SAMPLE LEADS THAT NEED TO BE MOVED:\n";
echo "-" . str_repeat("-", 70) . "\n";

$sampleQuery = "
    WITH lead_movements AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.name,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
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
            END as proposed_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
        AND l.vici_list_id > 0
    )
    SELECT *
    FROM lead_movements
    WHERE current_list != proposed_list
    ORDER BY call_count DESC
    LIMIT 20
";

$samples = $db->query($sampleQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-15s | %-20s | %-10s | %-15s | %-15s\n",
    "Lead ID", "Name", "Calls", "Current List", "Should Be In"
);
echo str_repeat("-", 90) . "\n";

foreach ($samples as $sample) {
    echo sprintf("%-15s | %-20s | %9d | List %-10d | List %-10d\n",
        substr($sample['external_lead_id'] ?? $sample['id'], 0, 15),
        substr($sample['name'], 0, 20),
        $sample['call_count'],
        $sample['current_list'],
        $sample['proposed_list']
    );
}

// Summary
echo "\n\n✅ SUMMARY:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "• Total leads in Autodial campaign: " . number_format($totalLeads) . "\n";
echo "• Leads that need to be moved: " . number_format($totalNeedMoving) . "\n";
echo "• Percentage needing redistribution: " . round(($totalNeedMoving / $totalLeads) * 100, 1) . "%\n";

echo "\n📌 RECOMMENDATION:\n";
echo "Create a migration script to move " . number_format($totalNeedMoving) . " leads to their correct lists\n";
echo "based on their call count following your established call flow.\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📞 LEAD DISTRIBUTION ANALYSIS BY CALL COUNT\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules based on your specification
$listRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0, 'description' => 'No calls yet'],
    '102' => ['name' => 'First Call', 'min_calls' => 1, 'max_calls' => 1, 'description' => 'After 1st call'],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2, 'description' => '1 call for VM'],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15, 'description' => '4x/day for 3 days'],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20, 'description' => 'Next phase'],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25, 'description' => 'Continued follow-up'],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30, 'description' => 'Extended follow-up'],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35, 'description' => 'Long-term follow-up'],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999, 'description' => 'Many attempts'],
];

echo "📋 CALL FLOW RULES:\n";
echo "-" . str_repeat("-", 70) . "\n";
foreach ($listRules as $listId => $rule) {
    echo sprintf("List %s - %-15s: %2d-%-3d calls | %s\n", 
        $listId, 
        $rule['name'],
        $rule['min_calls'],
        $rule['max_calls'] == 999 ? '∞' : $rule['max_calls'],
        $rule['description']
    );
}

// Get database connection
$db = \DB::connection()->getPdo();

// First, let's see current distribution in Autodial campaign
echo "\n\n📊 CURRENT DISTRIBUTION IN AUTODIAL CAMPAIGN:\n";
echo "-" . str_repeat("-", 70) . "\n";

$currentQuery = "
    SELECT 
        l.vici_list_id,
        COUNT(*) as lead_count,
        COUNT(vcm.id) as leads_with_calls,
        AVG(COALESCE(vcm.call_attempts, 0))::NUMERIC(10,1) as avg_calls,
        MIN(COALESCE(vcm.call_attempts, 0)) as min_calls,
        MAX(COALESCE(vcm.call_attempts, 0)) as max_calls
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id IS NOT NULL
    AND l.vici_list_id > 0  -- Exclude list 0 (not in dialer)
    GROUP BY l.vici_list_id
    ORDER BY l.vici_list_id
";

$currentDist = $db->query($currentQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-10s | %-12s | %-15s | %-20s\n", 
    "List ID", "Lead Count", "Avg Calls", "Call Range"
);
echo str_repeat("-", 70) . "\n";

foreach ($currentDist as $dist) {
    echo sprintf("List %-5d | %11s | %14.1f | %d - %d calls\n",
        $dist['vici_list_id'],
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls']
    );
}

// Now analyze how leads SHOULD be distributed based on call count
echo "\n\n🎯 PROPOSED REDISTRIBUTION BASED ON CALL COUNT:\n";
echo "-" . str_repeat("-", 70) . "\n";

$redistributionQuery = "
    WITH lead_call_data AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            l.created_at,
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
            END as proposed_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
        AND l.vici_list_id > 0
    )
    SELECT 
        proposed_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls,
        AVG(call_count)::NUMERIC(10,1) as avg_calls,
        COUNT(CASE WHEN current_list != proposed_list THEN 1 END) as needs_moving
    FROM lead_call_data
    GROUP BY proposed_list
    ORDER BY proposed_list
";

$redistribution = $db->query($redistributionQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-10s | %-12s | %-15s | %-20s | %-12s\n", 
    "New List", "Lead Count", "Avg Calls", "Call Range", "Need Moving"
);
echo str_repeat("-", 90) . "\n";

$totalLeads = 0;
$totalNeedMoving = 0;

foreach ($redistribution as $dist) {
    $listName = $listRules[$dist['proposed_list']]['name'] ?? 'Unknown';
    echo sprintf("%-10s | %11s | %14.1f | %2d - %-14d | %11s\n",
        $dist['proposed_list'] . " " . substr($listName, 0, 7),
        number_format($dist['lead_count']),
        $dist['avg_calls'],
        $dist['min_calls'],
        $dist['max_calls'],
        number_format($dist['needs_moving'])
    );
    $totalLeads += $dist['lead_count'];
    $totalNeedMoving += $dist['needs_moving'];
}

echo str_repeat("-", 90) . "\n";
echo sprintf("%-10s | %11s | %-15s | %-20s | %11s\n",
    "TOTAL",
    number_format($totalLeads),
    "",
    "",
    number_format($totalNeedMoving)
);

// Show specific examples of leads that need to be moved
echo "\n\n📝 SAMPLE LEADS THAT NEED TO BE MOVED:\n";
echo "-" . str_repeat("-", 70) . "\n";

$sampleQuery = "
    WITH lead_movements AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.name,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
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
            END as proposed_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
        AND l.vici_list_id > 0
    )
    SELECT *
    FROM lead_movements
    WHERE current_list != proposed_list
    ORDER BY call_count DESC
    LIMIT 20
";

$samples = $db->query($sampleQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-15s | %-20s | %-10s | %-15s | %-15s\n",
    "Lead ID", "Name", "Calls", "Current List", "Should Be In"
);
echo str_repeat("-", 90) . "\n";

foreach ($samples as $sample) {
    echo sprintf("%-15s | %-20s | %9d | List %-10d | List %-10d\n",
        substr($sample['external_lead_id'] ?? $sample['id'], 0, 15),
        substr($sample['name'], 0, 20),
        $sample['call_count'],
        $sample['current_list'],
        $sample['proposed_list']
    );
}

// Summary
echo "\n\n✅ SUMMARY:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "• Total leads in Autodial campaign: " . number_format($totalLeads) . "\n";
echo "• Leads that need to be moved: " . number_format($totalNeedMoving) . "\n";
echo "• Percentage needing redistribution: " . round(($totalNeedMoving / $totalLeads) * 100, 1) . "%\n";

echo "\n📌 RECOMMENDATION:\n";
echo "Create a migration script to move " . number_format($totalNeedMoving) . " leads to their correct lists\n";
echo "based on their call count following your established call flow.\n";
