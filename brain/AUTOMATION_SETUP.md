# ViciDial Automation Setup - FINAL STEPS

## ✅ Everything is cleaned up and optimized!

### 1. Add ONE line to crontab:
```bash
crontab -e
# Add this line:
* * * * * /var/www/html/brain/run_scheduled_tasks.sh
```

### 2. Verify it's working:
```bash
# Check automation health
./check_automation_health.sh

# View recent logs
tail -f storage/logs/automation.log
```

### 3. What runs automatically:
- **Every 5 min**: Health checks
- **Every 15 min**: Call log sync
- **Every 30 min**: Test A lead flow (9 AM - 6 PM only)
- **Every hour**: Optimal timing control (9 AM - 6 PM only)
- **Daily at 2 AM**: Log cleanup

### 4. Monitor from UI:
- Sync Status: https://quotingfast-brain-ohio.onrender.com/vici/sync-status
- Command Center: https://quotingfast-brain-ohio.onrender.com/vici-command-center
- Analytics: https://quotingfast-brain-ohio.onrender.com/admin/vici-comprehensive-reports

### 5. If something goes wrong:
- Check: `storage/logs/automation.log`
- Run: `./check_automation_health.sh`
- All scripts have error handling and will retry

## 💤 The system is now self-running and reliable!
