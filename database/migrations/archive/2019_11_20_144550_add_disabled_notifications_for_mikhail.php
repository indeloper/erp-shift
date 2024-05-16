<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDisabledNotificationsForMikhail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        DB::table('user_disabled_notifications')->insert([
            // Ismagilov Disabled Notifications
            [
                'notification_id' => 55,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 56,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 57,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 58,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 59,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 60,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 61,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 62,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 11,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
            ],
            [
                'notification_id' => 12,
                'user_id' => 6,
                'in_telegram' => 0,
                'in_system' => 0,
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
        //
    }
}
