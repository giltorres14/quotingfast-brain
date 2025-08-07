# RingBA API Authentication Required

## **Issue Identified** ❌
The script attempted to create 69 parameters but all failed with **HTTP 401 Authorization** errors.

```
❌ HTTP 401: {"message":"Authorization has been denied for this request."}
```

## **Root Cause**
The RingBA API requires authentication beyond just the account ID. You need an **API token/key**.

## **Solution Options**

### **Option 1: Get API Token from RingBA Dashboard (Recommended)**

1. **Login to RingBA Dashboard**
2. **Go to Settings > API Settings** (or similar)
3. **Generate/Find your API Token**
4. **Update the script with authentication**

Once you have the API token, update the script:

```php
// In create_ringba_parameters.php, find this section:
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
    // Add your API authentication here:
    // 'Authorization: Bearer YOUR_API_TOKEN',
    // OR
    // 'X-API-Key: YOUR_API_TOKEN',
]);
```

### **Option 2: Manual Creation via Dashboard (Immediate Solution)**

Since API authentication is required, you can create the parameters manually:

1. **Login to RingBA Dashboard**
2. **Go to Settings > URL Parameters**
3. **For each parameter below, click "Add Parameter":**

**Parameters to Create (69 total):**
```
first_name, last_name, email, phone, address1, city, state, country, dob, gender, 
marital_status, residence_status, education_level, occupation, currently_insured, 
current_insurance_company, policy_expiration_date, current_premium, insurance_duration, 
policy_expires, coverage_level, deductible_preference, coverage_type, credit_score_range, 
credit_score, home_ownership, home_status, years_licensed, accidents_violations, 
dui_conviction, sr22_required, license_age, active_license, dui_timeframe, dui_sr22, 
num_vehicles, vehicle_year, vehicle_make, vehicle_model, vehicle_trim, vin, leased, 
annual_mileage, primary_use, commute_days, commute_mileage, garage_type, alarm, 
lead_source, lead_quality_score, urgency_level, best_time_to_call, motivation_level, 
motivation_score, urgency, consent_timestamp, opt_in_method, tcpa_compliant, ip_address, 
user_agent, referrer_url, landing_page, qualified_by_agent, qualification_timestamp, 
agent_notes, call_duration, shopping_for_rates, ready_to_speak, allstate_quote
```

**For each parameter:**
- **Incoming Query String Name**: `parameter_name`
- **Map to Tag Name**: `parameter_name`  
- **Map to Tag Type**: `User`

### **Option 3: Contact RingBA Support**

If you can't find the API authentication method:

1. **Contact RingBA Support**
2. **Ask for**:
   - API token/key for your account `RAf810ac4421a34c9cbfbbf61288a1bec2`
   - Proper authentication method for the `/queryPathMaps` endpoint
   - Documentation for bulk parameter creation

## **Next Steps**

**Immediate Action:**
1. Check your RingBA dashboard for API settings
2. Look for API token, API key, or authentication credentials
3. Let me know what authentication method RingBA uses

**Once Authentication is Found:**
I'll update the script with the proper authentication and we can run it again to create all 69 parameters automatically.

**Alternative:**
If you prefer to create them manually via the dashboard, that works too - just more time-consuming but guaranteed to work.

## **Account Details Confirmed** ✅
- **Account ID**: `RAf810ac4421a34c9cbfbbf61288a1bec2`
- **API URL**: `https://api.ringba.com/v2/RAf810ac4421a34c9cbfbbf61288a1bec2/queryPathMaps`
- **Parameters Ready**: 69 parameters identified and ready to create

The script is ready to go once we have the authentication method! 🚀

