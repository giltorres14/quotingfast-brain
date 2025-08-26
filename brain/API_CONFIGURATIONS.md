# 🔗 API CONFIGURATIONS REGISTRY
## Centralized API Management - Updated: 2025-08-24 23:30 EST

---

## 🎯 ALLSTATE LEAD MARKETPLACE API

### **Testing Environment**
```env
ALLSTATE_TEST_URL=https://int.allstateleadmarketplace.com/v2/
ALLSTATE_TEST_KEY=testvendor:
ALLSTATE_TEST_AUTH=Basic dGVzdHZlbmRvcjo=
ALLSTATE_TEST_ENDPOINT=/ping
```

### **Production Environment**
```env
ALLSTATE_PROD_URL=https://api.allstateleadmarketplace.com/v2/
ALLSTATE_PROD_KEY=b91446ade9d37650f93e305cbaf8c2c9
ALLSTATE_PROD_AUTH=Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
ALLSTATE_VENDOR_NAME=quoting-fast
```

### **Required Headers**
```php
'Authorization' => 'Basic dGVzdHZlbmRvcjo=', // Testing
'Content-Type' => 'application/json',
'Accept' => 'application/json'
```

### **Field Requirements**
```php
// Driver Object (CRITICAL - exact field names required)
'id' => 1,                    // NOT 'driver_number'
'relation' => 'self',         // primary driver
'gender' => 'female',         // lowercase, NOT 'F'
'marital_status' => 'single', // exact enum
'edu_level' => 'BDG',        // NOT 'COLLEGE'
'occupation' => 'MARKETING',  // approved enum
'dob' => '1985-03-15',       // Y-m-d format, NOT 'date_of_birth'
'requires_sr22' => false,     // boolean, NOT 'sr22'
'tickets_and_accidents' => false, // boolean, NOT integer count

// Vehicle Object
'id' => 1,                    // NOT 'vehicle_number'  
'primary_use' => 'commutework', // NOT 'commute'
'garage_type' => 'garage',    // enum value

// Lead Object
'tcpa' => true,              // boolean, NOT 'tcpa_compliant'
'residence_status' => 'home' // NOT 'own'
```

---

## 🎯 RINGBA CALL TRACKING API

### **Configuration**
```env
RINGBA_URL=https://api.ringba.com/v2/
RINGBA_API_KEY=[TO_BE_CONFIGURED]
RINGBA_CAMPAIGN_ID=[TO_BE_CONFIGURED]
```

### **Endpoints**
- **Send Lead**: `POST /leads`
- **Track Call**: `POST /calls/{callId}/events`
- **Get Analytics**: `GET /campaigns/{campaignId}/analytics`

### **Integration Points**
- Agent qualification "Enrich" buttons
- Post-qualification lead enrichment
- Call outcome tracking

### **Enrich Endpoints (CRITICAL - Parameter Names)**
```php
// Insured Enrich: https://display.ringba.com/enrich/2674154334576444838
// Uninsured Enrich: https://display.ringba.com/enrich/2676487329580844084
// Homeowner Enrich: https://display.ringba.com/enrich/2717035800150673197

// CRITICAL: All enrich endpoints use 'callerid' parameter for phone number
// NOT 'primary_phone' - this was causing "Bad Request" errors
$params = [
    'callerid' => $phone,  // Required for all enrich endpoints
    'currently_insured' => 'true',  // For insured endpoint
    'current_insurance_company' => $provider,
    // ... other parameters
];
```

### **Confirmation Request → Allstate (Production)**
```
URL: https://api.allstateleadmarketplace.com/v2/calls/post/[bid-id]
Method: POST
Content-Type: application/json
Headers:
  - Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
  - Accept: application/json
Body:
  { "home_phone": "[tag:InboundNumber:AreaCode][tag:InboundNumber:Prefix][tag:InboundNumber:Suffix]" }

Parsers:
  - Call Acceptance (JavaScript): returns true when `matched` is true
  - Dynamic Number/SIP (JavaScript): prefers SIP, otherwise normalizes inbound number to E.164
  - Bid ID (JavaScript): extracts `bid_id|call_id|id|result.*|response.*|_meta.request_id`

Recommended Timeouts/Toggles:
  - Confirmation Request: ON
  - Confirmation Request Required: OFF (so priority buyers can receive first)
  - Timeout: 4–5s
```

### **Call Flow Pattern**
- Put priority buyers first (Buyer A, Buyer B, Ring Tree) → Allstate as fallback.
- Optionally move Allstate into a separate flow with Required=ON if you want gating at that step only.

---

## 🎯 VICI DIALER SYSTEM

### **Webhook Configuration**
```env
VICI_WEBHOOK_URL=/webhook/vici
VICI_LIST_ID=101
```

### **Non-Agent API Credentials & Connection**
```env
# Server and endpoint
VICI_SERVER=philli.callix.ai
VICI_API_ENDPOINT=/vicidial/non_agent_api.php

# Credentials (stored in Render env)
VICI_API_USER=UploadAPI
VICI_API_PASS=RENDER_SECRET   # actual value saved in Render: ZL8aY2MuQM (Admin, Level 9)
```

### **Operational Notes**
- Non-Agent API returns plain text. Success/failure is parsed from body; common failure: `ERROR: Login incorrect, please try again: |||BAD|` (bad credentials).
- Firewall auth script available at `https://{VICI_SERVER}:26793/92RG8UJYTW.php` (used automatically for whitelisting).
- Protocol fallback implemented: tries HTTPS first, then HTTP, and caches the working protocol for 24h.
 - **IP Whitelist is required**: All DB and API calls must originate from one of: 3.134.238.10, 3.129.111.220, 52.15.118.168.

