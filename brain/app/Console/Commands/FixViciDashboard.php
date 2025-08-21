<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class FixViciDashboard extends Command
{
    protected $signature = 'vici:fix-dashboard';
    protected $description = 'Fix Vici Dashboard 500 errors by ensuring all required tables exist';

    public function handle()
    {
        $this->info('=== Fixing Vici Dashboard 500 Error ===');
        
        // Check and create vici_call_metrics table if missing
        $this->info('Checking vici_call_metrics table...');
        if (!Schema::hasTable('vici_call_metrics')) {
            $this->error('  Table missing - creating it...');
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
            $this->info('  ✅ Table created successfully');
        } else {
            $this->info('  ✅ Table exists');
            
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
                    $this->error("  Column '$column' missing - adding it...");
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
                    $this->info('  ✅ Column added');
                }
            }
        }
        
        // Check and create orphan_call_logs table if missing
        $this->info('');
        $this->info('Checking orphan_call_logs table...');
        if (!Schema::hasTable('orphan_call_logs')) {
            $this->error('  Table missing - creating it...');
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
            $this->info('  ✅ Table created successfully');
        } else {
            $this->info('  ✅ Table exists');
        }
        
        // Insert sample data if tables are empty
        $viciCount = DB::table('vici_call_metrics')->count();
        if ($viciCount == 0) {
            $this->info('');
            $this->info('Inserting sample data for vici_call_metrics...');
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
            $this->info('  ✅ Sample data inserted');
        }
        
        $orphanCount = DB::table('orphan_call_logs')->count();
        if ($orphanCount == 0) {
            $this->info('');
            $this->info('Inserting sample data for orphan_call_logs...');
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
            $this->info('  ✅ Sample data inserted');
        }
        
        // Clear all caches
        $this->info('');
        $this->info('Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        $this->info('  ✅ All caches cleared');
        
        $this->info('');
        $this->info('✅ FIXES COMPLETE!');
        $this->info('The Vici Dashboard should now load without 500 errors.');
        
        return 0;
    }
}



