<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViciFieldsToLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Add Vici-related fields if they don't exist
            if (!Schema::hasColumn('leads', 'vici_lead_id')) {
                $table->string('vici_lead_id')->nullable()->after('external_lead_id')
                    ->comment('ViciDial lead ID from vicidial_list table');
            }
            
            if (!Schema::hasColumn('leads', 'vici_pushed_at')) {
                $table->timestamp('vici_pushed_at')->nullable()->after('vici_lead_id')
                    ->comment('Timestamp when lead was pushed to ViciDial');
            }
            
            if (!Schema::hasColumn('leads', 'vici_list_id')) {
                $table->string('vici_list_id')->nullable()->after('vici_pushed_at')
                    ->comment('Current ViciDial list ID (101-111)');
            }
            
            if (!Schema::hasColumn('leads', 'vici_campaign')) {
                $table->string('vici_campaign')->nullable()->after('vici_list_id')
                    ->comment('ViciDial campaign (AUTODIAL or AUTO2)');
            }
            
            if (!Schema::hasColumn('leads', 'tcpajoin_date')) {
                $table->date('tcpajoin_date')->nullable()->after('vici_campaign')
                    ->comment('TCPA consent date for compliance');
            }
            
            // Add indexes for better query performance
            $table->index('vici_lead_id', 'idx_vici_lead_id');
            $table->index('vici_list_id', 'idx_vici_list_id');
            $table->index('tcpajoin_date', 'idx_tcpajoin_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_vici_lead_id');
            $table->dropIndex('idx_vici_list_id');
            $table->dropIndex('idx_tcpajoin_date');
            
            // Drop columns
            $table->dropColumn([
                'vici_lead_id',
                'vici_pushed_at',
                'vici_list_id',
                'vici_campaign',
                'tcpajoin_date'
            ]);
        });
    }
}
