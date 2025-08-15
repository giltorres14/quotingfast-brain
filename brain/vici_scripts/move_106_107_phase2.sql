-- move_106_107_phase2.sql
-- Moves leads from List 106 to 107 (Cool Down) after 10 days from original entry
-- Runs daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_106_107 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    107 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), DATE(IFNULL(vl.original_entry_date, vl.entry_date))) as days_since_original
FROM vicidial_list vl
LEFT JOIN excluded_statuses es ON vl.status = es.status
WHERE vl.list_id = 106
AND es.status IS NULL
AND (
    -- 10 days from original entry
    DATEDIFF(CURDATE(), DATE(IFNULL(vl.original_entry_date, vl.entry_date))) >= 10
    OR
    -- If using calendar table for workdays
    (SELECT COUNT(*) FROM calendar 
     WHERE date_value > DATE(IFNULL(vl.original_entry_date, vl.entry_date))
     AND date_value <= CURDATE() 
     AND is_workday = 1) >= 10
)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 107
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT('Move to cool down phase - ', days_since_original, ' days old'),
       brain_id
FROM leads_to_move_106_107;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_106_107 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 107,
    vl.status = 'NEW',  -- Keep NEW but list 107 should be inactive
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_106_107;

COMMIT;

