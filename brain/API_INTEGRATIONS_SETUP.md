# API Integrations Setup Guide - Brain System

## **RingBA URL Parameter Management**

### **Overview**
RingBA requires URL parameters to be created individually via their API before they can be used in campaigns. This guide covers how to create parameters for new buyer integrations.

### **Current RingBA URL Parameters (Complete List)**

#### **EXISTING PARAMETERS (Already Configured)**
```
active_dl, adset_name, allstate, allstate_quote_inlastmonth, autos, clickid, 
continuous_coverage, current_insurance, desired_coverage_type, dui, external_id, 
homeowner, insured, insured_allstate, lead_id, multi_vehicle, ready_totalk, sr22, 
state_name, subid, tcpa_text, tcpa_url, transfer_agent, transfer_to_allstate, 
utm_ad_name, utm_campaign, utm_medium, utm_source, vehicles, zip_code, Zip5, 
zipcode, zipcode_number
```

#### **ALLSTATE INTEGRATION PARAMETERS (Added August 2025)**
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

**TOTAL PARAMETERS: ~95 URL parameters available**

---

## **Setting Up New Buyer Integration Parameters**

### **Step 1: Analyze Buyer API Requirements**

1. **Get API Documentation** from the new buyer
2. **Identify Required Fields** - Look for:
   - Personal information (name, email, phone, address)
   - Demographics (age, gender, marital status)
   - Insurance details (current provider, policy info)
   - Vehicle information (year, make, model, VIN)
   - Financial data (credit score, income, home ownership)
   - Lead quality metrics (source, urgency, motivation)
   - TCPA compliance fields

3. **Compare with Existing Parameters**
   - Check the complete list above
   - Identify which parameters are missing
   - Note any naming differences (e.g., buyer uses `date_of_birth` vs our `dob`)

### **Step 2: Create Missing Parameters in RingBA**

#### **Method 1: Automated Script (Recommended)**

1. **Update Account ID** in the script:
   ```bash
   # Edit the script
   nano /path/to/brain/create_ringba_parameters.php
   
   # Change this line:
   $accountId = 'YOUR_ACCOUNT_ID'; // Replace with actual account ID
   ```

2. **Add API Authentication** (if required):
   ```php
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
       'Content-Type: application/json',
       'Accept: application/json',
       'Authorization: Bearer YOUR_API_TOKEN', // Add if needed
   ]);
   ```

3. **Run the Script**:
   ```bash
   cd /path/to/brain
   php create_ringba_parameters.php
   ```

#### **Method 2: Manual API Calls**

For each missing parameter, send:
```bash
curl -X POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"incomingQueryStringName":"PARAMETER_NAME","mapToTagName":"PARAMETER_NAME","mapToTagType":"User"}'
```

#### **Method 3: RingBA Dashboard (Manual)**

1. Login to RingBA dashboard
2. Go to **Settings > URL Parameters**
3. Click **"Add Parameter"**
4. Fill in:
   - **Incoming Query String Name**: `parameter_name`
   - **Map to Tag Name**: `parameter_name`
   - **Map to Tag Type**: `User`
5. Click **Save**
6. Repeat for each parameter

### **Step 3: Verify Parameters**

1. **Check RingBA Dashboard**:
   - Go to Settings > URL Parameters
   - Verify all new parameters are listed
   - Test with a sample campaign

2. **Update Brain Documentation**:
   - Add new parameters to this list
   - Update the TOTAL count
   - Note which buyer integration they're for

---

## **Brain System Integration Process**

### **Step 1: Create Buyer Service Class**

```php
// Example: app/Services/NewBuyerCallTransferService.php
class NewBuyerCallTransferService
{
    private function prepareLeadData($lead, $qualificationData = [])
    {
        // Map Brain lead data to buyer's API format
        return [
            'external_id' => $lead->external_lead_id,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            // ... map all required fields
        ];
    }
    
    public function transferCall($lead, $vertical, $qualificationData = [])
    {
        $transferData = $this->prepareLeadData($lead, $qualificationData);
        
        // Send to buyer API
        $response = Http::post($this->apiUrl, $transferData);
        
        return [
            'success' => $response->successful(),
            'response' => $response->json()
        ];
    }
}
```

