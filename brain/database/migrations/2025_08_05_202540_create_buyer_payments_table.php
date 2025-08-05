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
        Schema::create('buyer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique(); // External payment processor ID
            $table->enum('type', ['deposit', 'auto_reload', 'refund', 'charge']); // Payment type
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable(); // card, ach, etc.
            $table->string('payment_processor')->nullable(); // stripe, paypal, etc.
            $table->json('processor_response')->nullable(); // Store response from payment processor
            $table->text('description')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['buyer_id', 'created_at']);
            $table->index(['status', 'type']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_payments');
    }
};
