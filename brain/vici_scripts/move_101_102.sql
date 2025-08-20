-- move_101_102.sql
-- Moves leads from List 101 to 102 after first call attempt
-- Runs every 15 minutes via cron
-- Only moves leads that have been called (not CALLBK status)

START TRANSACTION;

-- Create temp table with leads to move
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    102 as to_list,
    vl.vendor_lead_code as brain_id,
    v_latest.status
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
AND v_latest.status NOT IN ('CALLBK', 'NEW')  -- CALLBK goes to 103, NEW hasn't been called
AND NOT EXISTS (
    -- Prevent duplicate moves today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 102
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, from_list, to_list, '30-min delay after first attempt', status, brain_id
FROM leads_to_move_101_102;

-- Set original_entry_date if not set
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET vl.original_entry_date = NOW()
WHERE vl.original_entry_date IS NULL;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,
    vl.status = 'NEW',  -- Reset to NEW for dialing
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW(),
    vl.modify_date = DATE_SUB(NOW(), INTERVAL 15 MINUTE);  -- 15-min delay before dialing

-- Clean up
DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;

COMMIT;
-- Runs every 15 minutes via cron
-- Only moves leads that have been called (not CALLBK status)

START TRANSACTION;

-- Create temp table with leads to move
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    102 as to_list,
    vl.vendor_lead_code as brain_id,
    v_latest.status
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
AND v_latest.status NOT IN ('CALLBK', 'NEW')  -- CALLBK goes to 103, NEW hasn't been called
AND NOT EXISTS (
    -- Prevent duplicate moves today
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 102
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, from_list, to_list, '30-min delay after first attempt', status, brain_id
FROM leads_to_move_101_102;

-- Set original_entry_date if not set
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET vl.original_entry_date = NOW()
WHERE vl.original_entry_date IS NULL;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,
    vl.status = 'NEW',  -- Reset to NEW for dialing
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW(),
    vl.modify_date = DATE_SUB(NOW(), INTERVAL 15 MINUTE);  -- 15-min delay before dialing

-- Clean up
DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;

COMMIT;

