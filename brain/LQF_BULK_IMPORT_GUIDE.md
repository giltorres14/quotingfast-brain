# 📥 LQF BULK IMPORT GUIDE
*Import historical LeadsQuotingFast CSV exports into Brain*

---

## 📁 SETUP

1. **Place CSV files in folder**:
   - Location: `~/Downloads/LQF/`
   - Files should be LQF export CSVs
   - Example: `webleads_export_2025-08-01_-_2025-08-31.csv`

---

## 🚀 IMPORT COMMANDS

### 1. **Dry Run (Test First)**
See what would be imported without actually doing it:
```bash
php artisan lqf:import-bulk --dry-run
```

### 2. **Live Import**
Actually import the leads:
```bash
php artisan lqf:import-bulk
```

### 3. **Import and Push to Vici**
Import leads and immediately push them to Vici List 101:
```bash
php artisan lqf:import-bulk --push-to-vici
```

### 4. **Custom Folder**
Import from a different location:
```bash
php artisan lqf:import-bulk --folder=/path/to/csv/folder
```

---

## 📊 CSV COLUMN MAPPING

The importer automatically maps these LQF CSV columns:

| **LQF Column** | **Brain Field** | **Description** |
|----------------|-----------------|-----------------|
| Lead ID | meta.lqf_lead_id | Original LQF lead ID |
| Timestamp | created_at | When lead was created |
| Vertical | type | auto/home insurance |
| Buy Price | cost | What we paid for lead |
| Sell Price | sell_price | What we sold lead for |
| Vendor | vendor_name | Lead source |
| Vendor Campaign | vendor_campaign | Vendor's campaign |
| Buyer | buyer_name | Who bought the lead |
| Buyer Campaign | buyer_campaign | Buyer's campaign |
| LeadiD Code | tcpa_lead_id | TCPA tracking ID |
| Trusted Form Cert URL | trusted_form_cert | TrustedForm certificate |
| TCPA | tcpa_compliant | TCPA compliance flag |
| First Name | first_name | Lead's first name |
| Last Name | last_name | Lead's last name |
| Email | email | Lead's email |
| Phone | phone | Lead's phone (10 digits) |
| Address | address | Street address |
| City | city | City |
| State | state | State code |
| ZIP Code | zip_code | ZIP code |
| IP Address | ip_address | Lead's IP |
| User Agent | user_agent | Browser info |
| Landing Page URL | landing_page_url | Where lead came from |
| Source ID | meta.source_id | LQF source ID |
| Offer ID | meta.offer_id | LQF offer ID |

---

## 🔄 DUPLICATE HANDLING

**For bulk historical imports:**
- Checks phone number against ALL existing leads
- Skips any duplicate phone numbers
- This is STRICT duplicate prevention (no updates)

**Why different from daily imports?**
- Historical imports = one-time, prevent any duplicates
- Daily imports = use smart duplicate rules (0-10 day update, etc.)

---

## 📈 AUTO-CREATION FEATURES

The importer automatically:

1. **Creates Vendors**:
   - Extracts vendor name (removes extra info after dash)
   - Creates vendor record if new
   - Adds campaign to vendor's campaign list

2. **Creates Buyers**:
   - Cleans buyer name (removes parentheses, suffixes)
   - Creates buyer record if new
   - Adds campaign to buyer's campaign list

---

## 🎯 EXAMPLE WORKFLOW

```bash
# 1. First, do a dry run to see what will happen
php artisan lqf:import-bulk --dry-run

# Output:
# Found 5 CSV files to process
# [1/5] Processing: webleads_export_2025-08-01.csv
#   → Imported: 150, Skipped: 23, Errors: 0
# ...
# This was a DRY RUN - no data was actually imported

# 2. If looks good, run the actual import
php artisan lqf:import-bulk

# 3. Optional: Also push to Vici
php artisan lqf:import-bulk --push-to-vici
```

---

## 📊 IMPORT STATISTICS

After import, you'll see:
- Files Processed
- Total Rows
- Imported
- Skipped (Duplicates)
- Errors
- New Vendors created
- New Buyers created

---

## ⚠️ IMPORTANT NOTES

1. **Phone Format**: Automatically cleans to 10 digits
2. **Vendor Names**: Cleans "Quinn Street - Quinn Street Auto 2" → "Quinn Street"
3. **Buyer Names**: Cleans "What If Media Group, LLC () - Auto" → "What If Media Group, LLC"
4. **TCPA**: Converts "Yes"/"No" to boolean
5. **Timestamps**: Uses CSV timestamp for created_at
6. **External Lead ID**: Generates new 9-digit ID for Vici

---

## 🔧 TROUBLESHOOTING

**"No CSV files found"**
- Check folder path: `~/Downloads/LQF/`
- Ensure files have `.csv` extension

**"Missing phone number"**
- Some rows may not have phones
- These are skipped automatically

**High duplicate count**
- Normal for historical imports
- Shows system is preventing duplicates

---

*Last Updated: December 20, 2024*

