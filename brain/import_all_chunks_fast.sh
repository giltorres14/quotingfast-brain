#!/bin/bash

# Import all LQF chunks SUPER FAST

CHUNK_DIR="/Users/giltorres/Downloads/lqf_chunks/"
BRAIN_DIR="/Users/giltorres/Downloads/platformparcelsms-main/brain"

echo "🚀 ULTRA FAST LQF CHUNK IMPORT"
echo "=============================="
echo ""

# Get starting count
BEFORE=$(cd $BRAIN_DIR && php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
echo "Starting count: $BEFORE"
echo ""

# Process all chunks
for CHUNK in $CHUNK_DIR/lqf_chunk_*.csv; do
    if [ -f "$CHUNK" ]; then
        echo "Importing: $(basename $CHUNK)"
        cd $BRAIN_DIR && php import_single_chunk.php "$CHUNK" 2>&1 | tail -1
    fi
done

echo ""
echo "=============================="

# Get final count
AFTER=$(cd $BRAIN_DIR && php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
NEW=$((AFTER - BEFORE))

echo "✅ IMPORT COMPLETE!"
echo "Before: $BEFORE"
echo "After: $AFTER"
echo "New leads imported: $NEW"
echo ""



# Import all LQF chunks SUPER FAST

CHUNK_DIR="/Users/giltorres/Downloads/lqf_chunks/"
BRAIN_DIR="/Users/giltorres/Downloads/platformparcelsms-main/brain"

echo "🚀 ULTRA FAST LQF CHUNK IMPORT"
echo "=============================="
echo ""

# Get starting count
BEFORE=$(cd $BRAIN_DIR && php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
echo "Starting count: $BEFORE"
echo ""

# Process all chunks
for CHUNK in $CHUNK_DIR/lqf_chunk_*.csv; do
    if [ -f "$CHUNK" ]; then
        echo "Importing: $(basename $CHUNK)"
        cd $BRAIN_DIR && php import_single_chunk.php "$CHUNK" 2>&1 | tail -1
    fi
done

echo ""
echo "=============================="

# Get final count
AFTER=$(cd $BRAIN_DIR && php artisan tinker --execute="echo \App\Models\Lead::where('source', 'LQF_BULK')->count();")
NEW=$((AFTER - BEFORE))

echo "✅ IMPORT COMPLETE!"
echo "Before: $BEFORE"
echo "After: $AFTER"
echo "New leads imported: $NEW"
echo ""






