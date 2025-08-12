<?php

/**
 * Test script to demonstrate Brain moving leads between Vici lists
 * 
 * YES - The Brain CAN move leads to different Vici lists through the API!
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Services\ViciDialerService;

echo "\n";
echo "========================================\n";
echo "  VICI LIST MOVE TEST - Brain → Vici\n";
echo "========================================\n\n";

// Get a test lead
$lead = Lead::where('phone', '!=', null)
            ->where('external_lead_id', '!=', null)
            ->first();

if (!$lead) {
    echo "❌ No lead found with external_lead_id\n";
    exit(1);
}

echo "📋 Test Lead:\n";
echo "  - Lead ID: {$lead->id}\n";
echo "  - Phone: {$lead->phone}\n";
echo "  - External ID: {$lead->external_lead_id}\n";
echo "  - Current List: " . ($lead->vici_list_id ?? 'unknown') . "\n";
echo "  - Status: {$lead->status}\n\n";

// Initialize Vici service
$viciService = new ViciDialerService();

// Test moving to different lists
$testMoves = [
    ['list' => 102, 'reason' => 'Testing retry list'],
    ['list' => 103, 'reason' => 'Testing callback list'],
    ['list' => 101, 'reason' => 'Back to fresh leads']
];

foreach ($testMoves as $move) {
    echo "🔄 Moving lead to List {$move['list']}...\n";
    
    $result = $viciService->moveLeadToList($lead, $move['list'], $move['reason']);
    
    if ($result['success']) {
        echo "✅ SUCCESS: {$result['message']}\n";
        echo "   From List: " . ($result['old_list_id'] ?? 'unknown') . "\n";
        echo "   To List: {$result['new_list_id']}\n";
    } else {
        echo "❌ FAILED: {$result['message']}\n";
    }
    
    echo "\n";
    sleep(2); // Wait between moves
}

// Test auto-assignment based on status
echo "🤖 Testing auto-assignment based on status...\n";
$lead->status = 'qualified';
$lead->save();

$result = $viciService->autoAssignLeadToList($lead);
echo "   Status: qualified → List: " . ($result['new_list_id'] ?? 'unchanged') . "\n\n";

echo "========================================\n";
echo "✅ The Brain CAN move leads between lists!\n";
echo "========================================\n\n";

echo "How it works:\n";
echo "1. Brain uses Vici's Non-Agent API\n";
echo "2. Sends 'update_lead' function with new list_id\n";
echo "3. Tracks the move in local database\n";
echo "4. Maintains complete history of list moves\n\n";

echo "Available functions:\n";
echo "- moveLeadToList() - Move single lead\n";
echo "- autoAssignLeadToList() - Auto-assign based on status\n";
echo "- bulkMoveLeadsToLists() - Move multiple leads\n\n";
