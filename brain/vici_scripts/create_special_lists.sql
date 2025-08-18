-- =====================================================
-- SPECIAL LISTS CREATION SCRIPT
-- =====================================================

-- List 112: NI Retargeting (45-day cool-off)
-- List 120: Training Campaign (aged but valid)
-- List 199: TCPA Graveyard (expired, special campaigns only)

-- Create List 112: NI Retargeting
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    112, 
    'NI Retarget - Rate Reduction', 
    'AutoDial',  -- Assign to main campaign
    'Y',
    'Not Interested leads after 45-day cool-off. Special script: Rate Reduction approach'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    list_description = VALUES(list_description);

-- Create List 120: Training Campaign
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    120, 
    'Training - Aged Valid Leads', 
    'Auto2',  -- IMPORTANT: Assign to Auto2 campaign
    'Y',
    'Days 40-85 heavily worked leads for agent training. Still within TCPA.'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    campaign_id = 'Auto2',  -- Ensure it's on Auto2
    list_description = VALUES(list_description);

-- Create List 199: TCPA Graveyard
INSERT INTO vicidial_lists (list_id, list_name, campaign_id, active, list_description)
VALUES (
    199, 
    'TCPA Expired - Special Campaigns Only', 
    'AutoDial',  -- Default campaign, but will be managed specially
    'N',  -- INACTIVE by default
    'TCPA expired leads (89+ days). NON-ALLSTATE CAMPAIGNS ONLY. Special permission required.'
)
ON DUPLICATE KEY UPDATE 
    list_name = VALUES(list_name),
    active = 'N',  -- Keep inactive
    list_description = VALUES(list_description);

-- =====================================================
-- POPULATE LIST 112: NI RETARGETING
-- =====================================================

-- Move NI status leads that have cooled off for 45+ days
INSERT INTO vicidial_list (
    lead_id, first_name, last_name, phone_number, 
    address1, city, state, postal_code, 
    list_id, status, entry_date, vendor_lead_code,
    source_id, called_since_last_reset
)
SELECT 
    lead_id, first_name, last_name, phone_number,
    address1, city, state, postal_code,
    112 as list_id,  -- Move to List 112
    'NEW' as status,  -- Reset status for fresh attempt
    NOW() as entry_date,
    vendor_lead_code,
    source_id,
    'N' as called_since_last_reset
FROM vicidial_list
WHERE status = 'NI'  -- Not Interested status
    AND list_id BETWEEN 101 AND 111  -- From active lists
    AND DATEDIFF(NOW(), modify_date) >= 45  -- 45+ days since last contact
    AND DATEDIFF(NOW(), entry_date) < 85  -- Still within TCPA window
    AND NOT EXISTS (  -- Not already in List 112
        SELECT 1 FROM vicidial_list vl2 
        WHERE vl2.phone_number = vicidial_list.phone_number 
        AND vl2.list_id = 112
    )
LIMIT 10000;  -- Process in batches

-- =====================================================
-- POPULATE LIST 120: TRAINING LEADS
-- =====================================================

-- Move heavily-worked but valid leads for training
INSERT INTO vicidial_list (
    lead_id, first_name, last_name, phone_number, 
    address1, city, state, postal_code, 
    list_id, status, entry_date, vendor_lead_code,
    source_id, called_since_last_reset, campaign_id
)
SELECT 
    lead_id, first_name, last_name, phone_number,
    address1, city, state, postal_code,
    120 as list_id,  -- Move to List 120
    status,  -- Keep current status
    NOW() as entry_date,
    vendor_lead_code,
    source_id,
    'N' as called_since_last_reset,
    'Auto2' as campaign_id  -- ASSIGN TO AUTO2
FROM vicidial_list vl
WHERE list_id IN (107, 108, 109)  -- From later-stage lists
    AND DATEDIFF(NOW(), entry_date) BETWEEN 40 AND 85  -- Days 40-85
    AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')  -- Not successful/DNC
    AND (
        SELECT COUNT(*) 
        FROM vicidial_dial_log vdl 
        WHERE vdl.lead_id = vl.lead_id
        AND vdl.status IN ('A','AA','AL','AM','B','NA','DROP','PDROP',
                          'SALE','NI','DNC','XFER','CALLBK','DC','ADCT')
    ) > 30  -- Heavily worked (30+ real calls)
    AND NOT EXISTS (  -- Not already in List 120
        SELECT 1 FROM vicidial_list vl2 
        WHERE vl2.phone_number = vl.phone_number 
        AND vl2.list_id = 120
    )
LIMIT 5000;  -- Process in batches

-- =====================================================
-- POPULATE LIST 199: TCPA GRAVEYARD
-- =====================================================

-- Move ALL expired leads to graveyard (daily sweep)
UPDATE vicidial_list
SET 
    list_id = 199,
    status = 'TCPAEXP',
    called_since_last_reset = 'Y',  -- Prevent accidental dialing
    modify_date = NOW(),
    comments = CONCAT(IFNULL(comments, ''), ' | TCPA EXPIRED: ', DATE(NOW()))
WHERE DATEDIFF(NOW(), entry_date) >= 89  -- 89+ days old
    AND list_id != 199  -- Not already in graveyard
    AND list_id BETWEEN 101 AND 120;  -- From active lists

-- =====================================================
-- REPORTING QUERIES
-- =====================================================

-- Check List 112 (NI Retargeting) population
SELECT 
    'List 112 - NI Retargeting' as list_name,
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'NEW' THEN 1 ELSE 0 END) as ready_to_dial,
    MIN(DATEDIFF(NOW(), entry_date)) as newest_lead_days,
    MAX(DATEDIFF(NOW(), entry_date)) as oldest_lead_days
FROM vicidial_list
WHERE list_id = 112;

-- Check List 120 (Training) population
SELECT 
    'List 120 - Training Leads' as list_name,
    COUNT(*) as total_leads,
    COUNT(DISTINCT phone_number) as unique_numbers,
    AVG(call_count) as avg_calls_made,
    'Auto2' as assigned_campaign
FROM vicidial_list
WHERE list_id = 120;

-- Check List 199 (TCPA Graveyard) population
SELECT 
    'List 199 - TCPA Graveyard' as list_name,
    COUNT(*) as total_leads,
    MIN(entry_date) as oldest_lead_date,
    MAX(entry_date) as newest_lead_date,
    'INACTIVE - Special Campaigns Only' as status
FROM vicidial_list
WHERE list_id = 199;

