#!/usr/bin/env php
<?php
/**
 * Direct ViciDial to Brain Sync
 * Uses direct database queries without SSH tunnel
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
echo "           BRAIN DATABASE LEAD ANALYSIS                        \n";
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
    
    // Analyze current state
    echo "📊 CURRENT BRAIN DATABASE STATE:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Total leads
    $stmt = $brain_pdo->query("SELECT COUNT(*) FROM leads");
    $total = $stmt->fetchColumn();
    echo "Total Leads: " . number_format($total) . "\n\n";
    
    // Leads by list
    $stmt = $brain_pdo->query("
        SELECT 
            vici_list_id as list_id,
            COUNT(*) as count,
            COUNT(CASE WHEN external_lead_id IS NOT NULL THEN 1 END) as with_external_id
        FROM leads
        GROUP BY vici_list_id
        ORDER BY 
            CASE 
                WHEN vici_list_id IS NULL THEN 0
                WHEN vici_list_id = '0' THEN 1
                WHEN vici_list_id::text ~ '^[0-9]+$' THEN CAST(vici_list_id AS INTEGER) + 1000
                ELSE 999999
            END
        LIMIT 20
    ");
    
    echo "LEADS BY VICIDIAL LIST:\n";
    echo "List ID          | Total Leads | With External ID\n";
    echo "-----------------|-------------|------------------\n";
    
    $list_0_count = 0;
    $null_list_count = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['list_id'] == '0') {
            $list_0_count = $row['count'];
        }
        if ($row['list_id'] === null) {
            $null_list_count = $row['count'];
        }
        
        $display_id = $row['list_id'] ?? 'NULL';
        printf("%-16s | %11s | %16s\n", 
            $display_id,
            number_format($row['count']),
            number_format($row['with_external_id'])
        );
    }
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "KEY FINDINGS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    if ($list_0_count > 0) {
        echo "⚠️  " . number_format($list_0_count) . " leads in List 0 (need redistribution)\n";
    }
    if ($null_list_count > 0) {
        echo "⚠️  " . number_format($null_list_count) . " leads with NULL list_id\n";
    }
    
    // Check for leads without external IDs
    $stmt = $brain_pdo->query("
        SELECT COUNT(*) 
        FROM leads 
        WHERE external_lead_id IS NULL OR external_lead_id = ''
    ");
    $no_external = $stmt->fetchColumn();
    
    if ($no_external > 0) {
        echo "⚠️  " . number_format($no_external) . " leads without external_lead_id\n";
    }
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "RECOMMENDED ACTIONS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Generate external IDs for leads without them\n";
    echo "2. Redistribute List 0 leads to proper ViciDial lists\n";
    echo "3. Export leads for ViciDial import\n";
    echo "\n";
    echo "Choose action (1-3): ";
    
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    
    switch($choice) {
        case '1':
            echo "\n🔧 GENERATING EXTERNAL IDs...\n";
            
            $stmt = $brain_pdo->prepare("
                UPDATE leads 
                SET external_lead_id = CONCAT(
                    CAST(EXTRACT(EPOCH FROM NOW()) * 1000 AS BIGINT)::text,
                    LPAD(
                        CAST(id % 1000 AS TEXT),
                        3,
                        '0'
                    )
                )
                WHERE external_lead_id IS NULL OR external_lead_id = ''
                RETURNING id, external_lead_id
            ");
            $stmt->execute();
            
            $count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count++;
                if ($count <= 5) {
                    echo "  Generated: Lead " . $row['id'] . " -> " . $row['external_lead_id'] . "\n";
                }
            }
            
            echo "\n✅ Generated external IDs for " . number_format($count) . " leads\n";
            break;
            
        case '2':
            echo "\n🔄 REDISTRIBUTING LEADS...\n";
            echo "\nTarget lists:\n";
            echo "A. Lists 6018-6026 (Auto Manual)\n";
            echo "B. Lists 101-111 (Test A - 48 call persistence)\n";
            echo "C. Lists 150-153 (Test B - 12-18 call optimized)\n";
            echo "D. Split evenly across all above\n";
            echo "\nChoose distribution (A-D): ";
            
            $dist_choice = strtoupper(trim(fgets($handle)));
            
            $target_lists = [];
            switch($dist_choice) {
                case 'A':
                    $target_lists = range(6018, 6026);
                    break;
                case 'B':
                    $target_lists = range(101, 111);
                    break;
                case 'C':
                    $target_lists = range(150, 153);
                    break;
                case 'D':
                    $target_lists = array_merge(
                        range(6018, 6026),
                        range(101, 111),
                        range(150, 153)
                    );
                    break;
                default:
                    echo "Invalid choice\n";
                    exit(1);
            }
            
            echo "\nRedistributing to lists: " . implode(', ', $target_lists) . "\n";
            
            // Get leads to redistribute
            $total_to_move = $list_0_count + $null_list_count;
            $per_list = ceil($total_to_move / count($target_lists));
            
            $moved = 0;
            foreach ($target_lists as $index => $list_id) {
                $limit = min($per_list, $total_to_move - $moved);
                if ($limit <= 0) break;
                
                $stmt = $brain_pdo->prepare("
                    UPDATE leads 
                    SET vici_list_id = :list_id
                    WHERE id IN (
                        SELECT id FROM leads 
                        WHERE vici_list_id = '0' OR vici_list_id IS NULL
                        LIMIT :limit
                    )
                ");
                
                $stmt->execute([
                    ':list_id' => $list_id,
                    ':limit' => $limit
                ]);
                
                $count = $stmt->rowCount();
                $moved += $count;
                
                echo "  Moved " . number_format($count) . " leads to List $list_id\n";
            }
            
            echo "\n✅ Redistributed " . number_format($moved) . " leads\n";
            break;
            
        case '3':
            echo "\n📤 EXPORTING LEADS FOR VICIDIAL...\n";
            echo "Enter list IDs to export (comma-separated) or 'all': ";
            $lists_input = trim(fgets($handle));
            
            $where = "";
            if ($lists_input != 'all') {
                $lists = array_map('trim', explode(',', $lists_input));
                $placeholders = array_map(function($i) { return ":list$i"; }, array_keys($lists));
                $where = "WHERE vici_list_id IN (" . implode(',', $placeholders) . ")";
            }
            
            $filename = 'brain_export_' . date('Ymd_His') . '.csv';
            
            $query = "
                SELECT 
                    external_lead_id,
                    vici_list_id,
                    phone,
                    first_name,
                    last_name,
                    address,
                    city,
                    state,
                    zip_code,
                    email
                FROM leads
                $where
                ORDER BY vici_list_id, id
            ";
            
            if ($where) {
                $stmt = $brain_pdo->prepare($query);
                foreach ($lists as $i => $list_id) {
                    $stmt->bindValue(":list$i", $list_id);
                }
                $stmt->execute();
            } else {
                $stmt = $brain_pdo->query($query);
            }
            
            $fp = fopen($filename, 'w');
            
            // Write header
            fputcsv($fp, [
                'vendor_lead_code',
                'list_id', 
                'phone_number',
                'first_name',
                'last_name',
                'address1',
                'city',
                'state',
                'postal_code',
                'email'
            ]);
            
            $count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Ensure external_lead_id exists
                if (!$row['external_lead_id']) {
                    $row['external_lead_id'] = time() . '000' . str_pad($count, 3, '0', STR_PAD_LEFT);
                }
                
                fputcsv($fp, [
                    $row['external_lead_id'],
                    $row['vici_list_id'] ?: '999',
                    $row['phone'],
                    $row['first_name'] ?: '',
                    $row['last_name'] ?: '',
                    $row['address'] ?: '',
                    $row['city'] ?: '',
                    $row['state'] ?: '',
                    $row['zip_code'] ?: '',
                    $row['email'] ?: ''
                ]);
                $count++;
            }
            
            fclose($fp);
            
            echo "\n✅ Exported " . number_format($count) . " leads to: $filename\n";
            echo "You can now import this file into ViciDial\n";
            break;
            
        default:
            echo "Invalid choice\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
