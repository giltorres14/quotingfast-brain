# Brain System Current State
**Last Updated:** August 22, 2025 - Evening Session

## ✅ Recent Accomplishments

### Lead View Page (Agent)
- Implemented sticky top panel with full address and phone formatted as `(xxx) xxx-xxxx`
- Restored layout and sections in this exact order:
  1) Top Panel (sticky)
  2) Lead Information
  3) TCPA Compliance
  4) Drivers
  5) Vehicles
  6) Current Insurance Policy
- Drivers/Vehicles show all available data with expandable “More details” blocks
- TCPA panel enriched with:
  - `trusted_form_cert_url` (with copy),
  - Opt-in Date (uses `opt_in_date` or meta `originally_created`, shown as mm-dd-yyyy),
  - Landing Page URL (with copy),
  - LeadiD/lead_id_code when available,
  - Consent text in collapsible view with copy button

### Lead Edit Page (Agent / Iframe)
- Top 13 Questions appear directly under the header (no scrolling needed)
- Canonical question order aligned with project docs; conditional fields show when “Currently Insured = Yes”
- In iframe view, header actions (Back, View Payload, Copy Payload, View Mode) are hidden
- Lead Information: only Lead Type and External Lead ID visible in edit mode
- TCPA Compliance: only TCPA Consent and Opt‑in Date visible in edit mode
- Added Enrichment buttons after the questions: `Insured`, `Uninsured`, `Home` (hooked to RingBA test endpoints)
- Opt-in date formatted as `mm-dd-yyyy`

### Stability/Styling
- Converted Blade conditionals inside JS to pure PHP (prevents compilation errors)
- Added Tailwind via CDN in `layouts/app.blade.php` so utility classes render without local build

## 📊 System Status

## 📊 System Status

### ✅ Working
- **LQF Webhook:** Receiving 1,360+ leads/day via `/api-webhook`
- **Database:** 242,891+ leads in PostgreSQL
- **Import Script:** Fixed to read Vertical column for lead type

### ❌ Not Working / Pending
- Health endpoints `/health` and `/health/ui` availability in production is still inconsistent
- Final RingBA parameter payloads (Insured/Uninsured/Home variants) need confirmation

### ⚠️ Pending Issues
- **Lead Types:** Many showing "unknown" - need migration
- **ViciDial Sync:** Lists 6018-6026 need matching
- **Bulk Import:** LQF CSV ready but not imported

## 🔧 This Session’s Work Log (Aug 22, 2025)
- Restored and finalized Agent view page sections and styling
- Rebuilt Agent edit page to display canonical Top 13 with conditional insured fields
- Hid header action buttons in iframe mode; removed inline edit affordances elsewhere
- Enriched TCPA panel and ensured mm‑dd‑yyyy formatting for Opt‑in
- Replaced enrichment buttons with `Insured` / `Uninsured` / `Home` hooks
- Injected Tailwind CDN; removed reliance on Blade-in-JS patterns

### Files Modified
- `resources/views/agent/lead-display.blade.php` (major UI + logic updates)
- `resources/views/layouts/app.blade.php` (Tailwind CDN)
- `routes/web.php` (lead view route stability, qualification save, RingBA test hooks)

## 🛠️ Debug Tools Available
```bash
php pre_deploy_check.php      # Check before deploying
php check_recent_leads.php    # Monitor webhook activity
php find_unbalanced_if.php    # Find Blade imbalances
php trace_ifs.php             # Trace @if/@endif pairs
php clear_view_cache.php      # Clear local view cache
```

## 📝 Critical Learnings
1. **Always check git history** when something "used to work"
2. **Blade compilation caches aggressively** on Render
3. **CACHE_BUST in Dockerfile** forces complete rebuild
4. **PDO queries more stable** than Eloquent in routes
5. **View cache must be cleared** both locally and on server

## 🎯 Immediate Next Actions
1. **Wait for deployment** - Cache rebuild should complete in 2-3 min
2. **Test lead pages** - Check if error is resolved
3. **If still broken:** May need manual server intervention
4. **If fixed:** Continue with pending tasks

## 📋 TODO Status
- [x] Fix Blade syntax errors
- [x] Document session work
- [ ] Wait for cache rebuild to complete
- [ ] Verify lead pages working
- [ ] Run type migration for "unknown" leads
- [ ] Import LQF Bulk CSV
- [ ] Sync ViciDial lists

## 🔑 Key Information

### Database Connection
```
Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
Database: brain_production
User: brain_user
```

### Test Lead
- **ID:** 491471 (or 491801)
- **Issue:** Shows "unknown" type, should be "auto"

### Blade Template Rules
- Must have exactly 81 @if and 81 @endif
- Qualification form and TCPA sections are SEQUENTIAL, not nested
- Never put @if/@endif inside JavaScript

## ⚡ Quick Commands
```bash
# Check system
php pre_deploy_check.php

# Monitor leads
php check_recent_leads.php

# Deploy with cache bust
git add -A && git commit -m "Message" && git push origin main

# Check specific lead
psql -h dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com \
  -U brain_user -d brain_production \
  -c "SELECT id, type, name FROM leads WHERE id = 491471;"
```

---
*This represents the exact state as of Dec 19, 2024 late night. Blade error persisting, cache rebuild in progress.*