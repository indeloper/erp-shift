<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о новом комментарии к заявке на неисправность';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            [
                'id' => 76,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 76,
                'group_id' => 5,
            ],
            [
                'notification_id' => 76,
                'group_id' => 6,
            ],
            [
                'notification_id' => 76,
                'group_id' => 13,
            ],
            [
                'notification_id' => 76,
                'group_id' => 19,
            ],
            [
                'notification_id' => 76,
                'group_id' => 27,
            ],
            [
                'notification_id' => 76,
                'group_id' => 47,
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->where('name', self::NOTIFICATION_NAME)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 76)->delete();

        DB::commit();
    }
};
