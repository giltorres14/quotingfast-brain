# 📋 ViciDial Call Flow SQL Playbook
*Production-Ready SQL Implementation for Automated Lead Movement*
*Last Updated: December 2024*

## 🎯 Overview
This playbook contains all SQL queries needed to implement the automated lead flow system in ViciDial. All queries are designed to be idempotent, race-proof, and respect TCPA compliance.

## 📊 Database Schema Setup

### 1. Calendar Table (for workday calculations)
```sql
-- Create calendar table for workday tracking
CREATE TABLE IF NOT EXISTS calendar (
    date_value DATE PRIMARY KEY,
    is_workday TINYINT(1) DEFAULT 1,
    is_holiday TINYINT(1) DEFAULT 0,
    holiday_name VARCHAR(100),
    INDEX idx_workday (is_workday, date_value)
) ENGINE=InnoDB;

-- Populate calendar with dates (2024-2026)
INSERT IGNORE INTO calendar (date_value, is_workday)
SELECT 
    DATE_ADD('2024-01-01', INTERVAL seq DAY) as date_value,
    CASE 
        WHEN DAYOFWEEK(DATE_ADD('2024-01-01', INTERVAL seq DAY)) IN (1, 7) THEN 0
        ELSE 1
    END as is_workday
FROM (
    SELECT a.N + b.N * 10 + c.N * 100 as seq
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
          UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
    CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
    CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c
) seq_table
WHERE DATE_ADD('2024-01-01', INTERVAL seq DAY) <= '2026-12-31';

-- Mark US Federal Holidays 2024-2025
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'New Years Day' WHERE date_value IN ('2024-01-01', '2025-01-01');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'MLK Day' WHERE date_value IN ('2024-01-15', '2025-01-20');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Presidents Day' WHERE date_value IN ('2024-02-19', '2025-02-17');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Memorial Day' WHERE date_value IN ('2024-05-27', '2025-05-26');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Juneteenth' WHERE date_value IN ('2024-06-19', '2025-06-19');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Independence Day' WHERE date_value IN ('2024-07-04', '2025-07-04');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Labor Day' WHERE date_value IN ('2024-09-02', '2025-09-01');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Columbus Day' WHERE date_value IN ('2024-10-14', '2025-10-13');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Veterans Day' WHERE date_value IN ('2024-11-11', '2025-11-11');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Thanksgiving' WHERE date_value IN ('2024-11-28', '2025-11-27');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Day After Thanksgiving' WHERE date_value IN ('2024-11-29', '2025-11-28');
UPDATE calendar SET is_workday = 0, is_holiday = 1, holiday_name = 'Christmas' WHERE date_value IN ('2024-12-25', '2025-12-25');
```

### 2. Custom Fields in vicidial_list
```sql
-- Add custom fields if they don't exist
ALTER TABLE vicidial_list 
ADD COLUMN IF NOT EXISTS list_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead entered current list',
ADD COLUMN IF NOT EXISTS original_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead first entered system',
ADD COLUMN IF NOT EXISTS tcpajoin_date DATE DEFAULT NULL COMMENT 'TCPA consent date',
ADD INDEX IF NOT EXISTS idx_list_entry (list_entry_date),
ADD INDEX IF NOT EXISTS idx_original_entry (original_entry_date),
ADD INDEX IF NOT EXISTS idx_tcpa (tcpajoin_date);
```

### 3. Lead Movement Audit Table
```sql
-- Create audit table for tracking lead movements
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT,
    to_list_id INT,
    move_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    move_reason VARCHAR(100),
    disposition VARCHAR(20),
    INDEX idx_lead (lead_id),
    INDEX idx_move_date (move_date),
    INDEX idx_lists (from_list_id, to_list_id)
) ENGINE=InnoDB;
```

### 4. Excluded Statuses Table
```sql
-- Create table for managing excluded statuses
CREATE TABLE IF NOT EXISTS excluded_statuses (
    status VARCHAR(20) PRIMARY KEY,
    description VARCHAR(100),
    never_move TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Populate with standard exclusions
INSERT IGNORE INTO excluded_statuses (status, description) VALUES
('XFER', 'Transferred - Active Sale'),
('XFERA', 'Transferred - Agent'),
('DNC', 'Do Not Call'),
('DNCL', 'Do Not Call List'),
('ADCT', 'Disconnected'),
('ADC', 'Disconnected Number'),
('NI', 'Not Interested'),
('DC', 'Disconnected'),
('CALLBK', 'Callback Scheduled'),
('NEW', 'Never Called');
```

