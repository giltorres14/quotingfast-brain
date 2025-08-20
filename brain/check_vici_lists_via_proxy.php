<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$baseUrl = 'https://quotingfast-brain-ohio.onrender.com';

echo "=== CHECKING VICI LISTS VIA PROXY ===\n\n";

// Function to execute command via proxy
function executeViaProxy($command) {
    global $baseUrl;
    
    $response = Http::timeout(30)->post($baseUrl . '/vici-proxy/execute', [
        'command' => $command
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        if ($data['success'] ?? false) {
            return $data['output'] ?? '';
        } else {
            return "Error: " . ($data['error'] ?? 'Unknown error');
        }
    } else {
        return "HTTP Error: " . $response->status();
    }
}

// Check Autodial campaign
echo "1. Checking Autodial Campaign Details...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT campaign_id, campaign_name, active, dial_method FROM vicidial_campaigns WHERE campaign_id = 'Autodial'\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check lists in campaign_lists table
echo "2. Checking Lists Associated with Autodial (campaign_lists table)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT cl.list_id, l.list_name, l.list_description, l.active FROM vicidial_campaign_lists cl LEFT JOIN vicidial_lists l ON cl.list_id = l.list_id WHERE cl.campaign_id = 'Autodial' ORDER BY cl.list_id\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check lists with campaign_id = Autodial
echo "3. Checking Lists with campaign_id = 'Autodial'...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT list_id, list_name, list_description, active FROM vicidial_lists WHERE campaign_id = 'Autodial' ORDER BY list_id\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check all lists with lead counts
echo "4. All Lists in System with Lead Counts...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT l.list_id, l.list_name, l.campaign_id, COUNT(vl.lead_id) as lead_count FROM vicidial_lists l LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id GROUP BY l.list_id, l.list_name, l.campaign_id ORDER BY lead_count DESC LIMIT 20\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Focus on List 101 (our main list)
echo "5. List 101 (LeadsQuotingFast) Details...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT list_id, list_name, campaign_id, active FROM vicidial_lists WHERE list_id = 101\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Count leads in List 101
echo "6. Lead Count in List 101...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT COUNT(*) as total_leads FROM vicidial_list WHERE list_id = 101\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Status breakdown for List 101
echo "7. Status Breakdown for List 101...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT status, COUNT(*) as count FROM vicidial_list WHERE list_id = 101 GROUP BY status ORDER BY count DESC\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Sample recent leads from List 101
echo "8. Sample Recent Leads from List 101 (checking for Brain IDs)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, status FROM vicidial_list WHERE list_id = 101 ORDER BY lead_id DESC LIMIT 10\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check if any leads have Brain IDs (13-digit vendor_lead_code)
echo "9. Checking for Leads with Brain IDs (13-digit vendor_lead_code)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT COUNT(*) as leads_with_brain_id FROM vicidial_list WHERE list_id = 101 AND vendor_lead_code REGEXP '^[0-9]{13}$'\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check call logs for today
echo "10. Call Logs for Today (sample)...\n";
$today = date('Y-m-d');
$command = "mysql -u cron -p1234 asterisk -e \"SELECT uniqueid, lead_id, list_id, campaign_id, call_date, length_in_sec, status, phone_number FROM vicidial_log WHERE call_date >= '$today 00:00:00' ORDER BY call_date DESC LIMIT 10\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

echo "=== ANALYSIS COMPLETE ===\n";
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$baseUrl = 'https://quotingfast-brain-ohio.onrender.com';

echo "=== CHECKING VICI LISTS VIA PROXY ===\n\n";

// Function to execute command via proxy
function executeViaProxy($command) {
    global $baseUrl;
    
    $response = Http::timeout(30)->post($baseUrl . '/vici-proxy/execute', [
        'command' => $command
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        if ($data['success'] ?? false) {
            return $data['output'] ?? '';
        } else {
            return "Error: " . ($data['error'] ?? 'Unknown error');
        }
    } else {
        return "HTTP Error: " . $response->status();
    }
}

// Check Autodial campaign
echo "1. Checking Autodial Campaign Details...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT campaign_id, campaign_name, active, dial_method FROM vicidial_campaigns WHERE campaign_id = 'Autodial'\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check lists in campaign_lists table
echo "2. Checking Lists Associated with Autodial (campaign_lists table)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT cl.list_id, l.list_name, l.list_description, l.active FROM vicidial_campaign_lists cl LEFT JOIN vicidial_lists l ON cl.list_id = l.list_id WHERE cl.campaign_id = 'Autodial' ORDER BY cl.list_id\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check lists with campaign_id = Autodial
echo "3. Checking Lists with campaign_id = 'Autodial'...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT list_id, list_name, list_description, active FROM vicidial_lists WHERE campaign_id = 'Autodial' ORDER BY list_id\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check all lists with lead counts
echo "4. All Lists in System with Lead Counts...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT l.list_id, l.list_name, l.campaign_id, COUNT(vl.lead_id) as lead_count FROM vicidial_lists l LEFT JOIN vicidial_list vl ON l.list_id = vl.list_id GROUP BY l.list_id, l.list_name, l.campaign_id ORDER BY lead_count DESC LIMIT 20\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Focus on List 101 (our main list)
echo "5. List 101 (LeadsQuotingFast) Details...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT list_id, list_name, campaign_id, active FROM vicidial_lists WHERE list_id = 101\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Count leads in List 101
echo "6. Lead Count in List 101...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT COUNT(*) as total_leads FROM vicidial_list WHERE list_id = 101\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Status breakdown for List 101
echo "7. Status Breakdown for List 101...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT status, COUNT(*) as count FROM vicidial_list WHERE list_id = 101 GROUP BY status ORDER BY count DESC\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Sample recent leads from List 101
echo "8. Sample Recent Leads from List 101 (checking for Brain IDs)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, status FROM vicidial_list WHERE list_id = 101 ORDER BY lead_id DESC LIMIT 10\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check if any leads have Brain IDs (13-digit vendor_lead_code)
echo "9. Checking for Leads with Brain IDs (13-digit vendor_lead_code)...\n";
$command = "mysql -u cron -p1234 asterisk -e \"SELECT COUNT(*) as leads_with_brain_id FROM vicidial_list WHERE list_id = 101 AND vendor_lead_code REGEXP '^[0-9]{13}$'\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

// Check call logs for today
echo "10. Call Logs for Today (sample)...\n";
$today = date('Y-m-d');
$command = "mysql -u cron -p1234 asterisk -e \"SELECT uniqueid, lead_id, list_id, campaign_id, call_date, length_in_sec, status, phone_number FROM vicidial_log WHERE call_date >= '$today 00:00:00' ORDER BY call_date DESC LIMIT 10\"";
$output = executeViaProxy($command);
echo $output . "\n\n";

echo "=== ANALYSIS COMPLETE ===\n";

