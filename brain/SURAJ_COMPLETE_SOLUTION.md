# 📥 SURAJ LEADS - COMPLETE SOLUTION

## Overview
Complete solution for importing Suraj leads with two approaches:
1. **Bulk Import Historical CSVs** - Import all past CSV files (strict no duplicates)
2. **Automated Daily Import** - Watch folder or web portal with LQF duplicate rules

---

## 🗂️ **PART 1: BULK IMPORT HISTORICAL CSV FILES**

### Import All CSV Files From Your Computer Folder

```bash
# Tell me where your Suraj CSV files are, and I'll import them one by one
php artisan suraj:bulk-import /path/to/your/suraj/folder

# Example - if files are in Downloads/suraj_leads/
php artisan suraj:bulk-import ~/Downloads/suraj_leads

# Preview first (dry run)
php artisan suraj:bulk-import ~/Downloads/suraj_leads --dry-run

# Process oldest files first (recommended for chronological order)
php artisan suraj:bulk-import ~/Downloads/suraj_leads --oldest-first

# Import and push each file to Vici
php artisan suraj:bulk-import ~/Downloads/suraj_leads --oldest-first --push-to-vici
```

### What This Does:
- ✅ Scans folder for ALL CSV files
- ✅ Processes them one by one
- ✅ **STRICT DUPLICATE CHECK** - No phone number imported twice
- ✅ Shows progress for each file
- ✅ Maintains import order (oldest first if specified)
- ✅ Comprehensive statistics at the end

### Example Output:
```
📂 Found 47 CSV files to process

📄 File 1/47: suraj_2024-09-01.csv
   Date: 2024-09-01 | Size: 125 KB
   ✅ Processed: 500 rows
   New: 425 | Duplicates: 75

📄 File 2/47: suraj_2024-09-02.csv
   Date: 2024-09-02 | Size: 98 KB
   ✅ Processed: 380 rows
   New: 310 | Duplicates: 70

[... continues for all files ...]

BULK IMPORT COMPLETE
Files Processed: 47/47
Total Rows: 18,500
✅ Imported: 14,250
🚫 Duplicates Skipped: 4,100
⚠️  Invalid Phones: 150
```

---

## 🔄 **PART 2: AUTOMATED DAILY IMPORTS**

### Option A: Watch Folder (Automatic)

Set up a folder where Suraj drops CSV files:

```bash
# Create watch folder
mkdir /path/to/suraj_daily_folder

# Start watching (runs continuously)
php artisan suraj:watch-folder /path/to/suraj_daily_folder --push-to-vici

# Watch with processed file management
php artisan suraj:watch-folder /path/to/suraj_daily_folder --move-processed --push-to-vici
```

**Features:**
- Checks folder every 60 seconds
- Auto-imports new CSV files
- Uses **LQF DUPLICATE RULES**:
  - 0-10 days: Updates existing lead
  - 11-90 days: Creates re-engagement lead
  - 91+ days: Creates new lead
- Moves processed files to `processed/` subfolder
- Pushes to Vici automatically

### Option B: Web Upload Portal

Access the upload portal at:
```
https://quotingfast-brain-ohio.onrender.com/suraj/upload
```

**Features:**
- 🖱️ Drag & drop CSV files
- 📊 Real-time import statistics
- ✅ Automatic duplicate handling (LQF rules)
- 📤 Auto-push to Vici
- 🔒 Secure upload with validation

### Option C: Automated Cron Job

Add to crontab for fully automated daily processing:

```bash
# Check for new files every hour
0 * * * * cd /path/to/brain && php artisan suraj:watch-folder /path/to/suraj_folder --once --push-to-vici

# Or specific time daily (8 AM)
0 8 * * * cd /path/to/brain && php artisan suraj:watch-folder /path/to/suraj_folder --once --push-to-vici
```

---

## 📋 **CSV FORMAT**

The system **auto-detects** common header variations:

