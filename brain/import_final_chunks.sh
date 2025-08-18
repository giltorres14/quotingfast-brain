#!/bin/bash

# Import all 30 LQF chunks

CHUNK_DIR="/Users/giltorres/Downloads/lqf_chunks_final"
BRAIN_DIR="/Users/giltorres/Downloads/platformparcelsms-main/brain"
IMPORT_SCRIPT="$BRAIN_DIR/import_single_chunk.php"

echo "🚀 IMPORTING ALL 30 LQF CHUNKS"
echo "=============================="
echo ""

# Get starting count
cd $BRAIN_DIR
BEFORE=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
echo "Starting count: $BEFORE"
echo ""

# Import each chunk
CHUNK_NUM=1
for CHUNK in $CHUNK_DIR/*.csv; do
    if [ -f "$CHUNK" ]; then
        echo "[$CHUNK_NUM/30] Importing: $(basename $CHUNK)"
        php $IMPORT_SCRIPT "$CHUNK" 2>&1 | grep "✓"
        CHUNK_NUM=$((CHUNK_NUM + 1))
    fi
done

echo ""
echo "=============================="

# Get final count
AFTER=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
NEW=$((AFTER - BEFORE))

echo "✅ ALL CHUNKS IMPORTED!"
echo ""
echo "Before: $BEFORE"
echo "After: $AFTER"
echo "New leads imported: $NEW"
echo ""
echo "🎉 LQF IMPORT COMPLETE!"



# Import all 30 LQF chunks

CHUNK_DIR="/Users/giltorres/Downloads/lqf_chunks_final"
BRAIN_DIR="/Users/giltorres/Downloads/platformparcelsms-main/brain"
IMPORT_SCRIPT="$BRAIN_DIR/import_single_chunk.php"

echo "🚀 IMPORTING ALL 30 LQF CHUNKS"
echo "=============================="
echo ""

# Get starting count
cd $BRAIN_DIR
BEFORE=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
echo "Starting count: $BEFORE"
echo ""

# Import each chunk
CHUNK_NUM=1
for CHUNK in $CHUNK_DIR/*.csv; do
    if [ -f "$CHUNK" ]; then
        echo "[$CHUNK_NUM/30] Importing: $(basename $CHUNK)"
        php $IMPORT_SCRIPT "$CHUNK" 2>&1 | grep "✓"
        CHUNK_NUM=$((CHUNK_NUM + 1))
    fi
done

echo ""
echo "=============================="

# Get final count
AFTER=$(php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
NEW=$((AFTER - BEFORE))

echo "✅ ALL CHUNKS IMPORTED!"
echo ""
echo "Before: $BEFORE"
echo "After: $AFTER"
echo "New leads imported: $NEW"
echo ""
echo "🎉 LQF IMPORT COMPLETE!"






