<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDidHealthTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Main DID health monitoring table
        Schema::create('did_health_monitor', function (Blueprint $table) {
            $table->id();
            $table->string('did_number', 20)->unique();
            $table->string('area_code', 5)->index();
            $table->string('state', 2)->index();
            $table->string('campaign_id', 20)->nullable();
            
            // Daily metrics
            $table->date('date')->index();
            $table->integer('total_calls')->default(0);
            $table->integer('answered_calls')->default(0);
            $table->decimal('answer_rate', 5, 2)->default(0);
            $table->integer('avg_talk_time')->default(0);
            
            // Health scoring
            $table->integer('health_score')->default(100);
            $table->enum('spam_risk_level', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('LOW');
            $table->timestamp('last_spam_check')->nullable();
            
            // Rotation status
            $table->enum('status', ['ACTIVE', 'WARNING', 'NEEDS_REST', 'RESTING', 'RETIRED'])->default('ACTIVE');
            $table->date('rest_start_date')->nullable();
            $table->date('rest_end_date')->nullable();
            
            // Historical tracking
            $table->integer('lifetime_calls')->default(0);
            $table->decimal('lifetime_answer_rate', 5, 2)->default(0);
            $table->date('first_used')->nullable();
            $table->date('last_used')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['date', 'status']);
            $table->index(['health_score', 'status']);
            $table->index(['spam_risk_level', 'status']);
        });
        
        // Daily performance tracking
        Schema::create('did_daily_performance', function (Blueprint $table) {
            $table->id();
            $table->string('did_number', 20);
            $table->date('date');
            $table->integer('hour');
            $table->integer('calls_made')->default(0);
            $table->integer('calls_answered')->default(0);
            $table->decimal('answer_rate', 5, 2)->default(0);
            $table->integer('avg_ring_time')->default(0);
            $table->timestamps();
            
            $table->unique(['did_number', 'date', 'hour'], 'unique_did_date_hour');
            $table->index(['date', 'hour']);
        });
        
        // DID rotation pool management
        Schema::create('did_rotation_pool', function (Blueprint $table) {
            $table->id();
            $table->string('did_number', 20)->unique();
            $table->string('area_code', 5)->index();
            $table->string('state', 2)->index();
            $table->string('provider', 50)->nullable();
            $table->decimal('monthly_cost', 10, 2)->nullable();
            
            $table->enum('pool_status', ['AVAILABLE', 'IN_USE', 'RESTING', 'NEEDS_REPLACEMENT'])->default('AVAILABLE');
            $table->string('assigned_campaign', 20)->nullable();
            
            $table->integer('spam_reports')->default(0);
            $table->date('last_spam_report')->nullable();
            $table->integer('replacement_priority')->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('pool_status');
            $table->index(['area_code', 'pool_status']);
        });
        
        // DID alert history
        Schema::create('did_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('did_number', 20)->nullable();
            $table->enum('alert_type', ['SPAM_DETECTED', 'OVERUSE', 'LOW_ANSWER_RATE', 'COVERAGE_GAP', 'ROTATION_NEEDED']);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->text('message');
            $table->json('metrics')->nullable();
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('acknowledged_by')->nullable();
            $table->timestamps();
            
            $table->index(['created_at', 'severity']);
            $table->index(['acknowledged', 'severity']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('did_alerts');
        Schema::dropIfExists('did_rotation_pool');
        Schema::dropIfExists('did_daily_performance');
        Schema::dropIfExists('did_health_monitor');
    }
}




