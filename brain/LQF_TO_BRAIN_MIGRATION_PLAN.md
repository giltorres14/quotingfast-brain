# 📋 LEAD MIGRATION PLAN: LQF → BRAIN → VICI
*Complete strategy for migrating lead flow and updating historical data*

---

## 🎯 OBJECTIVE
Migrate all lead flow from LeadsQuotingFast (LQF) to Brain system, assign Brain Lead IDs, and update all leads in Vici with their corresponding Brain IDs.

---

## 📊 CURRENT SITUATION

### What We Have
- **1,539 leads** already in Brain database (from previous Allstate testing)
- **3 months of leads** in Vici WITHOUT Brain IDs
- **Working Vici API** (apiuser/UZPATJ59GJAVKG8ES6)
- **Active webhooks** ready to receive from LQF
- **Update functionality** tested and working

### What Needs to Happen
1. Point LQF webhooks to Brain
2. Ensure all new leads get Brain IDs
3. Update historical Vici leads with Brain IDs
4. Verify agents can access leads via Brain

---

## 🚀 PHASE 1: NEW LEAD FLOW (Immediate)

### Step 1: Update LQF Webhook Configuration
**Action**: Change webhook URL in LeadsQuotingFast

**OLD URL** (Direct to Vici):
```
https://vici-server.com/webhook
```

**NEW URL** (To Brain):
```
https://quotingfast-brain-ohio.onrender.com/webhook.php
```

### Step 2: Lead Flow Process
```
1. LQF sends lead → Brain webhook
2. Brain assigns unique ID (13-digit timestamp)
3. Brain stores complete lead data
4. Brain pushes to Vici List 101 with vendor_lead_code = "BRAIN_{id}"
5. Vici agents see lead with Brain ID
```

### Step 3: Test with Small Batch
```bash
# 1. Update LQF webhook to Brain
# 2. Send 10 test leads
# 3. Verify in Brain:
curl https://quotingfast-brain-ohio.onrender.com/leads

# 4. Verify in Vici List 101
# 5. Confirm agents can access
```

---

## 📚 PHASE 2: HISTORICAL DATA UPDATE (This Week)

### Step 1: Export Existing Vici Leads
**Option A: Via API** (Preferred)
```php
// Use the Vici API to fetch all leads from List 101
// Script: export_vici_leads.php
$params = [
    'source' => 'brain',
    'user' => 'apiuser',
    'pass' => 'UZPATJ59GJAVKG8ES6',
    'function' => 'search_leads',
    'list_id' => '101',
    'records_per_page' => '1000'
];
```

**Option B: Via CSV Export**
```bash
# Export from Vici admin panel
# Download CSV of all leads from past 3 months
```

### Step 2: Import to Brain Database
```bash
# Run import script
php artisan leads:import vici_export.csv --source=vici_historical

# This will:
# - Create Brain records for each Vici lead
# - Assign Brain IDs
# - Store original Vici lead_id as external_lead_id
```

### Step 3: Update Vici with Brain IDs
```bash
# Dry run first (test with 10 leads)
php artisan vici:update-vendor-codes --dry-run --batch=10

# Review results, then run full update
php artisan vici:update-vendor-codes --start-date=2024-10-01

# This updates vendor_lead_code in Vici to "BRAIN_{id}"
```

---

## 🔄 PHASE 3: VERIFICATION (Day 2-3)

### Step 1: Verify Data Integrity
```sql
-- Check Brain database
SELECT COUNT(*) FROM leads WHERE source = 'vici_historical';
SELECT COUNT(*) FROM leads WHERE external_lead_id IS NOT NULL;

-- Should match number of Vici leads
```

### Step 2: Spot Check Leads
```bash
# Pick 10 random leads and verify:
# 1. Lead exists in Brain
# 2. Vici has correct vendor_lead_code
# 3. Agent can access via Brain interface
```

### Step 3: Monitor New Lead Flow
```bash
# Check webhook status
curl https://quotingfast-brain-ohio.onrender.com/webhook/status

# Monitor logs for any failures
tail -f storage/logs/laravel.log
```

---

## 🛠️ IMPLEMENTATION SCRIPTS

### 1. Import Historical Vici Leads
```php
<?php
// import_vici_historical.php

// Step 1: Get all Vici leads without Brain IDs
$viciLeads = getViciLeadsWithoutBrainIds();

// Step 2: For each lead, create Brain record
foreach ($viciLeads as $viciLead) {
    $brainLead = Lead::create([
        'external_lead_id' => $viciLead->lead_id,
        'name' => $viciLead->first_name . ' ' . $viciLead->last_name,
        'first_name' => $viciLead->first_name,
        'last_name' => $viciLead->last_name,
        'phone' => $viciLead->phone_number,
        'email' => $viciLead->email,
        'address' => $viciLead->address1,
        'city' => $viciLead->city,
        'state' => $viciLead->state,
        'zip_code' => $viciLead->postal_code,
        'source' => 'vici_historical',
        'created_at' => $viciLead->entry_date
    ]);
    
    echo "Created Brain Lead {$brainLead->id} for Vici {$viciLead->lead_id}\n";
}
```

