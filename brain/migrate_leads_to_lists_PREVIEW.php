<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📋 LEAD MIGRATION LOGIC PREVIEW (DRY RUN - NO CHANGES WILL BE MADE)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules
$callFlowRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0],
    '102' => ['name' => 'First Call Made', 'min_calls' => 1, 'max_calls' => 1],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999],
];

echo "🔍 MIGRATION LOGIC:\n";
echo "-" . str_repeat("-", 70) . "\n\n";

echo "STEP 1: Determine target list based on call count\n";
echo "The logic checks each lead's call_attempts from vici_call_metrics:\n\n";

foreach ($callFlowRules as $listId => $rule) {
    if ($rule['max_calls'] == 999) {
        echo sprintf("• If calls >= %d → List %s (%s)\n", 
            $rule['min_calls'], $listId, $rule['name']);
    } else if ($rule['min_calls'] == $rule['max_calls']) {
        echo sprintf("• If calls = %d → List %s (%s)\n", 
            $rule['min_calls'], $listId, $rule['name']);
    } else {
        echo sprintf("• If calls between %d-%d → List %s (%s)\n", 
            $rule['min_calls'], $rule['max_calls'], $listId, $rule['name']);
    }
}

// Get database connection
$db = \DB::connection()->getPdo();

echo "\n\nSTEP 2: Analyze current leads and their required movements\n";
echo "-" . str_repeat("-", 70) . "\n";

// Build the SQL query that would be used for migration
$analysisQuery = "
    WITH lead_analysis AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            -- Determine target list based on call count
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
            END as target_list,
            -- Check if lead needs to be moved
            CASE 
                WHEN l.vici_list_id != CASE 
                    WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 101
                    WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 102
                    WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 103
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 15 THEN 104
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN 105
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 25 THEN 106
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 26 AND 30 THEN 107
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 31 AND 35 THEN 108
                    ELSE 110
                END THEN true
                ELSE false
            END as needs_movement
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
    )
    SELECT 
        current_list,
        target_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls
    FROM lead_analysis
    WHERE needs_movement = true
    GROUP BY current_list, target_list
    ORDER BY current_list, target_list
";

