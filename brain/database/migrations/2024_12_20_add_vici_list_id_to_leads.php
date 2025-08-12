<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add vici_list_id for tracking which Vici list a lead is in
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'vici_list_id')) {
                $table->integer('vici_list_id')->nullable()->after('external_lead_id')
                      ->comment('Vici list ID (101=New, 102=Retry, 103=Callback, etc.)');
                
                // Add index for faster queries
                $table->index('vici_list_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'vici_list_id')) {
                $table->dropIndex(['vici_list_id']);
                $table->dropColumn('vici_list_id');
            }
        });
    }
};
