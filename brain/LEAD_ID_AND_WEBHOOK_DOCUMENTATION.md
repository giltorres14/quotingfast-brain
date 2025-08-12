# 📋 LEAD ID SYSTEM & WEBHOOK DOCUMENTATION
*Complete documentation of the Lead ID numbering system and webhook endpoints*

---

## 🔢 LEAD ID NUMBERING SYSTEM

### Current Format: 13-Digit Timestamp ID
**Format**: `TTTTTTTTTTXXX`
- **First 10 digits**: Unix timestamp (seconds since epoch)
- **Last 3 digits**: Sequence number (000-999) for multiple leads in same second
- **Example**: `1754577125000`

### Implementation Details
```php
// Located in: app/Models/Lead.php
public static function generateExternalLeadId()
{
    // Get current Unix timestamp (10 digits)
    $timestamp = time();
    
    // Get count of leads created in the same second (for sequence)
    $countThisSecond = self::whereBetween('created_at', [$startOfSecond, $endOfSecond])
                          ->count();
    
    // Create sequence number (000-999)
    $sequence = str_pad($countThisSecond, 3, '0', STR_PAD_LEFT);
    
    // Combine timestamp + sequence for 13-digit ID
    $externalId = $timestamp . $sequence;
    
    return $externalId; // e.g., "1754577125000"
}
```

### Where It's Used
1. **Brain System**: Stored as `external_lead_id` in leads table
2. **Vici Dialer**: Sent as `vendor_lead_code = "BRAIN_1754577125000"`
3. **Agent Interface**: Displayed as Lead ID in agent views
4. **API Responses**: Returned in webhook responses

### Key Features
- **Unique**: Timestamp + sequence ensures no duplicates
- **Sortable**: Natural chronological ordering
- **Traceable**: Can determine exact creation time
- **Scalable**: Supports up to 1000 leads per second

---

## 🌐 WEBHOOK ENDPOINTS

### 1. AUTO INSURANCE WEBHOOK
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook/auto`
**Method**: POST
**Content-Type**: application/json

**Purpose**: Dedicated endpoint for auto insurance leads from LeadsQuotingFast

**Features**:
- Automatically sets `type = 'auto'`
- Validates vehicle and driver data
- Generates 13-digit timestamp ID
- Pushes to Vici List 101

**Expected Payload**:
```json
{
  "contact": {
    "name": "John Doe",
    "phone": "5551234567",
    "email": "john@example.com",
    "address": "123 Main St",
    "city": "Columbus",
    "state": "OH",
    "zip_code": "43215"
  },
  "data": {
    "source": "LQF",
    "drivers": [
      {
        "name": "John Doe",
        "age": 35,
        "license_status": "valid"
      }
    ],
    "vehicles": [
      {
        "year": 2020,
        "make": "Toyota",
        "model": "Camry"
      }
    ]
  }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Auto lead received and sent to Vici",
  "lead_id": "1754577125000",
  "type": "auto",
  "vici_status": "success"
}
```

### 2. HOME INSURANCE WEBHOOK
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook/home`
**Method**: POST
**Content-Type**: application/json

**Purpose**: Dedicated endpoint for home insurance leads from LeadsQuotingFast

**Features**:
- Automatically sets `type = 'home'`
- Validates property data
- Generates 13-digit timestamp ID
- Pushes to Vici List 101

**Expected Payload**:
```json
{
  "contact": {
    "name": "Jane Smith",
    "phone": "5559876543",
    "email": "jane@example.com",
    "address": "456 Oak Ave",
    "city": "Cleveland",
    "state": "OH",
    "zip_code": "44101"
  },
  "data": {
    "source": "LQF",
    "property": {
      "type": "single_family",
      "year_built": 1995,
      "square_feet": 2500,
      "bedrooms": 4,
      "bathrooms": 2.5
    },
    "current_insurance": {
      "carrier": "State Farm",
      "premium": 200
    }
  }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Home lead received and sent to Vici",
  "lead_id": "1754577125001",
  "type": "home",
  "vici_status": "success"
}
```

### 3. MAIN/FALLBACK WEBHOOK
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
**Method**: POST
**Content-Type**: application/json

