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
        Schema::create('buyer_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->string('contract_version')->default('1.0'); // Track contract versions
            $table->text('contract_content'); // Store the contract text/HTML
            $table->timestamp('signed_at');
            $table->string('signature_ip');
            $table->string('signature_method')->default('electronic'); // electronic, docusign, etc.
            $table->json('signature_data')->nullable(); // Store signature details
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['buyer_id', 'is_active']);
            $table->index(['contract_version', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_contracts');
    }
};
