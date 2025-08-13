# LQF (LeadsQuotingFast) Import Mapping Documentation

## Overview
The LQF bulk import command processes CSV exports from LeadsQuotingFast system. The CSV contains 33 columns with comprehensive lead data including personal information, insurance details, and tracking metadata.

## Command Usage

```bash
# Test with dry run (preview only)
php artisan lqf:bulk-import ~/Downloads/LQF/file.csv --limit=10 --dry-run

# Import first 100 records
php artisan lqf:bulk-import ~/Downloads/LQF/file.csv --limit=100

# Import all records, skip duplicates
php artisan lqf:bulk-import ~/Downloads/LQF/file.csv --skip-duplicates

# Full import
php artisan lqf:bulk-import ~/Downloads/LQF/file.csv
```

## Complete Field Mapping

### Core Lead Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Lead ID | meta.lead_id | string | 77388176 | Original LQF lead ID |
| Timestamp | created_at | datetime | 2025-05-01 00:05:49 | When lead was processed |
| Originally Created | opt_in_date | datetime | 2025-05-01 00:05:37 | **CRITICAL for 90-day archiving** |

### Personal Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| First Name | first_name | string | ALEX | Stored in leads table |
| Last Name | last_name | string | URQUIAGA | Stored in leads table |
| Email | email | string | alex@gmail.com | Validated email |
| Phone | phone | string | 7862140855 | Primary phone, 10 digits |
| Phone 2 | meta.phone_2 | string | | Secondary phone if exists |

### Address Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Address | address | string | 19705 sw 130th ave | Street address |
| Address 2 | meta.address_2 | string | Apt 4B | Additional address |
| City | city | string | Miami | City name |
| State | state | string | FL | 2-letter state code |
| ZIP Code | zip_code | string | 33177 | 5-digit ZIP |

### Business Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Vertical | type | string | Auto Insurance | Mapped to auto/home/health/life |
| Vendor | vendor_name | string | Otto Quotes - Auto shared | Lead source |
| Vendor Campaign | meta.vendor_campaign | string | 1013452 | Vendor's campaign ID |
| Vendor Status | meta.vendor_status | string | Purchased | Purchase status |
| Buyer | buyer_name | string | QF - FL Flinsco | Lead buyer |
| Buyer Campaign | campaign_id | string | 1023907 | Extracted ID from string |
| Buyer Status | meta.buyer_status | string | Purchased | Buyer's status |

### Financial Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Buy Price | meta.buy_price | decimal | 0.75 | Price paid for lead |
| Sell Price | meta.sell_price | decimal | 0.75 | Price sold for |
| Return Reason | meta.return_reason | string | | If returned |

### Tracking Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Source ID | meta.source_id | string | 4000 | Traffic source |
| Offer ID | meta.offer_id | string | 51792576-3b5a | Offer identifier |
| LeadiD Code | meta.leadid_code | string | 4886D05C-D367 | Jornaya LeadiD |
| IP Address | meta.ip_address | string | 76.229.166.180 | User's IP |

### Compliance Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| TCPA | tcpa_compliant | boolean | Yes | Parsed to true/false |
| TCPA Consent Text | meta.tcpa_consent_text | text | Full consent text | Complete TCPA language |
| Trusted Form Cert URL | meta.trusted_form_cert | string | https://cert... | TrustedForm certificate |

### Technical Information
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| User Agent | meta.user_agent | string | Mozilla/5.0... | Browser information |
| Landing Page URL | meta.landing_page | string | https://l.otto... | Original landing page |

### Insurance Data (JSON)
| CSV Column | DB Field | Type | Example | Notes |
|------------|----------|------|---------|-------|
| Data | Multiple | JSON | See below | Complex nested data |

