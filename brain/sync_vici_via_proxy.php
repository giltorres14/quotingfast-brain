#!/usr/bin/env php
<?php
/**
 * Sync ViciDial to Brain via Proxy
 * Pulls ViciDial data through Render proxy and matches with Brain
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
echo "        VICIDIAL → BRAIN SYNC (Via Render Proxy)               \n";
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
    echo "✅ Connected to Brain database\n\n";
    
    // Get ViciDial data via proxy
    echo "📊 VICIDIAL DATA (via Proxy):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $proxy_url = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
    
    // Get count of ViciDial leads
    $command = 'mysql -u root Q6hdjl67GRigMofv -e "SELECT list_id, COUNT(*) as count FROM vicidial_list WHERE list_id BETWEEN 6018 AND 6026 GROUP BY list_id;"';
    
    $ch = curl_init($proxy_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    if ($result && $result['success']) {
        $output = $result['output'];
        // Parse the output
        $lines = explode("\n", $output);
        $total_vici = 0;
        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s+(\d+)$/', trim($line), $matches)) {
                $list_id = $matches[1];
                $count = $matches[2];
                echo "  List $list_id: " . number_format($count) . " leads\n";
                $total_vici += $count;
            }
        }
        echo "\n  Total ViciDial Leads: " . number_format($total_vici) . "\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "BRAIN DATABASE STATUS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Get Brain stats
    $stmt = $brain_pdo->query("
        SELECT 
            COALESCE(vici_list_id::text, 'NULL') as list_id,
            COUNT(*) as count
        FROM leads
        WHERE vici_list_id = '0' OR vici_list_id IS NULL OR vici_list_id::text LIKE '60%'
        GROUP BY vici_list_id
        ORDER BY count DESC
    ");
    
    $list_0_count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  List " . $row['list_id'] . ": " . number_format($row['count']) . " leads\n";
        if ($row['list_id'] == '0') {
            $list_0_count = $row['count'];
        }
    }
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "SYNC PLAN:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Pull ViciDial leads from lists 6018-6026\n";
    echo "2. Match with Brain List 0 leads by phone number\n";
    echo "3. Update Brain leads with correct list_id and external_id\n";
    echo "4. Import any ViciDial leads not found in Brain\n";
    echo "\nProceed with sync? (y/n): ";
    
    $handle = fopen("php://stdin", "r");
    $response = strtolower(trim(fgets($handle)));
    
    if ($response !== 'y') {
        echo "Sync cancelled.\n";
        exit(0);
    }
    
    echo "\n🔄 STARTING SYNC...\n\n";
    
    $total_matched = 0;
    $total_not_in_brain = 0;
    $total_imported = 0;
    
    // Process each ViciDial list
    $lists = range(6018, 6026);
    
    foreach ($lists as $list_id) {
        echo "Processing List $list_id...\n";
        
        // Get ViciDial leads for this list (in batches)
        $offset = 0;
        $limit = 500;
        $list_matched = 0;
        $list_not_found = 0;
        $list_imported = 0;
        
        while (true) {
            $command = sprintf(
                'mysql -u root Q6hdjl67GRigMofv -e "SELECT lead_id, phone_number, first_name, last_name, address1, city, state, postal_code FROM vicidial_list WHERE list_id = %d LIMIT %d OFFSET %d;"',
                $list_id,
                $limit,
                $offset
            );
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
            $response = curl_exec($ch);
            $result = json_decode($response, true);
            
            if (!$result || !$result['success']) {
                break;
            }
            
            $output = $result['output'];
            $lines = explode("\n", $output);
            
            $leads_found = false;
            foreach ($lines as $line) {
                // Skip header and empty lines
                if (strpos($line, 'lead_id') !== false || trim($line) == '' || strpos($line, 'Could not') !== false) {
                    continue;
                }
                
                // Parse tab-separated values
                $parts = preg_split('/\s+/', trim($line), 8);
                if (count($parts) >= 2) {
                    $leads_found = true;
                    $vici_lead_id = $parts[0];
                    $phone = preg_replace('/[^0-9]/', '', $parts[1]);
                    
                    // Try to match with Brain
                    $stmt = $brain_pdo->prepare("
                        SELECT id, external_lead_id, vici_list_id
                        FROM leads
                        WHERE phone = :phone
                           OR phone = :phone_formatted
                        LIMIT 1
                    ");
                    
                    $stmt->execute([
                        ':phone' => $phone,
                        ':phone_formatted' => substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6)
                    ]);
                    
                    $brain_lead = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($brain_lead) {
                        // Update Brain lead with ViciDial info
                        $update_stmt = $brain_pdo->prepare("
                            UPDATE leads
                            SET 
                                vici_list_id = :list_id,
                                external_lead_id = CASE 
                                    WHEN external_lead_id IS NULL OR external_lead_id = '' 
                                    THEN :external_id
                                    ELSE external_lead_id
                                END,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        
                        $update_stmt->execute([
                            ':list_id' => $list_id,
                            ':external_id' => $vici_lead_id,
                            ':id' => $brain_lead['id']
                        ]);
                        
                        $list_matched++;
                    } else {
                        // Lead not in Brain - import it
                        $list_not_found++;
                        
                        // Import the lead
                        $insert_stmt = $brain_pdo->prepare("
                            INSERT INTO leads (
                                external_lead_id,
                                vici_list_id,
                                phone,
                                first_name,
                                last_name,
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
                                'imported_from_vici',
                                'vicidial_sync',
                                NOW(),
                                NOW(),
                                5
                            )
                            ON CONFLICT (phone) DO UPDATE
                            SET 
                                vici_list_id = EXCLUDED.vici_list_id,
                                external_lead_id = EXCLUDED.external_lead_id,
                                updated_at = NOW()
                        ");
                        
                        try {
                            $insert_stmt->execute([
                                ':external_id' => $vici_lead_id,
                                ':vici_list_id' => $list_id,
                                ':phone' => $phone,
                                ':first_name' => $parts[2] ?? '',
                                ':last_name' => $parts[3] ?? ''
                            ]);
                            $list_imported++;
                        } catch (Exception $e) {
                            // Duplicate or other error, skip
                        }
                    }
                }
            }
            
            if (!$leads_found) {
                break; // No more leads in this batch
            }
            
            $offset += $limit;
            
            // Show progress
            if ($offset % 2000 == 0) {
                echo "  Processed $offset leads...\n";
            }
        }
        
        echo "  List $list_id: $list_matched matched, $list_not_found not in Brain, $list_imported imported\n";
        
        $total_matched += $list_matched;
        $total_not_in_brain += $list_not_found;
        $total_imported += $list_imported;
    }
    
    curl_close($ch);
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                      SYNC COMPLETE!                           \n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ Matched with Brain: " . number_format($total_matched) . "\n";
    echo "🆕 Not in Brain (imported): " . number_format($total_imported) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Show updated Brain stats
    echo "📊 UPDATED BRAIN DATABASE:\n";
    $stmt = $brain_pdo->query("
        SELECT 
            vici_list_id,
            COUNT(*) as count
        FROM leads
        WHERE vici_list_id::text LIKE '60%'
        GROUP BY vici_list_id
        ORDER BY vici_list_id::int
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  List " . $row['vici_list_id'] . ": " . number_format($row['count']) . " leads\n";
    }
    
    echo "\n✅ Sync complete!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}









