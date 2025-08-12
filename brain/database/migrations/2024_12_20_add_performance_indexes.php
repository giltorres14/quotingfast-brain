<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add performance indexes
     */
    public function up(): void
    {
        // Add indexes to leads table for faster queries
        Schema::table('leads', function (Blueprint $table) {
            // Phone is used for duplicate checking
            if (!Schema::hasIndex('leads', 'leads_phone_index')) {
                $table->index('phone');
            }
            
            // External lead ID for Vici lookups
            if (!Schema::hasIndex('leads', 'leads_external_lead_id_index')) {
                $table->index('external_lead_id');
            }
            
            // Source and type for filtering
            if (!Schema::hasIndex('leads', 'leads_source_type_index')) {
                $table->index(['source', 'type']);
            }
            
            // Status for workflow queries
            if (!Schema::hasIndex('leads', 'leads_status_index')) {
                $table->index('status');
            }
            
            // Created at for date range queries
            if (!Schema::hasIndex('leads', 'leads_created_at_index')) {
                $table->index('created_at');
            }
            
            // Vendor and buyer for business analytics
            if (!Schema::hasIndex('leads', 'leads_vendor_name_index')) {
                $table->index('vendor_name');
            }
            if (!Schema::hasIndex('leads', 'leads_buyer_name_index')) {
                $table->index('buyer_name');
            }
        });
        
        // Add indexes to vici_call_metrics for faster joins
        Schema::table('vici_call_metrics', function (Blueprint $table) {
            if (!Schema::hasIndex('vici_call_metrics', 'vici_call_metrics_lead_id_index')) {
                $table->index('lead_id');
            }
            if (!Schema::hasIndex('vici_call_metrics', 'vici_call_metrics_phone_number_index')) {
                $table->index('phone_number');
            }
            if (!Schema::hasIndex('vici_call_metrics', 'vici_call_metrics_status_index')) {
                $table->index('status');
            }
        });
        
        // Add indexes to buyers table
        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasIndex('buyers', 'buyers_name_index')) {
                $table->index('name');
            }
            if (!Schema::hasIndex('buyers', 'buyers_active_index')) {
                $table->index('active');
            }
        });
        
        // Add indexes to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasIndex('vendors', 'vendors_name_index')) {
                $table->index('name');
            }
            if (!Schema::hasIndex('vendors', 'vendors_active_index')) {
                $table->index('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['external_lead_id']);
            $table->dropIndex(['source', 'type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['vendor_name']);
            $table->dropIndex(['buyer_name']);
        });
        
        Schema::table('vici_call_metrics', function (Blueprint $table) {
            $table->dropIndex(['lead_id']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['status']);
        });
        
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['active']);
        });
        
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['active']);
        });
    }
};
