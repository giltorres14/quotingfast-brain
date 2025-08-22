#!/usr/bin/env php
<?php
/**
 * Sync ViciDial Leads TO Brain
 * Pulls leads from ViciDial and matches them with Brain database
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
echo "        VICIDIAL → BRAIN LEAD SYNC TOOL                        \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo date('Y-m-d H:i:s') . " EST\n\n";

echo "This tool will:\n";
echo "1. Connect to ViciDial via SSH tunnel\n";
echo "2. Pull leads from ViciDial lists (6018-6026, etc.)\n";
echo "3. Match them with Brain leads by phone number\n";
echo "4. Update Brain with correct ViciDial IDs\n\n";

echo "⚠️  IMPORTANT: You need to set up SSH tunnel first!\n";
echo "Run this in another terminal:\n";
echo "ssh -L 3307:localhost:3306 root@167.172.143.234\n\n";

echo "Is the SSH tunnel running? (y/n): ";
$handle = fopen("php://stdin", "r");
$response = strtolower(trim(fgets($handle)));

if ($response !== 'y') {
    echo "Please set up the SSH tunnel first, then run this script again.\n";
    exit(1);
}

try {
    // Connect to Brain database
    $brain_pdo = new PDO(
        "pgsql:host=$brain_host;port=5432;dbname=$brain_db",
        $brain_user,
        $brain_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to Brain database\n";
    
    // Connect to ViciDial via SSH tunnel
    $vici_pdo = new PDO(
        "mysql:host=127.0.0.1;port=3307;dbname=asterisk",
        "cron",
        "1234",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to ViciDial database via SSH tunnel\n\n";
    
    // Get ViciDial lists
    echo "📋 VICIDIAL LISTS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $vici_pdo->query("
        SELECT 
            list_id,
            list_name,
            list_description,
            active,
            COUNT(lead_id) as lead_count
        FROM vicidial_lists vl
        LEFT JOIN vicidial_list vlist ON vl.list_id = vlist.list_id
        WHERE vl.list_id BETWEEN 6018 AND 6026
           OR vl.list_id BETWEEN 101 AND 111
           OR vl.list_id BETWEEN 150 AND 153
        GROUP BY vl.list_id
        ORDER BY vl.list_id
    ");
    
    $vici_lists = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vici_lists[] = $row['list_id'];
        printf("  List %s: %s (%s leads) - %s\n", 
            $row['list_id'],
            $row['list_name'] ?? 'No name',
            number_format($row['lead_count']),
            $row['active'] == 'Y' ? 'Active' : 'Inactive'
        );
    }
    
    if (empty($vici_lists)) {
        echo "\n❌ No ViciDial lists found in the expected ranges.\n";
        echo "Please check ViciDial for available lists.\n";
        exit(1);
    }
    
    echo "\nWhich lists do you want to sync?\n";
    echo "1. Lists 6018-6026 (Auto Manual)\n";
    echo "2. Lists 101-111 (Test A)\n";
    echo "3. Lists 150-153 (Test B)\n";
    echo "4. All of the above\n";
    echo "5. Custom (enter list IDs)\n";
    echo "\nEnter choice (1-5): ";
    
    $choice = trim(fgets($handle));
    
    $lists_to_sync = [];
    switch($choice) {
        case '1':
            $lists_to_sync = range(6018, 6026);
            break;
        case '2':
            $lists_to_sync = range(101, 111);
            break;
        case '3':
            $lists_to_sync = range(150, 153);
            break;
        case '4':
            $lists_to_sync = array_merge(range(6018, 6026), range(101, 111), range(150, 153));
            break;
        case '5':
            echo "Enter list IDs (comma-separated): ";
            $custom = trim(fgets($handle));
            $lists_to_sync = array_map('trim', explode(',', $custom));
            break;
        default:
            echo "Invalid choice\n";
            exit(1);
    }
    
    echo "\n🔄 SYNCING VICIDIAL LISTS: " . implode(', ', $lists_to_sync) . "\n";
    echo "----------------------------------------\n";
    
    $total_matched = 0;
    $total_updated = 0;
    $total_not_found = 0;
    
    foreach ($lists_to_sync as $list_id) {
        echo "\nProcessing List $list_id...\n";
        
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
                last_local_call_time
            FROM vicidial_list
            WHERE list_id = :list_id
        ");
        $stmt->execute([':list_id' => $list_id]);
        
        $list_matched = 0;
        $list_updated = 0;
        $list_not_found = 0;
        
        while ($vici_lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                
                $list_updated++;
                
                if ($list_updated <= 3) {
                    echo "  ✓ Matched: Phone {$vici_lead['phone_number']} -> Brain ID {$brain_lead['id']}\n";
                }
            } else {
                $list_not_found++;
                
                if ($list_not_found <= 3) {
                    echo "  ✗ Not found in Brain: {$vici_lead['phone_number']} ({$vici_lead['first_name']} {$vici_lead['last_name']})\n";
                }
            }
        }
        
        echo "  List $list_id Results: $list_matched matched, $list_updated updated, $list_not_found not found in Brain\n";
        
        $total_matched += $list_matched;
        $total_updated += $list_updated;
        $total_not_found += $list_not_found;
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "SYNC COMPLETE!\n";
    echo "----------------------------------------\n";
    echo "✅ Total Matched: " . number_format($total_matched) . "\n";
    echo "✅ Total Updated: " . number_format($total_updated) . "\n";
    echo "⚠️  Not Found in Brain: " . number_format($total_not_found) . "\n";
    
    if ($total_not_found > 0) {
        echo "\n📝 NEXT STEPS:\n";
        echo "The $total_not_found leads not found in Brain are in ViciDial but not in Brain.\n";
        echo "Options:\n";
        echo "1. Import these missing leads into Brain\n";
        echo "2. Continue with only matched leads\n";
        echo "3. Export missing leads for review\n";
        echo "\nWould you like to export the missing leads? (y/n): ";
        
        if (strtolower(trim(fgets($handle))) == 'y') {
            $filename = 'vici_leads_not_in_brain_' . date('Ymd_His') . '.csv';
            $fp = fopen($filename, 'w');
            
            // Write header
            fputcsv($fp, [
                'lead_id',
                'vendor_lead_code',
                'list_id',
                'phone_number',
                'first_name',
                'last_name',
                'city',
                'state',
                'status'
            ]);
            
            // Export missing leads
            foreach ($lists_to_sync as $list_id) {
                $stmt = $vici_pdo->prepare("
                    SELECT * FROM vicidial_list
                    WHERE list_id = :list_id
                ");
                $stmt->execute([':list_id' => $list_id]);
                
                while ($vici_lead = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $phone = preg_replace('/[^0-9]/', '', $vici_lead['phone_number']);
                    
                    // Check if exists in Brain
                    $check = $brain_pdo->prepare("
                        SELECT COUNT(*) FROM leads 
                        WHERE phone = :phone 
                           OR phone = :phone_formatted
                           OR phone = :phone_plus1
                    ");
                    $check->execute([
                        ':phone' => $phone,
                        ':phone_formatted' => substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6),
                        ':phone_plus1' => '1' . $phone
                    ]);
                    
                    if ($check->fetchColumn() == 0) {
                        fputcsv($fp, [
                            $vici_lead['lead_id'],
                            $vici_lead['vendor_lead_code'],
                            $list_id,
                            $vici_lead['phone_number'],
                            $vici_lead['first_name'],
                            $vici_lead['last_name'],
                            $vici_lead['city'],
                            $vici_lead['state'],
                            $vici_lead['status']
                        ]);
                    }
                }
            }
            
            fclose($fp);
            echo "\n✅ Exported missing leads to: $filename\n";
        }
    }
    
    // Show updated Brain statistics
    echo "\n📊 UPDATED BRAIN DATABASE STATISTICS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $brain_pdo->query("
        SELECT 
            vici_list_id,
            COUNT(*) as count
        FROM leads
        WHERE vici_list_id IS NOT NULL
        GROUP BY vici_list_id
        ORDER BY vici_list_id::int
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("  List %-10s: %s leads\n", $row['vici_list_id'], number_format($row['count']));
    }
    
    echo "\n✅ Sync complete! Brain database now has correct ViciDial IDs.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure SSH tunnel is running: ssh -L 3307:localhost:3306 root@167.172.143.234\n";
    echo "2. Check ViciDial credentials\n";
    echo "3. Verify network connectivity\n";
}


