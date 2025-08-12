# 📊 LQF TO BRAIN FIELD MAPPING DOCUMENTATION
*Complete guide to how LeadsQuotingFast fields map to Brain database*

---

## 🎯 OVERVIEW

This document details how fields from LeadsQuotingFast (LQF) webhook payloads are mapped to the Brain database, including the new vendor/buyer tracking fields added for comprehensive lead management.

---

## 📦 NEW FIELDS ADDED TO BRAIN

### Database Migration: `2024_12_20_add_vendor_and_business_fields_to_leads.php`

```sql
-- Vendor Information
vendor_name         VARCHAR(255)  -- Name of lead vendor
vendor_campaign     VARCHAR(255)  -- Vendor's campaign ID/name  
cost                DECIMAL(10,2) -- What we paid for the lead

-- Buyer Information  
buyer_name          VARCHAR(255)  -- Name of lead buyer
buyer_campaign      VARCHAR(255)  -- Buyer's campaign ID/name
sell_price          DECIMAL(10,2) -- What we sell the lead for

-- TCPA Compliance
tcpa_lead_id        VARCHAR(255)  -- TCPA tracking ID
trusted_form_cert   VARCHAR(255)  -- TrustedForm certificate URL
tcpa_compliant      BOOLEAN       -- TCPA compliance flag
```

---

## 🔄 LQF WEBHOOK FIELD MAPPING

### Location: `brain/routes/web.php` (Line ~1790-1810)

```php
// Vendor Information (from LQF payload)
'vendor_name' => $data['vendor'] ?? $data['vendor_name'] ?? null,
'vendor_campaign' => $data['vendor_campaign'] ?? $data['vendor_campaign_id'] ?? null,
'cost' => $data['cost'] ?? $data['lead_cost'] ?? null,

// Buyer Information (from LQF payload)
'buyer_name' => $data['buyer'] ?? $data['buyer_name'] ?? null,
'buyer_campaign' => $data['buyer_campaign'] ?? $data['buyer_campaign_id'] ?? null,
'sell_price' => $data['sell_price'] ?? $data['revenue'] ?? null,

// TCPA Compliance (from LQF payload)
'tcpa_lead_id' => $data['tcpa_lead_id'] ?? $data['lead_id'] ?? null,
'trusted_form_cert' => $data['trusted_form_cert'] ?? $data['trusted_form_cert_url'] ?? null,
'tcpa_compliant' => $data['tcpa_compliant'] ?? $data['tcpa'] ?? $data['meta']['tcpa_compliant'] ?? false,
```

### Field Mapping Table

| **LQF Field(s)** | **Brain Field** | **Type** | **Description** |
|------------------|-----------------|----------|-----------------|
| `vendor`, `vendor_name` | `vendor_name` | string | Lead vendor/source name |
| `vendor_campaign`, `vendor_campaign_id` | `vendor_campaign` | string | Vendor campaign identifier |
| `cost`, `lead_cost` | `cost` | decimal | Cost of lead acquisition |
| `buyer`, `buyer_name` | `buyer_name` | string | Lead buyer name |
| `buyer_campaign`, `buyer_campaign_id` | `buyer_campaign` | string | Buyer campaign identifier |
| `sell_price`, `revenue` | `sell_price` | decimal | Lead selling price |
| `tcpa_lead_id`, `lead_id` | `tcpa_lead_id` | string | TCPA tracking ID |
| `trusted_form_cert`, `trusted_form_cert_url` | `trusted_form_cert` | string | TrustedForm certificate |
| `tcpa_compliant`, `tcpa`, `meta.tcpa_compliant` | `tcpa_compliant` | boolean | TCPA compliance status |

---

## 🤖 AUTO-CREATION OF VENDORS & BUYERS

### Location: `brain/routes/web.php` (Line ~1828-1858)

When a lead comes in with vendor or buyer information, the system automatically:

1. **Creates Vendor if doesn't exist**:
```php
$vendor = \App\Models\Vendor::firstOrCreate(
    ['name' => $leadData['vendor_name']],
    ['campaigns' => [], 'active' => true]
);
```

2. **Creates Buyer if doesn't exist**:
```php
$buyer = \App\Models\Buyer::firstOrCreate(
    ['name' => $leadData['buyer_name']],
    ['campaigns' => [], 'active' => true]
);
```

3. **Adds campaigns to their lists**:
- Vendor campaigns are tracked for cost analysis
- Buyer campaigns are tracked for revenue analysis

---

## 📊 VENDOR & BUYER MODELS

### Vendor Model (`app/Models/Vendor.php`)
- Tracks all vendors we buy leads from
- Maintains campaign list
- Calculates total leads and total cost
- Relationship: `hasMany(Lead::class, 'vendor_name', 'name')`

### Buyer Model (`app/Models/Buyer.php`)
- Tracks all buyers we sell leads to
- Maintains campaign list  
- Calculates total leads and total revenue
- Relationship: `hasMany(Lead::class, 'buyer_name', 'name')`
- Note: Existing complex buyer system for accounts remains separate

---

## 📈 BUSINESS INTELLIGENCE FEATURES

With these new fields, Brain can now:

1. **Track Lead Economics**:
   - Cost per lead by vendor
   - Revenue per lead by buyer
   - Profit margins (sell_price - cost)
   - ROI by campaign

2. **Vendor Performance**:
   - Which vendors provide best quality leads
   - Cost trends over time
   - Campaign effectiveness

3. **Buyer Analytics**:
   - Which buyers generate most revenue
   - Return rates by buyer
   - Campaign performance

4. **TCPA Compliance**:
   - Track consent status
   - Store TrustedForm certificates
   - Maintain audit trail

---

## 🔍 EXAMPLE LQF PAYLOAD

```json
{
  "contact": {
    "first_name": "John",
    "last_name": "Doe",
    "phone": "5551234567",
    "email": "john@example.com",
    "address": "123 Main St",
    "city": "Columbus",
    "state": "OH",
    "zip_code": "43215"
  },
  "vendor": "Quinn Street",
  "vendor_campaign": "QS_Auto_2024",
  "cost": 5.50,
  "buyer": "Allstate",
  "buyer_campaign": "AS_Ohio_Auto",
  "sell_price": 25.00,
  "tcpa_lead_id": "QS123456789",
  "trusted_form_cert": "https://cert.trustedform.com/abc123",
  "tcpa_compliant": true,
  "data": {
    "drivers": [...],
    "vehicles": [...],
    "current_policy": {...}
  }
}
```

---

## 🚀 DEPLOYMENT STEPS

1. **Run migrations**:
```bash
php artisan migrate
```

2. **Clear caches**:
```bash
php artisan config:clear
php artisan cache:clear
```

3. **Test webhook**:
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {"phone": "5551234567"},
    "vendor": "Test Vendor",
    "cost": 10.00,
    "buyer": "Test Buyer",
    "sell_price": 30.00
  }'
```

---

## 📝 NOTES

- All vendor/buyer data is auto-created on first occurrence
- Campaigns are automatically added to vendor/buyer records
- Statistics are updated in real-time
- TCPA compliance is tracked for every lead
- Profit margins can be calculated as: `sell_price - cost`

---

*Last Updated: December 20, 2024*

