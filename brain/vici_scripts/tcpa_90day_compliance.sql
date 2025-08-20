-- tcpa_90day_compliance.sql
-- CRITICAL TCPA COMPLIANCE: Archive ALL leads 90 days after opt-in date
-- Runs hourly to ensure immediate compliance
-- This is MANDATORY - leads CANNOT be called after 89 days from opt-in

START TRANSACTION;

-- Find ALL leads approaching or past 89 days from opt-in across ALL lists
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_90day_expired AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    199 as to_list,  -- List 199 for TCPA DNC
    vl.vendor_lead_code as brain_id,
    vl.entry_date as opt_in_date,
    DATEDIFF(CURDATE(), DATE(vl.entry_date)) as days_since_optin
FROM vicidial_list vl
WHERE vl.list_id NOT IN (199, 998, 999)  -- Not already in DNC/Archive lists
AND vl.entry_date IS NOT NULL
AND DATEDIFF(CURDATE(), DATE(vl.entry_date)) >= 89  -- 89 days or more
AND NOT EXISTS (
    -- Don't move if already moved today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 199
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves for audit trail
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('TCPA COMPLIANCE - 90 DAY LIMIT (', days_since_optin, ' days since opt-in)'),
       brain_id
FROM tcpa_90day_expired;

-- Perform the move - IMMEDIATE DNC status
UPDATE vicidial_list vl
INNER JOIN tcpa_90day_expired te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 199,  -- DNC list
    vl.status = 'DNC',  -- Do Not Call status
    vl.called_since_last_reset = 'N',
    vl.comments = CONCAT(IFNULL(vl.comments, ''), ' | TCPA 90-day limit reached on ', CURDATE());

-- Get count for logging
SELECT COUNT(*) as moved_count, 
       MIN(days_since_optin) as min_days,
       MAX(days_since_optin) as max_days
FROM tcpa_90day_expired;

DROP TEMPORARY TABLE IF EXISTS tcpa_90day_expired;

COMMIT;


-- CRITICAL TCPA COMPLIANCE: Archive ALL leads 90 days after opt-in date
-- Runs hourly to ensure immediate compliance
-- This is MANDATORY - leads CANNOT be called after 89 days from opt-in

START TRANSACTION;

-- Find ALL leads approaching or past 89 days from opt-in across ALL lists
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_90day_expired AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    199 as to_list,  -- List 199 for TCPA DNC
    vl.vendor_lead_code as brain_id,
    vl.entry_date as opt_in_date,
    DATEDIFF(CURDATE(), DATE(vl.entry_date)) as days_since_optin
FROM vicidial_list vl
WHERE vl.list_id NOT IN (199, 998, 999)  -- Not already in DNC/Archive lists
AND vl.entry_date IS NOT NULL
AND DATEDIFF(CURDATE(), DATE(vl.entry_date)) >= 89  -- 89 days or more
AND NOT EXISTS (
    -- Don't move if already moved today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 199
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves for audit trail
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('TCPA COMPLIANCE - 90 DAY LIMIT (', days_since_optin, ' days since opt-in)'),
       brain_id
FROM tcpa_90day_expired;

-- Perform the move - IMMEDIATE DNC status
UPDATE vicidial_list vl
INNER JOIN tcpa_90day_expired te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 199,  -- DNC list
    vl.status = 'DNC',  -- Do Not Call status
    vl.called_since_last_reset = 'N',
    vl.comments = CONCAT(IFNULL(vl.comments, ''), ' | TCPA 90-day limit reached on ', CURDATE());

-- Get count for logging
SELECT COUNT(*) as moved_count, 
       MIN(days_since_optin) as min_days,
       MAX(days_since_optin) as max_days
FROM tcpa_90day_expired;

DROP TEMPORARY TABLE IF EXISTS tcpa_90day_expired;

COMMIT;








