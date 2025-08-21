# 📞 DID HEALTH CHECK SCHEDULE
**Last Updated: August 20, 2025**

## ✅ RECOMMENDED SCHEDULE

### **HOURLY CHECKS (24/7)**
```bash
# Add to crontab
0 * * * * php /var/www/html/brain/artisan did:monitor >> /var/www/html/brain/storage/logs/did_health.log 2>&1
```
- Runs every hour on the hour
- Tracks answer rates throughout the day
- Catches spam issues quickly

### **PEAK HOURS CHECKS (Every 30 min)**
```bash
# During calling hours (9 AM - 6 PM EST)
*/30 9-18 * * * php /var/www/html/brain/artisan did:monitor --force >> /var/www/html/brain/storage/logs/did_health_peak.log 2>&1
```
- More frequent during active calling
- Faster detection of issues
- Prevents wasted calls on bad DIDs

### **DAILY SUMMARY REPORT (6 AM)**
```bash
# Daily comprehensive report
0 6 * * * php /var/www/html/brain/artisan did:report --email=admin@quotingfast.com >> /var/www/html/brain/storage/logs/did_daily_report.log 2>&1
```
- Full analysis of previous day
- Trend analysis
- Rotation recommendations

### **WEEKLY DEEP ANALYSIS (Sunday 2 AM)**
```bash
# Weekly deep dive
0 2 * * 0 php /var/www/html/brain/artisan did:analyze --deep >> /var/www/html/brain/storage/logs/did_weekly.log 2>&1
```
- Historical trend analysis
- Carrier reputation check
- Coverage gap analysis

## 📊 WHAT GETS CHECKED

### **Every Hour:**
1. Answer rate per DID
2. Call volume count
3. Health score calculation
4. Spam likelihood detection
5. Alert generation

### **During Peak Hours (Every 30 min):**
1. Real-time answer rates
2. Immediate spam detection
3. Overuse monitoring
4. Quick rotation decisions

### **Daily Report Includes:**
- Total calls per DID
- Average answer rates
- DIDs needing rotation
- Coverage gaps by area
- Cost per successful connection
- Recommended actions

## 🚨 ALERT THRESHOLDS

### **IMMEDIATE ALERTS (Real-time)**
- Answer rate < 5% = CRITICAL (Spam)
- Calls > 50/day = OVERUSE
- Answer rate drop > 10% in 1 hour = INVESTIGATE

### **WARNING ALERTS (Hourly)**
- Answer rate 5-10% = WARNING
- Calls > 45/day = NEAR LIMIT
- Coverage < 3 DIDs per area = LOW COVERAGE

### **MONITORING ALERTS (Daily)**
- Answer rate 10-15% = MONITOR
- DID age > 60 days = CONSIDER ROTATION
- Underuse < 10 calls/day = INEFFICIENT

## 🔄 AUTOMATIC ACTIONS

### **When Spam Detected (<5% answer rate):**
1. Mark DID as "NEEDS_REST"
2. Remove from active rotation
3. Start 21-day rest timer
4. Alert sent to admin
5. Activate replacement DID

### **When Overuse Detected (>50 calls):**
1. Stop using DID for remainder of day
2. Reset counter at midnight
3. Distribute calls to other DIDs
4. Log for pattern analysis

### **When Coverage Low (<3 DIDs):**
1. Alert to add more DIDs
2. Redistribute existing DIDs
3. Prioritize healthy DIDs
4. Request new numbers from carrier

## 📈 MONITORING DASHBOARD

Access real-time stats at:
- **Command Center:** `/vici-command-center` → DID Health tab
- **Detailed Analytics:** `/did/analytics`
- **Rotation Schedule:** `/did/rotation`
- **Alert History:** `/did/alerts`

## 💾 DATABASE CLEANUP

### **Data Retention:**
- Hourly data: Keep 7 days
- Daily summaries: Keep 90 days
- Monthly reports: Keep 1 year
- Alert history: Keep 30 days

### **Cleanup Job (Daily at 3 AM):**
```bash
0 3 * * * php /var/www/html/brain/artisan did:cleanup >> /var/www/html/brain/storage/logs/did_cleanup.log 2>&1
```

## 🎯 EXPECTED OUTCOMES

With consistent monitoring:
- **Catch spam labeling** within 1-2 hours (not days)
- **Maintain >15% answer rates** across all DIDs
- **Reduce wasted calls** by 30-40%
- **Extend DID lifespan** by 50%
- **Improve overall conversion** by 10-15%

## 📝 IMPLEMENTATION CHECKLIST

- [ ] Add hourly cron job
- [ ] Add peak hours cron job (every 30 min)
- [ ] Add daily report cron job
- [ ] Add weekly analysis cron job
- [ ] Configure email alerts
- [ ] Set up SMS alerts for critical issues
- [ ] Test rotation automation
- [ ] Verify dashboard updates
- [ ] Document carrier contacts
- [ ] Train team on alerts

## 🚀 QUICK SETUP

```bash
# Add all cron jobs at once
(crontab -l 2>/dev/null; echo "# DID Health Monitoring
0 * * * * php /var/www/html/brain/artisan did:monitor >> /var/www/html/brain/storage/logs/did_health.log 2>&1
*/30 9-18 * * * php /var/www/html/brain/artisan did:monitor --force >> /var/www/html/brain/storage/logs/did_health_peak.log 2>&1
0 6 * * * php /var/www/html/brain/artisan did:report --email=admin@quotingfast.com >> /var/www/html/brain/storage/logs/did_daily_report.log 2>&1
0 2 * * 0 php /var/www/html/brain/artisan did:analyze --deep >> /var/www/html/brain/storage/logs/did_weekly.log 2>&1
0 3 * * * php /var/www/html/brain/artisan did:cleanup >> /var/www/html/brain/storage/logs/did_cleanup.log 2>&1") | crontab -
```

---

**Note:** Adjust frequencies based on call volume. High-volume operations may need checks every 15 minutes during peak hours.

