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
            // Add missing LQF fields
            if (!Schema::hasColumn('leads', 'jangle_lead_id')) {
                $table->string('jangle_lead_id')->nullable()->after('external_lead_id');
            }
            if (!Schema::hasColumn('leads', 'leadid_code')) {
                $table->string('leadid_code')->nullable()->after('jangle_lead_id');
            }
            if (!Schema::hasColumn('leads', 'trusted_form_cert')) {
                $table->text('trusted_form_cert')->nullable()->after('leadid_code');
            }
            if (!Schema::hasColumn('leads', 'tcpa_consent_text')) {
                $table->text('tcpa_consent_text')->nullable()->after('tcpa_compliant');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['jangle_lead_id', 'leadid_code', 'trusted_form_cert', 'tcpa_consent_text']);
        });
    }
};
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['jangle_lead_id', 'leadid_code', 'trusted_form_cert', 'tcpa_consent_text']);
        });
    }
};
