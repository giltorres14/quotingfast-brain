-- move_103_104_lvm.sql
-- Moves leads from List 103 to 104 after voicemail is left (LVM status)
-- Runs every 15 minutes

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_103_104 AS
SELECT DISTINCT 
    vl.lead_id, 
    vl.list_id as from_list, 
    104 as to_list,
    vl.vendor_lead_code as brain_id
FROM vicidial_list vl
INNER JOIN (
    -- Get leads where voicemail was left
    SELECT lead_id, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE status IN ('LVM', 'AL', 'AM')  -- LVM=Left VM, AL/AM=Answering Machine
    AND call_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
WHERE vl.list_id = 103
AND v_latest.latest_call > IFNULL(vl.list_entry_date, vl.entry_date)  -- VM after entering list
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 104
);

-- Log the moves
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, brain_lead_id)
SELECT lead_id, from_list, to_list, 'Voicemail left - move to Phase 1', brain_id
FROM leads_to_move_103_104;

-- Perform the move
UPDATE vicidial_list vl
INNER JOIN leads_to_move_103_104 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 104,
    vl.status = 'NEW',
    vl.called_since_last_reset = 'N',
    vl.list_entry_date = NOW();

DROP TEMPORARY TABLE IF EXISTS leads_to_move_103_104;

COMMIT;

