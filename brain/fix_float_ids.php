<?php

// Fix IDs that have .0 appended from Excel import

require_once __DIR__ . '/vendor/autoload.php';

$dbConfig = [
    'host' => 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com',
    'port' => 5432,
    'dbname' => 'brain_production',
    'user' => 'brain_user',
    'password' => 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
];

try {
    $pdo = new PDO(
        "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Fixing float IDs in database...\n\n";
    
    // Get all leads with payload data
    $stmt = $pdo->query("SELECT id, payload, meta FROM leads WHERE source IN ('leadsquotingfast', 'LQF', 'LQF_BULK') LIMIT 1000");
    
    $fixed = 0;
    $total = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total++;
        $needsUpdate = false;
        
        // Process payload
        $payload = $row['payload'];
        if ($payload) {
            $originalPayload = $payload;
            
            // Handle double-encoded JSON
            $payloadData = json_decode($payload, true);
            if (is_string($payloadData)) {
                $payloadData = json_decode($payloadData, true);
            }
            
            if ($payloadData) {
                // Fix IDs in payload
                $fieldsToFix = ['vendor_id', 'buyer_id', 'vendor_campaign_id', 'buyer_campaign_id', 'campaign_id', 'lead_id', 'source_id'];
                
                foreach ($fieldsToFix as $field) {
                    if (isset($payloadData[$field])) {
                        $value = $payloadData[$field];
                        
                        // Check if it needs fixing
                        if (is_numeric($value) || (is_string($value) && preg_match('/^\d+\.0+$/', $value))) {
                            // Remove .0 from the end
                            $cleanValue = strval($value);
                            if (strpos($cleanValue, '.') !== false) {
                                $cleanValue = rtrim(rtrim($cleanValue, '0'), '.');
                            }
                            
                            if ($cleanValue !== strval($value)) {
                                $payloadData[$field] = $cleanValue;
                                $needsUpdate = true;
                                echo "  Fixed {$field}: {$value} -> {$cleanValue}\n";
                            }
                        }
                    }
                }
                
                if ($needsUpdate) {
                    // Re-encode the payload
                    $newPayload = json_encode($payloadData);
                    
                    // Update the database
                    $updateStmt = $pdo->prepare("UPDATE leads SET payload = :payload WHERE id = :id");
                    $updateStmt->execute([
                        'payload' => $newPayload,
                        'id' => $row['id']
                    ]);
                    
                    $fixed++;
                    echo "Updated lead ID {$row['id']}\n";
                }
            }
        }
        
        // Process meta
        $meta = $row['meta'];
        if ($meta) {
            $metaData = json_decode($meta, true);
            if ($metaData) {
                $metaNeedsUpdate = false;
                
                // Fix vendor_campaign_id in meta
                if (isset($metaData['vendor_campaign_id'])) {
                    $value = $metaData['vendor_campaign_id'];
                    if (is_numeric($value) || (is_string($value) && preg_match('/^\d+\.0+$/', $value))) {
                        $cleanValue = strval($value);
                        if (strpos($cleanValue, '.') !== false) {
                            $cleanValue = rtrim(rtrim($cleanValue, '0'), '.');
                        }
                        
                        if ($cleanValue !== strval($value)) {
                            $metaData['vendor_campaign_id'] = $cleanValue;
                            $metaNeedsUpdate = true;
                            echo "  Fixed vendor_campaign_id in meta: {$value} -> {$cleanValue}\n";
                        }
                    }
                }
                
                if ($metaNeedsUpdate) {
                    $newMeta = json_encode($metaData);
                    $updateStmt = $pdo->prepare("UPDATE leads SET meta = :meta WHERE id = :id");
                    $updateStmt->execute([
                        'meta' => $newMeta,
                        'id' => $row['id']
                    ]);
                    
                    if (!$needsUpdate) {
                        $fixed++;
                    }
                    echo "Updated meta for lead ID {$row['id']}\n";
                }
            }
        }
        
        if ($total % 100 == 0) {
            echo "Processed {$total} leads...\n";
        }
    }
    
    echo "\n========================================\n";
    echo "COMPLETE!\n";
    echo "Total processed: {$total}\n";
    echo "Fixed: {$fixed}\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
