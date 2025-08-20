# CRITICAL WEBHOOK ENDPOINTS DOCUMENTATION
**LAST INCIDENT: January 12, 2025 - All webhooks failed due to missing tenant_id**

## 🚨 CRITICAL ENDPOINTS - DO NOT BREAK THESE!

### Primary Production Endpoints (ACTIVELY RECEIVING LEADS)

1. **`/api-webhook`** (PRIMARY - LeadsQuotingFast uses this)
   - URL: `https://quotingfast-brain-ohio.onrender.com/api-webhook`
   - Method: POST
   - Status: **ACTIVE - RECEIVING LIVE LEADS**
   - Purpose: Main webhook for LeadsQuotingFast lead intake

2. **`/webhook.php`** (SECONDARY)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
   - Method: POST
   - Status: **ACTIVE - BACKUP ENDPOINT**
   - Purpose: Backup webhook for LeadsQuotingFast

3. **`/webhook/auto`** (AUTO INSURANCE)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook/auto`
   - Method: POST
   - Status: **ACTIVE**
   - Purpose: Dedicated endpoint for auto insurance leads

4. **`/webhook/home`** (HOME INSURANCE)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook/home`
   - Method: POST
   - Status: **ACTIVE**
   - Purpose: Dedicated endpoint for home insurance leads

## ⚠️ WHAT BROKE AND WHY (January 12, 2025)

### The Problem
- All webhook endpoints returned 500 errors
- Error: `null value in column "tenant_id" of relation "leads" violates not-null constraint`
- **Root Cause**: Database schema was modified to add a required `tenant_id` column, but webhook endpoints weren't updated

### The Fix Applied
1. Added `tenant_id` to Lead model's `$fillable` array
2. Updated ALL webhook endpoints to include `'tenant_id' => 5` in lead data
3. Files modified:
   - `app/Models/Lead.php` - Added tenant_id to fillable
   - `routes/web.php` - Added tenant_id to all Lead::create() calls

## 📋 REQUIRED FIELDS FOR LEAD CREATION

These fields MUST be included when creating a lead or the database will reject it:

```php
$leadData = [
    // REQUIRED by database constraints
    'tenant_id' => 5,  // CRITICAL - Added Jan 12, 2025
    'phone' => '...',  // Required
    'source' => 'leadsquotingfast',
    'type' => 'auto|home',  // Lead type
    
    // Should include
    'name' => '...',
    'first_name' => '...',
    'last_name' => '...',
    'email' => '...',
    'state' => '...',
    'received_at' => now(),
    'joined_at' => now(),
    'external_lead_id' => Lead::generateExternalLeadId(),
];
```

## 🔍 TESTING WEBHOOKS

### Quick Test Commands

**Test Auto Endpoint:**
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "first_name": "Test",
      "last_name": "Auto",
      "phone": "5555551234",
      "email": "test@example.com",
      "city": "Test City",
      "state": "CA"
    },
    "data": {
      "drivers": [{"first_name": "Test", "last_name": "Auto"}],
      "vehicles": [{"year": "2020", "make": "Toyota", "model": "Camry"}]
    }
  }'
```

**Test Home Endpoint:**
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/home \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "first_name": "Test",
      "last_name": "Home",
      "phone": "5555556789",
      "email": "test@example.com",
      "city": "Test City",
      "state": "CA"
    },
    "data": {
      "properties": [{"address": "123 Test St", "type": "single_family"}]
    }
  }'
```

## 🛡️ PREVENTION CHECKLIST

**BEFORE making ANY database changes:**

1. **Check if field is required (NOT NULL)**
   - If yes, ALL webhook endpoints must be updated
   - Search for: `Lead::create(`, `Lead::firstOrCreate(`, `Lead::updateOrCreate(`

2. **Update these files:**
   - [ ] `app/Models/Lead.php` - Add to $fillable array
   - [ ] `routes/web.php` - Update ALL webhook endpoints
   - [ ] Any command files that create leads

3. **Test IMMEDIATELY after deployment:**
   - [ ] Test `/api-webhook` endpoint
   - [ ] Test `/webhook/auto` endpoint  
   - [ ] Test `/webhook/home` endpoint
   - [ ] Check Laravel logs for any errors

4. **Monitor for 5 minutes:**
   - [ ] Verify leads are being created in database
   - [ ] Check for any 500 errors in logs

## 📍 WHERE WEBHOOKS ARE DEFINED

All webhook endpoints are in: **`routes/web.php`**

Key sections:
- Line ~206: `/api-webhook` (PRIMARY)
- Line ~1741: `/webhook.php` (SECONDARY)
- Line ~5869: `/webhook/home`
- Line ~6071: `/webhook/auto`

## 🔄 DEPLOYMENT NOTES

- **Platform**: Render.com
- **Region**: Ohio
- **Deployment Time**: ~3-5 minutes after git push
- **Cache Issues**: Sometimes requires CACHE_BUST increment in Dockerfile.render

## 💡 LESSONS LEARNED

1. **Database changes affect webhooks immediately** - Even if code hasn't deployed yet
2. **Required fields break everything** - NOT NULL constraints cause immediate failures
3. **Test webhooks after EVERY database migration**
4. **Document all required fields** - Keep this list updated
5. **Have fallback endpoints** - api-webhook saved us when webhook.php had issues

## 🚨 EMERGENCY CONTACTS

If webhooks are down:
1. Check this document first
2. Test with curl commands above
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check database for recent schema changes
5. Verify all required fields are being sent

---

**REMEMBER**: Webhooks are the lifeblood of lead flow. A broken webhook means lost revenue. Always test after changes!

