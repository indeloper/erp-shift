<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 86;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $notification_group = [
            'notification_id' => self::NOTIFICATION_TYPE,
            'user_id' => User::HARDCODED_PERSONS['router'],
        ];

        DB::table('notifications_for_users')->insert($notification_group);

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

        DB::table('notifications_for_users')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
