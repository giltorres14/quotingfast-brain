# ViciDial to Brain Sync Breakdown
**Date: August 21, 2025**

## What Will Happen During Sync

### Step 1: ViciDial → Brain Matching
The sync will pull each lead from ViciDial and try to match it with Brain leads by phone number.

### Expected Results:

#### Lists After Sync:

**Brain List 6018:**
- Currently: ~1 lead
- After Sync: **5,893 leads** (matching ViciDial)

**Brain List 6019:**
- Currently: ~1 lead  
- After Sync: **5,813 leads** (matching ViciDial)

**Brain List 6020:**
- Currently: 0 leads
- After Sync: **14,772 leads** (matching ViciDial)

**Brain List 6021:**
- Currently: 0 leads
- After Sync: **12,197 leads** (matching ViciDial)

**Brain List 6022:**
- Currently: 0 leads
- After Sync: **13,500 leads** (matching ViciDial)

**Brain List 6023:**
- Currently: ~1 lead
- After Sync: **9,547 leads** (matching ViciDial)

**Brain List 6024:**
- Currently: 0 leads
- After Sync: **23,774 leads** (matching ViciDial)

**Brain List 6025:**
- Currently: 0 leads
- After Sync: **14,844 leads** (matching ViciDial)

**Brain List 6026:**
- Currently: ~46 leads
- After Sync: **1,575 leads** (matching ViciDial)

### What Happens to List 0?

**Current List 0:** 227,715 leads

The sync will:
1. Take each ViciDial lead (101,915 total)
2. Search for matching phone numbers in Brain's List 0
3. When found: Move that lead from List 0 → appropriate list (6018-6026)
4. When not found: Import the ViciDial lead as new

**After Sync:**
- **~101,915 leads** will be moved from List 0 to lists 6018-6026
- **~125,800 leads** will remain in List 0 (these don't match ViciDial)

### Summary of Changes:

| List | Before Sync (Brain) | After Sync (Brain) | Source |
|------|-------------------|-------------------|---------|
| 6018 | 1 | 5,893 | From ViciDial |
| 6019 | 1 | 5,813 | From ViciDial |
| 6020 | 0 | 14,772 | From ViciDial |
| 6021 | 0 | 12,197 | From ViciDial |
| 6022 | 0 | 13,500 | From ViciDial |
| 6023 | 1 | 9,547 | From ViciDial |
| 6024 | 0 | 23,774 | From ViciDial |
| 6025 | 0 | 14,844 | From ViciDial |
| 6026 | 46 | 1,575 | From ViciDial |
| **List 0** | **227,715** | **~125,800** | Remaining unmatched |
| **Total** | **242,243** | **242,243+** | May increase if new imports |

## Important Notes:

1. **Matching is by phone number** - The sync matches ViciDial leads with Brain leads using phone numbers
2. **List 0 will shrink** - About 101,915 leads will move from List 0 to their proper lists
3. **New leads may be added** - If ViciDial has leads not in Brain, they'll be imported
4. **External IDs will be set** - Each matched lead gets the ViciDial lead_id as external_id
5. **Unmatched leads stay in List 0** - The ~125,800 leads that don't match ViciDial remain in List 0

## The Unmatched Leads
The ~125,800 leads remaining in List 0 after sync are leads that:
- Were manually imported to Brain
- Don't exist in ViciDial lists 6018-6026
- May belong to other ViciDial lists (101-111, 150-153, etc.)
- May be duplicates or test data

## Next Steps After Sync:
1. Verify the sync worked correctly
2. Decide what to do with remaining List 0 leads
3. Export synced leads if needed for ViciDial import
4. Set up regular sync schedule


