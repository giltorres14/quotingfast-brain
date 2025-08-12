# 📊 SURAJ CSV - FINAL COLUMN MAPPING

## Based on Your Requirements and Actual CSV Structure

### ✅ **PRIMARY FIELDS MAPPED TO BRAIN**

| CSV Column | Field Name | Sample Value | → | Brain Field | Notes |
|------------|------------|--------------|---|-------------|--------|
| Column 30 | **PhoneNumber** | 6828886054 | → | **phone** | Primary identifier |
| Column 27 | **FirstName** | Kenneth | → | **first_name** | |
| Column 28 | **LastName** | Takett | → | **last_name** | |
| | **Full Name** | Kenneth Takett | → | **name** | Combined |
| Column 29 | **EmailAddress** | ktackett10@hotmail.com | → | **email** | |
| Column 31 | **MailAddress1** | 108 Gradic Lane | → | **address** | |
| Column 32 | **CityName** | Dothan | → | **city** | |
| Column 33 | **ProvinceStateName** | AL | → | **state** | |
| Column 34 | **PostalZipCode** | 36301 | → | **zip_code** | |

### 💼 **BUSINESS FIELDS**

| CSV Column | Field Name | Sample Value | Storage Location | Purpose |
|------------|------------|--------------|------------------|---------|
| **Column 7** | vertical_name | "home_insurance" or "auto" | → **type** field | Determines if auto or home lead |
| **Column 8** | vendor_campaign_id | "1011298" | → **campaign_id** field | Vendor Campaign ID |
| **Column 10** | vendor_name | "Quinn Street" | → meta.vendor | Vendor (who you buy from) |
| **Column 13** | buyer_name | "QF - Alabama HOI" | → meta.buyer | Buyer (who buys from you) |
| **Column D** | cost | (if present) | → meta.cost | What you paid |
| **Column E** | sell_price | (if present) | → meta.sell_price | What you sell for |

### 🔄 **AUTOMATIC ASSIGNMENTS**

| Brain Field | Value | Reason |
|------------|-------|--------|
| **source** | "Suraj" | All leads from Suraj |
| **type** | "auto" or "home" | Based on vertical_name column |
| **campaign_id** | From vendor_campaign_id | Or "SURAJ_[date]" if empty |
| **external_lead_id** | 13-digit timestamp | Unique Brain ID |

### 📦 **COMPLETE METADATA STORAGE**

All business and tracking data stored in `meta` JSON field:

```json
{
  "vendor": "Quinn Street - Quinn Street Home Leads",
  "vendor_id": "1003803",
  "vendor_campaign_id": "1011298",
  "buyer": "QF - QF - Alabama HOI",
  "buyer_id": "1007846",
  "cost": "5.00",  // If available in your CSV
  "sell_price": "25.00",  // If available in your CSV
  "vertical": "home_insurance",
  "lead_quality": {
    "ContactabilityLevel": "VERY_HIGH",
    "ContactIsQualified": "1.0",
    "EmailIsValid": "1.0",
    "PhoneActivityScore": "100.0",
    "PhoneIsLitigator": "0.0",
    "PhoneIsValid": "1.0",
    "PhoneLineType": "MOBILE",
    "PhoneStatus": "CONNECTED"
  },
  "import_details": {
    "file": "10jun_auto_lead.csv",
    "date": "2024-12-20",
    "original_id": "amFuZ2wud2VibGVhZHMubG9ncy52ZXJpZnkrMCs2MTgzOTg5"
  }
}
```

---

## 🎯 **HOW TO RUN THE IMPORT**

### Command:
```bash
# Preview the import and mapping
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --dry-run

# Actual import with all mappings
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --oldest-first
```

### What You'll See:
```
📂 Found 50 CSV files to process

📄 File 1/50: 10jun_auto_lead.csv
Column Mapping Detected:
   phone => PhoneNumber (Column 30)
   first_name => FirstName (Column 27)
   last_name => LastName (Column 28)
   type => vertical_name (Column 7) [auto/home]
   vendor => vendor_name (Column 10)
   buyer => buyer_name (Column 13)
   
Processing...
   ✅ Imported: 425 leads
   Type breakdown: 380 auto, 45 home
   Vendors: Quinn Street (200), ABC Leads (225)
   Buyers: QF (300), Allstate (125)
```

---

## 📊 **IMPORT RESULTS**

After import, your leads will have:

1. **Core Contact Info**: Phone, Name, Email, Address
2. **Lead Type**: Properly set as "auto" or "home"
3. **Source**: All marked as "Suraj"
4. **Business Data**: Vendor, Buyer, Campaign preserved in meta
5. **Quality Scores**: All phone/email validation data preserved
6. **Full Traceability**: Original file, import date, original ID

---

## ✅ **VALIDATION & RULES**

1. **Phone Number**: 
   - Must be valid 10-digit US number
   - Primary duplicate check field

2. **Lead Type**:
   - "auto" if vertical_name contains "auto"
   - "home" if vertical_name contains "home"
   - Defaults to "auto" if unclear

3. **Duplicate Handling**:
   - **Bulk Import**: Skip all duplicates (strict)
   - **Daily Import**: LQF rules (0-10 update, 11-90 re-engage, 91+ new)

---

## 📝 **NOTES**

- If columns D (cost) and E (sell_price) aren't in the current CSV structure, we can add custom logic to calculate or set default values
- All vendor/buyer relationships are preserved for reporting
- The system automatically handles variations in column positions
- Every field from Suraj is preserved, nothing is lost

Ready to import when you are!


