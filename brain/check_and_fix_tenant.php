<?php

// Direct database connection
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$dbname = 'brain_production';
$user = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n\n";
    
    // Check all existing tenants
    echo "Existing tenants:\n";
    $stmt = $pdo->query("SELECT id, name, slug, status FROM tenants ORDER BY id");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tenants as $tenant) {
        echo "  ID: {$tenant['id']}, Name: {$tenant['name']}, Slug: {$tenant['slug']}, Status: {$tenant['status']}\n";
    }
    
    // Find the QuotingFast tenant
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE slug = 'quotingfast'");
    $stmt->execute();
    $existingTenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingTenant) {
        $existingId = $existingTenant['id'];
        echo "\n✅ Found QuotingFast tenant with ID: $existingId\n";
        
        // Update all webhook endpoints to use this tenant ID instead of 5
        echo "\nUpdating webhook endpoints to use tenant_id = $existingId...\n";
        
        // We need to update the routes file
        $updateNeeded = true;
        $tenantId = $existingId;
    } else {
        echo "\n❌ No QuotingFast tenant found. Creating one...\n";
        
        // Find an available ID
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM tenants");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newId = ($result['max_id'] ?? 0) + 1;
        
        $uuid = generateUUID();
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, uuid, name, slug, domain, settings, status, created_at, updated_at) 
            VALUES (:id, :uuid::uuid, 'QuotingFast', 'quotingfast-' || :id2, 'quotingfast.com', :settings, 'active', NOW(), NOW())
        ");
        
        $settings = json_encode([
            'company' => 'QuotingFast',
            'timezone' => 'America/New_York',
            'active' => true
        ]);
        
        $stmt->execute([
            'id' => $newId,
            'id2' => $newId,
            'uuid' => $uuid,
            'settings' => $settings
        ]);
        
        echo "✅ Created QuotingFast tenant with ID: $newId\n";
        $tenantId = $newId;
        $updateNeeded = true;
    }
    
    // Output the correct tenant ID to use
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "IMPORTANT: Use tenant_id = $tenantId in all webhooks\n";
    echo str_repeat("=", 60) . "\n";
    
    // Test with the correct tenant ID
    echo "\nTesting with tenant_id = $tenantId...\n";
    $testId = 'TEST_' . time();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at, status
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', :tenant_id,
                NOW(), NOW(), NOW(), NOW(), 'new'
            )
        ");
        $stmt->execute(['external_id' => $testId, 'tenant_id' => $tenantId]);
        echo "✅ Test successful with tenant_id = $tenantId\n";
        
        // Clean up
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    if ($tenantId != 5) {
        echo "\n⚠️  WEBHOOK CODE NEEDS UPDATE:\n";
        echo "The webhooks are using tenant_id = 5, but should use tenant_id = $tenantId\n";
        echo "Updating the code now...\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
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





// Direct database connection
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$dbname = 'brain_production';
$user = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n\n";
    
    // Check all existing tenants
    echo "Existing tenants:\n";
    $stmt = $pdo->query("SELECT id, name, slug, status FROM tenants ORDER BY id");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tenants as $tenant) {
        echo "  ID: {$tenant['id']}, Name: {$tenant['name']}, Slug: {$tenant['slug']}, Status: {$tenant['status']}\n";
    }
    
    // Find the QuotingFast tenant
    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE slug = 'quotingfast'");
    $stmt->execute();
    $existingTenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingTenant) {
        $existingId = $existingTenant['id'];
        echo "\n✅ Found QuotingFast tenant with ID: $existingId\n";
        
        // Update all webhook endpoints to use this tenant ID instead of 5
        echo "\nUpdating webhook endpoints to use tenant_id = $existingId...\n";
        
        // We need to update the routes file
        $updateNeeded = true;
        $tenantId = $existingId;
    } else {
        echo "\n❌ No QuotingFast tenant found. Creating one...\n";
        
        // Find an available ID
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM tenants");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newId = ($result['max_id'] ?? 0) + 1;
        
        $uuid = generateUUID();
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, uuid, name, slug, domain, settings, status, created_at, updated_at) 
            VALUES (:id, :uuid::uuid, 'QuotingFast', 'quotingfast-' || :id2, 'quotingfast.com', :settings, 'active', NOW(), NOW())
        ");
        
        $settings = json_encode([
            'company' => 'QuotingFast',
            'timezone' => 'America/New_York',
            'active' => true
        ]);
        
        $stmt->execute([
            'id' => $newId,
            'id2' => $newId,
            'uuid' => $uuid,
            'settings' => $settings
        ]);
        
        echo "✅ Created QuotingFast tenant with ID: $newId\n";
        $tenantId = $newId;
        $updateNeeded = true;
    }
    
    // Output the correct tenant ID to use
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "IMPORTANT: Use tenant_id = $tenantId in all webhooks\n";
    echo str_repeat("=", 60) . "\n";
    
    // Test with the correct tenant ID
    echo "\nTesting with tenant_id = $tenantId...\n";
    $testId = 'TEST_' . time();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at, status
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', :tenant_id,
                NOW(), NOW(), NOW(), NOW(), 'new'
            )
        ");
        $stmt->execute(['external_id' => $testId, 'tenant_id' => $tenantId]);
        echo "✅ Test successful with tenant_id = $tenantId\n";
        
        // Clean up
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
    }
    
    if ($tenantId != 5) {
        echo "\n⚠️  WEBHOOK CODE NEEDS UPDATE:\n";
        echo "The webhooks are using tenant_id = 5, but should use tenant_id = $tenantId\n";
        echo "Updating the code now...\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
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










