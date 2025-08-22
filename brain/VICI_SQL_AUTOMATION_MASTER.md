# ViciDial SQL Automation Master Document
*Last Updated: January 19, 2025*

## 📁 File Organization Structure

```
brain/
├── app/Console/Commands/          # Laravel Commands
│   ├── ViciOptimalTimingControl.php    # Main timing controller
│   ├── ViciExecuteLeadFlow.php         # Lead flow movements
│   ├── SyncViciCallLogsIncremental.php # Call log sync
│   └── SystemHealthCheck.php           # Health monitoring
│
├── database/vici_scripts/         # Raw SQL Scripts
│   ├── test_b_timing.sql         # Test B timing logic
│   ├── lead_movements.sql        # Lead list transitions
│   ├── tcpa_archive.sql          # TCPA compliance
│   └── reset_flags.sql           # Reset operations
│
├── config/vici_flow.php           # Configuration file
└── storage/logs/vici/             # Execution logs
```

## 🔄 Active Automated Scripts

### 1. **Optimal Timing Control** (`ViciOptimalTimingControl.php`)
**Schedule:** Every 5 minutes
**Purpose:** Controls when leads are available for calling
**Key Operations:**
```sql
-- List 151: Available at 10 AM and 2 PM only
UPDATE vicidial_list 
SET called_since_last_reset = 'N'
WHERE list_id = 151 
AND call_count < 7
AND (HOUR(NOW()) = 10 OR HOUR(NOW()) = 14)
```
**Logs:** `/storage/logs/vici/timing_control.log`
**Troubleshooting:** Check if leads have `called_since_last_reset = 'N' during optimal hours

---

### 2. **Lead Flow Movement** (`ViciExecuteLeadFlow.php`)
**Schedule:** Every 5 minutes
**Purpose:** Moves leads between lists based on call count and time
**Key Operations:**
```sql
-- Move from List 150 to 151 after 5 calls
UPDATE vicidial_list 
SET list_id = 151
WHERE list_id = 150 
AND call_count >= 5
AND status NOT IN ('XFER','XFERA')
```
**Logs:** `/storage/logs/vici/lead_movements.log`
**Troubleshooting:** Check `call_count` and `status` fields

---

### 3. **Call Log Sync** (`SyncViciCallLogsIncremental.php`)
**Schedule:** Every 5 minutes
**Purpose:** Imports call logs from Vici to Brain
**Key Operations:**
```sql
SELECT * FROM vicidial_log 
WHERE call_date > :last_sync_time
AND campaign_id IN ('AUTODIAL', 'AUTO2')
```
**Logs:** `/storage/logs/vici/call_sync.log`
**Database:** Uses `Q6hdjl67GRigMofv` (NOT 'asterisk')

---

### 4. **TCPA Archive** (Part of `ViciExecuteLeadFlow.php`)
**Schedule:** Daily at 2 AM
**Purpose:** Archive leads older than 89 days
**Key Operations:**
```sql
UPDATE vicidial_list 
SET list_id = 199
WHERE DATEDIFF(NOW(), entry_date) >= 89
AND list_id NOT IN (199, 998, 999)
```
**Logs:** `/storage/logs/vici/tcpa_archive.log`

---

## 📊 Test B Specific SQL Logic

### List Progression Rules
```sql
-- LIST 150 → 151 (After Golden Hour)
UPDATE vicidial_list SET list_id = 151
WHERE list_id = 150 AND call_count >= 5

-- LIST 151 → 152 (After Day 2)
UPDATE vicidial_list SET list_id = 152
WHERE list_id = 151 AND call_count >= 7
AND TIMESTAMPDIFF(HOUR, last_call_time, NOW()) >= 20

-- LIST 152 → 153 (After Days 3-5)
UPDATE vicidial_list SET list_id = 153
WHERE list_id = 152 AND call_count >= 10
AND DATEDIFF(NOW(), entry_date) >= 5

-- LIST 153 → 160 (NI Retargeting)
UPDATE vicidial_list SET list_id = 160
WHERE list_id = 153 AND status = 'NI'
AND TIMESTAMPDIFF(DAY, last_call_time, NOW()) >= 30
```

### Timing Control by List
```sql
-- List 150: Always available (Golden Hour)
-- No timing restrictions

-- List 151: 10 AM and 2 PM only
UPDATE vicidial_list SET called_since_last_reset = 
  CASE 
    WHEN HOUR(NOW()) IN (10, 14) THEN 'N'
    ELSE 'Y'
  END
WHERE list_id = 151

-- List 152: Once per day, optimal hours
UPDATE vicidial_list SET called_since_last_reset = 'N'
WHERE list_id = 152 
AND DATE(last_call_time) < CURDATE()
AND HOUR(NOW()) IN (10, 11, 14, 15)

-- List 153: Every 3 days
UPDATE vicidial_list SET called_since_last_reset = 'N'
WHERE list_id = 153 
AND DATEDIFF(NOW(), last_call_time) >= 3
```

## 🔍 Monitoring Queries

### Check Lead Distribution
```sql
SELECT 
    list_id,
    COUNT(*) as total_leads,
    SUM(CASE WHEN called_since_last_reset = 'N' THEN 1 ELSE 0 END) as available_now,
    AVG(call_count) as avg_calls,
    MAX(call_count) as max_calls
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153, 160)
GROUP BY list_id
ORDER BY list_id;
```

### Check Today's Movement
```sql
SELECT 
    DATE_FORMAT(modify_date, '%H:00') as hour,
    list_id,
    COUNT(*) as leads_moved
