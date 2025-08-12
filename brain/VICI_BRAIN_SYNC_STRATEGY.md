# Vici-Brain Synchronization Strategy
*Last Updated: August 11, 2025*

## The Problem
- Leads have been going directly to Vici for months/years
- Now importing the same leads into The Brain will create duplicates
- Need to sync both systems without disrupting operations

## Two-Way Sync Strategy

### Phase 1: Import Vici Leads INTO Brain (Reverse Import)
Instead of importing CSV into Brain first, we should:

1. **Extract from Vici First**
   - Query Vici database for all leads in Auto2/Autodial campaigns
   - Get their existing `lead_id` from Vici
   - Import these INTO Brain with proper `external_lead_id`

2. **Mark as Vici-Origin**
   ```php
   $leadData = [
       'external_lead_id' => generateLeadId(),
       'meta' => json_encode([
           'vici_lead_id' => $viciLead->lead_id,
           'origin' => 'vici_import',
           'synced_at' => now()
       ])
   ];
   ```

### Phase 2: Update Vici with External IDs
After Brain has all the leads:
1. Update Vici's `vendor_lead_code` with Brain's `external_lead_id`
2. This creates the two-way link

### Phase 3: Handle New Incoming Leads
Going forward, new leads will:
1. Come into Brain first (via webhook)
2. Get assigned `external_lead_id`
3. Push to Vici with `vendor_lead_code` set

## Implementation Commands

### Option A: Vici-First Import (RECOMMENDED)
```bash
# 1. Import FROM Vici into Brain
php artisan vici:import-to-brain --campaigns=Auto2,Autodial

# 2. Then update Vici with vendor codes
php artisan vici:update-vendor-codes --campaigns=Auto2,Autodial
```

### Option B: CSV Import with Vici Matching
```bash
# Import CSV but match against existing Vici leads
php artisan leads:import-csv file.csv --match-vici --update-existing
```

## Duplicate Resolution Logic

### For Each Lead in CSV:
1. **Check Vici First**
   - Query by phone number
   - If exists: Import to Brain with Vici's `lead_id` in metadata
   - If not: Create new in both systems

2. **Prevent Double-Calling**
   - If lead exists in Vici with recent call (< 30 days)
   - Mark as `DO_NOT_CALL` in Brain
   - Skip re-import to Vici

3. **Merge Strategy**
   ```php
   // Check Vici first
   $viciLead = queryVici($phone);
   
   if ($viciLead) {
       // Lead exists in Vici
       $brainLead = Lead::updateOrCreate(
           ['phone' => $phone],
           [
               'external_lead_id' => $viciLead->vendor_lead_code ?: generateLeadId(),
               'meta' => json_encode([
                   'vici_lead_id' => $viciLead->lead_id,
                   'vici_status' => $viciLead->status,
                   'last_call_date' => $viciLead->last_call_time
               ])
           ]
       );
       
       // Update Vici if needed
       if (!$viciLead->vendor_lead_code) {
           updateViciVendorCode($viciLead->lead_id, $brainLead->external_lead_id);
       }
   } else {
       // New lead - create in both
       $brainLead = Lead::create($leadData);
       pushToVici($brainLead);
   }
   ```

## Critical Considerations

### 1. Vici Status Mapping
- `NEW` - Never called
- `DROP` - Called but no agent
- `NA` - No answer
- `SALE` - Converted
- `DNC` - Do not call

### 2. Prevent Duplicate Calls
- Check Vici's `last_local_call_time`
- If called within 30 days, don't re-queue
- Mark as `recently_called` in Brain

### 3. Campaign Preservation
- Keep leads in their original Vici campaigns
- Don't move between Auto2/Autodial during sync

## Recommended Approach

### Step 1: Audit Current State
```sql
-- In Vici
SELECT COUNT(*), campaign_id, 
       COUNT(DISTINCT phone_number) as unique_phones
FROM vicidial_list 
WHERE campaign_id IN ('Auto2', 'Autodial')
GROUP BY campaign_id;

-- Check for vendor_lead_code usage
SELECT COUNT(*) as with_vendor_code
FROM vicidial_list 
WHERE vendor_lead_code IS NOT NULL 
  AND vendor_lead_code != ''
  AND campaign_id IN ('Auto2', 'Autodial');
```

### Step 2: Test with Small Batch
1. Export 100 leads from Vici
2. Import to Brain with matching
3. Verify no duplicate calling
4. Check vendor_code updates

### Step 3: Full Sync
1. Run full Vici → Brain import
2. Update all vendor_lead_codes
3. Switch to Brain-first flow for new leads

## Emergency Rollback

If issues arise:
1. Stop webhook processing
2. Clear Brain's leads table (keep backup)
3. Continue Vici-only operation
4. Fix issues and retry

## Success Metrics

- Zero duplicate calls to same number within 30 days
- All Vici leads have `vendor_lead_code`
- All Brain leads have `vici_lead_id` in meta
- New leads flow: Brain → Vici (not direct to Vici)

---

*This strategy prevents duplicate calling while establishing Brain as the source of truth.*

