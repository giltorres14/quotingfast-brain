<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Main A/B test tracking table
        Schema::create('ab_test_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->enum('test_group', ['A', 'B']);
            $table->string('test_name')->default('aggressive_vs_strategic');
            
            // Assignment details
            $table->timestamp('assigned_at');
            $table->integer('starting_list_id');
            
            // Journey tracking
            $table->integer('total_attempts')->default(0);
            $table->integer('day1_attempts')->default(0);
            $table->integer('week1_attempts')->default(0);
            $table->integer('week2_attempts')->default(0);
            
            // Key milestones
            $table->integer('first_contact_attempt')->nullable();
            $table->timestamp('first_contact_time')->nullable();
            $table->integer('conversion_attempt')->nullable();
            $table->timestamp('conversion_time')->nullable();
            
            // Outcomes
            $table->boolean('contacted')->default(false);
            $table->boolean('converted')->default(false);
            $table->boolean('dnc_requested')->default(false);
            $table->string('final_status')->nullable();
            $table->decimal('revenue', 10, 2)->default(0);
            
            // Cost tracking
            $table->decimal('total_cost', 8, 2)->default(0);
            $table->decimal('cost_per_minute', 8, 4)->default(0.50);
            
            $table->timestamps();
            $table->index(['lead_id', 'test_group']);
            $table->index('assigned_at');
        });

        // Detailed call-by-call log
        Schema::create('ab_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->enum('test_group', ['A', 'B']);
            $table->integer('attempt_number');
            $table->integer('list_id');
            
            // Call details
            $table->timestamp('call_time');
            $table->string('did_used', 20);
            $table->string('status', 20);
            $table->integer('talk_time')->default(0);
            $table->string('agent')->nullable();
            
            // Lead state at time of call
            $table->integer('hours_since_entry');
            $table->integer('days_since_entry');
            $table->integer('previous_attempts');
            
            // Outcomes
            $table->boolean('answered')->default(false);
            $table->boolean('contacted')->default(false);
            $table->boolean('positive_outcome')->default(false);
            
            $table->timestamps();
            $table->index(['lead_id', 'attempt_number']);
            $table->index(['test_group', 'status']);
            $table->index('call_time');
        });

        // Aggregated hourly stats for real-time monitoring
        Schema::create('ab_test_hourly_stats', function (Blueprint $table) {
            $table->id();
            $table->timestamp('hour_bucket');
            $table->enum('test_group', ['A', 'B']);
            
            // Volume metrics
            $table->integer('new_leads')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('unique_leads_called')->default(0);
            
            // Performance metrics
            $table->integer('contacts')->default(0);
            $table->integer('conversions')->default(0);
            $table->integer('dnc_requests')->default(0);
            
            // Rates (stored as percentages)
            $table->decimal('contact_rate', 5, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('answer_rate', 5, 2)->default(0);
            
            // Financial
            $table->decimal('hourly_cost', 10, 2)->default(0);
            $table->decimal('hourly_revenue', 10, 2)->default(0);
            $table->decimal('hourly_roi', 8, 2)->default(0);
            
            $table->timestamps();
            $table->unique(['hour_bucket', 'test_group']);
            $table->index('hour_bucket');
        });

        // Configuration for each test group
        Schema::create('ab_test_config', function (Blueprint $table) {
            $table->id();
            $table->string('test_name');
            $table->enum('group', ['A', 'B']);
            $table->string('strategy_name');
            $table->json('list_flow');  // [101, 102, 103...] or [201, 202, 203...]
            $table->json('timing_rules');  // When to move between lists
            $table->json('attempt_limits');  // Max attempts per period
            $table->boolean('active')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // Insert default configurations
        DB::table('ab_test_config')->insert([
            [
                'test_name' => 'aggressive_vs_strategic',
                'group' => 'A',
                'strategy_name' => 'Aggressive Front-Load',
                'list_flow' => json_encode([101, 102, 103, 104, 105, 106, 107, 108, 109]),
                'timing_rules' => json_encode([
                    'list_102' => '5 minutes',
                    'list_103' => '30 minutes', 
                    'list_104' => '2 hours',
                    'list_105' => 'next day',
                    'list_106' => 'day 3',
                    'list_107' => 'day 5',
                    'list_108' => 'day 7',
                    'list_109' => 'day 10'
                ]),
                'attempt_limits' => json_encode([
                    'day_1' => 8,
                    'week_1' => 20,
                    'week_2' => 10,
                    'total' => 35
                ]),
                'active' => true,
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_name' => 'aggressive_vs_strategic',
                'group' => 'B',
                'strategy_name' => 'Strategic Persistence',
                'list_flow' => json_encode([201, 202, 203, 204, 205, 206, 207]),
                'timing_rules' => json_encode([
                    'list_202' => '2 hours',
                    'list_203' => 'next day 9am',
                    'list_204' => 'day 3',
                    'list_205' => 'day 5',
                    'list_206' => 'day 8',
                    'list_207' => 'day 12'
                ]),
                'attempt_limits' => json_encode([
                    'day_1' => 3,
                    'week_1' => 10,
                    'week_2' => 5,
                    'total' => 18
                ]),
                'active' => true,
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('ab_test_config');
        Schema::dropIfExists('ab_test_hourly_stats');
        Schema::dropIfExists('ab_test_attempts');
        Schema::dropIfExists('ab_test_leads');
    }
};
