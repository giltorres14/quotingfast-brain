# Current System State - Last Updated: August 13, 2025 (5:00 PM EST)

## 🔌 VICI SERVER INTEGRATION - NEW!

### Static IP Configuration
**Render Static Outbound IPs (Ohio Region):**
1. **3.134.238.10**
2. **3.129.111.220** (currently active)
3. **52.15.118.168**

**Vici Server Details:**
- IP: 37.27.138.222
- User: root
- Password: Monster@2213@!
- **Action Required:** Whitelist all 3 Render IPs on Vici server

### Vici Proxy Setup
- **Test Endpoint:** https://quotingfast-brain-ohio.onrender.com/vici-proxy/test
- **Controller:** app/Http/Controllers/ViciProxyController.php
- **Routes:** /vici-proxy/test, /vici-proxy/execute, /vici-proxy/call-logs
- **Status:** Deployed and ready for testing once IPs are whitelisted

## 📊 BULK IMPORT STATUS - WHERE WE LEFT OFF

### SURAJ BULK IMPORT (PAUSED)
**Current Status:** STOPPED at file 21 of 86 due to corrupted CSV data
- **Successfully Imported:** 76,487 leads (after duplicate cleanup)
- **Files Processed:** 21 of 86 CSV files
- **Location:** `~/Downloads/Suraj Leads/`
- **Issues Found:** 
  - Files 22+ contain HTTP error responses in address fields (corrupted data from failed webhooks)
  - Example: Address fields contain "Cache-Control: no-cache<br>Connection: keep-alive" etc.
- **Last Good File:** File 21 completed successfully
- **Scripts Created:**
  - `suraj_import_clean.php` - Validates and skips corrupted records
  - `clean_suraj_duplicates_fast.php` - Removed 2,794 duplicates
  - `fix_all_suraj_data.php` - Updates missing fields from CSV
  - `complete_suraj_import.php` - Latest import script with all fixes
- **TO RESUME:** Need to clean HTTP corruption from CSV files 22-86

### LQF BULK IMPORT (READY TO RUN)
**Current Status:** TEST COMPLETED - Ready for full import
- **Test Results:** 10 records imported successfully with source "LQF_BULK"
- **File:** `~/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv`
- **Total Records:** 149,548 leads to import
- **File Size:** 397 MB
- **Command to Run:** `php artisan lqf:bulk-import ~/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv`
- **Features:**
  - Automatically replaces Suraj duplicates when phone matches
  - Sets source to "LQF_BULK" (fixed in ImportLqfBulkCsv.php line 262)
  - Handles all required field mappings
- **Test Import Examples:** 
  - Chad Marshall (720-410-1824) - ID: 86167
  - SARA BARTLETT (850-628-6205) - ID: 86166
  - View at: https://quotingfast-brain-ohio.onrender.com/agent/lead/[ID]

## ✅ COMPLETED TODAY (August 13)

### UI Updates - Lead Edit Page
- **Save button** moved inside Lead Details section
- **Header** now shows phone and address, made sticky
- **Vendor/buyer section** hidden in edit mode
- **Back button** shows for admin, hidden in iframe
- **Lead Qualification section** removed

### Data Cleanup
- **Suraj Duplicates:** Cleaned 2,794 duplicate records
- **Final Count:** 76,487 unique Suraj leads
- **LQF Import:** Fixed source to use "LQF_BULK" instead of "LQF"

### Other Fixes
- Campaign ID .0 suffix fixed
- Vici Call Reports complete at /admin/vici-reports
- Database nullable fields fixed (tenant_id, password)

## 🔌 VICI INTEGRATION STATUS - TROUBLESHOOTING CONNECTION

