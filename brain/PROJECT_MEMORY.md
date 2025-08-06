# 🧠 BRAIN PROJECT MEMORY
## Living Documentation System - Auto-Updated: {{ date('Y-m-d H:i:s') }}

---

## 🎯 CURRENT PROJECT STATUS

### **ACTIVE INTEGRATION: Allstate Lead Marketplace API**
- **Status**: 🧪 TESTING MODE - Working on field validation issues
- **Priority**: HIGH - Get API working perfectly before RingBA integration
- **Next Step**: Fix remaining validation errors and perfect data mapping

---

## 🔗 API INTEGRATIONS REGISTRY

### **1. ALLSTATE LEAD MARKETPLACE API**
**TESTING ENVIRONMENT:**
- URL: `https://int.allstateleadmarketplace.com/v2/`
- API Key: `testvendor:` (Base64: `dGVzdHZlbmRvcjo=`)
- Authorization: `Basic dGVzdHZlbmRvcjo=`
- Test Endpoint: `/ping` (POST with empty JSON {})
- Status: ✅ ACTIVE - Connection working, fixing validation errors

**PRODUCTION ENVIRONMENT:**
- URL: `https://api.allstateleadmarketplace.com/v2/`
- API Key: `b91446ade9d37650f93e305cbaf8c2c9`
- Base64 Encoded: `YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6`
- Authorization: `Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6`
- Vendor Name: `quoting-fast`
- Status: 🔒 READY - Don't test until live

**CRITICAL FIELD MAPPINGS:**
```json
{
  "tcpa": boolean (not "tcpa_compliant"),
  "dob": "1985-03-15" (not "date_of_birth"),
  "residence_status": "home" (not "own"),
  "edu_level": "BDG|HS|SCL|ADG|MDG|DOC" (not "COLLEGE"),
  "occupation": "MARKETING|SALES|ADMINMGMT|..." (see occupation mapping),
  "gender": "female|male" (not "F|M"),
  "primary_use": "commutework" (not "commute"),
  "requires_sr22": boolean (not "sr22"),
  "id": integer (not "driver_number|vehicle_number")
}
```

### **2. RINGBA CALL TRACKING**
- URL: `https://api.ringba.com/v2/`
- Purpose: Lead enrichment and call routing
- Status: 📋 PLANNED - After Allstate API is perfect
- Integration Point: Agent qualification "Enrich" buttons

### **3. VICI DIALER SYSTEM**
- Webhook: `/webhook/vici`
- Purpose: Agent outbound calling and qualification
- Status: 🧪 TEMPORARILY BYPASSED for Allstate testing
- **RESTORE AFTER TESTING**: See memory ID 5307562

---

## 🔄 CURRENT LEAD FLOW

### **TESTING MODE (TEMPORARY):**
```
LeadsQuotingFast → Brain → Auto-Qualification → Allstate API
```

### **PRODUCTION FLOW (TO RESTORE):**
```
LeadsQuotingFast → Brain → Vici → Agent Qualification → RingBA Enrichment → Allstate
```

---

## 🗂️ KEY FILES & SERVICES

### **Core Services:**
- `app/Services/AllstateCallTransferService.php` - Main Allstate integration
- `app/Services/AllstateTestingService.php` - Testing orchestration
- `app/Services/AutoQualificationService.php` - Auto-fills qualification data
- `app/Services/RingBAService.php` - RingBA integration (pending)
- `app/Services/CRMIntegrationService.php` - Multi-CRM support

### **Database Models:**
- `app/Models/AllstateTestLog.php` - Testing results logging
- `app/Models/Lead.php` - Main lead data
- `app/Models/LeadOutcome.php` - Buyer outcome tracking

### **Admin Dashboards:**
- `/admin/allstate-testing` - Monitor API test results
- `/admin/buyer-management` - Buyer account management
- `/admin/simple-dashboard` - Main admin hub

### **Routes File:**
- `routes/web.php` - All routing (webhook handlers, admin routes, API endpoints)

---

## 🧪 TESTING SETUP

### **Test Lead Data:**
- **Tambara Farrell** - Real auto insurance lead from LeadsQuotingFast
- **Tony Clark** - Real homeowner lead
- Located in production PostgreSQL, replicated in local SQLite

### **Testing Process:**
1. Lead comes via `/webhook.php` (LeadsQuotingFast)
2. Auto-bypasses Vici (temporary)
3. Auto-qualification service fills "Top 12 Questions"
4. AllstateCallTransferService processes and sends to API
5. Results logged in `allstate_test_logs` table
6. Monitor via `/admin/allstate-testing` dashboard

---

## 🔧 SMART DATA MAPPING LOGIC

### **Occupation Mapping:**
- Default: `SUPERVISOR` if no match
- Logic: Maps common terms to Allstate-approved codes
- Examples: "Marketing Manager" → "MARKETING", "Engineer" → "ENGINEEROTHER"

### **Education Mapping:**
- Default: `BDG` (Bachelor's Degree)
- Logic: Takes highest education level among all drivers
- Examples: "College" → "BDG", "Masters" → "MDG"

### **Vehicle Usage Mapping:**
- Default: `pleasure`
- Logic: "commuting|commute" → "commutework"
- Smart mapping for various usage terms

---

## 🚨 CRITICAL ISSUES TO REMEMBER

### **NEVER FORGET:**
1. **Test Environment Only**: Don't send to production until live
2. **Restore Vici Integration**: After Allstate API is perfect
3. **Database Differences**: Production = PostgreSQL, Local = SQLite
4. **Field Name Precision**: Allstate API is very strict on field names/types
5. **Boolean vs String**: All booleans must be true/false, not "true"/"false"

### **RECENT FIXES:**
- Disabled DataNormalizationService (was interfering)
- Fixed field name mappings (dob, tcpa, id vs driver_number)
- Implemented comprehensive data extraction logic
- Added smart mapping for occupation/education/usage

---

## 📋 AGENT QUALIFICATION QUESTIONS (TOP 12)

1. Date of Birth
2. Gender  
3. Marital Status
4. Currently Insured (Yes/No)
5. Current Insurance Company
6. Policy Expiration Date
7. Current Monthly Premium
8. Desired Coverage Type (REMOVED - defaults to STANDARD)
9. Deductible Preference
10. Credit Score Range
11. Home Ownership Status
12. Years Licensed
13. Accidents/Violations (Last 5 years)
14. DUI Conviction
15. SR22 Required
16. Best Time to Call

---

## 🎯 IMMEDIATE NEXT STEPS

1. **Fix Allstate API validation errors** (if any remain)
2. **Perfect data mapping and field formatting**
3. **Test thoroughly with real lead data**
4. **Set up RingBA enrichment integration**
5. **Restore Vici integration with enhanced flow**

---

## 💾 BACKUP CONFIGURATIONS

### **Vici Integration (TO RESTORE):**
```php
// ORIGINAL VICI INTEGRATION (TEMPORARILY DISABLED FOR TESTING):
if ($externalLeadId) {
    try {
        $viciResult = sendToViciList101($leadData, $externalLeadId);
        Log::info('Lead sent to Vici list 101', ['external_lead_id' => $externalLeadId]);
    } catch (Exception $viciError) {
        Log::error('Failed to send lead to Vici', ['error' => $viciError->getMessage()]);
    }
}
```

### **Database Connections:**
- **Local**: SQLite (`database/database.sqlite`)
- **Production**: PostgreSQL on Render (`brain-postgres`)

---

*This document is automatically updated with each significant change to maintain project continuity and prevent knowledge loss.*


