# VICIDIAL ACCESS DOCUMENTATION
**Last Updated: August 20, 2025, 10:00 AM EDT**

## 🔐 ACCESS METHODS TO VICIDIAL

### 1. SSH Direct Access (Primary)
```bash
# Server Details
Host: 37.27.138.222
SSH Port: 11845 (NOT standard 22!)
Username: root
Password: Monster@2213@!

# SSH Command
ssh -p 11845 root@37.27.138.222
```

### 2. Database Access
```bash
# MySQL Databases
Primary DB: Q6hdjl67GRigMofv (contains vicidial tables)
Secondary DB: asterisk (older, might not have current data)

# MySQL Credentials
Username: cron
Password: 1234

# Direct MySQL Command
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv

# Example Query
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e "SELECT * FROM vicidial_campaigns WHERE campaign_id = 'AUTODIAL'"
```

### 3. Via Brain Proxy (Render)
```bash
# Proxy Endpoint
URL: https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute
API Key: sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ

# cURL Example
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "X-API-Key: sk-KrtJqEUxCrUvYRQQQ8OKbMBmOa2OYnW5S5tPwPQJzIGBBgSZ" \
  -H "Content-Type: application/json" \
  -d '{"command": "echo test"}'

# IMPORTANT: Use "command" not "query" as the parameter
```

### 4. Laravel Database Connection
```php
// In config/database.php
'vicidial' => [
    'driver' => 'mysql',
    'host' => '37.27.138.222',
    'port' => '3306',
    'database' => 'Q6hdjl67GRigMofv',
    'username' => 'cron',
    'password' => '1234',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => false,
    'engine' => null,
],
```

## 📊 KEY VICIDIAL TABLES

### Campaign Management
- `vicidial_campaigns` - Campaign settings
- `vicidial_lists` - List configurations
- `vicidial_lead_filters` - Lead filtering rules

### Lead Data
- `vicidial_list` - All leads
- `vicidial_log` - Call logs
- `vicidial_dial_log` - Actual dial attempts

### Important Fields
- `called_since_last_reset` - Controls if lead is dialable ('N' = ready, 'Y' = not ready)
- `list_id` - Which list the lead belongs to
- `status` - Disposition (XFER, NA, B, etc.)
- `call_count` - Number of call attempts
- `vendor_lead_code` - Links to Brain external_lead_id

## 🚨 COMMON ISSUES & SOLUTIONS

### Issue 1: SSH Connection Fails
**Symptom:** "Connection refused" or timeout
**Solution:** 
1. Check firewall - Render IP must be whitelisted
2. Use correct port 11845 (not 22)
3. Current Render IP: 3.129.111.220

### Issue 2: Wrong Database
**Symptom:** Tables not found or old data
**Solution:** Use `Q6hdjl67GRigMofv` not `asterisk`

### Issue 3: Proxy Returns "Test connection"
**Symptom:** Proxy responds but doesn't execute command
**Cause:** Using wrong parameter name
**Solution:** Use `"command"` not `"query"` in JSON payload

### Issue 4: Permission Denied Errors
**Symptom:** "Could not create directory '/var/www/.ssh'"
**Cause:** SSH trying to save host key
**Solution:** Add `-o StrictHostKeyChecking=no` to SSH commands

## 📋 CAMPAIGN SETTINGS FOR LEAD FLOW

### Current AUTODIAL Campaign (Before Changes)
- **Hopper Level:** 50 ✅
- **List Order Mix:** Likely RANDOM or UP COUNT
- **Lead Filter:** NONE or basic
- **Dial Method:** RATIO or ADAPT_AVERAGE
- **Drop Call Seconds:** 5-10

### Target Settings (After 8 PM Update)
- **Hopper Level:** 50 (no change)
- **List Order Mix:** DOWN COUNT ⚠️
- **Lead Filter:** called_since_last_reset = 'N' ⚠️
- **Next Agent Call:** oldest_call_finish
- **Drop Call Seconds:** 5

## 🔧 USEFUL COMMANDS

### Check Campaign Settings
```bash
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e "
SELECT campaign_id, dial_method, hopper_level, list_order_mix, 
       next_agent_call, lead_filter_id 
FROM vicidial_campaigns 
WHERE campaign_id = 'AUTODIAL'"
```

### Check List Configuration
```bash
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e "
SELECT list_id, list_name, active, campaign_id, reset_time 
FROM vicidial_lists 
WHERE campaign_id = 'AUTODIAL' 
ORDER BY list_id"
```

### Check Lead Counts by List
```bash
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e "
SELECT list_id, 
       COUNT(*) as total,
       SUM(CASE WHEN called_since_last_reset = 'N' THEN 1 ELSE 0 END) as ready
FROM vicidial_list 
WHERE list_id IN (101,102,103,104,106,107,108,109,111,150,151,152,153)
GROUP BY list_id"
```

### Mark Leads Ready to Call
```bash
mysql -h localhost -u cron -p'1234' Q6hdjl67GRigMofv -e "
UPDATE vicidial_list 
SET called_since_last_reset = 'N'
WHERE list_id = 101 
AND status NOT IN ('XFER','XFERA','DNC','DNCL','DNQ')
AND call_count < 5"
```

## 🚀 DEPLOYMENT CHECKLIST

### Before Making Campaign Changes:
- [ ] Verify it's after 8 PM EDT
- [ ] Check current hopper level
- [ ] Backup current settings
- [ ] Ensure cron jobs are running
- [ ] Have rollback plan ready

### After Making Changes:
- [ ] Monitor hopper fill rate
- [ ] Check agent call flow
- [ ] Verify lead movements
- [ ] Watch for errors in logs
- [ ] Test with small batch first

## 📞 SUPPORT CONTACTS

- **ViciDial Server:** 37.27.138.222:11845
- **Brain Application:** https://quotingfast-brain-ohio.onrender.com
- **Database:** Q6hdjl67GRigMofv (primary), asterisk (legacy)

---

**Note:** Always test changes in off-hours first. The lead flow system is complex and affects live calling operations.

