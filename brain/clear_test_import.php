<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

// Delete test import leads
$deleted = Lead::where('source', 'SURAJ_BULK')->delete();
echo "✅ Deleted $deleted test import leads\n";

// Clear vendors and buyers created from test
DB::table('vendors')->where('notes', 'LIKE', '%Suraj%')->delete();
echo "✅ Cleared test vendors\n";

DB::table('buyers')->where('notes', 'LIKE', '%Suraj%')->delete();
echo "✅ Cleared test buyers\n";

// Clear test campaigns
DB::table('campaigns')->where('description', 'LIKE', '%Suraj%')->delete();
echo "✅ Cleared test campaigns\n";

echo "\n✅ Ready for fresh import!\n";
