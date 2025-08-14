<?php

echo "Testing Vici SSH Connection on Port 11845\n";
echo "==========================================\n\n";

// Test basic SSH connectivity
echo "1. Testing SSH connectivity to 37.27.138.222:11845...\n";
$testCommand = 'ssh -p 11845 -o ConnectTimeout=5 -o StrictHostKeyChecking=no root@37.27.138.222 "echo CONNECTION_OK" 2>&1';
$result = shell_exec($testCommand);
echo "Result: " . ($result ?? "No response") . "\n\n";

// If you have sshpass installed, test with password
echo "2. Testing with sshpass (if available)...\n";
$sshpassCheck = shell_exec('which sshpass 2>&1');
if (strpos($sshpassCheck, 'sshpass') !== false) {
    echo "sshpass found at: " . trim($sshpassCheck) . "\n";
    
    // Test MySQL query through SSH
    echo "\n3. Testing MySQL query through SSH...\n";
    $mysqlQuery = "SELECT COUNT(*) as total FROM vicidial_list WHERE list_id = 101";
    $sshCommand = sprintf(
        'sshpass -p %s ssh -p 11845 -o StrictHostKeyChecking=no root@37.27.138.222 "mysql -u cron -p1234 asterisk -e \"%s\"" 2>&1',
        'Monster@2213@!',
        $mysqlQuery
    );
    
    echo "Executing: Count leads in list 101...\n";
    $result = shell_exec($sshCommand);
    echo "Result: " . ($result ?? "No response") . "\n\n";
    
    // Search for specific phone
    echo "4. Searching for phone 8064378907 in Vici...\n";
    $searchQuery = "SELECT lead_id, phone_number, vendor_lead_code, list_id FROM vicidial_list WHERE phone_number LIKE '%8064378907%' LIMIT 5";
    $searchCommand = sprintf(
        'sshpass -p %s ssh -p 11845 -o StrictHostKeyChecking=no root@37.27.138.222 "mysql -u cron -p1234 asterisk -e \"%s\"" 2>&1',
        'Monster@2213@!',
        $searchQuery
    );
    
    $result = shell_exec($searchCommand);
    echo "Search Result:\n" . ($result ?? "No response") . "\n\n";
    
} else {
    echo "sshpass not found. Install with: brew install hudochenkov/sshpass/sshpass (Mac) or apt-get install sshpass (Linux)\n";
    echo "Or run this script from the deployed Render environment where sshpass should be available.\n";
}

echo "\n5. Alternative: Use curl to test through Render proxy...\n";
echo "curl https://quotingfast-brain-ohio.onrender.com/vici-proxy/test\n\n";

echo "Done!\n";