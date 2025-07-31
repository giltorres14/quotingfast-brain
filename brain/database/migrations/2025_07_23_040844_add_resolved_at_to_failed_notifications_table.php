<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResolvedAtToFailedNotificationsTable extends Migration
{
    public function up()
    {
        Schema::table('failed_notifications', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->after('last_attempt_at');
        });
    }

    public function down()
    {
        Schema::table('failed_notifications', function (Blueprint $table) {
            $table->dropColumn('resolved_at');
        });
    }
}
