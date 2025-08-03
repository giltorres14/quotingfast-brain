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
        Schema::create('lead_conversions', function (Blueprint $table) {
            $table->id();
            
            // Lead relationship
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            
            // Vici call metrics relationship
            $table->unsignedBigInteger('vici_call_metrics_id')->nullable();
            $table->foreign('vici_call_metrics_id')->references('id')->on('vici_call_metrics')->onDelete('set null');
            
            // Ringba identifiers
            $table->string('ringba_call_id')->nullable();
            $table->string('ringba_campaign_id')->nullable();
            $table->string('ringba_publisher_id')->nullable();
            
            // Conversion details
            $table->boolean('converted')->default(false);
            $table->timestamp('conversion_time')->nullable();
            $table->string('buyer_name')->nullable(); // allstate, progressive, etc.
            $table->string('buyer_id')->nullable();
            $table->decimal('conversion_value', 10, 2)->nullable();
            $table->string('conversion_type')->nullable(); // sale, lead, etc.
            
            // Timing metrics
            $table->integer('time_to_first_call')->nullable(); // seconds from lead entry to first call
            $table->integer('time_to_transfer')->nullable(); // seconds from first call to transfer
            $table->integer('time_to_conversion')->nullable(); // seconds from transfer to conversion
            
            // Performance metrics
            $table->integer('total_call_attempts')->nullable();
            $table->integer('total_talk_time')->nullable(); // seconds
            $table->string('final_disposition')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('campaign_id')->nullable();
            
            // Raw data from Ringba
            $table->json('ringba_payload')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for reporting
            $table->index(['lead_id', 'converted']);
            $table->index(['converted', 'conversion_time']);
            $table->index(['buyer_name', 'conversion_time']);
            $table->index(['agent_id', 'conversion_time']);
            $table->index(['campaign_id', 'conversion_time']);
            $table->index('conversion_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_conversions');
    }
};