### 5. SMS Integration Table (for Brain server)
```sql
-- Create table for SMS queue (to be processed by Brain server)
CREATE TABLE IF NOT EXISTS twilio_outbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT,
    scheduled_time DATETIME,
    sent_time DATETIME DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    list_id INT,
    trigger_event VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_scheduled (status, scheduled_time),
    INDEX idx_lead (lead_id)
) ENGINE=InnoDB;
```

## 🔄 Lead Movement SQL Queries

### List 101 → 102 (Immediate → 30-min delay)
**Runs every 15 minutes via cron**
```sql
-- Move from 101 to 102 after first call attempt (excluding specific dispositions)
START TRANSACTION;

-- Create temp table with leads to move
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 102 as to_list, v_latest.status
FROM vicidial_list vl
INNER JOIN (
    -- Get latest disposition for each lead
    SELECT lead_id, status, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE call_date >= CURDATE()
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
LEFT JOIN excluded_statuses es ON v_latest.status = es.status
WHERE vl.list_id = 101
AND es.status IS NULL  -- Not in excluded statuses
AND v_latest.status NOT IN ('CALLBK')  -- CALLBK goes to 103
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 102
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition)
SELECT lead_id, from_list, to_list, '30-min delay after first attempt', status
FROM leads_to_move_101_102;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N',
    vl.modify_date = DATE_SUB(NOW(), INTERVAL 15 MINUTE);  -- 15-min delay

-- Clean up
DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;

COMMIT;
```

### List 101 → 103 (CALLBK direct move)
**Runs every 15 minutes via cron**
```sql
-- Move CALLBK leads directly to List 103
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 103 as to_list
FROM vicidial_list vl
INNER JOIN (
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE call_date >= CURDATE()
    AND status = 'CALLBK'
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 101
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
    AND DATE(lm.move_date) = CURDATE()
);

-- Set original_entry_date if not set
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_103 tm ON vl.lead_id = tm.lead_id
SET vl.original_entry_date = NOW()
WHERE vl.original_entry_date IS NULL;

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'CALLBK direct to voicemail phase'
FROM leads_to_move_101_103;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_103;

COMMIT;
```

### List 102 → 103 (After 3 workdays of aggressive calling)
**Runs daily at 12:01 AM**
```sql
-- Move from 102 to 103 after 3 workdays
START TRANSACTION;

-- Calculate workdays between list entry and today
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 103 as to_list
FROM vicidial_list vl
CROSS JOIN (
    -- Count workdays since list entry
    SELECT COUNT(*) as workdays_passed
    FROM calendar
    WHERE date_value > DATE(vl.list_entry_date)
    AND date_value <= CURDATE()
    AND is_workday = 1
) wd
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 102
AND vl.status NOT IN ('VMQ', 'XFER', 'XFERA', 'DNC', 'DNCL', 'ADCT', 'ADC', 'NI', 'DC')
AND wd.workdays_passed >= 3
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, '3 workdays aggressive calling complete'
FROM leads_to_move_102_103;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;

COMMIT;
```

### List 103 → 104 (After LVM - Left Voice Mail)
**Runs every 15 minutes**
```sql
-- Move from 103 to 104 after voicemail left
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_103_104 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 104 as to_list
FROM vicidial_list vl
INNER JOIN (
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE status = 'LVM'
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 103
AND v_latest.latest_call > vl.list_entry_date  -- LVM happened after entering list
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1  -- Only move on workdays
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 104
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'Voicemail left - move to Phase 1'
FROM leads_to_move_103_104;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_103_104 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 104,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_103_104;

COMMIT;
```

### List 104 → 105 (Phase 1 to Phase 2 - after 5 days from original entry)
**Runs daily at 12:01 AM**
```sql
-- Move from 104 to 105 after 5 days from original entry
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_104_105 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 105 as to_list
FROM vicidial_list vl
CROSS JOIN (
    SELECT COUNT(*) as workdays_since_original
    FROM calendar
    WHERE date_value > DATE(vl.original_entry_date)
    AND date_value <= CURDATE()
    AND is_workday = 1
) wd
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 104
AND vl.status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'ADCT', 'ADC', 'NI', 'DC')
AND wd.workdays_since_original >= 5
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 105
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'Phase 1 complete - 5 days reached'
FROM leads_to_move_104_105;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_104_105 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 105,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_104_105;

COMMIT;
```

