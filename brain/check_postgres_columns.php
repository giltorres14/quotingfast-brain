<?php
// Direct PostgreSQL connection
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = 5432;
$dbname = 'brain_production';
$user = 'brain_user';
$pass = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING LEADS TABLE COLUMNS ===\n\n";
    
    // Get all columns from leads table
    $sql = "SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'leads' 
            ORDER BY ordinal_position";
    
    $result = $pdo->query($sql);
    $columns = [];
    
    echo "Columns in leads table:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['column_name'];
        echo "  - {$row['column_name']} ({$row['data_type']})\n";
    }
    
    // Check for specific columns
    echo "\n🔍 Checking required columns:\n";
    $required = ['vendor_name', 'buyer_name', 'source', 'payload', 'meta'];
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "  ✅ $col exists\n";
        } else {
            echo "  ❌ $col MISSING - Need to add!\n";
        }
    }
    
    // Check vendors and buyers tables
    echo "\n📊 Related tables:\n";
    $tables = ['vendors', 'buyers', 'campaigns', 'campaign_buyer', 'sources'];
    foreach ($tables as $table) {
        $check = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '$table'");
        $exists = $check->fetchColumn() > 0;
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "  ✅ $table exists (rows: $count)\n";
        } else {
            echo "  ❌ $table MISSING\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
