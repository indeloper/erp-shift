<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponsibleAssignmentNotification extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о создании задачи Назначение исполнителя заявки на неисправность техники';
    const SECOND_NOTIFICATION_NAME = 'Уведомление о назначении исполнителя заявки на неисправность техники';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Tech Accounting Notifications
            [
                'id' => 66,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0 // for groups
            ],
            [
                'id' => 67,
                'group' => 10,
                'name' => self::SECOND_NOTIFICATION_NAME,
                'for_everyone' => 0 // for groups
            ]
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 66,
                'group_id' => 47
            ],
            [
                'notification_id' => 67,
                'group_id' => 5
            ],
            [
                'notification_id' => 67,
                'group_id' => 6
            ],
            [
                'notification_id' => 67,
                'group_id' => 13
            ],
            [
                'notification_id' => 67,
                'group_id' => 19
            ],
            [
                'notification_id' => 67,
                'group_id' => 27
            ],
            [
                'notification_id' => 67,
                'group_id' => 47
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
        DB::table('notification_types')->where('name', self::SECOND_NOTIFICATION_NAME)->delete();

        DB::table('notifications_for_groups')->where('notification_id', 66)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 67)->delete();

        DB::commit();
    }
}