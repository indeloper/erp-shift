<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNotificationTypeForDefectControlTasks extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о задаче Контроль неисправности техники';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            [
                'id' => 75,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 75,
                'group_id' => 46,
            ],
            [
                'notification_id' => 75,
                'group_id' => 47,
            ],
            [
                'notification_id' => 75,
                'group_id' => 48,
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

        DB::table('notification_types')->where('name', self::NOTIFICATION_NAME)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 75)->delete();

        DB::commit();
    }
}
