<?php

// Fix ALL IDs that have .0 appended - comprehensive fix

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
    
    echo "Fixing ALL float IDs in database...\n\n";
    
    // Fix campaign_id column directly
    echo "Fixing campaign_id column...\n";
    $stmt = $pdo->exec("
        UPDATE leads 
        SET campaign_id = REGEXP_REPLACE(campaign_id, '\\.0+$', '')
        WHERE campaign_id LIKE '%.0%'
    ");
    echo "  Fixed $stmt campaign_id values\n";
    
    // Get all leads with payload or meta data
    echo "\nProcessing payload and meta fields...\n";
    $stmt = $pdo->query("SELECT id, payload, meta FROM leads WHERE payload IS NOT NULL OR meta IS NOT NULL");
    
    $fixed = 0;
    $total = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total++;
        $needsUpdate = false;
        $updates = [];
        
        // Process payload
        if ($row['payload']) {
            $payload = json_decode($row['payload'], true);
            
            if ($payload) {
                $changed = false;
                
                // Fix all ID fields
                $fieldsToFix = [
                    'vendor_id', 'buyer_id', 'vendor_campaign_id', 'buyer_campaign_id', 
                    'campaign_id', 'lead_id', 'source_id', 'vendor campaign', 'buyer campaign'
                ];
                
                foreach ($fieldsToFix as $field) {
                    if (isset($payload[$field]) && strpos($payload[$field], '.0') !== false) {
                        $oldValue = $payload[$field];
                        $payload[$field] = preg_replace('/\.0+$/', '', $payload[$field]);
                        if ($oldValue !== $payload[$field]) {
                            $changed = true;
                        }
                    }
                }
                
                if ($changed) {
                    $updates['payload'] = json_encode($payload);
                    $needsUpdate = true;
                }
            }
        }
        
        // Process meta
        if ($row['meta']) {
            $meta = json_decode($row['meta'], true);
            
            if ($meta) {
                $changed = false;
                
                // Fix vendor_campaign_id and other IDs in meta
                $fieldsToFix = ['vendor_campaign_id', 'vendor_id', 'buyer_id', 'lead_id'];
                
                foreach ($fieldsToFix as $field) {
                    if (isset($meta[$field]) && strpos($meta[$field], '.0') !== false) {
                        $oldValue = $meta[$field];
                        $meta[$field] = preg_replace('/\.0+$/', '', $meta[$field]);
                        if ($oldValue !== $meta[$field]) {
                            $changed = true;
                        }
                    }
                }
                
                if ($changed) {
                    $updates['meta'] = json_encode($meta);
                    $needsUpdate = true;
                }
            }
        }
        
        // Apply updates if needed
        if ($needsUpdate) {
            $sql = "UPDATE leads SET ";
            $params = [];
            $setParts = [];
            
            foreach ($updates as $field => $value) {
                $setParts[] = "$field = :$field";
                $params[$field] = $value;
            }
            
            $sql .= implode(', ', $setParts) . " WHERE id = :id";
            $params['id'] = $row['id'];
            
            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute($params);
            
            $fixed++;
            
            if ($fixed % 100 == 0) {
                echo "  Fixed $fixed leads...\n";
            }
        }
        
        if ($total % 1000 == 0) {
            echo "  Processed $total leads...\n";
        }
    }
    
    echo "\n========================================\n";
    echo "COMPLETE!\n";
    echo "Total processed: $total\n";
    echo "Fixed: $fixed\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
