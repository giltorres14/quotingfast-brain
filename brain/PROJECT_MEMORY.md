# 🧠 BRAIN PROJECT MEMORY
## Living Documentation System - Last Updated: 2025-08-25 04:15 EST

---

## ⚠️ CRITICAL: CURRENT STATUS

### **PRODUCTION ENVIRONMENT**
- **URL**: https://quotingfast-brain-ohio.onrender.com
- **Status**: ✅ LIVE - Receiving 120+ leads/hour
- **Database**: PostgreSQL on Render
- **Lead Count**: 245,743 total (18,076 from webhook)
- **Last 24 Hours**: 2,873 new leads
- **External Lead ID**: 13-digit timestamp format ✅

### **DEPLOYMENT**
- **Platform**: Render.com (Ohio region)
- **Method**: Auto-deploy via GitHub push
- **Repository**: https://github.com/giltorres14/quotingfast-brain
- **Deploy Time**: ~2-3 minutes after push

---

## 🎯 PROJECT STATUS SUMMARY

### **TODAY'S ACHIEVEMENTS (Aug 24, 2025)**
1. ✅ **Agent UI Completely Restored**
   - View page with all lead data, expandable sections
   - Edit page with 8 qualification questions (not 13)
   - Conditional logic for insurance and DUI fields
   - Enrichment buttons: Insured/Uninsured/Homeowner

2. ✅ **Technical Fixes**
   - Removed Blade directives from JavaScript
   - Added Tailwind CSS via CDN
   - Fixed phone formatting (xxx) xxx-xxxx
   - Implemented copy-to-clipboard for all fields

3. ✅ **RingBA Integration**
   - Three enrichment paths configured
   - Parameter mapping for Allstate compatibility
   - Validation logic for insured/uninsured states
4. ✅ **Duplicate Handling & Tools**
   - Added `/duplicates` (preview) and `/admin/lead-duplicates` (admin)
   - Bulk cleanup endpoint `/duplicates/cleanup-all` (admin_key protected)
   - Implemented keep-best by detail score; 58,533 duplicates removed
   - Fixed 500s by guarding `count()` with `is_countable()` and array casting

5. ⚠️ **ViciDial Sync Tooling - CRITICAL ISSUE**
   - `public/vici_dry_run_sync.php` (no writes) - ONLY SCANNING 826 OF 21,220+ LEADS
   - `public/vici_sync_assign_ids.php` (writer, default dry-run)
   - Issue: Query/buffer limitation preventing full data retrieval from ViciDial
   - Connection working with correct credentials (Port 20540, DB YLtZX713f1r6uauf)
   - `public/test_vici_debug.php` to inspect normalization
   - Removed the "only empty vendor_lead_code" filter from reporting
   - Lists discovery for `AUTODIAL` supported

---

## 🔄 LEAD FLOW ARCHITECTURE

### **Current Flow (Testing)**
```
LeadsQuotingFast
    ↓ (webhook)
Brain (/api-webhook)
    ↓ (store)
PostgreSQL Database
    ↓ (view)
Agent UI (/agent/lead/{id})
    ↓ (qualify)
8 Questions + Enrichment
    ↓ (enrich)
RingBA Test Endpoints
```

### **Target Production Flow**
```
LeadsQuotingFast → Brain → ViciDial (auto-dial) → Agent Qualification 
    → RingBA Enrichment → Allstate API → Revenue
```

---

## 📋 AGENT QUALIFICATION QUESTIONS (FINAL)

**The Correct 8 Questions (NOT 13):**

1. **Are you currently insured?**
   - If Yes → 1B. Current provider? (State Farm, GEICO, Progressive, Allstate, etc.)
   - If Yes → 1C. How long continuously insured? (Under 6mo, 6mo-1yr, 1-3yrs, 3+yrs)

2. **How many cars need a quote?** (1, 2, 3, 4+ vehicles)

3. **Do you own or rent your home?** (Own, Rent, Other)

4. **DUI or SR22?** (No, DUI Only, SR22 Only, Both)
   - If DUI → 4B. How long ago? (Under 1yr, 1-3yrs, Over 3yrs)

5. **State** (All 50 states + DC)

6. **ZIP Code** (5-digit input)

**[Agent Script Section]**
*"Let me go ahead and see who has the better rates in your area based on what we have. Oh ok, it looks like Allstate has the better rates in that area."*

7. **Have you received an Allstate quote in last 2 months?** (Yes/No)

8. **Ready to speak with an agent now?** (Yes, No, Maybe)

---

## 🔗 API INTEGRATIONS

### **1. ALLSTATE LEAD MARKETPLACE**
**Testing Environment:**
- URL: `https://int.allstateleadmarketplace.com/v2/`
- Auth: `Basic dGVzdHZlbmRvcjo=`
- Status: ⚠️ Testing validation

