-- tcpa_compliance_check.sql
-- Moves ANY lead to archive if TCPA consent has expired (30 days)
-- Runs hourly - This is a safety net to ensure compliance
-- Checks ALL lists 101-109

START TRANSACTION;

-- Find all TCPA-expired leads across all active lists
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired_leads AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    110 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), vl.tcpajoin_date) as days_since_consent
FROM vicidial_list vl
WHERE vl.list_id BETWEEN 101 AND 109  -- All active lists
AND vl.tcpajoin_date IS NOT NULL
AND CURDATE() >= DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY)
AND NOT EXISTS (
    -- Don't move if already moved today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 110
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('TCPA COMPLIANCE - Consent expired (', days_since_consent, ' days)'),
       brain_id
FROM tcpa_expired_leads;

-- Perform the move - IMMEDIATE archive for compliance
UPDATE vicidial_list vl
INNER JOIN tcpa_expired_leads te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPA_EXP',
    vl.called_since_last_reset = 'N';

-- Get count for logging
SELECT COUNT(*) as moved_count FROM tcpa_expired_leads;

DROP TEMPORARY TABLE IF EXISTS tcpa_expired_leads;

COMMIT;


-- Moves ANY lead to archive if TCPA consent has expired (30 days)
-- Runs hourly - This is a safety net to ensure compliance
-- Checks ALL lists 101-109

START TRANSACTION;

-- Find all TCPA-expired leads across all active lists
CREATE TEMPORARY TABLE IF NOT EXISTS tcpa_expired_leads AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    110 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), vl.tcpajoin_date) as days_since_consent
FROM vicidial_list vl
WHERE vl.list_id BETWEEN 101 AND 109  -- All active lists
AND vl.tcpajoin_date IS NOT NULL
AND CURDATE() >= DATE_ADD(vl.tcpajoin_date, INTERVAL 30 DAY)
AND NOT EXISTS (
    -- Don't move if already moved today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 110
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('TCPA COMPLIANCE - Consent expired (', days_since_consent, ' days)'),
       brain_id
FROM tcpa_expired_leads;

-- Perform the move - IMMEDIATE archive for compliance
UPDATE vicidial_list vl
INNER JOIN tcpa_expired_leads te ON vl.lead_id = te.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'TCPA_EXP',
    vl.called_since_last_reset = 'N';

-- Get count for logging
SELECT COUNT(*) as moved_count FROM tcpa_expired_leads;

DROP TEMPORARY TABLE IF EXISTS tcpa_expired_leads;

COMMIT;








