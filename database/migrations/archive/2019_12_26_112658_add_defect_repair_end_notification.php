<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о завершении работ по заявке на неисправность';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            [
                'id' => 80,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 80,
                'group_id' => 5,
            ],
            [
                'notification_id' => 80,
                'group_id' => 6,
            ],
            [
                'notification_id' => 80,
                'group_id' => 13,
            ],
            [
                'notification_id' => 80,
                'group_id' => 19,
            ],
            [
                'notification_id' => 80,
                'group_id' => 27,
            ],
            [
                'notification_id' => 80,
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
        DB::table('notifications_for_groups')->where('notification_id', 80)->delete();

        DB::commit();
    }
};
