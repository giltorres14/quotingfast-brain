# Vici Brain Lead ID Synchronization

## Overview
This document describes how Brain's 13-digit Lead IDs are synchronized with Vici's lead system.

## Background
- **Brain Lead ID**: A 13-digit unique identifier (e.g., `1755041041000`) generated using timestamp + sequence
- **Vici vendor_lead_code**: Field in Vici's vicidial_list table that can store external reference IDs
- **Problem**: Existing Vici leads didn't have Brain's Lead IDs, making cross-system tracking difficult

## Solution Implementation

### 1. Modified ViciDialerService
Updated `brain/app/Services/ViciDialerService.php` to:
- Use `external_lead_id` (13-digit) instead of auto-increment `id`
- Store Brain Lead ID in Vici's `vendor_lead_code` field
- Update `source_id` to include Brain Lead ID for clarity

### 2. New Update Function
Added `updateViciLeadWithBrainId()` method that:
- Finds existing Vici lead by phone number
- Updates `vendor_lead_code` with Brain's 13-digit ID
- Updates `source_id` to `BRAIN_[13-digit-id]`
- Preserves original comments while adding Brain ID reference

### 3. Artisan Command
Created `php artisan vici:update-brain-ids` command:
```bash
# Update all leads
php artisan vici:update-brain-ids

# Test mode (only 10 leads)
php artisan vici:update-brain-ids --test

# Update specific phone
php artisan vici:update-brain-ids --phone=2155551234

# Custom batch size (default 100)
php artisan vici:update-brain-ids --batch=500
```

## Field Mapping

| Brain Field | Vici Field | Format | Example |
|------------|------------|--------|---------|
| external_lead_id | vendor_lead_code | 13-digit number | 1755041041000 |
| "BRAIN_" + external_lead_id | source_id | String | BRAIN_1755041041000 |
| Lead details | comments | Text | Brain Lead ID: 1755041041000 \| ... |

## How It Works

### For New Leads (Going Forward)
When pushing new leads to Vici, the system will:
1. Ensure lead has a 13-digit `external_lead_id`
2. Store this ID in Vici's `vendor_lead_code`
3. Set `source_id` to `BRAIN_[id]` for easy identification

### For Existing Leads (Bulk Update)
The update command will:
1. Query all Brain leads
2. For each lead, find matching Vici record by phone
3. Update Vici's `vendor_lead_code` with Brain's ID
4. Log results (updated/not found/failed)

## Benefits
1. **Consistent Tracking**: Same Lead ID across both systems
2. **Easy Lookup**: Can find leads by Brain ID in Vici
3. **Audit Trail**: Comments show when Brain ID was added
4. **No Data Loss**: Original Vici data preserved

## Important Notes
- The 13-digit format is CRITICAL - do not change to 9 digits
- Format: Unix timestamp (10 digits) + sequence (3 digits)
- Example: 1755041041000 = timestamp 1755041041 + sequence 000

## Testing
Before running on production:
1. Test with single phone: `php artisan vici:update-brain-ids --test --phone=XXX`
2. Test with 10 leads: `php artisan vici:update-brain-ids --test`
3. Review logs in `storage/logs/laravel.log`

## Monitoring
Check progress in real-time:
- Command shows progress every 10 leads
- Final summary shows total updated/failed/not found
- All actions logged to Laravel log file

## Rollback
If needed, the original `vendor_lead_code` values are preserved in:
- Vici comments field (shows original value)
- Laravel logs (shows old_vendor_code)


