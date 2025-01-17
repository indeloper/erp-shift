<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 101;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $new_types = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 11,
            'name' => 'Уведомление о создании задачи Контроль явки',
            'for_everyone' => 0, // for groups
        ];

        DB::table('notification_types')->insert($new_types);

        $notification_groups = [
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 8,
            ],
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 13,
            ],
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 19,
            ],
            [
                'notification_id' => self::NOTIFICATION_TYPE,
                'group_id' => 27,
            ],
        ];

        DB::table('notifications_for_groups')->insert($notification_groups);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('id', self::NOTIFICATION_TYPE)->delete();
        DB::table('notifications_for_groups')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
