<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📞 CALL FLOW SCHEDULE ANALYSIS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow with days and calls per day
// You'll need to provide these details for each list
$callFlowSchedule = [
    '101' => [
        'name' => 'Brand New',
        'days' => 0,
        'calls_per_day' => 0,
        'total_calls' => 0,
        'description' => 'Fresh leads, no calls yet'
    ],
    '102' => [
        'name' => 'First Contact',
        'days' => 1,
        'calls_per_day' => 1,
        'total_calls' => 1,
        'description' => 'First call attempt'
    ],
    '103' => [
        'name' => 'VM Follow-up',
        'days' => 1,
        'calls_per_day' => 1,
        'total_calls' => 1,
        'description' => 'Voicemail message'
    ],
    '104' => [
        'name' => 'Intensive',
        'days' => 3,
        'calls_per_day' => 4,
        'total_calls' => 12,
        'description' => 'Aggressive calling phase'
    ],
    '105' => [
        'name' => 'Standard Follow-up',
        'days' => 5,
        'calls_per_day' => 2,
        'total_calls' => 10,
        'description' => 'Regular follow-up'
    ],
    '106' => [
        'name' => 'Reduced Follow-up',
        'days' => 7,
        'calls_per_day' => 1,
        'total_calls' => 7,
        'description' => 'Less frequent attempts'
    ],
    '107' => [
        'name' => 'Weekly Touch',
        'days' => 14,
        'calls_per_day' => 0.5,
        'total_calls' => 7,
        'description' => 'Every other day'
    ],
    '108' => [
        'name' => 'Final Attempts',
        'days' => 14,
        'calls_per_day' => 0.25,
        'total_calls' => 3.5,
        'description' => 'Twice a week'
    ],
    '110' => [
        'name' => 'Archive',
        'days' => 0,
        'calls_per_day' => 0,
        'total_calls' => 0,
        'description' => 'No more calls'
    ]
];

echo "📋 CALL FLOW SCHEDULE:\n";
echo "-" . str_repeat("-", 100) . "\n";
echo sprintf("%-8s | %-20s | %-6s | %-10s | %-12s | %-30s\n", 
    "List", "Name", "Days", "Calls/Day", "Total Calls", "Description"
);
echo str_repeat("-", 100) . "\n";

$cumulativeCalls = 0;
$callRanges = [];

foreach ($callFlowSchedule as $listId => $schedule) {
    echo sprintf("%-8s | %-20s | %-6d | %-10s | %-12s | %-30s\n",
        $listId,
        $schedule['name'],
        $schedule['days'],
        $schedule['calls_per_day'] > 0 ? number_format($schedule['calls_per_day'], 1) : '-',
        $schedule['total_calls'] > 0 ? number_format($schedule['total_calls'], 1) : '-',
        $schedule['description']
    );
    
    // Calculate cumulative call ranges
    if ($listId == '101') {
        $callRanges[$listId] = ['min' => 0, 'max' => 0];
    } else if ($listId == '110') {
        $callRanges[$listId] = ['min' => $cumulativeCalls + 1, 'max' => 999];
    } else {
        $callRanges[$listId] = [
            'min' => $cumulativeCalls + 1,
            'max' => $cumulativeCalls + $schedule['total_calls']
        ];
        $cumulativeCalls += $schedule['total_calls'];
    }
}

echo "\n\n📊 CALCULATED CALL RANGES:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo sprintf("%-8s | %-20s | %-20s | %-15s\n", 
    "List", "Name", "Call Range", "Total Calls"
);
echo str_repeat("-", 70) . "\n";

foreach ($callRanges as $listId => $range) {
    $schedule = $callFlowSchedule[$listId];
    if ($range['max'] == 999) {
        $rangeStr = $range['min'] . '+';
    } else if ($range['min'] == $range['max']) {
        $rangeStr = $range['min'];
    } else {
        $rangeStr = $range['min'] . '-' . $range['max'];
    }
    
    echo sprintf("%-8s | %-20s | %-20s | %-15s\n",
        $listId,
        $schedule['name'],
        $rangeStr,
        $schedule['total_calls'] > 0 ? number_format($schedule['total_calls'], 1) : '-'
    );
}

// Get database connection
$db = \DB::connection()->getPdo();

echo "\n\n🎯 LEAD DISTRIBUTION BASED ON CALCULATED RANGES:\n";
echo "-" . str_repeat("-", 70) . "\n";

// Build the SQL with the calculated ranges
$sqlConditions = [];
foreach ($callRanges as $listId => $range) {
    if ($range['min'] == $range['max']) {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) = {$range['min']} THEN {$listId}";
    } else if ($range['max'] == 999) {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) >= {$range['min']} THEN {$listId}";
    } else {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) BETWEEN {$range['min']} AND {$range['max']} THEN {$listId}";
    }
}

$caseStatement = implode("\n            ", $sqlConditions);

$distributionQuery = "
    WITH lead_distribution AS (
        SELECT 
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            CASE 
            {$caseStatement}
            END as target_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
    )
    SELECT 
        target_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls,
        AVG(call_count)::NUMERIC(10,1) as avg_calls
    FROM lead_distribution
    WHERE target_list IS NOT NULL
    GROUP BY target_list
    ORDER BY target_list
