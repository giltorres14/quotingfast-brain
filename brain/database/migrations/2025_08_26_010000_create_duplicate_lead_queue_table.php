<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_lead_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone_normalized', 16)->index();
            $table->string('vendor')->nullable();
            $table->string('source')->nullable();
            $table->longText('payload_json');
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('original_lead_id')->nullable();
            $table->string('original_external_lead_id', 32)->nullable();
            $table->timestamp('original_received_at')->nullable();
            $table->integer('days_since_original')->nullable();
            $table->string('match_reason', 24)->default('phone');
            $table->string('status', 32)->default('pending');
            $table->string('decision_by')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->string('applied_action')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_lead_queue');
    }
};



