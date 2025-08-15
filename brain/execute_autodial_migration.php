<?php
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
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.status IN ('DNC', 'DNCL', 'DNCC')"
    ],
    // Callback leads to List 103
    [
        "name" => "Callback Status → List 103",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 103
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.status IN ('CALLBK', 'CBHOLD')"
    ],
    // Never called to List 101
    [
        "name" => "Never Called → List 101",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 101
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count = 0"
    ],
    // 1-3 calls to List 102
    [
        "name" => "1-3 Calls → List 102",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 102
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 1 AND 3"
    ],
    // 4-10 calls to List 104
    [
        "name" => "4-10 Calls → List 104",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 104
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 4 AND 10"
    ],
    // 11-20 calls to List 106
    [
        "name" => "11-20 Calls → List 106",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 106
                  WHERE vls.campaign_id = 'Autodial'
                  AND vl.list_id NOT IN (101,102,103,104,105,106,107,108,110,199)
                  AND vl.called_count BETWEEN 11 AND 20"
    ],
    // 20+ calls to List 108
    [
        "name" => "20+ Calls → List 108",
        "sql" => "UPDATE vicidial_list vl 
                  INNER JOIN vicidial_lists vls ON vl.list_id = vls.list_id
                  SET vl.list_id = 108
                  WHERE vls.campaign_id = 'Autodial'
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
