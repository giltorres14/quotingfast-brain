# 🐘 POSTGRESQL DATABASE PERSISTENCE - DEFINITIVE FIX

## 🎯 PROBLEM IDENTIFIED
After extensive research, the root cause of database wiping is:
1. **Render's free tier has ephemeral storage** - SQLite files are wiped on every deployment
2. **PostgreSQL database is already provisioned** but not being used
3. **`render.yaml` `fromDatabase` properties don't work with Docker containers**

## ✅ SOLUTION: Manual Environment Variables in Render Dashboard

Based on official Render documentation for Laravel Docker deployments, we need to **manually set environment variables in the Render Dashboard**.

### **Step 1: Get PostgreSQL Connection Details**
1. Go to your Render Dashboard
2. Click on your `brain-postgres` database
3. Copy the **Internal Database URL** (starts with `postgresql://`)
4. Note down individual connection details

### **Step 2: Add Environment Variables to Web Service**
In your `brain-api` web service settings, add these environment variables:

| Key | Value | Example |
|-----|-------|---------|
| `DATABASE_URL` | Internal database URL | `postgresql://brain_user:password@dpg-xxx:5432/brain_production` |
| `DB_CONNECTION` | `pgsql` | `pgsql` |
| `DB_HOST` | PostgreSQL host | `dpg-xxx-xxx.render.com` |
| `DB_PORT` | `5432` | `5432` |
| `DB_DATABASE` | Database name | `brain_production` |
| `DB_USERNAME` | Database username | `brain_user` |
| `DB_PASSWORD` | Database password | `[your-password]` |

### **Step 3: Update Dockerfile (Already Done)**
Our Dockerfile already has the startup script that will:
1. Check for PostgreSQL environment variables
2. Update `.env` file with these variables
3. Run migrations on PostgreSQL
4. Use persistent PostgreSQL database

### **Step 4: Deploy and Verify**
1. After adding environment variables, redeploy the service
2. Check `/api/database/status` - should show `"database_type": "pgsql"`
3. Test that data persists across deployments

## 🔧 WHY THIS WORKS
- **PostgreSQL is persistent** - data survives deployments
- **Environment variables override** the hardcoded SQLite settings
- **Docker containers require explicit env vars** (not `fromDatabase` references)
- **Official Render approach** for Laravel Docker deployments

## 📚 SOURCES
- [Official Render Laravel Docker Guide](https://render.com/docs/deploy-php-laravel-docker)
- [Render Environment Variables for Docker](https://community.render.com/t/deploying-docker-containers-with-environment-variables-help/12635)
- [Render PostgreSQL Documentation](https://render.com/docs/postgresql-creating-connecting)

## 🎉 EXPECTED RESULT
✅ Database type: PostgreSQL  
✅ Data persistence: Permanent  
✅ No more database wiping  
✅ Production-ready setup  