<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "🔍 ANALYZING LEAD DISTRIBUTION AND CALL PATTERNS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Get database connection
$db = \DB::connection()->getPdo();

// 1. Check current list distribution
echo "📊 CURRENT LEAD DISTRIBUTION BY LIST:\n";
echo "-" . str_repeat("-", 50) . "\n";

$listQuery = "
    SELECT 
        vici_list_id,
        COUNT(*) as lead_count,
        MIN(created_at) as oldest_lead,
        MAX(created_at) as newest_lead,
        AVG(EXTRACT(EPOCH FROM (NOW() - created_at))/86400)::INT as avg_age_days
    FROM leads 
    WHERE vici_list_id IS NOT NULL 
    GROUP BY vici_list_id 
    ORDER BY vici_list_id
";

$lists = $db->query($listQuery)->fetchAll(PDO::FETCH_ASSOC);

foreach ($lists as $list) {
    echo sprintf("List %d: %s leads (Avg age: %d days)\n", 
        $list['vici_list_id'], 
        number_format($list['lead_count']),
        $list['avg_age_days']
    );
}

// 2. Analyze call patterns
echo "\n📞 CALL PATTERNS ANALYSIS:\n";
echo "-" . str_repeat("-", 50) . "\n";

// Get leads with call history
$callQuery = "
    SELECT 
        l.id,
        l.external_lead_id,
        l.vici_list_id,
        l.created_at,
        l.name,
        vcm.call_attempts,
        vcm.first_call_time,
        vcm.last_call_time,
        vcm.call_status,
        EXTRACT(EPOCH FROM (NOW() - l.created_at))/86400 as age_days,
        CASE 
            WHEN vcm.first_call_time IS NOT NULL AND vcm.last_call_time IS NOT NULL 
            THEN EXTRACT(EPOCH FROM (vcm.last_call_time - vcm.first_call_time))/86400
            ELSE 0
        END as call_span_days
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id IS NOT NULL
    ORDER BY vcm.call_attempts DESC NULLS LAST
    LIMIT 20
";