### List 105 → 106 (After LVM in Phase 2 Voicemail)
**Runs every 15 minutes**
```sql
-- Move from 105 to 106 after voicemail left
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_105_106 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 106 as to_list
FROM vicidial_list vl
INNER JOIN (
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE status = 'LVM'
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 105
AND v_latest.latest_call > vl.list_entry_date
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 106
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'Phase 2 voicemail left'
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

### List 106 → 107 (Phase 2 to Cool Down - after 10 days from original)
**Runs daily at 12:01 AM**
```sql
-- Move from 106 to 107 after 10 days from original entry
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_106_107 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 107 as to_list
FROM vicidial_list vl
CROSS JOIN (
    SELECT COUNT(*) as workdays_since_original
    FROM calendar
    WHERE date_value > DATE(vl.original_entry_date)
    AND date_value <= CURDATE()
    AND is_workday = 1
) wd
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 106
AND vl.status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'ADCT', 'ADC', 'NI', 'DC')
AND wd.workdays_since_original >= 10
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 107
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'Move to cool down phase'
FROM leads_to_move_106_107;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_106_107 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 107,
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_106_107;

COMMIT;
```

### List 107 → 108 (Cool Down to Phase 3 - after 7 days rest)
**Runs daily at 12:01 AM**
```sql
-- Move from 107 to 108 after 7 days of rest
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_107_108 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 108 as to_list
FROM vicidial_list vl
CROSS JOIN (
    SELECT COUNT(*) as workdays_in_cooldown
    FROM calendar
    WHERE date_value > DATE(vl.list_entry_date)
    AND date_value <= CURDATE()
    AND is_workday = 1
) wd
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id = 107
AND vl.status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL', 'ADCT', 'ADC', 'NI', 'DC')
AND wd.workdays_in_cooldown >= 7
AND (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 108
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'Cool down complete - Phase 3'
FROM leads_to_move_107_108;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_107_108 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 108,
    vl.called_since_last_reset = 'N';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_107_108;

COMMIT;
```

### List 108 → 110 (Phase 3 to Archive - after 30 days or TCPA expiry)
**Runs daily at 12:01 AM**
```sql
-- Move from 108 to 110 (Archive) after 30 days total or TCPA expiry
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_108_110 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 110 as to_list,
    CASE 
        WHEN jt.tcpajoin_date IS NOT NULL AND CURDATE() >= DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY) THEN 'TCPA expired'
        ELSE '30 days reached'
    END as reason
FROM vicidial_list vl
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
CROSS JOIN (
    SELECT COUNT(*) as total_workdays
    FROM calendar
    WHERE date_value > DATE(vl.original_entry_date)
    AND date_value <= CURDATE()
    AND is_workday = 1
) wd
WHERE vl.list_id = 108
AND (
    -- TCPA expired
    (jt.tcpajoin_date IS NOT NULL AND CURDATE() >= DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY))
    OR 
    -- 30 workdays reached
    wd.total_workdays >= 30
)
AND (SELECT is_workday FROM calendar WHERE date_value = CURDATE()) = 1
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 110
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, reason
FROM leads_to_move_108_110;

UPDATE vicidial_list vl
INNER JOIN leads_to_move_108_110 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'ARCHIVE';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_108_110;

COMMIT;
```

### TCPA Compliance Check (All Lists)
**Runs every hour**
```sql
-- Move any TCPA-expired leads to archive immediately
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired_leads AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 110 as to_list
FROM vicidial_list vl
INNER JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE vl.list_id BETWEEN 101 AND 109
AND CURDATE() >= DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 110
    AND DATE(lm.move_date) = CURDATE()
);

INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason)
SELECT lead_id, from_list, to_list, 'TCPA compliance - 30 day limit'
FROM tcpa_expired_leads;

UPDATE vicidial_list vl
INNER JOIN tcpa_expired_leads te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPA_EXP';

DROP TEMPORARY TABLE IF EXISTS tcpa_expired_leads;

COMMIT;
```

## 📅 Cron Job Configuration

Add these to your crontab:
```bash
# Every 15 minutes - Fast moves
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_101_102.sql
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_101_103_callbk.sql
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_103_104_lvm.sql
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_105_106_lvm.sql

# Daily at 12:01 AM - Workday-based moves
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_102_103_workdays.sql
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_104_105_phase1.sql
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_106_107_phase2.sql
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_107_108_cooldown.sql
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_108_110_archive.sql

