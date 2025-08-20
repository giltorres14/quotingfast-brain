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
    
    // Generate a UUID for the tenant
    $uuid = generateUUID();
    
    echo "Creating tenant with UUID: $uuid\n";
    
    // Check if tenant ID 5 exists
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = 5");
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tenant) {
        echo "Creating default tenant (ID: 5) for QuotingFast...\n";
        
        // Insert with the correct columns based on the table structure
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, uuid, name, slug, domain, settings, status, created_at, updated_at) 
            VALUES (5, :uuid::uuid, 'QuotingFast', 'quotingfast', 'quotingfast.com', :settings, 'active', NOW(), NOW())
            ON CONFLICT (id) DO UPDATE 
            SET name = 'QuotingFast', 
                slug = 'quotingfast',
                status = 'active',
                updated_at = NOW()
        ");
        
        $settings = json_encode([
            'company' => 'QuotingFast',
            'timezone' => 'America/New_York',
            'active' => true,
            'features' => [
                'auto_insurance' => true,
                'home_insurance' => true,
                'vici_integration' => true,
                'ringba_integration' => true
            ]
        ]);
        
        $stmt->execute([
            'uuid' => $uuid,
            'settings' => $settings
        ]);
        echo "✅ Default tenant created with ID 5!\n";
    } else {
        echo "✅ Tenant ID 5 already exists: {$tenant['name']}\n";
    }
    
    // Verify the tenant was created
    $stmt = $pdo->prepare("SELECT id, name, slug, status FROM tenants WHERE id = 5");
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tenant) {
        echo "\n✅ TENANT VERIFIED:\n";
        echo "  ID: {$tenant['id']}\n";
        echo "  Name: {$tenant['name']}\n";
        echo "  Slug: {$tenant['slug']}\n";
        echo "  Status: {$tenant['status']}\n";
    }
    
    // Test a webhook insert
    echo "\nTesting webhook functionality...\n";
    $testId = 'TEST_' . time();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at, status
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', 5,
                NOW(), NOW(), NOW(), NOW(), 'new'
            )
        ");
        $stmt->execute(['external_id' => $testId]);
        echo "✅ Test lead created successfully - webhooks are working!\n";
        
        // Clean up test lead
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
        echo "✅ Test lead cleaned up.\n";
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 SUCCESS! Your webhook endpoints are now working:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "AUTO: https://quotingfast-brain-ohio.onrender.com/webhook/auto\n";
    echo "HOME: https://quotingfast-brain-ohio.onrender.com/webhook/home\n";
    echo "MAIN: https://quotingfast-brain-ohio.onrender.com/api-webhook\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}





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
    
    // Generate a UUID for the tenant
    $uuid = generateUUID();
    
    echo "Creating tenant with UUID: $uuid\n";
    
    // Check if tenant ID 5 exists
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = 5");
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tenant) {
        echo "Creating default tenant (ID: 5) for QuotingFast...\n";
        
        // Insert with the correct columns based on the table structure
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, uuid, name, slug, domain, settings, status, created_at, updated_at) 
            VALUES (5, :uuid::uuid, 'QuotingFast', 'quotingfast', 'quotingfast.com', :settings, 'active', NOW(), NOW())
            ON CONFLICT (id) DO UPDATE 
            SET name = 'QuotingFast', 
                slug = 'quotingfast',
                status = 'active',
                updated_at = NOW()
        ");
        
        $settings = json_encode([
            'company' => 'QuotingFast',
            'timezone' => 'America/New_York',
            'active' => true,
            'features' => [
                'auto_insurance' => true,
                'home_insurance' => true,
                'vici_integration' => true,
                'ringba_integration' => true
            ]
        ]);
        
        $stmt->execute([
            'uuid' => $uuid,
            'settings' => $settings
        ]);
        echo "✅ Default tenant created with ID 5!\n";
    } else {
        echo "✅ Tenant ID 5 already exists: {$tenant['name']}\n";
    }
    
    // Verify the tenant was created
    $stmt = $pdo->prepare("SELECT id, name, slug, status FROM tenants WHERE id = 5");
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tenant) {
        echo "\n✅ TENANT VERIFIED:\n";
        echo "  ID: {$tenant['id']}\n";
        echo "  Name: {$tenant['name']}\n";
        echo "  Slug: {$tenant['slug']}\n";
        echo "  Status: {$tenant['status']}\n";
    }
    
    // Test a webhook insert
    echo "\nTesting webhook functionality...\n";
    $testId = 'TEST_' . time();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at, status
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', 5,
                NOW(), NOW(), NOW(), NOW(), 'new'
            )
        ");
        $stmt->execute(['external_id' => $testId]);
        echo "✅ Test lead created successfully - webhooks are working!\n";
        
        // Clean up test lead
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
        echo "✅ Test lead cleaned up.\n";
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 SUCCESS! Your webhook endpoints are now working:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "AUTO: https://quotingfast-brain-ohio.onrender.com/webhook/auto\n";
    echo "HOME: https://quotingfast-brain-ohio.onrender.com/webhook/home\n";
    echo "MAIN: https://quotingfast-brain-ohio.onrender.com/api-webhook\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}