#### Data Field Structure:
```json
{
  "drivers": [
    {
      "first_name": "ALEX",
      "last_name": "URQUIAGA",
      "birth_date": "2005-12-04",
      "marital_status": "Single",
      "relationship": "Self",
      "gender": "M",
      "license_status": "Active",
      "license_state": "FL",
      "age_licensed": 18,
      "residence_type": "Rent",
      "occupation": "Unknown",
      "education": "High School Diploma",
      "requires_sr22": false
    }
  ],
  "vehicles": [
    {
      "year": 2009,
      "make": "NISSAN",
      "model": "ALTIMA",
      "submodel": "2.5S",
      "vin": "1N4AL24E*9*******",
      "alarm": "Audible Alarm",
      "four_wheel_drive": false,
      "airbags": true,
      "automatic_seat_belts": true,
      "garage": "Full Garage",
      "ownership": "Own",
      "primary_use": "Commute School",
      "annual_miles": 12000,
      "one_way_distance": 7,
      "comprehensive_deductible": "500",
      "collision_deductible": "500"
    }
  ],
  "requested_policy": {
    "coverage_type": "Standard"
  }
}
```

## Data Storage Strategy

### Primary Fields (leads table)
- Basic personal information (name, phone, email)
- Address information
- Source and type
- Campaign and buyer/vendor names
- TCPA compliance flag
- Opt-in date

### JSON Fields
1. **payload**: Complete original CSV row data
2. **meta**: Additional metadata and tracking information
3. **drivers**: Array of driver information
4. **vehicles**: Array of vehicle information
5. **current_policy**: Policy request information

## Validation Rules

### Required Fields
- Phone number (must be 10 digits)
- Source (set to 'LQF')
- Type (auto/home/health/life)
- tenant_id (always 1)

### Data Transformations
1. **Phone**: Remove all non-digits, validate 10 digits
2. **TCPA**: Convert Yes/No/True/False/1/0 to boolean
3. **Dates**: Parse various formats to Y-m-d H:i:s
4. **Campaign ID**: Extract numeric ID from buyer campaign string
5. **Type**: Map vertical to standardized types

## Error Handling

### Common Issues
1. **Invalid phone numbers**: Skipped with log entry
2. **Malformed JSON in Data field**: Stored as string in payload
3. **Missing required fields**: Lead created with available data
4. **Duplicate phone numbers**: Skipped if --skip-duplicates flag

### Logging
- Errors logged to Laravel log with row number
- Summary statistics shown at end
- Progress updates every 100 records

## Performance Considerations

### Optimizations
- Batch processing available (not implemented yet)
- Duplicate checking uses indexed phone column
- Vendors/Buyers cached to avoid repeated lookups

### Expected Performance
- ~10-20 leads/second for individual inserts
- ~50-100 leads/second with batch inserts (future)

## Testing Recommendations

1. **Always start with dry-run**: `--dry-run` flag
2. **Test small batch first**: `--limit=10`
3. **Check data quality**: Review first few imported leads
4. **Verify relationships**: Check vendor/buyer/campaign creation
5. **Validate JSON parsing**: Ensure drivers/vehicles properly stored

## Sample Import Commands

```bash
# Development testing
php artisan lqf:bulk-import ~/Downloads/LQF/test.csv --limit=5 --dry-run

# Production test
DB_CONNECTION=pgsql php artisan lqf:bulk-import ~/Downloads/LQF/file.csv --limit=100

# Full production import
DB_CONNECTION=pgsql php artisan lqf:bulk-import ~/Downloads/LQF/file.csv --skip-duplicates
```

## Notes for User Review

1. **Opt-in Date**: Currently using "Originally Created" field - confirm this is correct
2. **Campaign ID Extraction**: Extracts 7-digit number from buyer campaign - verify pattern
3. **Type Mapping**: Auto-detecting from vertical field - review mapping logic
4. **Duplicate Handling**: Currently based on phone only - consider email too?
5. **Data Field**: Complex JSON being parsed - review if all fields needed

---
*Documentation created: August 13, 2025*
*Ready for user review upon waking*

