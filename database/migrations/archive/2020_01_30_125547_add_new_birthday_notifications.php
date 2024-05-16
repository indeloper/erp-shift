<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewBirthdayNotifications extends Migration
{
    const NOTIFICATIONS = [
        88 => 'Уведомление о дне рождения сотрудника за неделю',
        89 => 'Уведомление о дне рождения сотрудника в день рождения',
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
                'group' => 9,
                'name' => $name,
                'for_everyone' => 1,
            ];
        }

        DB::table('notification_types')->insert($new_types);

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

        DB::commit();
    }
}
