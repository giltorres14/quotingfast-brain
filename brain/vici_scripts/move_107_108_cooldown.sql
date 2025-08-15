-- move_107_108_cooldown.sql
-- Moves leads from List 107 to 108 after 7 days of rest (cool down period)
-- Runs daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_107_108 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    108 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) as days_in_cooldown
FROM vicidial_list vl
WHERE vl.list_id = 107
AND vl.list_entry_date IS NOT NULL
AND (
    -- 7 days in cool down
    DATEDIFF(CURDATE(), DATE(vl.list_entry_date)) >= 7
    OR
    -- If using calendar table for workdays
    (SELECT COUNT(*) FROM calendar 
     WHERE date_value > DATE(vl.list_entry_date)
     AND date_value <= CURDATE() 
     AND is_workday = 1) >= 7
)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 108
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('Cool down complete - ', days_in_cooldown, ' days rest'),
       brain_id
FROM leads_to_move_107_108;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_107_108 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 108,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_107_108;

COMMIT;

