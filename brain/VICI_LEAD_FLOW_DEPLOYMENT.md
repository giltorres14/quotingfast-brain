# 🚀 Vici Lead Flow System - Deployment Complete

## ✅ What We Accomplished

### 1. **Created 10 SQL Movement Scripts**
Each script handles specific lead transitions based on your playbook:

| Script | Purpose | Schedule |
|--------|---------|----------|
| `move_101_102.sql` | After first call attempt | Every 15 min |
| `move_101_103_callbk.sql` | CALLBK direct to voicemail | Every 15 min |
| `move_102_103_workdays.sql` | After 3 workdays aggressive | Daily 12:01 AM |
| `move_103_104_lvm.sql` | After voicemail left | Every 15 min |
| `move_104_105_phase1.sql` | After 5 days from entry | Daily 12:01 AM |
| `move_105_106_lvm.sql` | After second voicemail | Every 15 min |
| `move_106_107_phase2.sql` | After 10 days to cool down | Daily 12:01 AM |
| `move_107_108_cooldown.sql` | After 7 days rest | Daily 12:01 AM |
| `move_108_110_archive.sql` | After 30 days or TCPA expiry | Daily 12:01 AM |
| `tcpa_compliance_check.sql` | Emergency TCPA compliance | Hourly |

### 2. **Key Features Implemented**
- ✅ **Automatic lead progression** based on call outcomes
- ✅ **TCPA compliance** - Automatic archiving at 30 days
- ✅ **Voicemail detection** - Different paths for LVM status
- ✅ **Workday scheduling** - Uses calendar table when available
- ✅ **Duplicate prevention** - Won't move same lead twice per day
- ✅ **Audit trail** - All moves logged to `lead_moves` table
- ✅ **Brain ID tracking** - Preserves 13-digit IDs throughout

### 3. **Files Deployed to Vici**
All scripts successfully deployed to `/opt/vici_scripts/` on Vici server:
```
✅ move_101_102.sql
✅ move_101_103_callbk.sql
✅ move_102_103_workdays.sql
✅ move_103_104_lvm.sql
✅ move_104_105_phase1.sql
✅ move_105_106_lvm.sql
✅ move_106_107_phase2.sql
✅ move_107_108_cooldown.sql
✅ move_108_110_archive.sql
✅ tcpa_compliance_check.sql
```

### 4. **Monitoring Dashboard**
Created dashboard at: https://quotingfast-brain-ohio.onrender.com/admin/vici-lead-flow
- Real-time lead distribution view
- Recent movement history
- Auto-refreshes every 30 seconds

## 📋 NEXT STEPS - MANUAL SETUP REQUIRED

### 1. **Add Cron Jobs to Vici Server**

SSH to Vici server and add the cron entries:
```bash
ssh Superman@66.175.219.105 -p 22
crontab -e
```

Then add these entries (from `vici_scripts/crontab_entries.txt`):

```cron
# Every 15 minutes - Fast movements
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_callbk.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104_lvm.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106_lvm.sql 2>&1 | logger -t vici_flow

# Daily at 12:01 AM - Workday-based movements
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103_workdays.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105_phase1.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107_phase2.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108_cooldown.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_110_archive.sql 2>&1 | logger -t vici_flow

# Hourly - TCPA Compliance Check
0 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_compliance_check.sql 2>&1 | logger -t vici_tcpa
```

### 2. **Test the System**

Test a single script manually:
```bash
mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql
```

Monitor the lead flow:
```bash
mysql -u root Q6hdjl67GRigMofv -e 'SELECT * FROM lead_flow_dashboard'
```

### 3. **Configure Brain to Use List 101**

The Brain system has been updated to always push new leads to List 101:
- ViciDialerService now sets `$this->targetListId = 101`
- All new leads will enter the automated flow

## 🎯 How It Works

1. **New leads** arrive from Brain → Go to **List 101**
2. **After first call:**
   - CALLBK status → **List 103** (Voicemail phase)
   - Other statuses → **List 102** (Aggressive calling)
3. **Progressive movement** through lists based on:
   - Time in system
   - Call outcomes
   - Voicemail responses
4. **Automatic archiving** at 30 days or TCPA expiry → **List 110**

## 📊 Benefits

- **Fully automated** - No manual lead movement needed
- **TCPA compliant** - Automatic expiry handling
- **Optimized calling** - Right frequency at right time
- **Scalable** - Handles unlimited lead volume
- **Auditable** - Complete movement history

## 🔧 Troubleshooting

If leads aren't moving:
1. Check cron logs: `grep vici_flow /var/log/syslog`
2. Verify tables exist: `lead_moves`, `excluded_statuses`, `calendar`
3. Check for SQL errors in scripts
4. Ensure lists 101-110 exist in Vici

## 📈 Success Metrics

Monitor these KPIs:
- Lead velocity through lists
- Contact rates by list
- Transfer success by phase
- TCPA compliance rate
- Archive volume trends

---

**Status:** ✅ DEPLOYED - Awaiting cron configuration
**Date:** January 15, 2025
**Next Action:** Add cron jobs to Vici server



