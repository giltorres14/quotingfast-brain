-- move_105_106_lvm.sql
-- Moves leads from List 105 to 106 after second voicemail is left
-- Runs every 15 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_105_106 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    106 as to_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN (
    -- Get leads where voicemail was left
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE status IN ('LVM', 'AL', 'AM')  -- Voicemail statuses
    AND call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 105
AND v_latest.latest_call > IFNULL(vl.list_entry_date, vl.entry_date)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 106
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 'Phase 2 voicemail left', brain_id
FROM leads_to_move_105_106;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_105_106 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 106,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_105_106;

COMMIT;


-- Moves leads from List 105 to 106 after second voicemail is left
-- Runs every 15 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_105_106 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    106 as to_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN (
    -- Get leads where voicemail was left
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE status IN ('LVM', 'AL', 'AM')  -- Voicemail statuses
    AND call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 105
AND v_latest.latest_call > IFNULL(vl.list_entry_date, vl.entry_date)
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 106
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 'Phase 2 voicemail left', brain_id
FROM leads_to_move_105_106;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_105_106 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 106,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_105_106;

COMMIT;


