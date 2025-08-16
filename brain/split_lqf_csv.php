<?php
/**
 * Split LQF CSV into smaller chunks for fast parallel import
 */

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';
$outputDir = '/Users/giltorres/Downloads/lqf_chunks/';
$chunkSize = 5000; // 5k records per file

echo "\n📂 SPLITTING LQF CSV INTO CHUNKS\n";
echo "=====================================\n\n";

// Create output directory
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Open source CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open file: $csvFile\n");
}

// Read header
$header = fgetcsv($handle);

$chunkNum = 1;
$lineCount = 0;
$currentChunk = null;
$currentFile = null;

echo "Splitting into chunks of $chunkSize records...\n\n";

while (($data = fgetcsv($handle)) !== FALSE) {
    // Start new chunk if needed
    if ($lineCount % $chunkSize == 0) {
        // Close previous chunk
        if ($currentChunk) {
            fclose($currentChunk);
            echo "✓ Chunk $currentFile complete\n";
        }
        
        // Open new chunk
        $currentFile = sprintf("lqf_chunk_%03d.csv", $chunkNum);
        $currentChunk = fopen($outputDir . $currentFile, 'w');
        
        // Write header to new chunk
        fputcsv($currentChunk, $header);
        
        $chunkNum++;
    }
    
    // Write data to current chunk
    fputcsv($currentChunk, $data);
    $lineCount++;
    
    // Progress
    if ($lineCount % 10000 == 0) {
        echo "  Processed " . number_format($lineCount) . " records...\n";
    }
}

// Close last chunk
if ($currentChunk) {
    fclose($currentChunk);
    echo "✓ Chunk $currentFile complete\n";
}

fclose($handle);

$totalChunks = $chunkNum - 1;

echo "\n=====================================\n";
echo "✅ SPLIT COMPLETE!\n";
echo "=====================================\n\n";
echo "Total records: " . number_format($lineCount) . "\n";
echo "Chunk size: " . number_format($chunkSize) . " records\n";
echo "Files created: $totalChunks\n";
echo "Output directory: $outputDir\n\n";

// Create import script for chunks
$importScript = '#!/bin/bash
# Import all LQF chunks in parallel

CHUNK_DIR="/Users/giltorres/Downloads/lqf_chunks/"
LOG_DIR="/Users/giltorres/Downloads/lqf_logs/"
mkdir -p "$LOG_DIR"

echo "🚀 IMPORTING ALL LQF CHUNKS"
echo "============================"
echo ""

# Import function
import_chunk() {
    CHUNK=$1
    LOG="$LOG_DIR/$(basename $CHUNK .csv).log"
    echo "Starting: $(basename $CHUNK)"
    php /Users/giltorres/Downloads/platformparcelsms-main/brain/import_single_chunk.php "$CHUNK" > "$LOG" 2>&1
    echo "✓ Complete: $(basename $CHUNK)"
}

# Export function for parallel
export -f import_chunk

# Get all chunks
CHUNKS=$(ls -1 $CHUNK_DIR/*.csv)

# Run imports in parallel (5 at a time)
echo "$CHUNKS" | xargs -n 1 -P 5 -I {} bash -c "import_chunk {}"

echo ""
echo "✅ ALL CHUNKS IMPORTED!"
echo ""

# Show final count
php /Users/giltorres/Downloads/platformparcelsms-main/brain/artisan tinker --execute="
echo \"Total LQF leads: \" . \App\Models\Lead::where(\"source\", \"LQF_BULK\")->count();"
';

file_put_contents($outputDir . 'import_all_chunks.sh', $importScript);
chmod($outputDir . 'import_all_chunks.sh', 0755);

echo "Import script created: {$outputDir}import_all_chunks.sh\n";


