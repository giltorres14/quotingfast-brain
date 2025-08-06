# API Integrations - Brain System

## **Directory Structure**

This directory contains all API integration documentation, scripts, and configurations organized by company/service.

```
api-integrations/
├── README.md                    # This file - overview of all integrations
├── general/                     # General integration guides and templates
│   ├── integration-template.md  # Template for new integrations
│   └── best-practices.md       # Common best practices
├── ringba/                     # RingBA call tracking & routing
│   ├── README.md               # RingBA integration overview
│   ├── parameter-management.md # URL parameter setup & management
│   ├── scripts/               # Automation scripts
│   └── configurations/        # Config files & examples
├── allstate/                  # Allstate Lead Marketplace
│   ├── README.md              # Allstate integration overview
│   ├── api-documentation.md   # API specs & field mappings
│   ├── testing-setup.md       # Testing dashboard & tools
│   └── configurations/        # Config files & examples
├── vici/                      # Vici Dialer System
│   ├── README.md              # Vici integration overview
│   ├── api-documentation.md   # API specs & configuration
│   └── configurations/        # Config files & examples
└── [future-buyers]/           # Additional buyers as they're added
    ├── README.md
    ├── api-documentation.md
    └── configurations/
```

## **Current Integrations**

### **🎯 RingBA** - Call Tracking & Lead Routing
- **Status**: ✅ Active
- **Purpose**: URL parameter management for lead tracking and routing
- **Parameters**: 95 total URL parameters configured
- **Documentation**: `./ringba/`

### **🏢 Allstate** - Lead Marketplace API
- **Status**: 🧪 Testing Phase
- **Purpose**: Direct lead transfer to Allstate for auto insurance quotes
- **API Version**: v2.11.266
- **Documentation**: `./allstate/`

### **☎️ Vici** - Dialer System
- **Status**: 🔄 Temporarily Bypassed (for Allstate testing)
- **Purpose**: Agent dialer system for lead qualification
- **List ID**: 101 (configured)
- **Documentation**: `./vici/`

## **Integration Status Dashboard**

| Company | Service | Status | Last Updated | Success Rate | Notes |
|---------|---------|--------|--------------|--------------|-------|
| RingBA | Call Tracking | ✅ Active | Aug 2025 | N/A | 95 parameters configured |
| Allstate | Lead API | 🧪 Testing | Aug 2025 | TBD | Testing dashboard active |
| Vici | Dialer | 🔄 Bypassed | Aug 2025 | N/A | Temporarily disabled |
| LeadsQuotingFast | Webhook | ✅ Active | Ongoing | TBD | Primary lead source |

## **Quick Start Guide**

### **Adding a New Integration**

1. **Create Company Directory**:
   ```bash
   mkdir -p api-integrations/new-company/{scripts,configurations}
   ```

2. **Copy Template Files**:
   ```bash
   cp api-integrations/general/integration-template.md api-integrations/new-company/README.md
   ```

3. **Follow Setup Process**:
   - Analyze API requirements
   - Create RingBA parameters (if needed)
   - Implement Brain service class
   - Create testing dashboard
   - Update this documentation

4. **Update Integration Status**:
   - Add to the status table above
   - Update total integration count
   - Document any dependencies

### **Common Tasks**

- **RingBA Parameter Management**: See `./ringba/parameter-management.md`
- **API Testing**: Each integration has its own testing setup
- **Configuration Management**: Check `./[company]/configurations/`
- **Troubleshooting**: Check individual company README files

## **Development Standards**

### **File Naming Convention**
- `README.md` - Integration overview
- `api-documentation.md` - Technical API specs
- `testing-setup.md` - Testing tools and dashboards
- `configurations/` - Config files and examples
- `scripts/` - Automation tools

### **Documentation Requirements**
Each integration must include:
- ✅ API endpoint documentation
- ✅ Authentication details
- ✅ Field mapping specifications
- ✅ Error handling procedures
- ✅ Testing methodology
- ✅ Success metrics tracking

### **Code Organization**
- Service classes: `app/Services/[Company]CallTransferService.php`
- Testing services: `app/Services/[Company]TestingService.php`
- Models: `app/Models/[Company]TestLog.php`
- Views: `resources/views/admin/[company]-testing.blade.php`

## **Support & Maintenance**

### **Monthly Review Checklist**
- [ ] Review all integration success rates
- [ ] Update API documentation for any changes
- [ ] Check for deprecated endpoints or methods
- [ ] Verify authentication tokens/keys
- [ ] Update parameter lists and configurations

### **Emergency Contacts**
- **RingBA Support**: [Add contact info]
- **Allstate API Support**: [Add contact info]
- **Vici Support**: [Add contact info]

---

## **Recent Changes**

### **August 2025**
- ✅ Reorganized API documentation by company
- ✅ Created RingBA parameter management system (95 parameters)
- ✅ Implemented Allstate testing integration
- ✅ Temporarily bypassed Vici for Allstate testing
- ✅ Created comprehensive testing dashboards

---

*Last Updated: August 6, 2025*
*Total Integrations: 4 (RingBA, Allstate, Vici, LeadsQuotingFast)*
