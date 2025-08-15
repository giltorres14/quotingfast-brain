-- move_108_110_archive.sql
-- Moves leads from List 108 to 110 (Archive) after 85 days (before TCPA 90-day limit)
-- Runs daily at 12:01 AM

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_108_110 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    110 as to_list,
    vl.vendor_lead_code as brain_id,
    DATEDIFF(CURDATE(), DATE(IFNULL(vl.original_entry_date, vl.entry_date))) as total_days,
    'Approaching TCPA limit - archiving at 85 days' as reason
FROM vicidial_list vl
WHERE vl.list_id = 108
AND (
    -- Archive at 85 days to stay well within TCPA 90-day limit
    DATEDIFF(CURDATE(), DATE(vl.entry_date)) >= 85
)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 110
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 
       CONCAT(reason, ' - ', total_days, ' days total'),
       brain_id
FROM leads_to_move_108_110;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_108_110 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 110,
    vl.status = 'ARCHIVE',
    vl.called_since_last_reset = 'N';

DROP TEMPORARY TABLE IF EXISTS leads_to_move_108_110;

COMMIT;
