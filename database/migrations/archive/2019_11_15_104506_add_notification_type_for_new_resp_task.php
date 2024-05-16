<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('notification_types')->insert([
            // Commercial Offer Notifications
            [
                'id' => 63,
                'group' => 6,
                'name' => 'Уведомление о создании задачи Назначение ответственного руководителя проектов',
                'for_everyone' => 0, // for groups
            ],
        ]);

        DB::table('notifications_for_groups')->insert([
            // Commercial Offer Notifications
            [
                'notification_id' => 63,
                'group_id' => 14,
            ],
            [
                'notification_id' => 63,
                'group_id' => 23,
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

        DB::table('notification_types')->where('id', 63)->delete();
        DB::table('notifications_for_groups')->where('notification_id', 63)->delete();

        DB::commit();
    }
};
