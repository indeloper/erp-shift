<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsageReportTaskNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $new_types = [
            'id' => 110,
            'group' => 10,
            'name' => 'Уведомление о задаче "Отметка времени использования техники"',
            'for_everyone' => 1
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
        DB::table('notification_types')->whereIn('id', 110)->delete();
    }
}