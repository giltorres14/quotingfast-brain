<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('domain')->nullable();
            $table->json('settings')->default('{}');
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index('domain');
            $table->index('status');
        });
        
        // Create tenant_users pivot table
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['owner', 'admin', 'member'])->default('member');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'user_id']);
            $table->index('user_id');
        });
        
        // Create tenant_integrations table for API credentials
        Schema::create('tenant_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('type'); // ringba, allstate, vici, etc.
            $table->string('name');
            $table->text('credentials'); // Encrypted JSON
            $table->json('settings')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'type']);
            $table->unique(['tenant_id', 'type', 'name']);
        });
        
        // Create subscription_plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 10, 2);
            $table->integer('included_leads');
            $table->decimal('overage_rate', 8, 4);
            $table->json('features');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Create tenant_subscriptions table
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained();
            $table->enum('status', ['active', 'cancelled', 'past_due', 'trialing'])->default('trialing');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
        });
        
        // Create tenant_usage table for tracking
        Schema::create('tenant_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('metric'); // leads_processed, api_calls, sms_sent
            $table->integer('count')->default(0);
            $table->date('period');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'metric', 'period']);
            $table->index('period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_usage');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('tenant_integrations');
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
    }
}

