<?php

// SSH connection details
$sshHost = '37.27.138.222';
$sshPort = 11845;
$sshUser = 'root';
$sshPass = 'Monster@2213@!';

// MySQL details (through SSH)
$mysqlUser = 'cron';
$mysqlPass = '1234';
$mysqlDb = 'asterisk';

// Function to execute MySQL query through SSH
function executeMysqlQuery($query) {
    global $sshHost, $sshPort, $sshUser, $sshPass, $mysqlUser, $mysqlPass, $mysqlDb;
    
    $mysqlCommand = sprintf(
        'mysql -u %s -p%s %s -e %s',
        escapeshellarg($mysqlUser),
        escapeshellarg($mysqlPass),
        escapeshellarg($mysqlDb),
        escapeshellarg($query)
    );
    
    $sshCommand = sprintf(
        'sshpass -p %s ssh -p %d -o StrictHostKeyChecking=no %s@%s %s 2>&1',
        escapeshellarg($sshPass),
        $sshPort,
        escapeshellarg($sshUser),
        escapeshellarg($sshHost),
        escapeshellarg($mysqlCommand)
    );
    
    $output = shell_exec($sshCommand);
    return $output;
}

echo "=== CHECKING AUTODIAL CAMPAIGN LISTS ===\n\n";

// Check if Autodial campaign exists
$query = "SELECT campaign_id, campaign_name, active, dial_method FROM vicidial_campaigns WHERE campaign_id = 'Autodial'";
$output = executeMysqlQuery($query);
echo "Campaign Info:\n";
echo $output . "\n";

// Get lists associated with Autodial campaign
echo "\n=== LISTS IN AUTODIAL CAMPAIGN (from campaign_lists) ===\n";
$query = "
    SELECT cl.list_id, l.list_name, l.list_description, l.active
    FROM vicidial_campaign_lists cl
    LEFT JOIN vicidial_lists l ON cl.list_id = l.list_id
    WHERE cl.campaign_id = 'Autodial'
    ORDER BY cl.list_id
";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Get lists with campaign_id = 'Autodial'
echo "\n=== LISTS WITH campaign_id = 'Autodial' ===\n";
$query = "
    SELECT list_id, list_name, list_description, active
    FROM vicidial_lists
    WHERE campaign_id = 'Autodial'
    ORDER BY list_id
";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Get all lists and their lead counts
echo "\n=== ALL LISTS IN SYSTEM WITH LEAD COUNTS ===\n";
$query = "
    SELECT l.list_id, l.list_name, l.campaign_id, COUNT(vl.lead_id) as lead_count
    FROM vicidial_lists l
    LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id
    GROUP BY l.list_id, l.list_name, l.campaign_id
    ORDER BY l.list_id
";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Check specific list 101 (LeadsQuotingFast)
echo "\n=== LIST 101 DETAILS ===\n";
$query = "SELECT * FROM vicidial_lists WHERE list_id = 101";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Count leads in list 101
echo "\n=== LEAD COUNT IN LIST 101 ===\n";
$query = "SELECT COUNT(*) as total_leads FROM vicidial_list WHERE list_id = 101";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Status breakdown for list 101
echo "\n=== STATUS BREAKDOWN FOR LIST 101 ===\n";
$query = "
    SELECT status, COUNT(*) as count 
    FROM vicidial_list 
    WHERE list_id = 101 
    GROUP BY status 
    ORDER BY count DESC
";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Sample leads from list 101
echo "\n=== SAMPLE LEADS FROM LIST 101 ===\n";
$query = "
    SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, status, list_id
    FROM vicidial_list 
    WHERE list_id = 101 
    ORDER BY lead_id DESC
    LIMIT 10
";
$output = executeMysqlQuery($query);
echo $output . "\n";

// Check if there are any leads in other lists
echo "\n=== LEADS IN OTHER LISTS ===\n";
$query = "
    SELECT list_id, COUNT(*) as lead_count 
    FROM vicidial_list 
    WHERE list_id != 101 
    GROUP BY list_id 
    ORDER BY lead_count DESC
";
$output = executeMysqlQuery($query);
echo $output . "\n";
