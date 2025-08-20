<?php

// Connect to PostgreSQL
$pdo = new PDO(
    'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
    'brain_user',
    'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
);

echo "=================================\n";
echo "FIXING LEAD DATA\n";
echo "=================================\n\n";

// 1. Fix opt_in_date for all Suraj leads that have timestamp in payload
echo "1. Updating opt_in_dates from payload timestamps...\n";
$stmt = $pdo->query("
    SELECT id, payload 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    AND opt_in_date IS NULL
");

$updated = 0;
while ($lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payload = json_decode($lead['payload'], true);
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    if (isset($payload['timestamp'])) {
        try {
            $date = new DateTime($payload['timestamp']);
            $optInDate = $date->format('Y-m-d H:i:s');
            
            $updateStmt = $pdo->prepare("UPDATE leads SET opt_in_date = ? WHERE id = ?");
            $updateStmt->execute([$optInDate, $lead['id']]);
            $updated++;
        } catch (Exception $e) {
            // Skip
        }
    }
}
echo "   Updated $updated leads with opt_in_date\n\n";

// 2. Fix TCPA compliant flag for all Suraj leads
echo "2. Setting TCPA compliant for all Suraj leads...\n";
$result = $pdo->exec("
    UPDATE leads 
    SET tcpa_compliant = true 
    WHERE source = 'SURAJ_BULK' 
    AND (tcpa_compliant IS NULL OR tcpa_compliant = false)
");
echo "   Updated $result leads to TCPA compliant\n\n";

// 3. Fix campaign_id from payload buyer_campaign_id
echo "3. Updating campaign_id from buyer_campaign_id in payload...\n";
$stmt = $pdo->query("
    SELECT id, payload, campaign_id
    FROM leads 
    WHERE source = 'SURAJ_BULK'
");

$updated = 0;
while ($lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payload = json_decode($lead['payload'], true);
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    // If buyer_campaign_id exists and is not empty, use it as campaign_id
    if (isset($payload['buyer_campaign_id']) && !empty($payload['buyer_campaign_id'])) {
        $newCampaignId = $payload['buyer_campaign_id'];
        
        // Only update if different
        if ($lead['campaign_id'] !== $newCampaignId) {
            $updateStmt = $pdo->prepare("UPDATE leads SET campaign_id = ? WHERE id = ?");
            $updateStmt->execute([$newCampaignId, $lead['id']]);
            $updated++;
        }
    }
}
echo "   Updated $updated leads with correct campaign_id\n\n";

// 4. Show sample of fixed data
echo "4. Sample of fixed leads:\n";
$samples = $pdo->query("
    SELECT id, name, campaign_id, opt_in_date, tcpa_compliant, vendor_name, buyer_name
    FROM leads 
    WHERE source = 'SURAJ_BULK'
    AND opt_in_date IS NOT NULL
    ORDER BY id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($samples as $sample) {
    echo sprintf(
        "   Lead #%d: Campaign=%s, Opt-in=%s, TCPA=%s\n",
        $sample['id'],
        $sample['campaign_id'] ?: 'none',
        $sample['opt_in_date'] ? date('m/d/Y', strtotime($sample['opt_in_date'])) : 'none',
        $sample['tcpa_compliant'] ? 'YES' : 'NO'
    );
}

echo "\n=================================\n";
echo "COMPLETE\n";
echo "=================================\n";




// Connect to PostgreSQL
$pdo = new PDO(
    'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
    'brain_user',
    'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
);

echo "=================================\n";
echo "FIXING LEAD DATA\n";
echo "=================================\n\n";

// 1. Fix opt_in_date for all Suraj leads that have timestamp in payload
echo "1. Updating opt_in_dates from payload timestamps...\n";
$stmt = $pdo->query("
    SELECT id, payload 
    FROM leads 
    WHERE source = 'SURAJ_BULK' 
    AND opt_in_date IS NULL
");

$updated = 0;
while ($lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payload = json_decode($lead['payload'], true);
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    if (isset($payload['timestamp'])) {
        try {
            $date = new DateTime($payload['timestamp']);
            $optInDate = $date->format('Y-m-d H:i:s');
            
            $updateStmt = $pdo->prepare("UPDATE leads SET opt_in_date = ? WHERE id = ?");
            $updateStmt->execute([$optInDate, $lead['id']]);
            $updated++;
        } catch (Exception $e) {
            // Skip
        }
    }
}
echo "   Updated $updated leads with opt_in_date\n\n";

// 2. Fix TCPA compliant flag for all Suraj leads
echo "2. Setting TCPA compliant for all Suraj leads...\n";
$result = $pdo->exec("
    UPDATE leads 
    SET tcpa_compliant = true 
    WHERE source = 'SURAJ_BULK' 
    AND (tcpa_compliant IS NULL OR tcpa_compliant = false)
");
echo "   Updated $result leads to TCPA compliant\n\n";

// 3. Fix campaign_id from payload buyer_campaign_id
echo "3. Updating campaign_id from buyer_campaign_id in payload...\n";
$stmt = $pdo->query("
    SELECT id, payload, campaign_id
    FROM leads 
    WHERE source = 'SURAJ_BULK'
");

$updated = 0;
while ($lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payload = json_decode($lead['payload'], true);
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    // If buyer_campaign_id exists and is not empty, use it as campaign_id
    if (isset($payload['buyer_campaign_id']) && !empty($payload['buyer_campaign_id'])) {
        $newCampaignId = $payload['buyer_campaign_id'];
        
        // Only update if different
        if ($lead['campaign_id'] !== $newCampaignId) {
            $updateStmt = $pdo->prepare("UPDATE leads SET campaign_id = ? WHERE id = ?");
            $updateStmt->execute([$newCampaignId, $lead['id']]);
            $updated++;
        }
    }
}
echo "   Updated $updated leads with correct campaign_id\n\n";

// 4. Show sample of fixed data
echo "4. Sample of fixed leads:\n";
$samples = $pdo->query("
    SELECT id, name, campaign_id, opt_in_date, tcpa_compliant, vendor_name, buyer_name
    FROM leads 
    WHERE source = 'SURAJ_BULK'
    AND opt_in_date IS NOT NULL
    ORDER BY id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($samples as $sample) {
    echo sprintf(
        "   Lead #%d: Campaign=%s, Opt-in=%s, TCPA=%s\n",
        $sample['id'],
        $sample['campaign_id'] ?: 'none',
        $sample['opt_in_date'] ? date('m/d/Y', strtotime($sample['opt_in_date'])) : 'none',
        $sample['tcpa_compliant'] ? 'YES' : 'NO'
    );
}

echo "\n=================================\n";
echo "COMPLETE\n";
echo "=================================\n";









