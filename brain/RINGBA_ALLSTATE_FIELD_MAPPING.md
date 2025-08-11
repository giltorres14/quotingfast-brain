# RingBA to Allstate Field Mapping Requirements

## Critical Field Conversions Needed

### Current Issues and Solutions

| Field | Brain Sends to RingBA | RingBA Tag | Allstate Expects | Solution |
|-------|----------------------|------------|------------------|----------|
| **currently_insured** | "true" or "false" (after fix) | [tag:user:currently_insured] | boolean true/false | ✅ Fixed - Brain now sends "true"/"false" |
| **active_dl** | "true" or "false" (after fix) | [tag:user:active_dl] | boolean valid_license | Need to map field name |
| **received_quote** | "true" or "false" (after fix) | [tag:user:received_quote] | Not used by Allstate | OK - extra field |
| **ready_to_talk** | "true" or "false" (after fix) | [tag:user:ready_to_talk] | Not used by Allstate | OK - extra field |
| **dui_option** | "DUI", "SR22", "DUI_SR22", "no" | [tag:user:dui_option] | Needs: dui (boolean), requires_sr22 (boolean) | ⚠️ NEEDS CONVERSION |
| **continuous_coverage** | 0, 5, 6, 12, 24, 60 | [tag:user:continuous_coverage] | integer months | ✅ OK |
| **state_name** | "FL", "NY", etc | [tag:user:state_name] | "FL", "NY", etc | ✅ OK |
| **zip_code** | "12345" | [tag:user:zip_code] | "12345" | ✅ OK |
| **vehicle_count** | "1", "2", etc | [tag:user:vehicle_count] | Not directly used | OK |
| **callerid** | "1234567890" | [tag:user:callerid] | primary_phone | Need to map field name |

## Required Changes in Brain

### 1. Update the enrichment JavaScript to send Allstate-compatible values:

```javascript
// For Insured enrichment
const orderedPairs = [
    ['phone', digits(data.phone)],  // Changed from 'callerid'
    ['currently_insured', yn(data.currently_insured)], // Now sends "true"/"false"
    ['current_insurance_company', data.current_provider || ''],  // Changed from 'current_carrier'
    ['continuous_coverage', mapContinuous(data.insurance_duration)], // OK as is
    ['valid_license', yn(data.active_license)], // Changed from 'active_dl'
    ['num_vehicles', data.num_vehicles || ''], // Changed from 'vehicle_count'
    ['dui', data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both' ? 'true' : 'false'],
    ['requires_sr22', data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both' ? 'true' : 'false'],
    ['state', (data.state_input || data.state || '')], // Changed from 'state_name'
    ['zip_code', data.zip_code || ''], // OK
    ['first_name', data.first_name || ''],
    ['last_name', data.last_name || ''],
    ['email', data.email || ''],
    ['date_of_birth', data.date_of_birth || ''], // Added
    ['gender', data.gender || ''], // Added
    ['marital_status', data.marital_status || ''], // Added
    ['residence_status', mapResidence(data.homeowner)], // Map homeowner to residence_status
    ['tcpa_compliant', 'true'], // Always true for enriched leads
    ['external_id', leadId] // Add lead ID
];
```

### 2. Update RingBA Request Body Configuration:

```json
{
  "api-key": "b91446ade9d37650f93e305cbaf8c2c9",
  "vertical": "auto-insurance",
  "product_variant": "transfers",
  "zipcode": "[tag:user:zip_code]",
  "currently_insured": "[tag:user:currently_insured]",
  "current_insurance_company": "[tag:user:current_insurance_company]",
  "external_id": "[tag:user:external_id]",
  "primary_phone": "[tag:user:phone]",
  "desired_coverage_type": "standard",
  "residence_status": "[tag:user:residence_status]",
  "date_of_birth": "[tag:user:date_of_birth]",
  "tcpa_compliant": "[tag:user:tcpa_compliant]",
  "first_name": "[tag:user:first_name]",
  "last_name": "[tag:user:last_name]",
  "email": "[tag:user:email]",
  "gender": "[tag:user:gender]",
  "marital_status": "[tag:user:marital_status]",
  "dui": "[tag:user:dui]",
  "requires_sr22": "[tag:user:requires_sr22]",
  "valid_license": "[tag:user:valid_license]",
  "continuous_coverage": "[tag:user:continuous_coverage]",
  "state": "[tag:user:state]"
}
```

## Helper Functions Needed

```javascript
// Convert homeowner to residence_status
const mapResidence = (homeowner) => {
    if (homeowner === 'Y' || homeowner === 'yes' || homeowner === true) {
        return 'own';
    }
    return 'rent';
};

// Boolean converter (updated)
const yn = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'true' : 'false');
```

## Summary of Changes Required

1. ✅ **DONE**: Change yn() function to return "true"/"false" instead of "Y"/"N"
2. ⚠️ **TODO**: Split dui_option into two separate boolean fields: dui and requires_sr22
3. ⚠️ **TODO**: Rename fields to match Allstate's exact requirements
4. ⚠️ **TODO**: Add missing fields (date_of_birth, gender, marital_status, etc.)
5. ⚠️ **TODO**: Map homeowner to residence_status properly
6. ⚠️ **TODO**: Update RingBA configuration to use correct tag names
