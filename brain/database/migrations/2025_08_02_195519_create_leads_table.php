<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Basic contact information
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            
            // Lead metadata
            $table->string('source')->default('unknown'); // leadsquotingfast, ringba, vici, twilio, allstate
            $table->string('type')->default('unknown'); // auto, home, etc.
            $table->string('status')->default('new'); // new, assigned, contacted, qualified, transferred_to_allstate, etc.
            $table->timestamp('received_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            
            // Assignment and routing
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('external_lead_id')->nullable(); // LQF lead ID, etc.
            
            // Complex data as JSON (drivers, vehicles, policies, etc.)
            $table->json('drivers')->nullable(); // Array of driver information
            $table->json('vehicles')->nullable(); // Array of vehicle information  
            $table->json('current_policy')->nullable(); // Current insurance policy info
            $table->json('requested_policy')->nullable(); // Requested coverage info
            $table->json('meta')->nullable(); // Trusted form, TCPA, etc.
            $table->json('payload')->nullable(); // Full original payload for debugging
            
            // Tracking and analytics
            $table->decimal('sell_price', 8, 2)->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('landing_page_url')->nullable();
            $table->boolean('tcpa_compliant')->default(false);
            
            // Allstate integration specific
            $table->string('allstate_transfer_id')->nullable();
            $table->timestamp('allstate_transferred_at')->nullable();
            $table->json('allstate_response')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['source', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['assigned_user_id', 'status']);
            $table->index('phone');
            $table->index('email');
            $table->index('external_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};