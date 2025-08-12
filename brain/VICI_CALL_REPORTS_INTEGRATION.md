# 📞 VICI CALL REPORTS INTEGRATION

## ✅ COMPLETE SOLUTION FOR VICI CALL DATA

### Overview
We now have a complete system to capture Vici call reports both **historically** and **going forward** in real-time. All call data is stored in the Brain database for reporting, analytics, and lead tracking.

---

## 🔄 **HISTORICAL CALL REPORTS IMPORT**

### Import Past Call Data
Use the Artisan command to import historical call reports from Vici:

```bash
# Import last 30 days (default)
php artisan vici:import-call-reports

# Import specific date range
php artisan vici:import-call-reports --start-date=2024-01-01 --end-date=2024-12-31

# Import for specific campaigns
php artisan vici:import-call-reports --campaign=AUTODIAL --campaign=Auto2

# Dry run to preview
php artisan vici:import-call-reports --dry-run

# Update existing records
php artisan vici:import-call-reports --update-existing
```

### What Gets Imported:
- **Call Attempts**: Number of times each lead was called
- **Dispositions**: Agent-set outcomes (SALE, NI, DNC, etc.)
- **Talk Time**: Duration of connected calls
- **Agent Info**: Which agent handled the call
- **Call History**: Complete timeline of all attempts
- **Campaign Data**: Which campaign/list the call was from

---

## 📡 **REAL-TIME CALL WEBHOOKS**

### Available Webhook Endpoints

#### 1. **Call Status Updates**
```
POST https://quotingfast-brain-ohio.onrender.com/webhook/vici/call-status
```
Receives real-time updates about call status changes.

**Payload Example:**
```json
{
    "lead_id": "123456",
    "vendor_lead_code": "1734567890123",
    "phone_number": "5551234567",
    "status": "INCALL",
    "agent_id": "agent001",
    "campaign_id": "AUTODIAL",
    "talk_time": 120,
    "uniqueid": "1734567890.12345"
}
```

#### 2. **Agent Dispositions**
```
POST https://quotingfast-brain-ohio.onrender.com/webhook/vici/disposition
```
Triggered when agent sets a disposition in Vici.

**Payload Example:**
```json
{
    "lead_id": "123456",
    "vendor_lead_code": "1734567890123",
    "status": "SALE",
    "user": "agent001",
    "campaign_id": "AUTODIAL",
    "comments": "Customer qualified, ready for transfer"
}
```

#### 3. **Real-Time Events**
```
POST https://quotingfast-brain-ohio.onrender.com/webhook/vici/realtime
```
For real-time call events (start, connect, end, transfer).

**Payload Example:**
```json
{
    "event": "call_connect",
    "lead_id": "123456",
    "vendor_lead_code": "1734567890123",
    "agent_id": "agent001",
    "uniqueid": "1734567890.12345"
}
```

---

## 🔧 **VICI CONFIGURATION**

### Setting Up Webhooks in Vici

#### Option 1: DNC (Dial Next Call) Integration
In Vici Admin → Campaign Settings:
```
Dispo Call URL: https://quotingfast-brain-ohio.onrender.com/webhook/vici/disposition
Start Call URL: https://quotingfast-brain-ohio.onrender.com/webhook/vici/call-status
```

#### Option 2: Real-Time Monitoring
In Vici Admin → System Settings:
```
Real-time URL: https://quotingfast-brain-ohio.onrender.com/webhook/vici/realtime
Real-time Trigger: ALL
```

#### Option 3: List Settings
In Vici Admin → Lists → [Your List]:
```
Web Form: https://quotingfast-brain-ohio.onrender.com/agent/lead/--A--vendor_lead_code--B--
Web Form Target: _blank
```

---

## 📊 **DATA STRUCTURE**

### ViciCallMetrics Table
Stores all call-related data:

| Field | Description | Example |
|-------|-------------|---------|
| lead_id | Brain Lead ID | 1234 |
| vici_lead_id | Vici's internal ID | 567890 |
| campaign_id | Campaign name | AUTODIAL |
| agent_id | Agent username | agent001 |
| call_status | Current status | INCALL |
| disposition | Final outcome | SALE |
| call_attempts | Total attempts | 3 |
| talk_time | Seconds talked | 240 |
| first_call_time | First attempt | 2024-12-01 10:00:00 |
| last_call_time | Most recent | 2024-12-03 14:30:00 |
| call_history | JSON array of all attempts | [{...}] |

---

## 🎯 **DISPOSITION MAPPING**