$movements = $db->query($analysisQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "\nLeads that need to be moved:\n";
echo sprintf("%-15s | %-15s | %-12s | %-20s\n", 
    "From List", "To List", "Lead Count", "Call Range"
);
echo str_repeat("-", 70) . "\n";

$totalMovements = 0;
foreach ($movements as $move) {
    $fromName = $move['current_list'] == 0 ? 'Not in Dialer' : 'List ' . $move['current_list'];
    $toName = $callFlowRules[$move['target_list']]['name'] ?? 'List ' . $move['target_list'];
    
    echo sprintf("%-15s | List %-3d %-7s | %11s | %d-%d calls\n",
        $fromName,
        $move['target_list'],
        "(" . substr($toName, 0, 7) . ")",
        number_format($move['lead_count']),
        $move['min_calls'],
        $move['max_calls']
    );
    $totalMovements += $move['lead_count'];
}

if ($totalMovements == 0) {
    echo "No leads need to be moved - all are in their correct lists!\n";
}

echo "\nTotal leads to move: " . number_format($totalMovements) . "\n";

echo "\n\nSTEP 3: Migration SQL Commands (PREVIEW ONLY)\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "The following UPDATE statements would be executed:\n\n";

// Show the SQL that would be executed for each movement type
echo "-- Move List 0 leads with 0 calls to List 101 (Brand New)\n";
echo "UPDATE leads l\n";
echo "LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 101\n";
echo "WHERE l.vici_list_id = 0\n";
echo "AND COALESCE(vcm.call_attempts, 0) = 0;\n\n";

echo "-- Move leads with 1 call to List 102 (First Call Made)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 102\n";
echo "WHERE vcm.call_attempts = 1\n";
echo "AND l.vici_list_id != 102;\n\n";

echo "-- Move leads with 2 calls to List 103 (VM Message)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 103\n";
echo "WHERE vcm.call_attempts = 2\n";
echo "AND l.vici_list_id != 103;\n\n";

echo "-- Move leads with 3-15 calls to List 104 (Intensive)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 104\n";
echo "WHERE vcm.call_attempts BETWEEN 3 AND 15\n";
echo "AND l.vici_list_id != 104;\n\n";

echo "-- Continue pattern for remaining lists...\n";

echo "\n\nSTEP 4: Verification After Migration\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "After migration, we would verify:\n";
echo "• Each list contains only leads with the correct call count range\n";
echo "• No leads are left in List 0 (unless intentionally excluded)\n";
echo "• Total lead count remains the same\n";
echo "• Call metrics are preserved\n";

echo "\n\nSTEP 5: Sample Leads That Would Be Moved\n";
echo "-" . str_repeat("-", 70) . "\n";

$sampleQuery = "
    SELECT 
        l.external_lead_id,
        l.name,
        l.vici_list_id as current_list,
        COALESCE(vcm.call_attempts, 0) as calls,
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
        END as target_list
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    LIMIT 10
";

$samples = $db->query($sampleQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "First 10 leads from List 0 that would be moved:\n";
echo sprintf("%-15s | %-25s | %-8s | %-12s | %-12s\n",
    "Lead ID", "Name", "Calls", "Current", "Target"
);
echo str_repeat("-", 80) . "\n";

foreach ($samples as $sample) {
    $targetName = $callFlowRules[$sample['target_list']]['name'] ?? 'Unknown';
    echo sprintf("%-15s | %-25s | %7d | List %-7d | List %d (%s)\n",
        substr($sample['external_lead_id'], 0, 15),
        substr($sample['name'], 0, 25),
        $sample['calls'],
        $sample['current_list'],
        $sample['target_list'],
        substr($targetName, 0, 10)
    );
}

echo "\n\n✅ MIGRATION LOGIC SUMMARY:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. Each lead's target list is determined by its call_attempts count\n";
echo "2. Leads are moved ONLY if current_list != target_list\n";
echo "3. List 0 leads (not in dialer) with 0 calls → List 101\n";
echo "4. As calls are made, leads automatically progress through lists\n";
echo "5. Migration preserves all lead data and call history\n";
echo "\n⚠️  This is a PREVIEW ONLY - no actual changes have been made\n";
echo "Review the logic above and confirm it matches your requirements.\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "📋 LEAD MIGRATION LOGIC PREVIEW (DRY RUN - NO CHANGES WILL BE MADE)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Define the call flow rules
$callFlowRules = [
    '101' => ['name' => 'Brand New', 'min_calls' => 0, 'max_calls' => 0],
    '102' => ['name' => 'First Call Made', 'min_calls' => 1, 'max_calls' => 1],
    '103' => ['name' => 'VM Message', 'min_calls' => 2, 'max_calls' => 2],
    '104' => ['name' => 'Intensive', 'min_calls' => 3, 'max_calls' => 15],
    '105' => ['name' => 'Follow-up 1', 'min_calls' => 16, 'max_calls' => 20],
    '106' => ['name' => 'Follow-up 2', 'min_calls' => 21, 'max_calls' => 25],
    '107' => ['name' => 'Follow-up 3', 'min_calls' => 26, 'max_calls' => 30],
    '108' => ['name' => 'Follow-up 4', 'min_calls' => 31, 'max_calls' => 35],
    '110' => ['name' => 'Archive', 'min_calls' => 36, 'max_calls' => 999],
];

echo "🔍 MIGRATION LOGIC:\n";
echo "-" . str_repeat("-", 70) . "\n\n";

echo "STEP 1: Determine target list based on call count\n";
echo "The logic checks each lead's call_attempts from vici_call_metrics:\n\n";

foreach ($callFlowRules as $listId => $rule) {
    if ($rule['max_calls'] == 999) {
        echo sprintf("• If calls >= %d → List %s (%s)\n", 
            $rule['min_calls'], $listId, $rule['name']);
    } else if ($rule['min_calls'] == $rule['max_calls']) {
        echo sprintf("• If calls = %d → List %s (%s)\n", 
            $rule['min_calls'], $listId, $rule['name']);
    } else {
        echo sprintf("• If calls between %d-%d → List %s (%s)\n", 
            $rule['min_calls'], $rule['max_calls'], $listId, $rule['name']);
    }
}

// Get database connection
$db = \DB::connection()->getPdo();

echo "\n\nSTEP 2: Analyze current leads and their required movements\n";
echo "-" . str_repeat("-", 70) . "\n";

// Build the SQL query that would be used for migration
$analysisQuery = "
    WITH lead_analysis AS (
        SELECT 
            l.id,
            l.external_lead_id,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_count,
            -- Determine target list based on call count
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
            END as target_list,
            -- Check if lead needs to be moved
            CASE 
                WHEN l.vici_list_id != CASE 
                    WHEN COALESCE(vcm.call_attempts, 0) = 0 THEN 101
                    WHEN COALESCE(vcm.call_attempts, 0) = 1 THEN 102
                    WHEN COALESCE(vcm.call_attempts, 0) = 2 THEN 103
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 3 AND 15 THEN 104
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 16 AND 20 THEN 105
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 21 AND 25 THEN 106
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 26 AND 30 THEN 107
                    WHEN COALESCE(vcm.call_attempts, 0) BETWEEN 31 AND 35 THEN 108
                    ELSE 110
                END THEN true
                ELSE false
            END as needs_movement
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
    )
    SELECT 
        current_list,
        target_list,
        COUNT(*) as lead_count,
        MIN(call_count) as min_calls,
        MAX(call_count) as max_calls
    FROM lead_analysis
    WHERE needs_movement = true
    GROUP BY current_list, target_list
    ORDER BY current_list, target_list
";

$movements = $db->query($analysisQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "\nLeads that need to be moved:\n";
echo sprintf("%-15s | %-15s | %-12s | %-20s\n", 
    "From List", "To List", "Lead Count", "Call Range"
);
echo str_repeat("-", 70) . "\n";

$totalMovements = 0;
foreach ($movements as $move) {
    $fromName = $move['current_list'] == 0 ? 'Not in Dialer' : 'List ' . $move['current_list'];
    $toName = $callFlowRules[$move['target_list']]['name'] ?? 'List ' . $move['target_list'];
    
    echo sprintf("%-15s | List %-3d %-7s | %11s | %d-%d calls\n",
        $fromName,
        $move['target_list'],
        "(" . substr($toName, 0, 7) . ")",
        number_format($move['lead_count']),
        $move['min_calls'],
        $move['max_calls']
    );
    $totalMovements += $move['lead_count'];
}

if ($totalMovements == 0) {
    echo "No leads need to be moved - all are in their correct lists!\n";
}

echo "\nTotal leads to move: " . number_format($totalMovements) . "\n";

echo "\n\nSTEP 3: Migration SQL Commands (PREVIEW ONLY)\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "The following UPDATE statements would be executed:\n\n";

// Show the SQL that would be executed for each movement type
echo "-- Move List 0 leads with 0 calls to List 101 (Brand New)\n";
echo "UPDATE leads l\n";
echo "LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 101\n";
echo "WHERE l.vici_list_id = 0\n";
echo "AND COALESCE(vcm.call_attempts, 0) = 0;\n\n";

echo "-- Move leads with 1 call to List 102 (First Call Made)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 102\n";
echo "WHERE vcm.call_attempts = 1\n";
echo "AND l.vici_list_id != 102;\n\n";

echo "-- Move leads with 2 calls to List 103 (VM Message)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 103\n";
echo "WHERE vcm.call_attempts = 2\n";
echo "AND l.vici_list_id != 103;\n\n";

echo "-- Move leads with 3-15 calls to List 104 (Intensive)\n";
echo "UPDATE leads l\n";
echo "INNER JOIN vici_call_metrics vcm ON l.id = vcm.lead_id\n";
echo "SET l.vici_list_id = 104\n";
echo "WHERE vcm.call_attempts BETWEEN 3 AND 15\n";
echo "AND l.vici_list_id != 104;\n\n";

echo "-- Continue pattern for remaining lists...\n";

echo "\n\nSTEP 4: Verification After Migration\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "After migration, we would verify:\n";
echo "• Each list contains only leads with the correct call count range\n";
echo "• No leads are left in List 0 (unless intentionally excluded)\n";
echo "• Total lead count remains the same\n";
echo "• Call metrics are preserved\n";

echo "\n\nSTEP 5: Sample Leads That Would Be Moved\n";
echo "-" . str_repeat("-", 70) . "\n";

$sampleQuery = "
    SELECT 
        l.external_lead_id,
        l.name,
        l.vici_list_id as current_list,
        COALESCE(vcm.call_attempts, 0) as calls,
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
        END as target_list
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id = 0
    LIMIT 10
";

$samples = $db->query($sampleQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "First 10 leads from List 0 that would be moved:\n";
echo sprintf("%-15s | %-25s | %-8s | %-12s | %-12s\n",
    "Lead ID", "Name", "Calls", "Current", "Target"
);
echo str_repeat("-", 80) . "\n";

foreach ($samples as $sample) {
    $targetName = $callFlowRules[$sample['target_list']]['name'] ?? 'Unknown';
    echo sprintf("%-15s | %-25s | %7d | List %-7d | List %d (%s)\n",
        substr($sample['external_lead_id'], 0, 15),
        substr($sample['name'], 0, 25),
        $sample['calls'],
        $sample['current_list'],
        $sample['target_list'],
        substr($targetName, 0, 10)
    );
}

echo "\n\n✅ MIGRATION LOGIC SUMMARY:\n";
echo "-" . str_repeat("-", 70) . "\n";
echo "1. Each lead's target list is determined by its call_attempts count\n";
echo "2. Leads are moved ONLY if current_list != target_list\n";
echo "3. List 0 leads (not in dialer) with 0 calls → List 101\n";
echo "4. As calls are made, leads automatically progress through lists\n";
echo "5. Migration preserves all lead data and call history\n";
echo "\n⚠️  This is a PREVIEW ONLY - no actual changes have been made\n";
echo "Review the logic above and confirm it matches your requirements.\n";




