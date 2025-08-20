#!/bin/bash
# Quick health check for ViciDial automation

echo "ViciDial Automation Health Check - $(date)"
echo "═══════════════════════════════════════════"

# Check if cron is running
if pgrep -x "cron" > /dev/null; then
    echo "✅ Cron service is running"
else
    echo "❌ Cron service is NOT running!"
fi

# Check last sync time
LAST_SYNC=$(find storage/logs -name "sync_*.log" -type f -exec stat -c '%Y' {} \; 2>/dev/null | sort -n | tail -1)
if [ ! -z "$LAST_SYNC" ]; then
    CURRENT_TIME=$(date +%s)
    DIFF=$((CURRENT_TIME - LAST_SYNC))
    if [ $DIFF -lt 3600 ]; then
        echo "✅ Sync ran within last hour"
    else
        echo "⚠️ Sync hasn't run in $((DIFF/3600)) hours"
    fi
fi

# Check for recent errors
ERROR_COUNT=$(find storage/logs -name "*.log" -type f -mtime -1 -exec grep -l "ERROR\|Exception" {} \; 2>/dev/null | wc -l)
if [ $ERROR_COUNT -eq 0 ]; then
    echo "✅ No errors in last 24 hours"
else
    echo "⚠️ Found errors in $ERROR_COUNT log files"
fi

# Check disk space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 80 ]; then
    echo "✅ Disk usage is OK ($DISK_USAGE%)"
else
    echo "⚠️ Disk usage is high ($DISK_USAGE%)"
fi

echo ""
echo "Check complete. Logs in: storage/logs/"
