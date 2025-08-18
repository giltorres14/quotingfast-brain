<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== SEARCHING FOR LEADS IN AUTODIAL AND AUTO2 CAMPAIGNS ===\n\n";

// Get a sample of Brain leads to search for
$sampleLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'phone', 'first_name', 'last_name', 'external_lead_id']);

echo "Sample Brain leads to search for:\n";
foreach ($sampleLeads as $lead) {
    echo "- {$lead->first_name} {$lead->last_name}: {$lead->phone} (Brain ID: {$lead->external_lead_id})\n";
}
echo "\n";

// Function to execute command via proxy
function executeViciCommand($command) {
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => $command
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        return $data['output'] ?? '';
    }
    return null;
}

// First, let's see what campaigns exist
echo "Step 1: Checking what campaigns exist in Vici...\n";
$campaignsCmd = 'mysql asterisk -e "SELECT campaign_id, campaign_name, active FROM vicidial_campaigns" 2>&1';
$output = executeViciCommand($campaignsCmd);
if ($output) {
    echo substr($output, 0, 500) . "\n\n";
}

// Check what lists exist
echo "Step 2: Checking what lists exist...\n";
$listsCmd = 'mysql asterisk -e "SELECT list_id, list_name, campaign_id FROM vicidial_lists ORDER BY list_id" 2>&1';
$output = executeViciCommand($listsCmd);
if ($output) {
    echo substr($output, 0, 1000) . "\n\n";
}

// Check lead counts by list
echo "Step 3: Checking lead counts by list...\n";
$countCmd = 'mysql asterisk -e "SELECT list_id, COUNT(*) as total FROM vicidial_list GROUP BY list_id ORDER BY total DESC" 2>&1';
$output = executeViciCommand($countCmd);
if ($output) {
    echo substr($output, 0, 1000) . "\n\n";
}

// Check for leads in specific campaigns (join with campaign_lists table)
echo "Step 4: Checking leads in Autodial and Auto2 campaigns...\n";
$campaignLeadsCmd = 'mysql asterisk -e "
    SELECT 
        vl.list_id,
        COUNT(*) as lead_count,
        MIN(vl.phone_number) as sample_phone,
        MIN(vl.first_name) as sample_first,
        MIN(vl.last_name) as sample_last
    FROM vicidial_list vl
    WHERE vl.list_id IN (
        SELECT list_id FROM vicidial_lists WHERE campaign_id IN (\'Autodial\', \'Auto2\')
        UNION
        SELECT list_id FROM vicidial_campaign_lists WHERE campaign_id IN (\'Autodial\', \'Auto2\')
    )
    GROUP BY vl.list_id
    ORDER BY lead_count DESC
" 2>&1';
$output = executeViciCommand($campaignLeadsCmd);
if ($output) {
    echo $output . "\n\n";
}

// Search for specific phone numbers across ALL lists
echo "Step 5: Searching for specific Brain leads in ALL Vici lists...\n";
foreach ($sampleLeads->take(3) as $lead) {
    $phone = preg_replace('/[^0-9]/', '', $lead->phone); // Clean phone number
    echo "\nSearching for {$lead->first_name} {$lead->last_name} (Phone: {$phone})...\n";
    
    $searchCmd = sprintf(
        'mysql asterisk -e "SELECT lead_id, list_id, vendor_lead_code, first_name, last_name, status FROM vicidial_list WHERE phone_number = \'%s\' OR phone_number = \'%s\'" 2>&1',
        $phone,
        $lead->phone
    );
    
    $output = executeViciCommand($searchCmd);
    if ($output && strpos($output, 'ERROR') === false) {
        if (strpos($output, 'lead_id') !== false && count(explode("\n", $output)) > 1) {
            echo "✅ FOUND in Vici:\n";
            echo $output . "\n";
        } else {
            echo "❌ Not found in Vici\n";
        }
    } else {
        echo "❌ Error or not found\n";
    }
}

// Check what's in the most populated lists
echo "\nStep 6: Sampling leads from the most populated lists...\n";
$sampleCmd = 'mysql asterisk -e "
    SELECT 
        list_id,
        lead_id,
        phone_number,
        first_name,
        last_name,
        vendor_lead_code,
        status
    FROM vicidial_list
    WHERE list_id IN (
        SELECT list_id 
        FROM (
            SELECT list_id, COUNT(*) as cnt 
            FROM vicidial_list 
            GROUP BY list_id 
            ORDER BY cnt DESC 
            LIMIT 3
        ) as top_lists
    )
    ORDER BY list_id, lead_id DESC
    LIMIT 15
" 2>&1';
$output = executeViciCommand($sampleCmd);
if ($output) {
    echo $output . "\n";
}

echo "\n=== SEARCH COMPLETE ===\n";



require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Lead;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== SEARCHING FOR LEADS IN AUTODIAL AND AUTO2 CAMPAIGNS ===\n\n";

// Get a sample of Brain leads to search for
$sampleLeads = Lead::whereNotNull('phone')
    ->where('phone', '!=', '')
    ->whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'phone', 'first_name', 'last_name', 'external_lead_id']);

