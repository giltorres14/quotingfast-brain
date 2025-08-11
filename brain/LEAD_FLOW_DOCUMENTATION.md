# 📊 Complete Lead Flow & Duplicate Handling Integration
*Last Updated: August 11, 2025*

## Lead Flow Overview

```
LeadsQuotingFast (LQF) 
    ↓ [webhook]
The Brain (Central Hub)
    ↓ [API push]
ViciDial (Auto2/Autodial campaigns)
    ↓ [agent calls]
Agent Qualification (Top 12 Questions)
    ↓ [enrichment]
RingBA (Insured/Uninsured/Homeowner)
    ↓ [transfer]
Allstate (or other buyers)
```

## Detailed Lead Flow Stages

### Stage 1: Lead Ingestion (LQF → Brain)
**Entry Points:**
- `/webhook.php` - Main webhook endpoint
- CSV Import - Bulk historical leads
- API endpoints - Direct submissions

**Data Received:**
```json
{
  "contact": {
    "first_name": "John",
    "last_name": "Doe",
    "phone": "2482205565",
    "email": "john@example.com",
    "address": "123 Main St",
    "city": "Detroit",
    "state": "MI",
    "zip_code": "48201"
  },
  "data": {
    "drivers": [...],
    "vehicles": [...],
    "current_policy": {...}
  }
}
```

**Duplicate Detection at Ingestion:**
- **< 30 days**: Update existing lead
  - Status: `DUPLICATE_UPDATED`
  - Action: Refresh data, keep same `external_lead_id`
  - Vici: No new record, update existing if needed
  
- **30-90 days**: Re-engagement opportunity
  - Status: `RE_ENGAGEMENT`
  - Action: Create new lead with link to original
  - Vici: New record with priority flag
  
- **> 90 days**: Fresh lead
  - Status: `NEW_LEAD`
  - Action: Treat as completely new
  - Vici: New record in active list

### Stage 2: Lead Distribution (Brain → Vici)
**Process:**
1. Lead stored in Brain with `external_lead_id`
2. Push to Vici via API/Database
3. Vici assigns `lead_id` and stores our `external_lead_id` as `vendor_lead_code`

**Campaign Assignment:**
- Auto2: Primary auto insurance leads
- Autodial: Secondary/overflow leads

**Duplicate Handling:**
- Check Vici for existing `vendor_lead_code`
- If exists and < 30 days: Skip push
- If exists and 30-90 days: Push with `re_engagement` flag
- If exists and > 90 days: Push as new

### Stage 3: Agent Contact (Vici → Agent)
**Agent Workflow:**
1. Vici dials lead automatically
2. Agent sees lead in Vici interface
3. Clicks to open Brain iframe: `/agent/lead/{external_lead_id}`
4. Views lead details and qualification form

**Duplicate Prevention:**
- Vici's built-in duplicate checking by phone
- Brain's duplicate status shown in agent view
- Re-engagement leads marked with special icon

### Stage 4: Lead Qualification (Agent Interface)
**Top 12 Questions (Updated Order):**
1. Currently Insured? → Provider? → Duration?
2. How many cars?
3. Own or rent home?
4. DUI or SR22? → How long ago?
5. State
6. ZIP Code
7. Quote from Allstate in last 2 months?
8. Ready to speak with agent?

**Duplicate Context for Agents:**
- Shows if lead is re-engagement
- Displays days since last contact
- Shows previous qualification data if available

### Stage 5: Lead Enrichment (Brain → RingBA)
**Three Enrichment Paths:**
1. **Insured** - Currently has insurance
2. **Uninsured** - No current coverage
3. **Homeowner** - Property insurance opportunity

**Data Sent to RingBA:**
```javascript
{
  external_id: "1734367200000",
  first_name: "John",
  last_name: "Doe",
  phone: "2482205565",
  currently_insured: "true",
  valid_license: "true", // Hardcoded
  // ... additional fields
}
```

