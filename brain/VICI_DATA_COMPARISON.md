# Vici Data Collection Comparison

## 📊 New Export Script vs Previous API Method

### 🆕 **NEW: Direct Database Export Script (Every 5 min)**

**Data Source:** Direct MySQL query from `vicidial_log` + `vicidial_dial_log` tables

**Fields Collected:**
1. `call_date` - Exact timestamp of call
2. `lead_id` - Vici's internal lead ID
3. `list_id` - Which list the lead came from
4. `phone_number` - Full phone number
5. `campaign_id` - Campaign identifier
6. `status` - Call status (CONNECT, NA, VM, etc.)
7. `length_in_sec` - Total call duration
8. `server_ip` - Which Vici server handled the call
9. `extension` - Agent extension
10. `channel` - Technical channel info
11. `outbound_cid` - Caller ID used
12. `sip_hangup_cause` - Technical disconnect reason
13. `sip_hangup_reason` - Human-readable disconnect reason
14. `state` - Additional state info
15. `created_at` / `updated_at` - Timestamps

**Advantages:**
✅ **Real-time data** - Gets data within 5 minutes of calls
✅ **Complete call details** - All technical fields included
✅ **Direct database access** - No API limitations
✅ **SIP diagnostics** - Hangup causes for troubleshooting
✅ **Extension/Channel data** - Know exactly which agent/line
✅ **Server IP tracking** - Multi-server support
✅ **No data aggregation** - Raw, individual call records

---

### 📟 **PREVIOUS: API User Method (agent_stats_export)**

**Data Source:** Vici API endpoint with aggregated stats

**Fields Collected:**
1. `agent_name` - Agent identifier
2. `total_calls` - Count only
3. `talk_time` - Total seconds (aggregated)
4. `pause_time` - Agent pause metrics
5. `wait_time` - Time waiting for calls
6. `wrap_time` - After-call work time
7. `campaign` - Campaign name
8. Basic dispositions counts

**Limitations:**
❌ **Aggregated data** - Lost individual call details
❌ **No phone numbers** - Can't match specific leads
❌ **No SIP data** - Can't diagnose call issues
❌ **No timestamps** - Only date ranges
❌ **API restrictions** - Rate limits, auth issues
❌ **Missing technical data** - No channels, extensions, servers
❌ **Delayed data** - Often hours behind

---

## 🎯 **Key Differences Summary**

| Aspect | New Export Script | Previous API |
|--------|------------------|--------------|
| **Data Granularity** | Individual calls | Aggregated stats |
| **Update Frequency** | Every 5 minutes | Manual/hourly |
| **Phone Numbers** | ✅ Full numbers | ❌ Not available |
| **Lead Matching** | ✅ Can match to Brain leads | ❌ Only agent totals |
| **Technical Details** | ✅ SIP, channels, servers | ❌ Basic stats only |
| **Call Outcomes** | ✅ Detailed statuses | ⚠️ Simple counts |
| **Historical Data** | ✅ Complete history | ⚠️ Limited by API |
| **Real-time** | ✅ 5-minute delay | ❌ Hours delay |

---

## 💡 **What This Means For You**

### With the NEW Export Script, you can:

1. **Track every single call** - Not just summaries
2. **Match calls to specific leads** - Via phone number
3. **Debug call issues** - SIP hangup causes tell you why calls failed
4. **Monitor agent performance** - See exactly which extension made each call
5. **Analyze patterns** - Raw data allows custom analysis
6. **Build detailed reports** - Campaign performance by actual calls, not estimates
7. **Identify orphan calls** - Find calls without matching leads
8. **Track call routing** - See which server handled what

### What you were missing with API method:

- Individual call records
- Phone number tracking
- Technical diagnostics
- Real-time updates
- Server/extension details
- Accurate lead-to-call matching

---

## 📈 **Bottom Line**

The new export script provides **10x more detailed data** than the API method:
- **Raw call records** vs aggregated stats
- **Every field from Vici** vs limited API response
- **Lead-level tracking** vs agent-level summaries
- **Technical diagnostics** vs basic counts

