#!/usr/bin/env php
<?php
/**
 * Sync ViciDial Leads TO Brain - Auto Import Version
 * Automatically imports missing leads into Brain
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Brain Database
$brain_host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$brain_db = 'brain_production';
$brain_user = 'brain_user';
$brain_pass = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "     VICIDIAL → BRAIN SYNC (AUTO-IMPORT MISSING LEADS)         \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo date('Y-m-d H:i:s') . " EST\n\n";

try {
    // Connect to Brain database
    $brain_pdo = new PDO(
        "pgsql:host=$brain_host;port=5432;dbname=$brain_db",
        $brain_user,
        $brain_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to Brain database\n";
    
    // Try to connect to ViciDial via SSH tunnel
    echo "Attempting to connect to ViciDial via SSH tunnel on port 3307...\n";
    
    try {
        $vici_pdo = new PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=asterisk",
            "cron",
            "1234",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✅ Connected to ViciDial database\n\n";
    } catch (Exception $e) {
        echo "❌ Could not connect to ViciDial via port 3307\n";
        echo "Trying alternative port 3306...\n";
        
        try {
            $vici_pdo = new PDO(
                "mysql:host=127.0.0.1;port=3306;dbname=asterisk",
                "cron",
                "1234",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "✅ Connected to ViciDial database on port 3306\n\n";
        } catch (Exception $e2) {
            echo "❌ Could not connect to ViciDial database\n";
            echo "Please set up SSH tunnel first:\n";
            echo "ssh -L 3307:localhost:3306 root@167.172.143.234\n";
            exit(1);
        }
    }
    
    // Define lists to sync (6018-6026 from your screenshot)
    $lists_to_sync = range(6018, 6026);
    
    echo "🔄 SYNCING VICIDIAL LISTS: " . implode(', ', $lists_to_sync) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $total_vici_leads = 0;
    $total_matched = 0;
    $total_updated = 0;
    $total_not_found = 0;
    $total_imported = 0;
    
    foreach ($lists_to_sync as $list_id) {
        echo "📋 Processing List $list_id...\n";
        
        // Get ViciDial leads from this list
        $stmt = $vici_pdo->prepare("
            SELECT 
                lead_id,
                vendor_lead_code,
                phone_number,
                first_name,
                last_name,
                address1,
                city,
                state,
                postal_code,
                email,
                status,
                called_count,
                last_local_call_time,
                list_id
            FROM vicidial_list
            WHERE list_id = :list_id
        ");
        $stmt->execute([':list_id' => $list_id]);
        
        $list_total = 0;
        $list_matched = 0;
        $list_not_found = 0;
        $list_imported = 0;
        $missing_leads = [];
        
        while ($vici_lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list_total++;
            $total_vici_leads++;
            
            // Clean phone number (remove non-digits)
            $phone = preg_replace('/[^0-9]/', '', $vici_lead['phone_number']);
            
            // Try to find matching lead in Brain by phone
            $brain_stmt = $brain_pdo->prepare("
                SELECT id, external_lead_id, vici_list_id
                FROM leads
                WHERE phone = :phone
                   OR phone = :phone_formatted
                   OR phone = :phone_plus1
                LIMIT 1
            ");
            
            $brain_stmt->execute([
                ':phone' => $phone,
                ':phone_formatted' => substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6),
                ':phone_plus1' => '1' . $phone
            ]);
            
            $brain_lead = $brain_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($brain_lead) {
                $list_matched++;
                $total_matched++;
                
                // Update Brain lead with ViciDial info
                $update_stmt = $brain_pdo->prepare("
                    UPDATE leads
                    SET 
                        external_lead_id = :external_id,
                        vici_list_id = :list_id,
                        status = CASE 
                            WHEN status = 'new' THEN 'in_vicidial'
                            ELSE status
                        END,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                
                $update_stmt->execute([
                    ':external_id' => $vici_lead['vendor_lead_code'] ?: $vici_lead['lead_id'],
                    ':list_id' => $list_id,
                    ':id' => $brain_lead['id']
                ]);
                
                $total_updated++;
            } else {
                // Lead not found - add to missing list for import
                $list_not_found++;
                $total_not_found++;
                $missing_leads[] = $vici_lead;
            }
        }
        
        // Import missing leads into Brain
        if (!empty($missing_leads)) {
            echo "  ⚠️  Found {$list_not_found} leads not in Brain. Importing...\n";
            
            foreach ($missing_leads as $lead) {
                // Generate external_lead_id if not present
                $external_id = $lead['vendor_lead_code'] ?: $lead['lead_id'];
                if (!$external_id) {
                    $external_id = time() . str_pad($lead['lead_id'], 3, '0', STR_PAD_LEFT);
                }
                
                // Clean phone
                $phone = preg_replace('/[^0-9]/', '', $lead['phone_number']);
                
                // Insert into Brain
                $insert_stmt = $brain_pdo->prepare("
                    INSERT INTO leads (
                        external_lead_id,
                        vici_list_id,
                        phone,
                        first_name,
                        last_name,
                        address,
                        city,
                        state,
                        zip_code,
                        email,
                        status,
                        source,
                        created_at,
                        updated_at,
                        tenant_id
                    ) VALUES (
                        :external_id,
                        :vici_list_id,
                        :phone,
                        :first_name,
                        :last_name,
                        :address,
                        :city,
                        :state,
                        :zip_code,
                        :email,
                        'imported_from_vici',
                        'vicidial_import',
                        NOW(),
                        NOW(),
                        5
                    )
                    ON CONFLICT (external_lead_id) DO UPDATE
                    SET 
                        vici_list_id = EXCLUDED.vici_list_id,
                        updated_at = NOW()
                ");
                
                try {
                    $insert_stmt->execute([
                        ':external_id' => $external_id,
                        ':vici_list_id' => $lead['list_id'],
                        ':phone' => $phone,
                        ':first_name' => $lead['first_name'] ?: '',
                        ':last_name' => $lead['last_name'] ?: '',
                        ':address' => $lead['address1'] ?: '',
                        ':city' => $lead['city'] ?: '',
                        ':state' => $lead['state'] ?: '',
                        ':zip_code' => $lead['postal_code'] ?: '',
                        ':email' => $lead['email'] ?: ''
                    ]);
                    
                    $list_imported++;
                    $total_imported++;
                } catch (Exception $e) {
                    // Might be duplicate phone or other constraint
                    // Continue with next lead
                }
            }
            
            echo "  ✅ Imported {$list_imported} new leads into Brain\n";
        }
        
        echo "  List $list_id Complete: {$list_total} total, {$list_matched} matched, {$list_imported} imported\n\n";
    }
    
    // Also check Test A and Test B lists if they exist
    echo "📋 Checking Test Lists (101-111, 150-153)...\n";
    
    $test_lists = array_merge(range(101, 111), range(150, 153));
    foreach ($test_lists as $list_id) {
        $stmt = $vici_pdo->prepare("SELECT COUNT(*) FROM vicidial_list WHERE list_id = :list_id");
        $stmt->execute([':list_id' => $list_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "  List $list_id: $count leads found in ViciDial\n";
            
            // Process this list too
            $stmt = $vici_pdo->prepare("
                SELECT * FROM vicidial_list WHERE list_id = :list_id
            ");
            $stmt->execute([':list_id' => $list_id]);
            
            $list_matched = 0;
            $list_imported = 0;
            
            while ($vici_lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_vici_leads++;
                $phone = preg_replace('/[^0-9]/', '', $vici_lead['phone_number']);
                
                // Check if exists in Brain
                $check = $brain_pdo->prepare("
                    SELECT id FROM leads 
                    WHERE phone = :phone OR phone = :phone_formatted
                    LIMIT 1
                ");
                $check->execute([
                    ':phone' => $phone,
                    ':phone_formatted' => substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6)
                ]);
                
                if ($check->fetch()) {
                    $list_matched++;
                    $total_matched++;
                } else {
                    // Import this lead
                    $external_id = $vici_lead['vendor_lead_code'] ?: time() . str_pad($vici_lead['lead_id'], 3, '0', STR_PAD_LEFT);
                    
                    $insert_stmt = $brain_pdo->prepare("
                        INSERT INTO leads (
                            external_lead_id, vici_list_id, phone, first_name, last_name,
                            address, city, state, zip_code, email, status, source,
                            created_at, updated_at, tenant_id
                        ) VALUES (
                            :external_id, :vici_list_id, :phone, :first_name, :last_name,
                            :address, :city, :state, :zip_code, :email,
                            'imported_from_vici', 'vicidial_import', NOW(), NOW(), 5
                        )
                        ON CONFLICT (external_lead_id) DO NOTHING
                    ");
                    
                    try {
                        $insert_stmt->execute([
                            ':external_id' => $external_id,
                            ':vici_list_id' => $list_id,
                            ':phone' => $phone,
                            ':first_name' => $vici_lead['first_name'] ?: '',
                            ':last_name' => $vici_lead['last_name'] ?: '',
                            ':address' => $vici_lead['address1'] ?: '',
                            ':city' => $vici_lead['city'] ?: '',
                            ':state' => $vici_lead['state'] ?: '',
                            ':zip_code' => $vici_lead['postal_code'] ?: '',
                            ':email' => $vici_lead['email'] ?: ''
                        ]);
                        $list_imported++;
                        $total_imported++;
                    } catch (Exception $e) {
                        // Continue
                    }
                }
            }
            
            if ($list_imported > 0) {
                echo "    ✅ Imported $list_imported new leads\n";
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                      SYNC COMPLETE!                           \n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "📊 FINAL RESULTS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "  Total ViciDial Leads Processed: " . number_format($total_vici_leads) . "\n";
    echo "  ✅ Matched with Brain: " . number_format($total_matched) . "\n";
    echo "  ✅ Updated in Brain: " . number_format($total_updated) . "\n";
    echo "  🆕 Not Found (were missing): " . number_format($total_not_found) . "\n";
    echo "  ✅ Imported into Brain: " . number_format($total_imported) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Show updated Brain statistics
    echo "📊 UPDATED BRAIN DATABASE:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $stmt = $brain_pdo->query("
        SELECT 
            vici_list_id,
            COUNT(*) as count
        FROM leads
        WHERE vici_list_id IS NOT NULL
        GROUP BY vici_list_id
        ORDER BY 
            CASE 
                WHEN vici_list_id ~ '^[0-9]+$' THEN CAST(vici_list_id AS INTEGER)
                ELSE 999999
            END
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("  List %-10s: %s leads\n", $row['vici_list_id'], number_format($row['count']));
    }
    
    // Count leads with external IDs
    $stmt = $brain_pdo->query("
        SELECT COUNT(*) FROM leads WHERE external_lead_id IS NOT NULL AND external_lead_id != ''
    ");
    $with_external = $stmt->fetchColumn();
    
    echo "\n✅ Leads with External IDs: " . number_format($with_external) . "\n";
    echo "✅ Brain and ViciDial are now synchronized!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Set up SSH tunnel: ssh -L 3307:localhost:3306 root@167.172.143.234\n";
    echo "2. Make sure you have SSH access to the ViciDial server\n";
    echo "3. Check database credentials\n";
}


