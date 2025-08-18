-- move_101_103_callbk.sql
-- Moves CALLBK leads directly from List 101 to 103 (Voicemail phase)
-- Runs every 15 minutes via cron

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    103 as to_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN (
    -- Get leads with CALLBK status
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND status = 'CALLBK'
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 101
AND NOT EXISTS (
    -- Prevent duplicate moves
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

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 'CALLBK direct to voicemail phase', brain_id
FROM leads_to_move_101_103;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_103;

COMMIT;


-- Moves CALLBK leads directly from List 101 to 103 (Voicemail phase)
-- Runs every 15 minutes via cron

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_103 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    103 as to_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN (
    -- Get leads with CALLBK status
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND status = 'CALLBK'
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 101
AND NOT EXISTS (
    -- Prevent duplicate moves
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

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 'CALLBK direct to voicemail phase', brain_id
FROM leads_to_move_101_103;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_103;

COMMIT;






