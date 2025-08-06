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
        Schema::create('allstate_test_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('external_lead_id')->nullable();
            $table->string('lead_name');
            $table->string('lead_type');
            $table->string('lead_phone');
            $table->string('lead_email')->nullable();
            
            // Auto-qualification tracking
            $table->json('qualification_data'); // The auto-generated 12 questions
            $table->json('data_sources'); // Where each field came from (payload, smart logic, defaults)
            
            // Allstate API call tracking
            $table->json('allstate_payload'); // What was sent to Allstate
            $table->string('allstate_endpoint'); // Which endpoint was used
            $table->integer('response_status'); // HTTP status code
            $table->json('allstate_response'); // Full API response
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->json('validation_errors')->nullable(); // Specific field validation errors
            
            // Timing
            $table->timestamp('sent_at');
            $table->integer('response_time_ms')->nullable(); // How long the API call took
            
            // Testing metadata
            $table->string('test_environment')->default('testing'); // testing or production
            $table->string('test_session')->nullable(); // Group related tests
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for dashboard queries
            $table->index(['success', 'sent_at']);
            $table->index(['test_environment', 'sent_at']);
            $table->index('external_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allstate_test_logs');
    }
};
