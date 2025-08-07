# RingBA Integration - Brain System

## **Overview**
RingBA is our call tracking and lead routing platform. It manages URL parameters for campaign tracking and routes qualified leads to appropriate buyers.

## **Integration Status**
- **Status**: ✅ Fully Active
- **API Version**: v2
- **Account ID**: [Configure in scripts]
- **Total Parameters**: 95 URL parameters
- **Last Updated**: August 2025

---

## **URL Parameter Management**

### **Complete Parameter Registry (95 Total)**

#### **Pre-Existing Parameters (33)**
```
active_dl, adset_name, allstate, allstate_quote_inlastmonth, autos, clickid, 
continuous_coverage, current_insurance, desired_coverage_type, dui, external_id, 
homeowner, insured, insured_allstate, lead_id, multi_vehicle, ready_totalk, sr22, 
state_name, subid, tcpa_text, tcpa_url, transfer_agent, transfer_to_allstate, 
utm_ad_name, utm_campaign, utm_medium, utm_source, vehicles, zip_code, Zip5, 
zipcode, zipcode_number
```

#### **Allstate Integration Parameters (62 - Added Aug 2025)**
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

---

## **Parameter Creation Process**

### **Automated Method (Recommended)**

1. **Configure Account ID**:
   ```bash
   # Edit the script
   nano scripts/create_parameters.php
   # Update: $accountId = 'YOUR_RINGBA_ACCOUNT_ID';
   ```

2. **Run Parameter Creation**:
   ```bash
   cd api-integrations/ringba/scripts/
   php create_parameters.php
   ```

3. **Verify Creation**:
   - Login to RingBA dashboard
   - Go to Settings > URL Parameters
   - Confirm all parameters are listed

### **Manual API Method**

For each parameter:
```bash
curl -X POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "incomingQueryStringName": "parameter_name",
    "mapToTagName": "parameter_name", 
    "mapToTagType": "User"
  }'
```

---

## **Adding Parameters for New Buyers**

### **Step 1: Analyze Buyer Requirements**
```bash
# Create analysis file
touch configurations/new-buyer-analysis.md
```

Document:
- Required API fields
- Field naming conventions
- Data types and formats
- Special requirements

### **Step 2: Compare with Existing Parameters**
```bash
# Check what we already have
grep -i "field_name" configurations/complete-parameter-list.txt
```

### **Step 3: Create Missing Parameters**
```bash
# Add new parameters to creation script
nano scripts/create_parameters.php
# Add to $parameters array
```

### **Step 4: Update Documentation**
- Add to parameter registry above
- Update total count
- Note which buyer integration they support

---

## **Configuration Files**

### **Parameter Categories**

#### **Personal Information**
- `first_name`, `last_name`, `email`, `phone`
- `address1`, `city`, `state`, `country`
- `dob`, `gender`, `marital_status`

#### **Insurance Data**
- `currently_insured`, `current_insurance_company`
- `policy_expiration_date`, `current_premium`
- `coverage_level`, `desired_coverage_type`

#### **Vehicle Information**
- `num_vehicles`, `vehicle_year`, `vehicle_make`, `vehicle_model`
- `vin`, `annual_mileage`, `primary_use`

#### **Lead Quality Metrics**
- `lead_source`, `lead_quality_score`, `urgency_level`
- `motivation_level`, `best_time_to_call`

#### **Compliance & Tracking**
- `tcpa_compliant`, `consent_timestamp`
- `utm_source`, `utm_campaign`, `utm_medium`
- `clickid`, `subid`, `external_id`

---

## **Testing & Validation**

### **Parameter Testing**
```bash
# Test parameter creation
curl -X POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps \
  -H "Content-Type: application/json" \
  -d '{"incomingQueryStringName":"test_param","mapToTagName":"test_param","mapToTagType":"User"}'
```

### **Campaign Testing**
1. Create test campaign in RingBA
2. Add test parameters to tracking URLs
3. Verify parameter capture in reports
4. Test data flow to Brain system

---

## **Troubleshooting**

### **Common Issues**

#### **Parameter Creation Fails**
- ✅ Verify account ID is correct
- ✅ Check API authentication
- ✅ Ensure parameter doesn't already exist
- ✅ Validate JSON payload format

#### **Parameters Not Showing in Reports**
- ✅ Verify parameter is active
- ✅ Check campaign configuration
- ✅ Confirm URL format is correct
- ✅ Test with sample traffic

#### **Data Not Flowing to Brain**
- ✅ Check webhook configuration
- ✅ Verify parameter mapping
- ✅ Review Brain webhook handler
- ✅ Check logs for errors

### **Debug Commands**
```bash
# List all parameters
curl -X GET https://api.ringba.com/v2/{{accountId}}/queryPathMaps

# Test parameter creation
php scripts/test_parameter_creation.php

# Validate webhook flow
tail -f /path/to/brain/storage/logs/laravel.log | grep ringba
```

---

## **Maintenance**

### **Monthly Tasks**
- [ ] Review parameter usage statistics
- [ ] Clean up unused parameters
- [ ] Update documentation for any API changes
- [ ] Verify all integrations are using correct parameters

### **Quarterly Tasks**
- [ ] Audit all buyer integrations
- [ ] Update parameter categories
- [ ] Review and optimize parameter structure
- [ ] Plan for new integration requirements

---

## **API Documentation**

### **Base URL**
```
https://api.ringba.com/v2/
```

### **Authentication**
- Method: API Key or Bearer Token
- Header: `Authorization: Bearer {token}`

### **Key Endpoints**
- `GET /{{accountId}}/queryPathMaps` - List parameters
- `POST /{{accountId}}/queryPathMaps` - Create parameter
- `PUT /{{accountId}}/queryPathMaps/{{id}}` - Update parameter
- `DELETE /{{accountId}}/queryPathMaps/{{id}}` - Delete parameter

### **Parameter Object Structure**
```json
{
  "incomingQueryStringName": "parameter_name",
  "mapToTagName": "parameter_name",
  "mapToTagType": "User"
}
```

---

## **Support Resources**

- **RingBA API Documentation**: https://developers.ringba.com/
- **Support Portal**: [Add RingBA support URL]
- **Account Dashboard**: https://app.ringba.com/
- **Brain Integration Logs**: `/storage/logs/laravel.log`

---

*Last Updated: August 6, 2025*
*Parameters: 95 total*
*Integrations Supported: Allstate, Future Buyers*

