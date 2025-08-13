<?php

// Test importing a single specific file
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
    
    echo "Connected to database\n";
    
    // Pick a file we haven't imported yet
    $file = '/Users/giltorres/Downloads/Suraj Leads/11jun_auto_lead.csv';
    
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        exit(1);
    }
    
    echo "Processing file: " . basename($file) . "\n";
    echo "File size: " . number_format(filesize($file)) . " bytes\n";
    
    // Open and read the file
    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "Could not open file\n";
        exit(1);
    }
    
    // Read headers
    $headers = fgetcsv($handle);
    echo "Headers: " . count($headers) . " columns\n";
    
    // Count rows
    $rowCount = 0;
    $phoneNumbers = [];
    while (($row = fgetcsv($handle)) !== false) {
        $rowCount++;
        // Get phone from column index (usually column 5 or 6)
        foreach ($row as $index => $value) {
            if (stripos($headers[$index], 'phone') !== false) {
                $phone = preg_replace('/[^0-9]/', '', $value);
                if (strlen($phone) == 10) {
                    $phoneNumbers[] = $phone;
                    break;
                }
            }
        }
    }
    
    fclose($handle);
    
    echo "Total rows: $rowCount\n";
    echo "Valid phone numbers found: " . count($phoneNumbers) . "\n";
    
    if (count($phoneNumbers) > 0) {
        // Check how many of these phones already exist
        $phonesStr = "'" . implode("','", array_slice($phoneNumbers, 0, 10)) . "'";
        $stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE phone IN ($phonesStr)");
        $existing = $stmt->fetchColumn();
        
        echo "Sample check - First 10 phones:\n";
        echo "  Already in database: $existing\n";
        echo "  New phones: " . (min(10, count($phoneNumbers)) - $existing) . "\n";
        
        // Show first few phone numbers
        echo "\nFirst 5 phone numbers in file:\n";
        foreach (array_slice($phoneNumbers, 0, 5) as $phone) {
            echo "  $phone\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
