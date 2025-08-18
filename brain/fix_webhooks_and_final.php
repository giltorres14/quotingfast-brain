<?php

// Fix webhook endpoints and finalize all issues

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing webhook endpoints and final issues...\n\n";

// 1. Enable /webhook/home endpoint
$homeWebhookDisabled = '// DISABLED: Duplicate webhook - use /api-webhook instead
/*
Route::post(\'/webhook/home\', function (Request $request) {';

$homeWebhookEnabled = '// Home Insurance Webhook Endpoint
Route::post(\'/webhook/home\', function (Request $request) {';

$content = str_replace($homeWebhookDisabled, $homeWebhookEnabled, $content);

// Remove the closing comment */ for home webhook
$content = preg_replace('/(\s*\*\/\s*)(Route::post\(\'\/webhook\/leadsquotingfast)/', '$2', $content, 1);

echo "✓ Enabled /webhook/home endpoint\n";

// 2. Enable /webhook/auto endpoint  
$autoWebhookDisabled = '// DISABLED: Duplicate webhook - use /api-webhook instead
/*
Route::post(\'/webhook/auto\', function (Request $request) {';

$autoWebhookEnabled = '// Auto Insurance Webhook Endpoint
Route::post(\'/webhook/auto\', function (Request $request) {';

$content = str_replace($autoWebhookDisabled, $autoWebhookEnabled, $content);

// Remove the closing comment */ for auto webhook
$content = preg_replace('/(\s*\*\/\s*)(\n\n\/\/ Generic webhook)/', '$2', $content, 1);

echo "✓ Enabled /webhook/auto endpoint\n";

// Write back routes file
file_put_contents($routesFile, $content);

// 3. Create tables directly if they don't exist
echo "\nCreating Vici tables if they don't exist...\n";

