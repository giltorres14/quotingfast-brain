<?php
// Direct database connection to check campaigns table
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$dbname = 'brain_production';
$user = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to production database\n\n";
    
    // Check if campaigns table exists
    $result = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'campaigns')");
    $exists = $result->fetch(PDO::FETCH_NUM)[0];
    
    if ($exists) {
        echo "✅ Campaigns table EXISTS\n\n";
        
        // Get table structure
        $result = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'campaigns' ORDER BY ordinal_position");
        echo "Table structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['column_name'] . " (" . $row['data_type'] . ") " . ($row['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
        }
        
        // Count records
        $result = $pdo->query("SELECT COUNT(*) as count FROM campaigns");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\n📊 Total campaigns: " . $count . "\n";
        
        // Show sample data
        if ($count > 0) {
            echo "\nSample campaigns:\n";
            $result = $pdo->query("SELECT * FROM campaigns LIMIT 3");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "  Campaign #" . $row['campaign_id'] . ": " . $row['name'] . " (Status: " . $row['status'] . ")\n";
            }
        }
        
        // Check for any missing columns
        $requiredColumns = ['campaign_id', 'name', 'description', 'status', 'first_seen_at', 'last_lead_received_at', 'total_leads', 'is_auto_created'];
        $result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'campaigns'");
        $existingColumns = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        if (!empty($missingColumns)) {
            echo "\n⚠️  Missing columns: " . implode(', ', $missingColumns) . "\n";
        }
        
    } else {
        echo "❌ Campaigns table does NOT exist!\n";
        echo "\nWould you like me to create it? The table needs to be created for the Campaign Management page to work.\n";
        
        // Show the SQL that would be run
        echo "\nSQL to create table:\n";
        echo "CREATE TABLE campaigns (\n";
        echo "    id SERIAL PRIMARY KEY,\n";
        echo "    campaign_id VARCHAR(255) UNIQUE NOT NULL,\n";
        echo "    name VARCHAR(255) NOT NULL,\n";
        echo "    description TEXT,\n";
        echo "    status VARCHAR(50) DEFAULT 'active',\n";
        echo "    first_seen_at TIMESTAMP,\n";
        echo "    last_lead_received_at TIMESTAMP,\n";
        echo "    total_leads INTEGER DEFAULT 0,\n";
        echo "    is_auto_created BOOLEAN DEFAULT false,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        echo ");\n";
    }
    
    // Check if there are campaign_ids in leads table
    echo "\n📋 Checking leads table for campaign data...\n";
    $result = $pdo->query("SELECT COUNT(DISTINCT campaign_id) as unique_campaigns FROM leads WHERE campaign_id IS NOT NULL AND campaign_id != ''");
    $uniqueCampaigns = $result->fetch(PDO::FETCH_ASSOC)['unique_campaigns'];
    echo "Found $uniqueCampaigns unique campaign IDs in leads table\n";
    
    if ($uniqueCampaigns > 0) {
        echo "\nSample campaign IDs from leads:\n";
        $result = $pdo->query("SELECT DISTINCT campaign_id, COUNT(*) as lead_count FROM leads WHERE campaign_id IS NOT NULL AND campaign_id != '' GROUP BY campaign_id ORDER BY lead_count DESC LIMIT 5");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - Campaign ID: " . $row['campaign_id'] . " (Leads: " . $row['lead_count'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}




// Direct database connection to check campaigns table
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$dbname = 'brain_production';
$user = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to production database\n\n";
    
    // Check if campaigns table exists
    $result = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'campaigns')");
    $exists = $result->fetch(PDO::FETCH_NUM)[0];
    
    if ($exists) {
        echo "✅ Campaigns table EXISTS\n\n";
        
        // Get table structure
        $result = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'campaigns' ORDER BY ordinal_position");
        echo "Table structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['column_name'] . " (" . $row['data_type'] . ") " . ($row['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
        }
        
        // Count records
        $result = $pdo->query("SELECT COUNT(*) as count FROM campaigns");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\n📊 Total campaigns: " . $count . "\n";
        
        // Show sample data
        if ($count > 0) {
            echo "\nSample campaigns:\n";
            $result = $pdo->query("SELECT * FROM campaigns LIMIT 3");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "  Campaign #" . $row['campaign_id'] . ": " . $row['name'] . " (Status: " . $row['status'] . ")\n";
            }
        }
        
        // Check for any missing columns
        $requiredColumns = ['campaign_id', 'name', 'description', 'status', 'first_seen_at', 'last_lead_received_at', 'total_leads', 'is_auto_created'];
        $result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'campaigns'");
        $existingColumns = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        if (!empty($missingColumns)) {
            echo "\n⚠️  Missing columns: " . implode(', ', $missingColumns) . "\n";
        }
        
    } else {
        echo "❌ Campaigns table does NOT exist!\n";
        echo "\nWould you like me to create it? The table needs to be created for the Campaign Management page to work.\n";
        
        // Show the SQL that would be run
        echo "\nSQL to create table:\n";
        echo "CREATE TABLE campaigns (\n";
        echo "    id SERIAL PRIMARY KEY,\n";
        echo "    campaign_id VARCHAR(255) UNIQUE NOT NULL,\n";
        echo "    name VARCHAR(255) NOT NULL,\n";
        echo "    description TEXT,\n";
        echo "    status VARCHAR(50) DEFAULT 'active',\n";
        echo "    first_seen_at TIMESTAMP,\n";
        echo "    last_lead_received_at TIMESTAMP,\n";
        echo "    total_leads INTEGER DEFAULT 0,\n";
        echo "    is_auto_created BOOLEAN DEFAULT false,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        echo ");\n";
    }
    
    // Check if there are campaign_ids in leads table
    echo "\n📋 Checking leads table for campaign data...\n";
    $result = $pdo->query("SELECT COUNT(DISTINCT campaign_id) as unique_campaigns FROM leads WHERE campaign_id IS NOT NULL AND campaign_id != ''");
    $uniqueCampaigns = $result->fetch(PDO::FETCH_ASSOC)['unique_campaigns'];
    echo "Found $uniqueCampaigns unique campaign IDs in leads table\n";
    
    if ($uniqueCampaigns > 0) {
        echo "\nSample campaign IDs from leads:\n";
        $result = $pdo->query("SELECT DISTINCT campaign_id, COUNT(*) as lead_count FROM leads WHERE campaign_id IS NOT NULL AND campaign_id != '' GROUP BY campaign_id ORDER BY lead_count DESC LIMIT 5");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - Campaign ID: " . $row['campaign_id'] . " (Leads: " . $row['lead_count'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}








