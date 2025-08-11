# Multi-Tenancy Implementation Plan for The Brain Platform
## Making the System Resellable to Other Companies

Created: January 2025
Status: Planning Phase

---

## 🎯 **OBJECTIVE**
Transform The Brain from a single-company system into a white-label, multi-tenant SaaS platform that can be resold to other auto insurance lead generation companies.

---

## 📊 **CURRENT STATE ANALYSIS**

### Existing Infrastructure
- **Database**: PostgreSQL on Render (supports Row Level Security)
- **Framework**: Laravel (has built-in multi-tenancy packages available)
- **Parcelvoy**: Already has `organizations` and `projects` structure
- **Authentication**: Basic Laravel auth (needs tenant awareness)

### Current Single-Tenant Elements
- All leads stored in single `leads` table without tenant isolation
- API keys hardcoded in config files
- Single set of RingBA/Allstate/Vici credentials
- No billing or subscription management
- Single branded UI (QuotingFast)

---

## 🏗️ **PHASE 1: DATABASE MULTI-TENANCY** (Week 1-2)

### 1.1 Add Tenant Tables
```sql
-- Create tenants table
CREATE TABLE tenants (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255),
    settings JSONB DEFAULT '{}',
    status VARCHAR(50) DEFAULT 'active',
    trial_ends_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Create tenant_users table
CREATE TABLE tenant_users (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) DEFAULT 'member',
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(tenant_id, user_id)
);
```

### 1.2 Add tenant_id to All Tables
```php
// Migration to add tenant_id to existing tables
Schema::table('leads', function (Blueprint $table) {
    $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
    $table->index('tenant_id');
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});

// Repeat for: campaigns, webhooks, api_logs, test_logs, etc.
```

### 1.3 Implement Row Level Security (RLS)
```sql
-- Enable RLS on leads table
ALTER TABLE leads ENABLE ROW LEVEL SECURITY;

-- Create policy for tenant isolation
CREATE POLICY tenant_isolation ON leads
    FOR ALL
    USING (tenant_id = current_setting('app.current_tenant_id')::BIGINT);
```

---

## 🔐 **PHASE 2: AUTHENTICATION & MIDDLEWARE** (Week 2-3)

### 2.1 Tenant-Aware Middleware
```php
// app/Http/Middleware/TenantMiddleware.php
class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        // Identify tenant from subdomain, domain, or header
        $tenant = $this->identifyTenant($request);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        // Set tenant context
        app()->singleton('tenant', fn() => $tenant);
        config(['database.connections.pgsql.search_path' => "tenant_{$tenant->id},public"]);
        
        return $next($request);
    }
}
```

### 2.2 Tenant Identification Methods
- **Subdomain**: `acme.brain.quotingfast.com`
- **Custom Domain**: `leads.acmeinsurance.com`
- **API Header**: `X-Tenant-ID: acme`
- **JWT Claims**: Include tenant_id in token

---

## 🎨 **PHASE 3: WHITE-LABELING** (Week 3-4)

### 3.1 Customizable Elements
```json
// tenant.settings JSON structure
{
    "branding": {
        "logo_url": "https://...",
        "favicon_url": "https://...",
        "company_name": "ACME Insurance Leads",
        "primary_color": "#4f46e5",
        "secondary_color": "#764ba2",
        "font_family": "Inter"
    },
    "features": {
        "vici_integration": true,
        "ringba_integration": true,
        "allstate_api": false,
        "sms_center": true,
        "max_leads_per_month": 10000
    },
    "email": {
        "from_name": "ACME Leads",
        "from_email": "leads@acme.com",
        "smtp_settings": {...}
    }
}
```

### 3.2 Dynamic Theme Loading
```php
// resources/views/layouts/app.blade.php
<style>
    :root {
        --primary-color: {{ $tenant->getSetting('branding.primary_color', '#4f46e5') }};
        --secondary-color: {{ $tenant->getSetting('branding.secondary_color', '#764ba2') }};
        --font-family: {{ $tenant->getSetting('branding.font_family', 'Inter') }};
    }
</style>
```

---

## 🔌 **PHASE 4: API & INTEGRATION ISOLATION** (Week 4-5)

### 4.1 Tenant-Specific API Credentials
```php
// app/Models/TenantIntegration.php
class TenantIntegration extends Model
{
    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array'
    ];
    
    // Store encrypted: RingBA keys, Allstate tokens, Vici credentials
}
```

### 4.2 Dynamic Service Provider
```php
// app/Services/TenantAwareRingBAService.php
class TenantAwareRingBAService
{
    public function __construct()
    {
        $tenant = app('tenant');
        $integration = $tenant->integrations()->where('type', 'ringba')->first();
        
        $this->accountId = $integration->credentials['account_id'];
        $this->apiToken = $integration->credentials['api_token'];
    }
}
```

---

## 💰 **PHASE 5: BILLING & SUBSCRIPTIONS** (Week 5-6)

### 5.1 Subscription Plans
```php
// database/migrations/create_subscription_plans_table.php
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Starter, Professional, Enterprise
    $table->decimal('monthly_price', 10, 2);
    $table->integer('included_leads');
    $table->decimal('overage_rate', 8, 4); // Per lead over limit
    $table->json('features');
    $table->timestamps();
});
```

