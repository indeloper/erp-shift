<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const NOTIFICATION_TYPE = 87;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $new_types = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 10,
            'name' => 'Уведомление об одобрении продления использования техники',
            'for_everyone' => 0, // for groups and users
        ];

        DB::table('notification_types')->insert($new_types);

        $notification_groups = [
            'notification_id' => self::NOTIFICATION_TYPE,
            'group_id' => 47,
        ];

        DB::table('notifications_for_groups')->insert($notification_groups);

        $notification_users = [
            'notification_id' => self::NOTIFICATION_TYPE,
            'user_id' => User::HARDCODED_PERSONS['router'],
        ];

        DB::table('notifications_for_users')->insert($notification_users);

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

        DB::table('notification_types')->where('id', self::NOTIFICATION_TYPE)->delete();
        DB::table('notifications_for_groups')->where('notification_id', self::NOTIFICATION_TYPE)->delete();
        DB::table('notifications_for_users')->where('notification_id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
};
