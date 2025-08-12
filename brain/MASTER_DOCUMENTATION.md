# QuotingFast Brain System - Master Documentation
*Last Updated: January 2025*

## Table of Contents
1. [System Overview](#system-overview)
2. [Current Status](#current-status)
3. [Infrastructure](#infrastructure)
4. [Lead Flow](#lead-flow)
5. [API Integrations](#api-integrations)
6. [Vici Integration Details](#vici-integration-details)
7. [Database Configuration](#database-configuration)
8. [Deployment Process](#deployment-process)
9. [Known Issues & Solutions](#known-issues--solutions)
10. [Testing & Diagnostics](#testing--diagnostics)
11. [Go-Live Checklist](#go-live-checklist)
12. [Historical Data Migration](#historical-data-migration)
13. [Critical Files & Services](#critical-files--services)
14. [Cumulative Learning](#cumulative-learning)

---

## System Overview

The QuotingFast Brain system is a Laravel-based lead management platform that:
- Receives leads from LeadsQuotingFast (LQF) via webhooks
- Processes and enriches lead data
- Pushes leads to Vici dialer system (List 101)
- Provides agent interface for lead qualification
- Integrates with multiple buyer platforms (Allstate, RingBA, etc.)

### Tech Stack
- **Framework**: Laravel (PHP)
- **Database**: PostgreSQL (Render-hosted)
- **Deployment**: Render.com (Docker-based)
- **External Systems**: Vici Dialer, Allstate API, RingBA, Twilio (planned)

---

## Current Status

### ✅ Working Components
1. **Vici API Integration** - Fully functional with correct credentials
   - Add leads to List 101
   - Update existing leads
   - Proactive whitelist handling
2. **Webhook Endpoints** - All active and tested
   - `/webhook.php` - Main LQF webhook
   - `/webhook/home` - Home insurance leads
   - `/webhook/auto` - Auto insurance leads
3. **Lead Processing** - Database storage and field mapping
4. **Diagnostics Dashboard** - Comprehensive testing tools at `/diagnostics`

### ⏳ Pending Items
1. **Database Connection** - Fix for external hostname in deployment
2. **CSV Import** - Historical data import
3. **Twilio Integration** - SMS and scheduling
4. **Buyer Platform** - Full setup
5. **Agent Migration** - Move agents to Brain iframe

---

## Infrastructure

### Render Deployment
- **Service**: quotingfast-brain-ohio
- **Region**: Ohio (US East)
- **URL**: https://quotingfast-brain-ohio.onrender.com
- **Egress IP**: 3.129.111.220 (needs whitelisting in external systems)

### PostgreSQL Database (brain-postgres)
```
Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
Port: 5432
Database: brain_production
Username: brain_user
Password: KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ
```

**Internal Connection** (within Render):
```
postgresql://brain_user:KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ@dpg-d277kvk9c44c7388opg0-a:5432/brain_production
```

**External Connection** (from outside):
```
postgresql://brain_user:KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ@dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com:5432/brain_production
```

---

## Lead Flow

### Current Production Flow
```
1. LeadsQuotingFast → POST /webhook.php
2. Brain processes and stores lead
3. Brain pushes to Vici List 101 (via API)
4. Vici agents call and qualify leads
5. Qualified leads sent to buyers (Allstate, etc.)
```

### Webhook Data Structure
```json
{
  "contact": {
    "name": "John Doe",
    "phone": "5551234567",
    "email": "john@example.com",
    "address": "123 Main St",
    "city": "Columbus",
    "state": "OH",
    "zip_code": "43215"
  },
  "data": {
    "source": "LQF",
    "type": "auto",
    "drivers": [...],
    "vehicles": [...],
    "current_policy": {...}
  }
}
```

---

## API Integrations

### 1. Vici Dialer API

**WORKING CREDENTIALS** (Confirmed January 2025):
```
Server: philli.callix.ai
Endpoint: /vicidial/non_agent_api.php
User: apiuser
Password: UZPATJ59GJAVKG8ES6
List ID: 101 (hardcoded, not from env)
```

**Key Functions**:
- `add_lead` - Add new lead to List 101
- `update_lead` - Update existing lead by ID or vendor_lead_code
- `version` - Check API connectivity

**Implementation**: `app/Services/ViciDialerService.php`

### 2. Callix Whitelist Portal

**URL**: https://philli.callix.ai:26793/92RG8UJYTW.php

**Purpose**: Whitelist Render's IP (3.129.111.220) for Vici access

**Authentication Fields** (CRITICAL - must use these exact field names):
- `user_id` (NOT `user` or `username`)
- `password` (NOT `pass`)

**Known Credentials**:
- Superman / 8ZDWGAAQRD
- apiuser / UZPATJ59GJAVKG8ES6

**Implementation**: `app/Services/EnhancedCallixWhitelistService.php`

### 3. Allstate API

**Test Environment**:
```
URL: https://int.allstateleadmarketplace.com/v2/
Auth: Basic dGVzdHZlbmRvcjo=
```

**Production**:
```
URL: https://api.allstateleadmarketplace.com/v2/
API Key: b91446ade9d37650f93e305cbaf8c2c9
Auth: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6
```

### 4. RingBA API

**Credentials**:
```
Account ID: RAf810ac4421a34c9cbfbbf61288a1bec2
API Token: 09f0c9f046f7704cb233f54b8e21375fa6c9511b991e8f10fd3513342948f325...
Endpoint: https://api.ringba.com/v2/RAf810ac4421a34c9cbfbbf61288a1bec2/
```

---

## Vici Integration Details

### Lead Push Process

1. **Webhook receives lead** at `/webhook.php`
2. **Lead stored in database** with all fields
3. **ViciDialerService called** with lead data
4. **Proactive whitelist** attempted if connection fails
5. **API call to add_lead** with proper formatting
6. **Response logged** for debugging

### Field Mapping to Vici

```php
$viciData = [
    'phone_number' => $lead->phone,
    'first_name' => $lead->first_name,
    'last_name' => $lead->last_name,
    'email' => $lead->email,
    'address1' => $lead->address,
    'city' => $lead->city,
    'state' => $lead->state,
    'postal_code' => $lead->zip_code,
    'vendor_lead_code' => "BRAIN_{$lead->id}",
    'source_id' => $lead->source ?? 'LQF',
    'list_id' => 101,  // HARDCODED - DO NOT USE ENV
    'phone_code' => '1'
];
```

### Update Process for Historical Leads

**Artisan Command**: `php artisan vici:update-vendor-codes`

**Options**:
- `--dry-run` - Test without making changes
- `--batch=100` - Process in batches
- `--start-date=2024-10-01` - Filter by date

**What it does**:
1. Fetches leads from Brain database
2. For each lead with external_lead_id
3. Updates Vici lead's vendor_lead_code to "BRAIN_{id}"
4. Logs all changes

---

## Database Configuration

### Lead Table Structure
```sql
leads:
  - id (bigint, primary key)
  - external_lead_id (varchar) -- Vici's lead ID
  - name, first_name, last_name
  - phone, email
  - address, city, state, zip_code
  - source, type
  - campaign_id
  - drivers (json)
  - vehicles (json)
  - current_policy (json)
  - payload (json) -- Full webhook data
  - created_at, updated_at
```

### Important Notes
- IDs use 13-digit timestamp format (e.g., 1754577125000)
- Test leads have source = 'test' or NULL
- Production leads have source = 'LQF' or specific campaign

---

## Deployment Process

### Dockerfile Configuration (`Dockerfile.render`)

**Critical Settings**:
```dockerfile
ARG CACHE_BUST=12  # Increment when cache issues occur
DB_HOST=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com  # External hostname
VICI_API_PASS=UZPATJ59GJAVKG8ES6
VICI_TEST_MODE=false
```

### Deployment Steps
1. Make code changes
2. Increment CACHE_BUST if needed
3. Commit and push to GitHub
4. Render auto-deploys from main branch
5. Monitor at https://dashboard.render.com

### Common Deployment Issues & Fixes

**Cache Corruption**:
```bash
# In Dockerfile.render, increment:
ARG CACHE_BUST=13  # Was 12
```

**Database Connection Failures**:
- Use external hostname in production
- Skip artisan commands in startup.sh
- Add timestamp comment to force rebuild

---

## Known Issues & Solutions

### 1. Docker Cache Corruption
**Symptoms**: "failed to compute cache key", "unexpected commit digest"
**Solution**: Increment CACHE_BUST, add timestamp to .env

### 2. Database Authentication Failures
**Common Mistakes**:
- Using 'bpg0' instead of 'opg0' in hostname
- Wrong password (check character by character)
- Using internal hostname from outside Render

**Quick Fix**:
```bash
# Verify connection:
PGPASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ psql -h dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com -U brain_user brain_production -c "SELECT COUNT(*) FROM leads;"
```

### 3. Vici Connection Timeouts
**Cause**: IP not whitelisted
**Solution**: Hit Callix portal with correct credentials
```php
// Use EnhancedCallixWhitelistService
$whitelist = new EnhancedCallixWhitelistService();
$whitelist->authenticate('Superman', '8ZDWGAAQRD');
```

### 4. Leads Page Errors
**"Undefined property" errors**:
- Added isset() checks in blade templates
- This is correct defensive programming

**Wrong lead count**:
- Filter excludes test leads: `source != 'test' OR source IS NULL`

---

## Testing & Diagnostics

### Diagnostics Dashboard
**URL**: https://quotingfast-brain-ohio.onrender.com/diagnostics

**Available Tools**:
1. Database Connection Test
2. Vici API Test
3. Callix Whitelist Test
4. Webhook Test
5. Lead Push Test
6. Server Info (IP, environment)

### Test Scripts (in project root)
```bash
# Test Vici connection and lead push:
php test_vici_correct_api_creds.php

# Test updating a lead:
php test_update_real_lead.php

# Push test leads to Vici:
php push_test_leads_to_vici.php

# Test complete webhook flow:
php test_complete_lead.php
```

### Monitoring Endpoints
- `/webhook/status` - Check webhook health
- `/server-egress-ip` - Get Render's public IP
- `/test-leads` - Database connection test

---

## Go-Live Checklist

### Phase 1: Vici Agents Live ✅
- [x] Fix Vici API credentials
- [x] Ensure List 101 is target
- [x] Test lead push
- [x] Verify whitelist handling
- [x] Confirm vendor_lead_code format

### Phase 2: Historical Data Migration
- [ ] Run dry-run of vendor code update
- [ ] Update 3 months of historical leads
- [ ] Import CSV data if needed
- [ ] Verify all leads have Brain IDs

### Phase 3: Production Activation
- [ ] Update LQF webhook URL to Brain
- [ ] Monitor first 10 live leads
- [ ] Check Vici receives all data
- [ ] Verify agent access to leads

### Phase 4: Twilio Integration
- [ ] Configure Twilio credentials
- [ ] Set up SMS templates
- [ ] Implement scheduling logic
- [ ] Test appointment booking

### Phase 5: Buyer Platform
- [ ] Complete Allstate integration
- [ ] Configure RingBA routing
- [ ] Set up revenue tracking
- [ ] Test end-to-end flow

---

## Historical Data Migration

### Update Vendor Codes in Vici
```bash
# Test first with small batch:
php artisan vici:update-vendor-codes --dry-run --batch=10

# Run full update:
php artisan vici:update-vendor-codes

# Check specific date range:
php artisan vici:update-vendor-codes --start-date=2024-10-01 --end-date=2024-12-31
```

### Import CSV Data
```bash
# Place CSV in storage/app/imports/
php artisan leads:import historical_leads.csv

# With mapping file:
php artisan leads:import historical_leads.csv --mapping=field_mappings.json
```

---

## Critical Files & Services

### Core Services
- `app/Services/ViciDialerService.php` - Vici API integration
- `app/Services/EnhancedCallixWhitelistService.php` - Whitelist handling
- `app/Services/AllstateService.php` - Allstate API
- `app/Services/RingBAService.php` - RingBA integration

### Key Routes
- `routes/web.php` - All webhook and web routes
- Lines 897-925: Main webhook handler
- Lines 1200+: Diagnostic routes

### Blade Templates
- `resources/views/leads/index.blade.php` - Lead listing page
- `resources/views/diagnostics/index.blade.php` - Diagnostics dashboard
- `resources/views/agent/lead-display.blade.php` - Agent interface

### Configuration
- `config/services.php` - External service configs
- `.env` - Environment variables (created by Dockerfile)
- `Dockerfile.render` - Deployment configuration

### Artisan Commands
- `app/Console/Commands/UpdateViciVendorCodes.php` - Bulk update tool
- `app/Console/Commands/ImportLeads.php` - CSV import tool

---

## Cumulative Learning

### Docker/Render Lessons
1. **Never use heredocs** in Dockerfiles - causes parse errors
2. **Always increment CACHE_BUST** when builds fail
3. **Combine RUN commands** to reduce layers
4. **External hostname required** for DB in production
5. **Skip artisan commands** in startup to avoid premature DB connections
6. **Force rebuilds** with timestamp comments when needed

### Database Lessons
1. **Triple-check credentials** - small typos break everything
2. **Hostname: opg0 not bpg0** - common mistake
3. **Internal vs External** hostnames matter
4. **Document everything** in DATABASE_TROUBLESHOOTING.md
5. **Test connection** before assuming credentials are wrong

### Vici Integration Lessons
1. **Correct credentials**: apiuser / UZPATJ59GJAVKG8ES6
2. **List ID must be 101** - hardcode it, don't use env
3. **Whitelist expires** - need periodic re-authentication
4. **Callix portal fields**: user_id and password (not user/pass)
5. **Protocol fallback** - try HTTPS first, then HTTP
6. **Mock mode check** - ensure VICI_TEST_MODE=false

### Lead Processing Lessons
1. **Use isset() checks** in Blade templates
2. **Filter test leads** from counts and displays
3. **Store full payload** for debugging
4. **Generate proper IDs** - 13-digit timestamps
5. **Map fields correctly** to Vici format

### Testing Best Practices
1. **Create diagnostic tools** for production
2. **Test from deployed server** not just locally
3. **Log everything** during development
4. **Keep test scripts** for future debugging
5. **Document test credentials** and endpoints

---

## Emergency Procedures

### If Vici Stops Receiving Leads
1. Check `/diagnostics` dashboard
2. Test Vici API: `php test_vici_correct_api_creds.php`
3. Re-whitelist IP via Callix portal
4. Check credentials haven't changed
5. Verify List 101 exists and is active

### If Database Connection Fails
1. Check Render dashboard for service status
2. Verify credentials in Dockerfile.render
3. Test with external hostname
4. Increment CACHE_BUST and redeploy
5. Check DATABASE_TROUBLESHOOTING.md

### If Deployment Fails
1. Check Render build logs
2. Increment CACHE_BUST
3. Remove any heredoc syntax
4. Verify all COPY paths correct
5. Check DOCKER_CACHE_ISSUES.md

---

## Contact & Support

### External Systems
- **Vici/Callix**: Contact via existing support channels
- **Render**: https://dashboard.render.com/support
- **Allstate API**: Use test environment first
- **RingBA**: Check account dashboard for support

### Documentation Files
- `MASTER_DOCUMENTATION.md` - This file
- `VICI_GO_LIVE_CHECKLIST.md` - Step-by-step go-live
- `DATABASE_TROUBLESHOOTING.md` - DB connection issues
- `DOCKER_CACHE_ISSUES.md` - Build problems
- `CALLIX_WHITELIST_DOCUMENTATION.md` - Whitelist details
- `PROJECT_MEMORY.md` - Historical context
- `CHANGE_LOG.md` - All changes made
- `API_CONFIGURATIONS.md` - API credentials

---

## Version History
- v1.0 - Initial Brain system setup (August 2024)
- v1.1 - Allstate API integration (September 2024)
- v1.2 - Vici integration fixes (January 2025)
- v1.3 - Current version with full diagnostics

---

*End of Master Documentation*
