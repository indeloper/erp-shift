<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_NAME = 'Уведомление о создании задачи на проверку изменений в контрагентах';

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
                'id' => 94,
                'group' => 4,
                'name' => self::NOTIFICATION_NAME,
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            [
                'notification_id' => 94,
                'group_id' => 7,
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
        DB::table('notifications_for_groups')->where('notification_id', 94)->delete();

        DB::commit();
    }
};
