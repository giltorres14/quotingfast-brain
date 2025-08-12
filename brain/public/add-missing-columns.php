<?php
// Add missing columns to vici_call_metrics table

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 ADDING MISSING COLUMNS TO VICI_CALL_METRICS\n";
echo "=====================================\n\n";

try {
    // Check if vici_call_metrics table exists
    if (Schema::hasTable('vici_call_metrics')) {
        echo "✅ vici_call_metrics table exists\n\n";
        
        // List of columns that should exist
        $requiredColumns = [
            'total_calls' => 'integer DEFAULT 0',
            'connected' => 'boolean DEFAULT false',
            'talk_time' => 'integer DEFAULT 0',
            'agent_id' => 'varchar(255)',
            'disposition' => 'varchar(255)',
            'status' => 'varchar(255)'
        ];
        
        foreach ($requiredColumns as $column => $type) {
            if (!Schema::hasColumn('vici_call_metrics', $column)) {
                echo "  ⚠️ Missing column: $column\n";
                echo "  Adding $column ($type)...\n";
                
                // Add the column
                DB::statement("ALTER TABLE vici_call_metrics ADD COLUMN IF NOT EXISTS $column $type");
                echo "  ✅ Added $column\n\n";
            } else {
                echo "  ✅ Column exists: $column\n";
            }
        }
        
    } else {
        echo "❌ vici_call_metrics table does not exist\n";
        echo "Creating table...\n";
        
        // Create the table
        Schema::create('vici_call_metrics', function ($table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->integer('total_calls')->default(0);
            $table->boolean('connected')->default(false);
            $table->integer('talk_time')->default(0);
            $table->string('agent_id')->nullable();
            $table->string('disposition')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('last_call_date')->nullable();
            $table->timestamps();
            
            $table->index('lead_id');
            $table->index('phone_number');
        });
        
        echo "✅ Created vici_call_metrics table\n";
    }
    
    // Test the query that was failing
    echo "\nTesting the query that was failing...\n";
    try {
        $count = DB::table('vici_call_metrics')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('leads')
                    ->whereRaw('vici_call_metrics.lead_id = leads.id')
                    ->whereBetween('created_at', ['2025-08-05', '2025-08-12 23:59:59']);
            })
            ->where('total_calls', '>', 0)
            ->count();
            
        echo "✅ Query works! Found $count records\n";
    } catch (\Exception $e) {
        echo "❌ Query still fails: " . $e->getMessage() . "\n";
    }
    
    // Clear caches
    echo "\nClearing caches...\n";
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    echo "✅ Caches cleared\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "🏁 COLUMNS ADDED - Lead Flow page should work now!\n";
