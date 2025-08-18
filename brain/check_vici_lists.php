<?php

// Direct connection to Vici database
$host = '37.27.138.222';
$db = 'asterisk';
$user = 'Superman';
$pass = '8ZDWGAAQRD';
$port = 3306;

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to Vici database successfully!\n\n";
    
    // Check if Autodial campaign exists
    $stmt = $pdo->prepare("SELECT campaign_id, campaign_name, active, dial_method FROM vicidial_campaigns WHERE campaign_id = 'Autodial'");
    $stmt->execute();
    $campaign = $stmt->fetch();
    
    if ($campaign) {
        echo "=== AUTODIAL CAMPAIGN INFO ===\n";
        echo "Campaign ID: " . $campaign['campaign_id'] . "\n";
        echo "Campaign Name: " . $campaign['campaign_name'] . "\n";
        echo "Active: " . $campaign['active'] . "\n";
        echo "Dial Method: " . $campaign['dial_method'] . "\n\n";
    } else {
        echo "Autodial campaign not found!\n\n";
    }
    
    // Get lists associated with Autodial campaign
    echo "=== LISTS IN AUTODIAL CAMPAIGN ===\n\n";
    
    // Check campaign_lists table
    $stmt = $pdo->prepare("
        SELECT cl.list_id, l.list_name, l.list_description, l.active, l.campaign_id
        FROM vicidial_campaign_lists cl
        LEFT JOIN vicidial_lists l ON cl.list_id = l.list_id
        WHERE cl.campaign_id = 'Autodial'
        ORDER BY cl.list_id
    ");
    $stmt->execute();
    $lists = $stmt->fetchAll();
    
    if (empty($lists)) {
        echo "No lists found in campaign_lists for Autodial.\n\n";
        
        // Check if there are lists with campaign_id = 'Autodial'
        $stmt = $pdo->prepare("
            SELECT list_id, list_name, list_description, active
            FROM vicidial_lists
            WHERE campaign_id = 'Autodial'
            ORDER BY list_id
        ");
        $stmt->execute();
        $lists = $stmt->fetchAll();
    }
    
    if (!empty($lists)) {
        foreach ($lists as $list) {
            echo "List ID: " . $list['list_id'] . "\n";
            echo "Name: " . ($list['list_name'] ?? 'N/A') . "\n";
            echo "Description: " . ($list['list_description'] ?? 'N/A') . "\n";
            echo "Active: " . ($list['active'] ?? 'N/A') . "\n";
            
            // Count leads in this list
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vicidial_list WHERE list_id = ?");
            $stmt->execute([$list['list_id']]);
            $count = $stmt->fetchColumn();
            echo "Total Leads: " . $count . "\n";
            
            // Get status breakdown
            $stmt = $pdo->prepare("
                SELECT status, COUNT(*) as count 
                FROM vicidial_list 
                WHERE list_id = ? 
                GROUP BY status 
                ORDER BY count DESC
            ");
            $stmt->execute([$list['list_id']]);
            $statuses = $stmt->fetchAll();
            
            if (!empty($statuses)) {
                echo "Lead Status Breakdown:\n";
                foreach ($statuses as $status) {
                    echo "  - " . $status['status'] . ": " . $status['count'] . "\n";
                }
            }
            
            // Sample some leads to check vendor_lead_code
            $stmt = $pdo->prepare("
                SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, source_id, status
                FROM vicidial_list 
                WHERE list_id = ? 
                LIMIT 5
            ");
            $stmt->execute([$list['list_id']]);
            $sampleLeads = $stmt->fetchAll();
            
            if (!empty($sampleLeads)) {
                echo "\nSample Leads:\n";
                foreach ($sampleLeads as $lead) {
                    echo "  Lead " . $lead['lead_id'] . ": ";
                    echo $lead['first_name'] . " " . $lead['last_name'] . " - ";
                    echo $lead['phone_number'] . " - ";
                    echo "Status: " . $lead['status'] . " - ";
                    echo "Vendor Code: " . ($lead['vendor_lead_code'] ?: 'None') . " - ";
                    echo "Source: " . ($lead['source_id'] ?: 'None') . "\n";
                }
            }
            
            echo "\n" . str_repeat("-", 60) . "\n\n";
        }
    } else {
        echo "No lists found for Autodial campaign.\n\n";
    }
    
    // Check all lists in the system
    echo "=== ALL LISTS IN VICI SYSTEM ===\n\n";
    $stmt = $pdo->prepare("
        SELECT l.list_id, l.list_name, l.campaign_id, COUNT(vl.lead_id) as lead_count
        FROM vicidial_lists l
        LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id
        GROUP BY l.list_id, l.list_name, l.campaign_id
        ORDER BY l.list_id
    ");
    $stmt->execute();
    $allLists = $stmt->fetchAll();
    
    foreach ($allLists as $list) {
        echo "List " . $list['list_id'] . " (" . ($list['list_name'] ?: 'No Name') . ")";
        echo " - Campaign: " . ($list['campaign_id'] ?: 'Not Assigned');
        echo " - Leads: " . $list['lead_count'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}



// Direct connection to Vici database
$host = '37.27.138.222';
$db = 'asterisk';
$user = 'Superman';
$pass = '8ZDWGAAQRD';
$port = 3306;

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to Vici database successfully!\n\n";
    
    // Check if Autodial campaign exists
    $stmt = $pdo->prepare("SELECT campaign_id, campaign_name, active, dial_method FROM vicidial_campaigns WHERE campaign_id = 'Autodial'");
    $stmt->execute();
    $campaign = $stmt->fetch();
    
    if ($campaign) {
        echo "=== AUTODIAL CAMPAIGN INFO ===\n";
        echo "Campaign ID: " . $campaign['campaign_id'] . "\n";
        echo "Campaign Name: " . $campaign['campaign_name'] . "\n";
        echo "Active: " . $campaign['active'] . "\n";
        echo "Dial Method: " . $campaign['dial_method'] . "\n\n";
    } else {
        echo "Autodial campaign not found!\n\n";
    }
    
    // Get lists associated with Autodial campaign
    echo "=== LISTS IN AUTODIAL CAMPAIGN ===\n\n";
    
    // Check campaign_lists table
    $stmt = $pdo->prepare("
        SELECT cl.list_id, l.list_name, l.list_description, l.active, l.campaign_id
        FROM vicidial_campaign_lists cl
        LEFT JOIN vicidial_lists l ON cl.list_id = l.list_id
        WHERE cl.campaign_id = 'Autodial'
        ORDER BY cl.list_id
    ");
    $stmt->execute();
    $lists = $stmt->fetchAll();
    
    if (empty($lists)) {
        echo "No lists found in campaign_lists for Autodial.\n\n";
        
        // Check if there are lists with campaign_id = 'Autodial'
        $stmt = $pdo->prepare("
            SELECT list_id, list_name, list_description, active
            FROM vicidial_lists
            WHERE campaign_id = 'Autodial'
            ORDER BY list_id
        ");
        $stmt->execute();
        $lists = $stmt->fetchAll();
    }
    
    if (!empty($lists)) {
        foreach ($lists as $list) {
            echo "List ID: " . $list['list_id'] . "\n";
            echo "Name: " . ($list['list_name'] ?? 'N/A') . "\n";
            echo "Description: " . ($list['list_description'] ?? 'N/A') . "\n";
            echo "Active: " . ($list['active'] ?? 'N/A') . "\n";
            
            // Count leads in this list
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vicidial_list WHERE list_id = ?");
            $stmt->execute([$list['list_id']]);
            $count = $stmt->fetchColumn();
            echo "Total Leads: " . $count . "\n";
            
            // Get status breakdown
            $stmt = $pdo->prepare("
                SELECT status, COUNT(*) as count 
                FROM vicidial_list 
                WHERE list_id = ? 
                GROUP BY status 
                ORDER BY count DESC
            ");
            $stmt->execute([$list['list_id']]);
            $statuses = $stmt->fetchAll();
            
            if (!empty($statuses)) {
                echo "Lead Status Breakdown:\n";
                foreach ($statuses as $status) {
                    echo "  - " . $status['status'] . ": " . $status['count'] . "\n";
                }
            }
            
            // Sample some leads to check vendor_lead_code
            $stmt = $pdo->prepare("
                SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, source_id, status
                FROM vicidial_list 
                WHERE list_id = ? 
                LIMIT 5
            ");
            $stmt->execute([$list['list_id']]);
            $sampleLeads = $stmt->fetchAll();
            
            if (!empty($sampleLeads)) {
                echo "\nSample Leads:\n";
                foreach ($sampleLeads as $lead) {
                    echo "  Lead " . $lead['lead_id'] . ": ";
                    echo $lead['first_name'] . " " . $lead['last_name'] . " - ";
                    echo $lead['phone_number'] . " - ";
                    echo "Status: " . $lead['status'] . " - ";
                    echo "Vendor Code: " . ($lead['vendor_lead_code'] ?: 'None') . " - ";
                    echo "Source: " . ($lead['source_id'] ?: 'None') . "\n";
                }
            }
            
            echo "\n" . str_repeat("-", 60) . "\n\n";
        }
    } else {
        echo "No lists found for Autodial campaign.\n\n";
    }
    
    // Check all lists in the system
    echo "=== ALL LISTS IN VICI SYSTEM ===\n\n";
    $stmt = $pdo->prepare("
        SELECT l.list_id, l.list_name, l.campaign_id, COUNT(vl.lead_id) as lead_count
        FROM vicidial_lists l
        LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id
        GROUP BY l.list_id, l.list_name, l.campaign_id
        ORDER BY l.list_id
    ");
    $stmt->execute();
    $allLists = $stmt->fetchAll();
    
    foreach ($allLists as $list) {
        echo "List " . $list['list_id'] . " (" . ($list['list_name'] ?: 'No Name') . ")";
        echo " - Campaign: " . ($list['campaign_id'] ?: 'Not Assigned');
        echo " - Leads: " . $list['lead_count'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}






