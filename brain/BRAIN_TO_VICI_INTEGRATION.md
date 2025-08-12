# 🔄 Brain to ViciDial Integration Documentation
*Last Updated: December 2024*

## ✅ Integration Status: COMPLETE

The Brain system now automatically pushes all incoming leads to ViciDial List 101 for immediate calling.

## 📊 Lead Flow Architecture

```
LeadsQuotingFast (LQF)
        ↓ [webhook]
    The Brain
        ↓ [automatic push]
ViciDial List 101
        ↓ [automated movement]
    Lists 102-111
        ↓ [agent calls]
    Lead Qualified
```

## 🚀 Implementation Details

### 1. Webhook Integration (`routes/web.php`)
- **Endpoint**: `/webhook.php`
- **Process**:
  1. Receives lead from LQF
  2. Performs duplicate detection (< 30 days, 30-90 days, > 90 days)
  3. Stores lead in Brain database
  4. Automatically pushes to ViciDial
  5. Updates lead with Vici ID and status

### 2. ViciDialerService (`app/Services/ViciDialerService.php`)
- **Methods**:
  - `pushLead()`: Main entry point
  - `pushLeadToDatabase()`: Direct DB insert (production server only)
  - `pushLeadViaAPI()`: Non-Agent API method (works from anywhere)
  - Mock mode for local testing

- **Configuration**:
  ```php
  // Target list is hardcoded to prevent errors
  private int $targetListId = 101;
  
  // Campaigns mapped automatically
  'AUTODIAL' => Primary campaign
  'AUTO2' => Training campaign
  ```

### 3. Database Schema Updates
- **Migration**: `2024_12_15_add_vici_fields_to_leads_table.php`
- **New Fields**:
  - `vici_lead_id`: ViciDial's internal lead ID
  - `vici_pushed_at`: Timestamp of push
  - `vici_list_id`: Current list (101-111)
  - `vici_campaign`: Campaign assignment
  - `tcpajoin_date`: TCPA compliance date

### 4. Lead Movement Automation
- **SQL Playbook**: `VICIDIAL_CALL_FLOW_SQL_PLAYBOOK.md`
- **Lists**:
  - 101: Immediate (new leads)
  - 102: Day 1-3 Aggressive (4-5x/day)
  - 103: Voicemail Phase 1
  - 104: Phase 1 (3x/day)
  - 105: Voicemail Phase 2
  - 106: Phase 2 (2x/day)
  - 107: Cool Down (7 days rest)
  - 108: Phase 3 (1x/day)
  - 110: Archive
  - 111: Training (AUTO2 campaign)

## 🔧 Configuration

### Environment Variables
```env
# ViciDial Configuration
VICI_WEB_SERVER=philli.callix.ai
VICI_MYSQL_HOST=37.27.138.222
VICI_MYSQL_DB=asterisk
VICI_MYSQL_USER=Superman
VICI_MYSQL_PASS=8ZDWGAAQRD
VICI_DEFAULT_CAMPAIGN=AUTODIAL
VICI_TEST_MODE=false  # Set to true for mock mode
```

### Non-Agent API Credentials
```php
'user' => 'apiuser',
'pass' => 'apipass123',
```

## 🧪 Testing

### Local Testing (Mock Mode)
```bash
# Run test script
php test_brain_to_vici_push.php
```

### Production Testing
1. Send test lead to webhook
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify in ViciDial admin panel
4. Check List 101 for new lead
5. Confirm `vendor_lead_code` = `BRAIN_[lead_id]`

## 📝 Duplicate Handling

### Time-Based Strategy
- **< 30 days**: Update existing lead
- **30-90 days**: Create re-engagement lead
- **> 90 days**: Treat as new lead

### Implementation
```php
// In webhook endpoint
if ($existingLead) {
    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
    
    if ($daysSinceCreated < 30) {
        // Update existing
        $existingLead->update($leadData);
    } elseif ($daysSinceCreated <= 90) {
        // Re-engagement
        $leadData['status'] = 'RE_ENGAGEMENT';
        $lead = Lead::create($leadData);
    } else {
        // New lead
        $lead = Lead::create($leadData);
    }
}
```

## 🚨 TCPA Compliance

### 30-Day Hard Stop
- Field: `tcpajoin_date`
- SQL Check: `CURDATE() < DATE_ADD(tcpajoin_date, INTERVAL 30 DAY)`
- Automatic archiving at 30 days
- Hourly cron job for compliance check

## 📊 Monitoring

### Key Metrics
```sql
-- Leads by list
SELECT list_id, COUNT(*) as count 
FROM vicidial_list 
WHERE list_id BETWEEN 101 AND 111 
GROUP BY list_id;

-- Today's new leads
SELECT COUNT(*) 
FROM vicidial_list 
WHERE list_id = 101 
AND entry_date >= CURDATE();

-- TCPA at risk
SELECT COUNT(*) 
FROM vicidial_list vl
JOIN lead_tcpajoin jt ON vl.lead_id = jt.lead_id
WHERE DATEDIFF(DATE_ADD(jt.tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 3;
```

### Log Monitoring
```bash
# Watch Brain logs
tail -f storage/logs/laravel.log | grep -E "Vici|Lead"

# Check for errors
grep "ERROR\|FAILED" storage/logs/laravel.log | tail -50
```

## 🔄 Cron Jobs Required

Add to ViciDial server crontab:
```bash
# Every 15 minutes - Fast moves
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_101_102.sql
*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_103_104_lvm.sql

# Daily at midnight - Workday moves
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_102_103_workdays.sql
1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_104_105_phase1.sql

# Hourly - TCPA compliance
0 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/tcpa_compliance_check.sql
```

## 🐛 Troubleshooting

### Lead Not Appearing in Vici
1. Check Brain logs for push errors
2. Verify API credentials
3. Check if Brain server IP is whitelisted
4. Test Non-Agent API directly

### Duplicate Lead Issues
1. Check `vendor_lead_code` in Vici
2. Verify duplicate detection logic
3. Review `lead_moves` audit table

### Connection Issues
- Local development uses mock mode
- Production server uses direct DB or API
- Whitelist Brain server IP: https://philli.callix.ai/vicidial/admin_ip_access.php

## 📚 Related Documentation
- [Lead Flow Documentation](LEAD_FLOW_DOCUMENTATION.md)
- [ViciDial SQL Playbook](VICIDIAL_CALL_FLOW_SQL_PLAYBOOK.md)
- [Duplicate Lead Strategy](DUPLICATE_LEAD_STRATEGY.md)
- [Lead Migration Documentation](LEAD_MIGRATION_DOCUMENTATION.md)

## ✅ Deployment Checklist

- [x] Update webhook to push to Vici
- [x] Create ViciDialerService
- [x] Add database migration for Vici fields
- [x] Configure Non-Agent API
- [x] Implement mock mode for testing
- [x] Create test script
- [x] Document SQL queries for lead movement
- [ ] Deploy to production
- [ ] Run database migration
- [ ] Test with live lead
- [ ] Set up cron jobs on Vici server
- [ ] Monitor first day of operation

---

*Integration complete and ready for production deployment*


