<?php
// migrate_autodial_to_flow.php
// Migrate Autodial campaign leads to new flow lists (101-199) based on call attempts

echo "=== AUTODIAL LEAD MIGRATION TO FLOW LISTS ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// First, analyze current Autodial leads
echo "📊 STEP 1: Analyzing Autodial Campaign Leads...\n\n";

$analyzeCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
    SELECT 
        vl.list_id as current_list,
        vl.status,
        vl.called_count,
        COUNT(*) as lead_count,
        CASE 
            WHEN vl.status IN ('DNC', 'DNCL', 'DNCC') THEN '→ List 199 (DNC)'
            WHEN vl.status IN ('CALLBK', 'CBHOLD') THEN '→ List 103 (Callback)'
            WHEN vl.called_count = 0 THEN '→ List 101 (New)'
            WHEN vl.called_count BETWEEN 1 AND 3 THEN '→ List 102 (Aggressive)'
            WHEN vl.called_count BETWEEN 4 AND 10 THEN '→ List 104 (Phase 1)'
            WHEN vl.called_count BETWEEN 11 AND 20 THEN '→ List 106 (Phase 2)'
            WHEN vl.called_count > 20 THEN '→ List 108 (Phase 3)'
            ELSE '→ List 101 (Default)'
        END as proposed_list
    FROM vicidial_list vl
    INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
    WHERE vls.campaign_id = 'Autodial'
    AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
    GROUP BY vl.list_id, vl.status, vl.called_count
    ORDER BY lead_count DESC
    LIMIT 50
\" 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $analyzeCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    echo $result['output'] ?? 'No output';
    echo "\n";
}

// Get summary counts
echo "\n📈 STEP 2: Migration Summary Preview...\n\n";

$summaryCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
    SELECT 
        CASE 
            WHEN vl.status IN ('DNC', 'DNCL', 'DNCC') THEN 'List 199 (DNC)'
            WHEN vl.status IN ('CALLBK', 'CBHOLD') THEN 'List 103 (Callback)'
            WHEN vl.called_count = 0 THEN 'List 101 (New - Never Called)'
            WHEN vl.called_count BETWEEN 1 AND 3 THEN 'List 102 (Aggressive - 1-3 calls)'
            WHEN vl.called_count BETWEEN 4 AND 10 THEN 'List 104 (Phase 1 - 4-10 calls)'
            WHEN vl.called_count BETWEEN 11 AND 20 THEN 'List 106 (Phase 2 - 11-20 calls)'
            WHEN vl.called_count > 20 THEN 'List 108 (Phase 3 - 20+ calls)'
            ELSE 'List 101 (Default)'
        END as target_list,
        COUNT(*) as leads_to_move
    FROM vicidial_list vl
    INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
    WHERE vls.campaign_id = 'Autodial'
    AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
    GROUP BY target_list
    ORDER BY 
        CASE 
            WHEN target_list LIKE '%101%' THEN 1
            WHEN target_list LIKE '%102%' THEN 2
            WHEN target_list LIKE '%103%' THEN 3
            WHEN target_list LIKE '%104%' THEN 4
            WHEN target_list LIKE '%106%' THEN 5
            WHEN target_list LIKE '%108%' THEN 6
            WHEN target_list LIKE '%199%' THEN 7
            ELSE 8
        END
\" 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $summaryCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    echo $result['output'] ?? 'No output';
    echo "\n";
}

echo "\n🚀 STEP 3: Ready to Migrate?\n";
echo "----------------------------------------\n";
echo "This script will move Autodial leads to new flow lists based on:\n";
echo "  • Call attempts (0, 1-3, 4-10, 11-20, 20+)\n";
echo "  • Status (DNC → 199, CALLBK → 103)\n";
echo "  • Preserves all lead data\n\n";

echo "To execute migration, run: php execute_autodial_migration.php\n\n";

// Create the actual migration script
$migrationScript = '<?php
// execute_autodial_migration.php
// Actually perform the migration

echo "=== EXECUTING AUTODIAL MIGRATION ===\n\n";

$proxyUrl = "https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute";

// Migration queries
$migrations = [
    // DNC leads to List 199
    [
        "name" => "DNC Status → List 199",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 199
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.status IN (\'DNC\', \'DNCL\', \'DNCC\')"
    ],
    // Callback leads to List 103
    [
        "name" => "Callback Status → List 103",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 103
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.status IN (\'CALLBK\', \'CBHOLD\')"
    ],
    // Never called to List 101
    [
        "name" => "Never Called → List 101",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 101
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count = 0"
    ],
    // 1-3 calls to List 102
    [
        "name" => "1-3 Calls → List 102",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 102
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 1 AND 3"
    ],
    // 4-10 calls to List 104
    [
        "name" => "4-10 Calls → List 104",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 104
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 4 AND 10"
    ],
    // 11-20 calls to List 106
    [
        "name" => "11-20 Calls → List 106",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 106
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 11 AND 20"
    ],
    // 20+ calls to List 108
    [
        "name" => "20+ Calls → List 108",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 108
                  WHERE vls.campaign_id = \'Autodial\'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count > 20"
    ]
];

foreach ($migrations as $migration) {
    echo "📤 " . $migration["name"] . "... ";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["command" => "mysql -u root Q6hdjl67GRigMofv -e \"" . $migration["sql"] . "\" 2>&1"]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ Done\n";
    } else {
        echo "❌ Failed\n";
    }
}

echo "\n✅ Migration Complete!\n";
';

file_put_contents('execute_autodial_migration.php', $migrationScript);
echo "✅ Migration script created: execute_autodial_migration.php\n";
