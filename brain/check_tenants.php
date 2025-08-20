<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to database
    $pdo = new PDO(
        "pgsql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT', '5432') . ";dbname=" . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if tenants table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'tenants')");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ The 'tenants' table does not exist!\n";
        echo "This is why webhooks are failing.\n\n";
        
        // Create tenants table
        echo "Creating tenants table...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tenants (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default tenant with ID 5
        echo "Inserting default tenant with ID 5...\n";
        $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant') ON CONFLICT (id) DO NOTHING");
        
        echo "✅ Tenants table created and default tenant added!\n";
    } else {
        // Check existing tenants
        echo "Checking existing tenants:\n";
        $stmt = $pdo->query("SELECT * FROM tenants ORDER BY id");
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tenants)) {
            echo "No tenants found. Creating default tenant with ID 5...\n";
            $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant')");
            echo "✅ Default tenant created!\n";
        } else {
            echo "Found " . count($tenants) . " tenant(s):\n";
            foreach ($tenants as $tenant) {
                echo "  - ID: {$tenant['id']}, Name: {$tenant['name']}\n";
            }
            
            // Check if tenant ID 5 exists
            $hasId5 = false;
            foreach ($tenants as $tenant) {
                if ($tenant['id'] == 5) {
                    $hasId5 = true;
                    break;
                }
            }
            
            if (!$hasId5) {
                echo "\n⚠️ Tenant ID 5 not found. Creating it...\n";
                $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant')");
                echo "✅ Tenant ID 5 created!\n";
            } else {
                echo "\n✅ Tenant ID 5 exists - webhooks should work!\n";
            }
        }
    }
    
    // Test a simple lead insert
    echo "\n\nTesting lead insert with tenant_id=5...\n";
    try {
        $testId = 'TEST_' . time();
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', 5,
                NOW(), NOW(), NOW(), NOW()
            )
        ");
        $stmt->execute(['external_id' => $testId]);
        echo "✅ Test lead created successfully!\n";
        
        // Clean up test lead
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
        echo "✅ Test lead cleaned up.\n";
    } catch (Exception $e) {
        echo "❌ Failed to create test lead: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}





require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to database
    $pdo = new PDO(
        "pgsql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT', '5432') . ";dbname=" . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if tenants table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'tenants')");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ The 'tenants' table does not exist!\n";
        echo "This is why webhooks are failing.\n\n";
        
        // Create tenants table
        echo "Creating tenants table...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tenants (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default tenant with ID 5
        echo "Inserting default tenant with ID 5...\n";
        $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant') ON CONFLICT (id) DO NOTHING");
        
        echo "✅ Tenants table created and default tenant added!\n";
    } else {
        // Check existing tenants
        echo "Checking existing tenants:\n";
        $stmt = $pdo->query("SELECT * FROM tenants ORDER BY id");
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tenants)) {
            echo "No tenants found. Creating default tenant with ID 5...\n";
            $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant')");
            echo "✅ Default tenant created!\n";
        } else {
            echo "Found " . count($tenants) . " tenant(s):\n";
            foreach ($tenants as $tenant) {
                echo "  - ID: {$tenant['id']}, Name: {$tenant['name']}\n";
            }
            
            // Check if tenant ID 5 exists
            $hasId5 = false;
            foreach ($tenants as $tenant) {
                if ($tenant['id'] == 5) {
                    $hasId5 = true;
                    break;
                }
            }
            
            if (!$hasId5) {
                echo "\n⚠️ Tenant ID 5 not found. Creating it...\n";
                $pdo->exec("INSERT INTO tenants (id, name) VALUES (5, 'Default Tenant')");
                echo "✅ Tenant ID 5 created!\n";
            } else {
                echo "\n✅ Tenant ID 5 exists - webhooks should work!\n";
            }
        }
    }
    
    // Test a simple lead insert
    echo "\n\nTesting lead insert with tenant_id=5...\n";
    try {
        $testId = 'TEST_' . time();
        $stmt = $pdo->prepare("
            INSERT INTO leads (
                external_lead_id, name, phone, source, type, tenant_id, 
                received_at, joined_at, created_at, updated_at
            ) VALUES (
                :external_id, 'Test Lead', '5555551234', 'test', 'auto', 5,
                NOW(), NOW(), NOW(), NOW()
            )
        ");
        $stmt->execute(['external_id' => $testId]);
        echo "✅ Test lead created successfully!\n";
        
        // Clean up test lead
        $pdo->exec("DELETE FROM leads WHERE external_lead_id = '$testId'");
        echo "✅ Test lead cleaned up.\n";
    } catch (Exception $e) {
        echo "❌ Failed to create test lead: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}










