# Allstate API Field Format Validation

## Critical Format Requirements from Allstate

### 1. Date Fields
**Format Required**: `YYYY-MM-DD` (e.g., "1985-03-15")
- ✅ `date_of_birth` - Main lead DOB
- ✅ `dob` - Driver DOB (in drivers array)
- ✅ `policy_expiration_date` - Current policy expiration

### 2. Boolean Fields (MUST be boolean, not strings)
**Format Required**: `true` or `false` (no quotes in JSON)
- ✅ `tcpa_compliant` or `tcpa` - TCPA consent
- ✅ `currently_insured` - Insurance status
- ✅ `dui` - DUI conviction status
- ✅ `requires_sr22` - SR22 requirement
- ✅ `valid_license` - License validity
- ✅ `tickets_and_accidents` - Any incidents (boolean)
- ✅ `leased` - Vehicle lease status
- ✅ `alarm` - Vehicle alarm status

### 3. String Fields (MUST have specific values)

#### Gender
**Required Values**: `"male"`, `"female"`, `"unknown"`, `"X"`
- ❌ NOT: "M", "F", "Male", "Female"

#### Marital Status
**Required Values**: `"single"`, `"married"`, `"separated"`, `"divorced"`, `"widowed"`
- ❌ NOT: "Single", "Married" (no capitals)

#### Education Level (edu_level)
**Required Values**: `"GED"`, `"HS"`, `"SCL"`, `"ADG"`, `"BDG"`, `"MDG"`, `"DOC"`
- `GED` - GED
- `HS` - High School
- `SCL` - Some College
- `ADG` - Associate Degree
- `BDG` - Bachelor's Degree
- `MDG` - Master's Degree
- `DOC` - Doctorate

#### Occupation
**Required Values**: Specific enum list including:
`"MARKETING"`, `"SALES"`, `"ADMINMGMT"`, `"SUPERVISOR"`, etc.
- ❌ NOT: "Marketing", "Sales" (must be uppercase)

#### Residence Status
**Required Values**: `"rent"`, `"own"`, `"other"`
- ❌ NOT: "home", "renting", "owning"

#### Driver Relation
**Required Values**: `"self"`, `"spouse"`, `"child"`, `"parent"`, `"other"`
- First driver MUST be `"self"`

#### Vehicle Primary Use
**Required Values**: `"pleasure"`, `"business"`, `"commutework"`, `"selfemployed"`, `"school"`, `"farm"`, `"gov"`, `"other"`
- ❌ NOT: "commute" (must be "commutework")

#### Garage Type
**Required Values**: `"carport"`, `"garage"`, `"nocover"`, `"private"`
- ❌ NOT: "no_cover", "no cover"

### 4. Integer Fields (MUST be numbers, not strings)
- ✅ `continuous_coverage` - Months of coverage (0, 6, 12, 24, etc.)
- ✅ `years_licensed` - Years with license
- ✅ `years_employed` - Years at job
- ✅ `years_at_residence` - Years at address
- ✅ `license_age` - Age when first licensed
- ✅ `commute_days` - Days per week commuting
- ✅ `commute_mileage` - One-way miles
- ✅ `annual_mileage` - Miles per year
- ✅ `year` - Vehicle year

### 5. Field Name Requirements

#### CORRECT Field Names (What Allstate Expects):
- `dob` (NOT `date_of_birth` in driver object)
- `tcpa` or `tcpa_compliant` (both accepted)
- `requires_sr22` (NOT `sr22` or `sr22_required`)
- `id` for driver/vehicle (NOT `driver_number` or `vehicle_number`)
- `edu_level` (NOT `education_level` or `education`)
- `primary_phone` (NOT `phone` or `phone_number`)

## Current Brain → RingBA Field Mapping Status

| Field | Brain Sends | Format | Allstate Expects | Status |
|-------|------------|--------|------------------|--------|
| currently_insured | "true"/"false" | String | boolean true/false | ✅ Correct |
| allstate | "true"/"false" | String | Used by RingBA for routing | ✅ NEW |
| dui | "true"/"false" | String | boolean true/false | ✅ Correct |
| requires_sr22 | "true"/"false" | String | boolean true/false | ✅ Correct |
| valid_license | "true"/"false" | String | boolean true/false | ✅ Correct |
| tcpa_compliant | "true" | String | boolean true/false | ✅ Correct |
| date_of_birth | "1985-01-15" | String YYYY-MM-DD | String YYYY-MM-DD | ✅ Correct |
| gender | User input | String | "male"/"female" lowercase | ⚠️ Need validation |
| marital_status | User input | String | lowercase specific values | ⚠️ Need validation |
| residence_status | "own"/"rent" | String | "own"/"rent" | ✅ Correct |
| state | "FL", "NY" | String | State code | ✅ Correct |
| continuous_coverage | 0, 6, 12, 24 | Number | Integer | ✅ Correct |

## RingBA Configuration Requirements

### For Boolean Fields in Request Body:
```json
{
  "currently_insured": [tag:user:currently_insured],  // NO quotes
  "tcpa_compliant": [tag:user:tcpa_compliant],       // NO quotes
  "dui": [tag:user:dui],                             // NO quotes
  "requires_sr22": [tag:user:requires_sr22],         // NO quotes
  "valid_license": [tag:user:valid_license]          // NO quotes
}
```

### For String Fields in Request Body:
```json
{
  "first_name": "[tag:user:first_name]",             // WITH quotes
  "gender": "[tag:user:gender]",                     // WITH quotes
  "marital_status": "[tag:user:marital_status]",     // WITH quotes
  "state": "[tag:user:state]"                        // WITH quotes
}
```

### For Integer Fields in Request Body:
```json
{
  "continuous_coverage": [tag:user:continuous_coverage],  // NO quotes
  "years_licensed": [tag:user:years_licensed]            // NO quotes
}
```

## Required Validation Updates in Brain

### 1. Gender Validation
```javascript
const mapGender = (v) => {
    const g = (v || '').toLowerCase();
    if (g === 'm' || g === 'male') return 'male';
    if (g === 'f' || g === 'female') return 'female';
    if (g === 'x') return 'X';
    return 'unknown';
};
```

### 2. Marital Status Validation
```javascript
const mapMaritalStatus = (v) => {
    const m = (v || '').toLowerCase();
    const valid = ['single', 'married', 'separated', 'divorced', 'widowed'];
    return valid.includes(m) ? m : 'single';
};
```

### 3. Education Level Mapping
```javascript
const mapEducation = (v) => {
    const eduMap = {
        'ged': 'GED',
        'high school': 'HS',
        'some college': 'SCL',
        'associate': 'ADG',
        'bachelor': 'BDG',
        'master': 'MDG',
        'doctorate': 'DOC'
    };
    return eduMap[v.toLowerCase()] || 'HS';
};
```

## Summary of Required Changes

1. ✅ **Boolean fields** - Already sending as "true"/"false" strings
2. ⚠️ **Gender field** - Need to ensure lowercase "male"/"female"
3. ⚠️ **Marital status** - Need to ensure lowercase specific values
4. ⚠️ **Education level** - Need to map to correct enum codes
5. ✅ **Date formats** - Already in YYYY-MM-DD format
6. ✅ **Integer fields** - Already sending as numbers
7. ⚠️ **RingBA config** - Must remove quotes from boolean/integer tags
