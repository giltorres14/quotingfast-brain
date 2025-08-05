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
        Schema::create('lead_outcomes', function (Blueprint $table) {
            $table->id();
            
            // Lead identification
            $table->unsignedBigInteger('lead_id')->index();
            $table->unsignedBigInteger('buyer_id')->index();
            $table->string('external_lead_id')->index(); // Our lead ID
            $table->string('crm_lead_id')->nullable()->index(); // CRM's lead ID
            
            // Status tracking
            $table->enum('status', [
                'new',
                'contacted', 
                'qualified',
                'proposal_sent',
                'negotiating',
                'closed_won',
                'closed_lost',
                'not_interested',
                'bad_lead',
                'duplicate'
            ])->default('new')->index();
            
            $table->enum('outcome', [
                'pending',
                'sold',
                'not_sold', 
                'bad_lead',
                'duplicate'
            ])->default('pending')->index();
            
            // Financial tracking
            $table->decimal('sale_amount', 10, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            
            // Quality tracking
            $table->tinyInteger('quality_rating')->nullable(); // 1-5 stars
            $table->integer('contact_attempts')->default(0);
            
            // Timeline tracking
            $table->timestamp('first_contact_at')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Feedback and notes
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            
            // Source tracking
            $table->string('source_system')->nullable(); // Which CRM sent this
            $table->enum('reported_via', [
                'webhook',
                'api',
                'manual',
                'csv_import',
                'email'
            ])->default('webhook');
            
            // Additional data
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('buyers')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['buyer_id', 'status']);
            $table->index(['buyer_id', 'outcome']);
            $table->index(['status', 'created_at']);
            $table->index(['outcome', 'created_at']);
            $table->index(['quality_rating', 'created_at']);
            $table->index(['closed_at']);
            
            // Unique constraint to prevent duplicate outcomes for same lead
            $table->unique(['lead_id', 'buyer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_outcomes');
    }
};