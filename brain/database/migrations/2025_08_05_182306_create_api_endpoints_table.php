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
        Schema::create('api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('endpoint');
            $table->enum('method', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']);
            $table->enum('type', ['webhook', 'api', 'test']);
            $table->enum('status', ['active', 'inactive', 'testing']);
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // For feature list
            $table->string('test_url')->nullable(); // For test button
            $table->string('category')->default('general'); // For organization
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->boolean('is_system')->default(false); // System vs user-created
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['category', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_endpoints');
    }
};
