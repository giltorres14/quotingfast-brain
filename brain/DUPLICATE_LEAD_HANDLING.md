# 🔍 DUPLICATE LEAD HANDLING DOCUMENTATION
*Complete guide to how Brain handles duplicate leads from LeadsQuotingFast*

---

## 📋 OVERVIEW

The Brain system has **sophisticated duplicate detection and handling** based on:
- **Phone number matching** (primary identifier)
- **Time-based logic** (age of existing lead)
- **Three different handling strategies** based on lead age

---

## 🎯 DUPLICATE DETECTION LOGIC

### Primary Detection Method
```php
// Check for existing lead by phone number
$phone = preg_replace('/[^0-9]/', '', $contact['phone']); // Strip to 10 digits
$existingLead = Lead::where('phone', $phone)->first();
```

### Detection Triggers
- **Same phone number** = Potential duplicate
- Checked on EVERY incoming lead from LQF
- Applied to both `/webhook/auto` and `/webhook/home` endpoints

---

## ⏱️ TIME-BASED HANDLING STRATEGIES

The system uses **THREE different strategies** based on how old the existing lead is:

### 1️⃣ **0-10 Days Old: UPDATE EXISTING LEAD**
```php
if ($daysSinceCreated <= 10) {
    // Update the existing lead with new information
    $leadData['status'] = 'DUPLICATE_UPDATED';
    $leadData['meta'] = json_encode([
        'duplicate_action' => 'updated',
        'original_created_at' => $existingLead->created_at,
        'days_since_original' => $daysSinceCreated,
        'lead_flow_stage' => $existingLead->status
    ]);
    
    $existingLead->update($leadData);
    $lead = $existingLead; // Use the same lead record
}
```

**Rationale**: Lead is still fresh, likely the same person following up

**What Happens**:
- ✅ Original lead record is UPDATED
- ✅ Preserves original lead ID
- ✅ Updates contact info with latest data
- ✅ Adds metadata about duplicate action
- ✅ Maintains lead history

### 2️⃣ **11-90 Days Old: CREATE RE-ENGAGEMENT LEAD**
```php
elseif ($daysSinceCreated <= 90) {
    // Create new lead marked as re-engagement
    $leadData['status'] = 'RE_ENGAGEMENT';
    $leadData['meta'] = json_encode([
        're_engagement' => true,
        'original_lead_id' => $existingLead->id,
        'original_created_at' => $existingLead->created_at,
        'days_since_original' => $daysSinceCreated,
        'original_flow_stage' => $existingLead->status,
        'original_qualified' => $existingLead->qualified
    ]);
    
    $lead = Lead::create($leadData); // Create NEW lead
}
```

**Rationale**: Lead has gone cold but is re-engaging, treat as new opportunity

**What Happens**:
- ✅ NEW lead record created
- ✅ Gets new Brain ID (13-digit timestamp)
- ✅ Marked as RE_ENGAGEMENT status
- ✅ Links to original lead via metadata
- ✅ Preserves original lead unchanged

### 3️⃣ **91+ Days Old: CREATE AS NEW LEAD**
```php
else {
    // Over 90 days - treat as completely new lead
    $leadData['status'] = 'NEW_AFTER_90_DAYS';
    $leadData['meta'] = json_encode([
        'previous_lead_exists' => true,
        'previous_lead_id' => $existingLead->id,
        'days_since_previous' => $daysSinceCreated
    ]);
    
    $lead = Lead::create($leadData); // Create NEW lead
}
```

**Rationale**: After 90 days, treat as completely new customer journey

**What Happens**:
- ✅ NEW lead record created
- ✅ Gets new Brain ID
- ✅ Minimal reference to old lead
- ✅ Fresh start for sales process

---

## 📊 DUPLICATE HANDLING FLOW CHART

```
Incoming Lead from LQF
        ↓
Check Phone Number
        ↓
Existing Lead Found?
    ├─ NO → Create New Lead → Assign 13-digit ID → Push to Vici
    │
    └─ YES → Check Age of Existing Lead
              ├─ ≤10 days → UPDATE existing lead
              │              └─ Keep same ID
              │              └─ Update info
              │              └─ Mark as DUPLICATE_UPDATED
              │
              ├─ 11-90 days → CREATE re-engagement lead
              │                └─ New 13-digit ID
              │                └─ Mark as RE_ENGAGEMENT
              │                └─ Link to original
              │
              └─ >90 days → CREATE new lead
                            └─ New 13-digit ID
                            └─ Mark as NEW_AFTER_90_DAYS
                            └─ Minimal link to old
```

---

## 🏷️ STATUS CODES & METADATA

