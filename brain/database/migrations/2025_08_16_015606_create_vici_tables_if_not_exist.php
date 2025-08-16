<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViciTablesIfNotExist extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vici_call_metrics')) {
            Schema::create('vici_call_metrics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('vendor_lead_code')->nullable();
                $table->string('uniqueid')->nullable();
                $table->timestamp('call_date')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('status')->nullable();
                $table->string('user')->nullable();
                $table->string('campaign_id')->nullable();
                $table->integer('list_id')->nullable();
                $table->integer('length_in_sec')->nullable();
                $table->string('call_status')->nullable();
                $table->unsignedBigInteger('matched_lead_id')->nullable();
                $table->timestamps();
                
                $table->index('vendor_lead_code');
                $table->index('matched_lead_id');
                $table->index('call_date');
            });
        }
        
        if (!Schema::hasTable('orphan_call_logs')) {
            Schema::create('orphan_call_logs', function (Blueprint $table) {
                $table->id();
                $table->string('uniqueid')->nullable();
                $table->string('lead_id')->nullable();
                $table->integer('list_id')->nullable();
                $table->string('campaign_id')->nullable();
                $table->timestamp('call_date')->nullable();
                $table->bigInteger('start_epoch')->nullable();
                $table->bigInteger('end_epoch')->nullable();
                $table->integer('length_in_sec')->nullable();
                $table->string('status')->nullable();
                $table->string('phone_code')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('user')->nullable();
                $table->text('comments')->nullable();
                $table->boolean('processed')->default(false);
                $table->string('term_reason')->nullable();
                $table->string('vendor_lead_code')->nullable();
                $table->string('source_id')->nullable();
                $table->boolean('matched')->default(false);
                $table->unsignedBigInteger('matched_lead_id')->nullable();
                $table->timestamps();
                
                $table->index('vendor_lead_code');
                $table->index('matched');
                $table->index('phone_number');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('vici_call_metrics');
        Schema::dropIfExists('orphan_call_logs');
    }
}