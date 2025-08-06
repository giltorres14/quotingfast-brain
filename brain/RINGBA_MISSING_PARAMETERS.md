# Missing RingBA URL Parameters for Allstate Integration

Based on the comprehensive Allstate API integration, here are the URL parameters that need to be created in RingBA. Each parameter should be sent individually using the POST request format you specified.

## **MISSING PARAMETERS TO CREATE IN RINGBA**

### **Personal Information**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"first_name","mapToTagName":"first_name","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"last_name","mapToTagName":"last_name","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"email","mapToTagName":"email","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"phone","mapToTagName":"phone","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"address1","mapToTagName":"address1","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"city","mapToTagName":"city","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"state","mapToTagName":"state","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"country","mapToTagName":"country","mapToTagType":"User"}
```

### **Demographics**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"dob","mapToTagName":"dob","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"gender","mapToTagName":"gender","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"marital_status","mapToTagName":"marital_status","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"residence_status","mapToTagName":"residence_status","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"education_level","mapToTagName":"education_level","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"occupation","mapToTagName":"occupation","mapToTagType":"User"}
```

### **Insurance Status**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"currently_insured","mapToTagName":"currently_insured","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"current_insurance_company","mapToTagName":"current_insurance_company","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"policy_expiration_date","mapToTagName":"policy_expiration_date","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"current_premium","mapToTagName":"current_premium","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"insurance_duration","mapToTagName":"insurance_duration","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"policy_expires","mapToTagName":"policy_expires","mapToTagType":"User"}
```

### **Coverage Requirements**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"coverage_level","mapToTagName":"coverage_level","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"deductible_preference","mapToTagName":"deductible_preference","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"coverage_type","mapToTagName":"coverage_type","mapToTagType":"User"}
```

### **Financial Information**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"credit_score_range","mapToTagName":"credit_score_range","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"credit_score","mapToTagName":"credit_score","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"home_ownership","mapToTagName":"home_ownership","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"home_status","mapToTagName":"home_status","mapToTagType":"User"}
```

### **Driving Information**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"years_licensed","mapToTagName":"years_licensed","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"accidents_violations","mapToTagName":"accidents_violations","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"dui_conviction","mapToTagName":"dui_conviction","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"sr22_required","mapToTagName":"sr22_required","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"license_age","mapToTagName":"license_age","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"active_license","mapToTagName":"active_license","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"dui_timeframe","mapToTagName":"dui_timeframe","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"dui_sr22","mapToTagName":"dui_sr22","mapToTagType":"User"}
```

### **Vehicle Information**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"num_vehicles","mapToTagName":"num_vehicles","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"vehicle_year","mapToTagName":"vehicle_year","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"vehicle_make","mapToTagName":"vehicle_make","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"vehicle_model","mapToTagName":"vehicle_model","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"vehicle_trim","mapToTagName":"vehicle_trim","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"vin","mapToTagName":"vin","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"leased","mapToTagName":"leased","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"annual_mileage","mapToTagName":"annual_mileage","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"primary_use","mapToTagName":"primary_use","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"commute_days","mapToTagName":"commute_days","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"commute_mileage","mapToTagName":"commute_mileage","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"garage_type","mapToTagName":"garage_type","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"alarm","mapToTagName":"alarm","mapToTagType":"User"}
```

### **Lead Quality & Timing**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"lead_source","mapToTagName":"lead_source","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"lead_quality_score","mapToTagName":"lead_quality_score","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"urgency_level","mapToTagName":"urgency_level","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"best_time_to_call","mapToTagName":"best_time_to_call","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"motivation_level","mapToTagName":"motivation_level","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"motivation_score","mapToTagName":"motivation_score","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"urgency","mapToTagName":"urgency","mapToTagType":"User"}
```

### **TCPA & Compliance**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"consent_timestamp","mapToTagName":"consent_timestamp","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"opt_in_method","mapToTagName":"opt_in_method","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"tcpa_compliant","mapToTagName":"tcpa_compliant","mapToTagType":"User"}
```

### **Technical Data**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"ip_address","mapToTagName":"ip_address","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"user_agent","mapToTagName":"user_agent","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"referrer_url","mapToTagName":"referrer_url","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"landing_page","mapToTagName":"landing_page","mapToTagType":"User"}
```

### **Agent Qualification Metadata**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"qualified_by_agent","mapToTagName":"qualified_by_agent","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"qualification_timestamp","mapToTagName":"qualification_timestamp","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"agent_notes","mapToTagName":"agent_notes","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"call_duration","mapToTagName":"call_duration","mapToTagType":"User"}
```

### **Additional Qualification Questions**
```
POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"shopping_for_rates","mapToTagName":"shopping_for_rates","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"ready_to_speak","mapToTagName":"ready_to_speak","mapToTagType":"User"}

POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps
Body: {"incomingQueryStringName":"allstate_quote","mapToTagName":"allstate_quote","mapToTagType":"User"}
```

## **PARAMETERS YOU ALREADY HAVE (NO ACTION NEEDED)**

These are already configured in your RingBA setup:
- `active_dl` ✅
- `adset_name` ✅
- `allstate` ✅
- `allstate_quote_inlastmonth` ✅ (similar to `allstate_quote`)
- `autos` ✅ (similar to `num_vehicles`)
- `clickid` ✅
- `continuous_coverage` ✅ (similar to `insurance_duration`)
- `current_insurance` ✅ (similar to `current_insurance_company`)
- `desired_coverage_type` ✅
- `dui` ✅ (similar to `dui_conviction`)
- `external_id` ✅
- `homeowner` ✅ (similar to `home_ownership`)
- `insured` ✅ (similar to `currently_insured`)
- `insured_allstate` ✅
- `lead_id` ✅
- `multi_vehicle` ✅ (similar to `num_vehicles`)
- `ready_totalk` ✅ (similar to `ready_to_speak`)
- `sr22` ✅ (similar to `sr22_required`)
- `state_name` ✅ (similar to `state`)
- `subid` ✅
- `tcpa_text` ✅
- `tcpa_url` ✅
- `transfer_agent` ✅
- `transfer_to_allstate` ✅
- `utm_*` parameters ✅
- `vehicles` ✅
- `zip_code` / `zipcode` / `Zip5` / `zipcode_number` ✅

## **TOTAL: 63 New Parameters to Create**

This comprehensive list ensures you can capture ALL the data points that the Allstate API integration uses, making your RingBA system fully compatible with current and future buyer integrations.
