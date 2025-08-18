-- =====================================================
-- CHECK AND RENAME EXISTING VICI LISTS
-- Run this to audit current lists and standardize names
-- =====================================================

-- First, let's see what lists currently exist
SELECT 
    list_id,
    list_name,
    campaign_id,
    active,
    list_description,
    (SELECT COUNT(*) FROM vicidial_list WHERE list_id = vl.list_id) as lead_count,
    (SELECT COUNT(DISTINCT status) FROM vicidial_list WHERE list_id = vl.list_id) as unique_statuses
FROM vicidial_lists vl
WHERE list_id BETWEEN 101 AND 199
ORDER BY list_id;

-- =====================================================
-- RENAME EXISTING LISTS TO MATCH NEW FLOW
-- =====================================================

-- List 101: Initial Contact
UPDATE vicidial_lists 
SET list_name = '101 - Initial Contact',
    list_description = 'Immediate first call upon lead entry. Moves to 102 or 104 based on outcome.'
WHERE list_id = 101;

-- List 102: 20-Min Follow-Up
UPDATE vicidial_lists 
SET list_name = '102 - 20-Min Follow-Up',
    list_description = '20-minute hold before second attempt. Auto-moves to 103 after call.'
WHERE list_id = 102;

-- List 103: Voicemail #1
UPDATE vicidial_lists 
SET list_name = '103 - Voicemail #1 🔔',
    list_description = 'AGENT ALERT: Leave voicemail. Set LVM status to trigger move to 104.'
WHERE list_id = 103;

-- List 104: Hot Phase
UPDATE vicidial_lists 
SET list_name = '104 - Hot Phase 🔥',
    list_description = '3 days aggressive calling (4x/day). 12 total calls. Days 1-3.'
WHERE list_id = 104;

-- List 105: Voicemail #2
UPDATE vicidial_lists 
SET list_name = '105 - Voicemail #2 🔔',
    list_description = 'AGENT ALERT: Second voicemail with urgency. Set LVM status.'
WHERE list_id = 105;

-- List 106: Extended Follow-Up
UPDATE vicidial_lists 
SET list_name = '106 - Extended Follow-Up',
    list_description = '5 days moderate calling (3x/day). 15 total calls. Days 4-8.'
WHERE list_id = 106;

-- List 107: Cool Down
UPDATE vicidial_lists 
SET list_name = '107 - Cool Down',
    list_description = '5 days light calling (2x/day). 10 total calls. Days 9-13.'
WHERE list_id = 107;

-- List 108: Rest Period
UPDATE vicidial_lists 
SET list_name = '108 - Rest Period ⏸️',
    list_description = 'NO CALLS for 7 days. Psychological reset. Days 14-20.'
WHERE list_id = 108;

-- List 109: Final Attempt
UPDATE vicidial_lists 
SET list_name = '109 - Final Attempt',
    list_description = '5 days final calling (1x/day). 5 total calls. Days 21-25 or until TCPA limit.'
WHERE list_id = 109;

-- List 110: Final Archive
UPDATE vicidial_lists 
SET list_name = '110 - Final Archive',
    list_description = 'Permanent storage. Campaign complete. No more calls.'
WHERE list_id = 110;

-- List 111: 30-Day Reactivation
UPDATE vicidial_lists 
SET list_name = '111 - 30-Day Reactivation',
    list_description = '30 days after last call. Single attempt. "Situation changed?" approach.'
WHERE list_id = 111;

-- =====================================================
-- CREATE NEW SPECIAL PURPOSE LISTS
-- =====================================================

-- List 112: NI Retargeting (if doesn't exist)
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    112, 
    '112 - NI Retarget (Rate Reduction)', 
    'AutoDial',
    'Y',
    'Not Interested leads after 45-day cool-off. Special script: Rate Reduction approach. Max 2 attempts.'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    list_description = VALUES(list_description);

-- List 120: Training Campaign (MUST BE ON Auto2)
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    120, 
    '120 - Training (Auto2 ONLY)', 
    'Auto2',  -- CRITICAL: Must be Auto2
    'Y',
    'Days 40-85 heavily worked leads for agent training. AUTO2 CAMPAIGN ONLY. Still within TCPA.'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    campaign_id = 'Auto2',  -- Force to Auto2
    list_description = VALUES(list_description);

-- List 199: TCPA Graveyard
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    199, 
    '199 - TCPA Expired (NON-ALLSTATE)', 
    'SPECIAL',  -- Special campaign designation
    'N',  -- INACTIVE by default
    'TCPA expired leads (89+ days). NON-ALLSTATE CAMPAIGNS ONLY. Requires special permission.'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    active = 'N',  -- Keep inactive
    list_description = VALUES(list_description);

-- =====================================================
-- VERIFY CAMPAIGN ASSIGNMENTS
-- =====================================================

-- Check that List 120 is on Auto2
SELECT 
    list_id,
    list_name,
    campaign_id,
    CASE 
        WHEN list_id = 120 AND campaign_id != 'Auto2' THEN '⚠️ WRONG CAMPAIGN - SHOULD BE Auto2'
        WHEN list_id = 120 AND campaign_id = 'Auto2' THEN '✅ Correct - Auto2'
        ELSE 'N/A'
    END as campaign_check
FROM vicidial_lists
WHERE list_id IN (112, 120, 199);

-- =====================================================
-- AUDIT REPORT - FINAL STATUS
-- =====================================================

SELECT 
    vl.list_id,
    vl.list_name,
    vl.campaign_id,
    vl.active,
    COUNT(DISTINCT v.lead_id) as total_leads,
    SUM(CASE WHEN v.status = 'NEW' THEN 1 ELSE 0 END) as new_leads,
    SUM(CASE WHEN v.status IN ('XFER','XFERA') THEN 1 ELSE 0 END) as transferred,
    SUM(CASE WHEN v.status = 'NI' THEN 1 ELSE 0 END) as not_interested,
    SUM(CASE WHEN v.status IN ('DNC','DNCL') THEN 1 ELSE 0 END) as dnc,
    SUM(CASE WHEN DATEDIFF(NOW(), v.entry_date) > 89 THEN 1 ELSE 0 END) as tcpa_expired
FROM vicidial_lists vl
LEFT JOIN vicidial_list v ON vl.list_id = v.list_id
WHERE vl.list_id BETWEEN 101 AND 199
GROUP BY vl.list_id, vl.list_name, vl.campaign_id, vl.active
ORDER BY vl.list_id;

-- =====================================================
-- WARNING CHECK - TCPA VIOLATIONS
-- =====================================================

SELECT 
    'WARNING: TCPA EXPIRED LEADS IN ACTIVE LISTS' as alert,
    list_id,
    COUNT(*) as expired_count,
    MIN(entry_date) as oldest_lead
FROM vicidial_list
WHERE DATEDIFF(NOW(), entry_date) > 89
    AND list_id NOT IN (110, 199)  -- Not in archive lists
    AND list_id BETWEEN 101 AND 120
GROUP BY list_id
HAVING expired_count > 0;