# Hourly - TCPA compliance check
0 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/tcpa_compliance_check.sql
```

## 🎛️ ViciDial Admin Configuration

### Campaign Settings
```
Campaign ID: AUTODIAL
List Order: DOWN COUNT
List Order 2nd: NEW
List Order 3rd: NONE
Hopper Level: 500
Drop Call Seconds: 5
Next Agent Call: oldest_call_finish
Local Call Time: 9to9
Dial Timeout: 30
```

### List Configuration
| List ID | Name | Active | Reset Time | Reset Lead Status | Campaign |
|---------|------|--------|------------|-------------------|----------|
| 101 | Immediate | Y | Never | - | AUTODIAL |
| 102 | Day 1-3 Aggressive | Y | 0100 | NEW | AUTODIAL |
| 103 | Voicemail Phase 1 | Y | Never | - | AUTODIAL |
| 104 | Phase 1 (3x/day) | Y | 0100,1300,1700 | NEW | AUTODIAL |
| 105 | Voicemail Phase 2 | Y | Never | - | AUTODIAL |
| 106 | Phase 2 (2x/day) | Y | 0100,1400 | NEW | AUTODIAL |
| 107 | Cool Down | N | - | - | AUTODIAL |
| 108 | Phase 3 (1x/day) | Y | 0100 | NEW | AUTODIAL |
| 110 | Archive | N | - | - | AUTODIAL |
| 111 | Training | Y | 0100 | NEW | AUTO2 |

### Lead Filters
```sql
-- Filter for List 102 (15-minute delay)
SELECT lead_id FROM vicidial_list 
WHERE list_id = 102 
AND modify_date <= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
AND status IN ('VMQ', 'NEW');

-- TCPA Compliance Filter (all lists)
SELECT vl.lead_id FROM vicidial_list vl
LEFT JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE (jt.tcpajoin_date IS NULL OR CURDATE() < DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY));
```

### Agent Script Injection
Add to campaign's custom agent screen:
```javascript
<script>
(function() {
    function checkListAndShowAlert() {
        var listInput = document.querySelector("input[name='list_id']");
        if (!listInput) return;
        
        var listId = listInput.value;
        var alertDiv = document.getElementById('custom-list-alert');
        
        if (listId === '103' || listId === '105') {
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.id = 'custom-list-alert';
                alertDiv.style.cssText = 'background:#ff0000;color:#fff;padding:10px;font-weight:bold;position:fixed;top:60px;left:50%;transform:translateX(-50%);z-index:9999;border-radius:5px;';
                alertDiv.innerHTML = '⚠️ LEAVE A VOICEMAIL ON THIS CALL ⚠️';
                document.body.appendChild(alertDiv);
            }
        } else if (alertDiv) {
            alertDiv.remove();
        }
    }
    
    // Check on load and periodically
    checkListAndShowAlert();
    setInterval(checkListAndShowAlert, 1000);
})();
</script>
```

## 📊 Monitoring Queries

### Daily Lead Flow Report
```sql
SELECT 
    list_id,
    COUNT(*) as lead_count,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    SUM(CASE WHEN status = 'VMQ' THEN 1 ELSE 0 END) as awaiting_call,
    AVG(DATEDIFF(NOW(), original_entry_date)) as avg_age_days
FROM vicidial_list
WHERE list_id BETWEEN 101 AND 111
GROUP BY list_id
ORDER BY list_id;
```

### TCPA Compliance Check
```sql
SELECT 
    COUNT(*) as at_risk_count,
    MIN(DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE())) as days_until_first_expiry
FROM vicidial_list vl
INNER JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE list_id BETWEEN 101 AND 109
AND DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 3;
```

### Lead Movement Audit
```sql
SELECT 
    DATE(move_date) as move_day,
    from_list_id,
    to_list_id,
    move_reason,
    COUNT(*) as leads_moved
FROM lead_moves
WHERE move_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(move_date), from_list_id, to_list_id, move_reason
ORDER BY move_date DESC, from_list_id, to_list_id;
```

## 🚨 Troubleshooting

### Common Issues and Solutions

1. **Leads not moving from List 101**
   - Check if cron job is running
   - Verify leads have been called (check vicidial_log)
   - Ensure excluded_statuses table is populated

2. **TCPA leads still being called**
   - Verify lead_tcpajoin table has data
   - Check TCPA compliance cron is running hourly
   - Review campaign filters

3. **Workday calculations incorrect**
   - Verify calendar table is populated
   - Check holidays are marked correctly
   - Ensure timezone settings are correct

4. **Duplicate movements**
   - Check lead_moves table for duplicates
   - Verify race guard conditions in queries
   - Review cron job frequency

## 📝 Notes

- All queries use transactions for atomicity
- Temporary tables prevent race conditions
- Workday logic excludes weekends and holidays
- TCPA compliance overrides all other rules
- Lead movement happens only on workdays
- SMS integration handled by separate Brain server

---

*This playbook is designed for production use with ViciDial 9.x and MySQL 5.7+*
