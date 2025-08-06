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
        Schema::table('leads', function (Blueprint $table) {
            // RingBA API tracking
            $table->timestamp('ringba_sent_at')->nullable()->after('updated_at');
            $table->string('ringba_url')->nullable()->after('ringba_sent_at');
            $table->json('ringba_response')->nullable()->after('ringba_url');
            $table->string('ringba_status')->nullable()->after('ringba_response'); // sent, failed, pending
            $table->string('ringba_call_id')->nullable()->after('ringba_status');
            $table->decimal('ringba_revenue', 10, 2)->nullable()->after('ringba_call_id');
            
            // Agent qualification tracking
            $table->json('qualification_data')->nullable()->after('ringba_revenue');
            $table->timestamp('qualified_at')->nullable()->after('qualification_data');
            $table->unsignedBigInteger('qualified_by')->nullable()->after('qualified_at');
            
            // Lead scoring
            $table->integer('lead_score')->default(5)->after('qualified_by');
            $table->string('urgency_level')->nullable()->after('lead_score'); // immediate, 30_days, 60_days, just_shopping
            
            // Add indexes for performance
            $table->index('ringba_status');
            $table->index('qualified_at');
            $table->index('lead_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'ringba_sent_at',
                'ringba_url', 
                'ringba_response',
                'ringba_status',
                'ringba_call_id',
                'ringba_revenue',
                'qualification_data',
                'qualified_at',
                'qualified_by',
                'lead_score',
                'urgency_level'
            ]);
        });
    }
};