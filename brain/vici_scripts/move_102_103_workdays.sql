-- move_102_103_workdays.sql
-- Moves leads from List 102 to 103 after 3 workdays of aggressive calling
-- Runs daily at 12:01 AM

START TRANSACTION;

-- Calculate workdays and move eligible leads
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    103 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) as days_in_list
FROM vicidial_list vl
LEFT JOIN excluded_statuses es ON vl.status = es.status
WHERE vl.list_id = 102
AND es.status IS NULL  -- Not in excluded statuses
AND vl.list_entry_date IS NOT NULL
AND (
    -- Simple 3-day check (can enhance with calendar table later)
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) >= 3
    OR 
    -- If using calendar table for workdays
    (SELECT COUNT(*) FROM calendar 
     WHERE date_value > DATE(vl.list_entry_date) 
     AND date_value <= CURDATE() 
     AND is_workday = 1) >= 3
)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('3 workdays aggressive calling complete (', days_in_list, ' days)'),
       brain_id
FROM leads_to_move_102_103;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;

COMMIT;


-- Moves leads from List 102 to 103 after 3 workdays of aggressive calling
-- Runs daily at 12:01 AM

START TRANSACTION;

-- Calculate workdays and move eligible leads
CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_102_103 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    103 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) as days_in_list
FROM vicidial_list vl
LEFT JOIN excluded_statuses es ON vl.status = es.status
WHERE vl.list_id = 102
AND es.status IS NULL  -- Not in excluded statuses
AND vl.list_entry_date IS NOT NULL
AND (
    -- Simple 3-day check (can enhance with calendar table later)
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) >= 3
    OR 
    -- If using calendar table for workdays
    (SELECT COUNT(*) FROM calendar 
     WHERE date_value > DATE(vl.list_entry_date) 
     AND date_value <= CURDATE() 
     AND is_workday = 1) >= 3
)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 103
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('3 workdays aggressive calling complete (', days_in_list, ' days)'),
       brain_id
FROM leads_to_move_102_103;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_102_103 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 103,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_102_103;

COMMIT;