**Purpose**: Main webhook for general leads or when type is included in payload

**Features**:
- Auto-detects lead type from payload
- Generates 13-digit timestamp ID
- Handles any insurance type
- Fallback for unspecified types

---

## 🔄 LEAD FLOW PROCESS

### Step-by-Step Flow
```
1. LeadsQuotingFast sends lead to appropriate webhook
   ↓
2. Brain receives and validates data
   ↓
3. Brain generates 13-digit timestamp ID (e.g., 1754577125000)
   ↓
4. Brain stores complete lead in database
   ↓
5. Brain pushes to Vici List 101 with vendor_lead_code = "BRAIN_1754577125000"
   ↓
6. Vici agents see lead with Brain ID
   ↓
7. Agents access lead details via Brain interface
```

### Database Storage
```sql
leads table:
- id: Internal auto-increment (not exposed)
- external_lead_id: 1754577125000 (13-digit timestamp)
- name: John Doe
- phone: 5551234567
- type: auto/home
- source: LQF
- vendor_lead_code: BRAIN_1754577125000 (in Vici)
```

---

## 📊 MIGRATION STRATEGY

### For New Leads (Starting Now)
1. **Update LQF Configuration**:
   - Auto leads → `https://quotingfast-brain-ohio.onrender.com/webhook/auto`
   - Home leads → `https://quotingfast-brain-ohio.onrender.com/webhook/home`

2. **Automatic Processing**:
   - Each lead gets unique 13-digit ID
   - Stored in Brain database
   - Pushed to Vici with Brain ID

### For Historical Leads (3 Months)
1. **Export from Vici** (List 101)
2. **Import to Brain** with generated IDs
3. **Update Vici** vendor_lead_code to "BRAIN_XXXXX"

---

## 🧪 TESTING COMMANDS

### Test Auto Webhook
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "name": "Test Auto",
      "phone": "5551234567",
      "email": "test@auto.com",
      "city": "Columbus",
      "state": "OH",
      "zip_code": "43215"
    },
    "data": {
      "source": "TEST",
      "drivers": [{"name": "Test Driver", "age": 30}],
      "vehicles": [{"year": 2020, "make": "Toyota", "model": "Camry"}]
    }
  }'
```

### Test Home Webhook
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/home \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "name": "Test Home",
      "phone": "5559876543",
      "email": "test@home.com",
      "city": "Cleveland",
      "state": "OH",
      "zip_code": "44101"
    },
    "data": {
      "source": "TEST",
      "property": {
        "type": "single_family",
        "year_built": 2000,
        "square_feet": 2000
      }
    }
  }'
```

### Verify Lead ID Generation
```php
// Check in database
SELECT external_lead_id, created_at FROM leads ORDER BY created_at DESC LIMIT 5;

// Results should show 13-digit IDs like:
// 1754577125000
// 1754577125001
// 1754577126000
```

---

## ✅ CURRENT STATUS

### What's Working
- ✅ 13-digit timestamp ID generation
- ✅ Auto webhook endpoint (`/webhook/auto`)
- ✅ Home webhook endpoint (`/webhook/home`)
- ✅ Main webhook endpoint (`/webhook.php`)
- ✅ Vici integration with Brain IDs
- ✅ 1,539 leads in database

### Ready for Production
1. Update LQF webhook URLs
2. Test with 10 leads
3. Import historical data
4. Go fully live

---

## 📝 IMPORTANT NOTES

### ID Format History
- **Original Plan**: 9-digit starting with 10000001
- **Current Implementation**: 13-digit timestamp (1754577125000)
- **Reason for Change**: Better scalability and natural chronological ordering

### Webhook Selection
- Use `/webhook/auto` for auto insurance leads
- Use `/webhook/home` for home insurance leads
- Use `/webhook.php` as fallback or for mixed types

### Vici Integration
- All leads pushed to List 101
- vendor_lead_code format: "BRAIN_[13-digit-id]"
- Agents see Brain ID in Vici interface

---

*Last Updated: January 2025*
*System Version: 1.3*
*Lead ID Format: 13-digit timestamp*

