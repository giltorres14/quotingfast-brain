<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable();
            $table->index('state');
            $table->index('source');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['state']);
            $table->dropIndex(['source']);
            $table->dropIndex(['type']);
            $table->dropColumn('type');
            $table->dropColumn('received_at');
        });
    }
}; 