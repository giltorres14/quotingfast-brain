<?php
/**
 * Fix for recurring 500 errors on Vici Dashboard
 * This script ensures all required tables and columns exist
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fixing Vici Dashboard 500 Error ===\n\n";

// Check and create vici_call_metrics table if missing
echo "Checking vici_call_metrics table...\n";
if (!Schema::hasTable('vici_call_metrics')) {
    echo "  ❌ Table missing - creating it...\n";
    Schema::create('vici_call_metrics', function ($table) {
        $table->id();
        $table->string('vici_lead_id')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('call_status')->nullable();
        $table->integer('talk_time')->default(0);
        $table->string('agent')->nullable();
        $table->string('list_id')->nullable();
        $table->string('campaign_id')->nullable();
        $table->integer('total_calls')->default(0);
        $table->boolean('connected')->default(false);
        $table->jsonb('dispositions')->nullable();
        $table->timestamp('last_call_date')->nullable();
        $table->timestamps();
        
        $table->index('vici_lead_id');
        $table->index('phone_number');
        $table->index('call_status');
        $table->index('created_at');
    });
    echo "  ✅ Table created successfully\n";
} else {
    echo "  ✅ Table exists\n";
    
    // Check for required columns
    $columns = [
        'vici_lead_id' => 'string',
        'phone_number' => 'string',
        'call_status' => 'string',
        'talk_time' => 'integer',
        'agent' => 'string',
        'total_calls' => 'integer',
        'connected' => 'boolean',
        'dispositions' => 'jsonb',
        'last_call_date' => 'timestamp'
    ];
    
    foreach ($columns as $column => $type) {
        if (!Schema::hasColumn('vici_call_metrics', $column)) {
            echo "  ❌ Column '$column' missing - adding it...\n";
            Schema::table('vici_call_metrics', function ($table) use ($column, $type) {
                switch($type) {
                    case 'string':
                        $table->string($column)->nullable();
                        break;
                    case 'integer':
                        $table->integer($column)->default(0);
                        break;
                    case 'boolean':
                        $table->boolean($column)->default(false);
                        break;
                    case 'jsonb':
                        $table->jsonb($column)->nullable();
                        break;
                    case 'timestamp':
                        $table->timestamp($column)->nullable();
                        break;
                }
            });
            echo "  ✅ Column added\n";
        }
    }
}

// Check and create orphan_call_logs table if missing
echo "\nChecking orphan_call_logs table...\n";
if (!Schema::hasTable('orphan_call_logs')) {
    echo "  ❌ Table missing - creating it...\n";
    Schema::create('orphan_call_logs', function ($table) {
        $table->id();
        $table->string('uniqueid')->nullable();
        $table->string('lead_id')->nullable();
        $table->string('list_id')->nullable();
        $table->string('campaign_id')->nullable();
        $table->timestamp('call_date')->nullable();
        $table->bigInteger('start_epoch')->nullable();
        $table->bigInteger('end_epoch')->nullable();
        $table->integer('length_in_sec')->default(0);
        $table->string('status')->nullable();
        $table->string('phone_code')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('user')->nullable();
        $table->text('comments')->nullable();
        $table->boolean('processed')->default(false);
        $table->string('term_reason')->nullable();
        $table->string('vendor_lead_code')->nullable();
        $table->string('matched_lead_id')->nullable();
        $table->timestamps();
        
        $table->index('lead_id');
        $table->index('phone_number');
        $table->index('vendor_lead_code');
        $table->index('matched_lead_id');
    });
    echo "  ✅ Table created successfully\n";
} else {
    echo "  ✅ Table exists\n";
}

// Insert sample data if tables are empty
$viciCount = DB::table('vici_call_metrics')->count();
if ($viciCount == 0) {
    echo "\nInserting sample data for vici_call_metrics...\n";
    DB::table('vici_call_metrics')->insert([
        [
            'vici_lead_id' => '1000001',
            'phone_number' => '5551234567',
            'call_status' => 'NA',
            'talk_time' => 0,
            'agent' => 'VDAD',
            'total_calls' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'vici_lead_id' => '1000002',
            'phone_number' => '5559876543',
            'call_status' => 'XFER',
            'talk_time' => 245,
            'agent' => 'Agent001',
            'total_calls' => 3,
            'connected' => true,
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30)
        ]
    ]);
    echo "  ✅ Sample data inserted\n";
}

$orphanCount = DB::table('orphan_call_logs')->count();
if ($orphanCount == 0) {
    echo "\nInserting sample data for orphan_call_logs...\n";
    DB::table('orphan_call_logs')->insert([
        [
            'uniqueid' => '1234567890.123',
            'lead_id' => '999999',
            'phone_number' => '5555555555',
            'status' => 'NA',
            'processed' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "  ✅ Sample data inserted\n";
}

// Clear all caches
echo "\nClearing caches...\n";
Artisan::call('cache:clear');
Artisan::call('view:clear');
Artisan::call('config:clear');
Artisan::call('route:clear');
echo "  ✅ All caches cleared\n";

echo "\n✅ FIXES COMPLETE!\n";
echo "The Vici Dashboard should now load without 500 errors.\n";
echo "\nNext steps:\n";
echo "1. Visit https://quotingfast-brain-ohio.onrender.com/vici\n";
echo "2. The dashboard should display with sample data\n";
echo "3. Real data will populate as call logs are imported\n";




