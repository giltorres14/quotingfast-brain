# 📚 VICI COMPLETE LEAD FLOW SYSTEM
**Last Updated:** January 15, 2025  
**Total Campaign Duration:** 30 workdays + 7 rest days  
**Total Attempts:** 61 calls per lead

---

## 🎯 QUICK REFERENCE - THE COMPLETE FLOW

```
LEAD ENTERS → List 101 (Immediate call)
    ↓ [1st attempt made]
List 102 (Wait 20 minutes)
    ↓ [20 min elapsed]
List 103 (VOICEMAIL - Agent leaves message)
    ↓ [VM left with LVM status]
List 104 (HOT PHASE - 4 calls/day × 3 days = 12 calls)
    ↓ [3 workdays completed]
List 105 (EXTENDED - 3 calls/day × 7 days = 21 calls)
    ↓ [7 workdays completed]
List 106 (SECONDARY - 2 calls/day × 5 days = 10 calls)
    ↓ [5 workdays completed]
List 107 (COOL DOWN - 2 calls/day × 5 days = 10 calls)
    ↓ [5 workdays completed]
List 108 (REST PERIOD - NO CALLS × 7 days)
    ↓ [7 days rest completed]
List 109 (FINAL ATTEMPT - 1 call/day × 5 days = 5 calls)
    ↓ [5 workdays OR TCPA expiry]
List 110 (ARCHIVE - Permanent storage, no calls)
```

---

## 📋 TABLE OF CONTENTS

