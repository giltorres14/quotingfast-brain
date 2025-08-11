<?php
/**
 * Test script to verify we can update Vici vendor_lead_code
 * Run: php test_vici_update.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Lead;

echo "========================================\n";
echo "Testing Vici Update Capability\n";
echo "========================================\n\n";

try {
    // Connect to Vici database
    $viciDb = new PDO(
        'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
        env('VICI_DB_USER', 'cron'),
        env('VICI_DB_PASS', '1234')
    );
    
    echo "✅ Connected to Vici database\n\n";
    
    // First, let's check what campaigns exist and filter for Auto2 and Autodial
    echo "Checking campaigns in Vici...\n";
    $campaignStmt = $viciDb->query("
        SELECT DISTINCT list_id, campaign_id 
        FROM vicidial_list 
        WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL')
        LIMIT 10
    ");
    
    $campaigns = $campaignStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($campaigns)) {
        echo "⚠️  No leads found in Auto2 or Autodial campaigns\n";
        echo "Let's check what campaigns exist:\n";
        
        $allCampaignsStmt = $viciDb->query("
            SELECT DISTINCT campaign_id, COUNT(*) as lead_count 
            FROM vicidial_list 
            GROUP BY campaign_id 
            LIMIT 20
        ");
        
        $allCampaigns = $allCampaignsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allCampaigns as $camp) {
            echo "  - Campaign: {$camp['campaign_id']} ({$camp['lead_count']} leads)\n";
        }
    } else {
        echo "Found leads in target campaigns:\n";
        foreach ($campaigns as $camp) {
            echo "  - Campaign: {$camp['campaign_id']}, List: {$camp['list_id']}\n";
        }
    }
    
    echo "\n";
    
    // Now let's try to find a test lead to update
    echo "Finding a test lead to update...\n";
    
    $testStmt = $viciDb->prepare("
        SELECT lead_id, phone_number, first_name, last_name, vendor_lead_code, campaign_id
        FROM vicidial_list 
        WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL')
        AND (vendor_lead_code IS NULL OR vendor_lead_code = '')
        LIMIT 1
    ");
    
    $testStmt->execute();
    $testLead = $testStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testLead) {
        echo "Found test lead:\n";
        echo "  Lead ID: {$testLead['lead_id']}\n";
        echo "  Name: {$testLead['first_name']} {$testLead['last_name']}\n";
        echo "  Phone: {$testLead['phone_number']}\n";
        echo "  Campaign: {$testLead['campaign_id']}\n";
        echo "  Current vendor_lead_code: " . ($testLead['vendor_lead_code'] ?: '(empty)') . "\n\n";
        
        // Generate a test vendor code
        $testVendorCode = 'BRAIN_TEST_' . time();
        
        echo "Attempting to update vendor_lead_code to: $testVendorCode\n";
        
        // Try the update
        $updateStmt = $viciDb->prepare("
            UPDATE vicidial_list 
            SET vendor_lead_code = :vendor_code
            WHERE lead_id = :lead_id
        ");
        
        $result = $updateStmt->execute([
            'vendor_code' => $testVendorCode,
            'lead_id' => $testLead['lead_id']
        ]);
        
        if ($result) {
            echo "✅ Update executed successfully!\n\n";
            
            // Verify the update
            $verifyStmt = $viciDb->prepare("
                SELECT vendor_lead_code 
                FROM vicidial_list 
                WHERE lead_id = :lead_id
            ");
            
            $verifyStmt->execute(['lead_id' => $testLead['lead_id']]);
            $updated = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($updated['vendor_lead_code'] === $testVendorCode) {
                echo "✅ VERIFIED: vendor_lead_code was successfully updated!\n";
                echo "  New value: {$updated['vendor_lead_code']}\n\n";
                
                // Revert the test change
                echo "Reverting test change...\n";
                $revertStmt = $viciDb->prepare("
                    UPDATE vicidial_list 
                    SET vendor_lead_code = :vendor_code
                    WHERE lead_id = :lead_id
                ");
                
                $revertStmt->execute([
                    'vendor_code' => $testLead['vendor_lead_code'],
                    'lead_id' => $testLead['lead_id']
                ]);
                
                echo "✅ Test change reverted\n";
            } else {
                echo "❌ Update failed - value didn't change\n";
            }
        } else {
            echo "❌ Update query failed\n";
            print_r($updateStmt->errorInfo());
        }
    } else {
        echo "No test lead found in Auto2/Autodial campaigns without vendor_lead_code\n";
        
        // Let's check if there are ANY leads in those campaigns
        $countStmt = $viciDb->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN vendor_lead_code IS NOT NULL AND vendor_lead_code != '' THEN 1 ELSE 0 END) as with_vendor_code
            FROM vicidial_list 
            WHERE campaign_id IN ('Auto2', 'Autodial', 'AUTO2', 'AUTODIAL')
        ");
        
        $countStmt->execute();
        $counts = $countStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nCampaign statistics:\n";
        echo "  Total leads in Auto2/Autodial: {$counts['total']}\n";
        echo "  Leads with vendor_lead_code: {$counts['with_vendor_code']}\n";
        echo "  Leads needing update: " . ($counts['total'] - $counts['with_vendor_code']) . "\n";
    }
    
    echo "\n========================================\n";
    echo "CONCLUSION: ";
    if ($testLead) {
        echo "✅ We CAN update Vici vendor_lead_code!\n";
    } else {
        echo "⚠️  Need to verify with actual campaign data\n";
    }
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "This might be a connection or permission issue.\n";
}
