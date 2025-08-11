# Vici Go-Live Checklist

## ✅ Completed Items
- [x] Brain → Vici integration complete (webhook.php → List 101)
- [x] Webhook endpoints deployed and accessible
- [x] Database connection established (PostgreSQL)
- [x] Lead capture and storage working

## 🔄 Current Status
- **Webhook URL**: https://quotingfast-brain-ohio.onrender.com/webhook.php
- **Vici List**: 101 (AUTODIAL campaign)
- **Lead Flow**: LeadsQuotingFast → Brain → Vici List 101

## 📋 Go-Live Steps

### 1. Update LeadsQuotingFast Configuration
- [ ] Point production traffic to: `https://quotingfast-brain-ohio.onrender.com/webhook.php`
- [ ] Verify webhook is receiving live leads
- [ ] Monitor logs for any errors

### 2. Import Historical Data
- [ ] Export CSV from current system
- [ ] Import historical leads into Brain database
- [ ] Update vendor_lead_code in Vici for existing leads

### 3. Agent Interface Migration
- [ ] Current: Agents use Vici interface directly
- [ ] Migration: Gradual transition to Brain iframe
- [ ] Training: Brief agents on any interface changes

### 4. Vici Configuration
- [ ] Verify List 101 is active in AUTODIAL campaign
- [ ] Check agent assignments to campaign
- [ ] Confirm dialing settings are correct

### 5. Testing with Live Agents
- [ ] Send test lead through full flow
- [ ] Have agent receive and process lead
- [ ] Verify all data appears correctly
- [ ] Test disposition and callback features

## 🚀 Quick Start Commands

### Send Test Lead
```bash
curl -X POST https://quotingfast-brain-ohio.onrender.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "contact": {
      "first_name": "Test",
      "last_name": "Lead",
      "phone": "5555551234",
      "email": "test@example.com"
    },
    "drivers": [{
      "name": "Test Lead",
      "age": 35
    }],
    "vehicles": [{
      "year": 2020,
      "make": "Toyota",
      "model": "Camry"
    }]
  }'
```

### Check Lead Status
```bash
curl https://quotingfast-brain-ohio.onrender.com/webhook/status
```

## 📊 Monitoring
- Webhook Status: https://quotingfast-brain-ohio.onrender.com/webhook/status
- Admin Dashboard: https://quotingfast-brain-ohio.onrender.com/admin/dashboard

## ⚠️ Important Notes
1. **Vendor Code Format**: Brain generates 13-digit timestamp IDs
2. **Lead Types**: System auto-detects auto vs home insurance
3. **Duplicate Prevention**: 10-day window for duplicate detection
4. **Re-engagement**: Leads 11-90 days old are marked for re-engagement

## 🔧 Troubleshooting
- If leads not appearing in Vici: Check List 101 status
- If agents can't see leads: Verify campaign assignment
- If webhook fails: Check logs at /storage/logs/laravel.log

## 📞 Next Phase: Twilio Integration
After Vici agents are live, we'll add:
- SMS notifications
- Scheduled callbacks
- Automated follow-ups

## 💼 Final Phase: Buyer Platform
- Direct buyer integrations
- Real-time bidding
- Revenue optimization
