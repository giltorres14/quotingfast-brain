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
        Schema::create('vici_call_metrics', function (Blueprint $table) {
            $table->id();
            
            // Lead relationship
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            
            // Vici identifiers
            $table->string('vici_lead_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('list_id')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('phone_number')->nullable();
            
            // Call tracking
            $table->string('call_status')->nullable(); // QUEUE, INCALL, PAUSED, etc.
            $table->string('disposition')->nullable(); // SALE, NI, B, etc.
            $table->integer('call_attempts')->default(0);
            $table->timestamp('first_call_time')->nullable();
            $table->timestamp('last_call_time')->nullable();
            $table->timestamp('connected_time')->nullable();
            $table->timestamp('hangup_time')->nullable();
            $table->integer('call_duration')->nullable(); // seconds
            $table->integer('talk_time')->nullable(); // seconds
            
            // Transfer tracking
            $table->boolean('transfer_requested')->default(false);
            $table->timestamp('transfer_time')->nullable();
            $table->string('transfer_destination')->nullable(); // ringba, allstate, etc.
            $table->string('transfer_status')->nullable();
            
            // Metrics
            $table->decimal('connection_rate', 5, 2)->nullable();
            $table->decimal('transfer_rate', 5, 2)->nullable();
            $table->json('call_history')->nullable(); // Array of all call attempts
            
            // Raw data
            $table->json('vici_payload')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['lead_id', 'created_at']);
            $table->index(['agent_id', 'created_at']);
            $table->index(['campaign_id', 'created_at']);
            $table->index('call_status');
            $table->index('disposition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vici_call_metrics');
    }
};