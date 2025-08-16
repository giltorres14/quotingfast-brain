<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Test the queries
try {
    echo "Testing Vici queries...\n";
    
    // Test 1: Basic count
    $totalCalls = \App\Models\ViciCallMetrics::count();
    echo "✅ Total calls: $totalCalls\n";
    
    // Test 2: Today's calls - potential issue
    echo "Testing today's calls...\n";
    $todayCalls = \App\Models\ViciCallMetrics::whereDate('created_at', today())->count();
    echo "✅ Today's calls: $todayCalls\n";
    
    // Test 3: Connected calls
    $connectedCalls = \App\Models\ViciCallMetrics::where('call_status', 'XFER')->count();
    echo "✅ Connected calls: $connectedCalls\n";
    
    // Test 4: Orphan calls
    $orphanCalls = \App\Models\OrphanCallLog::unmatched()->count();
    echo "✅ Orphan calls: $orphanCalls\n";
    
    // Test 5: Recent calls
    $recentCalls = \App\Models\ViciCallMetrics::orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    echo "✅ Recent calls: " . $recentCalls->count() . "\n";
    
    echo "\n✅ All queries work!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
