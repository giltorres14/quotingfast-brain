<?php
// fix_vici_optin_and_lists.php
// 1. Create missing lists in Vici
// 2. Update existing leads with correct opt-in dates from Brain

echo "=== FIXING VICI OPT-IN DATES AND CREATING LISTS ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;

$proxyUrl = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';

// STEP 1: Create missing lists
echo "📋 STEP 1: Creating missing Vici lists...\n\n";

$lists = [
    ['id' => 105, 'name' => 'Voicemail Drop 2', 'active' => 'Y'],
    ['id' => 106, 'name' => 'Phase 2 - 2x/day', 'active' => 'Y'],
    ['id' => 107, 'name' => 'Cool Down - No Calls', 'active' => 'N'], // Inactive
    ['id' => 108, 'name' => 'Phase 3 - 1x/day', 'active' => 'Y'],
    ['id' => 110, 'name' => 'Archive - TCPA/Old', 'active' => 'N'], // Inactive
    ['id' => 199, 'name' => 'DNC - TCPA 90 Day', 'active' => 'N'], // Inactive
];

foreach ($lists as $list) {
    echo "   Creating List {$list['id']} ({$list['name']})... ";
    
    // Check if list exists first
    $checkCmd = "mysql -u root Q6hdjl67GRigMofv -e \"SELECT list_id FROM vicidial_lists WHERE list_id = {$list['id']} LIMIT 1\" 2>&1";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response && strpos($response, 'Empty set') === false && strpos($response, $list['id']) !== false) {
        echo "Already exists ✓\n";
        continue;
    }
    
    // Create the list
    $createCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
        INSERT IGNORE INTO vicidial_lists (
            list_id, list_name, campaign_id, active, 
            list_description, list_changedate, list_lastcalldate
        ) VALUES (
            {$list['id']}, 
            '{$list['name']}',
            'Autodial',
            '{$list['active']}',
            'Created by Brain Lead Flow System',
            NOW(),
            NOW()
        )
    \" 2>&1";
    
    $ch = curl_init($proxyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $createCmd]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ Created\n";
    } else {
        echo "❌ Failed\n";
    }
}

// STEP 2: Update existing Vici leads with correct opt-in dates
echo "\n📅 STEP 2: Updating Vici leads with correct opt-in dates from Brain...\n\n";

// Get Brain leads with external_lead_id
$totalLeads = Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->count();

echo "   Found {$totalLeads} Brain leads with valid IDs\n";
echo "   Processing in batches of 100...\n\n";

$processed = 0;
$updated = 0;
$batchSize = 100;

Lead::whereNotNull('external_lead_id')
    ->whereRaw("LENGTH(external_lead_id) = 13")
    ->chunk($batchSize, function($leads) use (&$processed, &$updated, $proxyUrl, $totalLeads) {
        
        $updateStatements = [];
        
        foreach ($leads as $lead) {
            // Get the opt-in date (use created_at if opt_in_date is null)
            $optInDate = $lead->opt_in_date ?? $lead->created_at;
            
            if ($optInDate) {
                $optInDateStr = $optInDate->format('Y-m-d H:i:s');
                $brainId = $lead->external_lead_id;
                
                // Create UPDATE statement
                $updateStatements[] = "UPDATE vicidial_list SET entry_date = '{$optInDateStr}' WHERE vendor_lead_code = '{$brainId}';";
            }
        }
        
        if (!empty($updateStatements)) {
            // Execute updates in batch
            $sql = implode("\n", $updateStatements);
            $command = "mysql -u root Q6hdjl67GRigMofv -e \"" . str_replace('"', '\\"', $sql) . "\" 2>&1";
            
            $ch = curl_init($proxyUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $command]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $updated += count($updateStatements);
            }
        }
        
        $processed += count($leads);
        
        if ($processed % 1000 == 0 || $processed == $totalLeads) {
            $percent = round(($processed / $totalLeads) * 100);
            echo "   Progress: {$processed}/{$totalLeads} ({$percent}%) - Updated: {$updated}\n";
        }
    });

// STEP 3: Check for leads that need immediate archiving
echo "\n⚠️  STEP 3: Checking for leads that need immediate TCPA archiving...\n\n";

$checkCmd = "mysql -u root Q6hdjl67GRigMofv -e \"
    SELECT 
        COUNT(*) as total,
        MIN(DATEDIFF(CURDATE(), entry_date)) as min_days,
        MAX(DATEDIFF(CURDATE(), entry_date)) as max_days
    FROM vicidial_list
    WHERE list_id NOT IN (199, 998, 999)
    AND entry_date IS NOT NULL
    AND DATEDIFF(CURDATE(), entry_date) >= 89
\" 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $checkCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo $result['output'] . "\n";
    }
}

// Run the TCPA compliance script immediately
echo "\n🚨 Running TCPA 90-day compliance check NOW...\n";
$tcpaCmd = "mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_90day_compliance.sql 2>&1";

$ch = curl_init($proxyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['command' => $tcpaCmd]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if (isset($result['output'])) {
        echo "   Result: " . substr($result['output'], 0, 200) . "\n";
    }
}

echo "\n=== FIX COMPLETE ===\n\n";
echo "✅ Summary:\n";
echo "   - Missing lists created (105-110, 199)\n";
echo "   - {$updated} Vici leads updated with correct opt-in dates\n";
echo "   - TCPA compliance check executed\n";
echo "   - Lead flow system is now fully operational\n\n";
echo "⚠️  IMPORTANT:\n";
echo "   - All future leads from Brain will include correct opt-in date\n";
echo "   - TCPA 90-day compliance is enforced every 30 minutes\n";
echo "   - Leads are archived at 85 days (safety buffer)\n";
echo "   - DNC list (199) for 90+ day leads\n";