This is like going from a summary report to having the actual database at your fingertips!



## 📊 New Export Script vs Previous API Method

### 🆕 **NEW: Direct Database Export Script (Every 5 min)**

**Data Source:** Direct MySQL query from `vicidial_log` + `vicidial_dial_log` tables

**Fields Collected:**
1. `call_date` - Exact timestamp of call
2. `lead_id` - Vici's internal lead ID
3. `list_id` - Which list the lead came from
4. `phone_number` - Full phone number
5. `campaign_id` - Campaign identifier
6. `status` - Call status (CONNECT, NA, VM, etc.)
7. `length_in_sec` - Total call duration
8. `server_ip` - Which Vici server handled the call
9. `extension` - Agent extension
10. `channel` - Technical channel info
11. `outbound_cid` - Caller ID used
12. `sip_hangup_cause` - Technical disconnect reason
13. `sip_hangup_reason` - Human-readable disconnect reason
14. `state` - Additional state info
15. `created_at` / `updated_at` - Timestamps

**Advantages:**
✅ **Real-time data** - Gets data within 5 minutes of calls
✅ **Complete call details** - All technical fields included
✅ **Direct database access** - No API limitations
✅ **SIP diagnostics** - Hangup causes for troubleshooting
✅ **Extension/Channel data** - Know exactly which agent/line
✅ **Server IP tracking** - Multi-server support
✅ **No data aggregation** - Raw, individual call records

---

### 📟 **PREVIOUS: API User Method (agent_stats_export)**

**Data Source:** Vici API endpoint with aggregated stats

**Fields Collected:**
1. `agent_name` - Agent identifier
2. `total_calls` - Count only
3. `talk_time` - Total seconds (aggregated)
4. `pause_time` - Agent pause metrics
5. `wait_time` - Time waiting for calls
6. `wrap_time` - After-call work time
7. `campaign` - Campaign name
8. Basic dispositions counts

**Limitations:**
❌ **Aggregated data** - Lost individual call details
❌ **No phone numbers** - Can't match specific leads
❌ **No SIP data** - Can't diagnose call issues
❌ **No timestamps** - Only date ranges
❌ **API restrictions** - Rate limits, auth issues
❌ **Missing technical data** - No channels, extensions, servers
❌ **Delayed data** - Often hours behind

---

## 🎯 **Key Differences Summary**

| Aspect | New Export Script | Previous API |
|--------|------------------|--------------|
| **Data Granularity** | Individual calls | Aggregated stats |
| **Update Frequency** | Every 5 minutes | Manual/hourly |
| **Phone Numbers** | ✅ Full numbers | ❌ Not available |
| **Lead Matching** | ✅ Can match to Brain leads | ❌ Only agent totals |
| **Technical Details** | ✅ SIP, channels, servers | ❌ Basic stats only |
| **Call Outcomes** | ✅ Detailed statuses | ⚠️ Simple counts |
| **Historical Data** | ✅ Complete history | ⚠️ Limited by API |
| **Real-time** | ✅ 5-minute delay | ❌ Hours delay |

---

## 💡 **What This Means For You**

### With the NEW Export Script, you can:

1. **Track every single call** - Not just summaries
2. **Match calls to specific leads** - Via phone number
3. **Debug call issues** - SIP hangup causes tell you why calls failed
4. **Monitor agent performance** - See exactly which extension made each call
5. **Analyze patterns** - Raw data allows custom analysis
6. **Build detailed reports** - Campaign performance by actual calls, not estimates
7. **Identify orphan calls** - Find calls without matching leads
8. **Track call routing** - See which server handled what

### What you were missing with API method:

- Individual call records
- Phone number tracking
- Technical diagnostics
- Real-time updates
- Server/extension details
- Accurate lead-to-call matching

---

## 📈 **Bottom Line**

The new export script provides **10x more detailed data** than the API method:
- **Raw call records** vs aggregated stats
- **Every field from Vici** vs limited API response
- **Lead-level tracking** vs agent-level summaries
- **Technical diagnostics** vs basic counts

This is like going from a summary report to having the actual database at your fingertips!


