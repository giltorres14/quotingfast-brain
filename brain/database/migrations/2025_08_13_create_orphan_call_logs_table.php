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
        Schema::create('orphan_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable()->index();
            $table->string('vendor_lead_code')->nullable()->index();
            $table->string('vici_lead_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('status')->nullable();
            $table->string('disposition')->nullable();
            $table->timestamp('call_date')->nullable();
            $table->integer('talk_time')->default(0);
            $table->json('call_data')->nullable();
            
            // Matching fields
            $table->unsignedBigInteger('matched_lead_id')->nullable()->index();
            $table->timestamp('matched_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for matching
            $table->index(['matched_lead_id', 'matched_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orphan_call_logs');
    }
};



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
        Schema::create('orphan_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable()->index();
            $table->string('vendor_lead_code')->nullable()->index();
            $table->string('vici_lead_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('status')->nullable();
            $table->string('disposition')->nullable();
            $table->timestamp('call_date')->nullable();
            $table->integer('talk_time')->default(0);
            $table->json('call_data')->nullable();
            
            // Matching fields
            $table->unsignedBigInteger('matched_lead_id')->nullable()->index();
            $table->timestamp('matched_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for matching
            $table->index(['matched_lead_id', 'matched_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orphan_call_logs');
    }
};


