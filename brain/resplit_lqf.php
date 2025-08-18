<?php
/**
 * Re-split LQF CSV to get ALL records
 */

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';
$outputDir = '/Users/giltorres/Downloads/lqf_chunks_new/';
$chunkSize = 5000; // 5k records per file

echo "\n📂 RE-SPLITTING LQF CSV INTO PROPER CHUNKS\n";
echo "==========================================\n\n";

// Create output directory
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
} else {
    // Clean old files
    array_map('unlink', glob("$outputDir*.csv"));
}

// Count total lines first
$totalLines = 0;
$handle = fopen($csvFile, 'r');
while (!feof($handle)) {
    fgets($handle);
    $totalLines++;
}
fclose($handle);
$totalRecords = $totalLines - 1; // Subtract header

echo "Total records in CSV: " . number_format($totalRecords) . "\n";
echo "Chunk size: " . number_format($chunkSize) . "\n";
$expectedChunks = ceil($totalRecords / $chunkSize);
echo "Expected chunks: $expectedChunks\n\n";

// Now split the file
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);

$chunkNum = 1;
$lineCount = 0;
$currentChunk = null;
$currentFile = null;

echo "Splitting CSV...\n\n";

while (($data = fgetcsv($handle)) !== FALSE) {
    // Start new chunk if needed
    if ($lineCount % $chunkSize == 0) {
        // Close previous chunk
        if ($currentChunk) {
            fclose($currentChunk);
            echo "✓ Created $currentFile (" . number_format($chunkSize) . " records)\n";
        }
        
        // Open new chunk
        $currentFile = sprintf("chunk_%03d.csv", $chunkNum);
        $currentChunk = fopen($outputDir . $currentFile, 'w');
        
        // Write header to new chunk
        fputcsv($currentChunk, $header);
        
        $chunkNum++;
    }
    
    // Write data to current chunk
    fputcsv($currentChunk, $data);
    $lineCount++;
}

// Close last chunk
if ($currentChunk) {
    $lastChunkSize = $lineCount % $chunkSize;
    if ($lastChunkSize == 0) $lastChunkSize = $chunkSize;
    fclose($currentChunk);
    echo "✓ Created $currentFile (" . number_format($lastChunkSize) . " records)\n";
}

fclose($handle);

$totalChunks = $chunkNum - 1;

echo "\n==========================================\n";
echo "✅ RE-SPLIT COMPLETE!\n";
echo "==========================================\n\n";
echo "Total records split: " . number_format($lineCount) . "\n";
echo "Files created: $totalChunks\n";
echo "Output directory: $outputDir\n\n";

// Verify
$verifyCount = count(glob($outputDir . "*.csv"));
echo "Verification: Found $verifyCount chunk files\n";

if ($verifyCount != $expectedChunks) {
    echo "⚠️  WARNING: Expected $expectedChunks chunks but created $verifyCount\n";
} else {
    echo "✅ Chunk count matches expected!\n";
}


/**
 * Re-split LQF CSV to get ALL records
 */

$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';
$outputDir = '/Users/giltorres/Downloads/lqf_chunks_new/';
$chunkSize = 5000; // 5k records per file

echo "\n📂 RE-SPLITTING LQF CSV INTO PROPER CHUNKS\n";
echo "==========================================\n\n";

// Create output directory
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
} else {
    // Clean old files
    array_map('unlink', glob("$outputDir*.csv"));
}

// Count total lines first
$totalLines = 0;
$handle = fopen($csvFile, 'r');
while (!feof($handle)) {
    fgets($handle);
    $totalLines++;
}
fclose($handle);
$totalRecords = $totalLines - 1; // Subtract header

echo "Total records in CSV: " . number_format($totalRecords) . "\n";
echo "Chunk size: " . number_format($chunkSize) . "\n";
$expectedChunks = ceil($totalRecords / $chunkSize);
echo "Expected chunks: $expectedChunks\n\n";

// Now split the file
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);

$chunkNum = 1;
$lineCount = 0;
$currentChunk = null;
$currentFile = null;

echo "Splitting CSV...\n\n";

while (($data = fgetcsv($handle)) !== FALSE) {
    // Start new chunk if needed
    if ($lineCount % $chunkSize == 0) {
        // Close previous chunk
        if ($currentChunk) {
            fclose($currentChunk);
            echo "✓ Created $currentFile (" . number_format($chunkSize) . " records)\n";
        }
        
        // Open new chunk
        $currentFile = sprintf("chunk_%03d.csv", $chunkNum);
        $currentChunk = fopen($outputDir . $currentFile, 'w');
        
        // Write header to new chunk
        fputcsv($currentChunk, $header);
        
        $chunkNum++;
    }
    
    // Write data to current chunk
    fputcsv($currentChunk, $data);
    $lineCount++;
}

// Close last chunk
if ($currentChunk) {
    $lastChunkSize = $lineCount % $chunkSize;
    if ($lastChunkSize == 0) $lastChunkSize = $chunkSize;
    fclose($currentChunk);
    echo "✓ Created $currentFile (" . number_format($lastChunkSize) . " records)\n";
}

fclose($handle);

$totalChunks = $chunkNum - 1;

echo "\n==========================================\n";
echo "✅ RE-SPLIT COMPLETE!\n";
echo "==========================================\n\n";
echo "Total records split: " . number_format($lineCount) . "\n";
echo "Files created: $totalChunks\n";
echo "Output directory: $outputDir\n\n";

// Verify
$verifyCount = count(glob($outputDir . "*.csv"));
echo "Verification: Found $verifyCount chunk files\n";

if ($verifyCount != $expectedChunks) {
    echo "⚠️  WARNING: Expected $expectedChunks chunks but created $verifyCount\n";
} else {
    echo "✅ Chunk count matches expected!\n";
}