";

$distribution = $db->query($distributionQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-8s | %-20s | %-12s | %-15s | %-10s\n",
    "List", "Name", "Lead Count", "Call Range", "Avg Calls"
);
echo str_repeat("-", 80) . "\n";

$totalLeads = 0;
foreach ($distribution as $dist) {
    $schedule = $callFlowSchedule[$dist['target_list']] ?? ['name' => 'Unknown'];
    echo sprintf("%-8s | %-20s | %11s | %2d - %-10d | %9.1f\n",
        $dist['target_list'],
        $schedule['name'],
        number_format($dist['lead_count']),
        $dist['min_calls'],
        $dist['max_calls'],
        $dist['avg_calls']
    );
    $totalLeads += $dist['lead_count'];
}

echo str_repeat("-", 80) . "\n";
echo sprintf("%-8s | %-20s | %11s\n", "TOTAL", "", number_format($totalLeads));

echo "\n\n📌 EXAMPLE CALCULATION:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "List 104 (Intensive): 3 days × 4 calls/day = 12 total calls\n";
echo "• Lead enters List 104 after 2 calls (from Lists 102-103)\n";
echo "• Lead stays in List 104 for calls 3-14 (12 calls total)\n";
echo "• After 14 total calls, lead moves to List 105\n";

echo "\nList 105 (Standard Follow-up): 5 days × 2 calls/day = 10 total calls\n";
echo "• Lead enters List 105 after 14 calls\n";
echo "• Lead stays in List 105 for calls 15-24 (10 calls total)\n";
echo "• After 24 total calls, lead moves to List 106\n";

echo "\n\n⚠️  IMPORTANT NOTES:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. Please verify the days and calls/day for each list\n";
echo "2. The system calculates cumulative call ranges automatically\n";
echo "3. Leads move to the next list after completing all calls in current list\n";
echo "4. You may need to adjust the schedule based on your actual call flow\n";

echo "\n\n❓ QUESTIONS TO CONFIRM:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. How many days should leads stay in List 102?\n";
echo "2. How many calls per day for List 102?\n";
echo "3. How many days should leads stay in List 103?\n";
echo "4. How many calls per day for List 103?\n";
echo "5. Are my assumptions for Lists 104-108 correct?\n";
echo "6. Should there be any lists between 108 and 110?\n";
echo "\nPlease provide the correct schedule for each list.\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📞 CALL FLOW SCHEDULE ANALYSIS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow with days and calls per day
// You'll need to provide these details for each list
$callFlowSchedule = [
    '101' => [
        'name' => 'Brand New',
        'days' => 0,
        'calls_per_day' => 0,
        'total_calls' => 0,
        'description' => 'Fresh leads, no calls yet'
    ],
    '102' => [
        'name' => 'First Contact',
        'days' => 1,
        'calls_per_day' => 1,
        'total_calls' => 1,
        'description' => 'First call attempt'
    ],
    '103' => [
        'name' => 'VM Follow-up',
        'days' => 1,
        'calls_per_day' => 1,
        'total_calls' => 1,
        'description' => 'Voicemail message'
    ],
    '104' => [
        'name' => 'Intensive',
        'days' => 3,
        'calls_per_day' => 4,
        'total_calls' => 12,
        'description' => 'Aggressive calling phase'
    ],
    '105' => [
        'name' => 'Standard Follow-up',
        'days' => 5,
        'calls_per_day' => 2,
        'total_calls' => 10,
        'description' => 'Regular follow-up'
    ],
    '106' => [
        'name' => 'Reduced Follow-up',
        'days' => 7,
        'calls_per_day' => 1,
        'total_calls' => 7,
        'description' => 'Less frequent attempts'
    ],
    '107' => [
        'name' => 'Weekly Touch',
        'days' => 14,
        'calls_per_day' => 0.5,
        'total_calls' => 7,
        'description' => 'Every other day'
    ],
    '108' => [
        'name' => 'Final Attempts',
        'days' => 14,
        'calls_per_day' => 0.25,
        'total_calls' => 3.5,
        'description' => 'Twice a week'
    ],
    '110' => [
        'name' => 'Archive',
        'days' => 0,
        'calls_per_day' => 0,
        'total_calls' => 0,
        'description' => 'No more calls'
    ]
];

echo "📋 CALL FLOW SCHEDULE:\n";
echo "-" . str_repeat("-", 100) . "\n";
echo sprintf("%-8s | %-20s | %-6s | %-10s | %-12s | %-30s\n", 
    "List", "Name", "Days", "Calls/Day", "Total Calls", "Description"
);
echo str_repeat("-", 100) . "\n";

$cumulativeCalls = 0;
$callRanges = [];

