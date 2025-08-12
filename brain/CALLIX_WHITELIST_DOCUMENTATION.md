# 🔐 Callix Whitelist System Documentation

## ⚠️ CRITICAL: IP Whitelisting is Required for Vici Integration

### **The Whitelist Portal**
- **URL**: https://philli.callix.ai:26793/92RG8UJYTW.php
- **Purpose**: Authenticates and whitelists Render's IP address for Vici API access
- **Frequency**: Must be refreshed every 30 minutes (sporadically)
- **Render's IP**: 3.129.111.220

## 📋 How It Works

### 1. **Automatic Whitelist Refresh**
The system automatically refreshes the whitelist:
- **Every 30 minutes** via proactive authentication
- **Before each Vici API call** if needed
- **On failure** with retry logic (3 attempts)

### 2. **Manual Whitelist Endpoints**
```bash
# Manual whitelist refresh
curl https://quotingfast-brain-ohio.onrender.com/vici/whitelist

# Check whitelist status
curl https://quotingfast-brain-ohio.onrender.com/debug/vici-config
```

### 3. **Whitelist Service (Already Implemented)**
Located in: `app/Services/CallixWhitelistService.php`

**Key Methods:**
- `refreshWhitelist()` - Authenticates with Callix portal
- `ensureWhitelisted()` - Checks if refresh needed (5-minute interval)
- `testViciConnection()` - Verifies Vici API access after whitelisting
- `refreshWithRetry()` - Retry logic with 3 attempts

## 🔧 Configuration

### Environment Variables
```env
# Vici Server Configuration
VICI_SERVER=philli.callix.ai
VICI_WEB_SERVER=philli.callix.ai
VICI_API_ENDPOINT=/vicidial/non_agent_api.php

# API Credentials (for Vici)
VICI_API_USER=UploadAPI
VICI_API_PASS=8ZDWGAAQRD

# Callix Portal Credentials (if different)
CALLIX_USER_ID=UploadAPI
CALLIX_PASSWORD=8ZDWGAAQRD
```

## 🔄 Automatic Whitelist Flow

### In Routes (web.php line ~5976)
```php
// Proactive whitelist every 30 minutes
$lastWhitelist = Cache::get('vici_last_whitelist');
$shouldProactiveWhitelist = !$lastWhitelist || 
    Carbon::parse($lastWhitelist)->diffInMinutes(now()) > 30;

if ($shouldProactiveWhitelist) {
    $firewallAuth = Http::asForm()->timeout(10)->post(
        "https://philli.callix.ai:26793/92RG8UJYTW.php",
        ['user' => $viciConfig['user'], 'pass' => $viciConfig['pass']]
    );
    Cache::put('vici_last_whitelist', now()->toISOString(), 3600);
}
```

### In ViciDialerService
The service automatically ensures whitelisting before sending leads:
1. Checks if whitelisted recently (within 5 minutes)
2. If not, triggers refresh
3. Retries up to 3 times if fails
4. Only sends lead if whitelisted successfully

## 🚨 Troubleshooting

### Common Issues

#### 1. "Connection timed out" to Vici
**Cause**: IP not whitelisted or whitelist expired
**Solution**: System auto-refreshes, or manually hit `/vici/whitelist`

#### 2. "ERROR: Login incorrect" from Vici
**Cause**: Wrong API credentials (not whitelist issue)
**Solution**: Check VICI_API_USER and VICI_API_PASS

#### 3. Leads not appearing in Vici
**Check:**
1. Whitelist status: `/debug/vici-config`
2. Last refresh time in cache
3. Vici API credentials
4. List 101 exists in Vici

### Manual Testing
```bash
# Test complete flow with whitelist
php test_complete_lead.php

# Check whitelist status
curl https://quotingfast-brain-ohio.onrender.com/vici-whitelist-check.php

# Force refresh
curl https://quotingfast-brain-ohio.onrender.com/vici/whitelist
```

## 📊 Monitoring

### Key Metrics to Track
- Last whitelist refresh time
- Number of failed whitelist attempts
- Vici API success rate after whitelisting
- Lead delivery success rate

### Log Entries to Watch
```
🔄 Refreshing Callix whitelist...
✅ Callix whitelist refreshed successfully
❌ Callix whitelist refresh failed
⚠️ Attempting to refresh Callix whitelist before Vici send
```

## 🔐 Security Notes

1. **SSL Verification**: Disabled for self-signed cert (`SSL_VERIFYPEER = false`)
2. **Credentials**: Stored in environment variables, not in code
3. **Cache**: Whitelist status cached for 1 hour
4. **Timeout**: 10-second timeout on whitelist requests

## 📝 Important Files

- `/app/Services/CallixWhitelistService.php` - Main whitelist service
- `/routes/web.php` (lines 749, 824, 940, 5979, 6037) - Whitelist triggers
- `/app/Services/ViciDialerService.php` - Uses whitelist service
- `/config/services.php` - Vici configuration

## ⚡ Quick Commands

```bash
# Check if whitelisted
curl https://quotingfast-brain-ohio.onrender.com/debug/vici-config | jq .last_whitelist

# Force whitelist refresh
curl https://quotingfast-brain-ohio.onrender.com/vici/whitelist

# Test Vici connection
curl https://quotingfast-brain-ohio.onrender.com/test-vici-connection

# Send test lead (with auto-whitelist)
php test_complete_lead.php
```

## 🎯 Key Takeaways

1. **Whitelist is AUTOMATIC** - System handles it every 30 minutes
2. **Retry logic exists** - 3 attempts with 5-second delays
3. **Multiple triggers** - Before leads, on routes, manual endpoints
4. **Cached for efficiency** - Doesn't hit Callix on every request
5. **Fallback mechanisms** - HTTPS→HTTP, multiple retry attempts

---

**Last Updated**: August 11, 2025
**Status**: ✅ FULLY IMPLEMENTED AND WORKING
**Render IP**: 3.129.111.220
**Callix Portal**: https://philli.callix.ai:26793/92RG8UJYTW.php


