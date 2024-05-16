<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTimeResponsibleDepositionNotification extends Migration
{
    const NOTIFICATION_TYPE = 91;

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
            'name' => 'Уведомление о снятии с позиции ответственного за учёт рабочего времени на проекте',
            'for_everyone' => 1,
        ];

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

        DB::table('notification_types')->where('id', self::NOTIFICATION_TYPE)->delete();

        DB::commit();
    }
}
