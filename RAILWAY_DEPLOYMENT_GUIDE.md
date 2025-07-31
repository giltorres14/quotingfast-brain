# Railway Deployment Guide for QuotingFast Platform

## Overview
This guide will help you deploy both the Laravel Brain (lead management) and Parcelvoy Platform (SMS/Email marketing) to Railway.

## Quick Deploy (Recommended)

### Step 1: Deploy via GitHub Integration
1. Go to [Railway.app](https://railway.app)
2. Sign up/Login with your GitHub account
3. Click "Deploy from GitHub repo"
4. Select: `giltorres14/quotingfast-brain`
5. Railway will automatically detect the `railway.toml` configuration

### Step 2: Add Database
1. In your Railway project dashboard, click "Add Service"
2. Select "Database" → "PostgreSQL"
3. Railway will automatically provision a PostgreSQL database
4. The database environment variables will be automatically connected

### Step 3: Configure Environment Variables
Railway will automatically set most variables, but verify these are set:

**For Laravel Brain Service:**
- `APP_KEY` - Will be auto-generated
- `APP_URL` - Will be set to your Railway domain
- `DB_*` variables - Auto-connected to PostgreSQL
- `APP_ENV=production`
- `APP_DEBUG=false`

**For Parcelvoy Platform Service:**
- `NODE_ENV=production`
- `API_SECRET=parcelvoy-secret-key-2025`
- `JWT_SECRET=jwt-secret-key-2025`
- `DATABASE_URL` - Auto-connected to PostgreSQL

### Step 4: Deploy
1. Railway will automatically build and deploy both services
2. You'll get two URLs:
   - Laravel Brain: `https://laravel-brain-xxx.railway.app`
   - Parcelvoy Platform: `https://parcelvoy-platform-xxx.railway.app`

## What's Included

### Laravel Brain Features
- ✅ Lead management system
- ✅ User authentication (admin/client roles)
- ✅ PostgreSQL database integration
- ✅ API endpoints for lead data
- ✅ Webhook support for LeadsQuotingFast
- ✅ Client portal for lead access

### Parcelvoy Platform Features
- ✅ SMS/Email marketing automation
- ✅ Campaign management
- ✅ Contact list management
- ✅ Journey automation
- ✅ Analytics and reporting

## Default Admin Access

After deployment, you can create an admin user by running:
```bash
php artisan make:admin-user
```

Or access the Laravel application directly and register the first user (will be admin by default).

## Database Schema

The following tables will be automatically created:
- `users` - Admin and client users
- `leads` - Lead management data
- `lead_assignments` - User-lead relationships
- Plus all Parcelvoy tables for campaigns, contacts, etc.

## Integration

Both applications share the same PostgreSQL database, allowing:
- Leads from Laravel Brain to be automatically added to Parcelvoy contact lists
- Campaign results to be tracked back to lead sources
- Unified user management across both platforms

## Monitoring

Railway provides built-in monitoring for:
- Application logs
- Database performance
- Resource usage
- Deployment status

## Custom Domain (Optional)

To use your own domain:
1. Go to your service settings in Railway
2. Add your custom domain
3. Update DNS records as instructed
4. Update `APP_URL` environment variable

## Support

If you encounter any issues:
1. Check Railway logs in the dashboard
2. Verify all environment variables are set
3. Ensure PostgreSQL database is running
4. Check GitHub repository for latest updates

## Cost Estimate

Railway pricing (as of 2024):
- Hobby Plan: $5/month per service
- Database: Included in service cost
- Total estimated: ~$10-15/month for both services

---

**Repository:** https://github.com/giltorres14/quotingfast-brain
**Documentation:** This README and inline code comments
**Support:** GitHub Issues in the repository