Last Updated: January 12, 2025
Incident Resolution Time: ~30 minutes




**LAST INCIDENT: January 12, 2025 - All webhooks failed due to missing tenant_id**

## 🚨 CRITICAL ENDPOINTS - DO NOT BREAK THESE!

### Primary Production Endpoints (ACTIVELY RECEIVING LEADS)

1. **`/api-webhook`** (PRIMARY - LeadsQuotingFast uses this)
   - URL: `https://quotingfast-brain-ohio.onrender.com/api-webhook`
   - Method: POST
   - Status: **ACTIVE - RECEIVING LIVE LEADS**
   - Purpose: Main webhook for LeadsQuotingFast lead intake

2. **`/webhook.php`** (SECONDARY)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
   - Method: POST
   - Status: **ACTIVE - BACKUP ENDPOINT**
   - Purpose: Backup webhook for LeadsQuotingFast

3. **`/webhook/auto`** (AUTO INSURANCE)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook/auto`
   - Method: POST
   - Status: **ACTIVE**
   - Purpose: Dedicated endpoint for auto insurance leads

4. **`/webhook/home`** (HOME INSURANCE)
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook/home`
   - Method: POST
   - Status: **ACTIVE**
   - Purpose: Dedicated endpoint for home insurance leads

## ⚠️ WHAT BROKE AND WHY (January 12, 2025)

### The Problem
- All webhook endpoints returned 500 errors
- Error: `null value in column "tenant_id" of relation "leads" violates not-null constraint`
- **Root Cause**: Database schema was modified to add a required `tenant_id` column, but webhook endpoints weren't updated

### The Fix Applied
1. Added `tenant_id` to Lead model's `$fillable` array
2. Updated ALL webhook endpoints to include `'tenant_id' => 5` in lead data
3. Files modified:
   - `app/Models/Lead.php` - Added tenant_id to fillable
   - `routes/web.php` - Added tenant_id to all Lead::create() calls

## 📋 REQUIRED FIELDS FOR LEAD CREATION

These fields MUST be included when creating a lead or the database will reject it:

```php
$leadData = [
    // REQUIRED by database constraints
    'tenant_id' => 5,  // CRITICAL - Added Jan 12, 2025
    'phone' => '...',  // Required
    'source' => 'leadsquotingfast',
    'type' => 'auto|home',  // Lead type
    
    // Should include
    'name' => '...',
    'first_name' => '...',
    'last_name' => '...',
    'email' => '...',
    'state' => '...',
    'received_at' => now(),
    'joined_at' => now(),
    'external_lead_id' => Lead::generateExternalLeadId(),
];
```

## 🔍 TESTING WEBHOOKS

### Quick Test Commands

**Test Auto Endpoint:**
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "first_name": "Test",
      "last_name": "Auto",
      "phone": "5555551234",
      "email": "test@example.com",
      "city": "Test City",
      "state": "CA"
    },
    "data": {
      "drivers": [{"first_name": "Test", "last_name": "Auto"}],
      "vehicles": [{"year": "2020", "make": "Toyota", "model": "Camry"}]
    }
  }'
```

**Test Home Endpoint:**
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/home \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "first_name": "Test",
      "last_name": "Home",
      "phone": "5555556789",
      "email": "test@example.com",
      "city": "Test City",
      "state": "CA"
    },
    "data": {
      "properties": [{"address": "123 Test St", "type": "single_family"}]
    }
  }'
```

## 🛡️ PREVENTION CHECKLIST

**BEFORE making ANY database changes:**

1. **Check if field is required (NOT NULL)**
   - If yes, ALL webhook endpoints must be updated
   - Search for: `Lead::create(`, `Lead::firstOrCreate(`, `Lead::updateOrCreate(`

2. **Update these files:**
   - [ ] `app/Models/Lead.php` - Add to $fillable array
   - [ ] `routes/web.php` - Update ALL webhook endpoints
   - [ ] Any command files that create leads

3. **Test IMMEDIATELY after deployment:**
   - [ ] Test `/api-webhook` endpoint
   - [ ] Test `/webhook/auto` endpoint  
   - [ ] Test `/webhook/home` endpoint
   - [ ] Check Laravel logs for any errors

4. **Monitor for 5 minutes:**
   - [ ] Verify leads are being created in database
   - [ ] Check for any 500 errors in logs

## 📍 WHERE WEBHOOKS ARE DEFINED

All webhook endpoints are in: **`routes/web.php`**

Key sections:
- Line ~206: `/api-webhook` (PRIMARY)
- Line ~1741: `/webhook.php` (SECONDARY)
- Line ~5869: `/webhook/home`
- Line ~6071: `/webhook/auto`

## 🔄 DEPLOYMENT NOTES

- **Platform**: Render.com
- **Region**: Ohio
- **Deployment Time**: ~3-5 minutes after git push
- **Cache Issues**: Sometimes requires CACHE_BUST increment in Dockerfile.render

## 💡 LESSONS LEARNED

1. **Database changes affect webhooks immediately** - Even if code hasn't deployed yet
2. **Required fields break everything** - NOT NULL constraints cause immediate failures
3. **Test webhooks after EVERY database migration**
4. **Document all required fields** - Keep this list updated
5. **Have fallback endpoints** - api-webhook saved us when webhook.php had issues

## 🚨 EMERGENCY CONTACTS

If webhooks are down:
1. Check this document first
2. Test with curl commands above
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check database for recent schema changes
5. Verify all required fields are being sent

---

**REMEMBER**: Webhooks are the lifeblood of lead flow. A broken webhook means lost revenue. Always test after changes!

Last Updated: January 12, 2025
Incident Resolution Time: ~30 minutes










