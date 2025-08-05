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
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->decimal('auto_reload_amount', 10, 2)->nullable();
            $table->decimal('auto_reload_threshold', 10, 2)->nullable();
            $table->boolean('auto_reload_enabled')->default(false);
            $table->json('permissions')->nullable(); // Store permissions as JSON
            $table->boolean('contract_signed')->default(false);
            $table->timestamp('contract_signed_at')->nullable();
            $table->string('contract_ip')->nullable();
            $table->json('preferences')->nullable(); // Lead preferences, notifications, etc.
            $table->timestamp('last_login_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('email');
            $table->index(['balance', 'auto_reload_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