### **Testing Endpoints**
- Push test lead: `GET /test/vici/{leadId?}`
- Update existing Vici lead: `GET /test/vici-update/{leadId?}`
- DB connectivity probe: `GET /test/vici-db`
- Login/version probe: `GET /test/vici-login`
- Server egress IP: `GET /server-egress-ip` (for Vici whitelist)
   - Current observed IP: 52.15.118.168

### **Database Access (Production - 11M rows!) - CONNECTED BUT ISSUE**
- SSH Host: `37.27.138.222` Port: `11845`
- SSH User: `root` Pass: `Monster@2213@!`
- MySQL Database: `YLtZX713f1r6uauf`
- MySQL Port: `20540` (CORRECTED from 23964)
- MySQL User: `wS3Vtb7rJgAGePi5` (CORRECTED from qUSDV7hoj5cM6OFh)
- MySQL Pass: `hkj7uAlV9wp9zOMr` (CORRECTED from dsHVMx9QqHtx5zNt)
- Table: `vicidial_list` (11 MILLION rows - always use LIMIT!)
- Purpose: Read leads, write `vendor_lead_code = external_lead_id`
- Status: Connected but only retrieving 826 of 21,220+ leads (query/buffer issue)
 - **Note**: Connection only works when originating IP is whitelisted (see list above)

### **Integration Status**
- **Current**: 🧪 API path OK previously; DB access pending
- **Normal Flow**: LeadsQuotingFast → Brain → Vici → Agent Qualification
- **Restore After**: Allstate API testing complete / DB creds obtained

---

## 🎯 LEADSQUOTINGFAST WEBHOOK

### **Webhook Endpoints**
```env
WEBHOOK_MAIN=/webhook.php
WEBHOOK_ALT=/webhook/leadsquotingfast
```

### **Payload Structure**
```json
{
  "contact": {
    "first_name": "Tambara",
    "last_name": "Farrell",
    "email": "tambara.farrell@example.com",
    "phone": "555-123-4567"
  },
  "data": {
    "drivers": [
      {
        "first_name": "Tambara",
        "last_name": "Farrell", 
        "date_of_birth": "1985-03-15",
        "gender": "F",
        "education": "Bachelors"
      }
    ],
    "vehicles": [
      {
        "year": 2018,
        "make": "Toyota",
        "model": "Camry",
        "primary_use": "commuting"
      }
    ],
    "current_policy": {
      "currently_insured": "Yes",
      "insurance_company": "State Farm"
    }
  }
}
```

---

## 🎯 DATABASE CONFIGURATIONS

### **Local Development**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### **Production (Render)**
```env
DB_CONNECTION=pgsql
DB_HOST=dpg-d277kvk9c44c7388opg0-a
DB_PORT=5432
DB_DATABASE=brain_production
DB_USERNAME=brain_user
DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ
```

---

## 🎯 CRM INTEGRATIONS

### **Allstate Lead Manager**
```php
'allstate_lead_manager' => [
    'posting_url' => 'http://www.leadmanagementlab.com/api/accounts/{account}/leads/',
    'provider_id' => '[UNIQUE_PROVIDER_ID]',
    'lead_type' => 'Auto'
]
```

### **Ricochet360**
```php  
'ricochet360' => [
    'api_url' => 'https://api.ricochet360.com/v1/',
    'api_key' => '[API_KEY]',
    'list_id' => '[LIST_ID]'
]
```

---

## 🔧 SMART MAPPING CONFIGURATIONS

### **Occupation Mapping**
```php
$occupationMap = [
    'MANAGER' => 'ADMINMGMT',
    'MARKETING MANAGER' => 'MARKETING', 
    'ENGINEER' => 'ENGINEEROTHER',
    'DOCTOR' => 'PHYSICIAN',
    // ... (full mapping in AllstateCallTransferService.php)
];
$defaultOccupation = 'SUPERVISOR';
```

### **Education Mapping**
```php
$educationMap = [
    'High School' => 'HS',
    'Some College' => 'SCL', 
    'College' => 'BDG',
    'Bachelors' => 'BDG',
    'Masters' => 'MDG',
    'Doctorate' => 'DOC'
];
$defaultEducation = 'BDG';
```

### **Vehicle Usage Mapping**
```php
$usageMap = [
    'commute' => 'commutework',
    'commuting' => 'commutework',
    'personal' => 'pleasure',
    'work related' => 'business'
];
$defaultUsage = 'pleasure';
```

---

## 🚨 CRITICAL REMINDERS

### **Field Type Requirements**
- **Booleans**: Must be `true`/`false`, NOT `"true"`/`"false"`
- **Integers**: Must be numbers, NOT strings
- **Dates**: Must be `Y-m-d` format: `"1985-03-15"`
- **Enums**: Must match exact approved values (case-sensitive)

### **Testing vs Production**
- **NEVER** test on production endpoints until live
- **ALWAYS** use test environment for development
- **VERIFY** authentication tokens before switching environments

### **Data Validation**
- **Allstate API** is extremely strict on field names and types
- **Missing required fields** will cause 400 errors
- **Invalid enum values** will be rejected
- **Boolean/string mismatches** will fail validation

---

*This configuration registry is maintained to prevent API integration issues and ensure consistent setup across environments.*


