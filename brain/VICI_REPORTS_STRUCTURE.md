# Vici Reports Structure - CLEANED UP
*Last Updated: January 14, 2025*

## ✅ ACTIVE VICI REPORT LOCATIONS (Only 2 Now!)

### 1. **Basic Vici Call Reports** 
- **URL**: `/admin/vici-reports`
- **View**: `resources/views/admin/vici-reports.blade.php`
- **Access**: Management dropdown → "📞 Vici Call Reports"
- **Features**:
  - Call metrics overview
  - Agent performance stats
  - Campaign breakdown
  - Recent calls list
  - Date filtering (defaults to today)
  - Search functionality

### 2. **Comprehensive Reports Dashboard**
- **URL**: `/admin/vici-comprehensive-reports`
- **View**: `resources/views/admin/vici-comprehensive-reports.blade.php`
- **Controller**: `ViciReportsController`
- **Access**: Management dropdown → "📊 Comprehensive Reports (NEW)"
- **12 Report Types**:
  1. Lead Journey Timeline
  2. Agent Leaderboard & Scorecard
  3. Campaign ROI Dashboard
  4. Speed to Lead Report
  5. Call Failure Diagnostics
  6. Optimal Call Time Analysis
  7. Lead Recycling Intelligence
  8. Transfer Success Analytics
  9. Real-Time Operations Dashboard
  10. Lead Waste Finder
  11. Predictive Lead Scoring
  12. Executive Summary
- **Features**:
  - CSV export for all reports
  - Real-time data refresh
  - Lead journey drill-down
  - Interactive charts

## 🔄 REDIRECTS & CLEANUP

### Removed/Redirected Routes:
- `/admin/vici-call-logs` → Redirects to `/admin/vici-reports`
- `/test/vici-db` → DISABLED
- `/test/vici/{leadId}` → DISABLED
- `/test/vici-login` → DISABLED
- `/test/vici-update/{leadId}` → DISABLED
- `/test-vici-connection` → DISABLED

### Deleted Files:
- `resources/views/admin/vici-call-logs.blade.php` (duplicate of vici-reports)

## 📊 DATA SOURCES

All reports pull from:
- **ViciCallMetrics** model - Main call data
- **OrphanCallLog** model - Unmatched calls
- **Lead** model - Lead information
- **Vici Database** - Direct queries via proxy for real-time data

## 🔄 SYNC & UPDATE MECHANISMS

### Automatic Sync (Every 5 minutes):
- Command: `vici:sync-incremental`
- Fetches new call logs from Vici
- Matches to Brain leads via `vendor_lead_code`
- Stores unmatched as orphans

### Archive System (Daily at 2 AM):
- Command: `vici:archive-old-leads`
- Moves 90+ day old leads to list 199
- Keeps database optimized

### Manual Sync Options:
- Button in Vici Reports UI
- Artisan command: `php artisan vici:sync-call-logs`

## 🚀 ACCESSING THE REPORTS

### For Users:
1. Go to https://quotingfast-brain-ohio.onrender.com/admin/simple-dashboard
2. Click "Management" dropdown
3. Select:
   - "📞 Vici Call Reports" for basic metrics
   - "📊 Comprehensive Reports (NEW)" for advanced analytics

### For Developers:
- Basic Reports: `/admin/vici-reports`
- Comprehensive: `/admin/vici-comprehensive-reports`
- Export API: `/admin/vici-reports/export/{type}`
- Real-time API: `/admin/vici-reports/real-time`
- Lead Journey API: `/admin/vici-reports/lead-journey/{leadId}`

## 📝 NOTES

- All test routes have been disabled to prevent confusion
- Old duplicate views have been removed
- Single source of truth for each report type
- Data syncs automatically every 5 minutes
- 90-day historical data is being imported
- Archive system prevents database bloat
