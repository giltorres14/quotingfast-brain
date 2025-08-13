<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test 1: Check if Campaign model exists
echo "Testing Campaign functionality...\n\n";

if (!class_exists('\App\Models\Campaign')) {
    echo "❌ Campaign model class not found!\n";
    echo "Looking for file at: " . __DIR__ . "/app/Models/Campaign.php\n";
    if (file_exists(__DIR__ . "/app/Models/Campaign.php")) {
        echo "File exists but class not loading. Including manually...\n";
        require_once __DIR__ . "/app/Models/Campaign.php";
    }
} else {
    echo "✅ Campaign model found\n";
}

// Test 2: Database connection
try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    echo "✅ Database connected\n";
    
    // Test 3: Check if campaigns table exists
    $result = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'campaigns')");
    $exists = $result->fetch(PDO::FETCH_NUM)[0];
    
    if ($exists) {
        echo "✅ Campaigns table exists\n";
        
        // Test 4: Check table structure
        $result = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'campaigns' ORDER BY ordinal_position");
        echo "\nTable structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['column_name'] . " (" . $row['data_type'] . ")\n";
        }
        
        // Test 5: Count campaigns
        $result = $pdo->query("SELECT COUNT(*) as count FROM campaigns");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\n📊 Total campaigns in database: " . $count . "\n";
        
        // Test 6: Show sample campaigns
        if ($count > 0) {
            echo "\nSample campaigns:\n";
            $result = $pdo->query("SELECT id, campaign_id, name, status, is_auto_created FROM campaigns LIMIT 5");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $status = $row['is_auto_created'] ? '🟡 Auto' : '🟢 Manual';
                echo "  - ID: " . $row['id'] . ", Campaign: #" . $row['campaign_id'] . ", Name: " . $row['name'] . " " . $status . "\n";
            }
        }
        
    } else {
        echo "❌ Campaigns table does NOT exist!\n";
        echo "\nCreating campaigns table...\n";
        
        // Create the table
        $sql = "CREATE TABLE campaigns (
            id SERIAL PRIMARY KEY,
            campaign_id VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'active',
            first_seen_at TIMESTAMP,
            last_lead_received_at TIMESTAMP,
            total_leads INTEGER DEFAULT 0,
            is_auto_created BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($pdo->exec($sql) !== false) {
            echo "✅ Campaigns table created successfully!\n";
            
            // Create index
            $pdo->exec("CREATE INDEX idx_campaigns_campaign_id ON campaigns(campaign_id)");
            echo "✅ Index created on campaign_id\n";
            
            // Insert sample data from existing leads
            echo "\nChecking for existing campaign IDs in leads...\n";
            $result = $pdo->query("SELECT DISTINCT campaign_id FROM leads WHERE campaign_id IS NOT NULL LIMIT 10");
            $campaignIds = $result->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($campaignIds) > 0) {
                echo "Found " . count($campaignIds) . " unique campaign IDs in leads table\n";
                foreach ($campaignIds as $cid) {
                    $stmt = $pdo->prepare("INSERT INTO campaigns (campaign_id, name, description, status, first_seen_at, is_auto_created) 
                                           VALUES (?, ?, ?, ?, NOW(), ?) 
                                           ON CONFLICT (campaign_id) DO NOTHING");
                    $stmt->execute([$cid, "Campaign #$cid", "Auto-created from incoming lead", "auto_detected", true]);
                    echo "  - Created campaign: #$cid\n";
                }
            }
        } else {
            echo "❌ Failed to create campaigns table\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 7: Check route file
echo "\n📁 Checking routes...\n";
$routeFile = __DIR__ . '/routes/web.php';
$content = file_get_contents($routeFile);

if (strpos($content, '/admin/campaigns') !== false) {
    echo "✅ Campaign route found in web.php\n";
    
    // Find the exact route
    preg_match('/Route::get\([\'"]\/admin\/campaigns[\'"].*?\}\);/s', $content, $matches);
    if (!empty($matches)) {
        echo "✅ GET /admin/campaigns route is defined\n";
    }
    
    preg_match('/Route::post\([\'"]\/admin\/campaigns.*?\}\);/s', $content, $matches);
    if (!empty($matches)) {
        echo "✅ POST /admin/campaigns update route is defined\n";
    }
} else {
    echo "❌ Campaign route NOT found in web.php!\n";
}

// Test 8: Check view file
$viewFile = __DIR__ . '/resources/views/admin/campaigns.blade.php';
if (file_exists($viewFile)) {
    echo "✅ Campaign view file exists\n";
    $size = filesize($viewFile);
    echo "   File size: " . number_format($size) . " bytes\n";
} else {
    echo "❌ Campaign view file NOT found!\n";
}

echo "\n✨ Diagnostics complete!\n";


