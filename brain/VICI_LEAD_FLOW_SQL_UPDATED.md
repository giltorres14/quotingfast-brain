# Vici Lead Flow SQL Scripts - Updated Configuration
**Last Updated:** January 15, 2025

## 📊 Complete Lead Flow Overview

### List Movement Logic:
```
101 (Immediate) 
  ↓ [After 1st call]
102 (20-min hold) 
  ↓ [After 20 min]
103 (Voicemail) 
  ↓ [After LVM status]
104 (Hot Phase - 4/day × 3 days) 
  ↓ [After 3 workdays]
105 (Extended - 3/day × 7 days) 
  ↓ [After 7 workdays]
106 (Secondary - 2/day × 5 days) 
  ↓ [After 5 workdays]
107 (Cool Down 1 - 2/day × 5 days) 
  ↓ [After 5 workdays]
108 (Rest Period - NO CALLS × 7 days) 
  ↓ [After 7 workdays rest]
109 (Final Attempt - 1/day × 5 days) 
  ↓ [After 5 workdays OR TCPA expiry]
110 (Archive - Permanent)
```

## 🔧 SQL Scripts Required

### 1. **move_101_102.sql** - Immediate to 20-Min Hold
**Trigger:** After first call attempt from List 101
**Timing:** Every 5 minutes (catch leads quickly)
**Logic:** Move all called leads except CALLBK status

```sql
-- move_101_102.sql
-- Moves leads from 101 to 102 after first call (NOT CALLBK)
-- Run every 5 minutes for speed to lead

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.vendor_lead_code as brain_id,
    v.status as last_status
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
AND v.call_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
AND v.status NOT IN ('CALLBK', 'NEW')
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 102
    AND DATE(lm.move_date) = CURDATE()
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 101, 102, '20-min follow-up hold', brain_id
FROM leads_to_move_101_102;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW(),
    vl.modify_date = DATE_ADD(NOW(), INTERVAL 20 MINUTE); -- 20-min delay

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;
COMMIT;
```

### 2. **move_102_103.sql** - 20-Min to Voicemail
**Trigger:** 20 minutes after entering List 102
**Timing:** Every 5 minutes
**Logic:** Move leads that have been in 102 for 20+ minutes

```sql
-- move_102_103.sql
-- Moves leads from 102 to 103 after 20-minute wait
-- Run every 5 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 102
AND vl.list_entry_date <= DATE_SUB(NOW(), INTERVAL 20 MINUTE)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
    AND DATE(lm.move_date) = CURDATE()
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 102, 103, 'Ready for voicemail', brain_id
FROM leads_to_move_102_103;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;
COMMIT;
```

### 3. **move_101_103_direct.sql** - CALLBK Direct to VM
**Trigger:** CALLBK status from List 101
**Timing:** Every 15 minutes
**Logic:** Skip 102, go straight to VM list

```sql
-- move_101_103_direct.sql
-- CALLBK leads skip 102 and go directly to 103
-- Run every 15 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
AND v.status = 'CALLBK'
AND v.call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 101, 103, 'CALLBK direct to voicemail', brain_id
FROM leads_to_move_101_103;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_103;
COMMIT;
```

### 4. **move_103_104.sql** - VM to Hot Phase
**Trigger:** After LVM status set in List 103
**Timing:** Every 15 minutes
**Logic:** Move when voicemail is left

```sql
-- move_103_104.sql
-- After voicemail left, move to hot phase
-- Run every 15 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_103_104 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 103
AND v.status IN ('LVM', 'AL', 'AM')
AND v.call_date >= vl.list_entry_date
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 104
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 103, 104, 'Voicemail left - start hot phase', brain_id
FROM leads_to_move_103_104;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_103_104 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 104,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_103_104;
COMMIT;
```

### 5. **move_104_105.sql** - Hot to Extended
**Trigger:** After 3 workdays in List 104
**Timing:** Daily at 12:01 AM
**Logic:** Move leads that have been in hot phase for 3 workdays

```sql
-- move_104_105.sql
-- After 3 workdays of hot calling, move to extended
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_104_105 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 104
AND (
    SELECT COUNT(DISTINCT DATE(cl.call_date))
    FROM vicidial_log cl
    WHERE cl.lead_id = vl.lead_id
    AND cl.call_date >= vl.list_entry_date
    AND DAYOFWEEK(cl.call_date) NOT IN (1, 7)
) >= 3;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 104, 105, 'Completed 3 days hot phase', brain_id
FROM leads_to_move_104_105;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_104_105 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 105,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_104_105;
COMMIT;
```

