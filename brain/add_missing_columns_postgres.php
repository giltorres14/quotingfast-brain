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
    
    echo "=== ADDING MISSING COLUMNS TO POSTGRESQL ===\n\n";
    
    // Add vendor_name column
    $sql = "ALTER TABLE leads ADD COLUMN IF NOT EXISTS vendor_name VARCHAR(255)";
    $pdo->exec($sql);
    echo "✅ Added vendor_name column\n";
    
    // Add buyer_name column
    $sql = "ALTER TABLE leads ADD COLUMN IF NOT EXISTS buyer_name VARCHAR(255)";
    $pdo->exec($sql);
    echo "✅ Added buyer_name column\n";
    
    // Create campaign_buyer pivot table
    $sql = "CREATE TABLE IF NOT EXISTS campaign_buyer (
        id SERIAL PRIMARY KEY,
        campaign_id BIGINT NOT NULL,
        buyer_id BIGINT NOT NULL,
        buyer_campaign_id VARCHAR(255),
        is_primary BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(campaign_id, buyer_id)
    )";
    $pdo->exec($sql);
    echo "✅ Created campaign_buyer pivot table\n";
    
    // Verify columns
    echo "\n📋 Verifying columns:\n";
    $sql = "SELECT column_name FROM information_schema.columns 
            WHERE table_name = 'leads' AND column_name IN ('vendor_name', 'buyer_name')";
    $result = $pdo->query($sql);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  ✅ {$row['column_name']} exists\n";
    }
    
    echo "\n✅ PostgreSQL is ready for Suraj import!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