### Lead Status Values
- `DUPLICATE_UPDATED` - Existing lead updated (≤10 days)
- `RE_ENGAGEMENT` - Re-engaged after 11-90 days
- `NEW_AFTER_90_DAYS` - New lead after 90+ days
- `NEW` - Completely new lead (no duplicate found)

### Metadata Stored
```json
{
  "duplicate_action": "updated|re_engagement|new",
  "original_lead_id": 1234,
  "original_created_at": "2024-01-01T00:00:00Z",
  "days_since_original": 15,
  "original_flow_stage": "qualified",
  "original_qualified": true,
  "re_engagement": true,
  "previous_lead_exists": true
}
```

---

## 🔧 IMPLEMENTATION LOCATIONS

### Auto Insurance Webhook
**File**: `routes/web.php`
**Lines**: ~1819-1894
**Endpoint**: `/webhook/auto`

### Home Insurance Webhook  
**File**: `routes/web.php`
**Lines**: ~5779-5844
**Endpoint**: `/webhook/home`

### Historical Import Script
**File**: `import_vici_historical_leads.php`
**Lines**: ~132-145
- Checks for existing `external_lead_id`
- Checks for existing phone number
- Updates or skips accordingly

---

## 📈 BENEFITS OF THIS APPROACH

### 1. **No Lost Leads**
- Every lead is captured
- Duplicates are handled intelligently
- Nothing falls through cracks

### 2. **Better Conversion Tracking**
- Can track re-engagement success
- Separate metrics for fresh vs returning
- Clear attribution

### 3. **Compliance Friendly**
- Recent leads (≤10 days) updated = respects "do not duplicate"
- Older leads = new consent assumed
- Clear audit trail

### 4. **Agent Efficiency**
- Agents see lead history
- Know if it's re-engagement
- Can reference previous interactions

### 5. **Accurate Reporting**
- True new lead count
- Re-engagement metrics
- Conversion by lead age

---

## 🛠️ CUSTOMIZATION OPTIONS

### Adjust Time Windows
```php
// In routes/web.php, modify these values:
if ($daysSinceCreated <= 10) {        // Change 10 to different days
} elseif ($daysSinceCreated <= 90) {  // Change 90 to different days
```

### Add Additional Duplicate Checks
```php
// Could also check by:
$existingLead = Lead::where('phone', $phone)
                    ->orWhere('email', $email)
                    ->first();
```

### Custom Status Codes
```php
// Add your own status codes:
$leadData['status'] = 'YOUR_CUSTOM_STATUS';
```

---

## 📊 MONITORING & REPORTING

### Track Duplicate Rates
```sql
-- Count duplicates by type
SELECT 
    status,
    COUNT(*) as count
FROM leads 
WHERE status IN ('DUPLICATE_UPDATED', 'RE_ENGAGEMENT', 'NEW_AFTER_90_DAYS')
GROUP BY status;
```

### Find All Re-engagement Leads
```sql
-- Get re-engagement leads
SELECT * FROM leads 
WHERE status = 'RE_ENGAGEMENT'
ORDER BY created_at DESC;
```

### Duplicate Detection Report
```sql
-- Find leads with same phone
SELECT 
    phone,
    COUNT(*) as occurrences,
    GROUP_CONCAT(id) as lead_ids,
    MIN(created_at) as first_seen,
    MAX(created_at) as last_seen
FROM leads
GROUP BY phone
HAVING COUNT(*) > 1
ORDER BY occurrences DESC;
```

---

## ⚠️ IMPORTANT CONSIDERATIONS

### 1. **Phone Number Formatting**
- Always strips to 10 digits
- Removes all non-numeric characters
- Example: (555) 123-4567 → 5551234567

### 2. **Vici Implications**
- Updated leads keep same `vendor_lead_code`
- New leads get new `vendor_lead_code`
- Re-engagement leads appear as new in Vici

### 3. **Historical Import**
- Import script also checks for duplicates
- Won't create duplicate phone numbers
- Updates `external_lead_id` if missing

### 4. **Testing Duplicate Handling**
```bash
# Send same lead twice to test
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{"contact":{"phone":"5551234567","name":"Test Dup"}}'

# Wait, then send again
sleep 5
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{"contact":{"phone":"5551234567","name":"Test Dup Updated"}}'
```

---

## ✅ SUMMARY

**The Brain system has robust duplicate handling that:**
- ✅ Detects duplicates by phone number
- ✅ Updates recent leads (≤10 days)
- ✅ Creates re-engagement leads (11-90 days)
- ✅ Treats old leads as new (>90 days)
- ✅ Maintains complete audit trail
- ✅ Prevents true duplicates while maximizing opportunities

**This ensures:**
- No wasted leads
- Better conversion tracking
- Compliance with best practices
- Optimal agent workflow

---

*Last Updated: January 2025*
*Duplicate Logic Version: 2.0*


