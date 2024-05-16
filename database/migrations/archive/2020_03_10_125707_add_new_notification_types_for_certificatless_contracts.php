<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATIONS = [
        104 => 'Уведомление о задаче Контроль наличия сертификатов',
        105 => 'Уведомление о создании задачи Контроль наличия сертификатов',
        106 => 'Уведомление о существовании операций без сертификатов',
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
                'group' => 7,
                'name' => $name,
                'for_everyone' => 0,
            ];
        }

        DB::table('notification_types')->insert($new_types);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 104,
                'group_id' => 15,
            ],
            [
                'notification_id' => 105,
                'group_id' => 5,
            ],
            [
                'notification_id' => 105,
                'group_id' => 6,
            ],
            [
                'notification_id' => 106,
                'group_id' => 8,
            ],
            [
                'notification_id' => 106,
                'group_id' => 13,
            ],
            [
                'notification_id' => 106,
                'group_id' => 14,
            ],
            [
                'notification_id' => 106,
                'group_id' => 15,
            ],
            [
                'notification_id' => 106,
                'group_id' => 17,
            ],
            [
                'notification_id' => 106,
                'group_id' => 19,
            ],
            [
                'notification_id' => 106,
                'group_id' => 23,
            ],
            [
                'notification_id' => 106,
                'group_id' => 27,
            ],
            [
                'notification_id' => 106,
                'group_id' => 31,
            ],
            [
                'notification_id' => 106,
                'group_id' => 35,
            ],
            [
                'notification_id' => 106,
                'group_id' => 39,
            ],
            [
                'notification_id' => 106,
                'group_id' => 40,
            ],
            [
                'notification_id' => 106,
                'group_id' => 41,
            ],
            [
                'notification_id' => 106,
                'group_id' => 42,
            ],
            [
                'notification_id' => 106,
                'group_id' => 43,
            ],
            [
                'notification_id' => 106,
                'group_id' => 44,
            ],
            [
                'notification_id' => 106,
                'group_id' => 45,
            ],
            [
                'notification_id' => 106,
                'group_id' => 46,
            ],
            [
                'notification_id' => 106,
                'group_id' => 47,
            ],
            [
                'notification_id' => 106,
                'group_id' => 48,
            ],
            [
                'notification_id' => 106,
                'group_id' => 52,
            ],
            [
                'notification_id' => 106,
                'group_id' => 53,
            ],
            [
                'notification_id' => 106,
                'group_id' => 54,
            ],
        ]);

        DB::table('notifications_for_users')->insert([
            [
                'notification_id' => 106,
                'user_id' => User::HARDCODED_PERSONS['certificateWorker'],
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
};
