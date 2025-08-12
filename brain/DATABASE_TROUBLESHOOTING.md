# Database Connection Troubleshooting Guide

## ⚠️ CRITICAL: Database Credentials Issue History

### The Recurring Problem
**This issue has happened MULTIPLE times:** The database credentials in the Dockerfile get out of sync with the actual Render database credentials.

### Issue Timeline
1. **First Occurrence**: Database was initially set up with credentials
2. **First Fix**: Had to correct the credentials when they didn't match
3. **Second Occurrence (Aug 11, 2025)**: Same issue - credentials in Dockerfile didn't match actual database
4. **Pattern**: The hostname and password are the most common points of failure

## 🔴 COMMON MISTAKES TO AVOID

### 1. Hostname Confusion
- **WRONG**: `dpg-d277kvk9c44c7388bpg0-a` (note the 'bpg0')
- **CORRECT**: `dpg-d277kvk9c44c7388opg0-a` (note the 'opg0')
- The difference is subtle but critical: **opg0** not **bpg0**

### 2. Password Variations
- **OLD/WRONG**: `KoK8TYXZ6PShPKi8LTSdhHQQsCrnzcCQ`
- **CORRECT**: `KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ`
- Note the differences: `TYX26` instead of `TYXZ6` and `LIS` instead of `LTS`

## ✅ CORRECT Database Credentials (As of Aug 11, 2025)

```bash
DB_CONNECTION=pgsql
DB_HOST=dpg-d277kvk9c44c7388opg0-a
DB_PORT=5432
DB_DATABASE=brain_production
DB_USERNAME=brain_user
DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ
```

### Full Connection String
```
postgresql://brain_user:KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ@dpg-d277kvk9c44c7388opg0-a:5432/brain_production
```

## 🔍 How to Verify Database Credentials

### Step 1: Check Render Dashboard
1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click on `brain-postgres` service
3. Look for "Connections" section
4. Copy the EXACT values for:
   - Hostname
   - Port
   - Database
   - Username
   - Password

### Step 2: Compare with Dockerfile
Check `brain/Dockerfile.render` around line 210:
```dockerfile
echo "DB_HOST=dpg-d277kvk9c44c7388opg0-a" >> .env && \
echo "DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ" >> .env && \
```

### Step 3: Test Connection Locally
```bash
PGPASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ psql \
  -h dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com \
  -U brain_user \
  -d brain_production \
  -c "SELECT 1;"
```

## 🚨 Common Error Messages

### "password authentication failed for user"
**Cause**: Password in Dockerfile doesn't match Render database
**Solution**: Check and update the password in Dockerfile.render

### "could not translate host name"
**Cause**: Hostname is incorrect (usually bpg0 vs opg0)
**Solution**: Verify hostname from Render dashboard

### "FATAL: database does not exist"
**Cause**: Database name is wrong
**Solution**: Should be `brain_production`

## 📝 Prevention Checklist

Before deploying, ALWAYS:
- [ ] Verify database credentials haven't changed on Render
- [ ] Check hostname carefully (opg0 not bpg0)
- [ ] Confirm password matches exactly (no typos)
- [ ] Test connection using psql command above
- [ ] Keep this document updated with any changes

## 🔄 If Credentials Change Again

1. **Get new credentials from Render dashboard**
2. **Update Dockerfile.render** (around line 210)
3. **Update this document**
4. **Update API_CONFIGURATIONS.md**
5. **Commit with clear message**: "Update database credentials - [describe what changed]"

## 📌 Files That Need Updating When Database Changes

1. `brain/Dockerfile.render` - Line ~210
2. `brain/DATABASE_TROUBLESHOOTING.md` - This file
3. `brain/API_CONFIGURATIONS.md` - Database section
4. `brain/BRAIN_SYSTEM_DOCUMENTATION.md` - Database configuration section

## ⚡ Quick Fix Commands

When database auth fails, run these:
```bash
# 1. Check current credentials in Dockerfile
grep -A 5 "DB_HOST" brain/Dockerfile.render

# 2. Update credentials (edit the file)
vim brain/Dockerfile.render

# 3. Commit and deploy
git add -A && git commit -m "Fix database credentials" && git push origin main

# 4. Monitor deployment
curl -s https://quotingfast-brain-ohio.onrender.com/test-leads
```

## 🎯 Root Cause Analysis

### Why This Keeps Happening:
1. **No automated sync** between Render database and Dockerfile
2. **Credentials might rotate** on Render side without notification
3. **Manual copying** leads to typos (bpg0 vs opg0)
4. **Similar looking characters** in passwords cause confusion

### Long-term Solutions:
1. Use environment variables on Render instead of hardcoding
2. Create a pre-deployment script to verify credentials
3. Add database connection test to CI/CD pipeline
4. Document every credential change with timestamp

---

**LAST VERIFIED**: August 11, 2025
**VERIFIED BY**: System deployment
**STATUS**: ✅ Working with correct credentials

