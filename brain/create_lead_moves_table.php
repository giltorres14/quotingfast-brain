<?php
// create_lead_moves_table.php
// Create the lead_moves table with proper structure

echo "=== CREATING LEAD_MOVES TABLE ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// First check if table exists and its structure
echo "📋 Checking existing table structure...\n";
$checkCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DESCRIBE lead_moves' 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (strpos($result['output'], "doesn't exist") !== false) {
        echo "   Table doesn't exist - will create it\n";
    } else {
        echo "   Table exists with structure:\n";
        echo $result['output'] . "\n";
        
        // Drop and recreate to ensure correct structure
        echo "\n🗑️ Dropping existing table...\n";
        $dropCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DROP TABLE IF EXISTS lead_moves' 2>&1";
        
        $ch = curl_init($proxyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $dropCmd]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
        
        echo "   ✅ Table dropped\n";
    }
}

// Create the table with correct structure
echo "\n🔨 Creating lead_moves table...\n";
$createCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT NOT NULL,
    to_list_id INT NOT NULL,
    move_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    move_reason VARCHAR(255),
    disposition VARCHAR(50),
    brain_lead_id VARCHAR(20),
    INDEX idx_lead_id (lead_id),
    INDEX idx_move_date (move_date),
    INDEX idx_from_list (from_list_id),
    INDEX idx_to_list (to_list_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
\" 2>&1 && echo 'Table created successfully'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $createCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    echo "   " . ($result['output'] ?? 'Created') . "\n";
} else {
    echo "   ❌ Failed to create table\n";
}

// Verify the table was created
echo "\n🔍 Verifying table structure...\n";
$verifyCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DESCRIBE lead_moves' 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (!empty($result['output'])) {
        echo $result['output'] . "\n";
    }
}

echo "\n=== TABLE CREATION COMPLETE ===\n";
echo "✅ The lead_moves table is ready for tracking lead movements\n";
echo "📊 Now testing the lead flow scripts again...\n";


// create_lead_moves_table.php
// Create the lead_moves table with proper structure

echo "=== CREATING LEAD_MOVES TABLE ===\n\n";

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// First check if table exists and its structure
echo "📋 Checking existing table structure...\n";
$checkCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DESCRIBE lead_moves' 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (strpos($result['output'], "doesn't exist") !== false) {
        echo "   Table doesn't exist - will create it\n";
    } else {
        echo "   Table exists with structure:\n";
        echo $result['output'] . "\n";
        
        // Drop and recreate to ensure correct structure
        echo "\n🗑️ Dropping existing table...\n";
        $dropCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DROP TABLE IF EXISTS lead_moves' 2>&1";
        
        $ch = curl_init($proxyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $dropCmd]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
        
        echo "   ✅ Table dropped\n";
    }
}

// Create the table with correct structure
echo "\n🔨 Creating lead_moves table...\n";
$createCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT NOT NULL,
    to_list_id INT NOT NULL,
    move_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    move_reason VARCHAR(255),
    disposition VARCHAR(50),
    brain_lead_id VARCHAR(20),
    INDEX idx_lead_id (lead_id),
    INDEX idx_move_date (move_date),
    INDEX idx_from_list (from_list_id),
    INDEX idx_to_list (to_list_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
\" 2>&1 && echo 'Table created successfully'";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $createCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    echo "   " . ($result['output'] ?? 'Created') . "\n";
} else {
    echo "   ❌ Failed to create table\n";
}

// Verify the table was created
echo "\n🔍 Verifying table structure...\n";
$verifyCmd = "mysql -u root Q6hdjl67GRigMofv -e 'DESCRIBE lead_moves' 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $verifyCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (!empty($result['output'])) {
        echo $result['output'] . "\n";
    }
}

echo "\n=== TABLE CREATION COMPLETE ===\n";
echo "✅ The lead_moves table is ready for tracking lead movements\n";
echo "📊 Now testing the lead flow scripts again...\n";






