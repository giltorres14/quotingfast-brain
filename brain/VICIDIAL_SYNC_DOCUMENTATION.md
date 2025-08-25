# ViciDial to Brain Sync Documentation
**Created: August 21, 2025**

## Current State

### Brain Database (as of Aug 21, 2025 7:16 PM EST)
- **Total Leads**: 242,243
- **List 0**: 227,715 leads (manually imported, need matching with ViciDial)
- **List 101**: 11,080 leads (Test A)
- **NULL List**: 3,396 leads
- **Lists 6018-6026**: Only 52 leads total (need to sync with ViciDial)

### ViciDial Server
- **Lists 6018-6026**: Auto Manual lists (have the actual leads)
- **Lists 101-111**: Test A lists (48-call persistence)
- **Lists 150-153**: Test B lists (12-18 call optimized)

## The Problem
The Brain has 227,715 leads in List 0 that were manually imported to match ViciDial, but they're not properly synced. We need to:
1. Pull leads FROM ViciDial
2. Match them with Brain leads by phone number
3. Update Brain with correct ViciDial external IDs and list assignments

## How to Access ViciDial Data

### Method 1: SSH Direct Access
```bash
# SSH into ViciDial server
ssh root@167.172.143.234

# Access MySQL
mysql -u cron -p1234 asterisk

# Query leads from specific lists
SELECT lead_id, vendor_lead_code, list_id, phone_number, 
       first_name, last_name, status
FROM vicidial_list 
WHERE list_id IN (6018,6019,6020,6021,6022,6023,6024,6025,6026);

# Export to CSV
SELECT * FROM vicidial_list 
WHERE list_id BETWEEN 6018 AND 6026
INTO OUTFILE '/tmp/vici_leads_6018_6026.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Method 2: SSH Tunnel for Remote Access
```bash
# Set up SSH tunnel (run on your local machine)
ssh -L 3307:localhost:3306 root@167.172.143.234

# Then connect via local port
mysql -h 127.0.0.1 -P 3307 -u cron -p1234 asterisk
```

### Method 3: ViciDial Admin Interface
1. Login to ViciDial Admin: http://167.172.143.234/vicidial/admin.php
2. Go to Lists > List IDs
3. Download each list (6018-6026) as CSV
4. Import the CSV data to match with Brain

## Manual Sync Process

### Step 1: Export ViciDial Data
```sql
-- On ViciDial server
USE asterisk;

-- Get counts per list
SELECT list_id, COUNT(*) as count 
FROM vicidial_list 
WHERE list_id BETWEEN 6018 AND 6026 
GROUP BY list_id;

-- Export all leads
SELECT lead_id, vendor_lead_code, list_id, phone_number,
       first_name, last_name, address1, city, state, 
       postal_code, status, called_count
FROM vicidial_list
WHERE list_id BETWEEN 6018 AND 6026
INTO OUTFILE '/tmp/vici_export.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Step 2: Import to Brain for Matching
```php
// Use the sync script
php sync_vici_to_brain.php

// Or manually update Brain database
UPDATE leads 
SET vici_list_id = [VICI_LIST_ID],
    external_lead_id = [VICI_VENDOR_LEAD_CODE]
WHERE phone = [VICI_PHONE_NUMBER];
```

### Step 3: Verify Sync
```sql
-- Check Brain database
SELECT vici_list_id, COUNT(*) 
FROM leads 
GROUP BY vici_list_id 
ORDER BY vici_list_id;
```

## Quick Commands

### Check Brain Status
```bash
cd /Users/giltorres/Downloads/platformparcelsms-main/brain
php vici_stats.php
```

### Run Sync Analysis
```bash
php sync_vici_direct.php
# Choose option 3 to export Brain leads
```

### Generate External IDs
```bash
printf "1\n" | php sync_vici_direct.php
```

## Important Notes

1. **DO NOT** push Brain leads to ViciDial - ViciDial already has the leads
2. **DO** pull ViciDial data and match it with Brain
3. **List 0 in Brain** contains leads that need to be matched with ViciDial lists 6018-6026
4. **External IDs** must match between systems for proper tracking

## Database Connections

### Brain Database (PostgreSQL)
- Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
- Port: 5432
- Database: brain_production
- Username: brain_user
- Password: KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ

### ViciDial Database (MySQL)
- Host: 167.172.143.234 (via SSH)
- Port: 3306
- Database: asterisk
- Username: cron
- Password: 1234

## Web UI Access

### Brain Dashboard
- URL: https://quotingfast-brain-ohio.onrender.com/admin
- Vici Dashboard: https://quotingfast-brain-ohio.onrender.com/vici
- Command Center: https://quotingfast-brain-ohio.onrender.com/vici/command-center

### ViciDial Admin
- URL: http://167.172.143.234/vicidial/admin.php
- Username: 6666
- Password: [Your ViciDial password]

## Next Steps

1. **Export ViciDial leads** from lists 6018-6026
2. **Match them** with Brain List 0 leads by phone number
3. **Update Brain** with correct list IDs and external IDs
4. **Verify** the sync is complete
5. **Set up automated sync** for future updates







