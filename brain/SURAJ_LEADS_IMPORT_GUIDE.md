# 📥 SURAJ LEADS IMPORT GUIDE

## Complete Solution for Suraj Leads (Historical + Daily)

### Overview
Suraj leads are a special source that don't come through LQF. This guide covers:
1. **Importing 3 months of historical Suraj leads from Vici**
2. **Daily CSV imports going forward**
3. **Keeping Brain and Vici in sync**

---

## 🕐 **STEP 1: IMPORT HISTORICAL SURAJ LEADS FROM VICI**

### Get the Last 3 Months of Suraj Leads
```bash
# Dry run first to see what will be imported
php artisan vici:import-suraj-leads --start-date=2024-09-01 --dry-run

# Actual import
php artisan vici:import-suraj-leads --start-date=2024-09-01

# Import with specific date range
php artisan vici:import-suraj-leads --start-date=2024-09-01 --end-date=2024-12-31
```

### What This Does:
- Connects to Vici API
- Exports all leads with source "Suraj" from List 101
- Imports them into Brain with proper lead IDs
- Preserves all Vici data (phone, name, address, etc.)
- Tracks which leads came from Vici

### Options:
- `--list-id=101` - Specify Vici list (default: 101)
- `--source=Suraj` - Filter by source name
- `--update-existing` - Update if lead already exists
- `--dry-run` - Preview without importing

---

## 📅 **STEP 2: DAILY CSV IMPORTS**

### Import Today's Suraj CSV File
```bash
# Preview the import
php artisan suraj:import-daily /path/to/suraj_leads_2024-12-20.csv --dry-run

# Import and skip duplicates
php artisan suraj:import-daily /path/to/suraj_leads_2024-12-20.csv --skip-duplicates

# Import and push to Vici
php artisan suraj:import-daily /path/to/suraj_leads_2024-12-20.csv --skip-duplicates --push-to-vici
```

### Auto-Detected CSV Headers:
The system automatically detects common header variations:
- **Phone**: phone, phone_number, telephone, mobile, cell
- **Name**: first_name, last_name, firstname, lastname
- **Email**: email, email_address
- **Address**: address, street, city, state, zip
- **Other**: dob, gender, source, id

### Example CSV Format:
```csv
phone,first_name,last_name,email,address,city,state,zip,dob
5551234567,John,Smith,john@email.com,123 Main St,Phoenix,AZ,85001,1985-03-15
5559876543,Jane,Doe,jane@email.com,456 Oak Ave,Tucson,AZ,85701,1990-07-22
```

---

## 🔄 **STEP 3: KEEP VICI UPDATED**

After importing Suraj leads into Brain, update Vici with Brain Lead IDs:

```bash
# Update vendor_lead_codes in Vici
php artisan vici:update-vendor-codes --source=SURAJ_DAILY

# Or push new leads to Vici
php artisan vici:push-new-leads --source=SURAJ_DAILY
```

---

## 🤖 **AUTOMATION SETUP**

### Daily Cron Job for Automatic Import
Add to your crontab:
```bash
# Import Suraj CSV every day at 8 AM
0 8 * * * cd /path/to/brain && php artisan suraj:import-daily /path/to/daily/suraj_$(date +\%Y-\%m-\%d).csv --skip-duplicates --push-to-vici
```

### Or Create a Shell Script:
```bash
#!/bin/bash
# save as import_suraj_daily.sh

DATE=$(date +%Y-%m-%d)
CSV_FILE="/path/to/suraj_leads_${DATE}.csv"

if [ -f "$CSV_FILE" ]; then
    echo "Importing Suraj leads for ${DATE}..."
    cd /path/to/brain
    php artisan suraj:import-daily "$CSV_FILE" --skip-duplicates --push-to-vici
    echo "Import complete!"
else
    echo "No Suraj file found for ${DATE}"
fi
```

---

## 📊 **WHAT GETS IMPORTED**

### From Vici (Historical):
- Lead ID (vici_lead_id)
- Phone Number
- Name (first, last)
- Address (street, city, state, zip)
- Email
- Source (Suraj)
- Call Status
- Entry Date
- Comments

### From CSV (Daily):
- All fields in CSV are mapped automatically
- Phone numbers are cleaned to 10 digits
- Duplicates can be skipped or updated
- Each lead gets a Brain Lead ID (13-digit)
- Source marked as "SURAJ_DAILY"

---

## 🔍 **DUPLICATE HANDLING**

### For Historical Import:
- Checks by phone number
- Use `--update-existing` to update existing leads
- Otherwise skips duplicates

### For Daily CSV:
- Use `--skip-duplicates` to prevent duplicates
- Checks against all existing phone numbers
- Reports which phones were duplicates

---

## 📈 **TRACKING & REPORTING**

### View Imported Suraj Leads:
```php
// In Laravel Tinker or code
$surajLeads = Lead::where('source', 'LIKE', '%SURAJ%')->get();
$todaysImport = Lead::where('source', 'SURAJ_DAILY')
    ->whereDate('created_at', today())
    ->get();
```

### Statistics After Import:
```
========================================
IMPORT COMPLETE
========================================

Total Rows Processed: 500
✅ Imported: 425
🚫 Duplicates Skipped: 70
⚠️  Invalid Phone Numbers: 3
❌ Errors: 2
```

---

## 🚨 **TROUBLESHOOTING**

### Issue: Can't Connect to Vici
**Solution**: Check API credentials in .env:
```
VICI_SERVER=philli.callix.ai
VICI_API_USER=apiuser
VICI_API_PASS=UZPATJ59GJAVKG8ES6
```

### Issue: CSV Headers Not Recognized
**Solution**: The import will show you detected headers. Map manually if needed or rename CSV headers to match expected names.

### Issue: Duplicates Not Being Caught
**Solution**: Ensure phone numbers in CSV are formatted consistently (10 digits). The system strips all non-numeric characters.

### Issue: Leads Not Appearing in Vici
**Solution**: After import, run:
```bash
php artisan vici:push-new-leads --source=SURAJ_DAILY
```

---

## ✅ **COMPLETE WORKFLOW**

### One-Time Historical Import:
```bash
# 1. Import last 3 months from Vici
php artisan vici:import-suraj-leads --start-date=2024-09-01

# 2. Update Vici with Brain Lead IDs
php artisan vici:update-vendor-codes
```

### Daily Process:
```bash
# 1. Import today's CSV
php artisan suraj:import-daily /path/to/todays_suraj.csv --skip-duplicates

# 2. Push to Vici
php artisan vici:push-new-leads --source=SURAJ_DAILY
```

### Result:
- ✅ All historical Suraj leads in Brain
- ✅ Daily imports automated
- ✅ No duplicates
- ✅ Brain and Vici stay in sync
- ✅ Complete tracking of Suraj leads

---

## 📝 **NOTES**

- Suraj leads are marked with source "SURAJ_VICI" (historical) or "SURAJ_DAILY" (CSV)
- Each lead gets a unique 13-digit Brain Lead ID
- Phone numbers are the primary duplicate check
- All imports are logged for audit trail
- CSV files can have any headers - system auto-detects

This ensures ALL Suraj leads (past and future) are properly managed in Brain!
