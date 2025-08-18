# Brain UI Documentation
**Last Updated:** January 15, 2025 - 10:30 AM EST

## 🎨 UI Structure Overview

### Top Navigation Bar
- **Color:** Blue gradient (#4A90E2) with white text
- **Logo:** QuotingFast white logo (https://quotingfast.com/whitelogo) 
- **Header Text:** "The Brain" (NOT "LQF")
- **Menu Structure:** LEADS | VICI | SMS | BUYER PORTAL | ADMIN
- **Layout:** Using `layouts/app.blade.php` as the main template

### Navigation Routes
```
/leads → Lead Dashboard
/vici → Vici Dashboard  
/sms → SMS Dashboard
/buyers → Buyer Portal
/admin → Admin Dashboard
```

---

## 📄 Page-by-Page Documentation

### 1. LEAD DASHBOARD (`/leads`)
**File:** `resources/views/leads/index-new.blade.php`

#### Header Stats Section
- **Default Period:** Today's numbers
- **Period Options:** Today | Yesterday | Last 7 Days | Last 30 Days | Custom (with date pickers)
- **Stats Display:** Total Leads, Today's Leads, Yesterday's Leads, This Week, This Month
- **Loading Indicator:** Shows "Loading..." when switching periods
- **Manual Refresh:** Blue refresh button in top-right of stats section

#### Lead Cards
- **Per-Page Selector:** Between search box and first lead card
- **Options:** 10, 25, 50, 100 leads per page
- **Lead Source Badge:** Shows vendor (e.g., "LQF" for LeadsQuotingFast)
- **NO Email Display:** Email removed from cards per user request
- **Vici Status:** Shows list ID if assigned
- **Action Buttons:** View | Edit | Payload

#### Data Sources
- Leads pulled from `leads` table
- Source field determines vendor badge
- Vici status from `vici_list_id` field

---

### 2. LEAD VIEW/EDIT PAGE (`/agent/lead/{id}`)
**File:** `resources/views/agent/lead-display.blade.php`

#### Header Section (Sticky)
- **Position:** Fixed to top when scrolling
- **Height:** Narrow (just enough space for info)
- **Content Order:**
  1. Back to Leads button (far left)
  2. Vendor badge
  3. Name
  4. Phone
  5. Address
  6. Email (above Lead ID)
  7. Lead ID (below email)
- **Action Buttons:** View Complete Payload | Save Lead (in header)

#### TCPA Compliance Section
**Data Sources (Check in order):**
1. Direct lead fields
2. `meta` JSON field
3. `payload` JSON field

**Fields Display:**
- Opt-in Date & Time (with TCPA status: X days remaining)
- TCPA Consent Text (hidden by default, "Show Text" button to reveal)
- TrustedForm Certificate URL (with copy button)
- LeadID/Jornaya Token (with copy button)
- IP Address (with copy button)
- Page URL (with copy button)
- Referring URL (with copy button)

**TCPA Days Calculation:**
```php
$daysOld = $lead->opt_in_date ? 
    Carbon::parse($lead->opt_in_date)->diffInDays(now()) : 90;
$daysRemaining = floor(90 - $daysOld);
```

#### Driver Details
**Years Licensed Calculation:**
- If `age_first_licensed` provided: Use that value
- Otherwise: Hard-code to 16

#### Vehicle Cards
**Required Fields:**
- Year, Make, Model, Trim
- VIN
- Primary Use
- Annual Mileage
- Comprehensive Deductible
- Collision Deductible
- Garage Type

#### Edit Mode
- Shows all 13 qualification questions at top
- Hides vendor/buyer section
- Back to Dashboard visible for admins only
- Hidden for iframe users

#### Iframe Detection
```javascript
const isInIframe = window.self !== window.top;
if (isInIframe) {
    // Hide navigation elements
}
```

---

### 3. VICI DASHBOARD (`/vici`)
**File:** `resources/views/vici/dashboard.blade.php`

#### Metrics Display
- Total Leads in Vici
- Leads Called Today
- Orphan Calls (unmatched)
- Average Call Time

#### Data Sources
- `vici_call_logs` table for call data
- `orphan_call_logs` table for unmatched calls
- Uses `matched_at` timestamp (NOT `matched` boolean)

#### Scopes Fixed
```php
// OrphanCallLog model
public function scopeUnmatched($query) {
    return $query->whereNull('matched_at');
}
```

---

### 4. VICI LEAD FLOW (`/vici/lead-flow`)
**File:** `resources/views/vici/lead-flow.blade.php`

#### Page Structure
- Visual flow chart: 101 → 102 → 103 → 104 → 105 → 106 → 107 → 108 → 110
- Each list shows as a card with editable fields

#### Editable Fields (Per List)
- Days in List
- List Resets/Day (not calls/day)
- Reset Times (e.g., "9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM")
- Description

#### Calculated Fields
- Total Calls = Days × Resets/Day
- Call Range (e.g., "3-14" for List 104)
- Current Lead Count (from database)

#### Edit Modes
1. **Unlocked (Default):** All fields editable with yellow background
2. **Locked:** Read-only mode for reference

#### Action Buttons
- 🔒 Lock Configuration / 🔓 Unlock for Editing
- 💾 Save All Changes
- 🔄 Recalculate Ranges
- 📊 Refresh Lead Counts

#### List Reset Schedule Using Vici's Reset Feature
| List | Days | Resets/Day | Reset Times | Total Calls |
|------|------|------------|-------------|-------------|
| 101 | 0 | 0 | None | 0 |
| 102 | 1 | 1 | 9:00 AM | 1 |
| 103 | 1 | 1 | 2:00 PM | 1 |
| 104 | 3 | 4 | 9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM | 12 |
| 105 | 5 | 2 | 10:00 AM, 3:00 PM | 10 |
| 106 | 7 | 1 | 11:00 AM | 7 |
| 107 | 14 | 0.5 | Mon/Wed/Fri 10:00 AM | 7 |
| 108 | 14 | 0.25 | Tue/Thu 2:00 PM | 3-4 |
| 110 | ∞ | 0 | None | Archive |

---

### 5. STUCK IN QUEUE (`/admin/lead-queue`)
**File:** `resources/views/admin/lead-queue.blade.php`

#### Purpose
Shows leads that haven't been processed by Vici

#### Table Features
- Horizontal scrolling for small screens
- Date column added
- Lead Detail button opens modal

#### Data Source
- Leads with `vici_list_id = 0` or NULL
- Ordered by `created_at DESC`

---

### 6. CAMPAIGNS (`/campaigns/directory`)
**File:** `resources/views/campaigns/directory.blade.php`

#### Features
- List all campaigns
- Delete button with confirmation
- Prevents deletion if campaign has leads

---

## 🔧 Common UI Components

### Date/Time Display
- **Timezone:** America/New_York (EST/EDT)
- **Helper Function:** `estNow()` instead of `now()`
- **Display Format:** "Jan 15, 2025 10:30 AM EST"

### Copy Buttons
```html
<button onclick="copyToClipboard('text-to-copy')" class="copy-btn">
    📋
</button>
```

### Loading States
```javascript
element.textContent = 'Loading...';
// After data loads
element.textContent = data.value;
```

### Modal Implementation
```javascript
function showModal(content) {
    const modal = document.getElementById('modal');
    modal.querySelector('.modal-content').innerHTML = content;
    modal.style.display = 'block';
}
```

---

## 🚨 Critical Issues Fixed Today

### 1. Vici 500 Error
**Problem:** `column "matched" does not exist`
**Solution:** Use `matched_at` timestamp field instead

### 2. Webhooks Not Saving Leads
**Problem:** Routes were commented out and missing `Lead::create()`
**Solution:** Uncommented routes and added:
```php
\App\Models\Lead::create($leadData);
```

### 3. Deployment Syntax Errors
**Fixed in routes/web.php:**
- Line 3: `use App\Helpers\timezone;` (was tab character)
- Line 8700: Added missing `*/` to close comment

### 4. Lead Dashboard Blank on Refresh
**Problem:** Auto-refresh every 30 seconds clearing page
**Solution:** Removed `setInterval`, added manual refresh button

### 5. TCPA Data Not Showing
**Problem:** Data nested in different JSON fields
**Solution:** Check multiple sources in order:
```php
$value = $lead->field 
    ?? $lead->meta['field'] 
    ?? $lead->payload['field'] 
    ?? 'Not provided';
```

---

## 📦 Deployment Configuration

### Docker Cache Busting
**File:** `Dockerfile.render`
```dockerfile
ARG CACHE_BUST=16
```

### Cache Clearing Commands
```bash
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
```

---

## 🔄 Webhook Endpoints

### Primary: `/api-webhook`
- No CSRF protection
- Handles nested data structure
- Generates 13-digit external IDs

### Secondary: `/webhook.php`
- Backup endpoint
- Same functionality

### Home/Auto Specific
- `/webhook/home`
- `/webhook/auto`

All endpoints save leads with:
- `tenant_id = 5`
- `external_lead_id` = 13-digit timestamp format
- Proper EST timestamps using `estNow()`

---

## 📝 Testing Commands

### Test Webhook
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/api-webhook \
  -H "Content-Type: application/json" \
  -d '{"test":"data"}'
```

### Check Lead Counts
```sql
SELECT source, COUNT(*) as count 
FROM leads 
GROUP BY source 
ORDER BY count DESC;
```

### View Recent Leads
```sql
SELECT id, name, created_at, source, vici_list_id 
FROM leads 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## ✅ Checklist for Future UI Changes

1. [ ] Update this documentation
2. [ ] Test all pages after changes
3. [ ] Verify data sources are correct
4. [ ] Check timezone handling
5. [ ] Test webhook endpoints
6. [ ] Clear all caches after deployment
7. [ ] Increment CACHE_BUST if needed
8. [ ] Verify mobile responsiveness
9. [ ] Test in iframe mode if applicable
10. [ ] Update CURRENT_STATE.md

---

## 🎯 Next Steps

1. **Lead Flow Configuration**
   - Finalize the reset schedule
   - Lock configuration when complete
   - Create matching SQL queries for Vici

2. **Navigation Update**
   - Add Lead Flow link to Vici dropdown
   - Ensure all links are working

3. **Data Validation**
   - Verify all TCPA fields populate correctly
   - Test lead flow progression
   - Monitor webhook data capture

---

This documentation represents the current state of the Brain UI as of January 15, 2025.
All changes have been tested and deployed to production.
**Last Updated:** January 15, 2025 - 10:30 AM EST

## 🎨 UI Structure Overview

### Top Navigation Bar
- **Color:** Blue gradient (#4A90E2) with white text
- **Logo:** QuotingFast white logo (https://quotingfast.com/whitelogo) 
- **Header Text:** "The Brain" (NOT "LQF")
- **Menu Structure:** LEADS | VICI | SMS | BUYER PORTAL | ADMIN
- **Layout:** Using `layouts/app.blade.php` as the main template

### Navigation Routes
```
/leads → Lead Dashboard
/vici → Vici Dashboard  
/sms → SMS Dashboard
/buyers → Buyer Portal
/admin → Admin Dashboard
```

---

## 📄 Page-by-Page Documentation

### 1. LEAD DASHBOARD (`/leads`)
**File:** `resources/views/leads/index-new.blade.php`

#### Header Stats Section
- **Default Period:** Today's numbers
- **Period Options:** Today | Yesterday | Last 7 Days | Last 30 Days | Custom (with date pickers)
- **Stats Display:** Total Leads, Today's Leads, Yesterday's Leads, This Week, This Month
- **Loading Indicator:** Shows "Loading..." when switching periods
- **Manual Refresh:** Blue refresh button in top-right of stats section

#### Lead Cards
- **Per-Page Selector:** Between search box and first lead card
- **Options:** 10, 25, 50, 100 leads per page
- **Lead Source Badge:** Shows vendor (e.g., "LQF" for LeadsQuotingFast)
- **NO Email Display:** Email removed from cards per user request
- **Vici Status:** Shows list ID if assigned
- **Action Buttons:** View | Edit | Payload

#### Data Sources
- Leads pulled from `leads` table
- Source field determines vendor badge
- Vici status from `vici_list_id` field

---

### 2. LEAD VIEW/EDIT PAGE (`/agent/lead/{id}`)
**File:** `resources/views/agent/lead-display.blade.php`

#### Header Section (Sticky)
- **Position:** Fixed to top when scrolling
- **Height:** Narrow (just enough space for info)
- **Content Order:**
  1. Back to Leads button (far left)
  2. Vendor badge
  3. Name
  4. Phone
  5. Address
  6. Email (above Lead ID)
  7. Lead ID (below email)
- **Action Buttons:** View Complete Payload | Save Lead (in header)

#### TCPA Compliance Section
**Data Sources (Check in order):**
1. Direct lead fields
2. `meta` JSON field
3. `payload` JSON field

**Fields Display:**
- Opt-in Date & Time (with TCPA status: X days remaining)
- TCPA Consent Text (hidden by default, "Show Text" button to reveal)
- TrustedForm Certificate URL (with copy button)
- LeadID/Jornaya Token (with copy button)
- IP Address (with copy button)
- Page URL (with copy button)
- Referring URL (with copy button)

**TCPA Days Calculation:**
```php
$daysOld = $lead->opt_in_date ? 
    Carbon::parse($lead->opt_in_date)->diffInDays(now()) : 90;
$daysRemaining = floor(90 - $daysOld);
```

#### Driver Details
**Years Licensed Calculation:**
- If `age_first_licensed` provided: Use that value
- Otherwise: Hard-code to 16

#### Vehicle Cards
**Required Fields:**
- Year, Make, Model, Trim
- VIN
- Primary Use
- Annual Mileage
- Comprehensive Deductible
- Collision Deductible
- Garage Type

#### Edit Mode
- Shows all 13 qualification questions at top
- Hides vendor/buyer section
- Back to Dashboard visible for admins only
- Hidden for iframe users

#### Iframe Detection
```javascript
const isInIframe = window.self !== window.top;
if (isInIframe) {
    // Hide navigation elements
}
```

---

### 3. VICI DASHBOARD (`/vici`)
**File:** `resources/views/vici/dashboard.blade.php`

#### Metrics Display
- Total Leads in Vici
- Leads Called Today
- Orphan Calls (unmatched)
- Average Call Time

#### Data Sources
- `vici_call_logs` table for call data
- `orphan_call_logs` table for unmatched calls
- Uses `matched_at` timestamp (NOT `matched` boolean)

#### Scopes Fixed
```php
// OrphanCallLog model
public function scopeUnmatched($query) {
    return $query->whereNull('matched_at');
}
```

---

### 4. VICI LEAD FLOW (`/vici/lead-flow`)
**File:** `resources/views/vici/lead-flow.blade.php`

#### Page Structure
- Visual flow chart: 101 → 102 → 103 → 104 → 105 → 106 → 107 → 108 → 110
- Each list shows as a card with editable fields

#### Editable Fields (Per List)
- Days in List
- List Resets/Day (not calls/day)
- Reset Times (e.g., "9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM")
- Description

#### Calculated Fields
- Total Calls = Days × Resets/Day
- Call Range (e.g., "3-14" for List 104)
- Current Lead Count (from database)

#### Edit Modes
1. **Unlocked (Default):** All fields editable with yellow background
2. **Locked:** Read-only mode for reference

#### Action Buttons
- 🔒 Lock Configuration / 🔓 Unlock for Editing
- 💾 Save All Changes
- 🔄 Recalculate Ranges
- 📊 Refresh Lead Counts

#### List Reset Schedule Using Vici's Reset Feature
| List | Days | Resets/Day | Reset Times | Total Calls |
|------|------|------------|-------------|-------------|
| 101 | 0 | 0 | None | 0 |
| 102 | 1 | 1 | 9:00 AM | 1 |
| 103 | 1 | 1 | 2:00 PM | 1 |
| 104 | 3 | 4 | 9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM | 12 |
| 105 | 5 | 2 | 10:00 AM, 3:00 PM | 10 |
| 106 | 7 | 1 | 11:00 AM | 7 |
| 107 | 14 | 0.5 | Mon/Wed/Fri 10:00 AM | 7 |
| 108 | 14 | 0.25 | Tue/Thu 2:00 PM | 3-4 |
| 110 | ∞ | 0 | None | Archive |

---

### 5. STUCK IN QUEUE (`/admin/lead-queue`)
**File:** `resources/views/admin/lead-queue.blade.php`

#### Purpose
Shows leads that haven't been processed by Vici

#### Table Features
- Horizontal scrolling for small screens
- Date column added
- Lead Detail button opens modal

#### Data Source
- Leads with `vici_list_id = 0` or NULL
- Ordered by `created_at DESC`

---

### 6. CAMPAIGNS (`/campaigns/directory`)
**File:** `resources/views/campaigns/directory.blade.php`

#### Features
- List all campaigns
- Delete button with confirmation
- Prevents deletion if campaign has leads

---

## 🔧 Common UI Components

### Date/Time Display
- **Timezone:** America/New_York (EST/EDT)
- **Helper Function:** `estNow()` instead of `now()`
- **Display Format:** "Jan 15, 2025 10:30 AM EST"

### Copy Buttons
```html
<button onclick="copyToClipboard('text-to-copy')" class="copy-btn">
    📋
</button>
```

### Loading States
```javascript
element.textContent = 'Loading...';
// After data loads
element.textContent = data.value;
```

### Modal Implementation
```javascript
function showModal(content) {
    const modal = document.getElementById('modal');
    modal.querySelector('.modal-content').innerHTML = content;
    modal.style.display = 'block';
}
```

---

## 🚨 Critical Issues Fixed Today

### 1. Vici 500 Error
**Problem:** `column "matched" does not exist`
**Solution:** Use `matched_at` timestamp field instead

### 2. Webhooks Not Saving Leads
**Problem:** Routes were commented out and missing `Lead::create()`
**Solution:** Uncommented routes and added:
```php
\App\Models\Lead::create($leadData);
```

### 3. Deployment Syntax Errors
**Fixed in routes/web.php:**
- Line 3: `use App\Helpers\timezone;` (was tab character)
- Line 8700: Added missing `*/` to close comment

### 4. Lead Dashboard Blank on Refresh
**Problem:** Auto-refresh every 30 seconds clearing page
**Solution:** Removed `setInterval`, added manual refresh button

### 5. TCPA Data Not Showing
**Problem:** Data nested in different JSON fields
**Solution:** Check multiple sources in order:
```php
$value = $lead->field 
    ?? $lead->meta['field'] 
    ?? $lead->payload['field'] 
    ?? 'Not provided';
```

---

## 📦 Deployment Configuration

### Docker Cache Busting
**File:** `Dockerfile.render`
```dockerfile
ARG CACHE_BUST=16
```

### Cache Clearing Commands
```bash
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
```

---

## 🔄 Webhook Endpoints

### Primary: `/api-webhook`
- No CSRF protection
- Handles nested data structure
- Generates 13-digit external IDs

### Secondary: `/webhook.php`
- Backup endpoint
- Same functionality

### Home/Auto Specific
- `/webhook/home`
- `/webhook/auto`

All endpoints save leads with:
- `tenant_id = 5`
- `external_lead_id` = 13-digit timestamp format
- Proper EST timestamps using `estNow()`

---

## 📝 Testing Commands

### Test Webhook
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/api-webhook \
  -H "Content-Type: application/json" \
  -d '{"test":"data"}'
```

### Check Lead Counts
```sql
SELECT source, COUNT(*) as count 
FROM leads 
GROUP BY source 
ORDER BY count DESC;
```

### View Recent Leads
```sql
SELECT id, name, created_at, source, vici_list_id 
FROM leads 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## ✅ Checklist for Future UI Changes

1. [ ] Update this documentation
2. [ ] Test all pages after changes
3. [ ] Verify data sources are correct
4. [ ] Check timezone handling
5. [ ] Test webhook endpoints
6. [ ] Clear all caches after deployment
7. [ ] Increment CACHE_BUST if needed
8. [ ] Verify mobile responsiveness
9. [ ] Test in iframe mode if applicable
10. [ ] Update CURRENT_STATE.md

---

## 🎯 Next Steps

1. **Lead Flow Configuration**
   - Finalize the reset schedule
   - Lock configuration when complete
   - Create matching SQL queries for Vici

2. **Navigation Update**
   - Add Lead Flow link to Vici dropdown
   - Ensure all links are working

3. **Data Validation**
   - Verify all TCPA fields populate correctly
   - Test lead flow progression
   - Monitor webhook data capture

---

This documentation represents the current state of the Brain UI as of January 15, 2025.
All changes have been tested and deployed to production.