Vici dispositions are automatically mapped to Brain lead statuses:

| Vici Disposition | Brain Status | Description |
|-----------------|--------------|-------------|
| SALE | SOLD | Lead converted to sale |
| XFER | TRANSFERRED | Transferred to buyer |
| DNC | DO_NOT_CALL | Added to DNC list |
| NI | NOT_INTERESTED | Not interested |
| CALLBK | CALLBACK_SCHEDULED | Callback requested |
| A | VOICEMAIL | Answering machine |
| B | BUSY | Line busy |
| N | NO_ANSWER | No answer |
| DROP | DROPPED | Call dropped |

---

## 📈 **REPORTING & ANALYTICS**

### Available Metrics:
- **Connection Rate**: % of calls that connected
- **Transfer Rate**: % of connected calls transferred
- **Agent Performance**: Calls per agent, talk time, conversions
- **Campaign Performance**: By campaign/list metrics
- **Time-based Analysis**: Best times to call

### Query Examples:
```php
// Get all call metrics for a lead
$metrics = ViciCallMetrics::where('lead_id', $leadId)->first();

// Get agent performance
$agentStats = ViciCallMetrics::where('agent_id', 'agent001')
    ->whereDate('created_at', today())
    ->get();

// Get campaign conversion rate
$conversions = ViciCallMetrics::where('campaign_id', 'AUTODIAL')
    ->where('disposition', 'SALE')
    ->count();
```

---

## 🔄 **AUTOMATIC SYNC SCHEDULE**

Set up a cron job to automatically import call reports:

```bash
# Add to crontab
*/30 * * * * cd /path/to/brain && php artisan vici:import-call-reports --start-date="-1 hour"
```

This runs every 30 minutes and imports calls from the last hour.

---

## 🚀 **IMPLEMENTATION STEPS**

### Step 1: Run Database Migration
```bash
php artisan migrate
```
This creates the `vici_call_metrics` table if it doesn't exist.

### Step 2: Import Historical Data
```bash
# Import last 3 months
php artisan vici:import-call-reports --start-date=2024-09-01 --dry-run
php artisan vici:import-call-reports --start-date=2024-09-01
```

### Step 3: Configure Vici Webhooks
Add webhook URLs to Vici campaign/system settings.

### Step 4: Test Webhooks
```bash
# Test call status webhook
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook/vici/call-status \
  -H "Content-Type: application/json" \
  -d '{
    "lead_id": "test123",
    "vendor_lead_code": "1734567890123",
    "status": "INCALL",
    "agent_id": "test_agent"
  }'
```

### Step 5: Monitor
Check logs for webhook activity:
```bash
tail -f storage/logs/laravel.log | grep "Vici"
```

---

## 🔍 **TROUBLESHOOTING**

### Issue: Leads Not Matching
**Solution**: The system matches leads by:
1. vendor_lead_code (Brain's external_lead_id)
2. Phone number (10-digit match)

Ensure vendor_lead_code is properly set in Vici.

### Issue: Webhooks Not Received
**Solution**: 
1. Check Vici server can reach Brain URL
2. Verify no firewall blocking
3. Check webhook URLs in Vici config
4. Review Brain logs for errors

### Issue: Historical Import Failing
**Solution**:
1. Verify API credentials in .env
2. Check Vici API is accessible
3. Try smaller date ranges
4. Use --dry-run to test first

---

## 📊 **SUCCESS METRICS**

After implementation, you'll have:
- ✅ Complete call history for every lead
- ✅ Real-time call status updates
- ✅ Agent performance tracking
- ✅ Campaign effectiveness metrics
- ✅ Automatic disposition updates
- ✅ Transfer tracking
- ✅ Historical data preservation

---

## 🎉 **BENEFITS**

1. **Complete Visibility**: See every interaction with every lead
2. **Performance Tracking**: Monitor agent and campaign performance
3. **Lead Journey**: Track lead progression through the sales funnel
4. **Compliance**: Maintain call records for compliance
5. **Analytics**: Data-driven decisions on campaigns and agents
6. **Integration**: Seamless flow between Vici and Brain

---

## 📝 **NOTES**

- Call metrics are linked to leads via the `lead_id` foreign key
- All webhook endpoints bypass CSRF for external access
- Call history is stored as JSON for flexibility
- Metrics are calculated automatically (connection rate, transfer rate)
- The system handles multiple call attempts per lead

This integration ensures NO CALL DATA IS LOST and provides complete visibility into your calling operations!


