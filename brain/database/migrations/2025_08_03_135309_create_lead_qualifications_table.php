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
        Schema::create('lead_qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('lead_id')->index();
            
            // Insurance Questions
            $table->string('currently_insured')->nullable();
            $table->string('current_provider')->nullable();
            $table->string('insurance_duration')->nullable();
            
            // License Question
            $table->string('active_license')->nullable();
            
            // Risk Level Questions
            $table->string('dui_sr22')->nullable();
            $table->string('dui_timeframe')->nullable();
            
            // Address Questions
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            
            // Auto Question
            $table->string('num_vehicles')->nullable();
            
            // Home Ownership
            $table->string('home_status')->nullable();
            
            // Competitive Quote
            $table->string('allstate_quote')->nullable();
            
            // Intent
            $table->string('ready_to_speak')->nullable();
            
            // Enrichment tracking
            $table->string('enrichment_type')->nullable(); // insured, uninsured, homeowner
            $table->timestamp('enriched_at')->nullable();
            $table->json('enrichment_data')->nullable(); // Store the data sent to Ringba
            
            $table->timestamps();
            
            // Foreign key constraint if leads table exists
            // $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_qualifications');
    }
};