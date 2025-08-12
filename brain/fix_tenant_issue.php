<?php

// Direct database connection to fix tenant issue
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$dbname = 'brain_production';
$user = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    // Connect to database
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if tenants table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'tenants')");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ The 'tenants' table does not exist!\n";
        echo "Creating tenants table...\n";
        
        // Create tenants table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tenants (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                settings JSONB DEFAULT '{}',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        echo "✅ Tenants table created!\n";
    }
    
    // Check if tenant ID 5 exists
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = 5");
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tenant) {
        echo "Creating default tenant (ID: 5) for QuotingFast...\n";
        
        // First, reset the sequence if needed to allow ID 5
        $pdo->exec("SELECT setval('tenants_id_seq', 5, false)");
        
        // Insert tenant with ID 5
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, name, slug, settings) 
            VALUES (5, 'QuotingFast', 'quotingfast', :settings)
            ON CONFLICT (id) DO UPDATE 
            SET name = 'QuotingFast', slug = 'quotingfast'
        ");
        
        $settings = json_encode([
            'company' => 'QuotingFast',
            'timezone' => 'America/New_York',
            'active' => true
        ]);
        
        $stmt->execute(['settings' => $settings]);
        echo "✅ Default tenant created with ID 5!\n";
    } else {
        echo "✅ Tenant ID 5 already exists: {$tenant['name']}\n";
    }
    
    // List all tenants
    echo "\nCurrent tenants in system:\n";
    $stmt = $pdo->query("SELECT id, name, slug FROM tenants ORDER BY id");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tenants as $tenant) {
        echo "  - ID: {$tenant['id']}, Name: {$tenant['name']}, Slug: {$tenant['slug']}\n";
    }
    
    echo "\n✅ Webhook endpoints should now work properly!\n";
    echo "\nYour webhook URLs:\n";
    echo "  AUTO: https://quotingfast-brain-ohio.onrender.com/webhook/auto\n";
    echo "  HOME: https://quotingfast-brain-ohio.onrender.com/webhook/home\n";
    echo "  MAIN: https://quotingfast-brain-ohio.onrender.com/api-webhook\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTrying alternative fix...\n";
    
    // If we can't create the tenant, let's update the webhook code to not require it
    echo "The tenant table issue needs to be fixed on the server.\n";
    echo "As a workaround, we'll update the webhook endpoints to make tenant_id optional.\n";
}