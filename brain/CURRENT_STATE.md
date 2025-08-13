# Current System State - Last Updated: August 13, 2025

## ✅ JUST COMPLETED
- **Vici Call Report Integration**: DONE!
  - UI: Complete at /admin/vici-reports (defaults to today)
  - API: Working with agent_stats_export function
  - Sync: New command `php artisan vici:sync-agent-stats`
  - Data: Successfully synced 78,757 calls from 16 agents
  - Status: Production ready

## ✅ COMPLETED TODAY (August 13)
- Bulk imports optimized and running (8-13 mins instead of 5 days)
- Campaign ID .0 suffix fixed in display and imports
- Lead view UI improvements (3x logo, removed duplicates)
- Jangle Lead ID extraction fixed
- Buy/Sell price display working
- Vici orphan call system implemented
- **Vici Call Reports UI** - Complete dashboard at /admin/vici-reports
- **Vici Agent Stats Sync** - Working sync command pulling real data
- **Campaign Delete Button** - Added to Campaign Directory (JS needs completion)

## 🚀 SYSTEM STATUS

### Imports Running
- **Suraj Bulk**: ~13,800 leads (may be complete)
- **LQF Bulk**: ~70,000 leads (may be complete)
- **Replacement Logic**: LQF replaces Suraj on duplicate phone

### Database
- **Type**: PostgreSQL 16 (NOT SQLite)
- **Host**: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
- **Database**: brain_production

### Webhooks
- **Primary**: `/api-webhook` (ACTIVE, receiving leads)
- **Secondary**: `/webhook.php` (backup)
- **Status**: Working, capturing all fields including Jangle ID

### Vici Integration
- **Status**: PAUSED during bulk imports
- **Orphan System**: Active (stores unmatched calls)
- **Dashboard**: `/admin/vici-call-logs`

## 📁 KEY FILES & THEIR PURPOSE

### Commands (Artisan)
- `app/Console/Commands/ImportSurajBulkCsvFastV2.php` - Optimized Suraj import
- `app/Console/Commands/ImportLqfBulkCsv.php` - LQF import with replacement logic
- `app/Console/Commands/SyncViciCallLogs.php` - Vici sync with orphan handling
- `app/Console/Commands/MatchOrphanCalls.php` - Match orphan calls to leads

### Views
- `resources/views/agent/lead-display.blade.php` - Lead view/edit (3x logo, no back button in edit)
- `resources/views/campaigns/directory.blade.php` - Campaign list (delete button INCOMPLETE)
- `resources/views/admin/vici-call-logs.blade.php` - Vici dashboard

### Routes
- `routes/web.php` - All endpoints including `/api-webhook` and Vici routes

## 🔧 CONFIGURATION NOTES

### Lead ID Format
- **external_lead_id**: 13-digit timestamp (e.g., 1755041041000)
- **jangle_lead_id**: From `payload['id']` for LQF leads
- **leadid_code**: Tracking parameter

### Field Mappings
- **Opt-in Date**: Column B (Suraj), `originally_created` (LQF)
- **Campaign ID**: Column L (cleaned to remove .0)
- **Buy Price**: In meta/payload (not main field)
- **Sell Price**: Main field in leads table

### Display Rules
- Phone format: (xxx)xxx-xxxx
- Timestamps: EST timezone
- Source labels: "SURAJ_BULK", "LQF_BULK", "LQF", "SURAJ"

## ⚠️ CRITICAL REMINDERS
1. **NEVER** give iframe agents access to Brain (no back buttons in edit mode)
2. **ALWAYS** test webhooks after deployment
3. **PostgreSQL** not SQLite in production
4. Campaign IDs need .0 suffix removed
5. Vici is PAUSED - don't send leads there during imports

## 📝 NEXT IMMEDIATE TASKS
1. Complete `deleteCampaign()` JavaScript function
2. Check if imports are complete
3. Test webhook with live data
4. Re-enable Vici after imports done

## 🎯 QUICK RESUME CHECKLIST
- [ ] Read this file first
- [ ] Check import status: `ps aux | grep artisan`
- [ ] If working on Campaign Delete, go to `resources/views/campaigns/directory.blade.php` line ~520
- [ ] Remember: PostgreSQL in production, not SQLite
