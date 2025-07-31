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
            $table->integer('vehicle_year')->nullable()->after('join_date');
            $table->string('vehicle_make')->nullable()->after('vehicle_year');
            $table->string('vehicle_model')->nullable()->after('vehicle_make');
            $table->string('vin')->nullable()->after('vehicle_model');
            $table->string('insurance_company')->nullable()->after('vin');
            $table->string('coverage_type')->nullable()->after('insurance_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_year',
                'vehicle_make',
                'vehicle_model',
                'vin',
                'insurance_company',
                'coverage_type',
            ]);
        });
    }
};