**Duplicate Handling in RingBA:**
- RingBA uses `external_id` for deduplication
- Prevents same lead being sent multiple times
- Re-engagement leads get new `external_id`

### Stage 6: Buyer Transfer (RingBA → Allstate)
**Transfer Process:**
1. RingBA sends to `/v2/calls/match` for bidding
2. If accepted, confirmation via `/v2/calls/post/[bid-id]`
3. Call transferred to Allstate agent

**Duplicate Prevention at Allstate:**
- Allstate checks their internal database
- Uses phone number as primary key
- May reject if recently quoted

## Duplicate Handling Decision Matrix

| Days Since | Brain Action | Vici Action | RingBA Action | Allstate Action |
|------------|-------------|-------------|---------------|-----------------|
| 0-30 | Update existing | Skip/Update | Block transfer | Likely reject |
| 31-90 | Create re-engagement | New with flag | Allow with note | May accept |
| 91+ | Create new lead | New record | Full transfer | Treat as new |

## Status Codes & Tracking

### Lead Status Progression:
1. `RECEIVED` - Lead arrived at Brain
2. `DUPLICATE_CHECK` - Checking for existing record
3. `QUEUED_VICI` - Ready for Vici push
4. `IN_VICI` - Active in dialer
5. `AGENT_CONTACT` - Being called
6. `QUALIFIED` - Agent completed questions
7. `ENRICHED` - Sent to RingBA
8. `TRANSFERRED` - Sent to buyer
9. `SOLD` - Successfully sold

### Duplicate-Specific Statuses:
- `DUPLICATE_UPDATED` - Existing lead refreshed
- `RE_ENGAGEMENT` - 30-90 day returning lead
- `DUPLICATE_BLOCKED` - Too recent to process

## Implementation in Code

### Webhook Duplicate Check (routes/web.php)
```php
// Line 1818-1892
$existingLead = Lead::where('phone', $phone)->first();
if ($existingLead) {
    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
    // Time-based logic applied
}
```

### CSV Import Duplicate Check (ImportLQFCsv.php)
```php
// Similar logic for bulk imports
// Includes batch processing optimizations
```

### Vici Update Logic (UpdateViciVendorCodes.php)
```php
// Updates vendor_lead_code
// Respects duplicate timeframes
```

## Monitoring & Reporting

### Key Metrics:
- Total leads received
- Duplicates detected (by timeframe)
- Re-engagement success rate
- Transfer success rate
- Revenue per lead status

### Log Entries:
- `🔍 Duplicate lead detected`
- `✅ Updated existing lead`
- `🔄 Created re-engagement lead`
- `🆕 Created new lead`
- `🚫 Duplicate blocked`

## Best Practices

1. **Always Check Phone Format**
   - Strip non-numeric characters
   - Ensure 10-digit format
   - Handle country codes

2. **Respect Time Windows**
   - 30-day cooling period for updates
   - 90-day maximum for re-engagement
   - Document all timeframe decisions

3. **Maintain Audit Trail**
   - Log all duplicate decisions
   - Track re-engagement success
   - Monitor rejection rates

4. **Agent Communication**
   - Clear indicators for re-engagement
   - Show previous interaction history
   - Provide context for better conversion

## Configuration Options

### Environment Variables:
```env
DUPLICATE_UPDATE_DAYS=30      # Days to update vs create new
DUPLICATE_REENGAGE_DAYS=90    # Days for re-engagement window
DUPLICATE_CHECK_ENABLED=true   # Enable/disable duplicate checking
```

### Future Enhancements:
- [ ] Configurable timeframes per campaign
- [ ] Duplicate merging interface
- [ ] Historical interaction viewer
- [ ] Predictive re-engagement scoring
- [ ] Multi-channel duplicate detection (SMS, email)

---

*This documentation integrates the complete lead flow with duplicate handling logic at each stage.*
