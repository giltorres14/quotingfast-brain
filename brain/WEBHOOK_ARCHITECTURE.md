# Webhook Architecture Documentation
*Last Updated: January 2025*

## Overview
The Brain system receives leads through multiple webhook endpoints, each designed for specific lead types and sources. All webhooks process leads through a unified pipeline while maintaining type-specific handling.

---

## Active Webhook Endpoints

### 1. Main Webhook - `/webhook.php`
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
**Method**: POST
**Purpose**: Primary endpoint for LeadsQuotingFast (LQF) leads
**Content-Type**: `application/json`

**Expected Payload Structure**:
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
    "type": "auto",
    "drivers": [...],
    "vehicles": [...],
    "current_policy": {...}
  }
}
```

**Processing Flow**:
1. Receives POST request
2. Extracts contact and data fields
3. Generates unique lead ID (13-digit timestamp)
4. Stores in database
5. Pushes to Vici List 101
6. Returns success response

---

### 2. Auto Insurance Webhook - `/webhook/auto`
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook/auto`
**Method**: POST
**Purpose**: Dedicated endpoint for auto insurance leads
**Content-Type**: `application/json`

**Features**:
- Automatically sets `type: 'auto'` for all leads
- Validates vehicle and driver data
- Ensures proper auto insurance field mapping
- Routes to auto-specific campaigns if configured

**Expected Fields**:
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
    "source": "AUTO_CAMPAIGN",
    "drivers": [
      {
        "name": "Jane Smith",
        "age": 35,
        "license_status": "valid",
        "accidents": 0,
        "violations": 0
      }
    ],
    "vehicles": [
      {
        "year": 2020,
        "make": "Toyota",
        "model": "Camry",
        "vin": "1234567890",
        "primary_use": "commute"
      }
    ],
    "current_insurance": {
      "carrier": "State Farm",
      "expiration": "2025-03-01",
      "premium": 150
    }
  }
}
```

**Vici List Mapping**: List 101 (same as main webhook)
**Campaign ID**: Can be set to 'AUTO_LEADS' for tracking

---

### 3. Home Insurance Webhook - `/webhook/home`
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook/home`
**Method**: POST
**Purpose**: Dedicated endpoint for home insurance leads
**Content-Type**: `application/json`

**Features**:
- Automatically sets `type: 'home'` for all leads
- Validates property-specific data
- Handles home insurance field mapping
- Routes to home-specific campaigns if configured

**Expected Fields**:
```json
{
  "contact": {
    "name": "Bob Johnson",
    "phone": "5551112222",
    "email": "bob@example.com",
    "address": "789 Pine St",
    "city": "Cincinnati",
    "state": "OH",
    "zip_code": "45201"
  },
  "data": {
    "source": "HOME_CAMPAIGN",
    "property": {
      "type": "single_family",
      "year_built": 1995,
      "square_feet": 2500,
      "bedrooms": 4,
      "bathrooms": 2.5,
      "garage": "2_car",
      "roof_type": "shingle",
      "roof_age": 5
    },
    "current_insurance": {
      "carrier": "Allstate",
      "expiration": "2025-04-01",
      "premium": 200,
      "coverage_amount": 350000
    },
    "mortgage": {
      "has_mortgage": true,
      "lender": "Wells Fargo",
      "balance": 250000
    }
  }
}
```

**Vici List Mapping**: List 101 (same as main webhook)
**Campaign ID**: Can be set to 'HOME_LEADS' for tracking

---

## Webhook Status Monitoring

### Status Check Endpoint - `/webhook/status`
**URL**: `https://quotingfast-brain-ohio.onrender.com/webhook/status`
**Method**: GET
**Purpose**: Monitor webhook health and recent activity

**Response**:
```json
{
  "status": "active",
  "endpoints": {
    "main": {
      "url": "/webhook.php",
      "last_received": "2025-01-15 10:30:00",
      "total_today": 45,
      "last_status": "success"
    },
    "auto": {
      "url": "/webhook/auto",
      "last_received": "2025-01-15 10:25:00",
      "total_today": 23,
      "last_status": "success"
    },
    "home": {
      "url": "/webhook/home",
      "last_received": "2025-01-15 10:20:00",
      "total_today": 12,
      "last_status": "success"
    }
  },
  "vici_connection": "active",
  "database_status": "connected",
  "last_whitelist": "2025-01-15 10:00:00"
}
```

