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
            // Vendor Information
            $table->string('vendor_name')->nullable()->after('source');
            $table->string('vendor_campaign')->nullable()->after('vendor_name');
            $table->decimal('cost', 10, 2)->nullable()->after('vendor_campaign');
            
            // Buyer Information (some may exist, adding if not)
            if (!Schema::hasColumn('leads', 'buyer_name')) {
                $table->string('buyer_name')->nullable()->after('cost');
            }
            if (!Schema::hasColumn('leads', 'buyer_campaign')) {
                $table->string('buyer_campaign')->nullable()->after('buyer_name');
            }
            $table->decimal('sell_price', 10, 2)->nullable()->after('buyer_campaign');
            
            // TCPA Information
            $table->string('tcpa_lead_id')->nullable()->after('external_lead_id');
            $table->string('trusted_form_cert')->nullable()->after('tcpa_lead_id');
            $table->boolean('tcpa_compliant')->default(false)->after('trusted_form_cert');
            
            // Indexes for performance
            $table->index('vendor_name');
            $table->index('buyer_name');
            $table->index('tcpa_compliant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_name',
                'vendor_campaign', 
                'cost',
                'sell_price',
                'tcpa_lead_id',
                'trusted_form_cert',
                'tcpa_compliant'
            ]);
            
            // Only drop if we created them
            if (Schema::hasColumn('leads', 'buyer_name')) {
                $table->dropColumn('buyer_name');
            }
            if (Schema::hasColumn('leads', 'buyer_campaign')) {
                $table->dropColumn('buyer_campaign');
            }
        });
    }
};

