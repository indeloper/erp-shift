<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewMatAccNotificationInNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Material Accounting notifications
            [
                'id' => 64,
                'group' => 2,
                'name' => 'Уведомление об обновлении запроса на создание операции',
                'for_everyone' => 0
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            // Material Accounting notifications
            [
                'notification_id' => 64,
                'group_id' => 8,
            ],
            [
                'notification_id' => 64,
                'group_id' => 19,
            ],
            [
                'notification_id' => 64,
                'group_id' => 27,
            ],
        ]);

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

        DB::table('notification_types')->where('id', 64)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 64)->delete();

        DB::commit();
    }
}
