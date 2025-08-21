#!/usr/bin/env php
<?php
/**
 * VICIDIAL LIST SETUP FOR TEST A & TEST B
 * Creates/updates all lists with proper configuration
 * Date: August 20, 2025, 9:30 AM EDT
 * 
 * This script sets up lists WITHOUT affecting current calling
 */

// Configuration
$viciProxy = 'https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute';
$apiKey = 'sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ';

// List configurations
$lists = [
    // TEST A - 48 Call Strategy with 3-day rest
    [
        'list_id' => 101,
        'list_name' => 'Test A - Fresh Leads (Days 1-2)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Fresh leads, first 2 days, high priority calling',
        'reset_time' => '',  // Blank - controlled by cron
        'agent_script_override' => 'INSURANCE',
        'list_order' => 10,  // Highest priority
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 5 calls or 2 days -> List 102'
    ],
    [
        'list_id' => 102,
        'list_name' => 'Test A - Early Stage (Days 3-4)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 3-4, continuing attempts',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 20,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 8 calls or 4 days -> List 103'
    ],
    [
        'list_id' => 103,
        'list_name' => 'Test A - Mid Stage (Days 5-6)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 5-6, persistence phase',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 30,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 12 calls or 6 days -> List 104'
    ],
    [
        'list_id' => 104,
        'list_name' => 'Test A - Week 1 Complete (Days 7-8)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 7-8, end of first week',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 40,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 16 calls or 8 days -> List 106'
    ],
    [
        'list_id' => 106,
        'list_name' => 'Test A - Extended (Days 9-11)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 9-11, extended calling',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 50,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 24 calls or 11 days -> List 107'
    ],
    [
        'list_id' => 107,
        'list_name' => 'Test A - Two Weeks (Days 12-13)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 12-13, approaching rest period',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 60,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 30 calls or 13 days -> List 108'
    ],
    [
        'list_id' => 108,
        'list_name' => 'Test A - REST PERIOD (3 Days)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => '3-DAY REST - No calling during this period',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 70,
        'expiration_date' => '2099-12-31',
        'comments' => 'REST PERIOD: Leads rest here for 3 days. Movement: After 3 days -> List 109'
    ],
    [
        'list_id' => 109,
        'list_name' => 'Test A - Final Push (Days 17-30)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 17-30, final attempts after rest',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 80,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 40 calls or 30 days -> List 111'
    ],
    [
        'list_id' => 111,
        'list_name' => 'Test A - Long Term (Days 31-90)',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Days 31-90, long-term nurture',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 90,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 48 calls or 90 days -> Archive'
    ],
    
    // TEST B - 12-18 Call Optimized Strategy
    [
        'list_id' => 150,
        'list_name' => 'Test B - Fresh Optimal',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Test B fresh leads, 0-4 calls',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 15,  // Higher priority than Test A List 102
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 5 calls -> List 151. NO REST PERIOD.'
    ],
    [
        'list_id' => 151,
        'list_name' => 'Test B - Mid Stage',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Test B mid stage, 5-8 calls',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 25,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 9 calls -> List 152'
    ],
    [
        'list_id' => 152,
        'list_name' => 'Test B - Extended',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Test B extended, 9-12 calls',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 35,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 13 calls -> List 153'
    ],
    [
        'list_id' => 153,
        'list_name' => 'Test B - Final',
        'campaign_id' => 'AUTODIAL',
        'active' => 'Y',
        'list_description' => 'Test B final attempts, 13-18 calls',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 45,
        'expiration_date' => '2099-12-31',
        'comments' => 'Movement: After 18 calls -> Archive. Total calls capped at 18.'
    ],
    
    // SPECIAL LISTS
    [
        'list_id' => 998,
        'list_name' => 'TRANSFERRED - Success',
        'campaign_id' => 'AUTODIAL',
        'active' => 'N',  // Not active - these are done
        'list_description' => 'Successfully transferred leads - DO NOT CALL',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 999,  // Never dial
        'expiration_date' => '2099-12-31',
        'comments' => 'Leads with XFER or XFERA disposition. Success tracking only.'
    ],
    [
        'list_id' => 999,
        'list_name' => 'DNC/DNQ - Never Call',
        'campaign_id' => 'AUTODIAL',
        'active' => 'N',  // Not active - permanent exclusion
        'list_description' => 'Do Not Call / Do Not Qualify - PERMANENT EXCLUSION',
        'reset_time' => '',
        'agent_script_override' => 'INSURANCE',
        'list_order' => 999,  // Never dial
        'expiration_date' => '2099-12-31',
        'comments' => 'DNC, DNCL, DNQ, DC, ADC dispositions. Never call these leads.'
    ]
];

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          VICIDIAL LIST SETUP FOR TEST A & TEST B              ║\n";
echo "║                 " . date('Y-m-d H:i:s T') . "                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Function to execute ViciDial query
function executeViciQuery($query, $viciProxy, $apiKey) {
    $ch = curl_init($viciProxy);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP $httpCode"];
    }
    
    $result = json_decode($response, true);
    return $result;
}

// Process each list
$created = 0;
$updated = 0;
$errors = 0;

