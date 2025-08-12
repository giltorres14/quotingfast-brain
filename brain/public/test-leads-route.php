<?php
// Test the leads route to find the exact error

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Force debug mode
config(['app.debug' => true]);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔍 TESTING LEADS ROUTE\n";
echo "=====================================\n\n";

// Import the Lead model
use App\Models\Lead;

try {
    echo "1. Testing Lead model:\n";
    $leadCount = Lead::count();
    echo "   ✅ Lead::count() = $leadCount\n";
    
    echo "\n2. Testing distinct queries:\n";
    $statuses = Lead::distinct('status')->pluck('status');
    echo "   ✅ Distinct statuses: " . $statuses->count() . "\n";
    
    $sources = Lead::distinct('source')->pluck('source');
    echo "   ✅ Distinct sources: " . $sources->count() . "\n";
    
    $states = Lead::distinct('state')->pluck('state');
    echo "   ✅ Distinct states: " . $states->count() . "\n";
    
    echo "\n3. Testing pagination:\n";
    $leads = Lead::orderBy('created_at', 'desc')->paginate(10);
    echo "   ✅ Paginated leads: " . $leads->count() . " items\n";
    
    echo "\n4. Testing view compilation:\n";
    
    // Prepare view data
    $viewData = [
        'leads' => $leads,
        'statuses' => $statuses,
        'sources' => $sources,
        'states' => $states,
        'search' => '',
        'status' => 'all',
        'source' => 'all',
        'state_filter' => 'all',
        'vici_status' => 'all',
        'stats' => [
            'total_leads' => $leadCount,
            'today_leads' => 0,
            'vici_sent' => 0,
            'allstate_sent' => 0
        ]
    ];
    
    // Try to render the view
    $view = view('leads.index', $viewData);
    $html = $view->render();
    
    echo "   ✅ View rendered successfully! Length: " . strlen($html) . " bytes\n";
    
    // Check for specific issues in the rendered HTML
    if (strpos($html, 'Error') !== false || strpos($html, 'Exception') !== false) {
        echo "   ⚠️ Warning: The word 'Error' or 'Exception' appears in the rendered HTML\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERROR FOUND:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "\n   Stack trace (first 10 lines):\n";
    $trace = explode("\n", $e->getTraceAsString());
    for ($i = 0; $i < min(10, count($trace)); $i++) {
        echo "   " . $trace[$i] . "\n";
    }
}

echo "\n=====================================\n";
echo "🏁 TEST COMPLETE\n";
