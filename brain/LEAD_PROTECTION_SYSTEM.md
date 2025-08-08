# Lead Protection System - Never Lose a Lead

## The Problem
When The Brain is being deployed or experiencing downtime:
- Webhooks fail with timeout errors
- Leads are lost forever
- No retry mechanism exists
- Deployment takes 30-60 seconds

## The Solutions

### 1. **Failsafe Webhook Endpoint** (Implemented)
**Endpoint**: `/webhook-failsafe.php`

This endpoint:
- **ALWAYS** returns 200 OK immediately
- Queues the lead for later processing
- Prevents webhook timeout failures
- Works even during high load

**Usage**:
```bash
# Configure LeadsQuotingFast to use this endpoint during deployments:
https://brain-api.onrender.com/webhook-failsafe.php
```

### 2. **Lead Queue System**
**Database Table**: `lead_queue`

Stores leads temporarily with:
- Full payload (JSON)
- Status tracking (pending/processing/completed/failed)
- Retry attempts counter
- Error logging

**Process Queue Command**:
```bash
php artisan leads:process-queue
```

### 3. **Recommended Setup**

#### Option A: Use Failsafe During Deployments
1. Before deploying, switch webhook URL to `/webhook-failsafe.php`
2. Deploy the application
3. After deployment, run: `php artisan leads:process-queue`
4. Switch back to main webhook `/webhook.php`

#### Option B: Always Use Failsafe (Most Reliable)
1. Configure all webhooks to use `/webhook-failsafe.php`
2. Set up a cron job to process queue every minute:
   ```bash
   * * * * * cd /path/to/brain && php artisan leads:process-queue
   ```

#### Option C: External Queue Service
Use a service like:
- **Webhook.site** - Free webhook capture
- **Pipedream** - Webhook workflows with retry
- **AWS SQS** - Message queue service
- **Redis Queue** - In-memory queue

### 4. **Migration to Run**
```bash
# Create the lead_queue table
php artisan migrate --path=database/migrations/2025_08_07_create_lead_queue_table.php
```

### 5. **Monitoring**

Check queue status:
```sql
-- See pending leads
SELECT * FROM lead_queue WHERE status = 'pending';

-- See failed leads
SELECT * FROM lead_queue WHERE status = 'failed';

-- Count by status
SELECT status, COUNT(*) FROM lead_queue GROUP BY status;
```

### 6. **Manual Recovery**

If leads are stuck in queue:
```bash
# Process all pending leads
php artisan leads:process-queue

# Or manually in tinker
php artisan tinker
>>> $queued = \App\Models\LeadQueue::pending()->get();
>>> foreach($queued as $q) { /* process */ }
```

## Best Practices

1. **During Deployment**:
   - Use blue-green deployment if possible
   - Switch to failsafe webhook before deploying
   - Process queue after deployment

2. **For High Availability**:
   - Always use the failsafe endpoint
   - Process queue every minute via cron
   - Monitor queue size

3. **Backup Strategy**:
   - Configure webhook provider to retry 3x
   - Log all incoming requests
   - Use external webhook capture service

## Emergency Recovery

If leads were lost:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Render logs for webhook attempts
3. Contact webhook provider for retry
4. Check lead_queue table for unprocessed items

## Implementation Status

✅ **Completed**:
- Failsafe webhook endpoint
- Lead queue model and migration
- Queue processing command

⏳ **TODO**:
- Set up cron job for automatic processing
- Add queue monitoring dashboard
- Implement email alerts for queue failures

---

*Never lose another lead during deployment!*