### 5.2 Usage Tracking
```php
// Track leads per tenant per month
Schema::create('tenant_usage', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();
    $table->string('metric'); // leads_processed, api_calls, sms_sent
    $table->integer('count');
    $table->date('period');
    $table->timestamps();
    
    $table->unique(['tenant_id', 'metric', 'period']);
});
```

### 5.3 Payment Integration
- **Stripe**: For credit card processing
- **Usage-based billing**: Track leads, API calls, SMS
- **Invoicing**: Automated monthly invoices
- **Payment methods**: Credit card, ACH, NET terms

---

## 🛠️ **PHASE 6: TENANT MANAGEMENT PORTAL** (Week 6-7)

### 6.1 Super Admin Dashboard
```
/super-admin
├── /tenants           # List all tenants
├── /tenants/create    # Onboard new tenant
├── /tenants/{id}      # Tenant details & settings
├── /billing           # Revenue reports
├── /usage             # Platform-wide usage stats
└── /settings          # Platform configuration
```

### 6.2 Tenant Admin Features
```
/admin (tenant-specific)
├── /settings          # Company settings & branding
├── /users             # Manage tenant users
├── /integrations      # API keys & webhooks
├── /billing           # Subscription & invoices
├── /usage             # Lead usage & limits
└── /api-keys          # Generate tenant API keys
```

---

## 📚 **PHASE 7: DOCUMENTATION & ONBOARDING** (Week 7-8)

### 7.1 Tenant Onboarding Flow
1. **Sign Up**: Collect company info, choose plan
2. **Domain Setup**: Configure subdomain or custom domain
3. **Branding**: Upload logo, set colors
4. **Integrations**: Connect RingBA, Vici, etc.
5. **Import Data**: Bulk import existing leads
6. **Training**: Video tutorials, documentation
7. **Go Live**: Launch with support

### 7.2 API Documentation
- Tenant-specific API endpoints
- Authentication methods
- Rate limiting per tenant
- Webhook management
- SDK examples (PHP, Python, Node.js)

---

## 🚀 **IMPLEMENTATION CHECKLIST**

### Immediate Actions (This Week)
- [ ] Create tenant migration files
- [ ] Add tenant_id to leads table
- [ ] Implement basic tenant middleware
- [ ] Create tenant seeder for testing

### Next Steps (Next 2 Weeks)
- [ ] Build tenant management CRUD
- [ ] Implement subdomain routing
- [ ] Add white-label settings
- [ ] Create billing tables

### Testing Requirements
- [ ] Unit tests for tenant isolation
- [ ] Integration tests for multi-tenant API
- [ ] Load testing with multiple tenants
- [ ] Security audit for data isolation

---

## 💡 **KEY CONSIDERATIONS**

### Security
- Strict data isolation between tenants
- Encrypted storage of API credentials
- Regular security audits
- GDPR/CCPA compliance per tenant

### Scalability
- Database partitioning by tenant_id
- Caching strategy per tenant
- Queue workers per tenant for large operations
- CDN for tenant assets

### Pricing Model Options
1. **Per Lead**: $0.50 - $2.00 per lead processed
2. **Monthly Subscription**: $500 - $5000/month with lead limits
3. **Revenue Share**: 5-10% of leads sold
4. **Hybrid**: Base fee + usage charges

### Migration Strategy
1. Create default tenant for existing data
2. Assign all current data to QuotingFast tenant
3. Test with second demo tenant
4. Gradual rollout to new customers

---

## 📈 **SUCCESS METRICS**

- **Technical**: < 100ms tenant context switching
- **Business**: 10+ tenants in first 6 months
- **Revenue**: $10K MRR within 3 months
- **Support**: < 2 hour onboarding time
- **Uptime**: 99.9% SLA per tenant

---

## 🔄 **MAINTENANCE & UPDATES**

### Regular Tasks
- Monitor tenant usage and limits
- Update white-label assets
- Rotate API keys
- Backup tenant data separately
- Review security logs

### Feature Rollout
- Feature flags per tenant
- Gradual rollout capability
- A/B testing within tenants
- Tenant-specific customizations

---

## 📞 **SUPPORT STRUCTURE**

### Tiers
1. **Self-Service**: Documentation, videos
2. **Standard**: Email support, 24hr response
3. **Premium**: Phone support, 2hr response
4. **Enterprise**: Dedicated account manager

### Tools Needed
- Helpdesk system (Zendesk/Freshdesk)
- Status page per tenant
- Monitoring & alerting (Datadog/New Relic)
- Error tracking (Sentry)

---

## ✅ **READY FOR IMPLEMENTATION**

This plan provides a complete roadmap to transform The Brain into a multi-tenant, resellable platform. Each phase builds on the previous one, allowing for incremental development and testing.

**Estimated Timeline**: 8 weeks for full implementation
**Estimated Cost**: Development time + $500-1000/month for additional infrastructure

**Next Step**: Begin with Phase 1 - Database Multi-Tenancy