foreach ($lists as $list) {
    echo "Processing List {$list['list_id']}: {$list['list_name']}...\n";
    
    // Check if list exists
    $checkQuery = "SELECT list_id FROM vicidial_lists WHERE list_id = '{$list['list_id']}'";
    $result = executeViciQuery($checkQuery, $viciProxy, $apiKey);
    
    if (!$result['success']) {
        echo "  ❌ Error checking list: {$result['error']}\n";
        $errors++;
        continue;
    }
    
    $exists = !empty($result['data']);
    
    if ($exists) {
        // Update existing list
        $updateQuery = "UPDATE vicidial_lists SET 
            list_name = '" . addslashes($list['list_name']) . "',
            campaign_id = '{$list['campaign_id']}',
            active = '{$list['active']}',
            list_description = '" . addslashes($list['list_description']) . "',
            reset_time = '{$list['reset_time']}',
            agent_script_override = '{$list['agent_script_override']}',
            list_changedate = NOW(),
            list_lastcalldate = NOW(),
            expiration_date = '{$list['expiration_date']}'
        WHERE list_id = '{$list['list_id']}'";
        
        $result = executeViciQuery($updateQuery, $viciProxy, $apiKey);
        
        if ($result['success']) {
            echo "  ✅ Updated existing list\n";
            $updated++;
        } else {
            echo "  ❌ Failed to update: {$result['error']}\n";
            $errors++;
        }
    } else {
        // Create new list
        $insertQuery = "INSERT INTO vicidial_lists (
            list_id,
            list_name,
            campaign_id,
            active,
            list_description,
            reset_time,
            agent_script_override,
            list_changedate,
            list_lastcalldate,
            expiration_date
        ) VALUES (
            '{$list['list_id']}',
            '" . addslashes($list['list_name']) . "',
            '{$list['campaign_id']}',
            '{$list['active']}',
            '" . addslashes($list['list_description']) . "',
            '{$list['reset_time']}',
            '{$list['agent_script_override']}',
            NOW(),
            NOW(),
            '{$list['expiration_date']}'
        )";
        
        $result = executeViciQuery($insertQuery, $viciProxy, $apiKey);
        
        if ($result['success']) {
            echo "  ✅ Created new list\n";
            $created++;
        } else {
            echo "  ❌ Failed to create: {$result['error']}\n";
            $errors++;
        }
    }
    
    // Small delay to avoid overwhelming the API
    usleep(100000); // 0.1 second
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        SETUP COMPLETE                         ║\n";
echo "╠════════════════════════════════════════════════════════════════╣\n";
echo "║  Created: $created lists                                             ║\n";
echo "║  Updated: $updated lists                                             ║\n";
echo "║  Errors:  $errors                                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📋 NEXT STEPS (TODO List Created):\n";
echo "1. Configure Campaign settings (see TODO list)\n";
echo "2. Verify list order settings in ViciDial admin\n";
echo "3. Test with a few leads before full deployment\n\n";

echo "⚠️  IMPORTANT NOTES:\n";
echo "• Reset times are BLANK (controlled by our crons)\n";
echo "• List order controls priority (lower = higher priority)\n";
echo "• Lists 998 & 999 are inactive (terminal statuses)\n";
echo "• Test B lists (150-153) interleave with Test A for optimal coverage\n\n";

// Create summary documentation
$summaryDoc = "# VICIDIAL LIST CONFIGURATION SUMMARY
Generated: " . date('Y-m-d H:i:s T') . "

## TEST A LISTS (48-Call Strategy with 3-Day Rest)
| List | Priority | Days | Calls | Description |
|------|----------|------|-------|-------------|
| 101  | 10       | 1-2  | 0-5   | Fresh leads, highest priority |
| 102  | 20       | 3-4  | 5-8   | Early stage continuation |
| 103  | 30       | 5-6  | 8-12  | Mid stage persistence |
| 104  | 40       | 7-8  | 12-16 | Week 1 complete |
| 106  | 50       | 9-11 | 16-24 | Extended calling |
| 107  | 60       | 12-13| 24-30 | Two weeks |
| 108  | 70       | 14-16| REST  | **3-DAY REST PERIOD** |
| 109  | 80       | 17-30| 30-40 | Final push after rest |
| 111  | 90       | 31-90| 40-48 | Long-term nurture |

## TEST B LISTS (12-18 Call Optimized Strategy)
| List | Priority | Calls | Description |
|------|----------|-------|-------------|
| 150  | 15       | 0-4   | Fresh optimal leads |
| 151  | 25       | 5-8   | Mid stage |
| 152  | 35       | 9-12  | Extended |
| 153  | 45       | 13-18 | Final attempts |

## SPECIAL LISTS
| List | Status   | Description |
|------|----------|-------------|
| 998  | Inactive | Transferred leads (XFER/XFERA) |
| 999  | Inactive | DNC/DNQ - Never call |

## KEY CONFIGURATION POINTS
- **Reset Time:** BLANK (managed by cron scripts)
- **Campaign:** AUTODIAL for all active lists
- **List Order:** Controls priority (lower number = higher priority)
- **Active Status:** Y for calling lists, N for terminal lists
- **Script Override:** INSURANCE (consistent across all)

## LEAD FLOW LOGIC
1. Fresh leads start in List 101 (Test A) or 150 (Test B)
2. Movement based on call count AND time in list
3. Test A includes 3-day rest period in List 108
4. Test B has no rest period (rapid 12-18 call strategy)
5. Terminal dispositions (XFER, DNC, etc.) move to 998/999
";

file_put_contents('VICIDIAL_LIST_SETUP.md', $summaryDoc);
echo "📄 Documentation saved to: VICIDIAL_LIST_SETUP.md\n";



