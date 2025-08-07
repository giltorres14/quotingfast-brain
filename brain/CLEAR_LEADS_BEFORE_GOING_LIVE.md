# 📋 CLEAR LEADS BEFORE GOING LIVE - STEP BY STEP

## 🎯 When to Use This
Use this procedure to clear all test leads from the system before going live with real production data.

## ✅ Pre-Flight Checklist

1. **Verify you have access to:**
   - [ ] Admin dashboard: https://brain-api.onrender.com/admin
   - [ ] Server logs (for verification)
   - [ ] Terminal access (for restore if needed)

2. **Check current lead counts:**
   - Go to: https://brain-api.onrender.com/leads
   - Note the total number of leads shown

## 🚀 Step-by-Step Clearing Process

### Step 1: Access the Clear Leads Page
```
https://brain-api.onrender.com/admin/clear-test-leads
```

### Step 2: Review What Will Be Deleted
You'll see three numbers:
- **Total Leads**: All lead records
- **Test Logs**: All Allstate API test logs
- **Queue Items**: Any pending queue items

**IMPORTANT**: Write these numbers down for your records.

### Step 3: Confirm Deletion
1. ✅ Check the confirmation checkbox:
   ```
   "I understand this will delete ALL XXX leads permanently"
   ```

2. The "Clear All Test Leads" button will become enabled (red)

### Step 4: Execute Deletion
1. Click the **"Clear All Test Leads"** button
2. A popup will appear asking for final confirmation
3. Click **OK** to proceed

### Step 5: Wait for Completion
The system will:
1. Create comprehensive backups (JSON, compressed, SQL)
2. Verify the backup is valid
3. Delete all data in correct order
4. Show success message with:
   - ✅ Success confirmation
   - 📦 Backup filename (e.g., `leads_backup_2025-01-08_143022.json`)
   - 🔐 Verification code (e.g., `A3F2B1`)

**SAVE THIS INFORMATION!** Write down:
- Backup filename
- Verification code
- Date/time of deletion

### Step 6: Verify Deletion
After 7 seconds, you'll be redirected to the leads page.
- Should show "0" leads
- Dashboard should show empty

## 📦 What Gets Backed Up

Before any deletion, the system creates THREE backups:

1. **JSON Backup** (`leads_backup_YYYY-MM-DD_HHMMSS.json`)
   - Human-readable format
   - Contains all lead data with external_lead_id preserved
   - All test logs and queue items

2. **Compressed Backup** (`leads_backup_YYYY-MM-DD_HHMMSS.json.gz`)
   - Same data, compressed for storage
   - Useful for archiving

3. **SQL Dump** (`leads_backup_YYYY-MM-DD_HHMMSS.sql`)
   - Database-level backup (when available)
   - Can be restored directly to database

## 🔐 Safety Features

The system has 9 safety layers:
1. ✅ Requires checkbox confirmation
2. ✅ Requires popup confirmation
3. ✅ Creates automatic backup BEFORE deletion
4. ✅ Verifies backup is valid
5. ✅ Uses database transaction (all or nothing)
6. ✅ Generates verification code for audit
7. ✅ Deletes in correct order (respects foreign keys)
8. ✅ Verifies deletion was complete
9. ✅ Automatic rollback on ANY error

## 🚨 If Something Goes Wrong

### Option 1: Check the Logs
```bash
# SSH into server (if you have access)
tail -100 storage/logs/laravel.log | grep "DELETION\|BACKUP"
```

### Option 2: Restore from Backup

#### Via Command Line (if you have SSH):
```bash
# Restore latest backup
php artisan leads:restore

# Or restore specific backup
php artisan leads:restore leads_backup_2025-01-08_143022.json
```

#### Via Database (if you have DB access):
```sql
-- Check if leads table is empty
SELECT COUNT(*) FROM leads;

-- If you need to restore and have the SQL backup
mysql -u brain_user -p brain_production < storage/app/backups/leads_backup_2025-01-08_143022.sql
```

## 📍 Important Notes

### What DOESN'T Get Reset
- ✅ External Lead ID format (13-digit timestamps) is preserved
- ✅ No auto-increment counters are reset
- ✅ Your numbering system remains intact

### What DOES Get Deleted
- ❌ ALL leads (cannot be selective)
- ❌ ALL Allstate test logs
- ❌ ALL queue items
- ❌ This is permanent (except for restore from backup)

### Backup Storage Location
All backups are stored on the server at:
```
/var/www/html/storage/app/backups/
```

## 🎯 Final Checklist Before Going Live

After clearing test leads:

1. **Verify Clean State:**
   - [ ] Leads page shows 0 leads
   - [ ] Dashboard shows 0 counts
   - [ ] No errors in logs

2. **Save Documentation:**
   - [ ] Backup filename noted
   - [ ] Verification code saved
   - [ ] Date/time recorded

3. **Update Webhook URLs:**
   - [ ] Change from `/webhook-failsafe.php` to production endpoint
   - [ ] Test with one real lead
   - [ ] Verify lead appears in system

4. **Enable Production Features:**
   - [ ] Turn off Allstate testing mode (if needed)
   - [ ] Enable Vici integration (if needed)
   - [ ] Set production environment variables

## 💡 Pro Tips

1. **Always clear during low-traffic period** - Even though it's safe, do it when no leads are coming in

2. **Keep the backup for 30 days** - Just in case you need to reference old test data

3. **Document the clearing** - Note in your project log:
   ```
   [DATE] Cleared XXX test leads before going live
   Backup: leads_backup_YYYY-MM-DD_HHMMSS.json
   Verification: XXXXXX
   ```

4. **Test with one lead after clearing** - Send a single test lead to verify the system works with empty database

## ⚠️ WARNINGS

- **DO NOT** run this after going live with real data
- **DO NOT** delete the backup files from the server
- **DO NOT** reset auto-increment counters manually
- **DO NOT** clear leads while leads are actively coming in

## 📞 Need Help?

If you encounter any issues:
1. Check this documentation first
2. Review the logs for error messages
3. The backup is your safety net - you can always restore

---

**Last Updated**: January 8, 2025
**System Version**: Brain 2.0 with Ultra-Safe Deletion
**Backup Format**: JSON + Compressed + SQL
**Restoration Time**: ~1-2 minutes via command line
