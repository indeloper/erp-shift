<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewNotificationForPrincipleMechanicsAboutTechnicSetFree extends Migration
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

        $new_type = [
            'id' => self::NOTIFICATION_TYPE,
            'group' => 10,
            'name' => 'Уведомление об освобождении техники',
            'for_everyone' => 0 // for groups
        ];

        DB::table('notification_types')->insert($new_type);

        $notification_group = [
            'notification_id'  => self::NOTIFICATION_TYPE,
            'group_id' => 47,
        ];

        DB::table('notifications_for_groups')->insert($notification_group);

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

        DB::commit();
    }
}
