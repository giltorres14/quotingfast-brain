# 🚨 WEBHOOK TESTING PROTOCOL

## CRITICAL REQUIREMENT
**ALWAYS TEST WEBHOOKS AFTER ANY CHANGE** - This is non-negotiable!

## Why This Matters
On January 8, 2025, we discovered that leads had stopped coming in for over 24 hours because webhook endpoints were returning errors (CSRF 419 and Server 500). The webhooks were broken but we didn't know until investigating why the lead count was stuck at 444.

## Testing Checklist for ANY Webhook Change

### 1. Basic Connectivity Test
```bash
curl -X POST https://your-domain.com/webhook-endpoint \
  -H "Content-Type: application/json" \
  -d '{"test":"data"}' \
  -w "\nHTTP Status: %{http_code}\n"
```

**Expected**: HTTP Status: 200

### 2. Test with Actual Lead Data
```bash
curl -X POST https://your-domain.com/webhook-endpoint \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Lead",
    "phone": "555-0123",
    "email": "test@example.com",
    "source": "test"
  }' \
  -w "\nHTTP Status: %{http_code}\n"
```

**Expected**: HTTP Status: 200

### 3. Test GET Method (Some providers test with GET first)
```bash
curl -X GET https://your-domain.com/webhook-endpoint \
  -w "\nHTTP Status: %{http_code}\n"
```

**Expected**: HTTP Status: 200 or 405 (Method Not Allowed is OK for GET)

### 4. Check Logs
```bash
# Check Render logs or local logs
tail -f storage/logs/laravel.log | grep -i webhook
```

**Expected**: See log entries confirming webhook received

## Common Issues and Fixes

### Issue: HTTP 419 - Page Expired
**Cause**: CSRF token protection
**Fix**: 
```php
Route::post('/webhook', function() {
    // handler
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```
Or use `Route::any()` for GET/POST support

### Issue: HTTP 500 - Server Error
**Cause**: Code error in webhook handler
**Fix**: Check logs, wrap in try-catch, always return 200:
```php
try {
    // process webhook
    return response()->json(['success' => true], 200);
} catch (\Exception $e) {
    \Log::error('Webhook error', ['error' => $e->getMessage()]);
    return response()->json(['success' => false], 200); // Still return 200!
}
```

### Issue: HTTP 404 - Not Found
**Cause**: Route not defined or wrong URL
**Fix**: Check routes file, ensure deployment completed

## Current Webhook Endpoints (as of Jan 8, 2025)

### Primary Endpoints
1. **`/webhook-failsafe.php`** - Queue-based, always returns 200
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook-failsafe.php`
   - Method: ANY (GET/POST)
   - CSRF: Disabled
   - Status: ✅ WORKING

2. **`/webhook.php`** - Direct processing
   - URL: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
   - Method: POST
   - CSRF: Should be disabled
   - Status: ⚠️ Needs testing

3. **`/test-webhook`** - Testing endpoint
   - URL: `https://quotingfast-brain-ohio.onrender.com/test-webhook`
   - Method: POST
   - CSRF: Should be disabled
   - Status: ⚠️ Needs testing

## Testing Commands for Current Setup

```bash
# Test failsafe webhook
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook-failsafe.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Lead","phone":"555-1234","email":"test@test.com"}' \
  -w "\nHTTP Status: %{http_code}\n"

# Test main webhook
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Lead","phone":"555-1234","email":"test@test.com"}' \
  -w "\nHTTP Status: %{http_code}\n"

# Test with GET (some providers test this way)
curl -X GET https://quotingfast-brain-ohio.onrender.com/webhook-failsafe.php \
  -w "\nHTTP Status: %{http_code}\n"
```

## After Deployment Checklist

- [ ] Wait for deployment to complete (check Render dashboard)
- [ ] Run all test commands above
- [ ] Verify HTTP 200 responses
- [ ] Check logs for webhook received messages
- [ ] Send test lead from actual provider (if possible)
- [ ] Monitor for 5 minutes to ensure stability

## Emergency Fix If Webhooks Break

1. **Immediate Response** - Create emergency endpoint:
```php
Route::any('/emergency-webhook', function() {
    \Log::info('Emergency webhook', request()->all());
    return response()->json(['success' => true], 200);
});
```

2. **Update Provider** - Give them new URL immediately

3. **Debug Original** - Fix the original endpoint while emergency runs

## Monitoring Webhooks

### Check if leads are coming in:
```sql
-- Check recent leads
SELECT COUNT(*) FROM leads WHERE created_at > NOW() - INTERVAL '1 hour';
```

### Check webhook logs:
```bash
grep "WEBHOOK" storage/logs/laravel.log | tail -20
```

### Set up alerts:
- Monitor lead count hourly
- Alert if no new leads in 2+ hours during business hours

## Documentation Updates

When changing webhooks, update:
1. This file (WEBHOOK_TESTING_PROTOCOL.md)
2. BRAIN_SYSTEM_DOCUMENTATION.md
3. API_CONFIGURATIONS.md (if URLs change)
4. Notify lead provider of any URL changes

## Lessons Learned

1. **Jan 8, 2025**: Webhooks broke due to CSRF protection and weren't tested after endpoint changes. Leads stopped for 24+ hours before discovery.

2. **Best Practice**: ALWAYS test webhooks immediately after ANY change, no matter how small.

3. **Provider Communication**: Keep lead provider's technical contact handy for quick webhook URL updates.

---

**Remember**: A broken webhook means ZERO leads. Test everything, every time!



