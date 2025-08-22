# Current State - Lead Sync Project
**Last Updated: August 21, 2025, 7:40 PM EST**

## 🔴 PAUSED: Waiting for LQF Bulk File

### Current Situation

#### ViciDial Database (Confirmed via Proxy)
- **101,915 total leads** in lists 6018-6026
  - List 6018: 5,893 leads
  - List 6019: 5,813 leads  
  - List 6020: 14,772 leads
  - List 6021: 12,197 leads
  - List 6022: 13,500 leads
  - List 6023: 9,547 leads
  - List 6024: 23,774 leads
  - List 6025: 14,844 leads
  - List 6026: 1,575 leads

#### Brain Database (Current)
- **242,243 total leads**
  - List 0: 227,715 leads (LQF bulk import - MISSING vehicle/driver data)
  - List NULL: 3,396 leads
  - List 101: 11,080 leads (Test A)
  - Lists 6018-6026: Only 52 leads (not synced)

### The Problem
1. **227,715 leads in Brain List 0** were imported from LQF but are missing critical data:
   - ❌ Vehicle information
   - ❌ Driver details
   - ❌ Policy information
   - ❌ Opt-in/TCPA data
   - ❌ Trusted Form certificates

2. **These leads exist in ViciDial** (101,915 in lists 6018-6026) but aren't matched

3. **Original LQF export had all data** in a lumped/JSON column that needs proper parsing

### What We're Waiting For
- **New LQF Bulk Export File** with complete data including:
  - Phone numbers, names, addresses
  - Vehicle details (year, make, model, VIN)
  - Driver information (all drivers)
  - Current policy details
  - Opt-in timestamps and methods
  - Trusted Form certificates
  - All data that was in the lumped/JSON column

### Tools Ready to Use

#### 1. Sync Scripts Created
- `sync_vici_via_proxy.php` - Syncs ViciDial with Brain using Render proxy
- `sync_vici_direct.php` - Direct Brain database operations
- `vici_stats.php` - Check current statistics

#### 2. Access Methods Confirmed
- ✅ **ViciDial via Proxy**: https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute
- ✅ **Brain Database**: PostgreSQL connection working
- ✅ **ViciDial Database**: Q6hdjl67GRigMofv (via proxy)

### Next Steps (When File Arrives)

1. **Import LQF Bulk File**
   ```bash
   # File will be in ~/Downloads/
   # Parse the lumped JSON column
   # Import with full data preservation
   ```

2. **Match with ViciDial**
   - Match by phone number
   - Assign to correct lists (6018-6026)
   - Preserve all vehicle/driver/policy data

3. **Update Brain Database**
   - Move from List 0 to proper lists
   - Add external_lead_ids
   - Maintain JSON data in appropriate columns

4. **Verify Sync**
   - Check lead counts match ViciDial
   - Verify data integrity
   - Export for ViciDial if needed

### Important Notes

- **DO NOT** push incomplete Brain data to ViciDial
- **DO NOT** lose the vehicle/driver/policy information
- **The lumped column** contains JSON or concatenated data that must be parsed
- **List 0 leads** are the LQF bulk import that need proper processing

### Files and Documentation
- `/brain/VICIDIAL_SYNC_DOCUMENTATION.md` - Complete sync documentation
- `/brain/SYNC_BREAKDOWN.md` - Detailed breakdown of sync process
- `/brain/sync_vici_via_proxy.php` - Main sync script
- `/brain/vici_stats.php` - Statistics checker

### Database Connections
```php
// Brain Database
Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
Database: brain_production
User: brain_user
Pass: KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ

// ViciDial (via proxy)
Database: Q6hdjl67GRigMofv
Tables: vicidial_list, vicidial_lists
```

### Web UI Status
- ✅ Admin Dashboard: Fixed and working
- ✅ Vici Dashboard: Working
- ✅ Command Center: Working
- ✅ Lead Flow: Working
- ✅ Reports: Working

## 📝 TO RESUME:
When the LQF Bulk file arrives:
1. Check filename in Downloads
2. Run import script with JSON parsing
3. Match with ViciDial data
4. Verify all data preserved
5. Update this document with results

