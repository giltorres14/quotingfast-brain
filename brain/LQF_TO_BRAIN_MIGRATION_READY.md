# 🚀 LQF TO BRAIN MIGRATION - READY TO EXECUTE

## ✅ EVERYTHING IS PREPARED AND TESTED

### 📋 **PHASE 1: INITIAL CSV IMPORT** (Ready)
**Purpose:** Import historical leads from LQF CSV export with STRICT duplicate prevention

#### Commands Ready:
```bash
# 1. First do a dry run to see what will be imported
php artisan leads:import-strict /path/to/lqf_export.csv --dry-run --show-duplicates

# 2. Review the output, then do actual import
php artisan leads:import-strict /path/to/lqf_export.csv

# 3. Verify imported leads
# Check at: https://quotingfast-brain-ohio.onrender.com/leads
```

**What This Does:**
- ✅ Imports ONLY new phone numbers (strict no duplicates)
- ✅ Generates 13-digit Brain Lead IDs for each
- ✅ Preserves all LQF data (drivers, vehicles, policies)
- ✅ Shows detailed statistics and duplicate info

---

### 📋 **PHASE 2: UPDATE VICI WITH BRAIN IDs** (Ready)
**Purpose:** Update all Vici leads with their Brain Lead IDs

#### Commands Ready:
```bash
# 1. Push new leads to Vici (if needed)
php artisan vici:push-new-leads

# 2. Update vendor_lead_codes in Vici with Brain IDs
php artisan vici:update-vendor-codes --dry-run
php artisan vici:update-vendor-codes

# Note: This uses the API since MySQL is blocked
```

**What This Does:**
- ✅ Updates vendor_lead_code field in Vici
- ✅ Links Vici leads to Brain Lead IDs
- ✅ Enables agent iframe to show correct lead

---

### 📋 **PHASE 3: SWITCH WEBHOOKS TO BRAIN** (Ready)
**Purpose:** Route all new leads through Brain first

#### LQF Webhook Updates Needed:
```
OLD: https://your-lqf-webhook.com/webhook
NEW: https://quotingfast-brain-ohio.onrender.com/webhook.php

OLD: https://your-lqf-webhook.com/auto
NEW: https://quotingfast-brain-ohio.onrender.com/webhook/auto

OLD: https://your-lqf-webhook.com/home  
NEW: https://quotingfast-brain-ohio.onrender.com/webhook/home
```

**Lead Flow After Switch:**
```
LQF → Brain (assigns ID) → Vici (with Brain ID) → Agents
```

---

### 📋 **PHASE 4: MIGRATE AGENTS TO BRAIN IFRAME** (Ready)
**Purpose:** Agents see enriched lead data from Brain

#### Vici Configuration:
```
OLD Webform: http://old-system/lead.php?lead_id=--A--vendor_lead_code--B--
NEW Webform: https://quotingfast-brain-ohio.onrender.com/agent/lead/--A--vendor_lead_code--B--
```

**What Agents Get:**
- ✅ Full lead details with all fields
- ✅ Qualification form (Top 13 Questions)
- ✅ Allstate transfer capability
- ✅ Lead history and notes

---

## 🔄 **DUPLICATE HANDLING LOGIC** (Implemented)

### For CSV Import (Phase 1):
- **STRICT MODE**: Any duplicate phone = SKIP completely
- No updates, no re-engagement during initial import
- Clean slate approach

### For Ongoing Webhooks (Phase 3+):
- **0-10 days old**: Update existing lead
- **11-90 days old**: Create re-engagement lead
- **91+ days old**: Create as new lead

---

## 📊 **MONITORING & VERIFICATION**

### Dashboard URLs:
- **Leads**: https://quotingfast-brain-ohio.onrender.com/leads
- **Diagnostics**: https://quotingfast-brain-ohio.onrender.com/diagnostics
- **API Directory**: https://quotingfast-brain-ohio.onrender.com/api
- **Webhook Status**: https://quotingfast-brain-ohio.onrender.com/webhook/status

### Key Metrics to Watch:
1. Lead count in Brain matches CSV import
2. Vici shows updated vendor_lead_codes
3. New leads flowing through webhooks
4. Agents can access lead details

---

## 🎯 **EXECUTION CHECKLIST**

When you're ready, we'll execute in this order:

### Day 1 - Import & Verify
- [ ] Run CSV import (dry run first)
- [ ] Verify imported lead count
- [ ] Check for any issues
- [ ] Run actual import if dry run looks good

### Day 2 - Update Vici
- [ ] Update Vici vendor codes
- [ ] Test a few leads manually
- [ ] Verify agents can see leads

### Day 3 - Switch Traffic
- [ ] Update LQF webhook URLs
- [ ] Monitor first few leads
- [ ] Verify Vici integration working
- [ ] Update agent webforms

### Day 4 - Full Migration
- [ ] All agents using Brain iframe
- [ ] Monitor for 24 hours
- [ ] Address any issues

---

## 🛠️ **TROUBLESHOOTING READY**

### If Duplicates Found During Import:
```bash
# Show detailed duplicate info
php artisan leads:import-strict file.csv --dry-run --show-duplicates

# These will be SKIPPED - working as designed
```

### If Vici Update Fails:
```bash
# Test with single lead first
php test_update_real_lead.php

# Check API credentials
php test_vici_correct_api_creds.php
```

### If Webhooks Don't Work:
```bash
# Test webhook manually
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"phone":"5551234567","first_name":"Test","last_name":"Lead"}'

# Check logs
tail -f storage/logs/laravel.log
```

---

## ✅ **READY STATUS**

| Component | Status | Notes |
|-----------|--------|-------|
| CSV Import Script | ✅ READY | Strict duplicate prevention implemented |
| Vici Update Commands | ✅ READY | API integration tested and working |
| Webhook Endpoints | ✅ READY | All 3 endpoints live and tested |
| Duplicate Logic | ✅ READY | Phone-based checking implemented |
| Agent Interface | ✅ READY | Iframe pages working |
| Monitoring Tools | ✅ READY | Diagnostics dashboard deployed |
| Documentation | ✅ READY | All processes documented |

---

## 📝 **FINAL NOTES**

1. **CSV Import is ONE-TIME**: The strict import is for initial migration only
2. **Ongoing leads use smart logic**: After migration, the 0-10-90 day logic applies
3. **No data loss**: All skipped duplicates are logged and reported
4. **Reversible**: We can track what was imported via source='LQF_CSV'

---

## 🚦 **WE ARE GO FOR LAUNCH**

Everything is tested, documented, and ready. Just give the word and we'll begin with Phase 1!

**Your next command will be:**
```bash
php artisan leads:import-strict /path/to/your_lqf_export.csv --dry-run --show-duplicates
```

Then review and proceed with actual import when satisfied.