---

## Common Processing Pipeline

All webhooks share the same core processing logic:

```php
1. Receive POST data
2. Validate JSON structure
3. Extract contact information
4. Extract type-specific data
5. Generate unique lead_id (13-digit timestamp)
6. Store in database with full payload
7. Call ViciDialerService->pushLead()
8. Handle whitelist if needed
9. Log result
10. Return JSON response
```

---

## Field Mapping

### Database Fields (Common to All)
```
- id (auto-generated)
- external_lead_id (from source system)
- name, first_name, last_name
- phone (10 digits, no formatting)
- email
- address, city, state, zip_code
- source (LQF, AUTO_CAMPAIGN, HOME_CAMPAIGN, etc.)
- type (auto, home, life, health)
- campaign_id (optional)
- drivers (JSON - auto leads only)
- vehicles (JSON - auto leads only)
- property (JSON - home leads only)
- current_policy (JSON)
- payload (complete webhook data)
- created_at, updated_at
```

### Vici Field Mapping
```php
[
    'phone_number' => $lead->phone,
    'first_name' => $lead->first_name,
    'last_name' => $lead->last_name,
    'email' => $lead->email,
    'address1' => $lead->address,
    'city' => $lead->city,
    'state' => $lead->state,
    'postal_code' => $lead->zip_code,
    'vendor_lead_code' => "BRAIN_{$lead->id}",
    'source_id' => $lead->source,
    'list_id' => 101,
    'campaign_id' => $lead->type === 'auto' ? 'AUTO_LEADS' : 
                     ($lead->type === 'home' ? 'HOME_LEADS' : 'GENERAL'),
    'comments' => "Type: {$lead->type}"
]
```

---

## Testing Webhooks

### Using cURL

**Test Main Webhook**:
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "name": "Test User",
      "phone": "5551234567",
      "email": "test@example.com",
      "city": "Columbus",
      "state": "OH",
      "zip_code": "43215"
    },
    "data": {
      "source": "TEST",
      "type": "auto"
    }
  }'
```

**Test Auto Webhook**:
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/auto \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "name": "Auto Test",
      "phone": "5559876543",
      "email": "auto@test.com",
      "city": "Cleveland",
      "state": "OH",
      "zip_code": "44101"
    },
    "data": {
      "source": "AUTO_TEST",
      "drivers": [{"name": "Auto Test", "age": 30}],
      "vehicles": [{"year": 2020, "make": "Toyota", "model": "Camry"}]
    }
  }'
```

**Test Home Webhook**:
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/home \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "name": "Home Test",
      "phone": "5551112222",
      "email": "home@test.com",
      "city": "Cincinnati",
      "state": "OH",
      "zip_code": "45201"
    },
    "data": {
      "source": "HOME_TEST",
      "property": {
        "type": "single_family",
        "year_built": 2000,
        "square_feet": 2000
      }
    }
  }'
```

### Using PHP Test Scripts

```php
// test_auto_webhook.php
$leadData = [
    'contact' => [
        'name' => 'PHP Auto Test',
        'phone' => '5551234567',
        'email' => 'phpauto@test.com',
        'city' => 'Columbus',
        'state' => 'OH',
        'zip_code' => '43215'
    ],
    'data' => [
        'source' => 'PHP_TEST',
        'drivers' => [
            ['name' => 'PHP Auto Test', 'age' => 35]
        ],
        'vehicles' => [
            ['year' => 2021, 'make' => 'Honda', 'model' => 'Accord']
        ]
    ]
];