### ⚠️ Current Issue: SSH Port 22 Blocked
- **Test Time:** January 13, 2025 - 10:27 PM EST
- **IP Being Used:** 52.15.118.168 (one of our static IPs)
- **Error:** "No route to host" (error 113) on SSH port 22
- **HTTP Port 80:** ✅ Working (open)
- **SSH Port 22:** ❌ Blocked
- **Test URL:** https://quotingfast-brain-ohio.onrender.com/test-vici-ssh.php

### 📋 Render Static IPs (Need SSH Whitelisting)
- **IP 1:** 3.134.238.10
- **IP 2:** 3.129.111.220
- **IP 3:** 52.15.118.168 (currently active)
- **Vici Server:** 37.27.138.222
- **Required:** SSH port 22 access for all 3 IPs

### ✅ Infrastructure (COMPLETE & DEPLOYED)
- **Export Script:** `vici_export_script.sh` ready
- **Processing Pipeline:** Fully deployed
- **Scheduler:** Configured for every 5 minutes
- **Old Data:** Cleaned (2,701 records backed up)
- **Commands Ready:**
  - `php artisan vici:run-export` - Manual trigger
  - `php artisan vici:process-csv {file}` - Process CSV
- **Logs:** Will output to `storage/logs/vici_export.log`

### 🔧 Next Steps
1. **Verify with Vici support** that all 3 IPs are whitelisted for SSH port 22
2. **Test connection** at https://quotingfast-brain-ohio.onrender.com/test-vici-ssh.php
3. **Once connected**, automated sync will begin immediately
4. **Monitor logs** for successful data collection

## 🚀 SYSTEM STATUS

### Database Current State
- **Total Leads:** 81,227
- **Breakdown by Source:**
  - SURAJ_BULK: 76,487 (94.2%)
  - LQF_BULK: 10 (test records)
  - Other: 4,730
- **Type**: PostgreSQL 16
- **Host**: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com

### Active Webhooks
- **Primary**: `/api-webhook` (receiving leads)
- **Secondary**: `/webhook.php` (backup)
- **Status**: Working, all fields captured

## 📁 KEY FILES FOR RESUMING WORK

### Import Scripts
- `app/Console/Commands/ImportSurajBulkCsvFastV2.php` - Suraj import command
- `app/Console/Commands/ImportLqfBulkCsv.php` - LQF import with replacement logic
- `suraj_import_clean.php` - Standalone script for corrupted data
- `clean_suraj_duplicates_fast.php` - Duplicate removal script

### UI Files
- `resources/views/agent/lead-display.blade.php` - Lead view/edit page
- `resources/views/campaigns/directory.blade.php` - Campaign list (needs JS for delete)

## ⚠️ IMMEDIATE NEXT STEPS

1. **Fix Vici SSH Connection**: 
   - Contact Vici support to whitelist SSH port 22 for IPs: 3.134.238.10, 3.129.111.220, 52.15.118.168
   - Test at: https://quotingfast-brain-ohio.onrender.com/test-vici-ssh.php
   - Once working, automated sync begins

2. **Complete LQF Import**: Run full import of 149k records
   ```bash
   php artisan lqf:bulk-import ~/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv
   ```

3. **Fix Suraj CSV Corruption**: Clean files 22-86 to remove HTTP headers from data
   - Create script to detect and clean corrupted fields
   - Re-import cleaned files

4. **Complete Campaign Delete**: Add JavaScript function in campaigns/directory.blade.php

## 🎯 QUICK COMMANDS TO RESUME

```bash
# Check current lead counts
php artisan tinker --execute="echo 'Suraj: ' . \App\Models\Lead::where('source', 'SURAJ_BULK')->count() . ' | LQF: ' . \App\Models\Lead::where('source', 'LQF_BULK')->count();"

# Start LQF import
php artisan lqf:bulk-import ~/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv

# Monitor import progress
tail -f storage/logs/laravel.log
```

## 📝 NOTES
- Vici integration is PAUSED during imports
- Campaign IDs with .0 suffix are automatically cleaned
- LQF data replaces Suraj data on phone number match
- All imports use tenant_id = 1