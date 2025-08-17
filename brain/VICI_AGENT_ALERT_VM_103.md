# Vici Agent Alert Script for VM List 103
**Created:** January 15, 2025

## Overview
List 103 is the Voicemail Drop list where leads are moved when agents need to leave a voicemail. We need to configure an Agent Alert Script that notifies agents when they're working with leads from this list.

## Agent Alert Script Configuration

### 1. In Vici Admin → Campaigns → [Your Campaign] → Detail View

**Agent Alert Enabled:** YES
**Agent Alert Delay:** 0 (immediate)

### 2. Agent Alert Text for List 103

```
*** VOICEMAIL LIST - LEAVE MESSAGE ***

This lead is in the VOICEMAIL queue (List 103).

ACTION REQUIRED:
1. Leave a brief voicemail message
2. Mark disposition as LVM (Left Voicemail)
3. Lead will auto-move to List 104 after VM

SCRIPT:
"Hi [NAME], this is [AGENT] from QuotingFast calling about your auto insurance quote request. I have some great rates to share with you. Please call me back at [CALLBACK NUMBER]. Thank you!"

Press OK to continue...
```

### 3. List-Specific Alert Using Custom Script

Create a custom AGI script that checks the list_id and displays different alerts:

```perl
#!/usr/bin/perl
# /usr/share/astguiclient/agi_alert_vm_list.agi

use strict;
use DBI;
use Asterisk::AGI;

my $AGI = new Asterisk::AGI;
my %input = $AGI->ReadParse();

# Get lead info
my $lead_id = $input{'lead_id'};
my $list_id = $input{'list_id'};

# Check if this is VM list 103
if ($list_id == 103) {
    # Send alert to agent screen
    $AGI->exec('SendText', "VM LIST: Leave voicemail and mark as LVM");
    
    # Log the VM attempt
    my $dbh = DBI->connect("DBI:mysql:Q6hdjl67GRigMofv:localhost:20540", "root", "");
    my $sth = $dbh->prepare("
        INSERT INTO vm_alert_log (lead_id, list_id, alert_time, agent) 
        VALUES (?, ?, NOW(), ?)
    ");
    $sth->execute($lead_id, $list_id, $input{'agent'});
    $dbh->disconnect();
}

exit 0;
```

### 4. Campaign Settings for List 103

In the Campaign settings, set up List 103 with specific handling:

```sql
-- Update campaign list settings for List 103
UPDATE vicidial_lists 
SET 
    list_name = 'VM Drop - Leave Message',
    list_description = 'Voicemail required - Agent must leave message',
    agent_script_override = 'VM_SCRIPT',
    campaign_cid_override = '5555551234',  -- Your VM callback number
    web_form_address = 'http://yourserver/vm_tracker.php?lead=--A--lead_id--B--'
WHERE list_id = 103;
```

### 5. Auto-Movement After VM

The existing script `move_103_104_lvm.sql` handles moving leads from List 103 to 104 after voicemail:

```sql
-- This runs every 15 minutes via cron
UPDATE vicidial_list vl
INNER JOIN lead_moves_tracking lmt ON lmt.lead_id = vl.lead_id
SET 
    vl.list_id = 104,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N'
WHERE 
    vl.list_id = 103
    AND vl.status = 'LVM'
    AND vl.modify_date >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list = 104 
        AND DATE(lm.move_date) = CURDATE()
    );
```

## Implementation Steps

### Step 1: Create VM Alert Table
```sql
CREATE TABLE IF NOT EXISTS vm_alert_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id VARCHAR(30),
    list_id INT,
    alert_time DATETIME,
    agent VARCHAR(20),
    vm_left BOOLEAN DEFAULT FALSE,
    INDEX idx_lead (lead_id),
    INDEX idx_time (alert_time)
);
```

### Step 2: Configure Campaign Alert Settings

1. Log into Vici Admin
2. Go to Campaigns → [Your Campaign] → Modify
3. Scroll to "Agent Alert" section
4. Set:
   - **Agent Alert Enabled:** YES
   - **Agent Alert Delay:** 0
   - **Agent Alert Text:** (paste the text from above)

### Step 3: Create List-Specific Web Form

Create `/srv/www/htdocs/vm_tracker.php`:

```php
<?php
// VM Tracker for List 103
$lead_id = $_GET['lead'] ?? '';

// Log VM attempt
$db = new mysqli('localhost', 'root', '', 'Q6hdjl67GRigMofv', 20540);
$stmt = $db->prepare("UPDATE vm_alert_log SET vm_left = TRUE WHERE lead_id = ?");
$stmt->bind_param("s", $lead_id);
$stmt->execute();

// Display VM confirmation
?>
<!DOCTYPE html>
<html>
<head>
    <title>VM List 103 - Leave Message</title>
    <style>
        body { 
            background: #fff3cd; 
            padding: 20px; 
            font-family: Arial;
        }
        .alert {
            background: #ffc107;
            color: #000;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .script {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="alert">
        <h2>⚠️ VOICEMAIL LIST - ACTION REQUIRED</h2>
        <p>This lead is in List 103 - You MUST leave a voicemail</p>
    </div>
    
    <div class="script">
        <h3>Voicemail Script:</h3>
        <p>"Hi [NAME], this is [YOUR NAME] from QuotingFast calling about your recent auto insurance quote request. I have some competitive rates that could save you money. Please give me a call back at [CALLBACK NUMBER] at your earliest convenience. Thank you and have a great day!"</p>
    </div>
    
    <h3>After Leaving VM:</h3>
    <ol>
        <li>Mark disposition as <strong>LVM</strong></li>
        <li>Lead will auto-move to List 104 in 15 minutes</li>
        <li>Do NOT call this lead again today</li>
    </ol>
</body>
</html>
```

### Step 4: Test the Alert

1. Move a test lead to List 103
2. Have an agent dial from that list
3. Verify they see the alert
4. Confirm LVM disposition moves lead to 104

## Monitoring

Check VM completion rate:
```sql
SELECT 
    DATE(alert_time) as date,
    COUNT(*) as alerts_shown,
    SUM(vm_left) as vms_left,
    ROUND(SUM(vm_left) / COUNT(*) * 100, 1) as completion_rate
FROM vm_alert_log
WHERE list_id = 103
GROUP BY DATE(alert_time)
ORDER BY date DESC
LIMIT 7;
```

## Alternative: Simple Campaign Script Override

If you just need a simple alert without custom tracking, use the Campaign Script Override:

1. Go to Scripts → Add New Script
2. Script ID: `VM_LIST_103`
3. Script Name: "Voicemail List Alert"
4. Script Text:
```
<div style="background:#ffc107; padding:10px; margin:10px 0;">
<b>VOICEMAIL REQUIRED - LIST 103</b><br>
Leave VM using script below, then mark as LVM
</div>

<div style="background:#f0f0f0; padding:10px;">
Hi [[first_name]], this is [[agent_name]] from QuotingFast...<br>
[Continue with script]
</div>
```

5. In Campaign settings, set Script Override for List 103 to use `VM_LIST_103`

This way, whenever an agent gets a lead from List 103, they'll automatically see this alert script instead of the default script.
