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
        Schema::create('buyer_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('external_lead_id'); // The 9-digit lead ID
            $table->enum('vertical', ['auto', 'home']); // Lead type
            $table->decimal('price', 8, 2); // Price paid for the lead
            $table->enum('status', ['delivered', 'returned', 'disputed'])->default('delivered');
            $table->string('return_reason')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamp('delivered_at');
            $table->timestamp('returned_at')->nullable();
            $table->json('lead_data'); // Store lead details as JSON
            $table->decimal('refund_amount', 8, 2)->nullable();
            $table->timestamp('refund_processed_at')->nullable();
            $table->boolean('quality_scored')->default(false);
            $table->integer('quality_rating')->nullable(); // 1-5 rating
            $table->text('quality_feedback')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['buyer_id', 'created_at']);
            $table->index(['status', 'delivered_at']);
            $table->index('external_lead_id');
            $table->index(['vertical', 'created_at']);
            
            // Unique constraint to prevent duplicate lead assignments
            $table->unique(['buyer_id', 'lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_leads');
    }
};