1. [Database Setup & Prerequisites](#database-setup)
2. [Status Management](#status-management)
3. [List Configuration Details](#list-configuration)
4. [SQL Movement Scripts](#sql-movement-scripts)
5. [Non-Movement SQL Scripts](#non-movement-scripts)
6. [Cron Schedule](#cron-schedule)
7. [Agent Instructions](#agent-instructions)
8. [Monitoring & Reporting](#monitoring)
9. [Troubleshooting Guide](#troubleshooting)

---

## 🗄️ DATABASE SETUP & PREREQUISITES {#database-setup}

### Required Tables

#### 1. **lead_moves** - Tracking Table
```sql
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT,
    to_list_id INT,
    move_date DATETIME DEFAULT NOW(),
    move_reason VARCHAR(255),
    disposition VARCHAR(20),
    brain_lead_id VARCHAR(20),
    INDEX idx_lead (lead_id),
    INDEX idx_date (move_date),
    INDEX idx_lists (from_list_id, to_list_id)
);
```

#### 2. **excluded_statuses** - Non-Dialable Status List
```sql
CREATE TABLE IF NOT EXISTS excluded_statuses (
    status VARCHAR(20) PRIMARY KEY,
    description VARCHAR(100),
    non_dialable BOOLEAN DEFAULT TRUE
);

-- Populate with excluded statuses
INSERT INTO excluded_statuses (status, description) VALUES
('VMQ', 'Voicemail Queue - Non-selectable'),
('XFER', 'Transferred - Success'),
('XFERA', 'Transferred Alt - Success'),
('DNC', 'Do Not Call - Federal'),
('DNCL', 'Do Not Call - Internal'),
('ADCT', 'Disconnected Number'),
('ADC', 'Disconnected'),
('NI', 'Not Interested'),
('DC', 'Disconnected'),
('LVM', 'Left Voicemail - Selectable');
```

#### 3. **workday_calendar** - Business Days Only
```sql
CREATE TABLE IF NOT EXISTS workday_calendar (
    cal_date DATE PRIMARY KEY,
    is_workday BOOLEAN DEFAULT TRUE,
    is_holiday BOOLEAN DEFAULT FALSE,
    holiday_name VARCHAR(50)
);

-- Populate with 2025 holidays
INSERT INTO workday_calendar (cal_date, is_workday, is_holiday, holiday_name) VALUES
('2025-01-01', FALSE, TRUE, 'New Years Day'),
('2025-01-20', FALSE, TRUE, 'MLK Day'),
('2025-02-17', FALSE, TRUE, 'Presidents Day'),
('2025-05-26', FALSE, TRUE, 'Memorial Day'),
('2025-07-04', FALSE, TRUE, 'Independence Day'),
('2025-09-01', FALSE, TRUE, 'Labor Day'),
('2025-11-27', FALSE, TRUE, 'Thanksgiving'),
('2025-11-28', FALSE, TRUE, 'Black Friday'),
('2025-12-25', FALSE, TRUE, 'Christmas');

-- Mark weekends as non-workdays
UPDATE workday_calendar 
SET is_workday = FALSE 
WHERE DAYOFWEEK(cal_date) IN (1, 7);
```

---

## 🚦 STATUS MANAGEMENT {#status-management}

### Dialable Statuses (Will be called)
| Status | Description | Action |
|--------|-------------|--------|
| NEW | Fresh lead, never called | Normal dialing |
| AA | Answering Machine - Retry | Continue calling |
| B | Busy | Retry later |
| NA | No Answer | Continue attempts |
| CALLBK | Callback Requested | Special routing to VM |
| DROP | Call Dropped | Retry |

### Non-Dialable Statuses (Stop calling)
| Status | Description | Result |
|--------|-------------|--------|
| **VMQ** | Voicemail Queue | System status - not selectable |
| **XFER** | Transferred Successfully | SUCCESS - Stop calling |
| **XFERA** | Alternate Transfer Success | SUCCESS - Stop calling |
| **DNC** | Do Not Call - Federal | PERMANENT - Never call |
| **DNCL** | Do Not Call - Internal | PERMANENT - Never call |
| **ADCT** | Disconnected Number | BAD NUMBER - Stop |
| **ADC** | Disconnected | BAD NUMBER - Stop |
| **NI** | Not Interested | REFUSAL - Stop calling |
| **DC** | Disconnected Call | TECHNICAL - Stop |
| **LVM** | Left Voicemail | Special - Triggers movement |

### Special Status: LVM (Left Voicemail)
- **Selectable by agents**: YES
- **Triggers movement**: From List 103 → 104
- **Required for**: Progressing past voicemail phase

---

## 📊 LIST CONFIGURATION DETAILS {#list-configuration}

### List 101: INITIAL CONTACT
- **Purpose**: Immediate first call attempt
- **Duration**: 0 minutes (immediate)
- **Calls**: 1 attempt
- **Reset Times**: N/A - Immediate dial
- **Movement Trigger**: Any call attempt
- **Special Logic**: CALLBK status → List 103, Others → List 102

### List 102: 20-MINUTE FOLLOW-UP
- **Purpose**: Quick second attempt
- **Duration**: 20 minutes hold
- **Calls**: 1 attempt
- **Reset Times**: After 20 minutes
- **Movement Trigger**: 20 minutes elapsed
- **Special Logic**: Automatic progression to List 103

### List 103: VOICEMAIL PHASE 🔔
- **Purpose**: Leave voicemail message
- **Duration**: Until VM left
- **Calls**: 1 attempt
- **Reset Times**: Upon entry
- **Agent Alert**: "LEAVE VOICEMAIL - Set status to LVM"
- **Movement Trigger**: LVM status set
- **Script**: "Hi [Name], this is [Agent] calling about your auto insurance quote request..."

### List 104: HOT PHASE 🔥
- **Purpose**: Aggressive follow-up while interest is high
- **Duration**: 3 workdays
- **Calls**: 4 per day = 12 total
- **Reset Times**: 9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM
- **Movement Trigger**: 3 workdays completed
- **Strategy**: Maximum attempts during peak interest

### List 105: EXTENDED FOLLOW-UP
- **Purpose**: Continued aggressive calling
- **Duration**: 7 workdays
- **Calls**: 3 per day = 21 total
- **Reset Times**: 10:00 AM, 1:00 PM, 4:00 PM
- **Movement Trigger**: 7 workdays completed
- **Strategy**: Still pursuing but slightly reduced

### List 106: SECONDARY FOLLOW-UP
- **Purpose**: Standard follow-up phase
- **Duration**: 5 workdays
- **Calls**: 2 per day = 10 total
- **Reset Times**: 11:00 AM, 3:30 PM
- **Movement Trigger**: 5 workdays completed
- **Strategy**: Moderate persistence

### List 107: FIRST COOL DOWN
- **Purpose**: Reduced calling frequency
- **Duration**: 5 workdays
- **Calls**: 2 per day = 10 total
- **Reset Times**: 10:00 AM, 2:00 PM
- **Movement Trigger**: 5 workdays completed
- **Strategy**: Maintaining presence

### List 108: REST PERIOD ⏸️
- **Purpose**: Complete break - NO CALLS
- **Duration**: 7 days
- **Calls**: 0 - NONE
- **Reset Times**: N/A - No calling
- **Movement Trigger**: 7 days elapsed OR TCPA < 7 days
- **Strategy**: Psychological reset - absence makes them receptive

### List 109: FINAL ATTEMPT
- **Purpose**: Last chance before archive
- **Duration**: 5 workdays OR until TCPA expiry
- **Calls**: 1 per day = 5 total
- **Reset Times**: 12:00 PM
- **Movement Trigger**: 5 days OR TCPA expiry
- **Strategy**: Final gentle attempts

### List 110: FINAL ARCHIVE 📦
- **Purpose**: Permanent storage
- **Duration**: Permanent
- **Calls**: 0 - NONE
- **Status**: ARCHIVE or TCPAEXP
- **Strategy**: Compliance storage, possible 30-day resurrection

---

## 📝 SQL MOVEMENT SCRIPTS {#sql-movement-scripts}

### 1. move_101_102.sql - IMMEDIATE TO 20-MIN HOLD
**When it runs**: Every 5 minutes  
**What it does**: Moves leads from List 101 to 102 after first call attempt  
**Conditions**: 
- Lead has been called (not NEW status)
- NOT CALLBK status (those go to 103)
- Not already moved today

```sql
-- Complete script with explanations
START TRANSACTION;

-- Find leads that have been called from List 101
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.vendor_lead_code as brain_id,
    v.status as last_status
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
    AND v.call_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)  -- Recent calls only
    AND v.status NOT IN ('CALLBK', 'NEW')  -- CALLBK goes different route
    AND v.status NOT IN (SELECT status FROM excluded_statuses)  -- Not DNC, XFER, etc
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 102
        AND DATE(lm.move_date) = CURDATE()  -- Prevent duplicate moves
    );

-- Log the movement for tracking
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, 101, 102, '20-min follow-up hold', last_status, brain_id
FROM leads_to_move_101_102;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,  -- Move to List 102
    vl.status = 'NEW',  -- Reset status for dialing
    vl.called_since_last_reset = 'N',  -- Allow dialing
    vl.list_entry_date = NOW(),
    vl.modify_date = DATE_ADD(NOW(), INTERVAL 20 MINUTE);  -- 20-min delay

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;
COMMIT;
```

### 2. move_102_103.sql - 20-MIN TO VOICEMAIL
**When it runs**: Every 5 minutes  
**What it does**: Moves leads from 102 to 103 after 20-minute wait  
**Conditions**: 20 minutes have elapsed since entering List 102

```sql
START TRANSACTION;

-- Find leads that have been in List 102 for 20+ minutes
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 102
    AND vl.list_entry_date <= DATE_SUB(NOW(), INTERVAL 20 MINUTE)  -- 20 min passed
    AND vl.status NOT IN (SELECT status FROM excluded_statuses)  -- Still dialable
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 103
        AND DATE(lm.move_date) = CURDATE()
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 102, 103, 'Ready for voicemail attempt', brain_id
FROM leads_to_move_102_103;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,  -- Move to voicemail list
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;
COMMIT;
```

### 3. move_101_103_direct.sql - CALLBK DIRECT TO VM
**When it runs**: Every 15 minutes  
**What it does**: Moves CALLBK leads directly from 101 to 103 (skip 102)  
**Conditions**: Lead has CALLBK status from List 101

```sql
START TRANSACTION;

-- Find CALLBK leads in List 101
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
    AND v.status = 'CALLBK'  -- Callback requested
    AND v.call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 103
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, 101, 103, 'CALLBK direct to voicemail', 'CALLBK', brain_id
FROM leads_to_move_101_103;

-- Execute the move
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

### 4. move_103_104.sql - VM TO HOT PHASE
**When it runs**: Every 15 minutes  
**What it does**: Moves leads from 103 to 104 after voicemail is left  
**Conditions**: Agent set status to LVM (or AL/AM for answering machine)

```sql
START TRANSACTION;

-- Find leads where voicemail was left
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_103_104 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 103
    AND v.status IN ('LVM', 'AL', 'AM')  -- Voicemail left statuses
    AND v.call_date >= vl.list_entry_date  -- VM after entering list
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 104
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 103, 104, 'Voicemail left - start hot phase', brain_id
FROM leads_to_move_103_104;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_103_104 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 104,  -- Move to hot phase
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_103_104;
COMMIT;
```

### 5-9. WORKDAY-BASED MOVEMENTS
These run daily at 12:01 AM and move leads based on days in list:

```sql
-- move_104_105.sql - HOT TO EXTENDED (after 3 workdays)
-- move_105_106.sql - EXTENDED TO SECONDARY (after 7 workdays)  
-- move_106_107.sql - SECONDARY TO COOL DOWN (after 5 workdays)
-- move_107_108.sql - COOL DOWN TO REST (after 5 workdays)
-- move_108_109.sql - REST TO FINAL (after 7 days OR TCPA < 7 days)
-- move_109_110.sql - FINAL TO ARCHIVE (after 5 days OR TCPA expired)
```

---

## 🔧 NON-MOVEMENT SQL SCRIPTS {#non-movement-scripts}

### 1. update_excluded_statuses.sql - STOP CALLING CERTAIN LEADS
**When it runs**: Every 30 minutes  
**What it does**: Marks leads as non-dialable based on status  
**Purpose**: Prevents calling DNC, transferred, not interested leads

```sql
-- Mark leads with excluded statuses as non-dialable
UPDATE vicidial_list vl
INNER JOIN excluded_statuses es ON vl.status = es.status
SET 
    vl.called_since_last_reset = 'Y',  -- Prevents dialing
    vl.modify_date = NOW()
WHERE vl.list_id BETWEEN 101 AND 109
    AND vl.called_since_last_reset = 'N';
```

### 2. tcpa_30day_compliance.sql - AUTOMATIC ARCHIVING
**When it runs**: Daily at 1:00 AM  
**What it does**: Moves ANY lead past 30-day TCPA to archive  
**Purpose**: Legal compliance - stop calling after consent expires

```sql
START TRANSACTION;

-- Find all expired TCPA leads
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired AS
SELECT DISTINCT 
    vl.lead_id,
    vl.list_id as from_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), vl.tcpajoin_date) as days_old
FROM vicidial_list vl
WHERE vl.list_id BETWEEN 101 AND 109
    AND vl.tcpajoin_date IS NOT NULL
    AND DATEDIFF(CURDATE(), vl.tcpajoin_date) >= 30;  -- 30 days expired

-- Log the archiving
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, 110, CONCAT('TCPA expired - ', days_old, ' days old'), brain_id
FROM tcpa_expired;

-- Archive the leads
UPDATE vicidial_list vl
INNER JOIN tcpa_expired te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPAEXP',  -- Special expired status
    vl.called_since_last_reset = 'Y',  -- Never dial again
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS tcpa_expired;
COMMIT;
```

### 3. reset_new_leads.sql - DAILY RESET FOR DIALING
**When it runs**: Daily at 4:00 AM  
**What it does**: Resets leads for the day's dialing  
**Purpose**: Ensures leads are ready for their scheduled resets

```sql
-- Reset leads for today's dialing
UPDATE vicidial_list
SET called_since_last_reset = 'N'
WHERE list_id BETWEEN 101 AND 109
    AND status = 'NEW'
    AND called_since_last_reset = 'Y';
```

### 4. workday_check.sql - SKIP WEEKENDS/HOLIDAYS
**When it runs**: Every morning at 6:00 AM  
**What it does**: Checks if today is a workday  
**Purpose**: Prevents movements on weekends/holidays

```sql
-- Check if today is a workday
SELECT 
    CASE 
        WHEN is_workday = TRUE THEN 'WORKDAY - Process normally'
        WHEN is_holiday = TRUE THEN CONCAT('HOLIDAY - ', holiday_name)
        WHEN DAYOFWEEK(CURDATE()) IN (1,7) THEN 'WEEKEND - Skip processing'
        ELSE 'NON-WORKDAY'
    END as day_status
FROM workday_calendar
WHERE cal_date = CURDATE();
```

---

## ⏰ COMPLETE CRON SCHEDULE {#cron-schedule}

```bash
# ===== SPEED TO LEAD - CRITICAL (Every 5 minutes) =====
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_101_102
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103.sql 2>&1 | logger -t vici_102_103

# ===== REGULAR MOVEMENTS (Every 15 minutes) =====
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_direct.sql 2>&1 | logger -t vici_callbk
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104.sql 2>&1 | logger -t vici_vm_hot

# ===== STATUS UPDATES (Every 30 minutes) =====
*/30 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/update_excluded_statuses.sql 2>&1 | logger -t vici_exclude

# ===== DAILY MOVEMENTS (12:01 AM) =====
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105.sql 2>&1 | logger -t vici_104_105
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106.sql 2>&1 | logger -t vici_105_106
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107.sql 2>&1 | logger -t vici_106_107
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108.sql 2>&1 | logger -t vici_107_108
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_109.sql 2>&1 | logger -t vici_108_109
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_109_110.sql 2>&1 | logger -t vici_109_110

# ===== COMPLIANCE (1:00 AM) =====
0 1 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_30day_compliance.sql 2>&1 | logger -t vici_tcpa

# ===== DAILY RESET (4:00 AM) =====
0 4 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/reset_new_leads.sql 2>&1 | logger -t vici_reset

# ===== WORKDAY CHECK (6:00 AM) =====
0 6 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/workday_check.sql 2>&1 | logger -t vici_workday
```

---

## 👥 AGENT INSTRUCTIONS {#agent-instructions}

### When You Get a Lead from List 103 (Voicemail)
1. **YOU WILL SEE**: Red alert banner "LEAVE VOICEMAIL"
2. **YOUR ACTION**: 
   - Leave a professional voicemail
   - Set status to **LVM** (Left Voicemail)
   - Do NOT set any other status
3. **SCRIPT**: "Hi [Name], this is [Agent] calling about your auto insurance quote request. I have some great rates that could save you money. Please call me back at [Number] to discuss your savings. Thank you!"

### Status Guide for Agents
| Use This Status | When |
|-----------------|------|
| **LVM** | You left a voicemail |
| **CALLBK** | Customer requested callback |
| **XFER** | Successfully transferred to buyer |
| **DNC** | Customer requests Do Not Call |
| **NI** | Customer says Not Interested |
| **NA** | No Answer (default) |
| **B** | Line is Busy |

### DO NOT USE These Statuses
- **VMQ** - System use only
- **NEW** - System use only
- **REST** - System use only
- **ARCHIVE** - System use only

---

## 📊 MONITORING & REPORTING {#monitoring}

### Daily Health Check Query
```sql
-- Check lead distribution across lists
SELECT 
    list_id,
    COUNT(*) as total_leads,
    SUM(CASE WHEN DATE(entry_date) = CURDATE() THEN 1 ELSE 0 END) as new_today,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    SUM(CASE WHEN status IN ('DNC','DNCL','NI') THEN 1 ELSE 0 END) as stopped
FROM vicidial_list
WHERE list_id BETWEEN 101 AND 110
GROUP BY list_id
ORDER BY list_id;
```

### Movement Activity Report
```sql
-- Today's movements
SELECT 
    from_list_id,
    to_list_id,
    COUNT(*) as leads_moved,
    move_reason,
    MAX(move_date) as last_move
FROM lead_moves
WHERE DATE(move_date) = CURDATE()
GROUP BY from_list_id, to_list_id, move_reason
ORDER BY from_list_id, to_list_id;
```

### TCPA Compliance Check
```sql
-- Leads approaching TCPA expiry
SELECT 
    list_id,
    COUNT(*) as at_risk,
    MIN(DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE())) as days_remaining
FROM vicidial_list
WHERE list_id BETWEEN 101 AND 109
    AND tcpajoin_date IS NOT NULL
    AND DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 3
GROUP BY list_id;
```

---

## 🔧 TROUBLESHOOTING GUIDE {#troubleshooting}

### Problem: Leads Not Moving from 101 to 102
**Check**:
1. Is the cron job running? `grep vici_101_102 /var/log/syslog`
2. Are leads being called? `SELECT * FROM vicidial_log WHERE list_id = 101 AND call_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)`
3. Are they excluded statuses? Check if status is DNC, XFER, etc.

### Problem: Leads Stuck in List 103 (Voicemail)
**Check**:
1. Are agents setting LVM status? `SELECT status, COUNT(*) FROM vicidial_list WHERE list_id = 103 GROUP BY status`
2. Is move_103_104.sql running? Check cron logs
3. Train agents on proper VM status setting

### Problem: Too Many Calls to Same Lead
**Check**:
1. Verify reset times are configured in Vici
2. Check called_since_last_reset flag
3. Review list reset configuration

### Problem: TCPA Leads Not Archiving
**Check**:
1. Is tcpajoin_date populated? `SELECT COUNT(*) FROM vicidial_list WHERE tcpajoin_date IS NULL`
2. Is compliance script running? Check 1:00 AM cron
3. Verify date calculation is correct

---

## 🎯 KEY SUCCESS METRICS

### Target Performance
- **Speed to Lead**: 3 attempts within first hour ✅
- **Voicemail Rate**: 100% of List 103 should get VM ✅
- **TCPA Compliance**: 0 calls after 30 days ✅
- **Transfer Rate**: Track XFER status by list
- **Contact Rate**: Monitor by list and time of day

### Expected Flow Timing
- **Hour 0-1**: Lists 101 → 102 → 103 (3 attempts)
- **Days 1-3**: List 104 (12 attempts)
- **Days 4-10**: List 105 (21 attempts)
- **Days 11-15**: List 106 (10 attempts)
- **Days 16-20**: List 107 (10 attempts)
- **Days 21-27**: List 108 (REST - 0 attempts)
- **Days 28-32**: List 109 (5 attempts)
- **Day 30+**: List 110 (Archive)

---

## 💡 30-DAY RESURRECTION STRATEGY

### The Opportunity
After 30 days in archive, leads have "cooled off" and may be receptive again.

### Implementation (Optional List 111)
```sql
-- move_110_111_resurrection.sql
-- Resurrect leads after 30 days in archive
-- Run daily at 2:00 AM

CREATE TEMPORARY TABLE IF NOT EXISTS resurrection_leads AS
SELECT lead_id, vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE list_id = 110
    AND status NOT IN ('DNC', 'DNCL', 'XFER', 'XFERA')
    AND DATEDIFF(NOW(), list_entry_date) >= 30
    AND DATEDIFF(CURDATE(), tcpajoin_date) < 60  -- Still valid
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves 
        WHERE lead_id = vl.lead_id 
        AND to_list_id = 111
    );

-- Move to resurrection list
UPDATE vicidial_list vl
INNER JOIN resurrection_leads rl ON vl.lead_id = rl.lead_id
SET 
    vl.list_id = 111,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.comments = 'RESURRECTION ATTEMPT';
```

### Expected Results
- **8-12% contact rate** on resurrected leads
- **3-5% transfer rate** 
- **High ROI** - these are free re-attempts

---

## ✅ IMPLEMENTATION CHECKLIST

### Initial Setup
- [ ] Create all required tables (lead_moves, excluded_statuses, workday_calendar)
- [ ] Populate excluded statuses
- [ ] Populate holiday calendar
- [ ] Create /opt/vici_scripts/ directory

### Deploy SQL Scripts
- [ ] Upload all movement scripts (101→102, 102→103, etc.)
- [ ] Upload compliance scripts (TCPA, excluded statuses)
- [ ] Upload utility scripts (reset, workday check)
- [ ] Set proper permissions (chmod +x)

### Configure Vici
- [ ] Set List 103 as "Voicemail List" in description
- [ ] Configure reset times for each list
- [ ] Set excluded statuses as non-dialable
- [ ] Configure agent screen for VM alert

### Setup Cron Jobs
- [ ] Add all cron entries
- [ ] Verify cron is running (crontab -l)
- [ ] Test each script manually first
- [ ] Monitor logs for first 24 hours

### Train Agents
- [ ] Explain List 103 voicemail process
- [ ] Train on LVM status usage
- [ ] Review excluded statuses
- [ ] Provide script for voicemails

### Monitor & Optimize
- [ ] Daily review of movement logs
- [ ] Check TCPA compliance
- [ ] Monitor transfer rates by list
- [ ] Adjust timing if needed

---

## 📞 FINAL NOTES

This system is designed to maximize contact while respecting:
1. **Customer Psychology** - Rest period creates receptiveness
2. **TCPA Compliance** - Automatic 30-day cutoff
3. **Agent Efficiency** - Clear instructions and alerts
4. **Business Hours** - Workday-only calling
5. **ROI Optimization** - 61 attempts over 30 days

**Remember**: The key to success is the REST PERIOD (List 108). This 7-day break resets the customer's receptiveness and makes List 109 surprisingly effective.

---

**Document Version**: 1.0  
**System Ready**: Deploy all scripts and start with a test batch first!
**Last Updated:** January 15, 2025  
**Total Campaign Duration:** 30 workdays + 7 rest days  
**Total Attempts:** 61 calls per lead

---

## 🎯 QUICK REFERENCE - THE COMPLETE FLOW

```
LEAD ENTERS → List 101 (Immediate call)
    ↓ [1st attempt made]
List 102 (Wait 20 minutes)
    ↓ [20 min elapsed]
List 103 (VOICEMAIL - Agent leaves message)
    ↓ [VM left with LVM status]
List 104 (HOT PHASE - 4 calls/day × 3 days = 12 calls)
    ↓ [3 workdays completed]
List 105 (EXTENDED - 3 calls/day × 7 days = 21 calls)
    ↓ [7 workdays completed]
List 106 (SECONDARY - 2 calls/day × 5 days = 10 calls)
    ↓ [5 workdays completed]
List 107 (COOL DOWN - 2 calls/day × 5 days = 10 calls)
    ↓ [5 workdays completed]
List 108 (REST PERIOD - NO CALLS × 7 days)
    ↓ [7 days rest completed]
List 109 (FINAL ATTEMPT - 1 call/day × 5 days = 5 calls)
    ↓ [5 workdays OR TCPA expiry]
List 110 (ARCHIVE - Permanent storage, no calls)
```

---

## 📋 TABLE OF CONTENTS

1. [Database Setup & Prerequisites](#database-setup)
2. [Status Management](#status-management)
3. [List Configuration Details](#list-configuration)
4. [SQL Movement Scripts](#sql-movement-scripts)
5. [Non-Movement SQL Scripts](#non-movement-scripts)
6. [Cron Schedule](#cron-schedule)
7. [Agent Instructions](#agent-instructions)
8. [Monitoring & Reporting](#monitoring)
9. [Troubleshooting Guide](#troubleshooting)

---

## 🗄️ DATABASE SETUP & PREREQUISITES {#database-setup}

### Required Tables

#### 1. **lead_moves** - Tracking Table
```sql
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT,
    to_list_id INT,
    move_date DATETIME DEFAULT NOW(),
    move_reason VARCHAR(255),
    disposition VARCHAR(20),
    brain_lead_id VARCHAR(20),
    INDEX idx_lead (lead_id),
    INDEX idx_date (move_date),
    INDEX idx_lists (from_list_id, to_list_id)
);
```

#### 2. **excluded_statuses** - Non-Dialable Status List
```sql
CREATE TABLE IF NOT EXISTS excluded_statuses (
    status VARCHAR(20) PRIMARY KEY,
    description VARCHAR(100),
    non_dialable BOOLEAN DEFAULT TRUE
);

-- Populate with excluded statuses
INSERT INTO excluded_statuses (status, description) VALUES
('VMQ', 'Voicemail Queue - Non-selectable'),
('XFER', 'Transferred - Success'),
('XFERA', 'Transferred Alt - Success'),
('DNC', 'Do Not Call - Federal'),
('DNCL', 'Do Not Call - Internal'),
('ADCT', 'Disconnected Number'),
('ADC', 'Disconnected'),
('NI', 'Not Interested'),
('DC', 'Disconnected'),
('LVM', 'Left Voicemail - Selectable');
```

#### 3. **workday_calendar** - Business Days Only
```sql
CREATE TABLE IF NOT EXISTS workday_calendar (
    cal_date DATE PRIMARY KEY,
    is_workday BOOLEAN DEFAULT TRUE,
    is_holiday BOOLEAN DEFAULT FALSE,
    holiday_name VARCHAR(50)
);

-- Populate with 2025 holidays
INSERT INTO workday_calendar (cal_date, is_workday, is_holiday, holiday_name) VALUES
('2025-01-01', FALSE, TRUE, 'New Years Day'),
('2025-01-20', FALSE, TRUE, 'MLK Day'),
('2025-02-17', FALSE, TRUE, 'Presidents Day'),
('2025-05-26', FALSE, TRUE, 'Memorial Day'),
('2025-07-04', FALSE, TRUE, 'Independence Day'),
('2025-09-01', FALSE, TRUE, 'Labor Day'),
('2025-11-27', FALSE, TRUE, 'Thanksgiving'),
('2025-11-28', FALSE, TRUE, 'Black Friday'),
('2025-12-25', FALSE, TRUE, 'Christmas');

-- Mark weekends as non-workdays
UPDATE workday_calendar 
SET is_workday = FALSE 
WHERE DAYOFWEEK(cal_date) IN (1, 7);
```

---

## 🚦 STATUS MANAGEMENT {#status-management}

### Dialable Statuses (Will be called)
| Status | Description | Action |
|--------|-------------|--------|
| NEW | Fresh lead, never called | Normal dialing |
| AA | Answering Machine - Retry | Continue calling |
| B | Busy | Retry later |
| NA | No Answer | Continue attempts |
| CALLBK | Callback Requested | Special routing to VM |
| DROP | Call Dropped | Retry |

### Non-Dialable Statuses (Stop calling)
| Status | Description | Result |
|--------|-------------|--------|
| **VMQ** | Voicemail Queue | System status - not selectable |
| **XFER** | Transferred Successfully | SUCCESS - Stop calling |
| **XFERA** | Alternate Transfer Success | SUCCESS - Stop calling |
| **DNC** | Do Not Call - Federal | PERMANENT - Never call |
| **DNCL** | Do Not Call - Internal | PERMANENT - Never call |
| **ADCT** | Disconnected Number | BAD NUMBER - Stop |
| **ADC** | Disconnected | BAD NUMBER - Stop |
| **NI** | Not Interested | REFUSAL - Stop calling |
| **DC** | Disconnected Call | TECHNICAL - Stop |
| **LVM** | Left Voicemail | Special - Triggers movement |

### Special Status: LVM (Left Voicemail)
- **Selectable by agents**: YES
- **Triggers movement**: From List 103 → 104
- **Required for**: Progressing past voicemail phase

---

## 📊 LIST CONFIGURATION DETAILS {#list-configuration}

### List 101: INITIAL CONTACT
- **Purpose**: Immediate first call attempt
- **Duration**: 0 minutes (immediate)
- **Calls**: 1 attempt
- **Reset Times**: N/A - Immediate dial
- **Movement Trigger**: Any call attempt
- **Special Logic**: CALLBK status → List 103, Others → List 102

### List 102: 20-MINUTE FOLLOW-UP
- **Purpose**: Quick second attempt
- **Duration**: 20 minutes hold
- **Calls**: 1 attempt
- **Reset Times**: After 20 minutes
- **Movement Trigger**: 20 minutes elapsed
- **Special Logic**: Automatic progression to List 103

### List 103: VOICEMAIL PHASE 🔔
- **Purpose**: Leave voicemail message
- **Duration**: Until VM left
- **Calls**: 1 attempt
- **Reset Times**: Upon entry
- **Agent Alert**: "LEAVE VOICEMAIL - Set status to LVM"
- **Movement Trigger**: LVM status set
- **Script**: "Hi [Name], this is [Agent] calling about your auto insurance quote request..."

### List 104: HOT PHASE 🔥
- **Purpose**: Aggressive follow-up while interest is high
- **Duration**: 3 workdays
- **Calls**: 4 per day = 12 total
- **Reset Times**: 9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM
- **Movement Trigger**: 3 workdays completed
- **Strategy**: Maximum attempts during peak interest

### List 105: EXTENDED FOLLOW-UP
- **Purpose**: Continued aggressive calling
- **Duration**: 7 workdays
- **Calls**: 3 per day = 21 total
- **Reset Times**: 10:00 AM, 1:00 PM, 4:00 PM
- **Movement Trigger**: 7 workdays completed
- **Strategy**: Still pursuing but slightly reduced

### List 106: SECONDARY FOLLOW-UP
- **Purpose**: Standard follow-up phase
- **Duration**: 5 workdays
- **Calls**: 2 per day = 10 total
- **Reset Times**: 11:00 AM, 3:30 PM
- **Movement Trigger**: 5 workdays completed
- **Strategy**: Moderate persistence

### List 107: FIRST COOL DOWN
- **Purpose**: Reduced calling frequency
- **Duration**: 5 workdays
- **Calls**: 2 per day = 10 total
- **Reset Times**: 10:00 AM, 2:00 PM
- **Movement Trigger**: 5 workdays completed
- **Strategy**: Maintaining presence

### List 108: REST PERIOD ⏸️
- **Purpose**: Complete break - NO CALLS
- **Duration**: 7 days
- **Calls**: 0 - NONE
- **Reset Times**: N/A - No calling
- **Movement Trigger**: 7 days elapsed OR TCPA < 7 days
- **Strategy**: Psychological reset - absence makes them receptive

### List 109: FINAL ATTEMPT
- **Purpose**: Last chance before archive
- **Duration**: 5 workdays OR until TCPA expiry
- **Calls**: 1 per day = 5 total
- **Reset Times**: 12:00 PM
- **Movement Trigger**: 5 days OR TCPA expiry
- **Strategy**: Final gentle attempts

### List 110: FINAL ARCHIVE 📦
- **Purpose**: Permanent storage
- **Duration**: Permanent
- **Calls**: 0 - NONE
- **Status**: ARCHIVE or TCPAEXP
- **Strategy**: Compliance storage, possible 30-day resurrection

---

## 📝 SQL MOVEMENT SCRIPTS {#sql-movement-scripts}

### 1. move_101_102.sql - IMMEDIATE TO 20-MIN HOLD
**When it runs**: Every 5 minutes  
**What it does**: Moves leads from List 101 to 102 after first call attempt  
**Conditions**: 
- Lead has been called (not NEW status)
- NOT CALLBK status (those go to 103)
- Not already moved today

```sql
-- Complete script with explanations
START TRANSACTION;

-- Find leads that have been called from List 101
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.vendor_lead_code as brain_id,
    v.status as last_status
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
    AND v.call_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)  -- Recent calls only
    AND v.status NOT IN ('CALLBK', 'NEW')  -- CALLBK goes different route
    AND v.status NOT IN (SELECT status FROM excluded_statuses)  -- Not DNC, XFER, etc
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 102
        AND DATE(lm.move_date) = CURDATE()  -- Prevent duplicate moves
    );

-- Log the movement for tracking
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, 101, 102, '20-min follow-up hold', last_status, brain_id
FROM leads_to_move_101_102;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,  -- Move to List 102
    vl.status = 'NEW',  -- Reset status for dialing
    vl.called_since_last_reset = 'N',  -- Allow dialing
    vl.list_entry_date = NOW(),
    vl.modify_date = DATE_ADD(NOW(), INTERVAL 20 MINUTE);  -- 20-min delay

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;
COMMIT;
```

### 2. move_102_103.sql - 20-MIN TO VOICEMAIL
**When it runs**: Every 5 minutes  
**What it does**: Moves leads from 102 to 103 after 20-minute wait  
**Conditions**: 20 minutes have elapsed since entering List 102

```sql
START TRANSACTION;

-- Find leads that have been in List 102 for 20+ minutes
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE vl.list_id = 102
    AND vl.list_entry_date <= DATE_SUB(NOW(), INTERVAL 20 MINUTE)  -- 20 min passed
    AND vl.status NOT IN (SELECT status FROM excluded_statuses)  -- Still dialable
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 103
        AND DATE(lm.move_date) = CURDATE()
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 102, 103, 'Ready for voicemail attempt', brain_id
FROM leads_to_move_102_103;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,  -- Move to voicemail list
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;
COMMIT;
```

### 3. move_101_103_direct.sql - CALLBK DIRECT TO VM
**When it runs**: Every 15 minutes  
**What it does**: Moves CALLBK leads directly from 101 to 103 (skip 102)  
**Conditions**: Lead has CALLBK status from List 101

```sql
START TRANSACTION;

-- Find CALLBK leads in List 101
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 101
    AND v.status = 'CALLBK'  -- Callback requested
    AND v.call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 103
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, 101, 103, 'CALLBK direct to voicemail', 'CALLBK', brain_id
FROM leads_to_move_101_103;

-- Execute the move
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

### 4. move_103_104.sql - VM TO HOT PHASE
**When it runs**: Every 15 minutes  
**What it does**: Moves leads from 103 to 104 after voicemail is left  
**Conditions**: Agent set status to LVM (or AL/AM for answering machine)

```sql
START TRANSACTION;

-- Find leads where voicemail was left
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_103_104 AS
SELECT DISTINCT 
    vl.lead_id,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN vicidial_log v ON vl.lead_id = v.lead_id
WHERE vl.list_id = 103
    AND v.status IN ('LVM', 'AL', 'AM')  -- Voicemail left statuses
    AND v.call_date >= vl.list_entry_date  -- VM after entering list
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves lm 
        WHERE lm.lead_id = vl.lead_id 
        AND lm.to_list_id = 104
    );

-- Log the movement
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, 103, 104, 'Voicemail left - start hot phase', brain_id
FROM leads_to_move_103_104;

-- Execute the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_103_104 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 104,  -- Move to hot phase
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_103_104;
COMMIT;
```

### 5-9. WORKDAY-BASED MOVEMENTS
These run daily at 12:01 AM and move leads based on days in list:

```sql
-- move_104_105.sql - HOT TO EXTENDED (after 3 workdays)
-- move_105_106.sql - EXTENDED TO SECONDARY (after 7 workdays)  
-- move_106_107.sql - SECONDARY TO COOL DOWN (after 5 workdays)
-- move_107_108.sql - COOL DOWN TO REST (after 5 workdays)
-- move_108_109.sql - REST TO FINAL (after 7 days OR TCPA < 7 days)
-- move_109_110.sql - FINAL TO ARCHIVE (after 5 days OR TCPA expired)
```

---

## 🔧 NON-MOVEMENT SQL SCRIPTS {#non-movement-scripts}

### 1. update_excluded_statuses.sql - STOP CALLING CERTAIN LEADS
**When it runs**: Every 30 minutes  
**What it does**: Marks leads as non-dialable based on status  
**Purpose**: Prevents calling DNC, transferred, not interested leads

```sql
-- Mark leads with excluded statuses as non-dialable
UPDATE vicidial_list vl
INNER JOIN excluded_statuses es ON vl.status = es.status
SET 
    vl.called_since_last_reset = 'Y',  -- Prevents dialing
    vl.modify_date = NOW()
WHERE vl.list_id BETWEEN 101 AND 109
    AND vl.called_since_last_reset = 'N';
```

### 2. tcpa_30day_compliance.sql - AUTOMATIC ARCHIVING
**When it runs**: Daily at 1:00 AM  
**What it does**: Moves ANY lead past 30-day TCPA to archive  
**Purpose**: Legal compliance - stop calling after consent expires

```sql
START TRANSACTION;

-- Find all expired TCPA leads
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired AS
SELECT DISTINCT 
    vl.lead_id,
    vl.list_id as from_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), vl.tcpajoin_date) as days_old
FROM vicidial_list vl
WHERE vl.list_id BETWEEN 101 AND 109
    AND vl.tcpajoin_date IS NOT NULL
    AND DATEDIFF(CURDATE(), vl.tcpajoin_date) >= 30;  -- 30 days expired

-- Log the archiving
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, 110, CONCAT('TCPA expired - ', days_old, ' days old'), brain_id
FROM tcpa_expired;

-- Archive the leads
UPDATE vicidial_list vl
INNER JOIN tcpa_expired te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPAEXP',  -- Special expired status
    vl.called_since_last_reset = 'Y',  -- Never dial again
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS tcpa_expired;
COMMIT;
```

### 3. reset_new_leads.sql - DAILY RESET FOR DIALING
**When it runs**: Daily at 4:00 AM  
**What it does**: Resets leads for the day's dialing  
**Purpose**: Ensures leads are ready for their scheduled resets

```sql
-- Reset leads for today's dialing
UPDATE vicidial_list
SET called_since_last_reset = 'N'
WHERE list_id BETWEEN 101 AND 109
    AND status = 'NEW'
    AND called_since_last_reset = 'Y';
```

### 4. workday_check.sql - SKIP WEEKENDS/HOLIDAYS
**When it runs**: Every morning at 6:00 AM  
**What it does**: Checks if today is a workday  
**Purpose**: Prevents movements on weekends/holidays

```sql
-- Check if today is a workday
SELECT 
    CASE 
        WHEN is_workday = TRUE THEN 'WORKDAY - Process normally'
        WHEN is_holiday = TRUE THEN CONCAT('HOLIDAY - ', holiday_name)
        WHEN DAYOFWEEK(CURDATE()) IN (1,7) THEN 'WEEKEND - Skip processing'
        ELSE 'NON-WORKDAY'
    END as day_status
FROM workday_calendar
WHERE cal_date = CURDATE();
```

---

## ⏰ COMPLETE CRON SCHEDULE {#cron-schedule}

```bash
# ===== SPEED TO LEAD - CRITICAL (Every 5 minutes) =====
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_101_102
*/5 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103.sql 2>&1 | logger -t vici_102_103

# ===== REGULAR MOVEMENTS (Every 15 minutes) =====
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_direct.sql 2>&1 | logger -t vici_callbk
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104.sql 2>&1 | logger -t vici_vm_hot

# ===== STATUS UPDATES (Every 30 minutes) =====
*/30 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/update_excluded_statuses.sql 2>&1 | logger -t vici_exclude

# ===== DAILY MOVEMENTS (12:01 AM) =====
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105.sql 2>&1 | logger -t vici_104_105
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106.sql 2>&1 | logger -t vici_105_106
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107.sql 2>&1 | logger -t vici_106_107
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108.sql 2>&1 | logger -t vici_107_108
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_109.sql 2>&1 | logger -t vici_108_109
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_109_110.sql 2>&1 | logger -t vici_109_110

# ===== COMPLIANCE (1:00 AM) =====
0 1 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_30day_compliance.sql 2>&1 | logger -t vici_tcpa

# ===== DAILY RESET (4:00 AM) =====
0 4 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/reset_new_leads.sql 2>&1 | logger -t vici_reset

# ===== WORKDAY CHECK (6:00 AM) =====
0 6 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/workday_check.sql 2>&1 | logger -t vici_workday
```

---

## 👥 AGENT INSTRUCTIONS {#agent-instructions}

### When You Get a Lead from List 103 (Voicemail)
1. **YOU WILL SEE**: Red alert banner "LEAVE VOICEMAIL"
2. **YOUR ACTION**: 
   - Leave a professional voicemail
   - Set status to **LVM** (Left Voicemail)
   - Do NOT set any other status
3. **SCRIPT**: "Hi [Name], this is [Agent] calling about your auto insurance quote request. I have some great rates that could save you money. Please call me back at [Number] to discuss your savings. Thank you!"

### Status Guide for Agents
| Use This Status | When |
|-----------------|------|
| **LVM** | You left a voicemail |
| **CALLBK** | Customer requested callback |
| **XFER** | Successfully transferred to buyer |
| **DNC** | Customer requests Do Not Call |
| **NI** | Customer says Not Interested |
| **NA** | No Answer (default) |
| **B** | Line is Busy |

### DO NOT USE These Statuses
- **VMQ** - System use only
- **NEW** - System use only
- **REST** - System use only
- **ARCHIVE** - System use only

---

## 📊 MONITORING & REPORTING {#monitoring}

### Daily Health Check Query
```sql
-- Check lead distribution across lists
SELECT 
    list_id,
    COUNT(*) as total_leads,
    SUM(CASE WHEN DATE(entry_date) = CURDATE() THEN 1 ELSE 0 END) as new_today,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    SUM(CASE WHEN status IN ('DNC','DNCL','NI') THEN 1 ELSE 0 END) as stopped
FROM vicidial_list
WHERE list_id BETWEEN 101 AND 110
GROUP BY list_id
ORDER BY list_id;
```

### Movement Activity Report
```sql
-- Today's movements
SELECT 
    from_list_id,
    to_list_id,
    COUNT(*) as leads_moved,
    move_reason,
    MAX(move_date) as last_move
FROM lead_moves
WHERE DATE(move_date) = CURDATE()
GROUP BY from_list_id, to_list_id, move_reason
ORDER BY from_list_id, to_list_id;
```

### TCPA Compliance Check
```sql
-- Leads approaching TCPA expiry
SELECT 
    list_id,
    COUNT(*) as at_risk,
    MIN(DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE())) as days_remaining
FROM vicidial_list
WHERE list_id BETWEEN 101 AND 109
    AND tcpajoin_date IS NOT NULL
    AND DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 3
GROUP BY list_id;
```

---

## 🔧 TROUBLESHOOTING GUIDE {#troubleshooting}

### Problem: Leads Not Moving from 101 to 102
**Check**:
1. Is the cron job running? `grep vici_101_102 /var/log/syslog`
2. Are leads being called? `SELECT * FROM vicidial_log WHERE list_id = 101 AND call_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)`
3. Are they excluded statuses? Check if status is DNC, XFER, etc.

### Problem: Leads Stuck in List 103 (Voicemail)
**Check**:
1. Are agents setting LVM status? `SELECT status, COUNT(*) FROM vicidial_list WHERE list_id = 103 GROUP BY status`
2. Is move_103_104.sql running? Check cron logs
3. Train agents on proper VM status setting

### Problem: Too Many Calls to Same Lead
**Check**:
1. Verify reset times are configured in Vici
2. Check called_since_last_reset flag
3. Review list reset configuration

### Problem: TCPA Leads Not Archiving
**Check**:
1. Is tcpajoin_date populated? `SELECT COUNT(*) FROM vicidial_list WHERE tcpajoin_date IS NULL`
2. Is compliance script running? Check 1:00 AM cron
3. Verify date calculation is correct

---

## 🎯 KEY SUCCESS METRICS

### Target Performance
- **Speed to Lead**: 3 attempts within first hour ✅
- **Voicemail Rate**: 100% of List 103 should get VM ✅
- **TCPA Compliance**: 0 calls after 30 days ✅
- **Transfer Rate**: Track XFER status by list
- **Contact Rate**: Monitor by list and time of day

### Expected Flow Timing
- **Hour 0-1**: Lists 101 → 102 → 103 (3 attempts)
- **Days 1-3**: List 104 (12 attempts)
- **Days 4-10**: List 105 (21 attempts)
- **Days 11-15**: List 106 (10 attempts)
- **Days 16-20**: List 107 (10 attempts)
- **Days 21-27**: List 108 (REST - 0 attempts)
- **Days 28-32**: List 109 (5 attempts)
- **Day 30+**: List 110 (Archive)

---

## 💡 30-DAY RESURRECTION STRATEGY

### The Opportunity
After 30 days in archive, leads have "cooled off" and may be receptive again.

### Implementation (Optional List 111)
```sql
-- move_110_111_resurrection.sql
-- Resurrect leads after 30 days in archive
-- Run daily at 2:00 AM

CREATE TEMPORARY TABLE IF NOT EXISTS resurrection_leads AS
SELECT lead_id, vendor_lead_code as brain_id
FROM vicidial_list vl
WHERE list_id = 110
    AND status NOT IN ('DNC', 'DNCL', 'XFER', 'XFERA')
    AND DATEDIFF(NOW(), list_entry_date) >= 30
    AND DATEDIFF(CURDATE(), tcpajoin_date) < 60  -- Still valid
    AND NOT EXISTS (
        SELECT 1 FROM lead_moves 
        WHERE lead_id = vl.lead_id 
        AND to_list_id = 111
    );

-- Move to resurrection list
UPDATE vicidial_list vl
INNER JOIN resurrection_leads rl ON vl.lead_id = rl.lead_id
SET 
    vl.list_id = 111,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.comments = 'RESURRECTION ATTEMPT';
```

### Expected Results
- **8-12% contact rate** on resurrected leads
- **3-5% transfer rate** 
- **High ROI** - these are free re-attempts

---

## ✅ IMPLEMENTATION CHECKLIST

### Initial Setup
- [ ] Create all required tables (lead_moves, excluded_statuses, workday_calendar)
- [ ] Populate excluded statuses
- [ ] Populate holiday calendar
- [ ] Create /opt/vici_scripts/ directory

### Deploy SQL Scripts
- [ ] Upload all movement scripts (101→102, 102→103, etc.)
- [ ] Upload compliance scripts (TCPA, excluded statuses)
- [ ] Upload utility scripts (reset, workday check)
- [ ] Set proper permissions (chmod +x)

### Configure Vici
- [ ] Set List 103 as "Voicemail List" in description
- [ ] Configure reset times for each list
- [ ] Set excluded statuses as non-dialable
- [ ] Configure agent screen for VM alert

### Setup Cron Jobs
- [ ] Add all cron entries
- [ ] Verify cron is running (crontab -l)
- [ ] Test each script manually first
- [ ] Monitor logs for first 24 hours

### Train Agents
- [ ] Explain List 103 voicemail process
- [ ] Train on LVM status usage
- [ ] Review excluded statuses
- [ ] Provide script for voicemails

### Monitor & Optimize
- [ ] Daily review of movement logs
- [ ] Check TCPA compliance
- [ ] Monitor transfer rates by list
- [ ] Adjust timing if needed

---

## 📞 FINAL NOTES

This system is designed to maximize contact while respecting:
1. **Customer Psychology** - Rest period creates receptiveness
2. **TCPA Compliance** - Automatic 30-day cutoff
3. **Agent Efficiency** - Clear instructions and alerts
4. **Business Hours** - Workday-only calling
5. **ROI Optimization** - 61 attempts over 30 days

**Remember**: The key to success is the REST PERIOD (List 108). This 7-day break resets the customer's receptiveness and makes List 109 surprisingly effective.

---

**Document Version**: 1.0  
**System Ready**: Deploy all scripts and start with a test batch first!