| Field | Accepted Headers |
|-------|-----------------|
| Phone | phone, phone_number, telephone, mobile, cell |
| First Name | first_name, firstname, fname, first |
| Last Name | last_name, lastname, lname, last |
| Email | email, email_address |
| Address | address, address1, street |
| City | city, town |
| State | state, province, st |
| ZIP | zip, zip_code, zipcode, postal_code |

### Example CSV:
```csv
phone,first_name,last_name,email,city,state,zip
5551234567,John,Smith,john@email.com,Phoenix,AZ,85001
5559876543,Jane,Doe,jane@email.com,Tucson,AZ,85701
```

---

## 🎯 **DUPLICATE HANDLING RULES**

### For Historical Bulk Import:
- **STRICT MODE** - Any duplicate phone = SKIP
- No updates, no re-engagement
- Ensures clean initial import

### For Daily Imports (LQF Rules):
```
Phone exists & ≤ 10 days old → UPDATE existing lead
Phone exists & 11-90 days old → CREATE re-engagement lead
Phone exists & > 90 days old → CREATE new lead
Phone doesn't exist → CREATE new lead
```

---

## 🚀 **QUICK START GUIDE**

### Step 1: Import All Historical CSV Files
```bash
# Point to your folder with all past CSV files
php artisan suraj:bulk-import /Users/YourName/Downloads/suraj_csvs --oldest-first

# This will import ALL files with strict no-duplicate rule
```

### Step 2: Set Up Daily Import
Choose one:

**A) Watch Folder:**
```bash
# Create folder for Suraj to drop files
mkdir ~/suraj_daily

# Start watching
php artisan suraj:watch-folder ~/suraj_daily --push-to-vici
```

**B) Web Portal:**
Share this URL with Suraj:
```
https://quotingfast-brain-ohio.onrender.com/suraj/upload
```

### Step 3: Verify in Brain
Check imported leads:
```
https://quotingfast-brain-ohio.onrender.com/leads?source=SURAJ
```

---

## 📊 **MONITORING & REPORTING**

### View Suraj Leads:
```sql
-- All Suraj leads
SELECT * FROM leads WHERE source LIKE '%SURAJ%';

-- Today's imports
SELECT * FROM leads 
WHERE source = 'SURAJ_AUTO' 
AND DATE(created_at) = CURDATE();

-- Re-engagement leads
SELECT * FROM leads 
WHERE status = 'RE_ENGAGEMENT' 
AND source LIKE '%SURAJ%';
```

### Check Import Stats:
```bash
# In Laravel Tinker
$stats = Lead::where('source', 'LIKE', '%SURAJ%')
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->get();
```

---

## 🛠️ **TROUBLESHOOTING**

### Issue: "Folder not found"
```bash
# Create the folder first
mkdir -p /path/to/folder

# Then run import
php artisan suraj:bulk-import /path/to/folder
```

### Issue: Headers not recognized
The system will show you what headers it found:
```
Column Mapping:
   phone => Phone Number
   first_name => First
   last_name => Last
   [missing] => email
```
Rename CSV headers to match expected names.

### Issue: Duplicates in daily import
Daily imports use LQF rules (update if < 10 days).
For strict no-duplicates, use bulk import instead.

---

## ✅ **COMPLETE WORKFLOW**

### One-Time Historical Import:
```bash
# 1. Import all historical CSVs (strict no duplicates)
php artisan suraj:bulk-import /path/to/historical/csvs --oldest-first

# 2. Push all to Vici
php artisan vici:push-new-leads --source=SURAJ_BULK
```

### Ongoing Daily Process:
```bash
# Option 1: Watch folder
php artisan suraj:watch-folder /path/to/daily/folder --push-to-vici

# Option 2: Web portal
# Share: https://quotingfast-brain-ohio.onrender.com/suraj/upload
```

---

## 🎉 **BENEFITS**

1. **No Manual Work** - Fully automated import
2. **No Duplicates** - Smart duplicate handling
3. **Complete History** - Import months of data at once
4. **Daily Automation** - Set and forget
5. **Vici Integration** - Auto-push to dialer
6. **Full Tracking** - Know exactly what was imported

This ensures ALL Suraj leads (past and future) flow seamlessly into Brain → Vici → Agents!
