# ViciDial Optimal Timing Implementation Guide

## The Challenge
ViciDial doesn't have built-in "call at 10 AM" functionality. Instead, it uses:
- **Hopper:** Queue of leads ready to dial
- **Called Since Last Reset:** Flag controlling if lead can be called again
- **Dial Status:** Which leads are available

## How ViciDial Actually Works

### Key Fields in vicidial_list Table:
```sql
- called_since_last_reset: 'Y' = already called, 'N' = ready to call
- call_count: Number of times called
- last_local_call_time: When last called
- status: Current disposition (A, NA, NI, etc.)
```

## Our Solution: Timing Control Script

### The Script Does 3 Things:

#### 1. Controls WHEN Leads Are Available
```sql
-- At 10 AM: Make List 151 available
UPDATE vicidial_list 
SET called_since_last_reset = 'N'
WHERE list_id = 151

-- At 11 AM: Make them unavailable again
UPDATE vicidial_list 
SET called_since_last_reset = 'Y'
WHERE list_id = 151
```

#### 2. Controls HOW OFTEN Leads Are Called
```sql
-- Only reset if not called today
WHERE last_local_call_time < CURDATE()
OR TIMESTAMPDIFF(HOUR, last_local_call_time, NOW()) >= 24
```

#### 3. Moves Leads Between Lists
```sql
-- After 5 calls in List 150, move to List 151
UPDATE vicidial_list 
SET list_id = 151
WHERE list_id = 150 AND call_count >= 5
```

## Implementation for Each List

### List 150 (Day 1 - Golden Hour)
**Goal:** 5 calls in first 4 hours
**How it works:**
- Always available (called_since_last_reset = 'N')
- ViciDial calls them rapidly
- After 5 calls, automatically moved to List 151
- **No special timing needed** - urgency is key

### List 151 (Day 2 - Momentum)
**Goal:** 2 calls at 10 AM and 2 PM
**How it works:**
```
9:55 AM → Script sets called_since_last_reset = 'N'
10:00 AM → ViciDial starts calling
11:00 AM → Script sets called_since_last_reset = 'Y'
1:55 PM → Script sets called_since_last_reset = 'N'
2:00 PM → ViciDial calls again
3:00 PM → Script sets called_since_last_reset = 'Y'
```

### List 152 (Days 3-5 - Persistence)
**Goal:** 1 call per day at optimal times
**How it works:**
```
10:00 AM → Check if called today
           If not → Set called_since_last_reset = 'N'
           Limit to 100 leads (controlled flow)
12:00 PM → Set all back to 'Y'
2:00 PM → Same check (for leads not reached in morning)
4:00 PM → Set all back to 'Y'
```

### List 153 (Days 6-10 - Final)
**Goal:** 2 calls total, 3 days apart
**How it works:**
```
Check: TIMESTAMPDIFF(DAY, last_call, NOW()) >= 3
If true → Make available for one optimal window
After called → Wait another 3 days
```

## Setting Up in ViciDial

### 1. Create the Lists in ViciDial Admin:
```
List 150: "Test B - Day 1 Golden Hour"
List 151: "Test B - Day 2 Momentum"
List 152: "Test B - Days 3-5 Persistence"
List 153: "Test B - Days 6-10 Final"
List 160: "Test B - NI Retargeting"
```

### 2. Set Campaign Settings:
```
Campaign: AUTODIAL
- Add Lists: 150, 151, 152, 153, 160
- Dial Method: RATIO
- Auto Dial Level: 2.0 (or your current setting)
- Drop Call Seconds: 5
```

### 3. Set List Mix (Important!):
```
In Campaign Detail:
List Order: DOWN COUNT 2nd NEW
List Mix: 
- List 150: 90%  (Highest priority)
- List 151: 70%
- List 152: 50%
- List 153: 30%
- List 160: 20%  (Lowest priority)
```

### 4. Add to Cron (Every 5 Minutes):
```bash
*/5 * * * * cd /path/to/brain && php artisan vici:optimal-timing >> /var/log/vici_timing.log 2>&1
```

## Why This Works

1. **List 150** leads are always available = immediate calling
2. **List 151-153** only become available during optimal windows
3. **called_since_last_reset** flag controls hopper loading
4. **Automatic progression** moves leads through lists
5. **No campaign changes needed** - same AutoDial for all

## Testing the System

### Check if working:
```sql
-- See how many leads are available right now
SELECT list_id, COUNT(*) as available
FROM vicidial_list
WHERE called_since_last_reset = 'N'
AND status IN ('NEW', 'NA', 'B', 'N')
GROUP BY list_id;

-- See last call times
SELECT list_id, 
       COUNT(*) as total,
       SUM(CASE WHEN DATE(last_local_call_time) = CURDATE() THEN 1 ELSE 0 END) as called_today
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153)
GROUP BY list_id;
```

## Alternative: Manual Reset Times

If you prefer, you can use ViciDial's built-in reset times:
```
Admin → Lists → List 151 → Modify
Reset Time: 1000,1400  (10 AM and 2 PM)
```

But this resets ALL leads in the list, not just the ones that need calling.

## The Key Insight

**ViciDial doesn't schedule calls, it dials what's available.**

Our script makes leads available at the right times, creating the illusion of scheduled calling.

---

*This approach gives you precise control over when Test B leads are called without changing campaign settings or affecting Test A leads.*




