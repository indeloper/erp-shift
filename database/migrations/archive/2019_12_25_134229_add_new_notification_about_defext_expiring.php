<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о скором окончании периода ремонта по заявке на неисправность';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            [
                'id' => 78,
                'group' => 10,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 78,
                'group_id' => 46,
            ],
            [
                'notification_id' => 78,
                'group_id' => 47,
            ],
            [
                'notification_id' => 78,
                'group_id' => 48,
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
        DB::table('notifications_for_groups')->where('notification_id', 78)->delete();

        DB::commit();
    }
};
