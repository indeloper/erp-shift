<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о создании задачи Назначение исполнителя заявки на неисправность техники';

    const SECOND_NOTIFICATION_NAME = 'Уведомление о назначении исполнителя заявки на неисправность техники';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Tech Accounting Notifications
            [
                'id' => 66,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
            [
                'id' => 67,
                'group' => 10,
                'name' => self::SECOND_NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 66,
                'group_id' => 47,
            ],
            [
                'notification_id' => 67,
                'group_id' => 5,
            ],
            [
                'notification_id' => 67,
                'group_id' => 6,
            ],
            [
                'notification_id' => 67,
                'group_id' => 13,
            ],
            [
                'notification_id' => 67,
                'group_id' => 19,
            ],
            [
                'notification_id' => 67,
                'group_id' => 27,
            ],
            [
                'notification_id' => 67,
                'group_id' => 47,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('name', self::NOTIFICATION_NAME)->delete();
        DB::table('notification_types')->where('name', self::SECOND_NOTIFICATION_NAME)->delete();

        DB::table('notifications_for_groups')->where('notification_id', 66)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 67)->delete();

        DB::commit();
    }
};
