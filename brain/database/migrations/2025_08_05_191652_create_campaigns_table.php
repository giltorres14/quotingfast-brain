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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_id')->unique()->index(); // The actual campaign ID from leads
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'auto_detected'])->default('active');
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_lead_received_at')->nullable();
            $table->integer('total_leads')->default(0);
            $table->boolean('is_auto_created')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'is_auto_created']);
            $table->index('last_lead_received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
