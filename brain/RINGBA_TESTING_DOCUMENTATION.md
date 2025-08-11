# RingBA Integration Testing Documentation
Last Updated: 2025-08-10

## Testing Process Overview

### Test URL Used
```
https://rtb.ringba.com/v1/production/dabee21382a94ec6aea5e4f05235596d.json?CID=13155551234&zipcode=90210
```

### Parameters
- **CID**: 13155551234 (test phone number)
- **zipcode**: 90210 (Beverly Hills test zip code)

## Testing Evolution

### Phase 1: Initial Marco Endpoint Testing
**Problem**: Used `/marco` endpoint which only returns health check "polo!" response
- **URL**: `https://int.allstateleadmarketplace.com/v2/marco`
- **Result**: 200 OK but no bid data, causing "Missing Bid Amount" errors

### Phase 2: Switching to Correct Endpoint
**Solution**: Changed to `/v2/calls/match` endpoint for actual RTB
- **URL**: `https://int.allstateleadmarketplace.com/v2/calls/match`
- **Method**: POST (not GET)
- **Content-Type**: application/json

### Phase 3: Field Validation Iterations

#### First Attempt - Missing Required Fields
**Error Response**:
```json
{
  "error": "The lead failed validation.",
  "properties": {
    "desired_coverage_type": "The value you selected is not a valid choice.",
    "residence_status": "The value you selected is not a valid choice.",
    "date_of_birth": "This value should not be blank.",
    "tcpa_compliant": "This value should not be null.",
    "external_id": "This value should not be blank."
  }
}
```

#### Second Attempt - Insurance Company Required
**Error Response**:
```json
{
  "error": "The lead failed validation.",
  "properties": {
    "current_insurance_company": "If the consumer is currently_insured, please specify a current_insurance_company."
  }
}
```

#### Final Successful Configuration
**Request Body**:
```json
{
  "api-key": "testvendor",
  "vertical": "auto-insurance",
  "product_variant": "transfers",
  "zipcode": "90210",
  "currently_insured": true,
  "current_insurance_company": "State Farm",
  "external_id": "TEST123",
  "callerid": "3125551234",
  "desired_coverage_type": "standard",
  "residence_status": "rent",
  "date_of_birth": "1985-01-15",
  "dob": "1985-01-15",
  "tcpa_compliant": true,
  "tcpa": true,
  "first_name": "Test",
  "last_name": "User",
  "email": "test@example.com",
  "primary_phone": "3125551234",
  "gender": "male",
  "marital_status": "single"
}
```

**Successful Response**:
```json
{
  "bid": 22.5,
  "call_id": "4a3a68991f9640bb202020835600",
  "carrier_name": "Allstate Insurance",
  "expiration": "2025-08-10 22:45:18",
  "matched": true,
  "phone_number": "+14302345185",
  "_meta": {
    "vendor_name": "testvendor",
    "api_version": "v2.11.266-1-g5128",
    "status_code": 200,
    "response_ms": 283
  }
}
```

## Key Learnings

### 1. Parsing Configuration Issue
**Problem**: Initially copied full JavaScript functions including `function(input){...}` wrapper
**Solution**: RingBA automatically adds the wrapper, only need to provide function body

### 2. Revenue Type Configuration
**Problem**: Dynamic revenue type with `/marco` endpoint caused negative bid amounts
**Solution**: Initially switched to Static revenue, then back to Dynamic with correct endpoint

### 3. Required Field Discovery
**Process**: Iterative testing revealed all required fields through API error messages
**Key Requirements**:
- Both `date_of_birth` and `dob` fields needed
- `tcpa_compliant` must be boolean, not string
- If `currently_insured` is true, must provide `current_insurance_company`
- `residence_status` must use Allstate's valid values ("rent" not "own")

### 4. Final Status
- **API Integration**: ✅ Working
- **Authentication**: ✅ Successful
- **Data Validation**: ✅ Passing
- **Bid Response**: ✅ Received ($22.50)
- **Phone Number**: ✅ Provided for transfer
- **RingBA Internal**: ❌ Shows "Final capacity check" (internal routing issue, not API problem)

## Production Ready Configuration

To switch to production, change:
1. **Base URL**: Remove "int." from all URLs
2. **API Key**: Change "testvendor" to "b91446ade9d37650f93e305cbaf8c2c9"
3. **Auth Header**: Use production Base64 token
4. **Request Body**: Use dynamic RingBA tags instead of static test values

## Test Timeline
- Started: ~5:00 PM EST
- Marco endpoint testing: 5:00-5:50 PM
- Switched to /calls/match: 6:00 PM
- Field validation iterations: 6:00-6:35 PM
- Successful response: 6:39 PM EST

Total testing time: ~1.5 hours to achieve successful API integration

