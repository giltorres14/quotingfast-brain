# 🚀 QUOTINGFAST BRAIN - QUICK REFERENCE CARD

## 🔗 LIVE URLS
```
Main System:     https://quotingfast-brain-ohio.onrender.com
Leads Page:      https://quotingfast-brain-ohio.onrender.com/leads
Admin Panel:     https://quotingfast-brain-ohio.onrender.com/admin
Diagnostics:     https://quotingfast-brain-ohio.onrender.com/diagnostics
API Directory:   https://quotingfast-brain-ohio.onrender.com/api-directory
```

## 📨 WEBHOOK ENDPOINTS (For LeadsQuotingFast)
```
Main:    POST https://quotingfast-brain-ohio.onrender.com/webhook.php
Auto:    POST https://quotingfast-brain-ohio.onrender.com/webhook/auto
Home:    POST https://quotingfast-brain-ohio.onrender.com/webhook/home
Status:  GET  https://quotingfast-brain-ohio.onrender.com/webhook/status
```

## 🔑 CRITICAL CREDENTIALS

### Database (PostgreSQL)
```bash
Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
DB:   brain_production
User: brain_user
Pass: KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ
```

### Vici API
```bash
Server: philli.callix.ai
User:   apiuser
Pass:   UZPATJ59GJAVKG8ES6
List:   101
```

### Callix Whitelist
```bash
URL:  https://philli.callix.ai:26793/92RG8UJYTW.php
User: Superman
Pass: 8ZDWGAAQRD
```

## 🛠️ QUICK FIXES

### Database Not Connecting?
```bash
curl https://quotingfast-brain-ohio.onrender.com/force-clear-all-cache.php
```

### Check Lead Count
```bash
curl -s https://quotingfast-brain-ohio.onrender.com/leads | grep -o '[0-9]\+' | head -1
```

### Test Vici Connection
```bash
cd /path/to/brain && php test_vici_correct_api_creds.php
```

### Push Test Lead
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"contact":{"name":"Test","phone":"5551234567"}}'
```

## 📊 CURRENT STATUS
- **Leads**: 1,539
- **Database**: ✅ Connected
- **Vici**: ✅ Ready
- **Webhooks**: ✅ Active
- **Server IP**: 3.129.111.220

## 🚨 IF SOMETHING BREAKS

1. **First**: Check diagnostics page
2. **Second**: Run cache clear script
3. **Third**: Check Render deployment status
4. **Fourth**: Increment CACHE_BUST and redeploy

## 📱 SUPPORT CONTACTS
- **GitHub**: https://github.com/giltorres14/quotingfast-brain
- **Render**: https://dashboard.render.com
- **Deployment**: Auto-deploys from main branch

---
*Keep this card handy for quick system access!*