### 2. Update Vici Vendor Codes
```php
<?php
// update_vici_vendor_codes.php

// Get all Brain leads with external_lead_id
$brainLeads = Lead::whereNotNull('external_lead_id')->get();

foreach ($brainLeads as $lead) {
    // Update Vici lead with Brain ID
    $params = [
        'source' => 'brain',
        'user' => 'apiuser',
        'pass' => 'UZPATJ59GJAVKG8ES6',
        'function' => 'update_lead',
        'lead_id' => $lead->external_lead_id,
        'vendor_lead_code' => "BRAIN_{$lead->id}"
    ];
    
    $response = callViciApi($params);
    echo "Updated Vici lead {$lead->external_lead_id} with BRAIN_{$lead->id}\n";
}
```

---

## 📅 TIMELINE

### Day 1 (Today/Tomorrow)
- [ ] Update LQF webhook URL to Brain
- [ ] Test with 10 new leads
- [ ] Verify flow working end-to-end

### Day 2-3
- [ ] Export historical Vici leads
- [ ] Import to Brain database
- [ ] Run vendor code update (test batch)

### Day 4-5
- [ ] Complete full vendor code update
- [ ] Verify all leads accessible
- [ ] Train agents on Brain interface

### Week 2
- [ ] Monitor system stability
- [ ] Address any issues
- [ ] Begin Twilio integration

---

## ✅ SUCCESS CRITERIA

1. **New Leads**: All flowing through Brain → Vici
2. **Historical Leads**: All have Brain IDs in vendor_lead_code
3. **Agents**: Can access all leads via Brain interface
4. **Data Integrity**: No lost or duplicate leads
5. **Performance**: <1 second webhook processing

---

## 🚨 ROLLBACK PLAN

If issues arise:

### Quick Rollback (< 5 minutes)
```bash
# Point LQF webhook back to Vici directly
# Agents continue using Vici interface
# No data loss
```

### Data Recovery
```bash
# All leads stored in Brain database
# Can re-push to Vici if needed
php artisan leads:push-to-vici --all
```

---

## 📞 TESTING CHECKLIST

### Before Go-Live
- [ ] Test webhook with 10 leads
- [ ] Verify Vici receives all fields
- [ ] Check vendor_lead_code format
- [ ] Test agent access
- [ ] Verify no duplicates

### After Go-Live
- [ ] Monitor first 100 leads
- [ ] Check error logs
- [ ] Verify lead counts match
- [ ] Test agent workflow
- [ ] Confirm data integrity

---

## 🔧 COMMANDS REFERENCE

### Check Current Status
```bash
# Lead count in Brain
curl -s https://quotingfast-brain-ohio.onrender.com/leads | grep "stat-number"

# Test Vici connection
php test_vici_correct_api_creds.php

# Check webhook status
curl https://quotingfast-brain-ohio.onrender.com/webhook/status
```

### Update Historical Leads
```bash
# Test update (10 leads)
php artisan vici:update-vendor-codes --dry-run --batch=10

# Full update
php artisan vici:update-vendor-codes

# Update specific date range
php artisan vici:update-vendor-codes --start-date=2024-10-01 --end-date=2024-12-31
```

### Monitor Progress
```bash
# Watch logs
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> Lead::where('source', 'vici_historical')->count()
>>> Lead::whereNotNull('external_lead_id')->count()
```

---

## 📊 EXPECTED RESULTS

### After Phase 1
- New leads flow: LQF → Brain → Vici
- Each lead has unique Brain ID
- Agents see vendor_lead_code = "BRAIN_XXXX"

### After Phase 2
- ~3 months of historical leads updated
- All Vici leads have Brain IDs
- Complete lead history in Brain database

### After Phase 3
- 100% leads trackable via Brain ID
- Full audit trail
- Ready for advanced features (Twilio, analytics)

---

## 🎯 NEXT STEPS

### Immediate Action Required
1. **Get LQF webhook URL** - Where to update it?
2. **Confirm Vici lead count** - How many historical leads?
3. **Schedule migration window** - Best time for update?

### Questions to Answer
1. Does LQF have multiple webhook URLs (auto, home, etc.)?
2. Are there any custom fields we need to preserve?
3. What's the agent training timeline?

---

*This plan ensures ZERO data loss and minimal disruption while migrating to the Brain system.*


