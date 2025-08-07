# 🧠 The Brain System - Complete Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Lead Flow Architecture](#lead-flow-architecture)
3. [External Lead ID System](#external-lead-id-system)
4. [API Integrations](#api-integrations)
5. [Testing Infrastructure](#testing-infrastructure)
6. [Database Schema](#database-schema)
7. [Key Files & Services](#key-files--services)
8. [Troubleshooting Guide](#troubleshooting-guide)

---

## System Overview

**The Brain** is a comprehensive lead management and distribution system that:
- Receives leads from multiple sources via webhooks
- Processes and enriches lead data
- Distributes leads to various buyers (Allstate, etc.)
- Integrates with ViciDialer for agent qualification
- Tracks all interactions and outcomes

### Technology Stack
- **Backend**: Laravel (PHP)
- **Database**: PostgreSQL (Production), SQLite (Local)
- **Deployment**: Render.com
- **Call Center**: ViciDialer
- **APIs**: Allstate, RingBA, Twilio

---

## Lead Flow Architecture

### Current Production Flow (As of Aug 2025)
```
1. Lead Reception (Webhook)
   ↓
2. External Lead ID Generation (9-digit: 100000001+)
   ↓
3. Database Storage
   ↓
4. Allstate API Testing (Automatic)
   ↓
5. Test Log Recording
   ↓
6. Dashboard Display
```

### Standard Flow (When Testing Complete)
```
1. Lead Reception → 2. Vici Integration → 3. Agent Qualification
   ↓
4. RingBA Enrichment → 5. Buyer Distribution → 6. Outcome Tracking
```

---

## External Lead ID System

### Format: 9-Digit Sequential IDs
- **Starting ID**: 100000001
- **Format**: Always 9 digits, zero-padded
- **Increment**: Sequential (+1 for each new lead)
- **Storage**: `external_lead_id` field in leads table

### Implementation Details
```php
// Location: routes/web.php - generateLeadId() function

function generateLeadId() {
    // Get highest existing 9-digit ID
    $lastLead = Lead::whereNotNull('external_lead_id')
                   ->whereRaw("LENGTH(external_lead_id) = 9")
                   ->whereRaw("external_lead_id >= '100000001'")
                   ->orderBy('external_lead_id', 'desc')
                   ->first();
    
    // Increment or start fresh
    $nextId = $lastLead ? intval($lastLead->external_lead_id) + 1 : 100000001;
    
    return str_pad($nextId, 9, '0', STR_PAD_LEFT);
}
```

### Why This Format?
- ViciDialer requires consistent numeric IDs
- 9 digits provide room for 899,999,999 leads
- Sequential for easy tracking and debugging
- Overrides any incoming webhook IDs for consistency

---

## API Integrations

### 1. Allstate Lead Marketplace API

#### Endpoints
- **Production**: `https://api.allstateleadmarketplace.com/v2`
- **Testing**: `https://int.allstateleadmarketplace.com/v2`

#### Authentication
- **Production API Key**: `b91446ade9d37650f93e305cbaf8c2c9`
- **Base64 Encoded**: `YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6`
- **Header**: `Authorization: Basic YjkxNDQ2YWRlOWQzNzY1MGY5M2UzMDVjYmFmOGMyYzk6`

#### Current Testing Setup
- Using production API with `/ping` endpoint for testing
- Test environment only supports connectivity checks
- All leads automatically sent for testing (bypassing Vici temporarily)

#### Key Service Files
- `app/Services/AllstateCallTransferService.php` - Main API integration
- `app/Services/AllstateTestingService.php` - Testing orchestration
- `app/Services/AutoQualificationService.php` - Auto-generates qualification data

### 2. RingBA API

#### Authentication
- **Account ID**: `RAf810ac4421a34c9cbfbbf61288a1bec2`
- **API Token**: `09f0c9f046f7704cb233f54b8e21375fa6c9511b991e8f10fd3513342948f325...`
- **Header Format**: `Authorization: Token {token}`

#### URL Parameters (95 Total)
All parameters configured for comprehensive lead tracking:
- Basic: lead_id, external_id, zip_code, state_name
- Insurance: currently_insured, current_insurance_company, policy_expiration_date
- Demographics: first_name, last_name, email, phone, dob, gender
- Vehicles: vehicle_year, vehicle_make, vehicle_model, vin
- And 60+ more...

### 3. ViciDialer Integration

#### Configuration
- **List ID**: 101 (hard-coded to prevent errors)
- **Webform URL**: `https://brain-api.onrender.com/agent/lead/{external_lead_id}`
- **Integration**: Two-way via webhooks and API

#### Key Files
- `app/Services/ViciDialerService.php` - API integration
- `resources/views/agent/lead-display.blade.php` - Agent interface

---

## Testing Infrastructure

### Allstate Testing Dashboard
**URL**: `/admin/allstate-testing`

#### Features
- Real-time test results display
- Success/failure tracking
- Response time monitoring
- Bulk processing capability
- Session-based grouping

#### Test Log Fields
```php
AllstateTestLog:
- lead_id, external_lead_id
- qualification_data (JSON)
- allstate_payload (JSON)
- allstate_endpoint
- response_status
- success (boolean)
- error_message
- response_time_ms
- test_session
```

### Local Testing Commands
```bash
# Test webhook with new lead
curl -X POST http://127.0.0.1:8001/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"contact": {...}, "data": {...}}'

# Check test results
php artisan tinker
>>> AllstateTestLog::latest()->first();
```

---

## Database Schema

### Primary Tables

#### `leads` Table
- **id**: Internal auto-increment ID
- **external_lead_id**: 9-digit ID for external systems
- **name**, **first_name**, **last_name**
- **phone**, **email**
- **address**, **city**, **state**, **zip_code**
- **type**: auto/home
- **source**: leadsquotingfast/other
- **drivers**: JSON field
- **vehicles**: JSON field
- **current_policy**: JSON field
- **payload**: Complete original data (JSON)

#### `allstate_test_logs` Table
- Complete test transaction records
- Request/response payloads
- Success metrics
- Error tracking

---

## Key Files & Services

### Routes & Handlers
- **`routes/web.php`** - All webhook endpoints and route definitions
  - `/webhook.php` - Main lead intake (LeadsQuotingFast)
  - `/webhook/vici` - ViciDialer integration
  - `/admin/allstate-testing` - Testing dashboard
  - `/agent/lead/{id}` - Agent lead display

### Services (app/Services/)
- **AllstateCallTransferService.php** - Allstate API communication
- **AllstateTestingService.php** - Testing orchestration
- **AutoQualificationService.php** - Smart data generation
- **ViciDialerService.php** - Vici API integration
- **RingBAService.php** - RingBA enrichment

### Views (resources/views/)
- **admin/allstate-testing.blade.php** - Testing dashboard
- **agent/lead-display.blade.php** - Agent qualification form
- **leads/index.blade.php** - Main leads dashboard
- **buyer/leads.blade.php** - Buyer-specific view

### Configuration
- **Dockerfile.render** - Production Docker configuration
- **render.yaml** - Render deployment blueprint
- **.env variables** - Environment-specific settings

---

## Troubleshooting Guide

### Common Issues & Solutions

#### 1. External Lead ID Not Incrementing
**Problem**: Leads getting random IDs instead of sequential
**Solution**: Check `generateLeadId()` function in routes/web.php
**Verify**: Database supports LENGTH() and comparison operators

#### 2. Allstate Testing Not Working
**Problem**: Leads not being sent to Allstate
**Check**:
- AllstateTestingService is properly imported in routes/web.php
- Testing bypass is active (line ~900 in routes/web.php)
- API credentials are correct in AllstateCallTransferService

#### 3. Vici Integration Issues
**Problem**: Leads not appearing in Vici or wrong list
**Solution**: Ensure list_id = 101 in ViciDialerService
**Check**: Webform URL format in Vici campaign settings

#### 4. Database Connection (Production)
**Connection String**: 
```
postgresql://brain_user:KoK8TYXZ6PShPKi8LTSdhHQQsCrnzcCQ@dpg-d277kvk9c44c7388bpg0-a:5432/brain_production
```

### Debug Commands
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# View recent leads
>>> Lead::latest()->take(5)->get();

# Check Allstate test results
>>> AllstateTestLog::where('success', true)->count();
```

---

## Current Status (Aug 2025)

### ✅ Working
- External Lead ID generation (9-digit format)
- Allstate API testing with /ping endpoint
- Lead reception via webhooks
- Database storage and retrieval
- Testing dashboard
- RingBA parameter configuration

### 🔧 In Testing Mode
- Vici integration temporarily bypassed
- All leads auto-sent to Allstate for testing
- Using production API for test submissions

### 📋 TODO
- Restore Vici integration after testing
- Implement bulk processing fixes
- Add more date filter options
- Optimize page load times

---

## Important Notes

### Security Considerations
- All API keys should be in environment variables
- CSRF protection enabled for POST routes
- Database credentials secured in Render

### Performance
- Webhook responses kept under 1 second
- Database indexes on external_lead_id
- JSON fields for flexible data storage

### Maintenance
- Regular log rotation needed
- Monitor test log table size
- Keep API documentation updated

---

*Last Updated: August 7, 2025*
*Version: 2.0 - Post Allstate Integration*
