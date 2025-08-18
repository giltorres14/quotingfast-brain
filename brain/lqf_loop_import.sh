#!/bin/bash

# Loop import - keeps restarting the artisan command when it fails

echo "🔄 LQF LOOP IMPORT - Will keep restarting on failure"
echo "======================================================"
echo ""

CSV_FILE="/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv"
COUNTER=0

while true; do
    COUNTER=$((COUNTER + 1))
    echo "🚀 Starting import attempt #$COUNTER at $(date)"
    
    # Get current count
    BEFORE=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
    echo "Current LQF leads: $BEFORE"
    
    # Run import (it will crash eventually but import some)
    timeout 300 php artisan lqf:bulk-import "$CSV_FILE" --skip-duplicates 2>&1 | tail -20
    
    # Get new count
    AFTER=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
    NEW=$((AFTER - BEFORE))
    
    echo ""
    echo "✓ Imported $NEW new leads (Total: $AFTER)"
    echo ""
    
    # Check if we're done
    if [ "$AFTER" -ge 149000 ]; then
        echo "🎉 IMPORT COMPLETE! Total: $AFTER leads"
        break
    fi
    
    # Check if we're making progress
    if [ "$NEW" -eq 0 ]; then
        echo "⚠️  No progress made, waiting 10 seconds..."
        sleep 10
    else
        echo "↻ Restarting immediately..."
        sleep 2
    fi
done



# Loop import - keeps restarting the artisan command when it fails

echo "🔄 LQF LOOP IMPORT - Will keep restarting on failure"
echo "======================================================"
echo ""

CSV_FILE="/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv"
COUNTER=0

while true; do
    COUNTER=$((COUNTER + 1))
    echo "🚀 Starting import attempt #$COUNTER at $(date)"
    
    # Get current count
    BEFORE=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
    echo "Current LQF leads: $BEFORE"
    
    # Run import (it will crash eventually but import some)
    timeout 300 php artisan lqf:bulk-import "$CSV_FILE" --skip-duplicates 2>&1 | tail -20
    
    # Get new count
    AFTER=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
    NEW=$((AFTER - BEFORE))
    
    echo ""
    echo "✓ Imported $NEW new leads (Total: $AFTER)"
    echo ""
    
    # Check if we're done
    if [ "$AFTER" -ge 149000 ]; then
        echo "🎉 IMPORT COMPLETE! Total: $AFTER leads"
        break
    fi
    
    # Check if we're making progress
    if [ "$NEW" -eq 0 ]; then
        echo "⚠️  No progress made, waiting 10 seconds..."
        sleep 10
    else
        echo "↻ Restarting immediately..."
        sleep 2
    fi
done






