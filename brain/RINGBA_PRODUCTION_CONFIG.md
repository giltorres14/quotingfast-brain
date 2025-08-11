# RingBA Production Configuration for Allstate

## Request Settings - PRODUCTION

### URL
```
https://api.allstateleadmarketplace.com/v2/calls/match
```

### HTTP Method
```
POST
```

### Content Type
```
application/json
```

### HTTP Headers
```
Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
```

### Request Body
```json
{
  "api-key": "b91446ade9d37650f93e305cbaf8c2c9",
  "vertical": "auto-insurance",
  "product_variant": "transfers",
  "zipcode": "[tag:user:zip_code]",
  "currently_insured": [tag:user:currently_insured],
  "current_insurance_company": "[tag:user:current_insurance_company]",
  "external_id": "[tag:user:external_id]",
  "primary_phone": "[tag:user:primary_phone]",
  "desired_coverage_type": "standard",
  "residence_status": "[tag:user:residence_status]",
  "date_of_birth": "[tag:user:date_of_birth]",
  "tcpa_compliant": [tag:user:tcpa_compliant],
  "first_name": "[tag:user:first_name]",
  "last_name": "[tag:user:last_name]",
  "email": "[tag:user:email]",
  "gender": "[tag:user:gender]",
  "marital_status": "[tag:user:marital_status]",
  "dui": [tag:user:dui],
  "requires_sr22": [tag:user:requires_sr22],
  "valid_license": [tag:user:valid_license],
  "continuous_coverage": [tag:user:continuous_coverage],
  "state": "[tag:user:state]",
  "city": "[tag:user:city]",
  "address": "[tag:user:address]"
}
```

**IMPORTANT**: Notice that boolean fields do NOT have quotes around the tag:
- `"currently_insured": [tag:user:currently_insured]` - NO quotes, so it sends as boolean
- `"tcpa_compliant": [tag:user:tcpa_compliant]` - NO quotes
- `"dui": [tag:user:dui]` - NO quotes
- `"requires_sr22": [tag:user:requires_sr22]` - NO quotes
- `"valid_license": [tag:user:valid_license]` - NO quotes

String fields HAVE quotes:
- `"first_name": "[tag:user:first_name]"` - WITH quotes
- `"state": "[tag:user:state]"` - WITH quotes

## Confirmation Request Settings

### URL
```
https://api.allstateleadmarketplace.com/v2/calls/post/[bid-id]
```

### HTTP Method
```
POST
```

### Content Type
```
application/json
```

### Request Body
```json
{
  "home_phone": "[tag:InboundNumber:AreaCode][tag:InboundNumber:Prefix][tag:InboundNumber:Suffix]"
}
```

### HTTP Headers
```
Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
```

## What The Brain Sends to RingBA

After the JavaScript conversion in the enrichment button, The Brain sends these URL parameters to RingBA:

### Insured Customer Example:
```
?primary_phone=5551234567
&currently_insured=true
&current_insurance_company=StateFarm
&continuous_coverage=12
&valid_license=true
&num_vehicles=2
&dui=false
&requires_sr22=false
&state=FL
&zip_code=33101
&first_name=John
&last_name=Doe
&email=john@example.com
&date_of_birth=1985-01-15
&gender=male
&marital_status=single
&residence_status=own
&tcpa_compliant=true
&external_id=1754677675000
```

### Uninsured Customer Example:
```
?primary_phone=5551234567
&currently_insured=false
&current_insurance_company=
&continuous_coverage=0
&valid_license=true
&num_vehicles=1
&dui=true
&requires_sr22=true
&state=FL
&zip_code=33101
...
```

## Summary

✅ **Dropdowns stay user-friendly** (yes/no, dui_only, etc.)
✅ **JavaScript converts to Allstate format** (true/false, separate dui/sr22 fields)
✅ **RingBA receives correct values** as URL parameters
✅ **RingBA sends to Allstate** using tags with proper boolean/string formatting

The logic is all in place - you just need to update the RingBA Request Body configuration to use the correct tag names and remove quotes from boolean fields!

