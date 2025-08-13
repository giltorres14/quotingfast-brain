<?php
// Direct PostgreSQL connection - NO LARAVEL
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = 5432;
$dbname = 'brain_production';
$user = 'brain_user';
$pass = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to PostgreSQL\n\n";
    
    // Create sources table
    $sql = "CREATE TABLE IF NOT EXISTS sources (
        id SERIAL PRIMARY KEY,
        code VARCHAR(255) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(50) CHECK (type IN ('webhook', 'api', 'bulk', 'portal', 'manual')),
        label VARCHAR(255),
        color VARCHAR(50) DEFAULT '#666',
        endpoint_url VARCHAR(255),
        api_key TEXT,
        notes TEXT,
        active BOOLEAN DEFAULT true,
        total_leads INTEGER DEFAULT 0,
        last_lead_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Created sources table\n";
    
    // Insert default sources
    $sources = [
        ['LQF_WEBHOOK', 'LeadsQuotingFast', 'webhook', 'LQF', '#10b981', '/api-webhook'],
        ['LQF_BULK', 'LQF Bulk Import', 'bulk', 'LQF Bulk', '#ec4899', null],
        ['SURAJ_BULK', 'Suraj Bulk Import', 'bulk', 'Suraj Bulk', '#8b5cf6', null],
        ['LQF_PORTAL', 'LQF Portal Upload', 'portal', 'LQF', '#06b6d4', null],
        ['SURAJ_PORTAL', 'Suraj Portal Upload', 'portal', 'Suraj', '#f59e0b', null],
        ['MANUAL', 'Manual Entry', 'manual', 'Manual', '#6b7280', null],
        ['AUTO_WEBHOOK', 'Auto Webhook', 'webhook', 'Auto', '#3b82f6', '/webhook/auto'],
        ['HOME_WEBHOOK', 'Home Webhook', 'webhook', 'Home', '#22c55e', '/webhook/home']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO sources (code, name, type, label, color, endpoint_url) 
                          VALUES (?, ?, ?, ?, ?, ?) 
                          ON CONFLICT (code) DO NOTHING");
    
    foreach ($sources as $source) {
        $stmt->execute($source);
    }
    echo "✅ Inserted default sources\n\n";
    
    // Show sources
    $result = $pdo->query("SELECT code, name, type, label FROM sources");
    echo "📋 Sources in PostgreSQL:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']}): {$row['label']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
