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
        Schema::table('buyers', function (Blueprint $table) {
            $table->boolean('is_test_account')->default(false)->after('status');
            $table->string('account_type')->nullable()->after('is_test_account'); // realistic, demo, minimal
            $table->string('created_via')->nullable()->after('account_type'); // admin_dummy, signup, import
            $table->index('is_test_account');
            $table->index(['is_test_account', 'account_type']);
        });
        
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('is_sample_data')->default(false)->after('payload');
            $table->index('is_sample_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropIndex(['buyers_is_test_account_index']);
            $table->dropIndex(['buyers_is_test_account_account_type_index']);
            $table->dropColumn(['is_test_account', 'account_type', 'created_via']);
        });
        
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['leads_is_sample_data_index']);
            $table->dropColumn('is_sample_data');
        });
    }
};