<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddWorkTimeControlTaskExceedanceNotification extends Migration
{
    const NOTIFICATIONS = [
        108 => 'Уведомление о возможно неправильном заполнении суточного табеля',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $new_types = [];
        foreach (self::NOTIFICATIONS as $id => $name) {
            $new_types[] = [
                'id' => $id,
                'group' => 11,
                'name' => $name,
                'for_everyone' => 0
            ];
        }

        DB::table('notification_types')->insert($new_types);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 108,
                'group_id' => 8
            ],
            [
                'notification_id' => 108,
                'group_id' => 13
            ],
            [
                'notification_id' => 108,
                'group_id' => 19
            ],
            [
                'notification_id' => 108,
                'group_id' => 27
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

        DB::table('notification_types')->whereIn('id', array_keys(self::NOTIFICATIONS))->delete();
        DB::table('notifications_for_groups')->whereIn('notification_id', array_keys(self::NOTIFICATIONS))->delete();
        DB::table('notifications_for_users')->whereIn('notification_id', array_keys(self::NOTIFICATIONS))->delete();

        DB::commit();
    }
}
