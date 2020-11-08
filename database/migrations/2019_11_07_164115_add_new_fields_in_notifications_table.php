<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsInNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('is_showing')->default(1)->after('is_seen');
            $table->unsignedInteger('type')->nullable()->after('is_showing');
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['is_showing', 'type']);
        });

        DB::commit();
    }
}
