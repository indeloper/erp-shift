<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewNotificationToMatAccOperationWithoutContractId extends Migration
{
    const NOTIFICATION_TYPE = 109;

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
            'group' => 2,
            'name' => 'Уведомление о задаче Контроль договоров в операциях',
            'for_everyone' => 0 // for groups
        ];

        DB::table('notification_types')->insert($new_type);

        $notification_groups = [
            [
                'notification_id'  => self::NOTIFICATION_TYPE,
                'group_id' => 8,
            ],
            [
                'notification_id'  => self::NOTIFICATION_TYPE,
                'group_id' => 27,
            ],
        ];

        DB::table('notifications_for_groups')->insert($notification_groups);

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