$callData = $db->query($callQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "\nTop 20 Leads by Call Attempts:\n";
foreach ($callData as $lead) {
    $callsPerDay = 0;
    if ($lead['call_span_days'] > 0) {
        $callsPerDay = round($lead['call_attempts'] / $lead['call_span_days'], 2);
    } elseif ($lead['call_attempts'] > 0) {
        $callsPerDay = $lead['call_attempts']; // All calls in one day
    }
    
    echo sprintf("Lead %s (List %d): %d calls over %.1f days = %.2f calls/day\n",
        substr($lead['external_lead_id'] ?? $lead['id'], 0, 10),
        $lead['vici_list_id'],
        $lead['call_attempts'] ?? 0,
        $lead['call_span_days'],
        $callsPerDay
    );
}

// 3. Calculate overall call frequency distribution
echo "\n📈 CALL FREQUENCY DISTRIBUTION:\n";
echo "-" . str_repeat("-", 50) . "\n";

$freqQuery = "
    SELECT 
        CASE 
            WHEN vcm.call_attempts IS NULL THEN '0 calls'
            WHEN vcm.call_attempts = 1 THEN '1 call'
            WHEN vcm.call_attempts BETWEEN 2 AND 3 THEN '2-3 calls'
            WHEN vcm.call_attempts BETWEEN 4 AND 6 THEN '4-6 calls'
            WHEN vcm.call_attempts BETWEEN 7 AND 10 THEN '7-10 calls'
            WHEN vcm.call_attempts > 10 THEN '10+ calls'
        END as call_range,
        COUNT(*) as lead_count,
        AVG(EXTRACT(EPOCH FROM (NOW() - l.created_at))/86400)::INT as avg_age_days
    FROM leads l
    LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
    WHERE l.vici_list_id IS NOT NULL
    GROUP BY call_range
    ORDER BY 
        CASE call_range
            WHEN '0 calls' THEN 1
            WHEN '1 call' THEN 2
            WHEN '2-3 calls' THEN 3
            WHEN '4-6 calls' THEN 4
            WHEN '7-10 calls' THEN 5
            WHEN '10+ calls' THEN 6
        END
";

$freqData = $db->query($freqQuery)->fetchAll(PDO::FETCH_ASSOC);

foreach ($freqData as $freq) {
    echo sprintf("%s: %s leads (Avg age: %d days)\n",
        str_pad($freq['call_range'], 12),
        str_pad(number_format($freq['lead_count']), 8, ' ', STR_PAD_LEFT),
        $freq['avg_age_days']
    );
}

// 4. Propose list assignments based on call frequency
echo "\n🎯 PROPOSED LIST ASSIGNMENTS (101-120):\n";
echo "-" . str_repeat("-", 50) . "\n";
echo "Based on call frequency per day:\n\n";

$rules = [
    '101' => ['name' => 'Fresh/New', 'min_calls_per_day' => 0, 'max_calls_per_day' => 0, 'max_age_days' => 1],
    '102' => ['name' => 'Hot', 'min_calls_per_day' => 0, 'max_calls_per_day' => 0.5, 'max_age_days' => 7],
    '103' => ['name' => 'Warm', 'min_calls_per_day' => 0.5, 'max_calls_per_day' => 1, 'max_age_days' => 14],
    '104' => ['name' => 'Follow-up', 'min_calls_per_day' => 1, 'max_calls_per_day' => 2, 'max_age_days' => 30],
    '105' => ['name' => 'Nurture', 'min_calls_per_day' => 0.1, 'max_calls_per_day' => 0.5, 'max_age_days' => 60],
    '106' => ['name' => 'Re-engage', 'min_calls_per_day' => 0.05, 'max_calls_per_day' => 0.1, 'max_age_days' => 90],
    '110' => ['name' => 'Archive', 'min_calls_per_day' => 0, 'max_calls_per_day' => 0.05, 'max_age_days' => 999],
    '199' => ['name' => 'DNC', 'min_calls_per_day' => -1, 'max_calls_per_day' => -1, 'max_age_days' => 999],
];

foreach ($rules as $listId => $rule) {
    echo sprintf("List %s - %s:\n", $listId, $rule['name']);
    if ($rule['min_calls_per_day'] >= 0) {
        echo sprintf("  • Calls/day: %.2f - %.2f\n", $rule['min_calls_per_day'], $rule['max_calls_per_day']);
        echo sprintf("  • Max age: %d days\n", $rule['max_age_days']);
    } else {
        echo "  • Do Not Call list\n";
    }
    echo "\n";
}

// 5. Count how many leads would go to each list
echo "📊 LEAD DISTRIBUTION WITH NEW RULES:\n";
echo "-" . str_repeat("-", 50) . "\n";

$distributionQuery = "
    WITH lead_metrics AS (
        SELECT 
            l.id,
            l.vici_list_id as current_list,
            COALESCE(vcm.call_attempts, 0) as call_attempts,
            EXTRACT(EPOCH FROM (NOW() - l.created_at))/86400 as age_days,
            CASE 
                WHEN vcm.first_call_time IS NOT NULL AND vcm.last_call_time IS NOT NULL 
                    AND vcm.last_call_time > vcm.first_call_time
                THEN COALESCE(vcm.call_attempts, 0) / NULLIF(EXTRACT(EPOCH FROM (vcm.last_call_time - vcm.first_call_time))/86400, 0)
                WHEN vcm.call_attempts > 0 AND EXTRACT(EPOCH FROM (NOW() - l.created_at))/86400 > 0
                THEN vcm.call_attempts / (EXTRACT(EPOCH FROM (NOW() - l.created_at))/86400)
                ELSE 0
            END as calls_per_day
        FROM leads l
        LEFT JOIN vici_call_metrics vcm ON l.id = vcm.lead_id
        WHERE l.vici_list_id IS NOT NULL
    )
    SELECT 
        CASE 
            WHEN age_days <= 1 AND call_attempts = 0 THEN '101 - Fresh/New'
            WHEN age_days <= 7 AND calls_per_day < 0.5 THEN '102 - Hot'
            WHEN age_days <= 14 AND calls_per_day BETWEEN 0.5 AND 1 THEN '103 - Warm'
            WHEN age_days <= 30 AND calls_per_day BETWEEN 1 AND 2 THEN '104 - Follow-up'
            WHEN age_days <= 60 AND calls_per_day BETWEEN 0.1 AND 0.5 THEN '105 - Nurture'
            WHEN age_days <= 90 AND calls_per_day BETWEEN 0.05 AND 0.1 THEN '106 - Re-engage'
            WHEN calls_per_day < 0.05 OR age_days > 90 THEN '110 - Archive'
            ELSE '110 - Archive'
        END as proposed_list,
        COUNT(*) as lead_count,
        AVG(age_days)::INT as avg_age,
        AVG(call_attempts)::NUMERIC(10,1) as avg_calls,
        AVG(calls_per_day)::NUMERIC(10,2) as avg_calls_per_day
    FROM lead_metrics
    GROUP BY proposed_list
    ORDER BY proposed_list
";

$distribution = $db->query($distributionQuery)->fetchAll(PDO::FETCH_ASSOC);

$totalLeads = 0;
foreach ($distribution as $dist) {
    echo sprintf("%s: %s leads (Avg: %.1f calls, %.2f calls/day, %d days old)\n",
        str_pad($dist['proposed_list'], 20),
        str_pad(number_format($dist['lead_count']), 8, ' ', STR_PAD_LEFT),
        $dist['avg_calls'],
        $dist['avg_calls_per_day'],
        $dist['avg_age']
    );
    $totalLeads += $dist['lead_count'];
}

echo "\nTotal leads to redistribute: " . number_format($totalLeads) . "\n";

echo "\n✅ RECOMMENDATION:\n";
echo "-" . str_repeat("-", 50) . "\n";
echo "1. Move leads to new lists based on call frequency\n";
echo "2. Fresh leads (< 1 day, no calls) → List 101\n";
echo "3. Recently added (< 7 days, few calls) → List 102\n";
echo "4. Active engagement (moderate calls) → Lists 103-104\n";
echo "5. Low engagement (few calls, older) → Lists 105-106\n";
echo "6. Inactive/Old (90+ days or very few calls) → List 110\n";
echo "\nWould you like me to create a migration script to move the leads?\n";
