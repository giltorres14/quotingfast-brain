#!/usr/bin/env php
<?php
/**
 * Sync Brain Leads with ViciDial
 * Matches leads and assigns external IDs
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

// ViciDial Database (via SSH tunnel)
$vici_host = '127.0.0.1';
$vici_port = '3307'; // Local port for SSH tunnel
$vici_db = 'asterisk';
$vici_user = 'cron';
$vici_pass = '1234';

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "           BRAIN <-> VICIDIAL LEAD SYNC TOOL                   \n";
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
    
    // Get Brain lead statistics
    $stmt = $brain_pdo->query("
        SELECT 
            vici_list_id,
            COUNT(*) as count
        FROM leads
        GROUP BY vici_list_id
        ORDER BY count DESC
        LIMIT 10
    ");
    
    echo "\n📊 BRAIN DATABASE - Lead Distribution:\n";
    echo "----------------------------------------\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $list = $row['vici_list_id'] ?? 'NULL';
        printf("  List %-10s: %s leads\n", $list, number_format($row['count']));
    }
    
    // Count leads without external IDs
    $stmt = $brain_pdo->query("
        SELECT COUNT(*) as count
        FROM leads
        WHERE external_lead_id IS NULL OR external_lead_id = ''
    ");
    $no_external = $stmt->fetchColumn();
    
    echo "\n⚠️  Leads without external_lead_id: " . number_format($no_external) . "\n";
    
    // Get leads in List 0 that need redistribution
    $stmt = $brain_pdo->query("
        SELECT COUNT(*) as count
        FROM leads
        WHERE vici_list_id = '0' OR vici_list_id IS NULL
    ");
    $list_zero = $stmt->fetchColumn();
    
    echo "⚠️  Leads in List 0 or NULL: " . number_format($list_zero) . "\n";
    
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "SYNC OPTIONS:\n";
    echo "1. Generate external_lead_ids for leads without them\n";
    echo "2. Redistribute List 0 leads to proper ViciDial lists\n";
    echo "3. Match Brain leads with ViciDial by phone number\n";
    echo "4. Export Brain leads for ViciDial import\n";
    echo "5. Show detailed statistics\n";
    echo "\nEnter option (1-5): ";
    
    $handle = fopen("php://stdin", "r");
    $option = trim(fgets($handle));
    
    switch($option) {
        case '1':
            echo "\n🔧 GENERATING EXTERNAL IDs...\n";
            
            // Generate external_lead_ids for leads without them
            $stmt = $brain_pdo->prepare("
                UPDATE leads 
                SET external_lead_id = CONCAT(
                    EXTRACT(EPOCH FROM NOW())::bigint::text,
                    LPAD(id::text, 3, '0')
                )
                WHERE external_lead_id IS NULL OR external_lead_id = ''
                RETURNING id, external_lead_id
            ");
            $stmt->execute();
            
            $updated = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $updated++;
                if ($updated <= 5) {
                    echo "  Lead ID " . $row['id'] . " -> External ID: " . $row['external_lead_id'] . "\n";
                }
            }
            
            echo "\n✅ Generated external IDs for " . number_format($updated) . " leads\n";
            break;
            
        case '2':
            echo "\n🔄 REDISTRIBUTING LIST 0 LEADS...\n";
            echo "Target lists for redistribution:\n";
            echo "  - Lists 101-111: Test A (48-call persistence)\n";
            echo "  - Lists 150-153: Test B (12-18 call optimized)\n";
            echo "  - Lists 6018-6026: Auto Manual lists\n";
            echo "\nChoose distribution:\n";
            echo "  A. Evenly across Test A lists (101-111)\n";
            echo "  B. Evenly across Test B lists (150-153)\n";
            echo "  C. Split 50/50 between Test A and Test B\n";
            echo "  D. Move to Auto Manual lists (6018-6026)\n";
            echo "\nEnter choice (A-D): ";
            
            $choice = strtoupper(trim(fgets($handle)));
            
            $lists = [];
            switch($choice) {
                case 'A':
                    $lists = range(101, 111);
                    break;
                case 'B':
                    $lists = range(150, 153);
                    break;
                case 'C':
                    $lists = array_merge(range(101, 111), range(150, 153));
                    break;
                case 'D':
                    $lists = range(6018, 6026);
                    break;
                default:
                    echo "Invalid choice\n";
                    exit(1);
            }
            
            // Redistribute leads
            $batch_size = 1000;
            $list_index = 0;
            $total_moved = 0;
            
            echo "\nRedistributing to lists: " . implode(', ', $lists) . "\n";
            
            $stmt = $brain_pdo->prepare("
                UPDATE leads 
                SET vici_list_id = :list_id
                WHERE id IN (
                    SELECT id FROM leads 
                    WHERE vici_list_id = '0' OR vici_list_id IS NULL
                    LIMIT :batch_size
                    OFFSET :offset
                )
            ");
            
            for ($offset = 0; ; $offset += $batch_size) {
                $target_list = $lists[$list_index % count($lists)];
                
                $stmt->execute([
                    ':list_id' => $target_list,
                    ':batch_size' => $batch_size,
                    ':offset' => $offset
                ]);
                
                $moved = $stmt->rowCount();
                if ($moved == 0) break;
                
                $total_moved += $moved;
                $list_index++;
                
                echo "  Moved $moved leads to List $target_list (Total: $total_moved)\n";
            }
            
            echo "\n✅ Redistributed " . number_format($total_moved) . " leads\n";
            break;
            
        case '3':
            echo "\n🔍 MATCHING WITH VICIDIAL BY PHONE...\n";
            echo "This requires ViciDial database access via SSH tunnel.\n";
            echo "Run this command in another terminal first:\n";
            echo "ssh -L 3307:localhost:3306 root@167.172.143.234\n";
            echo "\nIs the SSH tunnel running? (y/n): ";
            
            if (strtolower(trim(fgets($handle))) != 'y') {
                echo "Please set up the SSH tunnel first.\n";
                break;
            }
            
            try {
                $vici_pdo = new PDO(
                    "mysql:host=$vici_host;port=$vici_port;dbname=$vici_db",
                    $vici_user,
                    $vici_pass
                );
                
                echo "✅ Connected to ViciDial database\n";
                
                // Match leads by phone number
                // This is a simplified example - adjust based on your needs
                echo "Matching leads... (this may take a while)\n";
                
            } catch (Exception $e) {
                echo "❌ Could not connect to ViciDial: " . $e->getMessage() . "\n";
                echo "Make sure the SSH tunnel is running.\n";
            }
            break;
            
        case '4':
            echo "\n📤 EXPORTING BRAIN LEADS FOR VICIDIAL...\n";
            echo "Enter target list IDs (comma-separated, or 'all'): ";
            $lists_input = trim(fgets($handle));
            
            $where_clause = "";
            if ($lists_input != 'all') {
                $lists = array_map('trim', explode(',', $lists_input));
                $where_clause = "WHERE vici_list_id IN ('" . implode("','", $lists) . "')";
            }
            
            $filename = 'brain_leads_export_' . date('Ymd_His') . '.csv';
            
            $stmt = $brain_pdo->query("
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
                $where_clause
                ORDER BY vici_list_id, id
            ");
            
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
                fputcsv($fp, [
                    $row['external_lead_id'],
                    $row['vici_list_id'],
                    $row['phone'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['address'],
                    $row['city'],
                    $row['state'],
                    $row['zip_code'],
                    $row['email']
                ]);
                $count++;
            }
            
            fclose($fp);
            
            echo "\n✅ Exported " . number_format($count) . " leads to $filename\n";
            echo "You can now import this file into ViciDial.\n";
            break;
            
        case '5':
            echo "\n📊 DETAILED STATISTICS:\n";
            echo str_repeat("-", 60) . "\n";
            
            // Get more detailed stats
            $stmt = $brain_pdo->query("
                SELECT 
                    vici_list_id,
                    status,
                    COUNT(*) as count
                FROM leads
                GROUP BY vici_list_id, status
                ORDER BY vici_list_id, count DESC
            ");
            
            $current_list = null;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $list = $row['vici_list_id'] ?? 'NULL';
                if ($list != $current_list) {
                    if ($current_list !== null) echo "\n";
                    echo "List $list:\n";
                    $current_list = $list;
                }
                printf("  %-20s: %s\n", $row['status'], number_format($row['count']));
            }
            break;
            
        default:
            echo "Invalid option\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}







