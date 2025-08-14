#!/bin/bash
# Vici Update Script - Process all chunks

echo "=== PROCESSING VICI UPDATES IN CHUNKS ==="
echo ""

TOTAL_UPDATED=0
CHUNK_DIR="vici_update_chunks"

for CHUNK_FILE in $CHUNK_DIR/chunk_*.csv; do
    if [ -f "$CHUNK_FILE" ]; then
        CHUNK_NAME=$(basename "$CHUNK_FILE" .csv)
        echo "Processing $CHUNK_NAME..."
        
        # Process this chunk
        php process_vici_chunk.php "$CHUNK_FILE"
        
        if [ $? -eq 0 ]; then
            echo "  ✅ $CHUNK_NAME processed successfully"
            # Move processed chunk to done folder
            mkdir -p $CHUNK_DIR/done
            mv "$CHUNK_FILE" "$CHUNK_DIR/done/"
        else
            echo "  ❌ $CHUNK_NAME failed - will retry later"
        fi
        
        echo "  Waiting 2 seconds before next chunk..."
        sleep 2
        echo ""
    fi
done

echo "=== ALL CHUNKS PROCESSED ==="
