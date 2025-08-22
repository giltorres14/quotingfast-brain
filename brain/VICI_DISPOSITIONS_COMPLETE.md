# ViciDial Complete Disposition Reference

## Agent-Selected Dispositions (Manual)
These are set by agents when they handle a call:

### ✅ POSITIVE OUTCOMES (Stop Calling)
- **XFER** - Call Transferred (SALE!)
- **XFERA** - Transfer to Allstate (SALE!)
- **CALLBK** - Call Back (scheduled callback)

### ❌ NEGATIVE OUTCOMES (Stop Calling)
- **DNC** - DO NOT CALL (legal requirement)
- **DNCL** - DO NOT CALL Hopper Sys Match
- **DC** - Disconnected Number
- **DNQ** - Does Not Qualify

### 🔄 CONTINUE CALLING (Dialable)
- **A** - Answering Machine
- **B** - Busy
- **N** - No Answer
- **NI** - Not Interested (special handling after 30 days)
- **LVM** - Left Voice Mail
- **DAIR** - Dead Air
- **BLOCK** - BLOCK CALLER

## Auto-Dialer Dispositions (System Generated)
These are set automatically by the dialer:

### 🤖 AUTOMATED STATUSES
- **NA** - No Answer AutoDial (most common)
- **AB** - Busy Auto
- **ADC** - Disconnected Number Auto
- **ADCT** - Disconnected Number Temporary
- **DROP** - Agent Not Available (dropped call)
- **PDROP** - Outbound Pre-Routing Drop
- **TIMEOT** - Inbound Queue Timeout Drop

### 📞 CALL STATES
- **NEW** - New Lead (not called yet)
- **PU** - Call Picked Up
- **ERI** - Agent Error
- **RQXFER** - Re-Queue
- **DEC** - Declined Sale

## Lead Flow Movement Logic

### Test A - Current Lists (101-111)

#### List 101 → 102 (After 1st Call)
```sql
-- Move after ANY first dial attempt that's not a success
WHERE list_id = 101 
  AND call_count >= 1 
  AND status IN ('NA','A','B','AB','ADC','ADCT','N','DROP','PDROP','DAIR')
  AND status NOT IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
```

#### List 102 → 103 (After 3 No-Contact Attempts)
```sql
-- Move to voicemail list after 3 attempts with no human contact
WHERE list_id = 102
  AND call_count >= 3
  AND status IN ('NA','A','AB','N','DROP','PDROP','TIMEOT')
  AND hours_since_entry < 24
  -- Could also check: never had status PU (picked up)
```

#### List 103 → 104 (Day 2 Intensive)
```sql
-- Move after Day 1 complete
WHERE list_id = 103
  AND call_count >= 5
  AND hours_since_entry >= 24
  AND status NOT IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
```

### Test B - Optimized Lists (150-153)

#### List 150 → 151 (After Golden Hour)
```sql
-- After 5 intensive calls in first 4 hours
WHERE list_id = 150
  AND call_count >= 5
  AND status NOT IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
```

#### List 151 → 152 (After Day 2)
```sql
-- After 2 strategic calls on Day 2
WHERE list_id = 151
  AND call_count >= 7
  AND status NOT IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
  AND TIMESTAMPDIFF(HOUR, last_call_time, NOW()) >= 20
```

## Important Groupings for Logic

### 🛑 STOP CALLING (Terminal Statuses)
```sql
status IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
```

### 📞 NO HUMAN CONTACT (Machine/No Answer)
```sql
status IN ('NA','A','AB','N','DROP','PDROP','TIMEOT','DAIR')
```

### 👤 HUMAN CONTACT (But No Sale)
```sql
status IN ('NI','B','CALLBK','LVM','BLOCK','DEC','ERI')
```

### 🔄 KEEP TRYING (Dialable Statuses)
```sql
status IN ('NA','A','B','N','NI','LVM','DAIR','BLOCK','AB','NEW')
```

## Special Handling

### NI (Not Interested) Strategy
- Continue calling for first 10-12 attempts
- After max attempts, wait 30 days
- Move to List 160 for retargeting with different script
- Use "rate reduction" messaging

### Answering Machine (A) Logic
- First 3 times: Just mark as A, keep calling
- 4th time: Leave voicemail (if Day 5+)
- 5th+ times: Skip, too many machines

### DROP/PDROP Handling
- These indicate system dropped the call
- Should be called back ASAP (high priority)
- Don't count as "real" attempts

## Recommended Test A Movement Updates

### List 102 → 103 (CORRECTED)
```sql
UPDATE vicidial_list 
SET list_id = 103
WHERE list_id = 102
  AND call_count >= 3
  AND (
    -- No human contact scenarios
    (status IN ('NA','A','AB','N','DROP','PDROP','TIMEOT','DAIR'))
    OR 
    -- Multiple answering machines
    (status = 'A' AND call_count >= 3)
  )
  AND hours_since_entry < 24
  AND status NOT IN ('XFER','XFERA','DNC','DNCL','DC','DNQ')
```

This accounts for ALL the automated dispositions and various no-contact scenarios!