foreach ($callFlowSchedule as $listId => $schedule) {
    echo sprintf("%-8s | %-20s | %-6d | %-10s | %-12s | %-30s\n",
        $listId,
        $schedule['name'],
        $schedule['days'],
        $schedule['calls_per_day'] > 0 ? number_format($schedule['calls_per_day'], 1) : '-',
        $schedule['total_calls'] > 0 ? number_format($schedule['total_calls'], 1) : '-',
        $schedule['description']
    );
    
    // Calculate cumulative call ranges
    if ($listId == '101') {
        $callRanges[$listId] = ['min' => 0, 'max' => 0];
    } else if ($listId == '110') {
        $callRanges[$listId] = ['min' => $cumulativeCalls + 1, 'max' => 999];
    } else {
        $callRanges[$listId] = [
            'min' => $cumulativeCalls + 1,
            'max' => $cumulativeCalls + $schedule['total_calls']
        ];
        $cumulativeCalls += $schedule['total_calls'];
    }
}

echo "\n\n📊 CALCULATED CALL RANGES:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo sprintf("%-8s | %-20s | %-20s | %-15s\n", 
    "List", "Name", "Call Range", "Total Calls"
);
echo str_repeat("-", 70) . "\n";

foreach ($callRanges as $listId => $range) {
    $schedule = $callFlowSchedule[$listId];
    if ($range['max'] == 999) {
        $rangeStr = $range['min'] . '+';
    } else if ($range['min'] == $range['max']) {
        $rangeStr = $range['min'];
    } else {
        $rangeStr = $range['min'] . '-' . $range['max'];
    }
    
    echo sprintf("%-8s | %-20s | %-20s | %-15s\n",
        $listId,
        $schedule['name'],
        $rangeStr,
        $schedule['total_calls'] > 0 ? number_format($schedule['total_calls'], 1) : '-'
    );
}

// Get database connection
$db = \DB::connection()->getPdo();

echo "\n\n🎯 LEAD DISTRIBUTION BASED ON CALCULATED RANGES:\n";
echo "-" . str_repeat("-", 70) . "\n";

// Build the SQL with the calculated ranges
$sqlConditions = [];
foreach ($callRanges as $listId => $range) {
    if ($range['min'] == $range['max']) {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) = {$range['min']} THEN {$listId}";
    } else if ($range['max'] == 999) {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) >= {$range['min']} THEN {$listId}";
    } else {
        $sqlConditions[] = "WHEN COALESCE(vcm.call_attempts, 0) BETWEEN {$range['min']} AND {$range['max']} THEN {$listId}";
    }
}

$caseStatement = implode("\n            ", $sqlConditions);

$distributionQuery = "
    WITH lead_distribution AS (
        SELECT 
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            CASE 
            {$caseStatement}
            END as target_list
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
    )
    SELECT 
        target_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls,
        AVG(call_count)::NUMERIC(10,1) as avg_calls
    FROM lead_distribution
    WHERE target_list IS NOT NULL
    GROUP BY target_list
    ORDER BY target_list
";

$distribution = $db->query($distributionQuery)->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-8s | %-20s | %-12s | %-15s | %-10s\n",
    "List", "Name", "Lead Count", "Call Range", "Avg Calls"
);
echo str_repeat("-", 80) . "\n";

$totalLeads = 0;
foreach ($distribution as $dist) {
    $schedule = $callFlowSchedule[$dist['target_list']] ?? ['name' => 'Unknown'];
    echo sprintf("%-8s | %-20s | %11s | %2d - %-10d | %9.1f\n",
        $dist['target_list'],
        $schedule['name'],
        number_format($dist['lead_count']),
        $dist['min_calls'],
        $dist['max_calls'],
        $dist['avg_calls']
    );
    $totalLeads += $dist['lead_count'];
}

echo str_repeat("-", 80) . "\n";
echo sprintf("%-8s | %-20s | %11s\n", "TOTAL", "", number_format($totalLeads));

echo "\n\n📌 EXAMPLE CALCULATION:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "List 104 (Intensive): 3 days × 4 calls/day = 12 total calls\n";
echo "• Lead enters List 104 after 2 calls (from Lists 102-103)\n";
echo "• Lead stays in List 104 for calls 3-14 (12 calls total)\n";
echo "• After 14 total calls, lead moves to List 105\n";

echo "\nList 105 (Standard Follow-up): 5 days × 2 calls/day = 10 total calls\n";
echo "• Lead enters List 105 after 14 calls\n";
echo "• Lead stays in List 105 for calls 15-24 (10 calls total)\n";
echo "• After 24 total calls, lead moves to List 106\n";

echo "\n\n⚠️  IMPORTANT NOTES:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. Please verify the days and calls/day for each list\n";
echo "2. The system calculates cumulative call ranges automatically\n";
echo "3. Leads move to the next list after completing all calls in current list\n";
echo "4. You may need to adjust the schedule based on your actual call flow\n";

echo "\n\n❓ QUESTIONS TO CONFIRM:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. How many days should leads stay in List 102?\n";
echo "2. How many calls per day for List 102?\n";
echo "3. How many days should leads stay in List 103?\n";
echo "4. How many calls per day for List 103?\n";
echo "5. Are my assumptions for Lists 104-108 correct?\n";
echo "6. Should there be any lists between 108 and 110?\n";
echo "\nPlease provide the correct schedule for each list.\n";
