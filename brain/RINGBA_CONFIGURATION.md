# RingBA Configuration for Allstate Integration
Last Updated: 2025-08-10

## Overview
This document contains the complete RingBA Ring Tree Target configuration for Allstate call transfers.

## Ring Tree Target: RTT Allstate - Transfers Only

### Request Settings (Basic Mode)

#### URL
```
TEST: https://int.allstateleadmarketplace.com/v2/calls/match
PRODUCTION: https://api.allstateleadmarketplace.com/v2/calls/match
```

#### HTTP Method
```
POST
```

#### Content Type
```
application/json
```

#### HTTP Headers
```
TEST:
  Authorization: Basic dGVzdHZlbmRvcjo=

PRODUCTION:
  Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
```

#### Request Body
```json
TEST ENVIRONMENT:
{
  "api-key": "testvendor",
  "vertical": "auto-insurance",
  "product_variant": "transfers",
  "zipcode": "[tag:user:zipcode]",
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

PRODUCTION (with dynamic tags):
{
  "api-key": "b91446ade9d37650f93e305cbaf8c2c9",
  "vertical": "auto-insurance",
  "product_variant": "transfers",
  "zipcode": "[tag:user:zipcode]",
  "currently_insured": true,
  "current_insurance_company": "[tag:user:current_carrier]",
  "external_id": "[tag:user:external_id]",
  "callerid": "[tag:call:ani]",
  "desired_coverage_type": "[tag:user:coverage_type]",
  "residence_status": "[tag:user:residence_status]",
  "date_of_birth": "[tag:user:dob]",
  "tcpa_compliant": true,
  "first_name": "[tag:user:first_name]",
  "last_name": "[tag:user:last_name]",
  "email": "[tag:user:email]",
  "primary_phone": "[tag:call:ani]",
  "gender": "[tag:user:gender]",
  "marital_status": "[tag:user:marital_status]"
}
```

### Parsing Configuration

#### 1. Dynamic Bid Parsing
**Type**: JavaScript
```javascript
  try {
    var data = JSON.parse(input);
    return data.bidAmount || data.bid_amount || data.bid || 0;
  } catch(e) {
    return 0;
  }
```

#### 2. Call Acceptance Parsing
**Type**: JavaScript
```javascript
  try {
    var data = JSON.parse(input);
    return data.matched === true || data.matched === "true";
  } catch(e) {
    return false;
  }
```

#### 3. Dynamic Number/SIP Parsing
**Type**: JavaScript
```javascript
  try {
    var data = JSON.parse(input);
    return data.phone_number || "";
  } catch(e) {
    return "";
  }
```

#### 4. Bid ID Parsing
**Type**: JavaScript
```javascript
  try {
    var data = JSON.parse(input);
    return data.call_id || data.bid_id || data.id || "";
  } catch(e) {
    return "";
  }
```

### Revenue Settings

#### Conversion Settings
- **Mode**: Override (not Use Ring Tree Settings)
- **Revenue Type**: Dynamic
- **Failure Revenue Amount**: $0

#### Minimum Revenue Amount
- **Mode**: Override
- **Amount**: $0

### Confirmation Request Settings

#### Configuration
- **Confirmation Request**: ON
- **Confirmation Request Timeout**: 4 seconds
- **Confirmation Request Required**: OFF
- **Bid ID Required**: ON
- **Use Number from Confirmation Request**: OFF

#### Confirmation Request URL
```
TEST: https://int.allstateleadmarketplace.com/v2/calls/post/[bid-id]
PRODUCTION: https://api.allstateleadmarketplace.com/v2/calls/post/[bid-id]
```

#### HTTP Method
```
POST
```

#### Content Type
```
application/json
```

#### Request Body
```json
{
  "home_phone": "[tag:InboundNumber:AreaCode][tag:InboundNumber:Prefix][tag:InboundNumber:Suffix]"
}
```

#### HTTP Headers
```
TEST:
  Authorization: Basic dGVzdHZlbmRvcjo=

PRODUCTION:
  Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
```

## Test Results

### Successful Test Response (2025-08-10)
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

This confirms:
- ✅ API connectivity working
- ✅ Authentication successful
- ✅ Lead data accepted
- ✅ Bid returned ($22.50)
- ✅ Transfer number provided
- ✅ All parsing functions working correctly

## Important Notes

1. **RingBA Auto-wraps JavaScript**: When entering parsing code, only enter the function body. RingBA automatically adds `function(input){` and closing `}`.

2. **Test vs Production**: The test environment uses different credentials and may have limited capacity. The "Final capacity check (Code: 1006)" error in RingBA is internal routing logic, not an API issue.

3. **Required Fields**: Allstate requires:
   - date_of_birth (format: YYYY-MM-DD)
   - tcpa_compliant (boolean, not string)
   - external_id (unique identifier)
   - All contact information fields

4. **Tag Mapping**: RingBA tags should be mapped from your enrichment data:
   - [tag:user:*] - User/lead data from enrichment

   - [tag:call:*] - Call-specific data
   - [tag:InboundNumber:*] - Incoming phone number components

## Troubleshooting

1. **"Missing Bid Amount"**: Ensure Dynamic Bid Parsing is configured and Revenue Type is set to Dynamic.

2. **"Call Acceptance Parsing Rejection"**: Verify the matched field is being parsed correctly as boolean true.

3. **400 Bad Request**: Check that all required fields are present and properly formatted in the request body.

4. **Authentication Failed (403)**: Verify the Authorization header is correct for the environment (test vs production).