### **Step 2: Update Routes**

```php
// Add to routes/web.php
Route::post('/webhook/newbuyer', function () {
    // Handle buyer webhook callbacks
});
```

### **Step 3: Create Testing Dashboard**

Follow the Allstate testing dashboard pattern:
```php
// Create: resources/views/admin/newbuyer-testing.blade.php
// Create: app/Services/NewBuyerTestingService.php
// Add route: Route::get('/admin/newbuyer-testing', ...)
```

### **Step 4: Update Documentation**

1. **Add to API_CONFIGURATIONS.md**:
   - API endpoints
   - Authentication details
   - Field mappings
   - Test credentials

2. **Update CHANGE_LOG.md**:
   - Document all changes
   - Note any temporary configurations
   - Track success metrics

3. **Update PROJECT_MEMORY.md**:
   - Add to integrations registry
   - Update lead flow diagrams
   - Note critical configurations

---

## **Best Practices for New Integrations**

### **Field Mapping Strategy**

1. **Use Smart Logic**:
   ```php
   // Prioritize data sources: Agent qualification > Lead data > Defaults
   private function getBestValue($sources, $default = null)
   {
       foreach ($sources as $source) {
           if (!empty($source)) return $source;
       }
       return $default;
   }
   ```

2. **Handle Data Types**:
   ```php
   // Ensure correct data types
   'age' => (int) $calculatedAge,
   'insured' => (bool) $insuranceStatus,
   'premium' => (float) $monthlyPremium,
   ```

3. **Map Enumerations**:
   ```php
   private function mapGender($gender)
   {
       $mapping = ['M' => 'male', 'F' => 'female'];
       return $mapping[strtoupper($gender)] ?? 'unknown';
   }
   ```

### **Error Handling**

1. **Comprehensive Logging**:
   ```php
   Log::info('Buyer API call initiated', ['lead_id' => $lead->id]);
   Log::error('Buyer API failed', ['error' => $e->getMessage()]);
   ```

2. **Validation**:
   ```php
   // Validate required fields before sending
   if (empty($transferData['email'])) {
       throw new Exception('Email is required for buyer API');
   }
   ```

### **Testing Strategy**

1. **Create Test Dashboard** (like Allstate testing)
2. **Use Auto-Qualification** for testing without agents
3. **Log All Transactions** for debugging
4. **Monitor Success Rates** and response times

---

## **Maintenance Checklist**

### **Monthly Review**
- [ ] Check parameter usage in RingBA dashboard
- [ ] Review API success rates
- [ ] Update field mappings if buyer API changes
- [ ] Clean up unused parameters

### **New Integration Checklist**
- [ ] Analyze buyer API requirements
- [ ] Identify missing RingBA parameters
- [ ] Create parameters using scripts/manual process
- [ ] Update this documentation
- [ ] Test integration thoroughly
- [ ] Monitor for 1 week post-launch

### **Documentation Updates**
- [ ] Add new parameters to the complete list above
- [ ] Update total parameter count
- [ ] Note buyer-specific requirements
- [ ] Update memory with new configurations

---

## **Quick Reference Commands**

### **Create Parameters Script**
```bash
cd /path/to/brain
php create_ringba_parameters.php
```

### **Test RingBA API**
```bash
curl -X POST https://api.ringba.com/v2/{{accountId}}/queryPathMaps \
  -H "Content-Type: application/json" \
  -d '{"incomingQueryStringName":"test_param","mapToTagName":"test_param","mapToTagType":"User"}'
```

### **Check Current Parameters**
1. Login to RingBA Dashboard
2. Settings > URL Parameters
3. Export list for documentation

---

## **Support Resources**

- **RingBA API Documentation**: https://developers.ringba.com/
- **Brain API Configurations**: `./API_CONFIGURATIONS.md`
- **Change History**: `./CHANGE_LOG.md`
- **Project Memory**: `./PROJECT_MEMORY.md`

---

*Last Updated: August 6, 2025*
*Integration Count: 1 (Allstate)*
*Total RingBA Parameters: ~95*