### 6. **move_105_106.sql** - Extended to Secondary
**Trigger:** After 7 workdays in List 105
**Timing:** Daily at 12:01 AM

```sql
-- move_105_106.sql
-- After 7 workdays extended, move to secondary
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_105_106 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 105
AND DATEDIFF(NOW(), vl.list_entry_date) >= 7
AND (
    SELECT COUNT(DISTINCT DATE(cl.call_date))
    FROM vicidial_log cl
    WHERE cl.lead_id = vl.lead_id
    AND cl.call_date >= vl.list_entry_date
    AND DAYOFWEEK(cl.call_date) NOT IN (1, 7)
) >= 7;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 105, 106, 'Completed 7 days extended', brain_id
FROM leads_to_move_105_106;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_105_106 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 106,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_105_106;
COMMIT;
```

### 7. **move_106_107.sql** - Secondary to Cool Down 1
**Trigger:** After 5 workdays in List 106
**Timing:** Daily at 12:01 AM

```sql
-- move_106_107.sql
-- After 5 workdays secondary, move to cool down
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_106_107 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 106
AND DATEDIFF(NOW(), vl.list_entry_date) >= 5;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 106, 107, 'Move to cool down phase', brain_id
FROM leads_to_move_106_107;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_106_107 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 107,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_106_107;
COMMIT;
```

### 8. **move_107_108.sql** - Cool Down to Rest
**Trigger:** After 5 workdays in List 107
**Timing:** Daily at 12:01 AM

```sql
-- move_107_108.sql
-- After 5 workdays cool down, move to rest period
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_107_108 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 107
AND DATEDIFF(NOW(), vl.list_entry_date) >= 5;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 107, 108, 'Enter rest period - NO CALLS', brain_id
FROM leads_to_move_107_108;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_107_108 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 108,
    vl.status = 'REST',  -- Special status for rest period
    vl.called_since_last_reset = 'Y',  -- Prevent calling
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_107_108;
COMMIT;
```

### 9. **move_108_109.sql** - Rest to Final Attempt
**Trigger:** After 7 days rest OR if <7 days until TCPA expiry
**Timing:** Daily at 12:01 AM

```sql
-- move_108_109.sql
-- After 7 days rest, move to final attempts
-- OR skip if TCPA expiring soon
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_108_109 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id,
    CASE 
        WHEN DATEDIFF(DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 7 
        THEN 'TCPA expiring - skip to final'
        ELSE 'Completed rest period'
    END as reason
FROM vicidial_list vl
WHERE vl.list_id = 108
AND (
    -- Normal progression after 7 days
    DATEDIFF(NOW(), vl.list_entry_date) >= 7
    OR
    -- Skip if TCPA expiring within 7 days
    DATEDIFF(DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 7
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 108, 109, reason, brain_id
FROM leads_to_move_108_109;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_108_109 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 109,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_108_109;
COMMIT;
```

### 10. **move_109_110.sql** - Final to Archive
**Trigger:** After 5 workdays OR TCPA expiry
**Timing:** Daily at 12:01 AM

```sql
-- move_109_110.sql
-- After 5 days final attempts OR TCPA expiry
-- Run daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_109_110 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id,
    CASE 
        WHEN DATEDIFF(DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 0 
        THEN 'TCPA expired'
        ELSE 'Completed final attempts'
    END as reason
FROM vicidial_list vl
WHERE vl.list_id = 109
AND (
    -- Normal progression after 5 days
    DATEDIFF(NOW(), vl.list_entry_date) >= 5
    OR
    -- TCPA expired
    DATEDIFF(DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 0
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 109, 110, reason, brain_id
FROM leads_to_move_109_110;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_109_110 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'ARCHIVE',
    vl.called_since_last_reset = 'Y',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_109_110;
COMMIT;
```

### 11. **tcpa_30day_compliance.sql** - Direct to Archive
**Trigger:** Any list where TCPA is expired
**Timing:** Daily at 1:00 AM

```sql
-- tcpa_30day_compliance.sql
-- Move ANY expired TCPA leads directly to archive
-- Run daily at 1:00 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired AS
SELECT DISTINCT 
    vl.lead_id,
    vl.list_id as from_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id BETWEEN 101 AND 109
AND vl.tcpajoin_date IS NOT NULL
AND DATEDIFF(CURDATE(), vl.tcpajoin_date) >= 30;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, 110, 'TCPA 30-day expiry', brain_id
FROM tcpa_expired;

UPDATE vicidial_list vl
INNER JOIN tcpa_expired te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPAEXP',
    vl.called_since_last_reset = 'Y',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS tcpa_expired;
COMMIT;
```

