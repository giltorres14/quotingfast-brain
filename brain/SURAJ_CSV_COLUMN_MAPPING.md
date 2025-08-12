# 📊 SURAJ CSV TO BRAIN FIELD MAPPING

## Based on Your Actual CSV File: `10jun_auto_lead.csv`

### ✅ **FIELDS THAT WILL BE IMPORTED**

| Suraj CSV Column | Sample Value | → | Brain Database Field | Notes |
|-----------------|--------------|---|---------------------|--------|
| **PhoneNumber** | 6828886054 | → | **phone** | Cleaned to 10 digits, primary identifier |
| **FirstName** | Kenneth | → | **first_name** | |
| **LastName** | Takett | → | **last_name** | |
| **FirstName + LastName** | Kenneth Takett | → | **name** | Combined full name |
| **EmailAddress** | ktackett10@hotmail.com | → | **email** | |
| **MailAddress1** | 108 Gradic Lane | → | **address** | Street address |
| **CityName** | Dothan | → | **city** | |
| **ProvinceStateName** | AL | → | **state** | State abbreviation |
| **PostalZipCode** | 36301 | → | **zip_code** | 5-digit ZIP |
| **BirthDate** | (if present) | → | **date_of_birth** | Parsed to date format |
| **Gender** | M | → | **gender** | Stored as 'male'/'female' |

### 🔄 **AUTOMATIC FIELD GENERATION**

| Brain Field | How It's Generated | Example |
|------------|-------------------|---------|
| **external_lead_id** | 13-digit timestamp | 1734567890123 |
| **source** | Set based on import type | "SURAJ_BULK" or "SURAJ_AUTO" |
| **type** | Default for all Suraj | "auto" |
| **campaign_id** | Date-based campaign | "SURAJ_2024-12-20" |
| **status** | Based on duplicate rules | "NEW", "DUPLICATE_UPDATED", etc. |
| **created_at** | Import timestamp | 2024-12-20 10:30:00 |

### 📦 **METADATA STORED (in 'meta' JSON field)**

All these extra fields from Suraj CSV are preserved in the `meta` field as JSON:

```json
{
  "suraj_original_data": {
    "id": "amFuZ2wud2VibGVhZHMubG9ncy52ZXJpZnkrMCs2MTgzOTg5",
    "jangl_id": "bzzybjrkypfkf",
    "vendor_id": "1003803.0",
    "vendor_name": "Quinn Street - Quinn Street Home Leads",
    "buyer_id": "1007846.0",
    "buyer_name": "QF - QF - Alabama HOI",
    "Age": "",
    "ContactabilityLevel": "VERY_HIGH",
    "ContactIsQualified": "1.0",
    "EmailIsValid": "1.0",
    "PhoneActivityScore": "100.0",
    "PhoneIsLitigator": "0.0",
    "PhoneIsValid": "1.0",
    "PhoneLineType": "MOBILE",
    "PhoneStatus": "CONNECTED",
    "LeadIPAddress": "",
    "LeadUserAgent": "",
    "vertical": "home_insurance",
    "timestamp": "2025-06-09T14:02:03.004Z"
  },
  "import_file": "10jun_auto_lead.csv",
  "import_date": "2024-12-20T15:30:00Z",
  "import_method": "bulk_import"
}
```

### 🚫 **FIELDS NOT DIRECTLY MAPPED** (But Preserved in Meta)

These fields don't have direct Brain equivalents but are saved in metadata:
- log_type, event_type, event_name
- jangl_id, vertical, vertical_name
- vendor_campaign_id, vendor_id, vendor_name
- buyer_campaign_id, buyer_id, buyer_name
- integration, integration_id, integration_name
- request/response details
- ContactabilityLevel, ContactIsQualified
- PhoneActivityScore, PhoneIsLitigator, PhoneLineType
- All "_Out" fields (cleaned versions)

---

## 🔄 **DUPLICATE HANDLING**

### For Bulk Historical Import:
```
if (PhoneNumber already exists in Brain) {
    SKIP - Don't import
} else {
    CREATE new lead with all mapped fields
}
```

### For Daily Auto Import (LQF Rules):
```
if (PhoneNumber exists) {
    if (existing lead < 10 days old) {
        UPDATE existing lead with new data
    } else if (existing lead 11-90 days old) {
        CREATE re-engagement lead
    } else {
        CREATE new lead
    }
} else {
    CREATE new lead
}
```

---

## 💡 **EXAMPLE IMPORT PREVIEW**

When you run the import, you'll see:

```bash
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --dry-run

Column Mapping:
   phone => PhoneNumber
   first_name => FirstName
   last_name => LastName
   email => EmailAddress
   address => MailAddress1
   city => CityName
   state => ProvinceStateName
   zip => PostalZipCode
```

### Sample Lead Import:
```
CSV Row:
  PhoneNumber: 6828886054
  FirstName: Kenneth
  LastName: Takett
  EmailAddress: ktackett10@hotmail.com
  CityName: Dothan
  State: AL
  ZIP: 36301

↓ Becomes ↓

Brain Lead:
  phone: 6828886054
  first_name: Kenneth
  last_name: Takett
  name: Kenneth Takett
  email: ktackett10@hotmail.com
  address: 108 Gradic Lane
  city: Dothan
  state: AL
  zip_code: 36301
  source: SURAJ_BULK
  type: auto
  campaign_id: SURAJ_2024-12-20
  external_lead_id: 1734567890123
  meta: {full JSON with all extra fields}
```

---

## ✅ **VALIDATION RULES**

1. **Phone Number**: 
   - Must be 10 digits after cleaning
   - Removes country code if present (1)
   - Strips all non-numeric characters

2. **Email**:
   - Basic format validation
   - Stored as-is if valid

3. **State**:
   - Accepts 2-letter abbreviations
   - Full state names converted to abbreviations

4. **ZIP Code**:
   - Accepts 5 or 9 digit formats
   - Stores first 5 digits

---

## 🎯 **READY TO IMPORT**

Your folder: `~/Downloads/Suraj Leads/`

Command to run:
```bash
# Preview mapping and import simulation
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --dry-run

# Actual import (oldest files first)
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --oldest-first

# Import and push to Vici
php artisan suraj:bulk-import ~/Downloads/Suraj\ Leads --oldest-first --push-to-vici
```

The system will:
1. Auto-detect all these columns
2. Map them correctly to Brain fields
3. Skip any duplicate phone numbers
4. Show you progress for each file
5. Give complete statistics at the end