FROM vicidial_list_archive
WHERE DATE(modify_date) = CURDATE()
GROUP BY hour, list_id
ORDER BY hour, list_id;
```

### Check Timing Effectiveness
```sql
SELECT 
    list_id,
    HOUR(last_call_time) as call_hour,
    COUNT(*) as calls_made,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transfers
FROM vicidial_list
WHERE DATE(last_call_time) = CURDATE()
AND list_id IN (150, 151, 152, 153)
GROUP BY list_id, call_hour
ORDER BY list_id, call_hour;
```

## 🛠️ Troubleshooting Guide

### Issue: Leads Not Being Called
1. Check `called_since_last_reset` flag:
```sql
SELECT list_id, called_since_last_reset, COUNT(*)
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153)
GROUP BY list_id, called_since_last_reset;
```

2. Check hopper status:
```sql
SELECT status, COUNT(*) 
FROM vicidial_hopper
WHERE list_id IN (150, 151, 152, 153)
GROUP BY status;
```

### Issue: Leads Not Moving Between Lists
1. Check movement conditions:
```sql
-- Should move but haven't
SELECT list_id, COUNT(*) as stuck_leads
FROM vicidial_list
WHERE 
  (list_id = 150 AND call_count >= 5) OR
  (list_id = 151 AND call_count >= 7) OR
  (list_id = 152 AND call_count >= 10)
GROUP BY list_id;
```

### Issue: Wrong Timing
1. Check server time:
```sql
SELECT NOW() as server_time, 
       CONVERT_TZ(NOW(), 'UTC', 'America/New_York') as est_time;
```

2. Check last reset times:
```sql
SELECT list_id, 
       MIN(last_call_time) as oldest_call,
       MAX(last_call_time) as newest_call
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153)
GROUP BY list_id;
```

## 📝 Configuration File

Create `config/vici_flow.php`:
```php
<?php
return [
    'test_b' => [
        'lists' => [
            'golden_hour' => 150,
            'day_2' => 151,
            'persistence' => 152,
            'final' => 153,
            'ni_retarget' => 160,
            'tcpa_archive' => 199
        ],
        'timing' => [
            '151' => ['hours' => [10, 14]],
            '152' => ['hours' => [10, 11, 14, 15], 'once_per_day' => true],
            '153' => ['days_between' => 3]
        ],
        'movements' => [
            '150_to_151' => ['after_calls' => 5],
            '151_to_152' => ['after_calls' => 7, 'after_hours' => 20],
            '152_to_153' => ['after_calls' => 10, 'after_days' => 5],
            '153_to_160' => ['status' => 'NI', 'after_days' => 30]
        ]
    ]
];
```

## 🚀 Quick Commands

### Manual Execution
```bash
# Test timing control
php artisan vici:optimal-timing

# Force lead movement
php artisan vici:execute-lead-flow --force

# Check system health
php artisan system:health-check
```

### View Logs
```bash
# Timing control log
tail -f storage/logs/vici/timing_control.log

# Lead movement log
tail -f storage/logs/vici/lead_movements.log

# Combined view
tail -f storage/logs/vici/*.log
```

### Database Checks
```bash
# Connect to Vici DB
mysql -h 162.243.139.69 -u cron -p Q6hdjl67GRigMofv

# Quick status check
php artisan tinker
>>> DB::connection('vicidial')->select('SELECT list_id, COUNT(*) as cnt FROM vicidial_list WHERE list_id BETWEEN 150 AND 160 GROUP BY list_id');
```

## 📅 Cron Schedule

Add to crontab:
```bash
# Timing control - every 5 minutes
*/5 * * * * cd /path/to/brain && php artisan vici:optimal-timing >> storage/logs/vici/timing_control.log 2>&1

# Lead flow - every 5 minutes
*/5 * * * * cd /path/to/brain && php artisan vici:execute-lead-flow >> storage/logs/vici/lead_movements.log 2>&1

# Call sync - every 5 minutes
*/5 * * * * cd /path/to/brain && php artisan vici:sync-incremental >> storage/logs/vici/call_sync.log 2>&1

# Health check - every minute
* * * * * cd /path/to/brain && php artisan system:health-check >> storage/logs/vici/health.log 2>&1
```

## 🔴 Critical Notes

1. **Database Name:** Always use `Q6hdjl67GRigMofv` NOT 'asterisk'
2. **Time Zone:** All times in EST/EDT (America/New_York)
3. **Campaign:** All lists must be in AUTODIAL campaign
4. **List Priority:** Set in Vici Admin (150 highest, 160 lowest)
5. **Reset Flag:** `called_since_last_reset` controls availability

## 📊 Success Metrics

Track these daily:
- Leads moved between lists
- Calls made during optimal hours
- Connect rate by list and hour
- Conversion rate Test A vs Test B
- Cost per lead (should decrease)

---

*This document is the single source of truth for all ViciDial SQL automation.*