## ⏰ Cron Schedule

```bash
# Fast movements (every 5 minutes) - Speed to lead critical
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103.sql

# Regular movements (every 15 minutes)
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_direct.sql
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104.sql

# Daily movements (12:01 AM)
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_109.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_109_110.sql

# TCPA compliance (1:00 AM daily)
0 1 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_30day_compliance.sql
```

## 📊 Movement Logic Summary

### How Each Movement Works:

1. **101→102**: Immediate after first call (except CALLBK)
2. **102→103**: After 20-minute hold time
3. **101→103**: Direct route for CALLBK status
4. **103→104**: After voicemail is left (LVM status)
5. **104→105**: After 3 workdays of hot calling
6. **105→106**: After 7 workdays of extended calling
7. **106→107**: After 5 workdays of secondary calling
8. **107→108**: After 5 workdays of cool down calling
9. **108→109**: After 7 days rest OR TCPA <7 days
10. **109→110**: After 5 workdays OR TCPA expired
11. **Any→110**: Direct archive if TCPA expired

## 💡 30-Day Callback Strategy Analysis

### Your Question: "What do you think of calling them back 30 days after last call?"

**HIGHLY EFFECTIVE!** Here's why:

### Benefits of 30-Day Callback:
1. **Psychology Reset**: After 30 days, leads often forget previous attempts
2. **Situation Change**: 15-20% of people's insurance needs change monthly
3. **Renewal Timing**: Many policies renew every 6 months, you might catch them at renewal
4. **Fresh Start**: Can approach as "checking back in" rather than "following up"

### Recommended Implementation:

#### Create List 111: 30-Day Resurrection
```sql
-- move_110_111_resurrection.sql
-- Move leads from archive back to calling after 30 days rest
-- Run daily at 2:00 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS resurrection_leads AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
LEFT JOIN lead_moves lm ON vl.lead_id = lm.lead_id AND lm.to_list_id = 111
WHERE vl.list_id = 110
AND vl.status NOT IN ('DNC', 'DNCL', 'XFER', 'XFERA')
AND DATEDIFF(NOW(), vl.list_entry_date) >= 30
AND lm.lead_id IS NULL  -- Never been resurrected
AND vl.tcpajoin_date IS NOT NULL
AND DATEDIFF(CURDATE(), vl.tcpajoin_date) < 60;  -- Still within 60-day window

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 110, 111, '30-day resurrection attempt', brain_id
FROM resurrection_leads;

UPDATE vicidial_list vl
INNER JOIN resurrection_leads rl ON vl.lead_id = rl.lead_id
SET 
    vl.list_id = 111,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW(),
    vl.comments = CONCAT('RESURRECTION: ', DATE_FORMAT(NOW(), '%Y-%m-%d'));

DROP TEMPORARY TABLE IF EXISTS resurrection_leads;
COMMIT;
```

### List 111 Configuration:
- **Calls/Day**: 1 (gentle re-engagement)
- **Duration**: 3 days
- **Script**: "Hi [Name], I'm calling back about the auto insurance quote you requested last month. Rates have changed and I wanted to see if you're still looking to save money?"

### Success Metrics:
- **Industry Average**: 8-12% contact rate on 30-day callbacks
- **Conversion**: 3-5% of callbacks result in transfers
- **ROI**: Very high - these are essentially free leads

### Alternative: 60-Day Callback
If 30 days is too soon, consider 60-day callback:
- Aligns with bi-monthly insurance shopping patterns
- Complete psychological reset
- Still within reasonable TCPA window (if original consent was strong)

## 📋 Implementation Checklist

- [ ] Deploy all SQL scripts to `/opt/vici_scripts/`
- [ ] Set up cron jobs with correct timing
- [ ] Configure List 108 as non-dialable (REST status)
- [ ] Set up agent alerts for List 103 (voicemail)
- [ ] Configure reset times in Vici for each list
- [ ] Test movement logic with sample leads
- [ ] Monitor `lead_moves` table for tracking
- [ ] Consider implementing List 111 for 30-day callbacks

## 🎯 Key Success Factors

1. **Speed to Lead**: 101→102→103 happens within first hour
2. **Rest Period**: List 108 creates psychological reset
3. **TCPA Compliance**: Automatic archiving at 30 days
4. **Workday Logic**: Respects business hours
5. **Resurrection Option**: 30-day callback for maximum ROI