echo "Sample Brain leads to search for:\n";
foreach ($sampleLeads as $lead) {
    echo "- {$lead->first_name} {$lead->last_name}: {$lead->phone} (Brain ID: {$lead->external_lead_id})\n";
}
echo "\n";

// Function to execute command via proxy
function executeViciCommand($command) {
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => $command
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        return $data['output'] ?? '';
    }
    return null;
}

// First, let's see what campaigns exist
echo "Step 1: Checking what campaigns exist in Vici...\n";
$campaignsCmd = 'mysql asterisk -e "SELECT campaign_id, campaign_name, active FROM vicidial_campaigns" 2>&1';
$output = executeViciCommand($campaignsCmd);
if ($output) {
    echo substr($output, 0, 500) . "\n\n";
}

// Check what lists exist
echo "Step 2: Checking what lists exist...\n";
$listsCmd = 'mysql asterisk -e "SELECT list_id, list_name, campaign_id FROM vicidial_lists ORDER BY list_id" 2>&1';
$output = executeViciCommand($listsCmd);
if ($output) {
    echo substr($output, 0, 1000) . "\n\n";
}

// Check lead counts by list
echo "Step 3: Checking lead counts by list...\n";
$countCmd = 'mysql asterisk -e "SELECT list_id, COUNT(*) as total FROM vicidial_list GROUP BY list_id ORDER BY total DESC" 2>&1';
$output = executeViciCommand($countCmd);
if ($output) {
    echo substr($output, 0, 1000) . "\n\n";
}

// Check for leads in specific campaigns (join with campaign_lists table)
echo "Step 4: Checking leads in Autodial and Auto2 campaigns...\n";
$campaignLeadsCmd = 'mysql asterisk -e "
    SELECT 
        vl.list_id,
        COUNT(*) as lead_count,
        MIN(vl.phone_number) as sample_phone,
        MIN(vl.first_name) as sample_first,
        MIN(vl.last_name) as sample_last
    FROM vicidial_list vl
    WHERE vl.list_id IN (
        SELECT list_id FROM vicidial_lists WHERE campaign_id IN (\'Autodial\', \'Auto2\')
        UNION
        SELECT list_id FROM vicidial_campaign_lists WHERE campaign_id IN (\'Autodial\', \'Auto2\')
    )
    GROUP BY vl.list_id
    ORDER BY lead_count DESC
" 2>&1';
$output = executeViciCommand($campaignLeadsCmd);
if ($output) {
    echo $output . "\n\n";
}

// Search for specific phone numbers across ALL lists
echo "Step 5: Searching for specific Brain leads in ALL Vici lists...\n";
foreach ($sampleLeads->take(3) as $lead) {
    $phone = preg_replace('/[^0-9]/', '', $lead->phone); // Clean phone number
    echo "\nSearching for {$lead->first_name} {$lead->last_name} (Phone: {$phone})...\n";
    
    $searchCmd = sprintf(
        'mysql asterisk -e "SELECT lead_id, list_id, vendor_lead_code, first_name, last_name, status FROM vicidial_list WHERE phone_number = \'%s\' OR phone_number = \'%s\'" 2>&1',
        $phone,
        $lead->phone
    );
    
    $output = executeViciCommand($searchCmd);
    if ($output && strpos($output, 'ERROR') === false) {
        if (strpos($output, 'lead_id') !== false && count(explode("\n", $output)) > 1) {
            echo "✅ FOUND in Vici:\n";
            echo $output . "\n";
        } else {
            echo "❌ Not found in Vici\n";
        }
    } else {
        echo "❌ Error or not found\n";
    }
}

// Check what's in the most populated lists
echo "\nStep 6: Sampling leads from the most populated lists...\n";
$sampleCmd = 'mysql asterisk -e "
    SELECT 
        list_id,
        lead_id,
        phone_number,
        first_name,
        last_name,
        vendor_lead_code,
        status
    FROM vicidial_list
    WHERE list_id IN (
        SELECT list_id 
        FROM (
            SELECT list_id, COUNT(*) as cnt 
            FROM vicidial_list 
            GROUP BY list_id 
            ORDER BY cnt DESC 
            LIMIT 3
        ) as top_lists
    )
    ORDER BY list_id, lead_id DESC
    LIMIT 15
" 2>&1';
$output = executeViciCommand($sampleCmd);
if ($output) {
    echo $output . "\n";
}

echo "\n=== SEARCH COMPLETE ===\n";