## ✅ What We Accomplished

### 1. **Created 10 SQL Movement Scripts**
Each script handles specific lead transitions based on your playbook:

| Script | Purpose | Schedule |
|--------|---------|----------|
| `move_101_102.sql` | After first call attempt | Every 15 min |
| `move_101_103_callbk.sql` | CALLBK direct to voicemail | Every 15 min |
| `move_102_103_workdays.sql` | After 3 workdays aggressive | Daily 12:01 AM |
| `move_103_104_lvm.sql` | After voicemail left | Every 15 min |
| `move_104_105_phase1.sql` | After 5 days from entry | Daily 12:01 AM |
| `move_105_106_lvm.sql` | After second voicemail | Every 15 min |
| `move_106_107_phase2.sql` | After 10 days to cool down | Daily 12:01 AM |
| `move_107_108_cooldown.sql` | After 7 days rest | Daily 12:01 AM |
| `move_108_110_archive.sql` | After 30 days or TCPA expiry | Daily 12:01 AM |
| `tcpa_compliance_check.sql` | Emergency TCPA compliance | Hourly |

### 2. **Key Features Implemented**
- ✅ **Automatic lead progression** based on call outcomes
- ✅ **TCPA compliance** - Automatic archiving at 30 days
- ✅ **Voicemail detection** - Different paths for LVM status
- ✅ **Workday scheduling** - Uses calendar table when available
- ✅ **Duplicate prevention** - Won't move same lead twice per day
- ✅ **Audit trail** - All moves logged to `lead_moves` table
- ✅ **Brain ID tracking** - Preserves 13-digit IDs throughout

### 3. **Files Deployed to Vici**
All scripts successfully deployed to `/opt/vici_scripts/` on Vici server:
```
✅ move_101_102.sql
✅ move_101_103_callbk.sql
✅ move_102_103_workdays.sql
✅ move_103_104_lvm.sql
✅ move_104_105_phase1.sql
✅ move_105_106_lvm.sql
✅ move_106_107_phase2.sql
✅ move_107_108_cooldown.sql
✅ move_108_110_archive.sql
✅ tcpa_compliance_check.sql
```

### 4. **Monitoring Dashboard**
Created dashboard at: https://quotingfast-brain-ohio.onrender.com/admin/vici-lead-flow
- Real-time lead distribution view
- Recent movement history
- Auto-refreshes every 30 seconds

## 📋 NEXT STEPS - MANUAL SETUP REQUIRED

### 1. **Add Cron Jobs to Vici Server**

SSH to Vici server and add the cron entries:
```bash
ssh Superman@66.175.219.105 -p 22
crontab -e
```

Then add these entries (from `vici_scripts/crontab_entries.txt`):

```cron
# Every 15 minutes - Fast movements
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_callbk.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104_lvm.sql 2>&1 | logger -t vici_flow
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106_lvm.sql 2>&1 | logger -t vici_flow

# Daily at 12:01 AM - Workday-based movements
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103_workdays.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105_phase1.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107_phase2.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108_cooldown.sql 2>&1 | logger -t vici_flow
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_110_archive.sql 2>&1 | logger -t vici_flow

# Hourly - TCPA Compliance Check
0 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_compliance_check.sql 2>&1 | logger -t vici_tcpa
```

### 2. **Test the System**

Test a single script manually:
```bash
mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql
```

Monitor the lead flow:
```bash
mysql -u root Q6hdjl67GRigMofv -e 'SELECT * FROM lead_flow_dashboard'
```

### 3. **Configure Brain to Use List 101**

The Brain system has been updated to always push new leads to List 101:
- ViciDialerService now sets `$this->targetListId = 101`
- All new leads will enter the automated flow

## 🎯 How It Works

1. **New leads** arrive from Brain → Go to **List 101**
2. **After first call:**
   - CALLBK status → **List 103** (Voicemail phase)
   - Other statuses → **List 102** (Aggressive calling)
3. **Progressive movement** through lists based on:
   - Time in system
   - Call outcomes
   - Voicemail responses
4. **Automatic archiving** at 30 days or TCPA expiry → **List 110**

## 📊 Benefits

- **Fully automated** - No manual lead movement needed
- **TCPA compliant** - Automatic expiry handling
- **Optimized calling** - Right frequency at right time
- **Scalable** - Handles unlimited lead volume
- **Auditable** - Complete movement history

## 🔧 Troubleshooting

If leads aren't moving:
1. Check cron logs: `grep vici_flow /var/log/syslog`
2. Verify tables exist: `lead_moves`, `excluded_statuses`, `calendar`
3. Check for SQL errors in scripts
4. Ensure lists 101-110 exist in Vici

## 📈 Success Metrics

Monitor these KPIs:
- Lead velocity through lists
- Contact rates by list
- Transfer success by phase
- TCPA compliance rate
- Archive volume trends

---

**Status:** ✅ DEPLOYED - Awaiting cron configuration
**Date:** January 15, 2025
**Next Action:** Add cron jobs to Vici server