$ch = curl_init('https://quotingfast-brain-ohio.onrender.com/webhook/auto');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($leadData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: $httpCode\n";
echo "Response: $response\n";
```

---

## Error Handling

### Common Response Codes

**200 OK**: Lead successfully received and processed
```json
{
  "success": true,
  "message": "Lead received and sent to Vici",
  "lead_id": "1754577125000",
  "vici_status": "success"
}
```

**400 Bad Request**: Invalid JSON or missing required fields
```json
{
  "success": false,
  "error": "Invalid JSON payload"
}
```

**500 Internal Server Error**: Processing error
```json
{
  "success": false,
  "error": "Failed to process lead",
  "details": "Database connection failed"
}
```

### Retry Logic

If a webhook fails:
1. Log the full request and error
2. Store in failed_webhooks table
3. Attempt Vici whitelist refresh
4. Retry up to 3 times with exponential backoff
5. Alert if all retries fail

---

## Security Considerations

### Current Security
- HTTPS only (enforced by Render)
- No authentication required (trusted source IPs)
- Input validation and sanitization
- SQL injection protection via Laravel ORM

### Planned Enhancements
1. **API Key Authentication**: Add bearer token validation
2. **IP Whitelisting**: Restrict to known source IPs
3. **Rate Limiting**: Prevent abuse (100 requests/minute)
4. **Webhook Signatures**: Validate payload integrity
5. **Audit Logging**: Track all webhook activity

---

## Configuration

### Environment Variables
```env
# Webhook Settings
WEBHOOK_DEBUG=true
WEBHOOK_LOG_LEVEL=info
WEBHOOK_RETRY_ATTEMPTS=3
WEBHOOK_RETRY_DELAY=5

# Type-specific routing
AUTO_CAMPAIGN_ID=AUTO_LEADS
HOME_CAMPAIGN_ID=HOME_LEADS
DEFAULT_CAMPAIGN_ID=GENERAL

# Vici List Mapping
AUTO_LIST_ID=101
HOME_LIST_ID=101
DEFAULT_LIST_ID=101
```

### Route Configuration (routes/web.php)
```php
// Main webhook
Route::post('/webhook.php', function(Request $request) {
    // Process general leads
});

// Auto insurance webhook
Route::post('/webhook/auto', function(Request $request) {
    // Process auto leads with type='auto'
});

// Home insurance webhook  
Route::post('/webhook/home', function(Request $request) {
    // Process home leads with type='home'
});

// Status monitoring
Route::get('/webhook/status', function() {
    // Return webhook health status
});
```

---

## Migration Path

### Transitioning from Direct Vici to Brain

**Phase 1**: Parallel Operation
- Keep existing Vici webhooks active
- Add Brain webhooks as secondary
- Compare data quality

**Phase 2**: Primary Switch
- Make Brain primary webhook
- Keep Vici as backup
- Monitor for issues

**Phase 3**: Full Migration
- Disable direct Vici webhooks
- All leads through Brain
- Historical data migrated

### URL Updates Required

**LeadsQuotingFast**:
- Old: `https://vici-server.com/webhook`
- New: `https://quotingfast-brain-ohio.onrender.com/webhook.php`

**Auto Campaigns**:
- New: `https://quotingfast-brain-ohio.onrender.com/webhook/auto`

**Home Campaigns**:
- New: `https://quotingfast-brain-ohio.onrender.com/webhook/home`

---

## Monitoring & Alerts

### Health Checks
- Webhook status endpoint checked every 5 minutes
- Database connection verified
- Vici API connectivity tested
- Whitelist status monitored

### Alert Triggers
- No leads received in 1 hour during business hours
- Vici push failure rate > 10%
- Database connection lost
- Whitelist expired (no successful pushes in 30 min)

### Logging
- All webhooks logged with full payload
- Vici responses logged
- Errors logged with stack traces
- Daily summary reports generated

---

## Troubleshooting

### Lead Not Appearing in Vici
1. Check `/webhook/status` for recent activity
2. Verify lead in database: `SELECT * FROM leads ORDER BY created_at DESC LIMIT 10;`
3. Check Vici API connectivity: `/diagnostics`
4. Refresh whitelist if needed
5. Check Vici List 101 is active

### Webhook Returns 500 Error
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connection
3. Check payload structure matches expected format
4. Test with minimal payload
5. Check for required fields

### Duplicate Leads
1. Check for retry logic triggering multiple times
2. Verify source system not sending duplicates
3. Add deduplication logic based on phone number
4. Check Vici for existing lead before push

---

## Future Enhancements

### Planned Features
1. **Webhook Replay**: Ability to replay failed webhooks
2. **Bulk Import**: Accept batch lead uploads
3. **Real-time Dashboard**: Live webhook activity monitoring
4. **Smart Routing**: Route based on lead quality scores
5. **A/B Testing**: Split traffic between campaigns
6. **Lead Enrichment**: Add demographic data before Vici push
7. **Conversion Tracking**: Track lead outcomes back to source

### Integration Roadmap
- Q1 2025: Twilio SMS integration
- Q2 2025: Advanced buyer routing
- Q3 2025: AI-powered lead scoring
- Q4 2025: Full automation suite

---

*End of Webhook Architecture Documentation*


