# Brain System Current State
**Last Updated:** August 24, 2025 - 11:30 PM EST

## 🎯 System Overview
The Brain system is a lead management platform that receives, qualifies, and routes insurance leads from LeadsQuotingFast (LQF) to various buyers including Allstate via RingBA.

## ✅ Recent Accomplishments (Aug 24, 2025)

### Agent Lead View/Edit Pages - FULLY RESTORED
1. **View Page** (`/agent/lead/{id}?mode=view`)
   - Sticky header with lead name, phone (xxx) xxx-xxxx format, email, Lead ID
   - Sections in order: Lead Information, TCPA Compliance, Drivers, Vehicles, Current Insurance
   - All driver/vehicle data displays with expandable "More details"
   - TCPA enriched with TrustedForm URL, opt-in date (mm-dd-yyyy), landing page, LeadiD, consent text
   - Copy buttons for all important fields

2. **Edit Page** (`/agent/lead/{id}?mode=edit`)
   - **8 Agent Qualification Questions** (not 13) with proper conditional logic:
     1. Currently insured? → Shows provider & continuous coverage if Yes
     2. How many vehicles?
     3. Own or rent home?
     4. DUI/SR22? → Shows timeframe if DUI selected
     5. State
     6. ZIP Code
     7. Allstate quote in last 2 months? (with agent script)
     8. Ready to speak now?
   - **Enrichment Buttons**: Insured, Uninsured, Homeowner (RingBA integration)
   - In iframe mode: hides Back/View Payload/Copy buttons
   - Minimal Lead Info (Type, External ID) and TCPA (Consent, Opt-in date) shown

3. **Duplicate Handling & Tools**
   - New pages/endpoints:
     - `/duplicates` (preview) and `/admin/lead-duplicates` (admin alias)
     - Bulk cleanup: `/duplicates/cleanup-all` (GET/POST, admin_key protected)
   - "Keep best, delete others" implemented by detail score; 58,533 duplicates removed
   - CSRF disabled for duplicate delete endpoints to allow admin_key automation
   - Fixed 500 on `/duplicates` by guarding `count()` with `is_countable()` and array casting

4. **ViciDial Sync Prep**
   - Added public tooling (dry-run by default):
     - `public/vici_dry_run_sync.php` – scans Vici leads, reports Brain matches
     - `public/vici_sync_assign_ids.php` – writes `vendor_lead_code = external_lead_id`
     - `public/test_vici_debug.php` – shows normalization of sample phones
   - Correct Vici host for SSH/MySQL set to `162.241.97.210` (lists discovery for AUTODIAL)
   - Removed the "only empty vendor_lead_code" filter in reporting paths
   - Pending: working MySQL credentials for host to enable reads/writes

### Technical Improvements
- Removed all Blade directives from JavaScript (prevents compilation errors)
- Added Tailwind CSS via CDN for consistent styling
- Implemented proper conditional field toggling with JavaScript
- RingBA parameter mapping for all three enrichment types

## 📊 Current System Status

### ✅ Working
- **Lead Reception**: 2,873 leads in last 24 hours via `/api-webhook`
- **Database**: 245,743 total leads (18,076 from LQF webhook)
- **UI Pages**: Agent view/edit pages fully functional
- **Health Check**: `/health` endpoint returning 200 OK
- **Deployment**: Render.com auto-deploy via GitHub push
 - **Duplicates Dashboard**: `/duplicates` reachable; admin controls via `/admin/lead-duplicates`

### ⚠️ Pending Tasks
- **Lead Type Migration**: Many leads still showing "unknown" instead of "auto"/"home"
- **Bulk Import**: 111k+ LQF leads ready for import (CSV prepared)
- **ViciDial Integration**: Currently bypassed for testing, needs restoration
- **RingBA Production**: Test endpoints working, production config pending
- **Vici Sync**: Production credentials ready, awaiting IP whitelist for Render servers
  - DB: YLtZX713f1r6uauf (11M rows in vicidial_list)
  - Port: 23964, User: qUSDV7hoj5cM6OFh
  - Scripts updated with safety limits to prevent full table scans

### ❌ Known Issues
- Some Blade templates in admin pages have @if inside <script> tags (6 files)
- Duplicate route definitions need cleanup (70+ warnings)
- Direct property access without isset() checks in various views

## 🔄 Current Lead Flow

### Active Flow (Testing Mode)
```
LeadsQuotingFast → Brain (/api-webhook) → Database → Agent UI → RingBA Test → (Manual)
```

### Target Production Flow
```
LeadsQuotingFast → Brain → ViciDial → Agent Qualification → RingBA → Allstate API
```

## 🛠️ Key Components

### Endpoints
- `/api-webhook` - Primary webhook for LQF leads (WORKING)
- `/agent/lead/{id}` - Agent view/edit interface (WORKING)
- `/health` - Health check endpoint (WORKING)
- `/admin/allstate-testing` - Testing dashboard
- `/test/ringba-send/{id}` - RingBA test endpoints

### Database
- **Production**: PostgreSQL on Render (Ohio region)
- **Host**: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
- **Database**: brain_production
- **Lead Count**: 245,743+

### Files Modified Today
- `resources/views/agent/lead-display.blade.php` - UI order, header address, TCPA, iframe hides
- `resources/views/layouts/app.blade.php` - Duplicates nav link
- `routes/web.php` - `/duplicates` routes, admin alias, guards and error handling
- `public/cleanup_duplicates.php` - Bulk duplicate cleanup (admin_key)
- `public/vici_dry_run_sync.php`, `public/vici_sync_assign_ids.php`, `public/test_vici_debug.php`

## 📝 Agent Qualification Questions (Current Implementation)

1. **Are you currently insured?**
   - If Yes → Current provider (dropdown)
   - If Yes → Continuous coverage duration

2. **How many cars need a quote?** (1-4+ vehicles)

3. **Do you own or rent your home?** (Own/Rent/Other)

4. **DUI or SR22?**
   - If DUI → How long ago? (Under 1 year/1-3 years/Over 3 years)

5. **State** (All US states)

6. **ZIP Code**

7. **Have you received an Allstate quote in last 2 months?**

8. **Ready to speak with an agent now?** (Yes/No/Maybe)

## 🔧 Debug Tools Available
```bash
php pre_deploy_check.php      # Pre-deployment validation
php check_recent_leads.php    # Monitor lead activity
php find_unbalanced_if.php    # Find Blade syntax issues
php clear_view_cache.php      # Clear compiled views
```

## 🚀 Deployment Process
```bash
# Make changes
git add -A
git commit -m "Description of changes"
git push origin main
# Wait 2-3 minutes for Render deployment
```

## 📋 Immediate Priorities
1. ✅ Fix Agent UI pages (COMPLETED)
2. ⬜ Migrate "unknown" lead types to proper values
3. ⬜ Import 111k bulk leads from LQF CSV
4. ⬜ Restore ViciDial integration
5. ⬜ Configure production RingBA endpoints
6. ⬜ Complete Allstate API integration

## 🔑 Critical Notes
- **External Lead ID Format**: 13-digit timestamp (e.g., 1755897534000)
- **Lead Types**: Should be "auto" or "home", not "unknown"
- **Blade Templates**: Never use @if/@endif inside <script> tags
- **Cache Clearing**: Required after Blade template changes
- **Iframe Mode**: Automatically hides navigation elements

---
*System actively receiving ~120 leads/hour from LeadsQuotingFast*