**Production Environment:**
- URL: `https://api.allstateleadmarketplace.com/v2/`
- Auth: `Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6`
- Status: 🔒 Ready for production

### **2. RINGBA ENRICHMENT**
**URLs by Type:**
- Insured: `https://display.ringba.com/enrich/2674154334576444838`
- Uninsured: `https://display.ringba.com/enrich/2676487329580844084`
- Homeowner: `https://display.ringba.com/enrich/2717035800150673197`

**Parameter Mapping:**
- `currently_insured`: true/false based on selection
- `continuous_coverage`: Maps duration to months (0, 5, 6, 12, 24)
- `allstate`: true if current provider is Allstate
- `dui`/`requires_sr22`: Based on DUI/SR22 selection
- All parameters URL-encoded and validated

### **3. VICIDIAL** (Database access ready; dialing temporarily bypassed)
- Dialer API: `philli.callix.ai` (`non_agent_api.php`, user: `UploadAPI`)
- MySQL/SSH Host: `37.27.138.222` port `11845`
- Database: `YLtZX713f1r6uauf` port `23964` (11M rows in vicidial_list!)
- Credentials: `qUSDV7hoj5cM6OFh` / `dsHVMx9QqHtx5zNt`
- Status: ✅ Credentials ready, awaiting IP whitelist

---

## 🗂️ KEY FILES & LOCATIONS

### **Core Views:**
- `resources/views/agent/lead-display.blade.php` - Main agent UI (view/edit modes)
- `resources/views/layouts/app.blade.php` - Base layout with Tailwind CDN

### **Routes & Public Endpoints:**
- `/api-webhook` - LQF webhook handler
- `/agent/lead/{id}` - Agent interface
- `/duplicates`, `/admin/lead-duplicates`, `/duplicates/cleanup-all`
- `/lead/{id}/payload-view`
- `/test/ringba-*`, `/test_vici_debug.php`, `/vici_dry_run_sync.php`, `/vici_sync_assign_ids.php`

### **Services:**
- `app/Services/AllstateCallTransferService.php` - Allstate API
- `app/Services/RingBAService.php` - RingBA enrichment
- `app/Services/ViciDialerService.php` - ViciDial integration

### **Database:**
- Host: `dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com`
- Database: `brain_production`
- Tables: `leads`, `allstate_test_logs`, `lead_outcomes`

---

## 🚨 CRITICAL REMINDERS

### **DO's:**
- ✅ Use 13-digit timestamp for external_lead_id
- ✅ Format phone as (xxx) xxx-xxxx in UI
- ✅ Show opt-in date as mm-dd-yyyy
- ✅ Use pure PHP in script tags, not Blade
- ✅ Clear cache after template changes
- ✅ Test in both normal and iframe modes

### **DON'Ts:**
- ❌ Never use @if/@endif inside <script> tags
- ❌ Don't send test data to Allstate production
- ❌ Don't forget conditional field logic
- ❌ Don't change the 8-question structure
- ❌ Don't bypass enrichment validation

---

## 📊 METRICS & MONITORING

### **Current Performance:**
- Lead Reception: ~120/hour (2,873/day)
- Database Size: 245,743 leads
- Active Sources: LeadsQuotingFast (primary)
- Lead Types: Mostly "auto", some "unknown" need migration

### **Monitoring Commands:**
```bash
php check_recent_leads.php    # Lead activity
php pre_deploy_check.php      # System health
curl https://quotingfast-brain-ohio.onrender.com/health  # API health
```

---

## 🎯 NEXT PRIORITIES

1. **Lead Type Migration** 
   - Fix ~200k leads showing "unknown" type
   - Should be "auto" or "home" based on data

2. **Bulk Import**
   - 111k+ leads ready in CSV
   - Script: `import_lqf_bulk.php`

3. **ViciDial Restoration**
   - Re-enable after testing complete
   - Ensure List 101 targeting

4. **RingBA Production**
   - Move from test to production endpoints
   - Verify Allstate confirmation flow

5. **Allstate Production**
   - Complete field validation
   - Enable production API calls

---

## 💡 LESSONS LEARNED

1. **Blade Compilation**: Never mix Blade directives with JavaScript
2. **Cache Management**: Render aggressively caches - use CACHE_BUST
3. **Field Names**: Allstate API is extremely strict on naming
4. **Conditional Logic**: Must be handled client-side with JavaScript
5. **Testing First**: Always use test endpoints before production

---

## 🔐 SECURITY NOTES

- Webhook endpoint `/api-webhook` has no CSRF protection (intentional)
- RingBA URLs contain account IDs - keep secure
- Allstate API keys in environment variables
- Database credentials in Render environment

---

*This document represents the complete project state as of August 22, 2025, 11:30 PM EST*
*System is LIVE and actively processing leads*