<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTenantIdToExistingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create default tenant for existing data
        $tenantId = DB::table('tenants')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'QuotingFast',
            'slug' => 'quotingfast',
            'domain' => 'quotingfast-brain-ohio.onrender.com',
            'settings' => json_encode([
                'branding' => [
                    'logo_url' => 'https://quotingfast.com/logoqf0704.png',
                    'company_name' => 'QuotingFast',
                    'primary_color' => '#4f46e5',
                    'secondary_color' => '#764ba2'
                ],
                'features' => [
                    'vici_integration' => true,
                    'ringba_integration' => true,
                    'allstate_api' => true,
                    'sms_center' => true,
                    'max_leads_per_month' => 100000
                ]
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Add tenant_id to leads table
        Schema::table('leads', function (Blueprint $table) use ($tenantId) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });
        
        // Set default tenant for existing leads
        DB::table('leads')->update(['tenant_id' => $tenantId]);
        
        // Make tenant_id required
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
        
        // Add tenant_id to campaigns table if it exists
        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) use ($tenantId) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
            
            DB::table('campaigns')->update(['tenant_id' => $tenantId]);
            
            Schema::table('campaigns', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
        
        // Add tenant_id to webhooks table if it exists
        if (Schema::hasTable('webhooks')) {
            Schema::table('webhooks', function (Blueprint $table) use ($tenantId) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
            
            DB::table('webhooks')->update(['tenant_id' => $tenantId]);
            
            Schema::table('webhooks', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
        
        // Add tenant_id to test_logs table if it exists
        if (Schema::hasTable('test_logs')) {
            Schema::table('test_logs', function (Blueprint $table) use ($tenantId) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
            
            DB::table('test_logs')->update(['tenant_id' => $tenantId]);
            
            Schema::table('test_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
        
        // Add tenant_id to lead_queue table if it exists
        if (Schema::hasTable('lead_queue')) {
            Schema::table('lead_queue', function (Blueprint $table) use ($tenantId) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
            
            DB::table('lead_queue')->update(['tenant_id' => $tenantId]);
            
            Schema::table('lead_queue', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove tenant_id from all tables
        $tables = ['leads', 'campaigns', 'webhooks', 'test_logs', 'lead_queue'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
        
        // Delete the default tenant (will cascade delete all data due to foreign keys)
        DB::table('tenants')->where('slug', 'quotingfast')->delete();
    }
}
