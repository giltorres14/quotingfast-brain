<?php

// Vici database configuration
$viciHost = '148.72.213.125';
$viciPort = '3306';
$viciDb = 'asterisk';
$viciUser = 'cron';
$viciPass = '1234';

try {
    // First trigger whitelist
    $whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';
    echo "Triggering Vici whitelist...\n";
    $ch = curl_init($whitelistUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    echo "Whitelist triggered\n\n";
    
    sleep(2); // Wait for whitelist to take effect
    
    // Connect to Vici database
    echo "Connecting to Vici database...\n";
    $dsn = "mysql:host=$viciHost;port=$viciPort;dbname=$viciDb;charset=utf8mb4";
    $pdo = new PDO($dsn, $viciUser, $viciPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "Connected successfully!\n\n";
    
    // Check some sample phone numbers from the CSV
    $samplePhones = [
        '2482205565',
        '3133041372', 
        '2483553844',
        '5863868721',
        '3136459900'
    ];
    
    echo "Checking vendor_lead_code for sample leads:\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($samplePhones as $phone) {
        $stmt = $pdo->prepare("
            SELECT lead_id, phone_number, vendor_lead_code, list_id, status, campaign_id
            FROM vicidial_list 
            WHERE phone_number = ?
            AND campaign_id IN ('Auto2', 'Autodial')
            LIMIT 1
        ");
        $stmt->execute([$phone]);
        $lead = $stmt->fetch();
        
        if ($lead) {
            echo "Phone: $phone\n";
            echo "  Lead ID: {$lead['lead_id']}\n";
            echo "  Campaign: {$lead['campaign_id']}\n";
            echo "  Status: {$lead['status']}\n";
            echo "  Vendor Code: " . ($lead['vendor_lead_code'] ?: 'EMPTY') . "\n";
        } else {
            echo "Phone: $phone - NOT FOUND in Auto2/Autodial campaigns\n";
        }
        echo "\n";
    }
    
    // Get statistics
    echo str_repeat('-', 60) . "\n";
    echo "Statistics for Auto2 and Autodial campaigns:\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            campaign_id,
            COUNT(*) as total_leads,
            SUM(CASE WHEN vendor_lead_code IS NOT NULL AND vendor_lead_code != '' THEN 1 ELSE 0 END) as with_vendor_code,
            SUM(CASE WHEN vendor_lead_code IS NULL OR vendor_lead_code = '' THEN 1 ELSE 0 END) as without_vendor_code
        FROM vicidial_list
        WHERE campaign_id IN ('Auto2', 'Autodial')
        GROUP BY campaign_id
    ");
    
    $stats = $stmt->fetchAll();
    foreach ($stats as $stat) {
        echo "Campaign: {$stat['campaign_id']}\n";
        echo "  Total Leads: {$stat['total_leads']}\n";
        echo "  With Vendor Code: {$stat['with_vendor_code']}\n";
        echo "  Without Vendor Code: {$stat['without_vendor_code']}\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}