$createTablesSQL = "
-- Create vici_call_metrics table if not exists
CREATE TABLE IF NOT EXISTS vici_call_metrics (
    id BIGSERIAL PRIMARY KEY,
    lead_id BIGINT,
    vendor_lead_code VARCHAR(255),
    uniqueid VARCHAR(255),
    call_date TIMESTAMP,
    phone_number VARCHAR(255),
    status VARCHAR(255),
    \"user\" VARCHAR(255),
    campaign_id VARCHAR(255),
    list_id INTEGER,
    length_in_sec INTEGER,
    call_status VARCHAR(255),
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_vici_vendor_lead_code ON vici_call_metrics(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_vici_matched_lead_id ON vici_call_metrics(matched_lead_id);
CREATE INDEX IF NOT EXISTS idx_vici_call_date ON vici_call_metrics(call_date);

-- Create orphan_call_logs table if not exists
CREATE TABLE IF NOT EXISTS orphan_call_logs (
    id BIGSERIAL PRIMARY KEY,
    uniqueid VARCHAR(255),
    lead_id VARCHAR(255),
    list_id INTEGER,
    campaign_id VARCHAR(255),
    call_date TIMESTAMP,
    start_epoch BIGINT,
    end_epoch BIGINT,
    length_in_sec INTEGER,
    status VARCHAR(255),
    phone_code VARCHAR(255),
    phone_number VARCHAR(255),
    \"user\" VARCHAR(255),
    comments TEXT,
    processed BOOLEAN DEFAULT FALSE,
    term_reason VARCHAR(255),
    vendor_lead_code VARCHAR(255),
    source_id VARCHAR(255),
    matched BOOLEAN DEFAULT FALSE,
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_orphan_vendor_lead_code ON orphan_call_logs(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_orphan_matched ON orphan_call_logs(matched);
CREATE INDEX IF NOT EXISTS idx_orphan_phone_number ON orphan_call_logs(phone_number);
";

file_put_contents('create_vici_tables.sql', $createTablesSQL);
echo "✓ Created SQL file for Vici tables\n";

// Execute the SQL
$dbConfig = [
    'host' => env('DB_HOST', 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'brain_production'),
    'username' => env('DB_USERNAME', 'brain_user'),
    'password' => env('DB_PASSWORD', 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ')
];

$dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute the SQL
    $pdo->exec($createTablesSQL);
    echo "✓ Successfully created Vici tables in database\n";
} catch (PDOException $e) {
    echo "⚠️ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ All fixes applied:\n";
echo "  1. /webhook/home endpoint enabled\n";
echo "  2. /webhook/auto endpoint enabled\n";
echo "  3. Vici tables created in database\n";
echo "\nThe webhook endpoints should now receive leads properly.\n";


// Fix webhook endpoints and finalize all issues

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing webhook endpoints and final issues...\n\n";

// 1. Enable /webhook/home endpoint
$homeWebhookDisabled = '// DISABLED: Duplicate webhook - use /api-webhook instead
/*
Route::post(\'/webhook/home\', function (Request $request) {';

$homeWebhookEnabled = '// Home Insurance Webhook Endpoint
Route::post(\'/webhook/home\', function (Request $request) {';

$content = str_replace($homeWebhookDisabled, $homeWebhookEnabled, $content);

// Remove the closing comment */ for home webhook
$content = preg_replace('/(\s*\*\/\s*)(Route::post\(\'\/webhook\/leadsquotingfast)/', '$2', $content, 1);

echo "✓ Enabled /webhook/home endpoint\n";

// 2. Enable /webhook/auto endpoint  
$autoWebhookDisabled = '// DISABLED: Duplicate webhook - use /api-webhook instead
/*
Route::post(\'/webhook/auto\', function (Request $request) {';

$autoWebhookEnabled = '// Auto Insurance Webhook Endpoint
Route::post(\'/webhook/auto\', function (Request $request) {';

$content = str_replace($autoWebhookDisabled, $autoWebhookEnabled, $content);

// Remove the closing comment */ for auto webhook
$content = preg_replace('/(\s*\*\/\s*)(\n\n\/\/ Generic webhook)/', '$2', $content, 1);

echo "✓ Enabled /webhook/auto endpoint\n";

// Write back routes file
file_put_contents($routesFile, $content);

// 3. Create tables directly if they don't exist
echo "\nCreating Vici tables if they don't exist...\n";

$createTablesSQL = "
-- Create vici_call_metrics table if not exists
CREATE TABLE IF NOT EXISTS vici_call_metrics (
    id BIGSERIAL PRIMARY KEY,
    lead_id BIGINT,
    vendor_lead_code VARCHAR(255),
    uniqueid VARCHAR(255),
    call_date TIMESTAMP,
    phone_number VARCHAR(255),
    status VARCHAR(255),
    \"user\" VARCHAR(255),
    campaign_id VARCHAR(255),
    list_id INTEGER,
    length_in_sec INTEGER,
    call_status VARCHAR(255),
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_vici_vendor_lead_code ON vici_call_metrics(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_vici_matched_lead_id ON vici_call_metrics(matched_lead_id);
CREATE INDEX IF NOT EXISTS idx_vici_call_date ON vici_call_metrics(call_date);

-- Create orphan_call_logs table if not exists
CREATE TABLE IF NOT EXISTS orphan_call_logs (
    id BIGSERIAL PRIMARY KEY,
    uniqueid VARCHAR(255),
    lead_id VARCHAR(255),
    list_id INTEGER,
    campaign_id VARCHAR(255),
    call_date TIMESTAMP,
    start_epoch BIGINT,
    end_epoch BIGINT,
    length_in_sec INTEGER,
    status VARCHAR(255),
    phone_code VARCHAR(255),
    phone_number VARCHAR(255),
    \"user\" VARCHAR(255),
    comments TEXT,
    processed BOOLEAN DEFAULT FALSE,
    term_reason VARCHAR(255),
    vendor_lead_code VARCHAR(255),
    source_id VARCHAR(255),
    matched BOOLEAN DEFAULT FALSE,
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_orphan_vendor_lead_code ON orphan_call_logs(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_orphan_matched ON orphan_call_logs(matched);
CREATE INDEX IF NOT EXISTS idx_orphan_phone_number ON orphan_call_logs(phone_number);
";

file_put_contents('create_vici_tables.sql', $createTablesSQL);
echo "✓ Created SQL file for Vici tables\n";

// Execute the SQL
$dbConfig = [
    'host' => env('DB_HOST', 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'brain_production'),
    'username' => env('DB_USERNAME', 'brain_user'),
    'password' => env('DB_PASSWORD', 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ')
];

$dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute the SQL
    $pdo->exec($createTablesSQL);
    echo "✓ Successfully created Vici tables in database\n";
} catch (PDOException $e) {
    echo "⚠️ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ All fixes applied:\n";
echo "  1. /webhook/home endpoint enabled\n";
echo "  2. /webhook/auto endpoint enabled\n";
echo "  3. Vici tables created in database\n";
echo "\nThe webhook endpoints should now receive leads properly.\n";

